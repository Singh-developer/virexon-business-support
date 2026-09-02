import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
document.addEventListener('click',(e)=>{const m=document.querySelector('.mobile-menu');if(m&&e.target===m){document.querySelector('.sidebar')?.classList.toggle('mobile-open')}});
