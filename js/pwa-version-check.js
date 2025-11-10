/**
 * PWA Version Manager
 * Add this script to your main pages to automatically handle PWA updates
 * 
 * Usage: Include this file in your HTML:
 * <script src="js/pwa-version-check.js"></script>
 */

(function() {
    const CURRENT_APP_VERSION = '1.1.1'; // Updated to match service worker version
    const VERSION_KEY = 'uasg_app_version';
    
    // Check if version has changed
    function checkVersion() {
        const storedVersion = localStorage.getItem(VERSION_KEY);
        
        if (storedVersion && storedVersion !== CURRENT_APP_VERSION) {
            console.log(`UASG: Version change detected (${storedVersion} → ${CURRENT_APP_VERSION})`);
            
            // Show update available message
            if (window.uasgPWA && window.uasgPWA.showUpdateNotification) {
                window.uasgPWA.showUpdateNotification();
            }
            
            // Optionally auto-redirect to reset page after 3 seconds
            // setTimeout(() => {
            //     if (confirm('A new version is available. Update now?')) {
            //         window.location.href = '/uasg/pwa-reset.php';
            //     }
            // }, 3000);
        }
        
        // Store current version
        localStorage.setItem(VERSION_KEY, CURRENT_APP_VERSION);
    }
    
    // Check version on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkVersion);
    } else {
        checkVersion();
    }
    
    // Force service worker update check
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(registration => {
            // Check for updates every 30 seconds
            setInterval(() => {
                registration.update().then(() => {
                    console.log('UASG: Checked for service worker updates');
                });
            }, 30000);
            
            // Immediate check
            registration.update();
        });
    }
})();
