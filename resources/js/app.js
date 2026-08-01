import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { createIcons } from 'lucide';

Alpine.plugin(focus);

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    createIcons({
        strokeWidth: 1.8,
    });
});
