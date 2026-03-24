<template>
  <div>
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>
    <div v-else>
      <div class="d-flex align-items-center mb-4 gap-3">
        <div>
          <h2 class="mb-1">{{ project?.name }}</h2>
          <span class="text-muted small">{{ project?.prefix }}</span>
        </div>
      </div>

      <!-- 통계 카드 -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value">{{ dashboard.plans_count || 0 }}</div>
            <div class="stat-label">테스트 플랜</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value">{{ dashboard.suites_count || 0 }}</div>
            <div class="stat-label">테스트 스위트</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value">{{ dashboard.testcases_count || 0 }}</div>
            <div class="stat-label">테스트 케이스</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value">{{ dashboard.milestones_count || 0 }}</div>
            <div class="stat-label">밀리스톤</div>
          </div>
        </div>
      </div>

      <!-- 테스트 플랜 목록 -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">테스트 플랜</h5>
        </div>
        <div class="list-group list-group-flush">
          <div v-if="testPlans.length === 0" class="list-group-item text-muted text-center py-4">
            테스트 플랜이 없습니다.
          </div>
          <div
            v-for="plan in testPlans"
            :key="plan.id"
            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
            style="cursor: pointer;"
            @click="selectPlan(plan)"
          >
            <div>
              <div class="fw-medium" :class="selectedPlanId === plan.id ? 'text-primary' : ''">{{ plan.name }}</div>
              <small class="text-muted">{{ plan.notes || '' }}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span :class="plan.active == '1' ? 'badge bg-primary' : 'badge bg-secondary'">
                {{ plan.active == '1' ? '활성' : '비활성' }}
              </span>
              <RouterLink
                :to="`/projects/${route.params.id}/runs/${plan.id}`"
                class="btn btn-sm btn-outline-secondary"
                @click.stop
              >실행</RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- 플랜 선택 시 차트 표시 -->
      <div v-if="selectedPlanId" class="mt-4">
        <h5 class="mb-3">{{ selectedPlanName }} 현황</h5>
        <div class="row g-3">
          <div class="col-md-8">
            <ProgressChart :plan-id="selectedPlanId" />
          </div>
          <div class="col-md-4">
            <ActivityFeed :executions="planExecutions" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { getProject, getProjectTestPlans, getProjectDashboard } from '../api/projects.js'
import { getPlanExecutions } from '../api/executions.js'
import ProgressChart from '../components/dashboard/ProgressChart.vue'
import ActivityFeed from '../components/dashboard/ActivityFeed.vue'

const route = useRoute()
const loading = ref(true)
const project = ref(null)
const testPlans = ref([])
const dashboard = ref({})
const selectedPlanId = ref(null)
const selectedPlanName = ref('')
const planExecutions = ref([])

async function selectPlan(plan) {
  selectedPlanId.value = plan.id
  selectedPlanName.value = plan.name
  try {
    const res = await getPlanExecutions(plan.id)
    planExecutions.value = res.data?.items || []
  } catch (e) {}
}

onMounted(async () => {
  try {
    const id = route.params.id
    const [projRes, plansRes, dashRes] = await Promise.all([
      getProject(id),
      getProjectTestPlans(id),
      getProjectDashboard(id)
    ])
    project.value = projRes.data
    testPlans.value = plansRes.data?.items || []
    dashboard.value = dashRes.data?.item || {}
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.stat-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
}
.stat-value { font-size: 2rem; font-weight: 700; color: #0d6efd; }
.stat-label { color: #6c757d; font-size: 0.85rem; margin-top: 4px; }
</style>
