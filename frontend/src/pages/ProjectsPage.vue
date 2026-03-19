<template>
  <AppContainer>
    <section class="page-hero">
      <p class="page-hero__eyebrow">Проекты</p>
      <h1>Каркас для кейсов, которые продают не стек, а инженерный результат.</h1>
      <p>
        Вместо безликих карточек здесь уже есть структура под задачу, результат, стек и ключевые улучшения.
      </p>
    </section>

    <div class="project-list">
      <BaseCard v-for="item in items" :key="item.title" class="project-card">
        <div class="project-card__header">
          <div>
            <p class="card-kicker">Кейс</p>
            <h2>{{ item.title }}</h2>
          </div>
          <ul class="tag-list" aria-label="Стек проекта">
            <li v-for="tag in item.stack" :key="tag">{{ tag }}</li>
          </ul>
        </div>
        <p>{{ item.summary }}</p>
        <p class="card-note">{{ item.result }}</p>
        <ul class="list-clean">
          <li v-for="point in item.highlights" :key="point">{{ point }}</li>
        </ul>
      </BaseCard>
    </div>
  </AppContainer>
</template>

<script setup>
import AppContainer from '@/components/layout/AppContainer.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import { PROJECTS as items } from '@/constants/site';
</script>
