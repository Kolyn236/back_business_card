<template>
  <AppContainer>
    <section class="page-hero">
      <p class="page-hero__eyebrow">Обо мне</p>
      <h1>Backend-разработчик, который любит приводить систему в порядок.</h1>
      <p>
        Основной вектор — Symfony, API, интеграции, архитектура, тестирование и вытаскивание
        legacy из болота без громких лозунгов.
      </p>
    </section>

    <div class="grid-cards">
      <BaseCard v-for="item in cards" :key="item.title">
        <h2>{{ item.title }}</h2>
        <p>{{ item.text }}</p>
      </BaseCard>
    </div>

    <BaseCard>
      <h2>Принципы работы</h2>
      <ul class="list-clean">
        <li v-for="item in principles" :key="item">{{ item }}</li>
      </ul>
    </BaseCard>
  </AppContainer>
</template>

<script setup>
import AppContainer from '@/components/layout/AppContainer.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import { ABOUT_CARDS as cards, PRINCIPLES as principles } from '@/constants/site';
</script>
