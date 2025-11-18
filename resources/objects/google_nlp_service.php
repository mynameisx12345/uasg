<?php
/**
 * Google Cloud Natural Language API Integration Service
 * 
 * This service integrates with Google Cloud Natural Language API to classify
 * uploaded documents and automatically tag them based on available keywords.
 * 
 * Features:
 * - Document classification
 * - Entity extraction
 * - Keyword matching with existing categories
 * - Automatic file categorization based on content
 */

class GoogleNLPService {
    private $apiKey;
    private $apiEndpoint = 'https://language.googleapis.com/v1/documents:';
    
    /**
     * Constructor
     * @param string $apiKey - Google Cloud API key
     */
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?? $this->getApiKeyFromConfig();
    }
    
    /**
     * Get API key from configuration file
     */
    private function getApiKeyFromConfig() {
        $configFile = __DIR__ . '/../../config/google_nlp_config.php';
        if (file_exists($configFile)) {
            $config = include($configFile);
            return $config['api_key'] ?? '';
        }
        return '';
    }
    
    /**
     * Extract text from uploaded file
     * @param string $filePath - Path to the uploaded file
     * @param string $mimeType - MIME type of the file
     * @return array
     */
    public function extractTextFromFile($filePath, $mimeType) {
        try {
            $text = '';
            
            // Extract text based on file type
            if (strpos($mimeType, 'text/') === 0) {
                // Plain text file
                $text = file_get_contents($filePath);
            } elseif ($mimeType === 'application/pdf') {
                // PDF file - basic extraction
                $text = $this->extractTextFromPDF($filePath);
            } elseif (strpos($mimeType, 'application/vnd.openxmlformats') === 0) {
                // Office documents (docx, xlsx, pptx)
                $text = $this->extractTextFromOffice($filePath, $mimeType);
            } elseif (strpos($mimeType, 'application/msword') === 0 || 
                      strpos($mimeType, 'application/vnd.ms-') === 0) {
                // Old Office formats
                $text = $this->extractTextFromOldOffice($filePath, $mimeType);
            }
            
            return [
                'success' => true,
                'text' => $text,
                'length' => strlen($text)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'text' => ''
            ];
        }
    }
    
    /**
     * Extract text from PDF file
     */
    private function extractTextFromPDF($filePath) {
        // Basic PDF text extraction
        // For production, consider using libraries like pdftotext or PDF parser
        $content = file_get_contents($filePath);
        
        // Simple regex-based extraction (basic approach)
        preg_match_all('/\((.*?)\)/s', $content, $matches);
        $text = implode(' ', $matches[1]);
        
        // Clean up
        $text = preg_replace('/[^\w\s\.\,\-\:\;\?\!]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Extract text from Office documents (DOCX, XLSX, PPTX)
     */
    private function extractTextFromOffice($filePath, $mimeType) {
        $text = '';
        
        try {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                if (strpos($mimeType, 'wordprocessingml') !== false) {
                    // DOCX
                    $content = $zip->getFromName('word/document.xml');
                    if ($content) {
                        $xml = simplexml_load_string($content);
                        $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                        $texts = $xml->xpath('//w:t');
                        foreach ($texts as $t) {
                            $text .= (string)$t . ' ';
                        }
                    }
                } elseif (strpos($mimeType, 'spreadsheetml') !== false) {
                    // XLSX - extract from shared strings
                    $content = $zip->getFromName('xl/sharedStrings.xml');
                    if ($content) {
                        $xml = simplexml_load_string($content);
                        foreach ($xml->si as $si) {
                            $text .= (string)$si->t . ' ';
                        }
                    }
                } elseif (strpos($mimeType, 'presentationml') !== false) {
                    // PPTX
                    for ($i = 1; $i <= 50; $i++) {
                        $content = $zip->getFromName("ppt/slides/slide{$i}.xml");
                        if ($content) {
                            $xml = simplexml_load_string($content);
                            $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                            $texts = $xml->xpath('//a:t');
                            foreach ($texts as $t) {
                                $text .= (string)$t . ' ';
                            }
                        } else {
                            break;
                        }
                    }
                }
                $zip->close();
            }
        } catch (Exception $e) {
            error_log('Office extraction error: ' . $e->getMessage());
        }
        
        return trim($text);
    }
    
    /**
     * Extract text from old Office formats (DOC, XLS, PPT)
     */
    private function extractTextFromOldOffice($filePath, $mimeType) {
        // For old Office formats, basic text extraction
        // In production, consider using external tools or libraries
        $content = file_get_contents($filePath);
        
        // Remove binary data and extract readable text
        $text = preg_replace('/[^\x20-\x7E]/', ' ', $content);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim(substr($text, 0, 10000)); // Limit length
    }
    
    /**
     * Classify document content using Google Cloud Natural Language API
     * @param string $text - Document text content
     * @return array
     */
    public function classifyContent($text) {
        try {
            if (empty($text) || strlen($text) < 20) {
                throw new Exception('Text content too short for classification');
            }
            
            // Prepare API request
            $endpoint = $this->apiEndpoint . 'classifyText?key=' . $this->apiKey;
            
            $data = [
                'document' => [
                    'type' => 'PLAIN_TEXT',
                    'content' => substr($text, 0, 20000) // API limit
                ]
            ];
            
            $response = $this->makeApiRequest($endpoint, $data);
            
            if (isset($response['categories']) && !empty($response['categories'])) {
                return [
                    'success' => true,
                    'categories' => $response['categories']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'No categories found',
                'categories' => []
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'categories' => []
            ];
        }
    }
    
    /**
     * Analyze entities in document (people, organizations, locations, etc.)
     * @param string $text - Document text content
     * @return array
     */
    public function analyzeEntities($text) {
        try {
            if (empty($text) || strlen($text) < 20) {
                throw new Exception('Text content too short for analysis');
            }
            
            $endpoint = $this->apiEndpoint . 'analyzeEntities?key=' . $this->apiKey;
            
            $data = [
                'document' => [
                    'type' => 'PLAIN_TEXT',
                    'content' => substr($text, 0, 20000)
                ],
                'encodingType' => 'UTF8'
            ];
            
            $response = $this->makeApiRequest($endpoint, $data);
            
            if (isset($response['entities'])) {
                return [
                    'success' => true,
                    'entities' => $response['entities']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'No entities found',
                'entities' => []
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'entities' => []
            ];
        }
    }
    
    /**
     * Match file content with available keywords and suggest category
     * @param string $text - Document text
     * @param array $availableCategories - Array of categories with keywords
     * @return array
     */
    public function matchKeywordsAndSuggestCategory($text, $availableCategories) {
        try {
            // Convert text to lowercase for matching
            $textLower = strtolower($text);
            $words = preg_split('/\s+/', $textLower);
            $wordFreq = array_count_values($words);
            
            // Score each category based on keyword matches
            $categoryScores = [];
            
            foreach ($availableCategories as $category) {
                $categoryId = $category['file_category_id'];
                $categoryName = $category['file_category'];
                $keywords = $category['keywords'] ?? [];
                
                $score = 0;
                $matchedKeywords = [];
                
                foreach ($keywords as $keyword) {
                    $keywordLower = strtolower($keyword);
                    
                    // Check for exact keyword match
                    if (isset($wordFreq[$keywordLower])) {
                        $score += $wordFreq[$keywordLower] * 10; // High weight for exact match
                        $matchedKeywords[] = $keyword;
                    }
                    
                    // Check for partial match
                    if (stripos($textLower, $keywordLower) !== false) {
                        $score += 5; // Medium weight for partial match
                        if (!in_array($keyword, $matchedKeywords)) {
                            $matchedKeywords[] = $keyword;
                        }
                    }
                }
                
                if ($score > 0) {
                    $categoryScores[] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'score' => $score,
                        'matched_keywords' => $matchedKeywords,
                        'confidence' => min(100, ($score / (count($keywords) * 10)) * 100)
                    ];
                }
            }
            
            // Sort by score descending
            usort($categoryScores, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            if (!empty($categoryScores)) {
                return [
                    'success' => true,
                    'suggested_category' => $categoryScores[0],
                    'all_matches' => $categoryScores
                ];
            }
            
            return [
                'success' => false,
                'error' => 'No matching categories found',
                'suggested_category' => null,
                'all_matches' => []
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'suggested_category' => null,
                'all_matches' => []
            ];
        }
    }
    
    /**
     * Complete file analysis and categorization
     * @param string $filePath - Path to uploaded file
     * @param string $mimeType - MIME type
     * @param array $availableCategories - Available categories with keywords
     * @return array
     */
    public function analyzeFileAndSuggestCategory($filePath, $mimeType, $availableCategories) {
        $result = [
            'success' => false,
            'text_extraction' => null,
            'classification' => null,
            'entities' => null,
            'keyword_matching' => null,
            'suggested_category_id' => null,
            'suggested_category_name' => null,
            'confidence' => 0,
            'matched_keywords' => []
        ];
        
        try {
            // Step 1: Extract text from file
            $extraction = $this->extractTextFromFile($filePath, $mimeType);
            $result['text_extraction'] = $extraction;
            
            if (!$extraction['success'] || empty($extraction['text'])) {
                // Fallback to keyword matching based on filename if text extraction fails
                $filename = basename($filePath);
                $keywordMatch = $this->matchKeywordsAndSuggestCategory($filename, $availableCategories);
                $result['keyword_matching'] = $keywordMatch;
                
                if ($keywordMatch['success']) {
                    $result['success'] = true;
                    $result['suggested_category_id'] = $keywordMatch['suggested_category']['category_id'];
                    $result['suggested_category_name'] = $keywordMatch['suggested_category']['category_name'];
                    $result['confidence'] = $keywordMatch['suggested_category']['confidence'];
                    $result['matched_keywords'] = $keywordMatch['suggested_category']['matched_keywords'];
                }
                
                return $result;
            }
            
            $text = $extraction['text'];
            
            // Step 2: Classify content with Google NLP (optional, uses API quota)
            if (!empty($this->apiKey) && strlen($text) >= 20) {
                $classification = $this->classifyContent($text);
                $result['classification'] = $classification;
                
                // Analyze entities (optional)
                $entities = $this->analyzeEntities($text);
                $result['entities'] = $entities;
            }
            
            // Step 3: Match keywords with available categories
            $keywordMatch = $this->matchKeywordsAndSuggestCategory($text, $availableCategories);
            $result['keyword_matching'] = $keywordMatch;
            
            if ($keywordMatch['success']) {
                $result['success'] = true;
                $result['suggested_category_id'] = $keywordMatch['suggested_category']['category_id'];
                $result['suggested_category_name'] = $keywordMatch['suggested_category']['category_name'];
                $result['confidence'] = $keywordMatch['suggested_category']['confidence'];
                $result['matched_keywords'] = $keywordMatch['suggested_category']['matched_keywords'];
            }
            
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Make HTTP request to Google Cloud API
     * @param string $endpoint - API endpoint URL
     * @param array $data - Request data
     * @return array
     */
    private function makeApiRequest($endpoint, $data) {
        $ch = curl_init($endpoint);
        
        $headers = [
            'Content-Type: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'API request failed with HTTP code ' . $httpCode;
            throw new Exception($errorMessage);
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response: ' . json_last_error_msg());
        }
        
        return $result;
    }
}
?>
