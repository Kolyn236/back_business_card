<template>
  <AppContainer>
    <section class="page-hero not-found">
      <p class="page-hero__eyebrow">404</p>
      <h1>Такой страницы нет.</h1>
      <p>Либо адрес кривой, либо страницу ещё не сделали.</p>
      <RouterLink to="/" class="base-button base-button--primary">На главную</RouterLink>
    </section>
  </AppContainer>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import AppContainer from '@/components/layout/AppContainer.vue';
</script>
