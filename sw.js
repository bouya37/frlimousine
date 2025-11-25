// Service Worker pour Beverly Limousine PWA
const CACHE_NAME = 'beverly-limousine-v1.2';
const STATIC_CACHE = 'beverly-limousine-static-v1.2';
const DYNAMIC_CACHE = 'beverly-limousine-dynamic-v1.2';

// Ressources à mettre en cache
const STATIC_ASSETS = [
  '/',
  '/index.html',
  '/assets/css/main_clean.css',
  '/assets/css/frlimousine.css',
  '/assets/js/frlimousine-optimized.js',
  '/manifest.json',
  '/favicon.ico',
  '/apple-touch-icon.png'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('🔧 Service Worker installing...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('📦 Caching static assets...');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => self.skipWaiting())
  );
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('🚀 Service Worker activating...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
            console.log('🗑️ Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Interception des requêtes
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Stratégie Cache First pour les ressources statiques
  if (STATIC_ASSETS.includes(url.pathname) || request.destination === 'style' || request.destination === 'script') {
    event.respondWith(
      caches.match(request)
        .then(response => {
          if (response) {
            return response;
          }
          return fetch(request).then(response => {
            // Cache les nouvelles ressources statiques
            if (response.status === 200) {
              const responseClone = response.clone();
              caches.open(STATIC_CACHE).then(cache => {
                cache.put(request, responseClone);
              });
            }
            return response;
          });
        })
    );
  }
  // Stratégie Network First pour les API et contenu dynamique
  else if (url.pathname.includes('/receive-pdf.php') || request.method === 'POST') {
    event.respondWith(
      fetch(request)
        .then(response => {
          // Cache les réponses réussies
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(DYNAMIC_CACHE).then(cache => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // Fallback vers le cache si offline
          return caches.match(request);
        })
    );
  }
  // Stratégie Stale While Revalidate pour les autres ressources
  else {
    event.respondWith(
      caches.match(request)
        .then(response => {
          const fetchPromise = fetch(request).then(networkResponse => {
            if (networkResponse.status === 200) {
              const responseClone = networkResponse.clone();
              caches.open(DYNAMIC_CACHE).then(cache => {
                cache.put(request, responseClone);
              });
            }
            return networkResponse;
          });

          return response || fetchPromise;
        })
    );
  }
});

// Gestion des notifications push (pour futures fonctionnalités)
self.addEventListener('push', event => {
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: '/assets/images/icon-192.png',
      badge: '/assets/images/icon-192.png',
      vibrate: [100, 50, 100],
      data: {
        dateOfArrival: Date.now(),
        primaryKey: data.primaryKey
      }
    };

    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

// Gestion des clics sur notifications
self.addEventListener('notificationclick', event => {
  event.notification.close();

  event.waitUntil(
    clients.openWindow('/')
  );
});

// Synchronisation en arrière-plan (pour futures fonctionnalités)
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Logique de synchronisation en arrière-plan
  console.log('🔄 Background sync executed');
}

// Gestion de l'état hors ligne
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
});