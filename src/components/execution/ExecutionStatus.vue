<template>
  <div class="execution-buttons d-flex gap-2 flex-wrap">
    <button
      v-for="btn in buttons"
      :key="btn.status"
      class="exec-btn"
      :class="[btn.class, { active: modelValue === btn.status, loading: loading && modelValue === btn.status }]"
      @click="execute(btn.status)"
      :disabled="loading"
    >
      <i :class="btn.icon" class="me-1"></i>
      {{ btn.label }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: 'n' },
  loading: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'execute'])

const buttons = [
  { status: 'p', label: '통과', class: 'exec-pass', icon: 'bi bi-check-circle-fill' },
  { status: 'f', label: '실패', class: 'exec-fail', icon: 'bi bi-x-circle-fill' },
  { status: 'b', label: '차단', class: 'exec-blocked', icon: 'bi bi-slash-circle-fill' },
  { status: 's', label: '건너뜀', class: 'exec-skip', icon: 'bi bi-arrow-right-circle-fill' }
]

function execute(status) {
  emit('update:modelValue', status)
  emit('execute', status)
}
</script>

<style scoped>
.exec-btn {
  padding: 10px 20px;
  border: 2px solid transparent;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.15s;
  background: #f8f9fa;
  color: #495057;
}
.exec-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.exec-pass { border-color: #198754; color: #198754; }
.exec-pass:hover, .exec-pass.active { background: #198754; color: white; }
.exec-fail { border-color: #dc3545; color: #dc3545; }
.exec-fail:hover, .exec-fail.active { background: #dc3545; color: white; }
.exec-blocked { border-color: #fd7e14; color: #fd7e14; }
.exec-blocked:hover, .exec-blocked.active { background: #fd7e14; color: white; }
.exec-skip { border-color: #6c757d; color: #6c757d; }
.exec-skip:hover, .exec-skip.active { background: #6c757d; color: white; }
</style>
