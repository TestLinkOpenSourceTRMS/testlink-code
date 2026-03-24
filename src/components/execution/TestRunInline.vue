<template>
  <div class="test-run-container d-flex">
    <!-- 왼쪽: TC 목록 패널 -->
    <div class="tc-list-panel">
      <div class="tc-list-header">
        <div class="fw-semibold mb-2">테스트 케이스 목록</div>
        <div class="progress mb-2" style="height: 8px;">
          <div
            class="progress-bar bg-success"
            :style="{ width: progress.passRate + '%' }"
          ></div>
          <div
            class="progress-bar bg-danger"
            :style="{ width: progress.failRate + '%' }"
          ></div>
          <div
            class="progress-bar bg-warning"
            :style="{ width: progress.blockedRate + '%' }"
          ></div>
        </div>
        <small class="text-muted">
          {{ progress.executed }}/{{ progress.total }} 실행됨
          ({{ progress.passRate.toFixed(0) }}% 통과)
        </small>
      </div>

      <div class="tc-list-body">
        <template v-for="(suite, suiteId) in groupedTestCases" :key="suiteId">
          <div class="suite-header">{{ suite.name }}</div>
          <div
            v-for="tc in suite.cases"
            :key="tc.tc_id || tc.id"
            class="tc-item"
            :class="[getStatusClass(tc), { active: currentTC?.tc_id === tc.tc_id }]"
            @click="selectTC(tc)"
          >
            <div class="d-flex align-items-center gap-2">
              <span class="tc-status-dot" :class="getStatusDotClass(tc)"></span>
              <span class="tc-name">{{ tc.name }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- 오른쪽: TC 상세 + 실행 패널 -->
    <div class="tc-detail-panel">
      <div v-if="!currentTC" class="d-flex align-items-center justify-content-center h-100 text-muted">
        <div class="text-center">
          <i class="bi bi-arrow-left fs-2 d-block mb-2"></i>
          왼쪽에서 테스트 케이스를 선택하세요
        </div>
      </div>

      <div v-else class="tc-detail">
        <!-- TC 헤더 -->
        <div class="tc-detail-header">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <span class="text-muted small">{{ currentTC.external_id }}</span>
              <h4 class="mb-0 mt-1">{{ currentTC.name }}</h4>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-outline-secondary" @click="navigateTC(-1)" :disabled="currentIndex === 0">
                <i class="bi bi-chevron-up"></i>
              </button>
              <button class="btn btn-sm btn-outline-secondary" @click="navigateTC(1)" :disabled="currentIndex === flatTestCases.length - 1">
                <i class="bi bi-chevron-down"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- 사전 조건 -->
        <div v-if="currentTC.preconditions" class="tc-section mb-3">
          <div class="tc-section-label">사전 조건</div>
          <div class="tc-section-body" v-html="currentTC.preconditions"></div>
        </div>

        <!-- 테스트 단계 -->
        <div class="tc-section mb-4">
          <div class="tc-section-label">테스트 단계</div>
          <div v-if="currentTC.steps && currentTC.steps.length > 0">
            <div v-for="step in currentTC.steps" :key="step.step_number" class="tc-step">
              <div class="step-number">{{ step.step_number }}</div>
              <div class="step-content">
                <div class="step-action" v-html="step.actions"></div>
                <div v-if="step.expected_results" class="step-expected">
                  <small class="text-muted">예상 결과:</small>
                  <div v-html="step.expected_results"></div>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-muted small">테스트 단계가 없습니다.</p>
        </div>

        <!-- 메모 입력 -->
        <div class="mb-3">
          <label class="form-label fw-medium">실행 메모 (선택)</label>
          <textarea
            class="form-control"
            v-model="executionNote"
            rows="2"
            placeholder="버그 링크, 메모 등..."
          ></textarea>
        </div>

        <!-- 실행 버튼 -->
        <div class="execution-area">
          <ExecutionStatus
            v-model="executionStatus"
            :loading="executing"
            @execute="submitExecution"
          />
        </div>

        <!-- 이전 실행 결과 -->
        <div v-if="currentTC.last_exec_result" class="mt-3">
          <small class="text-muted">
            마지막 실행: <span :class="getStatusBadge(currentTC.last_exec_result)">
              {{ getStatusLabel(currentTC.last_exec_result) }}
            </span>
          </small>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import ExecutionStatus from './ExecutionStatus.vue'
import { createExecution } from '../../api/executions.js'

const props = defineProps({
  testCases: { type: Array, default: () => [] },
  planId: { type: [String, Number], required: true },
  buildId: { type: [String, Number], default: null }
})

const emit = defineEmits(['execution-saved', 'progress-updated'])

const currentTC = ref(null)
const currentIndex = ref(0)
const executionNote = ref('')
const executionStatus = ref('n')
const executing = ref(false)

// 진행률 계산
const progress = computed(() => {
  const total = props.testCases.length
  if (total === 0) return { total: 0, executed: 0, passRate: 0, failRate: 0, blockedRate: 0 }

  const passed = props.testCases.filter(tc => tc.last_exec_result === 'p').length
  const failed = props.testCases.filter(tc => tc.last_exec_result === 'f').length
  const blocked = props.testCases.filter(tc => tc.last_exec_result === 'b').length
  const executed = passed + failed + blocked

  return {
    total,
    executed,
    passRate: (passed / total) * 100,
    failRate: (failed / total) * 100,
    blockedRate: (blocked / total) * 100
  }
})

// 스위트별 그룹화
const groupedTestCases = computed(() => {
  const groups = {}
  props.testCases.forEach(tc => {
    const suiteName = tc.tsuite_name || tc.parent_id || '기본'
    if (!groups[suiteName]) {
      groups[suiteName] = { name: suiteName, cases: [] }
    }
    groups[suiteName].cases.push(tc)
  })
  return groups
})

const flatTestCases = computed(() => props.testCases)

function selectTC(tc) {
  currentTC.value = tc
  currentIndex.value = flatTestCases.value.findIndex(t => t.tc_id === tc.tc_id)
  executionNote.value = ''
  executionStatus.value = tc.last_exec_result || 'n'
}

function navigateTC(direction) {
  const newIndex = currentIndex.value + direction
  if (newIndex >= 0 && newIndex < flatTestCases.value.length) {
    selectTC(flatTestCases.value[newIndex])
  }
}

async function submitExecution(status) {
  if (!currentTC.value || executing.value) return

  executing.value = true
  try {
    const payload = {
      testcaseid: currentTC.value.tc_id || currentTC.value.id,
      testplanid: props.planId,
      buildid: props.buildId,
      status,
      notes: executionNote.value
    }

    await createExecution(payload)

    // 로컬 상태 업데이트
    currentTC.value.last_exec_result = status

    emit('execution-saved', { tc: currentTC.value, status })
    emit('progress-updated')

    // 자동으로 다음 TC로 이동
    if (currentIndex.value < flatTestCases.value.length - 1) {
      setTimeout(() => navigateTC(1), 500)
    }
  } catch (e) {
    console.error('실행 저장 실패:', e)
  } finally {
    executing.value = false
  }
}

function getStatusClass(tc) {
  const status = tc.last_exec_result
  if (status === 'p') return 'tc-passed'
  if (status === 'f') return 'tc-failed'
  if (status === 'b') return 'tc-blocked'
  return ''
}

function getStatusDotClass(tc) {
  const status = tc.last_exec_result
  if (status === 'p') return 'dot-pass'
  if (status === 'f') return 'dot-fail'
  if (status === 'b') return 'dot-blocked'
  return 'dot-none'
}

function getStatusBadge(status) {
  const map = { p: 'badge bg-success', f: 'badge bg-danger', b: 'badge bg-warning text-dark', n: 'badge bg-secondary' }
  return map[status] || 'badge bg-secondary'
}

function getStatusLabel(status) {
  const map = { p: '통과', f: '실패', b: '차단', n: '미실행' }
  return map[status] || '알 수 없음'
}
</script>

<style scoped>
.test-run-container { height: calc(100vh - 120px); }
.tc-list-panel {
  width: 300px;
  min-width: 300px;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
  background: white;
}
.tc-list-header { padding: 16px; border-bottom: 1px solid #dee2e6; }
.tc-list-body { flex: 1; overflow-y: auto; }
.suite-header {
  padding: 8px 16px;
  background: #f8f9fa;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-top: 1px solid #e9ecef;
}
.tc-item {
  padding: 10px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: background 0.1s;
}
.tc-item:hover { background: #f8f9fa; }
.tc-item.active { background: #e7f1ff; border-left: 3px solid #0d6efd; }
.tc-item.tc-passed { background: #f0fff4; }
.tc-item.tc-failed { background: #fff5f5; }
.tc-item.tc-blocked { background: #fff8f0; }
.tc-name { font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tc-status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-pass { background: #198754; }
.dot-fail { background: #dc3545; }
.dot-blocked { background: #fd7e14; }
.dot-none { background: #dee2e6; }

.tc-detail-panel { flex: 1; overflow-y: auto; padding: 24px; background: white; }
.tc-detail-header { border-bottom: 1px solid #e9ecef; padding-bottom: 16px; margin-bottom: 16px; }
.tc-section-label { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; color: #6c757d; margin-bottom: 8px; letter-spacing: 0.5px; }
.tc-section-body { background: #f8f9fa; border-radius: 6px; padding: 12px; font-size: 0.9rem; }
.tc-step { display: flex; gap: 12px; margin-bottom: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px; }
.step-number {
  width: 28px; height: 28px;
  background: #0d6efd; color: white;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 600;
  flex-shrink: 0;
}
.step-content { flex: 1; }
.step-action { font-size: 0.9rem; margin-bottom: 6px; }
.step-expected { font-size: 0.85rem; color: #495057; border-left: 3px solid #dee2e6; padding-left: 8px; margin-top: 6px; }
.execution-area { padding: 16px; background: #f8f9fa; border-radius: 10px; }
</style>
