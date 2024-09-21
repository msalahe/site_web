// Import CSS files
import '../assets/plugins/metisMenu/metisMenu.min.css';
import '../assets/plugins/metisMenu/mm-vertical.css';
import '../assets/plugins/notifications/css/lobibox.min.css';
import '../assets/css/bootstrap.min.css';
import '../assets/plugins/datatable/css/dataTables.bootstrap5.min.css';
import '../assets/css/bootstrap-extended.css';
import '../assets/plugins/bs-stepper/css/bs-stepper.css';
import '../assets/sass/main.css';
import '../assets/sass/dark-theme.css';
import '../assets/sass/semi-dark.css';
import '../assets/sass/bordered-theme.css';
import '../assets/sass/responsive.css';

// Import jQuery and set it globally
import * as $ from 'jquery';
global.$ = global.jQuery = $;

// Import Bootstrap
import 'bootstrap';

// Import other JS plugins
import PerfectScrollbar from 'perfect-scrollbar';
import 'datatables.net';
import 'datatables.net-bs5';
import '../assets/plugins/metisMenu/metisMenu.min.js';
import '../assets/plugins/notifications/js/lobibox.min.js';
import '../assets/plugins/notifications/js/notifications.min.js';
import '../assets/plugins/notifications/js/notification-custom-script.js';

import '../assets/plugins/datatable/js/jquery.dataTables.min.js';
import '../assets/plugins/datatable/js/dataTables.bootstrap5.min.js';
import Stepper from 'bs-stepper';

// Import custom JS
import './datatable_custom';
import './bootstrap-confirm-delete.js';
import '../assets/js/main.js';

// Initialize PerfectScrollbar if needed
document.addEventListener("DOMContentLoaded", function() {
    const containers = document.querySelectorAll('.perfect-scrollbar');
    containers.forEach(container => {
        new PerfectScrollbar(container);
    });
});

// Initialize Stepper if needed
document.addEventListener("DOMContentLoaded", function() {
    const steppers = document.querySelectorAll('.bs-stepper');
    steppers.forEach(stepperEl => {
        new Stepper(stepperEl);
    });
});

// Your custom JavaScript
console.log('Hello, Symfony!');
