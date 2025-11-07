// UASG PWA Helper Functions
class UASGPWAHelper {
    constructor() {
        this.init();
    }
    
    async init() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/uasg/sw.js');
                console.log('UASG PWA: Service Worker registered successfully:', registration);
                
                // Handle service worker updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
            } catch (error) {
                console.error('UASG PWA: Service Worker registration failed:', error);
            }
        }
        
        // Setup install prompt
        this.setupInstallPrompt();
        
        // Setup offline detection
        this.setupOfflineDetection();
        
        // Setup background sync
        this.setupBackgroundSync();
    }
    
    setupInstallPrompt() {
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            this.showInstallButton(deferredPrompt);
        });
        
        window.addEventListener('appinstalled', () => {
            console.log('UASG PWA: App was installed');
            this.hideInstallButton();
            this.showNotification('App Installed', 'UASG is now installed on your device!');
        });
    }
    
    showInstallButton(deferredPrompt) {
        // Create install button if it doesn't exist
        if (!document.getElementById('pwa-install-btn')) {
            const installBtn = document.createElement('button');
            installBtn.id = 'pwa-install-btn';
            installBtn.innerHTML = '📱 Install App';
            installBtn.className = 'pwa-install-button';
            installBtn.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #2196F3;
                color: white;
                border: none;
                padding: 12px 16px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 1000;
                transition: all 0.3s ease;
            `;
            
            installBtn.addEventListener('mouseover', () => {
                installBtn.style.transform = 'translateY(-2px)';
                installBtn.style.boxShadow = '0 6px 16px rgba(0,0,0,0.2)';
            });
            
            installBtn.addEventListener('mouseout', () => {
                installBtn.style.transform = 'translateY(0)';
                installBtn.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            });
            
            installBtn.onclick = async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const choiceResult = await deferredPrompt.userChoice;
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    }
                    deferredPrompt = null;
                    this.hideInstallButton();
                }
            };
            
            document.body.appendChild(installBtn);
        }
    }
    
    hideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.remove();
        }
    }
    
    setupOfflineDetection() {
        const updateOnlineStatus = () => {
            const status = navigator.onLine ? 'online' : 'offline';
            document.body.classList.toggle('offline', !navigator.onLine);
            
            if (!navigator.onLine) {
                this.showNotification('You are offline', 'Some features may be limited.');
            } else {
                this.showNotification('Back online', 'All features are now available.');
            }
        };
        
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
    }
    
    setupBackgroundSync() {
        // Store failed requests for background sync
        this.failedRequests = JSON.parse(localStorage.getItem('uasg-failed-requests') || '[]');
        
        // Intercept AJAX requests to handle offline scenarios
        this.interceptAjaxRequests();
    }
    
    interceptAjaxRequests() {
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                if (!response.ok && !navigator.onLine) {
                    this.queueRequest(args);
                }
                return response;
            } catch (error) {
                if (!navigator.onLine) {
                    this.queueRequest(args);
                    throw new Error('Request queued for when you\'re back online');
                }
                throw error;
            }
        };
    }
    
    queueRequest(requestArgs) {
        const request = {
            id: Date.now(),
            args: requestArgs,
            timestamp: new Date().toISOString()
        };
        
        this.failedRequests.push(request);
        localStorage.setItem('uasg-failed-requests', JSON.stringify(this.failedRequests));
        
        // Register background sync if available
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            navigator.serviceWorker.ready.then(registration => {
                return registration.sync.register('background-upload');
            });
        }
    }
    
    showUpdateNotification() {
        if (confirm('A new version of UASG is available. Update now?')) {
            if (navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
                window.location.reload();
            }
        }
    }
    
    showNotification(title, body, options = {}) {
        // Show browser notification if permission granted
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body,
                icon: '/uasg/resources/icons/icon-192x192.png',
                badge: '/uasg/resources/icons/icon-72x72.png',
                ...options
            });
        } else {
            // Fallback to console log or custom notification
            console.log(`${title}: ${body}`);
            this.showInAppNotification(title, body);
        }
    }
    
    showInAppNotification(title, body) {
        // Create in-app notification
        const notification = document.createElement('div');
        notification.className = 'pwa-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 16px;
            border-radius: 8px;
            max-width: 300px;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        
        notification.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 4px;">${title}</div>
            <div style="font-size: 14px; opacity: 0.9;">${body}</div>
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
        
        // Add CSS animations if not already added
        if (!document.getElementById('pwa-animations')) {
            const style = document.createElement('style');
            style.id = 'pwa-animations';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                .offline::before {
                    content: '📡 Offline Mode';
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    background: #f44336;
                    color: white;
                    text-align: center;
                    padding: 8px;
                    z-index: 10001;
                    font-size: 14px;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Request notification permission
    async requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }
        return Notification.permission === 'granted';
    }
    
    // Check if app is running as PWA
    isPWA() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }
    
    // Get installation status
    getInstallationStatus() {
        if (this.isPWA()) {
            return 'installed';
        }
        
        // Check if beforeinstallprompt has been triggered
        return localStorage.getItem('pwa-installable') === 'true' ? 'installable' : 'not-installable';
    }
}

// Initialize PWA helper when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.uasgPWA = new UASGPWAHelper();
    
    // Request notification permission after a delay
    setTimeout(() => {
        window.uasgPWA.requestNotificationPermission();
    }, 3000);
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UASGPWAHelper;
}