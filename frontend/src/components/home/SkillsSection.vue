<template>
  <BaseSection title="Чем могу быть полезен" description="Без воды и без витринного пафоса.">
    <div class="grid-cards">
      <BaseCard v-for="item in items" :key="item.title">
        <h3>{{ item.title }}</h3>
        <p>{{ item.text }}</p>
      </BaseCard>
    </div>
  </BaseSection>
</template>

<script setup>
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseSection from '@/components/ui/BaseSection.vue';
import { SKILLS as items } from '@/constants/site';
</script>
