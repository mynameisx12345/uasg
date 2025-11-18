<?php
/**
 * Google Cloud Natural Language API Configuration
 * 
 * Get your API key from: https://console.cloud.google.com/apis/credentials
 * Enable the Cloud Natural Language API in your Google Cloud Console
 */

return [
    // Your Google Cloud API key
    'api_key' => 'YOUR_GOOGLE_CLOUD_API_KEY_HERE',
    
    // Enable/disable NLP analysis for file uploads
    'enabled' => true,
    
    // Use Google Cloud API for classification (consumes API quota)
    // Set to false to use only keyword matching (free, faster)
    'use_google_api' => false,
    
    // Supported file types for NLP analysis
    'supported_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
        'text/plain',
        'text/html',
        'application/rtf'
    ],
    
    // Maximum file size for NLP analysis (in bytes)
    // Default: 10MB
    'max_file_size' => 10 * 1024 * 1024,
    
    // Minimum confidence score to auto-assign category (0-100)
    'min_confidence' => 30,
    
    // Enable automatic category assignment based on keyword matching
    'auto_categorization' => true,
    
    // Store analysis results in database
    'store_analysis' => true,
    
    // API timeout in seconds
    'timeout' => 30
];
?>
