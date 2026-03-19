<template>
  <div class="success-box">
    <div>
      <strong>Сообщение отправлено.</strong>
      <p>Я посмотрю предложение и вернусь с ответом, как только смогу.</p>
    </div>
    <button type="button" class="base-button base-button--secondary" @click="$emit('close')">
      Закрыть
    </button>
  </div>
</template>

<script setup>
defineEmits(['close']);
</script>
