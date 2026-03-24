<template>
  <div class="tc-table-wrapper">
    <div v-if="!testCases.length" class="text-muted text-center py-5">
      <i class="bi bi-file-text fs-2 d-block mb-2"></i>
      테스트 케이스가 없습니다.
    </div>
    <table v-else class="table table-hover tc-table">
      <thead>
        <tr>
          <th style="width: 40px;">#</th>
          <th>이름</th>
          <th style="width: 80px;">우선순위</th>
          <th style="width: 80px;">실행유형</th>
          <th style="width: 60px;"></th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="tc in testCases"
          :key="tc.id || tc.tc_id"
          class="tc-row"
          :class="{ selected: selectedId == (tc.id || tc.tc_id) }"
          @click="$emit('select', tc)"
        >
          <td class="text-muted small">{{ tc.external_id || tc.tc_external_id }}</td>
          <td>
            <div class="fw-medium">{{ tc.name }}</div>
            <small class="text-muted" v-if="tc.summary" v-html="stripHtml(tc.summary)"></small>
          </td>
          <td>
            <span :class="getPriorityClass(tc.importance)">{{ getPriorityLabel(tc.importance) }}</span>
          </td>
          <td>
            <span class="badge bg-light text-secondary">{{ getExecType(tc.execution_type) }}</span>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" @click.stop="$emit('edit', tc)">
              <i class="bi bi-pencil"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  testCases: { type: Array, default: () => [] },
  selectedId: { type: [String, Number], default: null }
})
defineEmits(['select', 'edit'])

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '').substring(0, 100)
}

function getPriorityClass(p) {
  const map = { 1: 'badge bg-secondary', 2: 'badge bg-warning text-dark', 3: 'badge bg-danger' }
  return map[p] || 'badge bg-light text-secondary'
}

function getPriorityLabel(p) {
  const map = { 1: '낮음', 2: '중간', 3: '높음' }
  return map[p] || '-'
}

function getExecType(t) {
  return t == 2 ? '자동' : '수동'
}
</script>

<style scoped>
.tc-table { font-size: 0.875rem; }
.tc-row { cursor: pointer; }
.tc-row.selected { background: #e7f1ff; }
.tc-table-wrapper { overflow-x: auto; }
</style>
