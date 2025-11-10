# PWA Update & Cache Management Guide

## Problem: Service Worker Caching Old Content

When you update your landing page (index.php) or any other files, users may still see the old version because the service worker has cached the previous content.

## ✅ Solution Implemented

### 1. Automatic Cache Version Update

**File: `sw.js`**
```javascript
const CACHE_NAME = 'uasg-pwa-v1.1.0'; // Updated from v1.0.0
```

**What happens:**
- When you change this version number, the service worker will automatically:
  - Install the new service worker
  - Delete old caches
  - Cache fresh content
  - Notify users about the update

### 2. Automatic Update Detection

**File: `js/pwa-helper.js`**

Enhanced features:
- ✅ Checks for updates immediately on page load
- ✅ Checks for updates every 60 seconds
- ✅ Shows prominent update banner when new version is available
- ✅ Auto-updates after 10 seconds if user doesn't respond
- ✅ Smooth transition without data loss

### 3. Manual Update Page

**File: `pwa-reset.php`**

Visit this page to force an immediate update:
```
http://localhost/uasg/pwa-reset.php
```

Features:
- 5-second countdown with auto-update
- Clears all service workers
- Deletes all caches
- Clears localStorage and sessionStorage
- Reloads with fresh content

### 4. Version Tracking

**File: `js/pwa-version-check.js`**

Optional script to track app versions and notify users of updates.

## 🚀 How to Use

### When You Update Your Code:

#### Option 1: Automatic (Recommended)
1. **Update the cache version** in `sw.js`:
   ```javascript
   const CACHE_NAME = 'uasg-pwa-v1.2.0'; // Increment version
   ```

2. **Users will automatically see update banner** within 60 seconds

3. **Banner auto-updates after 10 seconds** or user can click "Update Now"

#### Option 2: Force Immediate Reset
1. Direct users to visit: `http://localhost/uasg/pwa-reset.php`
2. Page automatically clears everything and reloads in 5 seconds

#### Option 3: Manual Browser Reset
1. Press `Ctrl + Shift + Delete`
2. Clear browsing data
3. Reload page with `Ctrl + F5`

## 📋 Update Checklist

When you make changes to your app:

- [ ] Update `CACHE_NAME` in `sw.js` (increment version)
- [ ] Update `CURRENT_APP_VERSION` in `js/pwa-version-check.js` (if using)
- [ ] Test on your browser first
- [ ] Inform users if major changes (via announcement or redirect to pwa-reset.php)

## 🔧 Version Number Format

Use semantic versioning: `vMAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes (e.g., database schema changes)
- **MINOR**: New features (e.g., new pages, new functionality)
- **PATCH**: Bug fixes (e.g., UI tweaks, small fixes)

Examples:
- `v1.0.0` → Initial release
- `v1.1.0` → Added file upload feature (current)
- `v1.1.1` → Fixed file upload bug
- `v2.0.0` → Major redesign

## 🎯 Current Version

**Service Worker Cache**: `uasg-pwa-v1.1.0`
**App Version**: `1.1.0`

**Changes in v1.1.0:**
- Fixed PWA start_url to explicitly use index.php
- Added automatic update detection
- Implemented update banner with auto-apply
- Created pwa-reset.php for manual updates
- Enhanced service worker update mechanism

## 📊 How Updates Work

### Update Flow:
```
1. You change CACHE_NAME in sw.js
   ↓
2. Browser detects new service worker
   ↓
3. New service worker installs in background
   ↓
4. pwa-helper.js detects the new worker
   ↓
5. Update banner appears
   ↓
6. After 10 seconds (or user clicks "Update Now"):
   - Old service worker skips waiting
   - New service worker activates
   - Old caches deleted
   - Page reloads
   ↓
7. User sees fresh content
```

### Update Timeline:
- **Immediate**: Users visiting after cache update see new version
- **Active users**: See update banner within 60 seconds
- **Auto-update**: Applies automatically after 10 seconds
- **Manual**: Can click "Update Now" or visit pwa-reset.php

## 🛡️ Safety Features

1. **No Data Loss**: Service worker updates don't affect user sessions or database data
2. **Graceful Degradation**: If service worker fails, app still works (fetches from server)
3. **User Control**: Users can choose to update now or later
4. **Fallback**: Manual reset page always available

## 🧪 Testing Updates

### Test in Development:

1. Make a change to index.php
2. Update cache version in sw.js
3. Open DevTools → Application → Service Workers
4. Click "Update" to force check
5. Should see new service worker installing
6. Update banner should appear
7. Click "Update Now" or wait 10 seconds
8. Page reloads with new content

### Test in Production:

1. Update cache version
2. Deploy changes
3. Visit site in incognito window (simulates new user)
4. Should see latest version immediately
5. Visit in regular window (simulates existing user)
6. Should see update banner within 60 seconds

## 🔍 Troubleshooting

### Issue: Update banner doesn't appear
**Solution:**
- Check browser console for errors
- Verify CACHE_NAME was changed in sw.js
- Force update: DevTools → Application → Service Workers → Update
- Visit pwa-reset.php to force complete reset

### Issue: Still seeing old content after update
**Solution:**
- Hard reload: `Ctrl + Shift + R`
- Clear browser cache: `Ctrl + Shift + Delete`
- Visit pwa-reset.php
- Check if service worker is actually updated (DevTools → Application)

### Issue: Service worker won't update
**Solution:**
- Unregister manually: DevTools → Application → Service Workers → Unregister
- Clear site data: DevTools → Application → Storage → Clear site data
- Reload page
- Service worker will re-register with new version

### Issue: Update loop (keeps showing update banner)
**Solution:**
- Verify CACHE_NAME matches between old and new service worker
- Check for console errors
- Clear all caches and start fresh with pwa-reset.php

## 📝 Best Practices

1. **Always increment version** when making changes
2. **Test locally first** before deploying
3. **Use pwa-reset.php** for major updates
4. **Communicate with users** about major version changes
5. **Monitor update success** via browser console logs
6. **Keep version numbers consistent** across sw.js and version-check.js
7. **Document changes** in each version

## 🎓 For Developers

### Adding Version Check to New Pages:

```html
<!-- Add to <head> or before </body> -->
<script src="js/pwa-helper.js"></script>
<script src="js/pwa-version-check.js"></script>
```

### Programmatic Update Trigger:

```javascript
// Trigger update manually
if (window.uasgPWA) {
    window.uasgPWA.applyUpdate();
}
```

### Check Current Service Worker Version:

```javascript
navigator.serviceWorker.ready.then(registration => {
    console.log('Service Worker Scope:', registration.scope);
});

// Check cache names
caches.keys().then(names => {
    console.log('Active Caches:', names);
});
```

## 📞 Support

If you encounter issues with PWA updates:
1. Check browser console for error messages
2. Verify service worker status in DevTools
3. Use pwa-reset.php as last resort
4. Contact system administrator if problems persist

---

**Last Updated**: November 10, 2025
**Current Version**: v1.1.0
