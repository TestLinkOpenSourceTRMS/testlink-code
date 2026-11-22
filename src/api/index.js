import axios from 'axios'

const api = axios.create({
  baseURL: '/lib/api/rest/v3',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// API 키를 localStorage에서 주입
api.interceptors.request.use(config => {
  const apiKey = localStorage.getItem('tl_apikey')
  if (apiKey) {
    config.headers['apiKey'] = apiKey
  }
  return config
})

// 401 시 로그인 페이지로 리다이렉트
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('tl_apikey')
      window.location.hash = '#/login'
    }
    return Promise.reject(error)
  }
)

export default api
