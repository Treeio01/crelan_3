import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Axios configuration
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF token for Laravel
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Pusher for Laravel Echo (required for Reverb)
window.Pusher = Pusher;

/**
 * Laravel Echo configuration for Reverb WebSocket server
 * 
 * Reverb использует Pusher protocol, поэтому используем pusher driver
 */
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    // Disable stats for Reverb
    disableStats: true,
});

// Debug logging in development
if (import.meta.env.DEV) {
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('[Echo] Connected to Reverb WebSocket server');
    });
    
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('[Echo] Disconnected from Reverb WebSocket server');
    });
    
    window.Echo.connector.pusher.connection.bind('error', (error) => {
        console.error('[Echo] Connection error:', error);
    });
}
