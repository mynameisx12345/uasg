<?php
// Simple test script for GoogleNLPService
require_once __DIR__ . '/google_nlp_service.php';

// Set test file path and mime type
$testFile = __DIR__ . '/sample_test.txt';
$mimeType = 'text/plain';

// Create a sample file if not exists
if (!file_exists($testFile)) {
    file_put_contents($testFile, "Resolution: The council voted on the amendment. Minutes attached. Dear members, please review the constitution and bylaw changes.");
}

// Load API key from config
$configFile = __DIR__ . '/../../config/google_nlp_config.php';
$apiKey = '';
if (file_exists($configFile)) {
    $config = include($configFile);
    $apiKey = $config['api_key'] ?? '';
}

$nlp = new GoogleNLPService($apiKey);

// Get available categories and keywords
$categories = [
    [
        'file_category_id' => 1,
        'file_category' => 'Resolutions',
        'keywords' => ['resolution', 'motion', 'vote', 'council']
    ],
    [
        'file_category_id' => 2,
        'file_category' => 'Amendments',
        'keywords' => ['amendment', 'change', 'constitution', 'bylaw']
    ],
    [
        'file_category_id' => 3,
        'file_category' => 'Minutes',
        'keywords' => ['minutes', 'meeting', 'attendance', 'agenda']
    ],
    [
        'file_category_id' => 4,
        'file_category' => 'Letters',
        'keywords' => ['dear']
    ]
];

$result = $nlp->analyzeFileAndSuggestCategory($testFile, $mimeType, $categories);

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>