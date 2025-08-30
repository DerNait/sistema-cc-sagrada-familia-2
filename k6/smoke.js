import http from 'k6/http'
import { check, sleep } from 'k6'

export const options = { vus: 1, duration: '30s', tags: { demo: 'smoke' }, discardResponseBodies: true }

const BASE = __ENV.BASE_URL || 'http://app:8000'

export default function () {
  const r1 = http.get(`${BASE}/productos`)
  check(r1, { 'GET /productos 200': r => r.status === 200 })

  const r2 = http.get(`${BASE}/productos/tipos`)
  check(r2, { 'GET /productos/tipos 200': r => r.status === 200 })

  const r3 = http.get(`${BASE}/`)
  check(r3, { 'GET / 200|302': r => [200,302].includes(r.status) })

  sleep(1)
}
