import http from 'k6/http'
import { check, sleep } from 'k6'
export const options = {
  scenarios: {
    stress: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '30s', target: 20 },
        { duration: '30s', target: 50 },
        { duration: '30s', target: 100 },
        { duration: '30s', target: 0 },
      ],
      tags: { demo: 'stress' },
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.02'],
    http_req_duration: ['p(95)<600'], // deliberado para que posiblemente falle
  },
  discardResponseBodies: true,
  httpReqTimeout: '120s',
}

const BASE = __ENV.BASE_URL || 'http://app:8000'

export default function () {
  const r = http.get(`${BASE}/productos`)
  check(r, { '200 OK': x => x.status === 200 })
  sleep(0.2)
}
