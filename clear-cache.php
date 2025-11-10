<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Cache & Unregister PWA</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .status {
            background: #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
        }
        .status div {
            margin: 10px 0;
            padding: 8px;
            border-left: 3px solid #667eea;
            background: white;
        }
        .success { border-left-color: #28a745 !important; color: #28a745; }
        .error { border-left-color: #dc3545 !important; color: #dc3545; }
        .info { border-left-color: #17a2b8 !important; color: #17a2b8; }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .countdown {
            font-size: 48px;
            color: #667eea;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Clear All Cache & Unregister PWA</h1>
        <p>This will completely remove the PWA service worker and clear all cached files.</p>
        
        <div id="countdown" class="countdown" style="display: none;">3</div>
        
        <div class="status" id="status">
            <div class="info">⏳ Click the button below to start cleanup...</div>
        </div>
        
        <button class="btn" id="clearBtn" onclick="clearEverything()">Clear Cache & Unregister PWA</button>
        <button class="btn" onclick="window.location.href='index.php'">Go to Login</button>
    </div>

    <script>
        let logs = [];
        
        function log(message, type = 'info') {
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            logs.push(`<div class="${type}">${icon} ${message}</div>`);
            document.getElementById('status').innerHTML = logs.join('');
            // Auto-scroll to bottom
            const statusDiv = document.getElementById('status');
            statusDiv.scrollTop = statusDiv.scrollHeight;
        }

        async function clearEverything() {
            logs = [];
            document.getElementById('clearBtn').disabled = true;
            document.getElementById('clearBtn').textContent = 'Clearing...';
            
            log('🚀 Starting cleanup process...', 'info');
            
            // 1. Unregister all service workers
            if ('serviceWorker' in navigator) {
                try {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    log(`Found ${registrations.length} service worker(s)`, 'info');
                    
                    for (let registration of registrations) {
                        await registration.unregister();
                        log('Service worker unregistered successfully', 'success');
                    }
                } catch (error) {
                    log('Error unregistering service workers: ' + error.message, 'error');
                }
            } else {
                log('Service workers not supported in this browser', 'info');
            }
            
            // 2. Clear all caches
            if ('caches' in window) {
                try {
                    const cacheNames = await caches.keys();
                    log(`Found ${cacheNames.length} cache(s): ${cacheNames.join(', ')}`, 'info');
                    
                    for (let cacheName of cacheNames) {
                        await caches.delete(cacheName);
                        log(`Deleted cache: ${cacheName}`, 'success');
                    }
                } catch (error) {
                    log('Error clearing caches: ' + error.message, 'error');
                }
            } else {
                log('Cache API not supported in this browser', 'info');
            }
            
            // 3. Clear localStorage
            try {
                const localStorageCount = localStorage.length;
                localStorage.clear();
                log(`Cleared localStorage (${localStorageCount} items)`, 'success');
            } catch (error) {
                log('Error clearing localStorage: ' + error.message, 'error');
            }
            
            // 4. Clear sessionStorage
            try {
                const sessionStorageCount = sessionStorage.length;
                sessionStorage.clear();
                log(`Cleared sessionStorage (${sessionStorageCount} items)`, 'success');
            } catch (error) {
                log('Error clearing sessionStorage: ' + error.message, 'error');
            }
            
            // 5. Clear IndexedDB
            if ('indexedDB' in window) {
                try {
                    const databases = await indexedDB.databases();
                    for (let db of databases) {
                        indexedDB.deleteDatabase(db.name);
                        log(`Deleted IndexedDB: ${db.name}`, 'success');
                    }
                } catch (error) {
                    log('IndexedDB cleanup: ' + error.message, 'info');
                }
            }
            
            log('✨ Cleanup complete!', 'success');
            log('🔄 Page will reload in 3 seconds...', 'info');
            
            // Show countdown
            document.getElementById('countdown').style.display = 'block';
            let count = 3;
            const countdownInterval = setInterval(() => {
                count--;
                document.getElementById('countdown').textContent = count;
                if (count === 0) {
                    clearInterval(countdownInterval);
                    // Force hard reload with cache bypass
                    window.location.href = 'index.php?nocache=' + Date.now();
                    setTimeout(() => {
                        window.location.reload(true);
                    }, 100);
                }
            }, 1000);
        }

        // Auto-clear on page load (optional)
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('auto')) {
                setTimeout(() => clearEverything(), 500);
            }
        });
    </script>
</body>
</html>
