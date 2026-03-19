<template>
  <BaseCard>
    <h2>Отправить предложение</h2>

    <FeedbackSuccess v-if="success" @close="closeSuccess" />

    <form class="feedback-form" @submit.prevent="submit">
      <div class="feedback-form__grid">
        <BaseInput v-model="form.name" label="Имя *" placeholder="Как к вам обращаться" :error="errors.name" />
        <BaseInput v-model="form.email" type="email" label="Email *" placeholder="you@company.com" :error="errors.email" />
        <BaseInput v-model="form.company" label="Компания" placeholder="Название компании" />

        <label class="field">
          <span class="field__label">Тип предложения</span>
          <select v-model="form.offerType" class="field__control">
            <option value="vacancy">Вакансия</option>
            <option value="project">Проект</option>
            <option value="consultation">Консультация</option>
            <option value="partnership">Партнёрство</option>
            <option value="other">Другое</option>
          </select>
        </label>

        <BaseInput v-model="form.budget" label="Бюджет / вилка" placeholder="Например: 2500-3500 EUR" />
        <BaseInput v-model="form.timeline" label="Сроки" placeholder="Например: старт в апреле" />
      </div>

      <BaseTextarea
        v-model="form.message"
        label="Описание предложения *"
        placeholder="Опишите задачу, роль, стек, условия и ожидания"
        rows="7"
        :error="errors.message"
      />

      <input v-model="form.website" type="text" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />

      <label class="checkbox">
        <input v-model="form.consent" type="checkbox" />
        <span>Согласен на обработку данных *</span>
      </label>
      <small v-if="errors.consent" class="field__error">{{ errors.consent }}</small>

      <p v-if="submitError" class="submit-error">{{ submitError }}</p>

      <BaseButton type="submit" :disabled="isDisabled" block>
        {{ loading ? 'Отправка...' : 'Отправить предложение' }}
      </BaseButton>
    </form>
  </BaseCard>
</template>

<script setup>
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import FeedbackSuccess from '@/components/feedback/FeedbackSuccess.vue';
import { useFeedbackForm } from '@/composables/useFeedbackForm';

const { form, errors, loading, success, submitError, isDisabled, submit, closeSuccess } =
  useFeedbackForm();
</script>
