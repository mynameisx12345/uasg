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
            if (strpos($mimeType, 'text/') === 0 || $mimeType === 'text/csv' || $mimeType === 'application/csv') {
                $text = $this->extractFromText($filePath);
            } elseif ($mimeType === 'application/pdf') {
                $text = $this->extractFromPDF($filePath);
            } elseif (strpos($mimeType, 'application/vnd.openxmlformats') === 0) {
                $text = $this->extractFromOfficeXML($filePath, $mimeType);
            } elseif (strpos($mimeType, 'application/msword') === 0 || 
                      strpos($mimeType, 'application/vnd.ms-') === 0) {
                $text = $this->extractFromOldOffice($filePath);
            } else {
                // Check file extension for CSV (fallback detection)
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($extension === 'csv') {
                    $text = $this->extractFromCSV($filePath);
                } else {
                    // Try as text file for unknown types
                    $text = $this->extractFromText($filePath);
                }
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
     * Extract from CSV file
     * Converts CSV data into readable text format
     */
    private function extractFromCSV($filePath) {
        $text = '';
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new Exception("Failed to open CSV file");
        }
        
        $rowCount = 0;
        $maxRows = 1000; // Limit to prevent huge files from consuming too much memory
        
        while (($row = fgetcsv($handle)) !== false && $rowCount < $maxRows) {
            // Join all cells with space, filter empty values
            $rowText = implode(' ', array_filter($row, function($cell) {
                return !empty(trim($cell));
            }));
            
            if (!empty($rowText)) {
                $text .= $rowText . ' ';
            }
            $rowCount++;
        }
        
        fclose($handle);
        
        if (empty($text)) {
            throw new Exception("No data found in CSV file");
        }
        
        return trim($text);
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
     * Extracts text from Excel spreadsheet cells
     */
    private function extractFromXLSX($zip) {
        $text = '';
        
        // Extract from shared strings (most common cell data storage)
        $content = $zip->getFromName('xl/sharedStrings.xml');
        
        if ($content) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            libxml_clear_errors();
            
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $text .= (string)$si->t . ' ';
                    } elseif (isset($si->r)) {
                        // Rich text format
                        foreach ($si->r as $r) {
                            if (isset($r->t)) {
                                $text .= (string)$r->t . ' ';
                            }
                        }
                    }
                }
            }
        }
        
        // Also try to extract from worksheet data (inline strings and numeric values)
        for ($i = 1; $i <= 10; $i++) { // Check first 10 sheets
            $sheetContent = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if ($sheetContent) {
                // Extract inline string values
                if (preg_match_all('/<v>(.*?)<\/v>/s', $sheetContent, $matches)) {
                    foreach ($matches[1] as $value) {
                        if (!is_numeric($value)) { // Skip pure numbers to reduce noise
                            $text .= $value . ' ';
                        }
                    }
                }
            } else {
                break; // No more sheets
            }
        }
        
        return trim($text);
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
