import { createRouter, createWebHashHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue')
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/:id',
    name: 'project',
    component: () => import('../views/ProjectView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/:id/runs/:planId',
    name: 'testrun',
    component: () => import('../views/TestRunView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/milestones',
    name: 'milestones',
    component: () => import('../views/MilestoneView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/:id/suites',
    name: 'testsuites',
    component: () => import('../views/TestSuiteView.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

router.beforeEach((to) => {
  const isAuthenticated = !!localStorage.getItem('tl_apikey')
  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'login' }
  }
  if (to.name === 'login' && isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router
