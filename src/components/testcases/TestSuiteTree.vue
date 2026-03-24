<template>
  <div class="suite-tree">
    <div
      v-for="suite in suites"
      :key="suite.id"
      class="suite-item"
      :class="{ active: selectedId == suite.id }"
      @click="$emit('select', suite)"
    >
      <i class="bi bi-folder me-2 text-warning"></i>
      <span>{{ suite.name }}</span>
      <span class="badge bg-light text-secondary ms-auto small">{{ suite.testcase_count || 0 }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  suites: { type: Array, default: () => [] },
  selectedId: { type: [String, Number], default: null }
})
defineEmits(['select'])
</script>

<style scoped>
.suite-tree { padding: 8px 0; }
.suite-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  cursor: pointer;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: background 0.1s;
  margin: 2px 0;
}
.suite-item:hover { background: #f0f4ff; }
.suite-item.active { background: #e7f1ff; font-weight: 600; color: #0d6efd; }
</style>
