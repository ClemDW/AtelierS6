<script setup lang="ts">
import { computed, toRefs } from "vue";

const props = withDefaults(
  defineProps<{
    modelValue: string;
    label?: string;
    placeholder?: string;
    id?: string;
  }>(),
  {
    label: "Rechercher par titre",
    placeholder: "Ex: portrait, voyage, famille...",
    id: "photo-search-field",
  },
);

const emit = defineEmits<{
  (event: "update:modelValue", value: string): void;
}>();

const { modelValue, label, placeholder, id } = toRefs(props);
const fieldId = computed(() => props.id);

const updateValue = (event: Event) => {
  emit("update:modelValue", (event.target as HTMLInputElement).value);
};
</script>

<template>
  <div class="search-bar">
    <label :for="fieldId" class="search-label">{{ label }}</label>
    <input
      :id="fieldId"
      :value="modelValue"
      type="search"
      class="search-input"
      :placeholder="placeholder"
      @input="updateValue"
    />
  </div>
</template>

<style scoped>
.search-bar {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.search-label {
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.search-input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  background: rgba(15, 23, 42, 0.7);
  color: #f1f5f9;
  padding: 0.75rem 0.95rem;
  outline: none;
}

.search-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}
</style>