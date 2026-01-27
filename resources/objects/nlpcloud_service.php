<?php
/**
 * NLP Cloud API Integration Service
 * 
 * Uses NLP Cloud API for document categorization and analysis
 * Free tier available with 100 requests/month
 * 
 * Setup:
 * 1. Sign up at https://nlpcloud.com
 * 2. Get your API token from the dashboard
 * 3. Add it to config/google_nlp_config.php
 * 
 * Features:
 * - Document classification
 * - Entity extraction
 * - Sentiment analysis
 * - Keyword extraction
 */

require_once 'text_extractor.php';

class NLPCloudService {
    private $apiToken;
    private $model;
    private $apiEndpoint = 'https://api.nlpcloud.io/v1';
    
    /**
     * Constructor
     */
    public function __construct($config = null) {
        if ($config === null) {
            $config = $this->loadConfig();
        }
        
        $this->apiToken = $config['nlpcloud']['api_token'] ?? '';
        $this->model = $config['nlpcloud']['model'] ?? 'bart-large-mnli-yahoo-answers';
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
            
            // Check if API token is configured
            if (empty($this->apiToken)) {
                throw new Exception('NLP Cloud API token not configured. Get one at https://nlpcloud.com');
            }
            
            // Use NLP Cloud for categorization - Pure AI analysis
            $result = $this->categorizeWithNLPCloud($text);
            
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
     * Categorize document using NLP Cloud API with TRUE AI Classification
     * Uses Google NLP for entity/keyword extraction to save NLP Cloud credits
     * @param string $text - Document text
     * @return array
     */
    private function categorizeWithNLPCloud($text) {
        try {
            // Step 1: Extract entities using Google NLP (free tier available, saves NLP Cloud credits)
            $entities = [];
            $keywords = [];
            
            try {
                require_once __DIR__ . '/google_nlp_service.php';
                $googleNLP = new GoogleNLPService();
                $entityAnalysis = $googleNLP->analyzeEntities($text);
                
                if ($entityAnalysis['success'] && !empty($entityAnalysis['entities'])) {
                    // Extract entity names for keywords
                    $entities = array_map(function($entity) {
                        return is_array($entity) ? ($entity['name'] ?? $entity['text'] ?? '') : $entity;
                    }, $entityAnalysis['entities']);
                    
                    // Use top entities as keywords
                    $keywords = array_slice($entities, 0, 8);
                } else {
                    // Fallback to NLP Cloud entities if Google fails
                    $entities = $this->extractEntities($text);
                    $keywords = array_slice($entities, 0, 8);
                }
            } catch (Exception $e) {
                error_log('Google NLP entity extraction failed: ' . $e->getMessage());
                // Fallback to NLP Cloud entities
                $entities = $this->extractEntities($text);
                $keywords = array_slice($entities, 0, 8);
            }
            
            // Step 2: Analyze sentiment using NLP Cloud AI
            $sentiment = $this->analyzeSentiment($text);
            
            // Step 3: TRUE AI CLASSIFICATION using NLP Cloud's bart-large-mnli model
            // This is the key feature - only use NLP Cloud credits for the AI categorization
            $category = $this->classifyDocumentWithAI($text);
            
            return [
                'category' => $category['name'],
                'confidence' => $category['score'],
                'keywords' => $keywords,
                'entities' => $entities,
                'sentiment' => $sentiment,
                'provider' => 'Hybrid (Google NLP + NLP Cloud)',
                'method' => 'Zero-shot Classification' // Indicate it's TRUE AI
            ];
            
        } catch (Exception $e) {
            // Fallback to keyword-based if API fails (to ensure system keeps working)
            error_log('NLP Cloud AI classification failed, using fallback: ' . $e->getMessage());
            
            // Try Google NLP entities first
            try {
                require_once __DIR__ . '/google_nlp_service.php';
                $googleNLP = new GoogleNLPService();
                $entityAnalysis = $googleNLP->analyzeEntities($text);
                $entities = array_map(function($entity) {
                    return is_array($entity) ? ($entity['name'] ?? $entity['text'] ?? '') : $entity;
                }, $entityAnalysis['entities'] ?? []);
            } catch (Exception $e2) {
                $entities = $this->extractEntities($text);
            }
            
            $sentiment = $this->analyzeSentiment($text);
            $category = $this->categorizeFromEntities($entities, $text);
            
            return [
                'category' => $category['name'],
                'confidence' => $category['score'],
                'keywords' => array_slice($entities, 0, 8),
                'entities' => $entities,
                'sentiment' => $sentiment,
                'provider' => 'Keyword Fallback',
                'method' => 'Pattern Matching'
            ];
        }
    }
    
    /**
     * Categorize based on entities and content analysis
     */
    private function categorizeFromEntities($entities, $text) {
        $textLower = strtolower($text);
        
        // Define patterns for different document types
        $patterns = [
            'Resolution' => ['resolution', 'resolved', 'whereas', 'enacted', 'motion', 'vote', 'approve'],
            'Amendment' => ['amend', 'amendment', 'revise', 'modify', 'change', 'alter', 'update'],
            'Report' => ['report', 'summary', 'findings', 'analysis', 'results', 'conclusion'],
            'Proposal' => ['proposal', 'propose', 'suggest', 'recommend', 'plan', 'project'],
            'Minutes' => ['minutes', 'meeting', 'agenda', 'attendees', 'discussion', 'action items'],
            'Memorandum' => ['memorandum', 'memo', 'to:', 'from:', 'subject:', 'date:'],
            'Policy Document' => ['policy', 'procedure', 'guidelines', 'rules', 'regulations'],
            'Legal Document' => ['contract', 'agreement', 'terms', 'conditions', 'legal', 'law'],
            'Budget Document' => ['budget', 'financial', 'expenditure', 'revenue', 'fiscal'],
            'Official Letter' => ['dear', 'sincerely', 'regards', 'letter', 'correspondence']
        ];
        
        $scores = [];
        foreach ($patterns as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $count = substr_count($textLower, $keyword);
                $score += $count * 10;
            }
            if ($score > 0) {
                $scores[$category] = $score;
            }
        }
        
        if (empty($scores)) {
            return ['name' => 'General Document', 'score' => 50];
        }
        
        arsort($scores);
        $topCategory = array_key_first($scores);
        $topScore = min(95, $scores[$topCategory]);
        
        return [
            'name' => $topCategory,
            'score' => $topScore
        ];
    }
    
