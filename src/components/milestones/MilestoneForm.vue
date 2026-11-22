<template>
  <div class="modal fade show d-block" tabindex="-1" @click.self="$emit('close')">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ isEdit ? '밀리스톤 수정' : '밀리스톤 추가' }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="save">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">이름 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="form.name" required placeholder="예: v1.0 릴리즈">
            </div>
            <div class="mb-3">
              <label class="form-label">설명</label>
              <textarea class="form-control" v-model="form.description" rows="3" placeholder="밀리스톤 목표 및 범위"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">목표일</label>
              <input type="date" class="form-control" v-model="form.due_date">
            </div>
            <div v-if="isEdit" class="form-check">
              <input type="checkbox" class="form-check-input" id="completed" v-model="form.is_completed">
              <label class="form-check-label" for="completed">완료됨</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">취소</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              저장
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show"></div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  milestone: { type: Object, default: null },
  loading: { type: Boolean, default: false }
})

const emit = defineEmits(['save', 'close'])
const isEdit = computed(() => !!props.milestone?.id)

const form = ref({
  name: props.milestone?.name || '',
  description: props.milestone?.description || '',
  due_date: props.milestone?.due_date || '',
  is_completed: props.milestone?.is_completed == 1
})

function save() {
  emit('save', {
    ...form.value,
    is_completed: form.value.is_completed ? 1 : 0
  })
}
</script>
