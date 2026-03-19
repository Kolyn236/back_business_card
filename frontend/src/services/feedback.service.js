import api from '@/services/api';

export async function sendFeedback(payload) {
  const response = await api.post('/feedback', payload);
  return response.data;
}
