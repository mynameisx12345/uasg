<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove PWA & Clear Cache - UASG</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section h2 {
            color: #444;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: #e74c3c;
        }
        .btn-danger:hover {
            background: #c0392b;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }
        .btn-success {
            background: #27ae60;
        }
        .btn-success:hover {
            background: #229954;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
        }
        .status {
            padding: 12px;
            border-radius: 6px;
            margin: 10px 0;
            font-weight: 500;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .log {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }
        .log div {
            margin: 5px 0;
            padding: 3px 0;
            border-bottom: 1px solid #34495e;
        }
        .progress {
            width: 100%;
            height: 30px;
            background: #ecf0f1;
            border-radius: 15px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Remove PWA Components</h1>
        <p class="subtitle">This tool will remove all Progressive Web App components and clear cached data</p>

        <div class="section">
            <h2>Step 1: Unregister Service Workers</h2>
            <button class="btn btn-danger" onclick="unregisterServiceWorkers()">Unregister All Service Workers</button>
            <div id="sw-status"></div>
        </div>

        <div class="section">
            <h2>Step 2: Clear All Caches</h2>
            <button class="btn btn-danger" onclick="clearAllCaches()">Clear Browser Caches</button>
            <div id="cache-status"></div>
        </div>

        <div class="section">
            <h2>Step 3: Clear IndexedDB</h2>
            <button class="btn btn-danger" onclick="clearIndexedDB()">Clear IndexedDB</button>
            <div id="idb-status"></div>
        </div>

        <div class="section">
            <h2>Step 4: Clear Local & Session Storage</h2>
            <button class="btn btn-danger" onclick="clearStorage()">Clear All Storage</button>
            <div id="storage-status"></div>
        </div>

        <div class="section">
            <h2>Complete Cleanup</h2>
            <button class="btn btn-danger" onclick="completeCleanup()" style="font-size: 18px; padding: 15px 30px;">🔥 FULL CLEANUP (All Steps)</button>
            <div class="progress" style="display: none;" id="progress-container">
                <div class="progress-bar" id="progress-bar">0%</div>
            </div>
            <div id="cleanup-log" class="log" style="display: none;"></div>
        </div>

        <div class="section">
            <h2>After Cleanup</h2>
            <p style="margin-bottom: 15px;">Once cleanup is complete, reload the page to ensure all PWA components are removed:</p>
            <button class="btn btn-success" onclick="window.location.reload()">🔄 Reload Page</button>
            <button class="btn" onclick="window.location.href='index.php'">🏠 Go to Login</button>
        </div>
    </div>

    <script>
        function log(message, type = 'info') {
            const logContainer = document.getElementById('cleanup-log');
            logContainer.style.display = 'block';
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.textContent = `[${timestamp}] ${message}`;
            logContainer.appendChild(logEntry);
            logContainer.scrollTop = logContainer.scrollHeight;
            console.log(message);
        }

        function updateProgress(percent) {
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            progressContainer.style.display = 'block';
            progressBar.style.width = percent + '%';
            progressBar.textContent = Math.round(percent) + '%';
        }

        async function unregisterServiceWorkers() {
            const statusDiv = document.getElementById('sw-status');
            statusDiv.innerHTML = '<div class="status info">⏳ Unregistering service workers...</div>';
            
            try {
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    
                    if (registrations.length === 0) {
                        statusDiv.innerHTML = '<div class="status info">ℹ️ No service workers found</div>';
                        log('No service workers to unregister');
                        return;
                    }
                    
                    for (let registration of registrations) {
                        await registration.unregister();
                        log(`Unregistered service worker: ${registration.scope}`);
                    }
                    
                    statusDiv.innerHTML = `<div class="status success">✅ Successfully unregistered ${registrations.length} service worker(s)</div>`;
                    log(`Total service workers unregistered: ${registrations.length}`);
                } else {
                    statusDiv.innerHTML = '<div class="status info">ℹ️ Service workers not supported</div>';
                    log('Service workers not supported in this browser');
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
                log(`Error unregistering service workers: ${error.message}`, 'error');
            }
        }

        async function clearAllCaches() {
            const statusDiv = document.getElementById('cache-status');
            statusDiv.innerHTML = '<div class="status info">⏳ Clearing caches...</div>';
            
            try {
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    
                    if (cacheNames.length === 0) {
                        statusDiv.innerHTML = '<div class="status info">ℹ️ No caches found</div>';
                        log('No caches to delete');
                        return;
                    }
                    
                    for (let cacheName of cacheNames) {
                        await caches.delete(cacheName);
                        log(`Deleted cache: ${cacheName}`);
                    }
                    
                    statusDiv.innerHTML = `<div class="status success">✅ Successfully cleared ${cacheNames.length} cache(s)</div>`;
                    log(`Total caches cleared: ${cacheNames.length}`);
                } else {
                    statusDiv.innerHTML = '<div class="status info">ℹ️ Cache API not supported</div>';
                    log('Cache API not supported in this browser');
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
                log(`Error clearing caches: ${error.message}`, 'error');
            }
        }

        async function clearIndexedDB() {
            const statusDiv = document.getElementById('idb-status');
            statusDiv.innerHTML = '<div class="status info">⏳ Clearing IndexedDB...</div>';
            
            try {
                if ('indexedDB' in window) {
                    const databases = await indexedDB.databases();
                    
                    if (databases.length === 0) {
                        statusDiv.innerHTML = '<div class="status info">ℹ️ No IndexedDB databases found</div>';
                        log('No IndexedDB databases to delete');
                        return;
                    }
                    
                    for (let db of databases) {
                        indexedDB.deleteDatabase(db.name);
                        log(`Deleted IndexedDB: ${db.name}`);
                    }
                    
                    statusDiv.innerHTML = `<div class="status success">✅ Successfully cleared ${databases.length} IndexedDB database(s)</div>`;
                    log(`Total IndexedDB databases cleared: ${databases.length}`);
                } else {
                    statusDiv.innerHTML = '<div class="status info">ℹ️ IndexedDB not supported</div>';
                    log('IndexedDB not supported in this browser');
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
                log(`Error clearing IndexedDB: ${error.message}`, 'error');
            }
        }

        function clearStorage() {
            const statusDiv = document.getElementById('storage-status');
            statusDiv.innerHTML = '<div class="status info">⏳ Clearing storage...</div>';
            
            try {
                const localCount = localStorage.length;
                const sessionCount = sessionStorage.length;
                
                localStorage.clear();
                log('Cleared localStorage');
                sessionStorage.clear();
                log('Cleared sessionStorage');
                
                statusDiv.innerHTML = `<div class="status success">✅ Cleared localStorage (${localCount} items) and sessionStorage (${sessionCount} items)</div>`;
                log(`Total storage cleared: localStorage=${localCount}, sessionStorage=${sessionCount}`);
            } catch (error) {
                statusDiv.innerHTML = `<div class="status error">❌ Error: ${error.message}</div>`;
                log(`Error clearing storage: ${error.message}`, 'error');
            }
        }

        async function completeCleanup() {
            log('========================================');
            log('Starting complete PWA cleanup...');
            log('========================================');
            
            updateProgress(0);
            
            // Step 1
            log('Step 1/4: Unregistering service workers...');
            await unregisterServiceWorkers();
            updateProgress(25);
            
            // Step 2
            log('Step 2/4: Clearing caches...');
            await clearAllCaches();
            updateProgress(50);
            
            // Step 3
            log('Step 3/4: Clearing IndexedDB...');
            await clearIndexedDB();
            updateProgress(75);
            
            // Step 4
            log('Step 4/4: Clearing storage...');
            clearStorage();
            updateProgress(100);
            
            log('========================================');
            log('✅ Complete cleanup finished!');
            log('Please reload the page to ensure all components are removed.');
            log('========================================');
            
            // Show reload button prominently
            setTimeout(() => {
                if (confirm('Cleanup complete! Reload the page now?')) {
                    window.location.reload();
                }
            }, 1000);
        }

        // Auto-run on page load
        window.addEventListener('load', () => {
            log('PWA Cleanup Tool Ready');
            log('Click "FULL CLEANUP" button to remove all PWA components');
        });
    </script>
</body>
</html>
