import http from 'k6/http'
import { check, sleep } from 'k6'

export const options = {
  vus: 1,
  duration: '1m',
  httpReqTimeout: '5s',
  tags: { demo: 'smoke' },
  discardResponseBodies: true, // menos I/O
}

const BASE = __ENV.BASE_URL || 'http://app:8000'

function getFast(url, tags = {}, tries = 2) {
  for (let i = 0; i < tries; i++) {
    const r = http.get(url, { tags, timeout: '5s' })
    if (r.status > 0) return r
    sleep(0.5 * (i + 1))
  }
  return { status: 0, headers: {}, body: null }
}

export default function () {
  const r1 = getFast(`${BASE}/productos`, { step: 'productos' })
  check(r1, { 'GET /productos 200': r => r.status === 200 })

  const r2 = getFast(`${BASE}/productos/tipos`, { step: 'productos_tipos' })
  check(r2, { 'GET /productos/tipos 200': r => r.status === 200 })

  const r3 = getFast(`${BASE}/`, { step: 'home' })
  check(r3, { 'GET / 200|302': r => [200, 302].includes(r.status) })

  sleep(1)
}
