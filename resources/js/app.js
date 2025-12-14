// Ganti seluruh isi file: resources/js/app.js

import './bootstrap';

// ===================================
// INI ADALAH PERBAIKANNYA:
// Tambahkan 3 baris ini untuk mengaktifkan Alpine.js
// ===================================
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
// ===================================


// (Biarkan kode jQuery/Select2 Anda jika sudah ada di sini)
// $(document).ready(function() {
//     ...
// });