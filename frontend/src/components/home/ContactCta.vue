<template>
  <section class="cta-box">
    <h2>Есть оффер, проект или тяжёлая backend-задача?</h2>
    <p>
      {{ profile.availability }} Лучше сразу написать стек, формат сотрудничества, сроки и вилку.
    </p>
    <RouterLink to="/feedback" class="base-button base-button--primary">Перейти к форме</RouterLink>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { SITE_PROFILE as profile } from '@/constants/site';
</script>
