(function(){
  // Initialize Laravel Echo with Pusher protocol (for self-hosted Laravel WebSockets)
  // Ensure your broadcasting.php is configured to use the pusher driver and that
  // the websockets server runs on ws://127.0.0.1:6001 (default for beyondcode/laravel-websockets)
  try {
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: 'local', // not used by self-hosted server but required by Echo API
      wsHost: window.location.hostname || '127.0.0.1',
      wsPort: 6001,
      wssPort: 6001,
      forceTLS: false,
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
    });
    console.log('[Echo] Initialized for self-hosted websockets');
  } catch (e) {
    console.warn('[Echo] Failed to initialize:', e);
  }
})();
