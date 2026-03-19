<template>
  <BaseSection title="Как подхожу к работе" description="Коротко о том, как превращаю хаос в план.">
    <div class="grid-cards process-grid">
      <BaseCard v-for="step in steps" :key="step.title">
        <p class="card-kicker">Подход</p>
        <h3>{{ step.title }}</h3>
        <p>{{ step.text }}</p>
      </BaseCard>
    </div>
  </BaseSection>
</template>

<script setup>
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseSection from '@/components/ui/BaseSection.vue';
import { PROCESS_STEPS as steps } from '@/constants/site';
</script>
