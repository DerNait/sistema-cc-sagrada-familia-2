import http from 'k6/http'
import { check, group, sleep } from 'k6'
import { Trend, Counter } from 'k6/metrics'

/**
 * ENV esperadas:
 * BASE_URL (p.ej. http://app:8000 en Docker; http://localhost:8080 desde host)
 * EMAIL / PASSWORD (credenciales válidas)
 * DO_WRITES=true|false  -> activa POSTs de prueba (default: false)
 */

export const options = {
  scenarios: {
    publico: {
      executor: 'ramping-vus',
      exec: 'flujoPublico',
      startVUs: 0,
      stages: [
        { duration: '45s', target: 10 },
        { duration: '2m',  target: 10 },
        { duration: '30s', target: 0  },
      ],
      tags: { flow: 'public' },
    },
    autenticado: {
      executor: 'ramping-vus',
      exec: 'flujoAutenticado',
      startVUs: 0,
      stages: [
        { duration: '45s', target: 10 },
        { duration: '3m',  target: 10 },
        { duration: '30s', target: 0  },
      ],
      tags: { flow: 'auth' },
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.02'],                 // <2% fallos
    'http_req_duration{flow:public}': ['p(95)<450'],
    'http_req_duration{flow:auth}':   ['p(95)<600'],
  },
  tags: { app: 'laravel-vue' },
  // Más tolerante si tu backend tiene colas bajo carga
  httpReqTimeout: '120s',
  // noConnectionReuse: true, // (déjalo comentado salvo que sospeches keep-alive roto)
}

// --------- ENV y métricas custom ----------
const BASE = __ENV.BASE_URL || 'http://localhost:8080'
const EMAIL = __ENV.EMAIL || 'test@example.com'
const PASSWORD = __ENV.PASSWORD || 'password'
const DO_WRITES = (__ENV.DO_WRITES || 'false').toLowerCase() === 'true'

const t_login = new Trend('login_ms')
const c_login_ok = new Counter('login_ok')
const c_csrf_parse_fail = new Counter('csrf_parse_fail')

// --------- Utilidades robustas ----------
function parseCsrfToken(html) {
  if (typeof html !== 'string') return null
  // Soporta comillas simples o dobles y atributos en distinto orden
  const m = html.match(/name=["']_token["'][^>]*value=["']([^"']+)["']/i)
  return m ? m[1] : null
}

function getWithRetry(url, params = {}, tries = 3) {
  for (let i = 0; i < tries; i++) {
    const res = http.get(url, params)
    if (res && res.status > 0) return res
    // backoff exponencial suave
    sleep(0.5 * (i + 1))
  }
  // Falso Response para que los checks puedan fallar sin crashear
  return { status: 0, headers: {}, body: null, timings: { duration: 0 } }
}

// --------- Flujo público (sin login) ----------
export function flujoPublico() {
  group('publico:listados', () => {
    const r1 = getWithRetry(`${BASE}/productos`, { tags: { step: 'productos' } }, 3)
    check(r1, {
      'GET /productos 200/OK': r => r.status === 200,
      'content ok': r => {
        const ct = (r.headers['Content-Type'] || '').toLowerCase()
        return ct.includes('text/html') || ct.includes('application/json')
      },
    })

    const r2 = getWithRetry(`${BASE}/productos/tipos`, { tags: { step: 'productos_tipos' } }, 3)
    check(r2, {
      'GET /productos/tipos 200/OK': r => r.status === 200,
    })
  })

  group('publico:home', () => {
    const rh = getWithRetry(`${BASE}/`, { tags: { step: 'home' } }, 3)
    check(rh, { 'GET / 200|302': r => [200, 302].includes(r.status) })
  })

  sleep(1) // pacing
}

// --------- Flujo autenticado (Laravel web guard) ----------
export function flujoAutenticado() {
  const jar = http.cookieJar()

  group('auth:login', () => {
    // 1) Form de login y CSRF
    const loginPage = getWithRetry(`${BASE}/login`, { tags: { step: 'login_form' } }, 3)
    const okForm = check(loginPage, { 'GET /login 200': r => r.status === 200 })
    if (!okForm) {
      c_csrf_parse_fail.add(1)
      sleep(1)
      return // sin form válido, no seguimos para evitar crash
    }

    const csrf = parseCsrfToken(loginPage.body)
    if (!csrf) {
      c_csrf_parse_fail.add(1)
      sleep(1)
      return // sin CSRF, detenemos este ciclo del VU
    }

    // 2) POST /login (form-urlencoded por objeto JS)
    const loginRes = http.post(
      `${BASE}/login`,
      { _token: csrf, email: EMAIL, password: PASSWORD },
      { tags: { step: 'login_post' }, redirects: 0, timeout: '120s' }
    )
    // Evita agregar NaN si hubo error de red
    if (loginRes && loginRes.timings && typeof loginRes.timings.duration === 'number') {
      t_login.add(loginRes.timings.duration)
    } else {
      t_login.add(0)
    }

    const logged = loginRes && (loginRes.status === 302 || loginRes.status === 200)
    check(loginRes, { 'login ok (302/200)': _ => logged })
    if (!logged) {
      sleep(1)
      return // si no logramos login, no sigas al dashboard
    }
    c_login_ok.add(1)

    // 3) /dashboard (autenticado por cookie de sesión)
    const dash = getWithRetry(`${BASE}/dashboard`, { tags: { step: 'dashboard' } }, 3)
    check(dash, { 'GET /dashboard 200': r => r.status === 200 })
  })

  group('auth:admin:index', () => {
    const rutas = [
      '/admin/empleados',
      '/admin/usuarios',
      '/admin/roles',
      '/admin/estudiantes',
      '/admin/productos',
      '/admin/cursos',
      '/admin/pagos',
      '/admin/actividades',
      '/admin/secciones',
    ]
    for (const path of rutas) {
      const r = getWithRetry(`${BASE}${path}`, { tags: { step: 'index', path } }, 2)
      check(r, { [`GET ${path} 200`]: res => res.status === 200 })
      sleep(0.2)
    }
  })

  if (DO_WRITES) {
    group('auth:writes(opcional)', () => {
      // 1) Form de create para obtener CSRF fresco
      const pageNew = getWithRetry(`${BASE}/admin/productos/crear`, { tags: { step: 'productos_create_form' } }, 2)
      const csrf2 = parseCsrfToken(pageNew.body)
      if (!csrf2) {
        c_csrf_parse_fail.add(1)
        // No hacemos POST si no hay token
      } else {
        // 2) POST create (payload mínimo; ajusta a tu formulario)
        const create = http.post(
          `${BASE}/admin/productos`,
          {
            _token: csrf2,
            name: `K6 Item ${Math.random().toString(36).slice(2, 8)}`,
            price: 99.99,
          },
          { tags: { step: 'productos_store' }, timeout: '120s' }
        )
        check(create, { 'POST productos 200/302': r => [200, 302].includes(r.status) })
      }
    })
  }

  sleep(1) // pacing
}
