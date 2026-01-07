<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Content-Type: application/json');

$imageFiles = glob('image/*.png');
$images = [];

foreach ($imageFiles as $file) {
    $filename = basename($file);
    $images[] = [
        'name' => $filename,
        'url' => 'https://admin.mishtiry.com/public/image/' . $filename,
        'exists' => file_exists($file)
    ];
}

echo json_encode([
    'success' => true,
    'server' => $_SERVER['HTTP_HOST'],
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'Not set',
    'images' => $images
]);