import { useRoute } from 'vue-router';
import { computed, watchEffect } from 'vue';
import { SITE_META } from '@/constants/site';

function ensureMetaTag(selector, attributes) {
  let tag = document.head.querySelector(selector);

  if (!tag) {
    tag = document.createElement('meta');
    Object.entries(attributes).forEach(([key, value]) => {
      tag.setAttribute(key, value);
    });
    document.head.appendChild(tag);
  }

  return tag;
}

export function usePageSeo() {
  const route = useRoute();

  const pageTitle = computed(() => route.meta.title || SITE_META.title);
  const pageDescription = computed(() => route.meta.description || SITE_META.description);

  watchEffect(() => {
    document.title = pageTitle.value;

    const descriptionTag = ensureMetaTag('meta[name="description"]', { name: 'description' });
    descriptionTag.setAttribute('content', pageDescription.value);

    const ogTitleTag = ensureMetaTag('meta[property="og:title"]', { property: 'og:title' });
    ogTitleTag.setAttribute('content', pageTitle.value);

    const ogDescriptionTag = ensureMetaTag('meta[property="og:description"]', {
      property: 'og:description',
    });
    ogDescriptionTag.setAttribute('content', pageDescription.value);
  });
}
