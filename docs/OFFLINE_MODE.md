# ACADEX Offline Mode Documentation

## Overview

ACADEX has been fully converted to work **100% offline** without requiring any internet connection. All external CDN dependencies have been replaced with local assets bundled via Vite.

## What Changed

### ✅ Replaced External CDN Resources

All previously external resources are now bundled locally:

| Resource | Previous Source | Current Source |
|----------|----------------|----------------|
| **Bootstrap 5.3.8** | cdn.jsdelivr.net | npm package (bundled) |
| **Bootstrap Icons** | cdn.jsdelivr.net | npm package (bundled) |
| **Font Awesome 7.1.0** | cdnjs.cloudflare.com | npm package (bundled) |
| **jQuery 3.7.1** | code.jquery.com | npm package (bundled) |
| **DataTables 1.13.6** | cdn.datatables.net | npm package (bundled) |
| **SweetAlert2** | cdn.jsdelivr.net | npm package (bundled) |
| **Chart.js** | cdn.jsdelivr.net | npm package (bundled) |
| **Bootbox** | cdn.jsdelivr.net | public/js/vendor/bootbox.min.js |
| **Google Fonts (Inter, Poppins)** | fonts.googleapis.com | resources/css/fonts.css |
| **CDNFonts (Feeling Passionate)** | fonts.cdnfonts.com | resources/css/fonts.css |

### ✅ Local Avatar Generation

Replaced `ui-avatars.com` API with local SVG generation:
- **Class**: `App\Support\AvatarGenerator`
- **Helper Function**: `avatar($name, $background, $color, $size)`
- **Usage**: `{{ avatar('John Doe', '259c59', 'fff', 128) }}`

## File Changes

### Modified Files

1. **resources/views/layouts/app.blade.php**
   - Removed all CDN links
   - Loads all assets via Vite bundle

2. **resources/views/layouts/guest.blade.php**
   - Removed Google Fonts CDN
   - Removed CDNFonts link
   - Removed Bootstrap CDN

3. **resources/views/layouts/navigation.blade.php**
   - Replaced ui-avatars.com with local `avatar()` helper

4. **resources/views/admin/users.blade.php**
   - Removed Font Awesome CDN
   - Removed SweetAlert2 CDN

5. **resources/views/admin/sessions.blade.php**
   - Removed Font Awesome CDN

6. **resources/js/app.js**
   - Added all library imports
   - Exports global variables (Swal, Chart, jQuery)

### New Files

1. **app/Support/AvatarGenerator.php**
   - Local SVG avatar generator

2. **app/Support/helpers.php**
   - Global helper functions (avatar)

3. **resources/css/fonts.css**
   - Font-face declarations for local fonts

4. **public/js/vendor/bootbox.min.js**
   - Bootbox library (compatibility workaround)

5. **scripts/setup-offline-resources.sh**
   - Setup script for Unix/Linux/macOS

6. **scripts/setup-offline-resources.ps1**
   - Setup script for Windows

## Font Files Required

The following fonts need to be downloaded and placed in `/public/fonts/`:

### Inter Font (Main Application Font)
**Download from**: https://fonts.google.com/specimen/Inter

Place in: `/public/fonts/inter/`
- `Inter-Light.woff2` (300)
- `Inter-Regular.woff2` (400)
- `Inter-Medium.woff2` (500)
- `Inter-SemiBold.woff2` (600)
- `Inter-Bold.woff2` (700)
- `.woff` versions (fallback)

### Poppins Font (Guest Pages)
**Download from**: https://fonts.google.com/specimen/Poppins

Place in: `/public/fonts/poppins/`
- `Poppins-Bold.woff2` (700)
- `Poppins-Bold.woff` (fallback)

### Feeling Passionate (Decorative)
**Download from**: https://www.cdnfonts.com/feeling-passionate.font

Place in: `/public/fonts/feeling-passionate/`
- `FeelingPassionate.woff2`
- `FeelingPassionate.woff` (fallback)

### Alternative: Google Webfonts Helper
Visit https://gwfh.mranftl.com/fonts to download optimized woff2 files.

## Setup Instructions

### Automated Setup

**Unix/Linux/macOS:**
```bash
./scripts/setup-offline-resources.sh
```

