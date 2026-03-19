<template>
  <BaseSection
    title="Что показать работодателю или заказчику"
    description="Каркас уже ведёт к более предметному разговору о результате, а не только о технологиях."
  >
    <div class="grid-cards">
      <BaseCard v-for="project in projects" :key="project.title">
        <p class="card-kicker">Кейс</p>
        <h3>{{ project.title }}</h3>
        <p>{{ project.summary }}</p>
        <p class="card-note">{{ project.result }}</p>
      </BaseCard>
    </div>
  </BaseSection>
</template>

<script setup>
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseSection from '@/components/ui/BaseSection.vue';
import { PROJECTS } from '@/constants/site';

const projects = PROJECTS.filter((project) => project.featured);
</script>
