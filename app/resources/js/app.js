import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('theme', {
    dark: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),

    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        this.updateDocument();
    },

    updateDocument() {
        document.documentElement.classList.toggle('dark', this.dark);
    },

    init() {
        this.updateDocument();
    }
});

Alpine.start();
