<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-body">
            <h4 class="card-title text-center mb-4">TestLink 로그인</h4>
            <form @submit.prevent="login">
              <div class="mb-3">
                <input type="text" class="form-control" v-model="username" placeholder="사용자명" required>
              </div>
              <div class="mb-3">
                <input type="password" class="form-control" v-model="password" placeholder="비밀번호" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">로그인</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const username = ref('')
const password = ref('')
function login() {
  // TODO: implement
}
</script>
