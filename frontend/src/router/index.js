import { createRouter, createWebHistory } from 'vue-router';
import { ROUTES } from '@/constants/routes';
import { SITE_META } from '@/constants/site';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: ROUTES.home,
      name: 'home',
      component: () => import('@/pages/HomePage.vue'),
      meta: {
        title: 'Главная | Alex Portfolio',
        description:
          'Backend-разработчик для задач вокруг Symfony, API, интеграций, legacy и стабилизации production.',
      },
    },
    {
      path: ROUTES.about,
      name: 'about',
      component: () => import('@/pages/AboutPage.vue'),
      meta: {
        title: 'Обо мне | Alex Portfolio',
        description:
          'Подход к backend-разработке: архитектура, рефакторинг, интеграции и спокойная инженерная коммуникация.',
      },
    },
    {
      path: ROUTES.projects,
      name: 'projects',
      component: () => import('@/pages/ProjectsPage.vue'),
      meta: {
        title: 'Проекты | Alex Portfolio',
        description:
          'Подборка типовых backend-кейсов: городские сервисы, инфраструктурные проверки и уведомления.',
      },
    },
    {
      path: ROUTES.feedback,
      name: 'feedback',
      component: () => import('@/pages/FeedbackPage.vue'),
      meta: {
        title: 'Оффер или проект | Alex Portfolio',
        description:
          'Форма для вакансий, проектных предложений, консультаций и задач по рефакторингу или API.',
      },
    },
    {
      path: ROUTES.contacts,
      name: 'contacts',
      component: () => import('@/pages/ContactsPage.vue'),
      meta: {
        title: 'Контакты | Alex Portfolio',
        description:
          'Прямые контакты для связи: email, Telegram и GitHub. Подходит для офферов, задач и консультаций.',
      },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/pages/NotFoundPage.vue'),
      meta: {
        title: 'Страница не найдена | Alex Portfolio',
        description: SITE_META.description,
      },
    },
  ],
  scrollBehavior() {
    return { top: 0 };
  },
});

export default router;
