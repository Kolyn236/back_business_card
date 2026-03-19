<template>
  <section class="hero">
    <div class="hero__content">
      <p class="hero__eyebrow">{{ hero.eyebrow }}</p>
      <h1>{{ hero.title }}</h1>
      <p class="hero__text">
        {{ hero.description }}
      </p>
      <ul class="hero__focus" aria-label="Ключевые направления">
        <li v-for="item in hero.focus" :key="item">{{ item }}</li>
      </ul>
      <div class="hero__actions">
        <RouterLink to="/feedback" class="base-button base-button--primary">Отправить оффер</RouterLink>
        <RouterLink to="/projects" class="base-button base-button--secondary">Смотреть проекты</RouterLink>
      </div>
    </div>
    <div class="hero__stats" aria-label="Ключевые факты">
      <div v-for="item in hero.stats" :key="item.label" class="hero-stat">
        <strong>{{ item.value }}</strong>
        <span>{{ item.label }}</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { HERO_CONTENT as hero } from '@/constants/site';
</script>
