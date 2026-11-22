<template>
  <div class="test-run-page">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="mt-2 text-muted">테스트 케이스 로딩 중...</p>
    </div>

    <div v-else>
      <!-- 페이지 헤더 -->
      <div class="run-header mb-3">
        <div class="d-flex align-items-center gap-3">
          <RouterLink :to="`/projects/${route.params.id}`" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
          </RouterLink>
          <div>
            <h3 class="mb-0">{{ planName }}</h3>
            <small class="text-muted">빌드: {{ buildName }}</small>
          </div>
        </div>
      </div>

      <!-- 빌드 선택 -->
      <div class="mb-3" v-if="builds.length > 0">
        <select class="form-select form-select-sm w-auto" v-model="selectedBuildId" @change="loadTestCases">
          <option v-for="b in builds" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>

      <!-- 인라인 실행 컴포넌트 -->
      <TestRunInline
        :test-cases="testCases"
        :plan-id="route.params.planId"
        :build-id="selectedBuildId"
        @progress-updated="loadProgress"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import TestRunInline from '../components/execution/TestRunInline.vue'
import { getPlanExecutions, getPlanBuilds } from '../api/executions.js'

const route = useRoute()
const loading = ref(true)
const testCases = ref([])
const builds = ref([])
const selectedBuildId = ref(null)
const planName = ref('')
const buildName = computed(() => {
  const b = builds.value.find(b => b.id == selectedBuildId.value)
  return b?.name || ''
})

onMounted(async () => {
  await loadBuilds()
  await loadTestCases()
})

async function loadBuilds() {
  try {
    const res = await getPlanBuilds(route.params.planId)
    builds.value = res.data?.items || []
    if (builds.value.length > 0) {
      selectedBuildId.value = builds.value[builds.value.length - 1].id
    }
  } catch (e) {
    console.error(e)
  }
}

async function loadTestCases() {
  loading.value = true
  try {
    const res = await getPlanExecutions(route.params.planId)
    testCases.value = res.data?.items || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function loadProgress() {
  // 진행률 새로 고침 (현재는 로컬 업데이트로 처리)
}
</script>

<style scoped>
.test-run-page { height: calc(100vh - 100px); display: flex; flex-direction: column; }
.run-header { padding: 0; }
</style>
