<template>
  <div class="milestone-list">
    <div v-if="milestones.length === 0" class="text-muted text-center py-5">
      <i class="bi bi-flag fs-1 d-block mb-2"></i>
      밀리스톤이 없습니다. 새 밀리스톤을 추가하세요.
    </div>

    <div v-else class="timeline">
      <div
        v-for="ms in sortedMilestones"
        :key="ms.id"
        class="milestone-card"
        :class="{
          'milestone-completed': ms.is_completed == 1,
          'milestone-overdue': isOverdue(ms),
          'milestone-upcoming': isUpcoming(ms)
        }"
      >
        <!-- 상태 아이콘 -->
        <div class="milestone-icon">
          <i v-if="ms.is_completed == 1" class="bi bi-check-circle-fill text-success fs-5"></i>
          <i v-else-if="isOverdue(ms)" class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
          <i v-else class="bi bi-flag-fill text-primary fs-5"></i>
        </div>

        <!-- 카드 내용 -->
        <div class="milestone-content">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h6 class="mb-0">{{ ms.name }}</h6>
              <small class="text-muted" v-if="ms.description">{{ ms.description }}</small>
            </div>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-secondary" @click="$emit('edit', ms)" title="수정">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="$emit('delete', ms)" title="삭제">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>

          <!-- 기한 표시 -->
          <div class="d-flex align-items-center gap-3 mb-2">
            <span v-if="ms.due_date" :class="getDueDateClass(ms)" class="small">
              <i class="bi bi-calendar3 me-1"></i>
              {{ formatDate(ms.due_date) }}
              <span v-if="isOverdue(ms)" class="ms-1 fw-bold">({{ daysOverdue(ms) }}일 초과)</span>
              <span v-else-if="daysUntil(ms) >= 0" class="ms-1 text-muted">({{ daysUntil(ms) }}일 남음)</span>
            </span>
            <span v-if="ms.is_completed == 1" class="badge bg-success">완료</span>
          </div>

          <!-- 완료율 프로그레스 바 -->
          <div v-if="ms.is_completed != 1">
            <div class="d-flex justify-content-between mb-1">
              <small class="text-muted">진행률</small>
              <small class="fw-medium">{{ ms.completion_rate || 0 }}%</small>
            </div>
            <div class="progress" style="height: 6px;">
              <div
                class="progress-bar"
                :class="isOverdue(ms) ? 'bg-danger' : 'bg-primary'"
                :style="{ width: (ms.completion_rate || 0) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  milestones: { type: Array, default: () => [] }
})

defineEmits(['edit', 'delete'])

const sortedMilestones = computed(() => {
  return [...props.milestones].sort((a, b) => {
    // 미완료 먼저, 그 다음 기한순
    if (a.is_completed != b.is_completed) return a.is_completed - b.is_completed
    if (!a.due_date && !b.due_date) return 0
    if (!a.due_date) return 1
    if (!b.due_date) return -1
    return new Date(a.due_date) - new Date(b.due_date)
  })
})

function isOverdue(ms) {
  if (!ms.due_date || ms.is_completed == 1) return false
  return new Date(ms.due_date) < new Date()
}

function isUpcoming(ms) {
  if (!ms.due_date || ms.is_completed == 1) return false
  const d = new Date(ms.due_date)
  const now = new Date()
  return d >= now && (d - now) < 7 * 24 * 60 * 60 * 1000
}

function daysOverdue(ms) {
  if (!ms.due_date) return 0
  return Math.floor((new Date() - new Date(ms.due_date)) / (1000 * 60 * 60 * 24))
}

function daysUntil(ms) {
  if (!ms.due_date) return -1
  return Math.floor((new Date(ms.due_date) - new Date()) / (1000 * 60 * 60 * 24))
}

function getDueDateClass(ms) {
  if (isOverdue(ms)) return 'text-danger fw-medium'
  if (isUpcoming(ms)) return 'text-warning fw-medium'
  return 'text-muted'
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('ko-KR', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}
</script>

<style scoped>
.timeline { position: relative; }
.milestone-card {
  display: flex;
  gap: 16px;
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 12px;
  transition: box-shadow 0.15s;
}
.milestone-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.milestone-completed { opacity: 0.7; border-color: #d1fae5; background: #f0fff4; }
.milestone-overdue { border-color: #fecaca; }
.milestone-upcoming { border-color: #fde68a; }
.milestone-icon { padding-top: 2px; }
.milestone-content { flex: 1; }
</style>
