<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Update & Reset - UASG</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 100%;
            padding: 15px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 15px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #1976D2;
        }
        .button.danger {
            background: #f44336;
        }
        .button.danger:hover {
            background: #d32f2f;
        }
        .button.success {
            background: #4CAF50;
        }
        .button.success:hover {
            background: #45a049;
        }
        .status {
            background: #f5f5f5;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 300px;
            overflow-y: auto;
        }
        .status p {
            margin: 5px 0;
            color: #333;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .warning p {
            color: #856404;
            margin: 0;
        }
        .step {
            background: #e3f2fd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #2196F3;
        }
        .step h3 {
            color: #1976D2;
            margin-bottom: 8px;
            font-size: 16px;
        }
        .step p {
            color: #333;
            margin: 0;
            font-size: 14px;
        }
        .auto-update {
            background: #e8f5e9;
            border-left-color: #4CAF50;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid #4CAF50;
        }
        .auto-update h2 {
            color: #2e7d32;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .countdown {
            font-size: 48px;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 PWA Update & Reset Tool</h1>
        <p>This page will automatically clear all caches and reload the latest version of UASG.</p>
        
        <div class="auto-update">
            <h2>✨ Automatic Update</h2>
            <p>The page will automatically update in:</p>
            <div class="countdown" id="countdown">5</div>
            <button class="button success" onclick="updateNow()">Update Immediately</button>
        </div>

        <div class="status" id="status">
            <p>Preparing to update...</p>
        </div>

        <button class="button danger" onclick="manualReset()">Force Complete Reset</button>
        <button class="button" onclick="cancelUpdate()">Cancel & Go Back</button>

        <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
            <h3 style="margin-bottom: 15px; color: #333;">What happens during update:</h3>
            
            <div class="step">
                <h3>Step 1: Clear Service Worker</h3>
                <p>Unregister old service worker and clear all caches</p>
            </div>

            <div class="step">
                <h3>Step 2: Clear Storage</h3>
                <p>Remove localStorage and sessionStorage data</p>
            </div>

            <div class="step">
                <h3>Step 3: Reload Page</h3>
                <p>Load fresh content from server</p>
            </div>

            <div class="step">
                <h3>Step 4: Register New SW</h3>
                <p>Install updated service worker with new cache version</p>
            </div>
        </div>
    </div>

    <script>
        let countdownValue = 5;
        let countdownInterval;
        let updateCancelled = false;

        function log(message, isError = false) {
            const status = document.getElementById('status');
            const p = document.createElement('p');
            p.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            if (isError) p.style.color = '#f44336';
            else p.style.color = '#4CAF50';
            status.appendChild(p);
            status.scrollTop = status.scrollHeight;
        }

        function startCountdown() {
            const countdownEl = document.getElementById('countdown');
            
            countdownInterval = setInterval(() => {
                if (updateCancelled) {
                    clearInterval(countdownInterval);
                    return;
                }
                
                countdownValue--;
                countdownEl.textContent = countdownValue;
                
                if (countdownValue <= 0) {
                    clearInterval(countdownInterval);
                    updateNow();
                }
            }, 1000);
        }

        async function updateNow() {
            updateCancelled = true;
            clearInterval(countdownInterval);
            
            log('Starting update process...');
            
            try {
                // Step 1: Unregister service workers
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    log(`Found ${registrations.length} service worker(s)`);
                    
                    for (let registration of registrations) {
                        await registration.unregister();
                        log(`✓ Unregistered service worker: ${registration.scope}`);
                    }
                }

                // Step 2: Clear all caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    log(`Found ${cacheNames.length} cache(s)`);
                    
                    for (let cacheName of cacheNames) {
                        await caches.delete(cacheName);
                        log(`✓ Deleted cache: ${cacheName}`);
                    }
                }

                // Step 3: Clear storage
                try {
                    localStorage.clear();
                    log('✓ Cleared localStorage');
                } catch (e) {
                    log('⚠ Could not clear localStorage', true);
                }

                try {
                    sessionStorage.clear();
                    log('✓ Cleared sessionStorage');
                } catch (e) {
                    log('⚠ Could not clear sessionStorage', true);
                }

                log('✅ Update complete! Reloading page...');
                
                // Step 4: Reload page
                setTimeout(() => {
                    window.location.href = '/uasg/index.php?' + new Date().getTime();
                }, 1000);

            } catch (error) {
                log('❌ Error during update: ' + error.message, true);
                console.error(error);
            }
        }

        async function manualReset() {
            if (!confirm('This will perform a complete reset and reload. Continue?')) {
                return;
            }
            
            updateCancelled = true;
            clearInterval(countdownInterval);
            await updateNow();
        }

        function cancelUpdate() {
            updateCancelled = true;
            clearInterval(countdownInterval);
            log('Update cancelled');
            window.history.back();
        }

        // Start countdown on load
        window.addEventListener('load', () => {
            log('Page loaded, starting countdown...');
            startCountdown();
        });
    </script>
