// js/api-handler.js

// URL dasar untuk backend kamu
const BASE_URL = 'http://localhost/mathventure/api';

/**
 * Fungsi global untuk memanggil API
 * @param {string} endpoint - Nama file API (contoh: 'login.php')
 * @param {string} method - 'GET' atau 'POST'
 * @param {object} data - Data yang akan dikirim (untuk POST)
 */
async function fetchAPI(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`${BASE_URL}/${endpoint}`, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error("Terjadi kesalahan koneksi API:", error);
        return { 
            status: 'error', 
            message: 'Gagal terhubung ke server. Periksa koneksi internet atau XAMPP.' 
        };
    }
}