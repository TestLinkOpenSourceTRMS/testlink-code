<template>
  <div class="app-layout" v-if="isAuthenticated">
    <AppSidebar :current-project="currentProject" />
    <div class="main-area">
      <AppHeader @project-changed="currentProject = $event" />
      <main class="content-area">
        <RouterView />
      </main>
    </div>
  </div>
  <RouterView v-else />
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import AppSidebar from './components/layout/AppSidebar.vue'
import AppHeader from './components/layout/AppHeader.vue'

const route = useRoute()
const currentProject = ref(null)
const isAuthenticated = computed(() => {
  return !!localStorage.getItem('tl_apikey') && route.name !== 'login'
})
</script>

<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f6f9; }
.app-layout { display: flex; min-height: 100vh; }
.main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.content-area { flex: 1; padding: 24px; overflow-y: auto; }
</style>
