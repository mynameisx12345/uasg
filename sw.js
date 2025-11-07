// UASG PWA Service Worker
const CACHE_NAME = 'uasg-pwa-v1.0.0';
const OFFLINE_URL = '/uasg/offline.html';

// Files to cache for offline functionality
const STATIC_CACHE_URLS = [
  '/uasg/',
  '/uasg/index.php',
  '/uasg/login.php',
  '/uasg/offline.html',
  '/uasg/resources/style.css',
  '/uasg/resources/datatable.css',
  '/uasg/js/jquery.js',
  '/uasg/js/datatable.js',
  '/uasg/js/all.js',
  '/uasg/resources/icons/icon-192x192.png',
  '/uasg/resources/icons/icon-512x512.png'
];

// Dynamic cache patterns
const DYNAMIC_CACHE_PATTERNS = [
  /\/uasg\/admin\//,
  /\/uasg\/adviser\//,
  /\/uasg\/member\//,
  /\/uasg\/resources\//,
  /\/uasg\/js\//
];

// API endpoints that should always go to network
const NETWORK_ONLY_PATTERNS = [
  /\/uasg\/.*ajax\.php/,
  /\/uasg\/auth\.php/,
  /\/uasg\/setup_admin\.php/
];

// Install event - cache static assets
self.addEventListener('install', event => {
  console.log('UASG PWA: Service Worker installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('UASG PWA: Caching static assets');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        console.log('UASG PWA: Static assets cached successfully');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('UASG PWA: Error caching static assets:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('UASG PWA: Service Worker activating...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames
            .filter(cacheName => {
              return cacheName.startsWith('uasg-pwa-') && cacheName !== CACHE_NAME;
            })
            .map(cacheName => {
              console.log('UASG PWA: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            })
        );
      })
      .then(() => {
        console.log('UASG PWA: Service Worker activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }
  
  // Network-only for API endpoints
  if (NETWORK_ONLY_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    event.respondWith(
      fetch(request)
        .catch(() => {
          // Return offline response for failed API calls
          return new Response(
            JSON.stringify({ 
              status: 'ERROR', 
              msg: 'You are offline. Please check your connection.',
              offline: true 
            }),
            { 
              headers: { 'Content-Type': 'application/json' },
              status: 503
            }
          );
        })
    );
    return;
  }
  
  // Cache-first strategy for static assets
  if (STATIC_CACHE_URLS.includes(url.pathname) || 
      url.pathname.includes('/resources/') || 
      url.pathname.includes('/js/')) {
    event.respondWith(
      caches.match(request)
        .then(response => {
          if (response) {
            return response;
          }
          return fetch(request)
            .then(response => {
              // Cache successful responses
              if (response.status === 200) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME)
                  .then(cache => cache.put(request, responseClone));
              }
              return response;
            });
        })
        .catch(() => {
          // Return offline page for navigation requests
          if (request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
        })
    );
    return;
  }
  
  // Network-first strategy for dynamic content
  if (DYNAMIC_CACHE_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    event.respondWith(
      fetch(request)
        .then(response => {
          // Cache successful responses
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => cache.put(request, responseClone));
          }
          return response;
        })
        .catch(() => {
          // Try to serve from cache
          return caches.match(request)
            .then(response => {
              if (response) {
                return response;
              }
              // Return offline page for navigation requests
              if (request.mode === 'navigate') {
                return caches.match(OFFLINE_URL);
              }
              throw new Error('No cached version available');
            });
        })
    );
    return;
  }
  
  // Default: Network-first for everything else
  event.respondWith(
    fetch(request)
      .catch(() => {
        if (request.mode === 'navigate') {
          return caches.match(OFFLINE_URL);
        }
      })
  );
});

// Background sync for file uploads when online
self.addEventListener('sync', event => {
  if (event.tag === 'background-upload') {
    event.waitUntil(processBackgroundUploads());
  }
});

// Process queued uploads when connection is restored
async function processBackgroundUploads() {
  try {
    const requests = await getQueuedRequests();
    for (const request of requests) {
      try {
        await fetch(request.url, request.options);
        await removeQueuedRequest(request.id);
        
        // Notify user of successful upload
        self.registration.showNotification('Upload Successful', {
          body: 'Your file has been uploaded successfully.',
          icon: '/uasg/resources/icons/icon-192x192.png',
          badge: '/uasg/resources/icons/icon-72x72.png'
        });
      } catch (error) {
        console.error('Background upload failed:', error);
      }
    }
  } catch (error) {
    console.error('Error processing background uploads:', error);
  }
}

// Helper functions for background sync
async function getQueuedRequests() {
  // Implementation would depend on IndexedDB storage
  return [];
}

async function removeQueuedRequest(id) {
  // Implementation would depend on IndexedDB storage
}

// Push notification handling
self.addEventListener('push', event => {
  if (!event.data) return;
  
  const data = event.data.json();
  const options = {
    body: data.body || 'New notification from UASG',
    icon: '/uasg/resources/icons/icon-192x192.png',
    badge: '/uasg/resources/icons/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: data.data || {},
    actions: [
      {
        action: 'view',
        title: 'View',
        icon: '/uasg/resources/icons/view-icon.png'
      },
      {
        action: 'dismiss',
        title: 'Dismiss'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title || 'UASG Notification', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow(event.notification.data.url || '/uasg/')
    );
  }
});

// Message handling from main thread
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

console.log('UASG PWA: Service Worker loaded successfully');