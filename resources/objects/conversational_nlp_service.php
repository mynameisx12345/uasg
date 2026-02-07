<?php
/**
 * Conversational NLP Service
 * Processes natural language queries to extract search intent and parameters
 */

class ConversationalNLPService {
    
    /**
     * Parse conversational query and extract intent
     * 
     * @param string $query The user's natural language query
     * @return array Contains: 'intent', 'category', 'keywords', 'action'
     */
    public static function parseQuery($query) {
        $originalQuery = $query; // Keep original for ML prediction
        $query = strtolower(trim($query));
        
        $result = [
            'intent' => 'search',
            'category' => null,
            'keywords' => [],
            'action' => 'filter',
            'show_all' => false,
            'ml_predicted' => false,
            'ml_confidence' => 0,
            'detection_method' => 'pattern' // 'pattern' or 'ml'
        ];
        
        // Remove common question words and phrases
        $questionWords = [
            'can you ', 'could you ', 'would you ', 'will you ',
            'please ', 'kindly ', 'i want to ', 'i need to ',
            'help me ', 'find ', 'search for ', 'look for ',
            'get me ', 'fetch ', 'retrieve ', 'display '
        ];
        
        foreach ($questionWords as $word) {
            $query = str_replace($word, '', $query);
        }
        
        // Detect "show all" intent
        $showAllPatterns = [
            '/show\s+(me\s+)?all/i',
            '/list\s+(me\s+)?all/i',
            '/display\s+(me\s+)?all/i',
            '/get\s+(me\s+)?all/i',
            '/give\s+(me\s+)?all/i',
            '/see\s+all/i',
            '/view\s+all/i'
        ];
        
        foreach ($showAllPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                $result['show_all'] = true;
                $result['action'] = 'show_all';
                break;
            }
        }
        
        // Category detection - map common terms to categories
        $categoryMappings = [
            'resolution' => ['resolution', 'resolutions', 'resolusyon'],
            'memorandum' => ['memorandum', 'memorandums', 'memo', 'memos'],
            'amendment' => ['amendment', 'amendments', 'revised'],
            'ordinance' => ['ordinance', 'ordinances'],
            'contract' => ['contract', 'contracts', 'agreement', 'agreements'],
            'report' => ['report', 'reports', 'reporting'],
            'minutes' => ['minutes', 'meeting minutes', 'meeting notes'],
            'letter' => ['letter', 'letters', 'correspondence'],
            'proposal' => ['proposal', 'proposals'],
            'policy' => ['policy', 'policies']
        ];
        
        foreach ($categoryMappings as $category => $terms) {
            foreach ($terms as $term) {
                if (stripos($query, $term) !== false) {
                    $result['category'] = $category;
                    break 2;
                }
            }
        }
        
        // Extract keywords (words that aren't common/stop words)
        $stopWords = [
            'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'about', 'as', 'is', 'are', 'was', 'were',
            'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did',
            'show', 'list', 'display', 'get', 'give', 'find', 'search', 'all',
            'me', 'my', 'mine', 'you', 'your', 'yours', 'can', 'could', 'would',
            'will', 'should', 'may', 'might', 'must', 'please', 'kindly'
        ];
        
