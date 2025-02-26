import './bootstrap';

import flatpickr from "flatpickr";
import 'flatpickr/dist/flatpickr.min.css';

// Import theme handler
import initThemeHandler from './theme-handler';

// Initialize Flatpickr globally if needed
window.flatpickr = flatpickr;

// Register theme handler with Alpine
document.addEventListener('alpine:init', () => {
    window.Alpine.data('initThemeHandler', initThemeHandler);
});
