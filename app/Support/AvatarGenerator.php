<?php

namespace App\Support;

/**
 * Local Avatar Generator
 * Generates SVG avatars with initials - no external API calls
 * Replaces ui-avatars.com API for offline use
 */
class AvatarGenerator
{
    /**
     * Generate an SVG avatar with initials
     *
     * @param string $name Full name
     * @param string $background Background color (hex without #)
     * @param string $color Text color (hex without #)
     * @param int $size Size in pixels (default: 128)
     * @return string SVG data URI
     */
    public static function generate(
        string $name,
        string $background = '259c59',
        string $color = 'fff',
        int $size = 128
    ): string {
        // Extract initials
        $initials = self::getInitials($name);
        
        // Ensure colors have # prefix
        $bgColor = '#' . ltrim($background, '#');
        $textColor = '#' . ltrim($color, '#');
        
        // Calculate font size (roughly 40% of avatar size)
        $fontSize = round($size * 0.4);
        
        // Generate SVG
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
    <rect width="{$size}" height="{$size}" fill="{$bgColor}" rx="4"/>
    <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-family="Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="{$fontSize}" font-weight="600" fill="{$textColor}">{$initials}</text>
</svg>
SVG;

        // Return as data URI
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Get initials from name
     *
     * @param string $name
     * @return string
     */
    private static function getInitials(string $name): string
    {
        // Clean and split name
        $name = trim($name);
        $parts = preg_split('/\s+/', $name);
        
        if (empty($parts)) {
            return '?';
        }
        
        // Get first letter of first word
        $initials = strtoupper(substr($parts[0], 0, 1));
        
        // Get first letter of last word (if exists and different from first)
        if (count($parts) > 1) {
            $initials .= strtoupper(substr(end($parts), 0, 1));
        }
        
        return $initials;
    }

    /**
     * Generate avatar URL helper for Blade templates
     *
     * @param string $name
     * @param string $background
     * @param string $color
     * @param int $size
     * @return string
     */
    public static function url(
        string $name,
        string $background = '259c59',
        string $color = 'fff',
        int $size = 128
    ): string {
        return self::generate($name, $background, $color, $size);
    }
}
