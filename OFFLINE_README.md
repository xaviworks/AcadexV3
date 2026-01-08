# Offline Resources - Quick Start

## This project is now 100% offline-ready!

### Quick Setup (3 steps)

1. **Install dependencies**
   ```bash
   npm install && composer install
   ```

2. **Download fonts** to `public/fonts/`
   - Download from: https://gwfh.mranftl.com/fonts
   - Inter (weights: 300, 400, 500, 600, 700) → `public/fonts/inter/`
   - Poppins (weight: 700) → `public/fonts/poppins/`

3. **Build & Run**
   ```bash
   npm run build
   php artisan serve
   ```

### What Changed?

- Removed ALL CDN dependencies
- All assets bundled locally (Bootstrap, jQuery, DataTables, Chart.js, SweetAlert2, etc.)
- Local avatar generation (no more ui-avatars.com API)
- Self-hosted fonts (no Google Fonts CDN)

### Need Help?

See full documentation: `docs/OFFLINE_MIGRATION_GUIDE.md`

---

**Branch:** `TN-019-offline_resources`
