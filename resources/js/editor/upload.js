import state from './state.js';
import { showToast, toastSuccess, toastError, apiFetch } from './utils.js';

export function uploadImageFile(file, callback) {
    const formData = new FormData();
    formData.append('image', file);
    showToast('Enviando imagem...', 'info');
    apiFetch('/page-builder/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
        body: formData,
    })
    .then(data => {
        if (data.url) { toastSuccess('Imagem enviada!'); callback(data.url); }
        else toastError('Falha ao enviar imagem');
    })
    .catch(() => toastError('Falha ao enviar imagem'));
}

export function uploadVideoFile(file, callback) {
    const formData = new FormData();
    formData.append('video', file);
    showToast('Enviando video...', 'info');
    apiFetch('/page-builder/upload-video', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
        body: formData,
    })
    .then(data => {
        if (data.url) { toastSuccess('Video enviado!'); callback(data.url); }
        else toastError('Falha ao enviar video');
    })
    .catch(() => toastError('Falha ao enviar video'));
}
