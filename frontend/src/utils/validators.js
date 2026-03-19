const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function isRequired(value) {
  return String(value || '').trim().length > 0;
}

export function isValidEmail(value) {
  return emailPattern.test(String(value || '').trim());
}

export function validateFeedbackForm(form) {
  const errors = {};

  if (!isRequired(form.name)) {
    errors.name = 'Укажи имя';
  }

  if (!isRequired(form.email)) {
    errors.email = 'Укажи email';
  } else if (!isValidEmail(form.email)) {
    errors.email = 'Некорректный email';
  }

  if (!isRequired(form.message)) {
    errors.message = 'Опиши предложение или задачу';
  }

  if (!form.consent) {
    errors.consent = 'Нужно согласие на обработку данных';
  }

  return errors;
}