**Windows:**
```powershell
.\scripts\setup-offline-resources.ps1
```

### Manual Setup

1. **Install npm packages** (already done):
   ```bash
   npm install
   ```

2. **Download fonts** (see Font Files Required section above)

3. **Build assets**:
   ```bash
   npm run build
   ```

4. **Clear caches**:
   ```bash
   composer dump-autoload
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

## Testing Offline Functionality

1. **Disconnect from internet** or use browser developer tools to simulate offline mode

2. **Test the following**:
   - [ ] Login page loads with proper styling
   - [ ] Dashboard loads without errors
   - [ ] All icons display correctly
   - [ ] Fonts render properly
   - [ ] User avatars show initials
   - [ ] DataTables work
   - [ ] Modals (SweetAlert2, Bootbox) function
   - [ ] Charts display

3. **Check browser console** for no 404 errors or missing resources

## NPM Packages Added

```json
{
  "jquery": "^3.7.1",
  "datatables.net-bs5": "^1.13.6",
  "chart.js": "^4.4.1",
  "sweetalert2": "^11.10.5",
  "bootbox.js": "^6.0.0"
}
```

## Build Output

After running `npm run build`, you'll see:
- `public/build/assets/app-*.js` (~869 KB, gzipped ~268 KB)
- `public/build/assets/app-*.css` (~430 KB, gzipped ~74 KB)
- Font files in `public/build/assets/`
- Manifest file: `public/build/manifest.json`

## Performance Considerations

### Bundle Size
- **JavaScript**: ~869 KB (uncompressed), ~268 KB (gzipped)
- **CSS**: ~430 KB (uncompressed), ~74 KB (gzipped)

### Optimization Opportunities
1. **Code Splitting**: Use dynamic imports for rarely-used components
2. **Lazy Loading**: Load Chart.js only on pages that need it
3. **Tree Shaking**: Remove unused Bootstrap components
4. **CDN Option**: For production with internet, consider hybrid approach

## Troubleshooting

### Fonts Not Loading
**Symptom**: Text displays in fallback fonts
**Solution**: 
1. Verify font files exist in `/public/fonts/`
2. Check browser console for 404 errors
3. Run `npm run build` again
4. Clear browser cache

### JavaScript Errors
**Symptom**: Console errors about undefined variables
**Solution**:
1. Verify `npm run build` completed successfully
2. Check that `public/build/manifest.json` exists
3. Run `php artisan view:clear`
4. Hard refresh browser (Ctrl+Shift+R)

### Bootbox Not Working
**Symptom**: `bootbox is not defined` error
**Solution**:
1. Verify `public/js/vendor/bootbox.min.js` exists
2. Check that script tag is in `layouts/app.blade.php`
3. Ensure jQuery loads before Bootbox

### Avatar Images Not Showing
**Symptom**: Broken image icons
**Solution**:
1. Verify `composer dump-autoload` was run
2. Check that `app/Support/helpers.php` exists
3. Verify `App\Support\AvatarGenerator` class is accessible

## Deployment

### Development
```bash
npm run dev
```

### Production
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Benefits of Offline Mode

✅ **No Internet Required** - System works in air-gapped environments
✅ **Faster Page Loads** - No external DNS lookups or CDN delays
✅ **Privacy** - No third-party tracking (Google Fonts, etc.)
✅ **Reliability** - No dependency on external services
✅ **Security** - Full control over all assets
✅ **Compliance** - Better GDPR/privacy compliance

## Rollback

To revert to CDN-based assets:
```bash
git checkout main -- resources/views/layouts/
git checkout main -- resources/js/app.js
npm run build
```

## Maintenance

### Updating Dependencies
```bash
npm update
npm run build
php artisan view:clear
```

### Adding New Fonts
1. Add font files to `/public/fonts/[font-name]/`
2. Update `resources/css/fonts.css` with @font-face declarations
3. Run `npm run build`

## Support

For issues or questions:
- Check browser console for errors
- Review `storage/logs/laravel.log`
- Verify all setup steps were completed
- Test in incognito mode to rule out cache issues

---

**Last Updated**: January 8, 2026
**Version**: 1.0.0
**Branch**: TN-019-offline_resources
