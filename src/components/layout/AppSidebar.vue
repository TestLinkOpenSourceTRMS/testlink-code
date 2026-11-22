<template>
  <nav class="sidebar" :class="{ collapsed: collapsed }">
    <div class="sidebar-header">
      <span class="sidebar-logo">TL</span>
      <span class="sidebar-title" v-if="!collapsed">TestLink</span>
      <button class="sidebar-toggle" @click="collapsed = !collapsed">
        <i :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
      </button>
    </div>

    <div class="sidebar-project" v-if="currentProject">
      <i class="bi bi-folder2-open"></i>
      <span v-if="!collapsed" class="ms-2">{{ currentProject.name }}</span>
    </div>

    <ul class="sidebar-nav">
      <li>
        <RouterLink :to="currentProject ? `/projects/${currentProject.id}` : '/'" class="sidebar-link">
          <i class="bi bi-speedometer2"></i>
          <span v-if="!collapsed" class="ms-2">대시보드</span>
        </RouterLink>
      </li>
      <li>
        <RouterLink :to="currentProject ? `/projects/${currentProject.id}/suites` : '/'" class="sidebar-link">
          <i class="bi bi-folder"></i>
          <span v-if="!collapsed" class="ms-2">테스트 케이스</span>
        </RouterLink>
      </li>
      <li>
        <RouterLink to="/milestones" class="sidebar-link">
          <i class="bi bi-flag"></i>
          <span v-if="!collapsed" class="ms-2">밀리스톤</span>
        </RouterLink>
      </li>
    </ul>

    <div class="sidebar-footer" v-if="!collapsed">
      <div class="sidebar-user">
        <i class="bi bi-person-circle"></i>
        <span class="ms-2 small">{{ username }}</span>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  currentProject: { type: Object, default: null }
})

const collapsed = ref(false)
const username = computed(() => localStorage.getItem('tl_username') || '사용자')
</script>

<style scoped>
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: #1e2a3a;
  color: #c8d6e5;
  display: flex;
  flex-direction: column;
  transition: width 0.2s ease;
  flex-shrink: 0;
}
.sidebar.collapsed { width: 56px; }
.sidebar-header {
  display: flex;
  align-items: center;
  padding: 16px 12px;
  border-bottom: 1px solid #2d3f54;
  gap: 8px;
}
.sidebar-logo {
  background: #0d6efd;
  color: white;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.85rem;
  flex-shrink: 0;
}
.sidebar-title { font-weight: 600; font-size: 1rem; color: #fff; }
.sidebar-toggle {
  margin-left: auto;
  background: none;
  border: none;
  color: #c8d6e5;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
}
.sidebar-toggle:hover { background: #2d3f54; }
.sidebar-project {
  padding: 10px 12px;
  font-size: 0.8rem;
  color: #7f8c9a;
  border-bottom: 1px solid #2d3f54;
  white-space: nowrap;
  overflow: hidden;
}
.sidebar-nav {
  list-style: none;
  padding: 8px 0;
  margin: 0;
  flex: 1;
}
.sidebar-link {
  display: flex;
  align-items: center;
  padding: 10px 16px;
  color: #c8d6e5;
  text-decoration: none;
  font-size: 0.9rem;
  white-space: nowrap;
  transition: background 0.15s;
}
.sidebar-link:hover,
.sidebar-link.router-link-active {
  background: #2d3f54;
  color: #fff;
}
.sidebar-footer {
  padding: 12px;
  border-top: 1px solid #2d3f54;
}
.sidebar-user { display: flex; align-items: center; color: #7f8c9a; }
</style>
