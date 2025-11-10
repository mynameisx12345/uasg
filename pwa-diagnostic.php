<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Cache Diagnostic - UASG</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .status.success { background: #e8f5e9; color: #2e7d32; }
        .status.error { background: #ffebee; color: #c62828; }
        .status.warning { background: #fff3e0; color: #ef6c00; }
        .file-list {
            margin: 20px 0;
        }
        .file-item {
            padding: 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .file-item:last-child { border-bottom: none; }
        .file-path {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #555;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        .section h2 {
            color: #444;
            margin-bottom: 15px;
            font-size: 18px;
        }
        button {
            background: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        button:hover { background: #1976D2; }
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #2196F3;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PWA Cache Diagnostic Tool</h1>
        <p style="color: #666; margin-bottom: 20px;">Check which files are cached and verify accessibility</p>

        <div style="margin-bottom: 20px;">
            <button onclick="checkAllFiles()">🔄 Check All Files</button>
            <button onclick="checkServiceWorker()">⚙️ Check Service Worker</button>
            <button onclick="clearAllCaches()">🗑️ Clear All Caches</button>
            <button onclick="window.location.href='pwa-reset.php'">🔁 Full Reset</button>
        </div>

        <div class="section">
            <h2>Service Worker Status</h2>
            <div id="swStatus">Checking...</div>
        </div>

        <div class="section">
            <h2>Essential Files (Required for offline)</h2>
            <div class="file-list" id="essentialFiles">Loading...</div>
        </div>

        <div class="section">
            <h2>Optional Files (Nice to have)</h2>
            <div class="file-list" id="optionalFiles">Loading...</div>
        </div>

        <div class="section">
            <h2>Cached Files (Currently in cache)</h2>
            <div class="file-list" id="cachedFiles">Loading...</div>
        </div>
    </div>

    <script>
        const ESSENTIAL_FILES = [
            '/uasg/index.php',
            '/uasg/offline.html',
            '/uasg/resources/style.css',
            '/uasg/js/jquery.js',
            '/uasg/js/all.js',
            '/uasg/js/pwa-helper.js'
        ];

        const OPTIONAL_FILES = [
            '/uasg/resources/datatable.css',
            '/uasg/js/datatable.js',
            '/uasg/resources/icons/icon-192x192.png',
            '/uasg/resources/icons/icon-512x512.png',
            '/uasg/resources/icons/icon-72x72.png',
            '/uasg/resources/icons/icon-96x96.png',
            '/uasg/resources/icons/icon-128x128.png',
            '/uasg/resources/icons/icon-144x144.png',
            '/uasg/resources/icons/icon-152x152.png',
            '/uasg/resources/icons/icon-384x384.png'
        ];

        async function checkFile(url) {
            try {
                const response = await fetch(url, { method: 'HEAD' });
                return response.ok;
            } catch (error) {
                return false;
            }
        }

        async function displayFileList(files, containerId, isRequired = false) {
            const container = document.getElementById(containerId);
            container.innerHTML = '<div class="loading"></div> Checking files...';

            const results = await Promise.all(
                files.map(async (file) => {
                    const exists = await checkFile(file);
                    return { file, exists };
                })
            );

            let html = '';
            let successCount = 0;
            let failCount = 0;

            results.forEach(({ file, exists }) => {
                if (exists) successCount++;
                else failCount++;

                const statusClass = exists ? 'success' : 'error';
                const statusText = exists ? '✓ Accessible' : '✗ Not Found';
                
                html += `
                    <div class="file-item">
                        <span class="file-path">${file}</span>
                        <span class="status ${statusClass}">${statusText}</span>
                    </div>
                `;
            });

            const summary = isRequired 
                ? `<p style="margin-bottom: 10px;"><strong>${successCount}/${files.length}</strong> essential files accessible. ${failCount > 0 ? '<span style="color: #c62828;">⚠️ Missing files will prevent offline functionality!</span>' : '✓ All essential files found!'}</p>`
                : `<p style="margin-bottom: 10px;"><strong>${successCount}/${files.length}</strong> optional files accessible.</p>`;

            container.innerHTML = summary + html;
        }

        async function checkServiceWorker() {
            const container = document.getElementById('swStatus');
            
            if (!('serviceWorker' in navigator)) {
                container.innerHTML = '<span class="status error">Not Supported</span> Service workers are not supported in this browser.';
                return;
            }

            const registrations = await navigator.serviceWorker.getRegistrations();
            
            if (registrations.length === 0) {
                container.innerHTML = '<span class="status warning">Not Registered</span> No service worker is currently registered.';
                return;
            }

            let html = '';
            registrations.forEach((reg, index) => {
                const state = reg.active ? 'active' : reg.installing ? 'installing' : reg.waiting ? 'waiting' : 'unknown';
                const statusClass = reg.active ? 'success' : 'warning';
                
                html += `
                    <div style="margin-bottom: 10px;">
                        <strong>Service Worker ${index + 1}:</strong>
                        <span class="status ${statusClass}">${state.toUpperCase()}</span>
                        <br>
                        <span style="font-size: 12px; color: #666;">Scope: ${reg.scope}</span>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        async function checkCachedFiles() {
            const container = document.getElementById('cachedFiles');
            
            if (!('caches' in window)) {
                container.innerHTML = 'Cache API not supported';
                return;
            }

            const cacheNames = await caches.keys();
            
            if (cacheNames.length === 0) {
                container.innerHTML = '<p style="color: #666;">No caches found</p>';
                return;
            }

            let html = `<p style="margin-bottom: 10px;"><strong>${cacheNames.length}</strong> cache(s) found</p>`;
            
            for (const cacheName of cacheNames) {
                const cache = await caches.open(cacheName);
                const keys = await cache.keys();
                
                html += `
                    <div style="margin: 15px 0; padding: 10px; background: white; border-radius: 4px;">
                        <strong>${cacheName}</strong> 
                        <span class="status success">${keys.length} files</span>
                        <div style="margin-top: 10px; font-size: 12px; color: #666;">
                            ${keys.slice(0, 5).map(req => `<div>• ${req.url}</div>`).join('')}
                            ${keys.length > 5 ? `<div style="margin-top: 5px;"><em>...and ${keys.length - 5} more</em></div>` : ''}
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;
        }

        async function checkAllFiles() {
            await Promise.all([
                displayFileList(ESSENTIAL_FILES, 'essentialFiles', true),
                displayFileList(OPTIONAL_FILES, 'optionalFiles', false),
                checkServiceWorker(),
                checkCachedFiles()
            ]);
        }

        async function clearAllCaches() {
            if (!confirm('This will clear all caches. Continue?')) return;

            const cacheNames = await caches.keys();
            await Promise.all(cacheNames.map(name => caches.delete(name)));
            
            alert(`Cleared ${cacheNames.length} cache(s). Reload the page to re-cache.`);
            checkAllFiles();
        }

        // Auto-run on load
        window.addEventListener('load', checkAllFiles);
    </script>
</body>
</html>
