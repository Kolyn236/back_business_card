<template>
  <AppContainer>
    <HeroSection />
    <SkillsSection />
    <ProcessSection />
    <ProjectsPreview />
    <ContactCta />
  </AppContainer>
</template>

<script setup>
import AppContainer from '@/components/layout/AppContainer.vue';
import ContactCta from '@/components/home/ContactCta.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import ProcessSection from '@/components/home/ProcessSection.vue';
import ProjectsPreview from '@/components/home/ProjectsPreview.vue';
import SkillsSection from '@/components/home/SkillsSection.vue';
</script>
