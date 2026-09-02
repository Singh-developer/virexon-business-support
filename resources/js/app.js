import './bootstrap';
import axios from 'axios';
import Alpine from 'alpinejs';

// Setup Axios (Note: This is often already handled inside your bootstrap.js file)
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Setup Alpine
window.Alpine = Alpine;
Alpine.start();

// Mobile menu toggle
document.addEventListener('click', (e) => {
    // Using closest() ensures the click registers even if you click an icon/span inside the button
    const m = e.target.closest('.mobile-menu'); 
    
    if (m) {
        document.querySelector('.sidebar')?.classList.toggle('mobile-open');
    }
});