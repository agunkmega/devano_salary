import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const host = window.reverbConfig?.host || import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const port = window.reverbConfig?.port || import.meta.env.VITE_REVERB_PORT || (window.location.protocol === 'https:' ? 443 : 8080);
const scheme = window.reverbConfig?.scheme || import.meta.env.VITE_REVERB_SCHEME || (window.location.protocol === 'https:' ? 'https' : 'http');
const appKey = window.reverbConfig?.app_key || import.meta.env.VITE_REVERB_APP_KEY || 'employee-key';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: appKey,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
});
