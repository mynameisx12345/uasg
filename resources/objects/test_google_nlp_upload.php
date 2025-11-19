<?php
// Simple upload and Google NLP test
require_once __DIR__ . '/google_nlp_service.php';

// HTML upload form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="post" enctype="multipart/form-data">'
        . '<label>Select file to analyze:</label><br>'
        . '<input type="file" name="file" required><br><br>'
        . '<button type="submit">Upload & Analyze</button>'
        . '</form>';
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo 'Error: No file uploaded or upload error.';
    exit;
}

$file = $_FILES['file'];
$tmpPath = $file['tmp_name'];
$mimeType = $file['type'];

// Load API key from config
$configFile = __DIR__ . '/../../config/google_nlp_config.php';
$apiKey = '';
if (file_exists($configFile)) {
    $config = include($configFile);
    $apiKey = $config['api_key'] ?? '';
}

$nlp = new GoogleNLPService($apiKey);

// Example categories and keywords (replace with DB fetch if needed)
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

$result = $nlp->analyzeFileAndSuggestCategory($tmpPath, $mimeType, $categories);

// Display result
if ($result['success']) {
    $cat = $result['suggested_category_name'] ?? 'Uncategorized';
    $score = $result['confidence'] ?? 0;
    echo '<h3>Google NLP Analysis Result</h3>';
    echo '<b>Suggested Category:</b> ' . htmlspecialchars($cat) . '<br>';
    echo '<b>Confidence Score:</b> ' . htmlspecialchars($score) . '%<br>';
    echo '<pre>' . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo 'NLP analysis failed.<br>';
    echo '<pre>' . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . '</pre>';
}
?>