        $words = preg_split('/\s+/', $query);
        foreach ($words as $word) {
            $word = trim($word, '.,!?;:');
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                // Don't add the category as a keyword
                $isCategory = false;
                foreach ($categoryMappings as $cat => $terms) {
                    if (in_array($word, $terms)) {
                        $isCategory = true;
                        break;
                    }
                }
                if (!$isCategory) {
                    $result['keywords'][] = $word;
                }
            }
        }
        
        // Detect specific actions
        if (preg_match('/\b(find|search|look for)\b/i', $query)) {
            $result['action'] = 'search';
        } elseif (preg_match('/\b(count|how many|number of)\b/i', $query)) {
            $result['action'] = 'count';
        } elseif (preg_match('/\b(recent|latest|new)\b/i', $query)) {
            $result['action'] = 'recent';
        }
        
        // 🤖 ML INTEGRATION: If no category found via patterns, try ML prediction
        if ($result['category'] === null && strlen($originalQuery) > 10) {
            try {
                require_once(__DIR__ . '/ml_service.php');
                $mlService = new MLClassificationService();
                
                // Use ML to predict category from the original query
                $mlResult = $mlService->predict($originalQuery);
                
                // Use ML prediction if confidence is above threshold (60%)
                if ($mlResult['success'] && $mlResult['confidence'] >= 0.60) {
                    $result['category'] = $mlResult['category'];
                    $result['ml_predicted'] = true;
                    $result['ml_confidence'] = $mlResult['confidence'];
                    $result['detection_method'] = 'ml';
                    
                    // Add ML category to keywords to help with searching
                    if (!in_array($mlResult['category'], $result['keywords'])) {
                        $result['keywords'][] = $mlResult['category'];
                    }
                }
            } catch (Exception $e) {
                // Silently fail - continue with pattern-based results
                error_log("ML prediction failed in conversational NLP: " . $e->getMessage());
            }
        }
        
        return $result;
    }
    
    /**
     * Get category ID from category name or slug
     * 
     * @param string $categoryName The category name to lookup
     * @return int|null The category ID or null if not found
     */
    public static function getCategoryIdByName($categoryName) {
        if (empty($categoryName)) {
            return null;
        }
        
        $db = Database::getInstance();
        
        // Try exact match first
        $category = $db->selectOne(
            "SELECT category_id FROM category_tbl 
             WHERE LOWER(category_name) = LOWER(?) 
             OR LOWER(category_slug) = LOWER(?)",
            [$categoryName, $categoryName]
        );
        
        if ($category) {
            return $category['category_id'];
        }
        
        // Try partial match
        $category = $db->selectOne(
            "SELECT category_id FROM category_tbl 
             WHERE LOWER(category_name) LIKE LOWER(?) 
             OR LOWER(category_slug) LIKE LOWER(?)",
            ['%' . $categoryName . '%', '%' . $categoryName . '%']
        );
        
        return $category ? $category['category_id'] : null;
    }
    
    /**
     * Process conversational search query
     * 
     * @param string $query Natural language query
     * @param int|null $userId User ID for permissions
     * @return array Search results
     */
    public static function processConversationalSearch($query, $userId = null) {
        // Parse the query
        $parsed = self::parseQuery($query);
        
        // Get category ID if category was detected
        $categoryId = null;
        if ($parsed['category']) {
            $categoryId = self::getCategoryIdByName($parsed['category']);
        }
        
        // Prepare search parameters
        $searchText = '';
        
        // If show_all is true and we have a category, search text is empty
        // Otherwise, use keywords
        if (!$parsed['show_all'] && !empty($parsed['keywords'])) {
            $searchText = implode(' ', $parsed['keywords']);
        }
        
        // Use existing search functionality
        require_once(__DIR__ . '/main_class.php');
        $fileManager = new FileManager();
        $results = $fileManager->searchFilesByContent($searchText, $categoryId, $userId);
        
        // Add metadata about the query understanding
        return [
            'results' => $results,
            'parsed_query' => $parsed,
            'category_id' => $categoryId,
            'total_results' => count($results)
        ];
    }
    
    /**
     * Generate a human-friendly response message
     * 
     * @param array $searchData The search data with parsed query and results
     * @return string Human-friendly message
     */
    public static function generateResponseMessage($searchData) {
        $parsed = $searchData['parsed_query'];
        $count = $searchData['total_results'];
        
        $message = '';
        
        // Add ML detection indicator
        $mlIndicator = '';
        if ($parsed['ml_predicted'] ?? false) {
            $confidence = number_format(($parsed['ml_confidence'] ?? 0) * 100, 1);
            $mlIndicator = " <small style='color: #28a745;'>(ML detected: {$confidence}% confidence)</small>";
        }
        
        if ($parsed['show_all'] && $parsed['category']) {
            $message = "Found {$count} " . ucfirst($parsed['category']) . " file(s){$mlIndicator}";
        } elseif ($parsed['category']) {
            $message = "Found {$count} " . ucfirst($parsed['category']) . " file(s){$mlIndicator}";
            if (!empty($parsed['keywords'])) {
                $keywords = array_filter($parsed['keywords'], function($kw) use ($parsed) {
                    return $kw !== $parsed['category'];
                });
                if (!empty($keywords)) {
                    $message .= " matching '" . implode(', ', $keywords) . "'";
                }
            }
        } elseif (!empty($parsed['keywords'])) {
            $message = "Found {$count} file(s) matching '" . implode(', ', $parsed['keywords']) . "'";
        } else {
            $message = "Found {$count} file(s)";
        }
        
        return $message;
    }
}
