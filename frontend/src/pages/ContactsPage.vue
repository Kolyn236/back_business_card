<template>
  <AppContainer>
    <section class="page-hero">
      <p class="page-hero__eyebrow">Контакты</p>
      <h1>Если форма не подходит, можно написать напрямую.</h1>
      <p>
        Для быстрых стартов лучше сразу прислать краткое описание задачи, стек, формат работы и
        ожидаемые сроки ответа.
      </p>
    </section>

    <div class="grid-cards contacts-grid">
      <BaseCard
        v-for="channel in channels"
        :key="channel.title"
      >
        <h2>{{ channel.title }}</h2>
        <a
          :href="channel.href"
          target="_blank"
          rel="noreferrer"
        >
          {{ channel.value }}
        </a>
        <p class="card-note">{{ channel.note }}</p>
      </BaseCard>
    </div>

    <BaseCard>
      <h2>Когда лучше использовать форму</h2>
      <p>
        Страница оффера полезнее, когда нужно собрать структуру запроса: роль, бюджет, сроки,
        стек, ограничения и контекст задачи в одном месте.
      </p>
    </BaseCard>
  </AppContainer>
</template>

<script setup>
import AppContainer from '@/components/layout/AppContainer.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import { CONTACT_CHANNELS as channels } from '@/constants/site';
</script>
