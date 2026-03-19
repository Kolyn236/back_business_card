<template>
  <footer class="site-footer">
    <AppContainer>
      <div class="site-footer__content">
        <div>
          <strong>{{ profile.availability }}</strong>
          <p>{{ profile.tagline }}</p>
        </div>

        <div class="site-footer__links">
          <a :href="`mailto:${contacts.email}`">{{ contacts.email }}</a>
          <a :href="contacts.telegram" target="_blank" rel="noreferrer">Telegram</a>
          <a :href="contacts.github" target="_blank" rel="noreferrer">GitHub</a>
        </div>
      </div>
    </AppContainer>
  </footer>
</template>

<script setup>
import AppContainer from '@/components/layout/AppContainer.vue';
import { CONTACTS as contacts } from '@/constants/contacts';
import { SITE_PROFILE as profile } from '@/constants/site';
</script>
