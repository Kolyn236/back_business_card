export function offerTypeLabel(value) {
  const map = {
    vacancy: 'Вакансия',
    project: 'Проект',
    consultation: 'Консультация',
    partnership: 'Партнёрство',
    other: 'Другое',
  };

  return map[value] || 'Не указано';
}
