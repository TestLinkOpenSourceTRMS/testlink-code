<template>
  <header class="app-header">
    <div class="header-left">
      <select class="project-select" v-model="selectedProjectId" @change="onProjectChange">
        <option value="">프로젝트 선택...</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
    </div>
    <div class="header-right">
      <span class="text-muted small me-3">API Key: {{ maskedKey }}</span>
      <button class="btn btn-sm btn-outline-secondary" @click="logout">로그아웃</button>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getProjects } from '../../api/projects.js'

const router = useRouter()
const projects = ref([])
const selectedProjectId = ref('')

const emit = defineEmits(['project-changed'])

const maskedKey = computed(() => {
  const key = localStorage.getItem('tl_apikey') || ''
  return key.length > 8 ? key.substring(0, 4) + '***' + key.slice(-4) : '***'
})

onMounted(async () => {
  try {
    const res = await getProjects()
    projects.value = res.data || []
  } catch (e) {
    // not logged in
  }
})

function onProjectChange() {
  emit('project-changed', projects.value.find(p => p.id == selectedProjectId.value))
  if (selectedProjectId.value) {
    router.push(`/projects/${selectedProjectId.value}`)
  }
}

function logout() {
  localStorage.removeItem('tl_apikey')
  localStorage.removeItem('tl_username')
  router.push('/login')
}
</script>

<style scoped>
.app-header {
  height: 52px;
  background: #fff;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  position: sticky;
  top: 0;
  z-index: 100;
}
.project-select {
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.9rem;
  min-width: 240px;
  background: #f8f9fa;
}
.header-right { display: flex; align-items: center; }
</style>
