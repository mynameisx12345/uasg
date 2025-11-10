// Service Worker DISABLED
console.log('[SW] Service Worker is DISABLED - clearing caches');

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.map(key => {
        console.log('[SW] Deleting cache:', key);
        return caches.delete(key);
      })
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', () => {
  return; // Do nothing - let all requests go to network
});
