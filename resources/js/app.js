import './bootstrap';
import { registerPasskey, authenticatePasskey } from './webauthn';

window.registerPasskey = registerPasskey;
window.authenticatePasskey = authenticatePasskey;

import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import 'primeicons/primeicons.css';

import LeadCrud from './components/LeadCrud.vue';
import CalendarShowcase from './components/CalendarShowcase.vue';

const initializeVueApp = (elementId, componentName, component) => {
    const el = document.getElementById(elementId);
    if (el && !el.__vue_app__) {
        const app = createApp({});

        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    darkModeSelector: '.dark',
                }
            }
        });
        
        app.use(ToastService);
        app.use(ConfirmationService);

        app.component(componentName, component);
        app.mount(el);
    }
};

const bootApps = () => {
    initializeVueApp('lead-crud-app', 'lead-crud', LeadCrud);
    initializeVueApp('calendar-app', 'calendar-showcase', CalendarShowcase);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApps);
} else {
    bootApps();
}

document.addEventListener('livewire:navigated', bootApps);

