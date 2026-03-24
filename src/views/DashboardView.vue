<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">프로젝트 목록</h2>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="projects.length === 0" class="text-muted text-center py-5">
      <i class="bi bi-folder2 fs-1 d-block mb-3"></i>
      프로젝트가 없습니다.
    </div>

    <div v-else class="row g-3">
      <div v-for="project in projects" :key="project.id" class="col-md-4">
        <RouterLink :to="`/projects/${project.id}`" class="text-decoration-none">
          <div class="card h-100 project-card">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <span class="project-prefix me-2">{{ project.prefix }}</span>
                <h5 class="card-title mb-0">{{ project.name }}</h5>
              </div>
              <p class="card-text text-muted small">{{ project.notes || '설명 없음' }}</p>
              <span :class="project.active == '1' ? 'badge bg-success' : 'badge bg-secondary'">
                {{ project.active == '1' ? '활성' : '비활성' }}
              </span>
            </div>
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { getProjects } from '../api/projects.js'

const projects = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await getProjects()
    const data = res.data
    projects.value = Array.isArray(data) ? data : (data?.item || data?.items || [])
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.project-card {
  border: 1px solid #e9ecef;
  transition: box-shadow 0.15s, transform 0.15s;
}
.project-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  transform: translateY(-2px);
}
.project-prefix {
  background: #e7f1ff;
  color: #0d6efd;
  font-weight: 600;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
}
</style>
