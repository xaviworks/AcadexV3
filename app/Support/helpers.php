<?php

use App\Support\AvatarGenerator;

if (! function_exists('avatar')) {
    /**
     * Generate a local SVG avatar
     *
     * @param string $name
     * @param string $background
     * @param string $color
     * @param int $size
     * @return string
     */
    function avatar(string $name, string $background = '259c59', string $color = 'fff', int $size = 128): string
    {
        return AvatarGenerator::generate($name, $background, $color, $size);
    }
}
