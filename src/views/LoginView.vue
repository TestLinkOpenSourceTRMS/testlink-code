<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <span class="logo-badge">TL</span>
        <h1>TestLink</h1>
        <p class="text-muted">API 키로 로그인하세요</p>
      </div>
      <form @submit.prevent="login">
        <div class="mb-3">
          <label class="form-label">API Key</label>
          <input
            type="text"
            class="form-control"
            v-model="apiKey"
            placeholder="TestLink API 키 입력"
            required
            autocomplete="off"
          >
          <div class="form-text">TestLink 계정 설정에서 API 키를 확인하세요.</div>
        </div>
        <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
        <button type="submit" class="btn btn-primary w-100" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          로그인
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api/index.js'

const router = useRouter()
const apiKey = ref('')
const loading = ref(false)
const error = ref('')

async function login() {
  loading.value = true
  error.value = ''
  try {
    // whoAmI 엔드포인트로 API 키 검증
    const res = await api.get('/whoAmI', {
      headers: { apiKey: apiKey.value }
    })
    if (res.data) {
      localStorage.setItem('tl_apikey', apiKey.value)
      localStorage.setItem('tl_username', res.data.login || res.data.email || 'user')
      router.push('/')
    }
  } catch (e) {
    error.value = 'API 키가 올바르지 않습니다.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: #1e2a3a;
  display: flex;
  align-items: center;
  justify-content: center;
}
.login-card {
  background: white;
  border-radius: 12px;
  padding: 40px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.login-logo {
  text-align: center;
  margin-bottom: 32px;
}
.logo-badge {
  display: inline-block;
  background: #0d6efd;
  color: white;
  font-weight: 700;
  font-size: 1.5rem;
  padding: 8px 16px;
  border-radius: 10px;
  margin-bottom: 12px;
}
.login-logo h1 { font-size: 1.5rem; font-weight: 700; color: #1e2a3a; margin: 8px 0 4px; }
</style>
