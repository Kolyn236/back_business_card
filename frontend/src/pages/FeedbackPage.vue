<template>
  <AppContainer>
    <section class="page-hero">
      <p class="page-hero__eyebrow">Оффер / проект / консультация</p>
      <h1>Открыт к сильным предложениям и backend-задачам.</h1>
      <p>
        Здесь можно отправить вакансию, проект, консультационный запрос или предложение по
        сотрудничеству.
      </p>
    </section>

    <div class="feedback-layout">
      <FeedbackInfo />
      <FeedbackForm />
    </div>
  </AppContainer>
</template>

<script setup>
import AppContainer from '@/components/layout/AppContainer.vue';
import FeedbackForm from '@/components/feedback/FeedbackForm.vue';
import FeedbackInfo from '@/components/feedback/FeedbackInfo.vue';
</script>
