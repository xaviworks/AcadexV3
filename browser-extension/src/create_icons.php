<?php

// Create icons directory if it doesn't exist
$iconsDir = __DIR__.'/icons';
if (! file_exists($iconsDir)) {
    mkdir($iconsDir, 0777, true);
}

// Source logo path
$logoPath = __DIR__.'/../../public/images/logo.png';

// Check if logo exists
if (! file_exists($logoPath)) {
    exit("Error: Logo file not found at $logoPath\n");
}

// Load the logo
$logo = imagecreatefrompng($logoPath);

// Icon sizes to generate
$sizes = [16, 48, 128];

foreach ($sizes as $size) {
    // Create a new image with the desired size
    $icon = imagecreatetruecolor($size, $size);

    // Preserve transparency
    imagealphablending($icon, false);
    imagesavealpha($icon, true);
    $transparent = imagecolorallocatealpha($icon, 255, 255, 255, 127);
    imagefilledrectangle($icon, 0, 0, $size, $size, $transparent);

    // Resize the logo
    imagecopyresampled(
        $icon,
        $logo,
        0, 0, 0, 0,
        $size, $size,
        imagesx($logo), imagesy($logo)
    );

    // Save the icon
    $outputPath = $iconsDir."/icon{$size}.png";
    imagepng($icon, $outputPath);
    imagedestroy($icon);

    echo "Created icon: $outputPath\n";
}

imagedestroy($logo);
echo "Icon generation complete!\n";
