// UASG PWA Helper Functions
class UASGPWAHelper {
    constructor() {
        this.init();
    }
    
    async init() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            try {
                // Determine correct path for service worker based on current location
                const currentPath = window.location.pathname;
                let swPath;
                
                if (currentPath.includes('/uasg/')) {
                    // Extract the base path and append /uasg/sw.js
                    const basePath = currentPath.substring(0, currentPath.indexOf('/uasg/'));
                    swPath = basePath + '/uasg/sw.js';
                } else {
                    // Default path for root level
                    swPath = '/uasg/sw.js';
                }
                
                console.log('UASG PWA: Registering service worker at:', swPath);
                const registration = await navigator.serviceWorker.register(swPath);
                console.log('UASG PWA: Service Worker registered successfully:', registration);
                
                // Check for updates immediately
                registration.update();
                
                // Handle service worker updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    console.log('UASG PWA: New service worker found, installing...');
                    
                    newWorker.addEventListener('statechange', () => {
                        console.log('UASG PWA: Service worker state changed to:', newWorker.state);
                        
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New service worker available
                            console.log('UASG PWA: New version available!');
                            this.showUpdateNotification();
                        }
                    });
                });
                
                // Check for updates every 60 seconds
                setInterval(() => {
                    registration.update();
                }, 60000);
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
        // Store original fetch
        const originalFetch = window.fetch;
        
        // Only intercept if fetch exists
        if (typeof originalFetch === 'function') {
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
        
        // Also intercept jQuery AJAX if available
        if (typeof $ !== 'undefined' && $.ajax) {
            const originalAjax = $.ajax;
            $.ajax = (options) => {
                const originalError = options.error;
                options.error = (xhr, status, error) => {
                    if (!navigator.onLine) {
                        this.queueRequest([options.url, options]);
                        this.showNotification('Request Queued', 'Your request will be sent when you\'re back online.');
                    }
                    if (typeof originalError === 'function') {
                        originalError(xhr, status, error);
                    }
                };
                return originalAjax(options);
            };
        }
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
        // Create a more prominent update banner
        const existingBanner = document.getElementById('pwa-update-banner');
        if (existingBanner) {
            existingBanner.remove();
        }
        
        const banner = document.createElement('div');
        banner.id = 'pwa-update-banner';
        banner.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
            z-index: 10000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            animation: slideDown 0.3s ease-out;
        `;
        
        banner.innerHTML = `
            <style>
                @keyframes slideDown {
                    from { transform: translateY(-100%); }
                    to { transform: translateY(0); }
                }
            </style>
            <span style="flex: 1; text-align: center;">
                <strong>🎉 New version available!</strong> 
                <span style="display: block; font-size: 12px; margin-top: 3px; opacity: 0.9;">
                    Click "Update Now" to get the latest features
                </span>
            </span>
            <button onclick="window.uasgPWA.applyUpdate()" style="
                background: white;
                color: #667eea;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                font-weight: bold;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s;
            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                Update Now
            </button>
            <button onclick="this.parentElement.remove()" style="
                background: transparent;
                color: white;
                border: 1px solid white;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
            ">
                Later
            </button>
        `;
        
        document.body.insertBefore(banner, document.body.firstChild);
        
        // Auto-update after 10 seconds if user doesn't respond
        setTimeout(() => {
            if (document.getElementById('pwa-update-banner')) {
                console.log('UASG PWA: Auto-updating after 10 seconds...');
                this.applyUpdate();
            }
        }, 10000);
    }
    
    applyUpdate() {
        console.log('UASG PWA: Applying update...');
        
        // Remove banner
        const banner = document.getElementById('pwa-update-banner');
        if (banner) {
            banner.remove();
        }
        
        // Tell service worker to skip waiting
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
        }
        
        // Listen for the controlling service worker to change
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('UASG PWA: Controller changed, reloading page...');
            window.location.reload();
        });
    }
    
    async showNotification(title, body, options = {}) {
        // Try native notification first (better for mobile)
        if (await this.tryNativeNotification(title, body, options)) {
            return;
        }
        
        // Try service worker notification (works in background on mobile)
        if (await this.tryServiceWorkerNotification(title, body, options)) {
            return;
        }
        
        // Fallback to in-app notification (always works)
        this.showInAppNotification(title, body, options);
    }
    
    async tryNativeNotification(title, body, options) {
        if (!('Notification' in window)) {
            return false;
        }
        
        try {
            // Request permission if not granted
            if (Notification.permission === 'default') {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    return false;
                }
            }
            
            if (Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body,
                    icon: '/uasg/resources/icons/icon-192x192.png',
                    badge: '/uasg/resources/icons/icon-72x72.png',
                    tag: 'uasg-notification',
                    requireInteraction: true, // Important for mobile
                    vibrate: [200, 100, 200], // Mobile vibration
                    actions: [
                        {
                            action: 'view',
                            title: '👁️ View',
                            icon: '/uasg/resources/icons/icon-72x72.png'
                        },
                        {
                            action: 'dismiss',
                            title: '❌ Dismiss'
                        }
                    ],
                    ...options
                });
                
                // Handle notification clicks (important for mobile)
                notification.onclick = () => {
                    window.focus();
                    notification.close();
                    if (options.url) {
                        window.location.href = options.url;
                    }
                };
                
                return true;
            }
        } catch (error) {
            console.warn('Native notification failed:', error);
        }
        
        return false;
    }
    
    async tryServiceWorkerNotification(title, body, options) {
        if (!('serviceWorker' in navigator)) {
            return false;
        }
        
        try {
            const registration = await navigator.serviceWorker.ready;
            if (registration.showNotification) {
                await registration.showNotification(title, {
                    body,
                    icon: '/uasg/resources/icons/icon-192x192.png',
                    badge: '/uasg/resources/icons/icon-72x72.png',
                    tag: 'uasg-sw-notification',
                    requireInteraction: true,
                    vibrate: [200, 100, 200],
                    data: {
                        url: options.url || window.location.href,
                        timestamp: Date.now()
                    },
                    actions: [
                        {
                            action: 'view',
                            title: '👁️ View'
                        },
                        {
                            action: 'dismiss',
                            title: '❌ Dismiss'
                        }
                    ],
                    ...options
                });
                return true;
            }
        } catch (error) {
            console.warn('Service Worker notification failed:', error);
        }
        
        return false;
    }
    
    showInAppNotification(title, body, options = {}) {
        // Create mobile-optimized in-app notification
        const notification = document.createElement('div');
        notification.className = 'pwa-notification mobile-notification';
        
        // Mobile-optimized styling
        const isMobile = window.innerWidth <= 768 || 'ontouchstart' in window;
        const mobileStyles = isMobile ? `
            top: 10px;
            left: 10px;
            right: 10px;
            max-width: none;
            font-size: 16px;
            padding: 20px;
            touch-action: pan-y;
        ` : `
            top: 20px;
            right: 20px;
            max-width: 350px;
            padding: 16px;
        `;
        
        notification.style.cssText = `
            position: fixed;
            ${mobileStyles}
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            z-index: 10000;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            animation: slideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        `;
        
        // Enhanced notification content with actions
        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div style="flex-shrink: 0; font-size: 24px;">🔔</div>
                <div style="flex: 1;">
                    <div style="font-weight: bold; font-size: ${isMobile ? '18px' : '16px'}; margin-bottom: 6px; line-height: 1.3;">${title}</div>
                    <div style="font-size: ${isMobile ? '16px' : '14px'}; opacity: 0.95; line-height: 1.4; margin-bottom: 12px;">${body}</div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        ${options.url ? `<button class="notification-btn primary" data-action="view">📱 View</button>` : ''}
                        <button class="notification-btn secondary" data-action="dismiss">✕ Dismiss</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Add event listeners for buttons
        notification.querySelectorAll('.notification-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.dataset.action;
                
                if (action === 'view' && options.url) {
                    window.location.href = options.url;
                } else if (action === 'dismiss') {
                    this.dismissNotification(notification);
                }
            });
        });
        
        // Click anywhere to dismiss (mobile-friendly)
        notification.addEventListener('click', () => {
            this.dismissNotification(notification);
        });
        
        // Mobile swipe to dismiss
        if (isMobile) {
            this.addSwipeGesture(notification);
        }
        
        // Auto-dismiss after delay (longer on mobile)
        const dismissDelay = isMobile ? 8000 : 6000;
        setTimeout(() => {
            if (document.body.contains(notification)) {
                this.dismissNotification(notification);
            }
        }, dismissDelay);
        
        // Add mobile-optimized CSS animations if not already added
        this.ensureMobileNotificationStyles();
        
        // Vibrate on mobile if supported
        if (isMobile && 'vibrate' in navigator) {
            navigator.vibrate([100, 50, 100]);
        }
        
        // Store notification for potential future actions
        notification.timestamp = Date.now();
        notification.options = options;
        
        return notification;
    }
    
    dismissNotification(notification) {
        notification.style.animation = 'slideOut 0.3s ease-out forwards';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }
    
    addSwipeGesture(notification) {
        let startX = 0;
        let startY = 0;
        let moved = false;
        
        notification.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            moved = false;
        }, { passive: true });
        
        notification.addEventListener('touchmove', (e) => {
            if (!moved) {
                const deltaX = e.touches[0].clientX - startX;
                const deltaY = Math.abs(e.touches[0].clientY - startY);
                
                // Only respond to horizontal swipes
                if (Math.abs(deltaX) > 30 && deltaY < 50) {
                    moved = true;
                    notification.style.transform = `translateX(${deltaX}px)`;
                    notification.style.opacity = Math.max(0.3, 1 - Math.abs(deltaX) / 200);
                }
            }
        }, { passive: true });
        
        notification.addEventListener('touchend', (e) => {
            if (moved) {
                const deltaX = e.changedTouches[0].clientX - startX;
                
                if (Math.abs(deltaX) > 100) {
                    // Swipe to dismiss
                    notification.style.transform = `translateX(${deltaX > 0 ? '100%' : '-100%'})`;
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                } else {
                    // Snap back
                    notification.style.transform = 'translateX(0)';
                    notification.style.opacity = '1';
                    notification.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                    setTimeout(() => {
                        notification.style.transition = '';
                    }, 300);
                }
            }
        }, { passive: true });
    }
    
    ensureMobileNotificationStyles() {
        if (!document.getElementById('pwa-mobile-notifications')) {
            const style = document.createElement('style');
            style.id = 'pwa-mobile-notifications';
            style.textContent = `
                @keyframes slideIn {
                    from { 
                        transform: translateY(-100%) scale(0.8); 
                        opacity: 0; 
                    }
                    to { 
                        transform: translateY(0) scale(1); 
                        opacity: 1; 
                    }
                }
                
                @keyframes slideOut {
                    from { 
                        transform: translateY(0) scale(1); 
                        opacity: 1; 
                    }
                    to { 
                        transform: translateY(-100%) scale(0.8); 
                        opacity: 0; 
                    }
                }
                
                .notification-btn {
                    background: rgba(255, 255, 255, 0.2);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    backdrop-filter: blur(5px);
                    min-height: 44px; /* Touch-friendly size */
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .notification-btn:hover,
                .notification-btn:active {
                    background: rgba(255, 255, 255, 0.3);
                    border-color: rgba(255, 255, 255, 0.5);
                    transform: scale(1.05);
                }
                
                .notification-btn.primary {
                    background: rgba(33, 150, 243, 0.8);
                    border-color: rgba(33, 150, 243, 1);
                }
                
                .notification-btn.secondary {
                    background: rgba(158, 158, 158, 0.3);
                    border-color: rgba(158, 158, 158, 0.5);
                }
                
                .offline::before {
                    content: '📡 Offline Mode - Limited functionality';
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    background: linear-gradient(90deg, #f44336, #d32f2f);
                    color: white;
                    text-align: center;
                    padding: 12px 8px;
                    z-index: 10001;
                    font-size: 14px;
                    font-weight: 500;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                }
                
                @media (max-width: 768px) {
                    .pwa-notification {
                        font-size: 16px !important;
                    }
                    
                    .notification-btn {
                        min-height: 48px;
                        padding: 12px 20px;
                        font-size: 16px;
                    }
                    
                    .offline::before {
                        padding: 16px 12px;
                        font-size: 16px;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Enhanced notification permission request for mobile
    async requestNotificationPermission() {
        if (!('Notification' in window)) {
            console.warn('Notifications not supported on this device');
            return false;
        }
        
        // Check current permission
        if (Notification.permission === 'granted') {
            return true;
        }
        
        if (Notification.permission === 'denied') {
            // Show instructions for manually enabling notifications
            this.showNotificationEnableInstructions();
            return false;
        }
        
        try {
            // Show user-friendly prompt before requesting permission
            const userWantsNotifications = await this.showNotificationRequestDialog();
            
            if (userWantsNotifications) {
                const permission = await Notification.requestPermission();
                
                if (permission === 'granted') {
                    // Show success message
                    this.showInAppNotification(
                        '🔔 Notifications Enabled!',
                        'You\'ll now receive important alerts and updates.',
                        { type: 'success' }
                    );
                    
                    // Send test notification after short delay
                    setTimeout(() => {
                        this.showNotification(
                            '✅ Test Notification',
                            'Great! Notifications are working on your device.',
                            { tag: 'uasg-test' }
                        );
                    }, 2000);
                    
                    return true;
                } else {
                    this.showInAppNotification(
                        '📱 Notifications Disabled',
                        'You can enable them later in your browser settings.',
                        { type: 'info' }
                    );
                    return false;
                }
            } else {
                return false;
            }
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            this.showInAppNotification(
                '⚠️ Notification Setup Failed',
                'There was an issue setting up notifications. In-app alerts will still work.',
                { type: 'warning' }
            );
            return false;
        }
    }
    
    showNotificationRequestDialog() {
        return new Promise((resolve) => {
            const dialog = document.createElement('div');
            dialog.className = 'notification-permission-dialog';
            dialog.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10002;
                padding: 20px;
                backdrop-filter: blur(5px);
            `;
            
            const isMobile = window.innerWidth <= 768;
            dialog.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 16px;
                    padding: ${isMobile ? '24px' : '32px'};
                    max-width: ${isMobile ? '90vw' : '400px'};
                    width: 100%;
                    text-align: center;
                    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3);
                ">
                    <div style="font-size: 48px; margin-bottom: 16px;">🔔</div>
                    <h3 style="margin: 0 0 16px 0; color: #333; font-size: ${isMobile ? '20px' : '18px'};">
                        Enable Notifications?
                    </h3>
                    <p style="color: #666; margin: 0 0 24px 0; line-height: 1.5; font-size: ${isMobile ? '16px' : '14px'};">
                        Get notified about important updates, file uploads, and system alerts. 
                        Notifications work even when the app is in the background.
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <button id="enableNotifications" style="
                            background: #2196F3;
                            color: white;
                            border: none;
                            padding: ${isMobile ? '14px 24px' : '12px 20px'};
                            border-radius: 8px;
                            font-size: ${isMobile ? '16px' : '14px'};
                            font-weight: 500;
                            cursor: pointer;
                            min-width: ${isMobile ? '120px' : '100px'};
                        ">
                            ✅ Enable
                        </button>
                        <button id="skipNotifications" style="
                            background: #f5f5f5;
                            color: #666;
                            border: 1px solid #ddd;
                            padding: ${isMobile ? '14px 24px' : '12px 20px'};
                            border-radius: 8px;
                            font-size: ${isMobile ? '16px' : '14px'};
                            cursor: pointer;
                            min-width: ${isMobile ? '120px' : '100px'};
                        ">
                            Maybe Later
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
            
            dialog.querySelector('#enableNotifications').onclick = () => {
                document.body.removeChild(dialog);
                resolve(true);
            };
            
            dialog.querySelector('#skipNotifications').onclick = () => {
                document.body.removeChild(dialog);
                resolve(false);
            };
            
            // Close on backdrop click
            dialog.onclick = (e) => {
                if (e.target === dialog) {
                    document.body.removeChild(dialog);
                    resolve(false);
                }
            };
        });
    }
    
    showNotificationEnableInstructions() {
        const isMobile = window.innerWidth <= 768;
        const userAgent = navigator.userAgent.toLowerCase();
        let instructions = 'To enable notifications, please check your browser settings.';
        
        if (userAgent.includes('chrome')) {
            instructions = isMobile ? 
                'Tap the menu (⋮) → Site settings → Notifications → Allow' :
                'Click the lock icon in the address bar → Notifications → Allow';
        } else if (userAgent.includes('firefox')) {
            instructions = isMobile ?
                'Tap the menu → Settings → Site settings → Notifications' :
                'Click the shield icon → Permissions → Notifications → Allow';
        } else if (userAgent.includes('safari')) {
            instructions = isMobile ?
                'Go to Settings → Safari → Notifications → Allow for this site' :
                'Safari → Preferences → Websites → Notifications → Allow';
        }
        
        this.showInAppNotification(
            '🔔 Enable Notifications',
            instructions,
            { type: 'info', duration: 10000 }
        );
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
    
    // Utility function for easy notification triggering from anywhere
    static notify(title, message, options = {}) {
        if (window.uasgPWA) {
            return window.uasgPWA.showNotification(title, message, options);
        } else {
            // Fallback if PWA helper not loaded
            console.warn('UASG PWA helper not loaded, showing basic alert');
            alert(`${title}\n\n${message}`);
            return Promise.resolve(false);
        }
    }
    
    // Quick notification shortcuts for common scenarios
    static notifySuccess(title, message, url) {
        return UASGPWAHelper.notify(title, message, { 
            type: 'success', 
            url: url,
            vibrate: [100, 50, 100] 
        });
    }
    
    static notifyError(title, message) {
        return UASGPWAHelper.notify(title, message, { 
            type: 'error',
            requireInteraction: true,
            vibrate: [300, 100, 300, 100, 300] 
        });
    }
    
    static notifyWarning(title, message, url) {
        return UASGPWAHelper.notify(title, message, { 
            type: 'warning', 
            url: url,
            requireInteraction: true,
            vibrate: [200, 100, 200] 
        });
    }
    
    static notifyInfo(title, message, url) {
        return UASGPWAHelper.notify(title, message, { 
            type: 'info', 
            url: url,
            vibrate: [100] 
        });
    }
}

// Initialize PWA helper as early as possible for mobile
function initializeUASGPWA() {
    try {
        window.uasgPWA = new UASGPWAHelper();
        
        // For mobile devices, request permission sooner and more proactively
        const isMobile = window.innerWidth <= 768 || 'ontouchstart' in window;
        const delay = isMobile ? 1500 : 3000; // Shorter delay on mobile
        
        setTimeout(() => {
            try {
                // Only auto-request on mobile if user hasn't been asked before
                const hasAskedBefore = localStorage.getItem('uasg-notification-asked');
                if (isMobile && !hasAskedBefore) {
                    localStorage.setItem('uasg-notification-asked', 'true');
                    window.uasgPWA.requestNotificationPermission();
                } else if (!isMobile) {
                    // Desktop: just request quietly
                    window.uasgPWA.requestNotificationPermission();
                }
            } catch (error) {
                console.warn('UASG PWA: Could not request notification permission:', error);
            }
        }, delay);
        
        // Add mobile-specific event listeners
        if (isMobile) {
            // Listen for app becoming visible (user switching back to app)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && window.uasgPWA) {
                    // Clear any pending notifications when user returns to app
                    setTimeout(() => {
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.ready.then(registration => {
                                return registration.getNotifications();
                            }).then(notifications => {
                                notifications.forEach(notification => {
                                    if (notification.tag && notification.tag.includes('uasg')) {
                                        notification.close();
                                    }
                                });
                            }).catch(() => {
                                // Ignore errors
                            });
                        }
                    }, 1000);
                }
            });
            
            // Handle app install prompt on mobile
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                // Show install prompt immediately on mobile if not installed
                setTimeout(() => {
                    if (window.uasgPWA) {
                        window.uasgPWA.showInstallButton(e);
                    }
                }, 2000);
            });
        }
        
    } catch (error) {
        console.error('UASG PWA: Failed to initialize PWA helper:', error);
    }
}

// Initialize immediately if DOM is already loaded, otherwise wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeUASGPWA);
} else {
    initializeUASGPWA();
}

// Global error handler for PWA
window.addEventListener('error', (event) => {
    if (event.filename && event.filename.includes('pwa-helper.js')) {
        console.warn('UASG PWA: Non-critical PWA error:', event.error);
        event.preventDefault();
    }
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason && event.reason.message && event.reason.message.includes('PWA')) {
        console.warn('UASG PWA: Non-critical PWA promise rejection:', event.reason);
        event.preventDefault();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UASGPWAHelper;
}