    /**
     * TRUE AI CLASSIFICATION using Zero-Shot Learning
     * Uses bart-large-mnli-yahoo-answers for intelligent document categorization
     * This is REAL AI - not keyword matching!
     */
    private function classifyDocumentWithAI($text) {
        try {
            // Use the classification model configured in your API
            $endpoint = $this->apiEndpoint . '/bart-large-mnli-yahoo-answers/classification';
            
            // Limit text to avoid API limits (free tier typically allows up to 1024 tokens)
            $textSample = substr($text, 0, 2048);
            
            // Define candidate labels (categories the AI will choose from)
            $labels = [
                'Resolution',
                'Amendment', 
                'Report',
                'Proposal',
                'Meeting Minutes',
                'Memorandum',
                'Policy Document',
                'Legal Document',
                'Budget Document',
                'Official Letter',
                'Academic Document',
                'Technical Document',
                'General Document'
            ];
            
            $data = [
                'text' => $textSample,
                'labels' => $labels,
                'multi_class' => false // We want single best category
            ];
            
            $response = $this->makeRequest($endpoint, $data);
            
            // Parse AI response
            if (isset($response['labels']) && isset($response['scores'])) {
                // Get the top classification
                $topLabel = $response['labels'][0];
                $topScore = $response['scores'][0];
                
                // Convert score to percentage (0-100)
                $confidence = round($topScore * 100, 1);
                
                return [
                    'name' => $topLabel,
                    'score' => $confidence,
                    'all_scores' => array_combine($response['labels'], $response['scores'])
                ];
            }
            
            // If API response format is unexpected, fallback
            throw new Exception('Unexpected API response format');
            
        } catch (Exception $e) {
            error_log('AI Classification error: ' . $e->getMessage());
            // Throw to trigger fallback in parent method
            throw $e;
        }
    }
    
    /**
     * Analyze sentiment of text
     */
    private function analyzeSentiment($text) {
        try {
            // Use a simple sentiment model (free tier)
            $endpoint = $this->apiEndpoint . '/distilbert-base-uncased-emotion/sentiment';
            
            $textSample = substr($text, 0, 512);
            
            $data = [
                'text' => $textSample
            ];
            
            $response = $this->makeRequest($endpoint, $data);
            
            if (isset($response['scored_labels'])) {
                return $response['scored_labels'];
            }
            
            return [];
            
        } catch (Exception $e) {
            // Sentiment analysis is optional, return empty if fails
            return [];
        }
    }
    
    /**
     * Extract entities from text
     * @param string $text - Text to analyze
     * @return array
     */
    private function extractEntities($text) {
        try {
            // Use entity extraction model (free models: flair/ner-english)
            $endpoint = $this->apiEndpoint . '/flair/ner-english/entities';
            
            // Limit text to avoid API limits
            $textSample = substr($text, 0, 1024);
            
            $data = [
                'text' => $textSample
            ];
            
            $response = $this->makeRequest($endpoint, $data);
            
            $entities = [];
            if (isset($response['entities']) && is_array($response['entities'])) {
                foreach ($response['entities'] as $entity) {
                    if (isset($entity['text'])) {
                        $entities[] = $entity['text'];
                    }
                }
            }
            
            // Return unique entities, limited to top 10
            return array_slice(array_unique($entities), 0, 10);
            
        } catch (Exception $e) {
            // If entity extraction fails, return empty array
            // Don't throw - entities are optional
            return [];
        }
    }
    
    /**
     * Make API request to NLP Cloud
     * @param string $endpoint - API endpoint
     * @param array $data - Request data
     * @return array
     */
    private function makeRequest($endpoint, $data) {
        $ch = curl_init($endpoint);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Token ' . $this->apiToken
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('NLP Cloud API error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            // Try to parse the error response
            $errorData = json_decode($response, true);
            
            // Get detailed error message
            $errorMessage = '';
            if (is_array($errorData)) {
                if (isset($errorData['detail'])) {
                    $errorMessage = $errorData['detail'];
                } elseif (isset($errorData['error'])) {
                    $errorMessage = $errorData['error'];
                } elseif (isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                } else {
                    // Show full error array
                    $errorMessage = json_encode($errorData);
                }
            } else {
                // Show raw response
                $errorMessage = substr($response, 0, 500);
            }
            
            throw new Exception('HTTP ' . $httpCode . ': ' . $errorMessage);
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse NLP Cloud response: ' . json_last_error_msg());
        }
        
        return $result;
    }
}
?>
