import { computed, reactive, ref } from 'vue';
import { validateFeedbackForm } from '@/utils/validators';
import { sendFeedback } from '@/services/feedback.service';

const FIELD_LABELS = {
  name: 'имя',
  email: 'email',
  company: 'компания',
  offerType: 'тип предложения',
  budget: 'бюджет',
  message: 'описание',
  timeline: 'сроки',
  consent: 'согласие',
};

function getDefaultForm() {
  return {
    name: '',
    email: '',
    company: '',
    offerType: 'vacancy',
    budget: '',
    message: '',
    timeline: '',
    consent: false,
    website: '',
  };
}

function normalizeApiErrors(apiErrors) {
  if (!apiErrors || typeof apiErrors !== 'object') {
    return {};
  }

  return Object.fromEntries(
    Object.entries(apiErrors)
      .map(([field, messages]) => [field, Array.isArray(messages) ? messages[0] : messages])
      .filter(([, message]) => typeof message === 'string' && message.trim() !== ''),
  );
}

function buildValidationSummary(apiErrors, fallbackSummary) {
  if (typeof fallbackSummary === 'string' && fallbackSummary.trim() !== '') {
    return fallbackSummary;
  }

  const labels = Object.keys(apiErrors).map((field) => FIELD_LABELS[field] || field);

  if (labels.length === 0) {
    return 'Проверь поля формы и попробуй снова.';
  }

  return `Проверь поля формы: ${labels.join(', ')}.`;
}

export function useFeedbackForm() {
  const form = reactive(getDefaultForm());
  const errors = ref({});
  const loading = ref(false);
  const success = ref(false);
  const submitError = ref('');

  const isDisabled = computed(() => loading.value);

  function resetForm() {
    Object.assign(form, getDefaultForm());
    errors.value = {};
    submitError.value = '';
  }

  async function submit() {
    submitError.value = '';

    if (form.website) {
      success.value = true;
      resetForm();
      return;
    }

    errors.value = validateFeedbackForm(form);

    if (Object.keys(errors.value).length > 0) {
      return;
    }

    loading.value = true;

    try {
      await sendFeedback({ ...form });
      success.value = true;
      resetForm();
    } catch (error) {
      const apiErrors = normalizeApiErrors(error?.response?.data?.errors);

      if (error?.response?.status === 422 && Object.keys(apiErrors).length > 0) {
        errors.value = {
          ...errors.value,
          ...apiErrors,
        };
        submitError.value = buildValidationSummary(apiErrors, error?.response?.data?.summary);
        return;
      }

      submitError.value =
        error?.response?.data?.summary ||
        error?.response?.data?.message ||
        'Не удалось отправить форму. Проверь endpoint или попробуй альтернативный способ связи.';
    } finally {
      loading.value = false;
    }
  }

  function closeSuccess() {
    success.value = false;
  }

  return {
    form,
    errors,
    loading,
    success,
    submitError,
    isDisabled,
    submit,
    closeSuccess,
  };
}
