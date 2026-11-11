<script setup>
import { ref } from 'vue'

// Campo de contraseña con botón para mostrar u ocultar lo escrito.
defineProps({
  id: { type: String, default: undefined },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
  minlength: { type: [Number, String], default: undefined },
  autocomplete: { type: String, default: 'new-password' },
})
const modelo = defineModel({ type: String, default: '' })
const visible = ref(false)
</script>

<template>
  <div class="contenedor">
    <input
      :id="id"
      v-model="modelo"
      :type="visible ? 'text' : 'password'"
      :placeholder="placeholder"
      :required="required"
      :minlength="minlength"
      :autocomplete="autocomplete"
    />
    <button
      type="button"
      class="ojo"
      :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
      :title="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
      @click="visible = !visible"
    >
      <svg v-if="!visible" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
      <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a19.8 19.8 0 0 1 5.06-5.94" />
        <path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a19.9 19.9 0 0 1-3.17 4.19" />
        <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
        <line x1="1" y1="1" x2="23" y2="23" />
      </svg>
    </button>
  </div>
</template>

<style scoped>
.contenedor { position: relative; display: flex; }
.contenedor input {
  width: 100%;
  border: 1px solid var(--borde);
  border-radius: 8px;
  padding: 10px 44px 10px 12px;
  background: #fff;
}
.contenedor input:focus { outline: 2px solid var(--teal-claro); border-color: transparent; }
.ojo {
  position: absolute;
  right: 4px;
  top: 50%;
  transform: translateY(-50%);
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  background: none;
  border: 0;
  border-radius: 8px;
  color: var(--gris);
  cursor: pointer;
}
.ojo:hover { color: var(--teal); }
</style>
