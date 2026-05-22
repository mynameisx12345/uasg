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
    public function extractText($filePath, $mimeType = '') {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }
            
            // Check if file is readable
            if (!is_readable($filePath)) {
                throw new Exception("File not readable: {$filePath}");
            }

            // Normalise MIME type — browsers sometimes send application/octet-stream
            // for valid PDFs/Office docs, so also check file extension.
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (empty($mimeType) || $mimeType === 'application/octet-stream') {
                $mimeMap = [
                    'pdf'  => 'application/pdf',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'doc'  => 'application/msword',
                    'xls'  => 'application/vnd.ms-excel',
                    'ppt'  => 'application/vnd.ms-powerpoint',
                    'csv'  => 'text/csv',
                    'txt'  => 'text/plain',
                ];
                $mimeType = $mimeMap[$extension] ?? $mimeType;
            }
            
            $text = '';
            
            // Extract based on MIME type
            if ($mimeType === 'text/csv' || $mimeType === 'application/csv' || $extension === 'csv') {
                // CSV: use proper cell-aware extractor (not raw file_get_contents)
                $text = $this->extractFromCSV($filePath);
            } elseif (strpos($mimeType, 'text/') === 0) {
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
     * Tries pdftotext (Poppler) first, then falls back to pure-PHP extraction.
     */
    private function extractFromPDF($filePath) {
        // ── Method 1: pdftotext (Poppler) ────────────────────────────────────
        // Install Poppler for Windows: https://github.com/oschwartz10612/poppler-windows/releases
        // and add its bin/ folder to the system PATH.
        $pdftotextPath = $this->findPdftotext();
        if ($pdftotextPath) {
            $escapedFile = escapeshellarg($filePath);
            $cmd = "$pdftotextPath -enc UTF-8 -nopgbrk $escapedFile -";
            $output = shell_exec($cmd);
            if ($output !== null && strlen(trim($output)) >= 10) {
                return trim($output);
            }
        }

        // ── Method 2: Pure-PHP extraction ────────────────────────────────────
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Cannot read PDF file");
        }

        $text = '';

        // 2a. Decompress FlateDecode (zlib) streams — used by virtually all modern PDFs
        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $streamMatches)) {
            foreach ($streamMatches[1] as $stream) {
                // Only decompress if the stream is binary (not readable ASCII)
                if (!mb_check_encoding($stream, 'ASCII') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', $stream)) {
                    $decompressed = @gzuncompress($stream);
                    if ($decompressed === false) {
                        // Try raw inflate (no zlib header)
                        $decompressed = @gzinflate($stream);
                    }
                    if ($decompressed !== false) {
                        $stream = $decompressed;
                    }
                }
                // Extract parenthesised strings: (Hello World)
                if (preg_match_all('/\(([^()\\\\]*(?:\\\\.[^()\\\\]*)*)\)/s', $stream, $m)) {
                    $pieces = [];
                    foreach ($m[1] as $piece) {
                        // Unescape PDF string escape sequences
                        $piece = str_replace(
                            ['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'],
                            ["\n", "\r", "\t", '(', ')', '\\'],
                            $piece
                        );
                        // Filter printable UTF-8 / ASCII only
                        if (preg_match('/[A-Za-z0-9\s,.\-:;\'\"!?]/u', $piece)) {
                            $pieces[] = $piece;
                        }
                    }
                    if ($pieces) {
                        // Join with space, then fix single-char fragmentation immediately
                        $joined = implode(' ', $pieces);
                        $joined = $this->mergeFragmentedChars($joined);
                        $text .= $joined . ' ';
                    }
                }
                // Extract hex strings: <48656C6C6F>
                if (preg_match_all('/<([0-9A-Fa-f\s]+)>/', $stream, $hexMatches)) {
                    foreach ($hexMatches[1] as $hex) {
                        $hex = preg_replace('/\s+/', '', $hex);
                        if (strlen($hex) % 2 === 0) {
                            $decoded = hex2bin($hex);
                            if ($decoded !== false && preg_match('/[A-Za-z0-9\s,.\-]/u', $decoded)) {
                                $text .= $decoded . ' ';
                            }
                        }
                    }
                }
            }
        }

        // 2b. Fallback: search the raw binary for BT…ET text blocks
        if (strlen(trim($text)) < 30) {
            if (preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $btMatches)) {
                foreach ($btMatches[1] as $block) {
                    if (preg_match_all('/\(([^()]{1,200})\)/', $block, $pm)) {
                        // Join pieces with space, then fix single-char fragmentation
                        $blockText = implode(' ', $pm[1]);
                        $text .= $blockText . ' ';
                    }
                }
            }
        }

        // 2c. Last resort: strip all non-printable bytes from the whole file
        if (strlen(trim($text)) < 30) {
            $raw = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $content);
            // Keep runs of at least 4 printable chars
            preg_match_all('/[A-Za-z0-9 ,.\-:;\'"!?]{4,}/', $raw, $words);
            $text = implode(' ', $words[0]);
        }

        // Final cleanup
        $text = preg_replace('/\s+/', ' ', $text);

        // ── Post-process: merge character-by-character fragmentation ─────────
        // Microsoft Word PDFs often store each glyph individually, producing
        // "A c c o m p l i s h m e n t" instead of "Accomplishment".
        // Merge any run of 3+ single letters (or digits) separated by spaces.
        $text = $this->mergeFragmentedChars($text);

        return trim($text);
    }

    /**
     * Merge runs of single characters separated by spaces back into words.
     * e.g. "A c c o m p l i s h m e n t   R e p o r t" → "Accomplishment Report"
     */
    private function mergeFragmentedChars($text) {
        // A "fragmented word" is 3 or more single alphanumeric chars each separated
        // by exactly one space, e.g. "A c c o m p".
        // We use a callback so we can strip the internal spaces only.
        $text = preg_replace_callback(
            '/(?<![^\s])([A-Za-z0-9] ){3,}[A-Za-z0-9](?!\S)/',
            function ($m) {
                return str_replace(' ', '', $m[0]);
            },
            $text
        );

        // Clean up any double-spaces left after merging
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return $text;
    }

    /**
     * Locate the pdftotext binary (Poppler).
     * Returns the full path/command, or null if not found.
     */
    private function findPdftotext() {
        // Common Windows Poppler paths
        $candidates = [
            'pdftotext',                                       // in PATH
            'C:/Program Files/poppler/bin/pdftotext.exe',
            'C:/poppler/bin/pdftotext.exe',
            'C:/tools/poppler/bin/pdftotext.exe',
        ];

        foreach ($candidates as $candidate) {
            // Quick availability check
            if ($candidate === 'pdftotext') {
                $out = shell_exec('pdftotext -v 2>&1');
                if ($out !== null && stripos($out, 'poppler') !== false) {
                    return 'pdftotext';
                }
            } elseif (file_exists($candidate)) {
                return '"' . $candidate . '"';
            }
        }
        return null;
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
        
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            // Fallback: strip XML tags
            return trim(preg_replace('/\s+/', ' ', strip_tags($content)));
        }
        libxml_clear_errors();
        
        $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Process paragraph by paragraph so runs within a paragraph are joined
        // WITHOUT a separator (formatting boundaries can split a single word across
        // multiple <w:t> elements), while paragraphs are separated by a space.
        $paragraphs = $xml->xpath('//w:p');
        if ($paragraphs) {
            foreach ($paragraphs as $p) {
                $p->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $runs = $p->xpath('.//w:t');
                if ($runs) {
                    $paraText = implode('', array_map('strval', $runs));
                    if (!empty(trim($paraText))) {
                        $text .= $paraText . ' ';
                    }
                }
            }
        } else {
            // Fallback: strip XML tags
            $text = preg_replace('/\s+/', ' ', strip_tags($content));
        }
        
        return $text;
    }
    
    /**
     * Extract from XLSX
     * Extracts text from Excel spreadsheet cells
     */
    private function extractFromXLSX($zip) {
        $text = '';

        // ── 1. Shared strings ────────────────────────────────────────────────
        // Most Excel string cells store their value in xl/sharedStrings.xml.
        // Rich-text cells split the string across multiple <r><t> runs which
        // must be joined WITHOUT a separator (they form one cell value).
        $sharedStrings = [];
        $ssContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssContent) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($ssContent);
            libxml_clear_errors();

            if ($xml) {
                foreach ($xml->si as $si) {
                    $cellText = '';
                    if (isset($si->t)) {
                        // Plain string
                        $cellText = (string)$si->t;
                    } elseif (isset($si->r)) {
                        // Rich text: runs belong to the same cell — join without space
                        foreach ($si->r as $r) {
                            if (isset($r->t)) {
                                $cellText .= (string)$r->t;
                            }
                        }
                    }
                    $sharedStrings[] = $cellText;
                    if (!empty(trim($cellText))) {
                        $text .= $cellText . ' ';
                    }
                }
            }
        }

        // ── 2. Inline strings from worksheets ───────────────────────────────
        // Some cells use inline strings (<c t="inlineStr"><is><t>…</t></is></c>)
        // instead of shared strings.  The old <v> loop was wrong — <v> for string
        // cells holds a numeric shared-string index, so !is_numeric always skipped
        // them.  We now look for <is> (inline string) elements instead.
        for ($i = 1; $i <= 50; $i++) {
            $sheetContent = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if (!$sheetContent) break;

            libxml_use_internal_errors(true);
            $sheetXml = simplexml_load_string($sheetContent);
            libxml_clear_errors();
            if (!$sheetXml) continue;

            // Register the spreadsheetml namespace (may vary, grab it dynamically)
            $nsMap = $sheetXml->getNamespaces(true);
            $ssNs  = $nsMap[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $sheetXml->registerXPathNamespace('ss', $ssNs);

            // Inline strings: <c t="inlineStr"><is><t>text</t></is></c>
            $inlineNodes = $sheetXml->xpath('//ss:is//ss:t');
            if ($inlineNodes) {
                foreach ($inlineNodes as $t) {
                    $val = trim((string)$t);
                    if (!empty($val)) {
                        $text .= $val . ' ';
                    }
                }
            }
        }

        return trim($text);
    }
    
    /**
     * Extract from PPTX
     */
    private function extractFromPPTX($zip) {
        $text = '';
        $aNs = 'http://schemas.openxmlformats.org/drawingml/2006/main';

        // Helper: extract text from a slide/notes XML string, paragraph-aware
        $extractSlideText = function($xmlContent) use ($aNs) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            libxml_clear_errors();
            if (!$xml) return '';

            $xml->registerXPathNamespace('a', $aNs);

            // Process paragraph by paragraph — runs within a paragraph share the
            // same word (formatting splits can cut a word mid-letter), so join
            // runs WITHOUT space; paragraphs are separated by a space.
            $paragraphs = $xml->xpath('//a:p');
            $out = '';
            if ($paragraphs) {
                foreach ($paragraphs as $p) {
                    $p->registerXPathNamespace('a', $aNs);
                    $runs = $p->xpath('.//a:t');
                    if ($runs) {
                        $paraText = implode('', array_map('strval', $runs));
                        if (!empty(trim($paraText))) {
                            $out .= $paraText . ' ';
                        }
                    }
                }
            }
            return $out;
        };

        // ── Slides ───────────────────────────────────────────────────────────
        for ($i = 1; $i <= 200; $i++) {
            $content = $zip->getFromName("ppt/slides/slide{$i}.xml");
            if (!$content) break;
            $text .= $extractSlideText($content);
        }

        // ── Speaker notes (often contain the most descriptive text) ─────────
        for ($i = 1; $i <= 200; $i++) {
            $content = $zip->getFromName("ppt/notesSlides/notesSlide{$i}.xml");
            if (!$content) break;
            $text .= $extractSlideText($content);
        }

        return trim($text);
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
