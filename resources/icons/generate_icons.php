<?php
// Simple icon generator for PWA
$sizes = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512];
$baseDir = __DIR__ . '/';

foreach ($sizes as $size) {
    // Create a simple colored square as placeholder
    $img = imagecreate($size, $size);
    
    // Define colors
    $blue = imagecolorallocate($img, 33, 150, 243); // #2196F3
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Fill background
    imagefill($img, 0, 0, $blue);
    
    // Add text
    $fontSize = $size * 0.3;
    $text = 'UASG';
    
    // Calculate text position (center)
    $textBox = imagettfbbox($fontSize, 0, __DIR__ . '/arial.ttf', $text);
    if (!$textBox) {
        // Fallback to imagestring if ttf fails
        $fontSize = $size > 96 ? 5 : ($size > 48 ? 3 : 2);
        $textWidth = strlen($text) * imagefontwidth($fontSize);
        $textHeight = imagefontheight($fontSize);
        $x = ($size - $textWidth) / 2;
        $y = ($size - $textHeight) / 2;
        imagestring($img, $fontSize, $x, $y, $text, $white);
    } else {
        $x = ($size - $textBox[4]) / 2;
        $y = ($size - $textBox[5]) / 2 + $textBox[5];
        imagettftext($img, $fontSize, 0, $x, $y, $white, __DIR__ . '/arial.ttf', $text);
    }
    
    // Save image
    $filename = $baseDir . "icon-{$size}x{$size}.png";
    imagepng($img, $filename);
    imagedestroy($img);
    
    echo "Generated: icon-{$size}x{$size}.png\n";
}

echo "All PWA icons generated successfully!\n";
?>