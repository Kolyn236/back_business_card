<template>
  <BaseCard>
    <h2>Что можно предложить</h2>
    <ul class="list-clean">
      <li v-for="item in topics" :key="item">{{ item }}</li>
    </ul>

    <h3>Что лучше указать сразу</h3>
    <ul class="list-clean">
      <li v-for="item in hints" :key="item">{{ item }}</li>
    </ul>
  </BaseCard>
</template>

<script setup>
import BaseCard from '@/components/ui/BaseCard.vue';
import { INTAKE_HINTS as hints, OFFER_TOPICS as topics } from '@/constants/site';
</script>
