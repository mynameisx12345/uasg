<?php
/**
 * Text Extractor - Extract text content from various file types
 */

class TextExtractor {
    
    /**
     * Extract text from file
     * @param string $filePath - Path to file
     * @param string $mimeType - MIME type
     * @return array
     */
    public function extractText($filePath, $mimeType) {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }
            
            // Check if file is readable
            if (!is_readable($filePath)) {
                throw new Exception("File not readable: {$filePath}");
            }
            
            $text = '';
            
            // Extract based on MIME type
            if (strpos($mimeType, 'text/') === 0) {
                $text = $this->extractFromText($filePath);
            } elseif ($mimeType === 'application/pdf') {
                $text = $this->extractFromPDF($filePath);
            } elseif (strpos($mimeType, 'application/vnd.openxmlformats') === 0) {
                $text = $this->extractFromOfficeXML($filePath, $mimeType);
            } elseif (strpos($mimeType, 'application/msword') === 0 || 
                      strpos($mimeType, 'application/vnd.ms-') === 0) {
                $text = $this->extractFromOldOffice($filePath);
            } else {
                // Try as text file for unknown types
                $text = $this->extractFromText($filePath);
            }
            
            // Check if we got any text (reduced threshold)
            if (empty($text) || strlen(trim($text)) < 3) {
                throw new Exception("Minimal or no text extracted. Got " . strlen(trim($text)) . " characters. MIME type: {$mimeType}");
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
     * Extract from plain text file
     */
    private function extractFromText($filePath) {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Failed to read file contents");
        }
        return $content;
    }
    
    /**
     * Extract text from PDF
     */
    private function extractFromPDF($filePath) {
        $content = file_get_contents($filePath);
        
        // Basic PDF text extraction using regex
        // For better results, consider using libraries like pdf-parser or pdftotext
        $text = '';
        
        // Method 1: Extract text between parentheses (common in PDFs)
        if (preg_match_all('/\((.*?)\)/s', $content, $matches)) {
            $text = implode(' ', $matches[1]);
        }
        
        // Method 2: Try to find text objects
        if (empty($text) || strlen($text) < 50) {
            if (preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    if (preg_match_all('/\[(.*?)\]/s', $match, $textMatches)) {
                        $text .= ' ' . implode(' ', $textMatches[1]);
                    }
                }
            }
        }
        
        // Clean up
        $text = preg_replace('/[^\x20-\x7E\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Extract from Office XML formats (DOCX, XLSX, PPTX)
     */
    private function extractFromOfficeXML($filePath, $mimeType) {
        $text = '';
        
        try {
            // Check if ZipArchive is available
            if (!class_exists('ZipArchive')) {
                throw new Exception('ZipArchive extension not available. Please enable php_zip extension.');
            }
            
            $zip = new ZipArchive();
            $opened = $zip->open($filePath);
            
            if ($opened !== true) {
                throw new Exception('Failed to open file as ZIP archive. Error code: ' . $opened);
            }
            
            if (strpos($mimeType, 'wordprocessingml') !== false) {
                // DOCX
                $text = $this->extractFromDOCX($zip);
            } elseif (strpos($mimeType, 'spreadsheetml') !== false) {
                // XLSX
                $text = $this->extractFromXLSX($zip);
            } elseif (strpos($mimeType, 'presentationml') !== false) {
                // PPTX
                $text = $this->extractFromPPTX($zip);
            }
            
            $zip->close();
            
            if (empty($text)) {
                throw new Exception('No text content found in Office document');
            }
            
        } catch (Exception $e) {
            throw new Exception('Office document extraction failed: ' . $e->getMessage());
        }
        
        return trim($text);
    }
    
    /**
     * Extract from DOCX
     */
    private function extractFromDOCX($zip) {
        $text = '';
        
        // Try to get the main document content
        $content = $zip->getFromName('word/document.xml');
        
        if (!$content) {
            throw new Exception('Could not find word/document.xml in DOCX file');
        }
        
        // Disable XML errors temporarily
        libxml_use_internal_errors(true);
        
        $xml = simplexml_load_string($content);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Exception('Failed to parse DOCX XML: ' . (isset($errors[0]) ? $errors[0]->message : 'Unknown error'));
        }
        
        // Register namespace
        $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Extract all text nodes
        $texts = $xml->xpath('//w:t');
        
        if ($texts && count($texts) > 0) {
            foreach ($texts as $t) {
                $text .= (string)$t . ' ';
            }
        } else {
            // Fallback: try to extract without namespace
            $text = strip_tags($content);
        }
        
        return $text;
    }
    
    /**
     * Extract from XLSX
     */
    private function extractFromXLSX($zip) {
        $text = '';
        $content = $zip->getFromName('xl/sharedStrings.xml');
        
        if ($content) {
            $xml = simplexml_load_string($content);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $text .= (string)$si->t . ' ';
                    }
                }
            }
        }
        
        return $text;
    }
    
    /**
     * Extract from PPTX
     */
    private function extractFromPPTX($zip) {
        $text = '';
        
        // Try to extract from up to 100 slides
        for ($i = 1; $i <= 100; $i++) {
            $content = $zip->getFromName("ppt/slides/slide{$i}.xml");
            if ($content) {
                $xml = simplexml_load_string($content);
                if ($xml) {
                    $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                    $texts = $xml->xpath('//a:t');
                    foreach ($texts as $t) {
                        $text .= (string)$t . ' ';
                    }
                }
            } else {
                break; // No more slides
            }
        }
        
        return $text;
    }
    
    /**
     * Extract from old Office formats
     */
    private function extractFromOldOffice($filePath) {
        $content = file_get_contents($filePath);
        
        // Remove binary data and extract readable text
        $text = preg_replace('/[^\x20-\x7E\s]/', ' ', $content);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Limit length
        return trim(substr($text, 0, 50000));
    }
}
?>
