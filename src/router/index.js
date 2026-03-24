import { createRouter, createWebHashHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    component: () => import('../views/DashboardView.vue')
  },
  {
    path: '/login',
    component: () => import('../views/LoginView.vue')
  },
  {
    path: '/projects/:id',
    component: () => import('../views/ProjectView.vue')
  },
  {
    path: '/projects/:id/runs/:planId',
    component: () => import('../views/TestRunView.vue')
  },
  {
    path: '/milestones',
    component: () => import('../views/MilestoneView.vue')
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
