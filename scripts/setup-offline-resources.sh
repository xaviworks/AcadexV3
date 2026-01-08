#!/bin/bash
# ACADEX Offline Resources Setup Script
# This script downloads and sets up all required fonts for offline use

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
FONTS_DIR="$PROJECT_ROOT/public/fonts"

echo "ACADEX Offline Resources Setup"
echo "=================================="
echo ""

# Create fonts directory if it doesn't exist
mkdir -p "$FONTS_DIR"

# Function to download font
download_font() {
    local font_name=$1
    local font_url=$2
    local font_dir="$FONTS_DIR/$font_name"
    
    echo "Downloading $font_name..."
    mkdir -p "$font_dir"
    
    # Download font file
    curl -L -o "$font_dir/font.zip" "$font_url" 2>/dev/null || {
        echo "Could not download $font_name automatically."
        echo "   Please download manually from: $font_url"
        echo "   Extract to: $font_dir"
        return 1
    }
    
    # Extract if zip file
    if [ -f "$font_dir/font.zip" ]; then
        unzip -q "$font_dir/font.zip" -d "$font_dir" 2>/dev/null || true
        rm "$font_dir/font.zip"
    fi
    
    echo "$font_name downloaded"
}

echo "Setting up fonts..."
echo ""

# Note: Google Fonts need to be downloaded manually or via google-webfonts-helper
echo "Font Setup Instructions:"
echo ""
echo "1. Inter Font Family:"
echo "   - Visit: https://fonts.google.com/specimen/Inter"
echo "   - Download the font family"
echo "   - Extract woff2 files (weights: 300, 400, 500, 600, 700)"
echo "   - Place in: $FONTS_DIR/inter/"
echo ""
echo "2. Poppins Font (Bold):"
echo "   - Visit: https://fonts.google.com/specimen/Poppins"
echo "   - Download Bold (700) weight"
echo "   - Extract woff2 file"
echo "   - Place in: $FONTS_DIR/poppins/"
echo ""
echo "3. Feeling Passionate (if needed):"
echo "   - Visit: https://www.cdnfonts.com/feeling-passionate.font"
echo "   - Download font files"
echo "   - Place in: $FONTS_DIR/feeling-passionate/"
echo ""
echo "Alternative: Use google-webfonts-helper"
echo "   - Visit: https://gwfh.mranftl.com/fonts"
echo "   - Select fonts and download woff2 files"
echo ""

# Check if fonts already exist
if [ -d "$FONTS_DIR/inter" ] && [ "$(ls -A $FONTS_DIR/inter)" ]; then
    echo "Inter fonts found"
else
    echo "Inter fonts not found in $FONTS_DIR/inter/"
fi

if [ -d "$FONTS_DIR/poppins" ] && [ "$(ls -A $FONTS_DIR/poppins)" ]; then
    echo "Poppins fonts found"
else
    echo "Poppins fonts not found in $FONTS_DIR/poppins/"
fi

echo ""
echo "=================================="
echo "Installing npm packages..."
npm install

echo ""
echo "Building assets..."
npm run build

echo ""
echo "Setup complete!"
echo ""
echo "Next steps:"
echo "1. Download fonts manually (see instructions above)"
echo "2. Run: composer dump-autoload"
echo "3. Run: php artisan config:clear"
echo "4. Run: php artisan view:clear"
echo "5. Test offline functionality"
echo ""
echo "Your ACADEX system is now configured for offline use!"
