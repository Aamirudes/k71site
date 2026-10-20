const CACHE_NAME = 'cloths42-store-v2';

self.addEventListener('install', (e) => {
    self.skipWaiting();
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll([
            '/manifest.json'
        ]))
    );
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    return caches.delete(key);
                }
            }));
        })
    );
});

self.addEventListener('fetch', (e) => {
    // Only cache GET requests
    if (e.request.method !== 'GET') return;
    
    // Don't intercept API or Livewire calls
    if (e.request.url.includes('/api/') || e.request.url.includes('/livewire/')) return;

    e.respondWith(
        fetch(e.request).catch(() => caches.match(e.request))
    );
});
