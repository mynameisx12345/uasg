<?php
/**
 * NLP Service Configuration
 * 
 * Supports multiple NLP providers for document categorization:
 * - 'openai': OpenAI API (GPT-based, requires API key)
 * - 'google': Google Cloud Natural Language API (requires API key)
 * - 'keyword': Keyword-based matching (free, no API needed)
 */

return [
    // NLP Provider: 'openai', 'google', 'nlpcloud', or 'keyword'
    'provider' => 'nlpcloud',
    
    // OpenAI Configuration
    'openai' => [
        'api_key' => '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',
        'model' => 'gpt-3.5-turbo', // or 'gpt-4' for better accuracy
        'api_endpoint' => 'https://api.openai.com/v1/chat/completions',
        'temperature' => 0.3, // Lower = more focused, Higher = more creative
    ],
    
    // Google Cloud API Configuration
    'google' => [
        'api_key' => 'YOUR_GOOGLE_CLOUD_API_KEY_HERE', // Get from: https://console.cloud.google.com/apis/credentials
        'use_api' => true,
    ],
    
    // NLP Cloud Configuration
    'nlpcloud' => [
        'api_token' => '3a2d1bdce1bc58f8ae2f27e7a7d2d9b8e8b91d52', // Get from: https://nlpcloud.com/home/token
        'model' => 'flair/ner-english', // Free Named Entity Recognition model
        'classification_model' => 'distilbert-base-uncased-emotion', // Free classification model
        'use_api' => true,
    ],
    
    // Enable/disable NLP analysis for file uploads
    'enabled' => true,
    
    // Enable/disable NLP analysis for file uploads
    'enabled' => true,
    
    // Predefined document categories for classification
    'categories' => [
        'Resolutions' => [
            'keywords' => ['resolution', 'resolve', 'whereas', 'enacted', 'legislative', 'motion', 'vote'],
            'description' => 'Formal resolutions and legislative documents'
        ],
        'Amendments' => [
            'keywords' => ['amendment', 'amend', 'revise', 'modify', 'change', 'update', 'alter'],
            'description' => 'Document amendments and revisions'
        ],
        'Proposals' => [
            'keywords' => ['proposal', 'propose', 'suggest', 'recommend', 'plan', 'project'],
            'description' => 'Project proposals and recommendations'
        ],
        'Reports' => [
            'keywords' => ['report', 'summary', 'findings', 'analysis', 'conclusion', 'results'],
            'description' => 'Reports and analytical documents'
        ],
        'Minutes' => [
            'keywords' => ['minutes', 'meeting', 'attendees', 'agenda', 'discussion', 'proceedings'],
            'description' => 'Meeting minutes and proceedings'
        ],
        'Memorandums' => [
            'keywords' => ['memorandum', 'memo', 'notice', 'announcement', 'circular'],
            'description' => 'Memos and official notices'
        ],
        'Policies' => [
            'keywords' => ['policy', 'guideline', 'regulation', 'rule', 'standard', 'procedure'],
            'description' => 'Policies and guidelines'
        ],
        'Contracts' => [
            'keywords' => ['contract', 'agreement', 'terms', 'conditions', 'covenant', 'stipulation'],
            'description' => 'Contracts and agreements'
        ]
    ],
    
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
