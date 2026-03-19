<template>
  <header class="site-header">
    <AppContainer class="site-header__inner">
      <RouterLink to="/" class="site-header__brand">{{ profile.brand }}</RouterLink>

      <nav class="site-header__nav" aria-label="Основная навигация">
        <RouterLink v-for="item in links" :key="item.to" :to="item.to" class="site-header__link">
          {{ item.label }}
        </RouterLink>
      </nav>
    </AppContainer>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import AppContainer from '@/components/layout/AppContainer.vue';
import { NAV_LINKS as links, SITE_PROFILE as profile } from '@/constants/site';
</script>
