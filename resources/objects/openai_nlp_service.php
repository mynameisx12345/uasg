<?php
/**
 * OpenAI NLP Service for Document Categorization
 * 
 * Uses OpenAI's GPT models to analyze and categorize uploaded documents
 * More affordable and accessible than Google Cloud NLP
 */

require_once 'text_extractor.php';

class OpenAINLPService {
    private $apiKey;
    private $model;
    private $apiEndpoint;
    private $temperature;
    private $categories;
    
    /**
     * Constructor
     * @param array $config - Configuration array
     */
    public function __construct($config = null) {
        if ($config === null) {
            $config = $this->loadConfig();
        }
        
        $this->apiKey = $config['openai']['api_key'] ?? '';
        $this->model = $config['openai']['model'] ?? 'gpt-3.5-turbo';
        $this->apiEndpoint = $config['openai']['api_endpoint'] ?? 'https://api.openai.com/v1/chat/completions';
        $this->temperature = $config['openai']['temperature'] ?? 0.3;
        $this->categories = $config['categories'] ?? [];
    }
    
    /**
     * Load configuration from file
     */
    private function loadConfig() {
        $configFile = __DIR__ . '/../../config/google_nlp_config.php';
        if (file_exists($configFile)) {
            return include($configFile);
        }
        return [];
    }
    
