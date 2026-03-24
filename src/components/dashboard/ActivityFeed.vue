<template>
  <div class="activity-feed">
    <h6 class="feed-title">최근 실행 활동</h6>
    <div v-if="!executions || executions.length === 0" class="text-muted text-center py-3 small">
      실행 내역이 없습니다.
    </div>
    <div v-else>
      <div v-for="exec in recentExecutions" :key="exec.id || exec.tc_id" class="activity-item">
        <span class="activity-badge" :class="getStatusClass(exec.last_exec_result)">
          {{ getStatusLabel(exec.last_exec_result) }}
        </span>
        <div class="activity-info">
          <div class="activity-name">{{ exec.name }}</div>
          <div class="activity-time text-muted small">{{ exec.execution_ts || '' }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  executions: { type: Array, default: () => [] }
})

const recentExecutions = computed(() =>
  props.executions
    .filter(e => e.last_exec_result && e.last_exec_result !== 'n')
    .slice(0, 20)
)

function getStatusClass(status) {
  const map = { p: 'badge-pass', f: 'badge-fail', b: 'badge-blocked' }
  return map[status] || 'badge-none'
}

function getStatusLabel(status) {
  const map = { p: '통과', f: '실패', b: '차단', n: '미실행' }
  return map[status] || '-'
}
</script>

<style scoped>
.activity-feed { background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 16px; }
.feed-title { font-weight: 600; color: #495057; margin-bottom: 12px; }
.activity-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
.activity-item:last-child { border-bottom: none; }
.activity-badge { font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 12px; white-space: nowrap; }
.badge-pass { background: #d1fae5; color: #065f46; }
.badge-fail { background: #fee2e2; color: #991b1b; }
.badge-blocked { background: #ffedd5; color: #9a3412; }
.badge-none { background: #f3f4f6; color: #6b7280; }
.activity-name { font-size: 0.85rem; font-weight: 500; }
.activity-time { font-size: 0.75rem; }
</style>