</body>
</html>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 100%;
            padding: 15px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 15px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #1976D2;
        }
        .button.danger {
            background: #f44336;
        }
        .button.danger:hover {
            background: #d32f2f;
        }
        .button.success {
            background: #4CAF50;
        }
        .button.success:hover {
            background: #45a049;
        }
        .status {
            background: #f5f5f5;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 300px;
            overflow-y: auto;
        }
        .status p {
            margin: 5px 0;
            color: #333;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .warning p {
            color: #856404;
            margin: 0;
        }
        .step {
            background: #e3f2fd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #2196F3;
        }
        .step h3 {
            color: #1976D2;
            margin-bottom: 8px;
            font-size: 16px;
        }
        .step p {
            color: #333;
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 PWA Reset Tool</h1>
        <p>Use this tool to reset the PWA and fix the landing page redirect issue.</p>
        
        <div class="warning">
            <p><strong>⚠️ Important:</strong> This will clear all cached data and unregister the service worker. You'll need to reinstall the PWA after this.</p>
        </div>

        <div class="status" id="status">
            <p>Ready to reset PWA...</p>
        </div>

        <button class="button danger" onclick="resetPWA()">1. Clear All Caches & Unregister SW</button>
        <button class="button" onclick="checkStatus()">2. Check Current Status</button>
        <button class="button success" onclick="reloadApp()">3. Reload App (index.php)</button>

        <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
            <h3 style="margin-bottom: 15px; color: #333;">Manual Reset Steps:</h3>
            
            <div class="step">
                <h3>Step 1: Clear Service Worker</h3>
                <p>Click "Clear All Caches & Unregister SW" button above</p>
            </div>

            <div class="step">
                <h3>Step 2: Clear Browser Cache</h3>
                <p>Press Ctrl+Shift+Delete and clear browsing data (last hour)</p>
            </div>

            <div class="step">
                <h3>Step 3: Uninstall PWA</h3>
                <p>If you have the app installed, uninstall it from your device</p>
            </div>

            <div class="step">
                <h3>Step 4: Reload</h3>
                <p>Click "Reload App" button to load index.php with new manifest</p>
            </div>

            <div class="step">
                <h3>Step 5: Reinstall PWA</h3>
                <p>Install the PWA again - it will now use index.php as the start page</p>
            </div>
        </div>
    </div>

    <script>
        function log(message, isError = false) {
            const status = document.getElementById('status');
            const p = document.createElement('p');
            p.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            if (isError) p.style.color = '#f44336';
            else p.style.color = '#4CAF50';
            status.appendChild(p);
            status.scrollTop = status.scrollHeight;
        }

        async function resetPWA() {
            try {
                log('Starting PWA reset...');

                // Unregister all service workers
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    log(`Found ${registrations.length} service worker(s)`);
                    
                    for (let registration of registrations) {
                        await registration.unregister();
                        log(`Unregistered service worker: ${registration.scope}`);
                    }
                } else {
                    log('Service workers not supported', true);
                }

                // Clear all caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    log(`Found ${cacheNames.length} cache(s)`);
                    
                    for (let cacheName of cacheNames) {
                        await caches.delete(cacheName);
                        log(`Deleted cache: ${cacheName}`);
                    }
                } else {
                    log('Cache API not supported', true);
                }

                // Clear local storage
                try {
                    localStorage.clear();
                    log('Cleared localStorage');
                } catch (e) {
                    log('Could not clear localStorage: ' + e.message, true);
                }

                // Clear session storage
                try {
                    sessionStorage.clear();
                    log('Cleared sessionStorage');
                } catch (e) {
                    log('Could not clear sessionStorage: ' + e.message, true);
                }

                log('✅ PWA reset complete!');
                log('⚠️ Now: 1) Clear browser cache (Ctrl+Shift+Delete), 2) Uninstall PWA if installed, 3) Click "Reload App"');

            } catch (error) {
                log('Error during reset: ' + error.message, true);
                console.error(error);
            }
        }

        async function checkStatus() {
            try {
                log('Checking PWA status...');

                // Check service workers
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    if (registrations.length === 0) {
                        log('✅ No service workers registered');
                    } else {
                        log(`⚠️ Found ${registrations.length} service worker(s):`, true);
                        registrations.forEach(reg => {
                            log(`  - ${reg.scope}`, true);
                        });
                    }
                }

                // Check caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    if (cacheNames.length === 0) {
                        log('✅ No caches found');
                    } else {
                        log(`⚠️ Found ${cacheNames.length} cache(s):`, true);
                        cacheNames.forEach(name => {
                            log(`  - ${name}`, true);
                        });
                    }
                }

                // Check storage
                const lsItems = localStorage.length;
                const ssItems = sessionStorage.length;
                if (lsItems === 0 && ssItems === 0) {
                    log('✅ Storage is clear');
                } else {
                    log(`⚠️ localStorage: ${lsItems} items, sessionStorage: ${ssItems} items`, true);
                }

                log('Status check complete');

            } catch (error) {
                log('Error checking status: ' + error.message, true);
                console.error(error);
            }
        }

        function reloadApp() {
            log('Redirecting to index.php...');
            setTimeout(() => {
                window.location.href = '/uasg/index.php';
            }, 500);
        }

        // Auto-check status on load
        window.addEventListener('load', () => {
            setTimeout(checkStatus, 500);
        });
    </script>
</body>
</html>