    /**
     * Analyze file and return categorization result
     * @param string $filePath - Path to uploaded file
     * @param string $mimeType - MIME type of file
     * @return array
     */
    public function analyzeFile($filePath, $mimeType) {
        $startTime = microtime(true);
        
        try {
            // Extract text from file
            $extractor = new TextExtractor();
            $extraction = $extractor->extractText($filePath, $mimeType);
            
            if (!$extraction['success']) {
                return [
                    'success' => false,
                    'error' => 'Text extraction failed: ' . ($extraction['error'] ?? 'Unknown error'),
                    'debug' => [
                        'file_path' => $filePath,
                        'mime_type' => $mimeType,
                        'file_exists' => file_exists($filePath),
                        'file_readable' => is_readable($filePath),
                        'file_size' => file_exists($filePath) ? filesize($filePath) : 0
                    ],
                    'category_tag' => 'Uncategorized',
                    'category_score' => 0,
                    'extracted_text' => '',
                    'word_count' => 0,
                    'entities' => [],
                    'keywords' => [],
                    'processing_time_ms' => 0
                ];
            }
            
            if (empty($extraction['text'])) {
                return [
                    'success' => false,
                    'error' => 'No text content extracted from file',
                    'debug' => [
                        'file_path' => $filePath,
                        'mime_type' => $mimeType,
                        'extraction_length' => $extraction['length'] ?? 0
                    ],
                    'category_tag' => 'Uncategorized',
                    'category_score' => 0,
                    'extracted_text' => '',
                    'word_count' => 0,
                    'entities' => [],
                    'keywords' => [],
                    'processing_time_ms' => 0
                ];
            }
            
            $text = $extraction['text'];
            $wordCount = str_word_count($text);
            
            // Check if API key is configured
            if (empty($this->apiKey)) {
                throw new Exception('OpenAI API key not configured');
            }
            
            // Use OpenAI for categorization - Pure AI analysis
            $result = $this->categorizeWithOpenAI($text);
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => true,
                'category_tag' => $result['category'],
                'category_score' => $result['confidence'],
                'extracted_text' => substr($text, 0, 50000), // Limit stored text
                'word_count' => $wordCount,
                'entities' => $result['entities'] ?? [],
                'keywords' => $result['keywords'] ?? [],
                'full_analysis' => $result,
                'processing_time_ms' => $processingTime
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'category_tag' => 'Uncategorized',
                'category_score' => 0,
                'extracted_text' => '',
                'word_count' => 0,
                'entities' => [],
                'keywords' => [],
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000)
            ];
        }
    }
    
    /**
     * Categorize document using OpenAI API
     * @param string $text - Document text
     * @return array
     */
    private function categorizeWithOpenAI($text) {
        // Create prompt for GPT - Let OpenAI determine category freely
        $prompt = "Analyze the following document and determine its most appropriate category.\n\n";
        $prompt .= "Document content:\n" . substr($text, 0, 4000); // Limit to avoid token limits
        $prompt .= "\n\nBased on the content, respond with a JSON object containing:\n";
        $prompt .= "1. category: A clear, concise category name that best describes this document (e.g., 'Resolution', 'Budget Report', 'Meeting Minutes', 'Policy Document', 'Amendment', 'Memorandum', etc.)\n";
        $prompt .= "2. confidence: Your confidence score (0-100) in this categorization\n";
        $prompt .= "3. keywords: Array of 5-8 most important keywords that define this document\n";
        $prompt .= "4. entities: Array of 3-5 key entities mentioned (people, organizations, places, dates)\n";
        $prompt .= "5. reasoning: Brief explanation (1-2 sentences) of why you chose this category\n";
        $prompt .= "6. document_type: The type of document (e.g., 'Official Document', 'Report', 'Communication', 'Legal Document', etc.)\n";
        $prompt .= "\nRespond ONLY with valid JSON, no additional text.";
        
        // Make API request
        $response = $this->makeOpenAIRequest($prompt);
        
        // Parse response
        $result = $this->parseOpenAIResponse($response);
        
        return $result;
    }
    
    /**
     * Make request to OpenAI API
     * @param string $prompt - The prompt to send
     * @return string
     */
    private function makeOpenAIRequest($prompt) {
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a document categorization assistant. Analyze documents and classify them accurately.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => 500
        ];
        
        $ch = curl_init($this->apiEndpoint);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('OpenAI API error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'API request failed with HTTP code ' . $httpCode;
            throw new Exception($errorMessage);
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response');
        }
        
        return $result['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Parse OpenAI response
     * @param string $response - API response content
     * @return array
     */
    private function parseOpenAIResponse($response) {
        // Try to extract JSON from response
        preg_match('/\{.*\}/s', $response, $matches);
        
        if (!empty($matches[0])) {
            $data = json_decode($matches[0], true);
            if ($data) {
                return [
                    'category' => $data['category'] ?? 'Uncategorized',
                    'confidence' => min(100, max(0, floatval($data['confidence'] ?? 50))),
                    'keywords' => $data['keywords'] ?? [],
                    'entities' => $data['entities'] ?? [],
                    'reasoning' => $data['reasoning'] ?? '',
                    'document_type' => $data['document_type'] ?? 'General Document'
                ];
            }
        }
        
        // Fallback: simple parsing
        return [
            'category' => 'Uncategorized',
            'confidence' => 50,
            'keywords' => [],
            'entities' => [],
            'reasoning' => 'Failed to parse AI response',
            'document_type' => 'Unknown'
        ];
    }
    
    /**
     * Keyword-based categorization (free, no API needed)
     * @param string $text - Document text
     * @param int $wordCount - Word count
     * @param float $startTime - Start timestamp
     * @return array
     */
    private function keywordBasedCategorization($text, $wordCount, $startTime) {
        $result = $this->keywordBasedCategorizationSimple($text);
        
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        return [
            'success' => true,
            'category_tag' => $result['category'],
            'category_score' => $result['confidence'],
            'extracted_text' => substr($text, 0, 50000),
            'word_count' => $wordCount,
            'entities' => [],
            'keywords' => $result['matched_keywords'],
            'full_analysis' => $result,
            'processing_time_ms' => $processingTime
        ];
    }
    
    /**
     * Simple keyword-based categorization
     * @param string $text - Document text
     * @return array
     */
    private function keywordBasedCategorizationSimple($text) {
        $textLower = strtolower($text);
        $scores = [];
        
        foreach ($this->categories as $categoryName => $categoryInfo) {
            $score = 0;
            $matchedKeywords = [];
            
            foreach ($categoryInfo['keywords'] as $keyword) {
                $keywordLower = strtolower($keyword);
                $count = substr_count($textLower, $keywordLower);
                
                if ($count > 0) {
                    $score += $count * 10;
                    $matchedKeywords[] = $keyword;
                }
            }
            
            if ($score > 0) {
                $scores[$categoryName] = [
                    'score' => $score,
                    'matched_keywords' => $matchedKeywords,
                    'confidence' => min(100, $score)
                ];
            }
        }
        
        if (empty($scores)) {
            return [
                'category' => 'Uncategorized',
                'confidence' => 0,
                'matched_keywords' => []
            ];
        }
        
        // Get highest scoring category
        arsort($scores);
        $topCategory = array_key_first($scores);
        
        return [
            'category' => $topCategory,
            'confidence' => $scores[$topCategory]['confidence'],
            'matched_keywords' => $scores[$topCategory]['matched_keywords']
        ];
    }
}
?>
