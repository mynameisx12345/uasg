# Google Cloud Natural Language API Integration

## 📚 Overview

The UASG system now uses **Google Cloud Natural Language API** to automatically classify uploaded documents and tag them based on available keywords in your database.

## 🎯 Key Features

### Automatic File Classification
When a user uploads a document, the system:

1. **Extracts Text** from PDF, DOCX, XLSX, PPTX, and other formats
2. **Matches Keywords** against your `file_category_key_tbl` database
3. **Calculates Confidence** based on keyword frequency and relevance
4. **Auto-Assigns Category** if confidence threshold is met
5. **Stores Analysis Results** in `file_nlp_analysis_tbl`

### Smart Keyword Matching
- Exact keyword matches get **10x weight**
- Partial keyword matches get **5x weight**
- Confidence calculated as percentage of matched keywords
- Best matching category automatically suggested

### Supported File Types

✅ **Documents**: PDF, DOC, DOCX, RTF, TXT  
✅ **Spreadsheets**: XLS, XLSX  
✅ **Presentations**: PPT, PPTX  
✅ **Text**: HTML, Plain Text

## 🚀 Setup Instructions

### Step 1: Enable Google Cloud Natural Language API (Optional)

**Note**: The system works WITHOUT the Google API using keyword matching. The API is only needed for advanced entity extraction and content classification.

If you want to use Google Cloud API:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable the **Cloud Natural Language API**
4. Go to **APIs & Credentials**
5. Create an API key
6. Copy your API key

### Step 2: Configure the System

Edit `config/google_nlp_config.php`:

```php
return [
    // Your Google Cloud API key (optional - only needed for advanced features)
    'api_key' => 'YOUR_GOOGLE_CLOUD_API_KEY_HERE',
    
    // Enable NLP analysis
    'enabled' => true,
    
    // Use Google Cloud API (set to false to use only keyword matching - FREE)
    'use_google_api' => false,  // Keep false for keyword-only mode
    
    // Minimum confidence to auto-assign category (0-100)
    'min_confidence' => 30,
    
    // Enable automatic category assignment
    'auto_categorization' => true
];
```

### Step 3: Add Keywords to Categories

The system uses keywords from your database to classify files. Add keywords to the `file_category_key_tbl`:

```sql
-- Example: Add keywords for "Resolutions" category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(1, 'resolution'),
(1, 'motion'),
(1, 'vote'),
(1, 'council'),
(1, 'whereas'),
(1, 'resolved');

-- Example: Add keywords for "Amendments" category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(2, 'amendment'),
(2, 'change'),
(2, 'modify'),
(2, 'constitution'),
(2, 'bylaw'),
(2, 'article');

-- Example: Add keywords for "Minutes" category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(3, 'minutes'),
(3, 'meeting'),
(3, 'attendance'),
(3, 'agenda'),
(3, 'motion carried'),
(3, 'discussion');

-- Example: Add keywords for "Letters" category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(4, 'dear'),
(4, 'sincerely'),
(4, 'letter'),
(4, 'respectfully'),
(4, 'yours truly');
```

## 💻 How It Works

### Automatic Classification on Upload

When a file is uploaded:

```
1. File Upload → Extract Text → Match Keywords → Calculate Confidence
                                                          ↓
2. If confidence ≥ min_confidence → Auto-assign category
                                                          ↓
3. Store analysis results in database
```

### Keyword Matching Algorithm

```php
// For each category:
foreach ($categories as $category) {
    $score = 0;
    
    foreach ($keywords as $keyword) {
        // Exact word match: +10 points per occurrence
        if (word appears exactly in text) {
            $score += count * 10;
        }
        
        // Partial match: +5 points
        if (keyword found anywhere in text) {
            $score += 5;
        }
    }
    
    // Calculate confidence percentage
    $confidence = ($score / (total_keywords * 10)) * 100;
}

// Category with highest score wins!
```

### Example Classification

**Document Content:**
```
"WHEREAS the Student Council recognizes the need for change,
BE IT RESOLVED that this motion be carried forward..."
```

**Keyword Matches:**
- "council" (exact match) → +10 points
- "resolution" (partial in "resolved") → +5 points  
- "motion" (exact match) → +10 points
- "whereas" (exact match) → +10 points

**Result:** Category "Resolutions" with 70% confidence ✓

## 📊 Database Structure

### `file_nlp_analysis_tbl`

Stores classification results:

```sql
CREATE TABLE file_nlp_analysis_tbl (
    analysis_id INT AUTO_INCREMENT PRIMARY KEY,
    file_upload_id INT NOT NULL,
    extracted_text LONGTEXT,
    word_count INT,
    suggested_category VARCHAR(255),
    category_confidence DECIMAL(5,2),  -- 0-100
    keywords JSON,                      -- Matched keywords
    entities JSON,                      -- Google NLP entities (if API used)
    full_analysis JSON,                 -- Complete results
    processing_time_ms INT,
    analyzed_at TIMESTAMP,
    FOREIGN KEY (file_upload_id) REFERENCES file_upload_tbl(file_upload_id)
);
```

### `file_category_key_tbl`

Stores keywords for each category:

```sql
CREATE TABLE file_category_key_tbl (
    file_category_key_id INT AUTO_INCREMENT PRIMARY KEY,
    file_category_id INT NOT NULL,
    keyword VARCHAR(250) NOT NULL,
    FOREIGN KEY (file_category_id) REFERENCES file_category_tbl(file_category_id)
);
```

## 🔧 API Endpoints

### AJAX Call 62: Get NLP Analysis

Retrieve classification results for a file:

```javascript
$.ajax({
    url: 'ajax.php',
    type: 'POST',
    data: {
        CALL: 62,
        file_id: fileId
    },
    success: function(response) {
        if (response.status === 'SUCCESS') {
            console.log('Category:', response.data.suggested_category);
            console.log('Confidence:', response.data.category_confidence + '%');
            console.log('Matched Keywords:', response.data.keywords);
        }
    }
});
```

### Example Response

```json
{
    "status": "SUCCESS",
    "data": {
        "analysis_id": 1,
        "file_upload_id": 123,
        "extracted_text": "WHEREAS the Student Council...",
        "word_count": 245,
        "suggested_category": "Resolutions",
        "category_confidence": 85.50,
        "keywords": [
            "resolution",
            "motion",
            "vote",
            "council",
            "whereas"
        ],
        "processing_time_ms": 324,
        "analyzed_at": "2025-11-18 10:30:45"
    }
}
```

## 🎨 Integration Examples

### Display Classification Results

```javascript
function showFileClassification(fileId) {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { CALL: 62, file_id: fileId },
        success: function(response) {
            if (response.status === 'SUCCESS') {
                const data = response.data;
                
                $('#suggestedCategory').text(data.suggested_category);
                $('#confidence').text(data.category_confidence.toFixed(1) + '%');
                
                // Display matched keywords
                const keywordHTML = data.keywords.map(kw => 
                    `<span class="keyword-badge">${kw}</span>`
                ).join(' ');
                $('#matchedKeywords').html(keywordHTML);
                
                // Show confidence bar
                $('#confidenceBar').css('width', data.category_confidence + '%');
            }
        }
    });
}
```

### Add to File Upload Form

In your dashboard, show classification preview:

```html
<div class="classification-preview">
    <h4>📁 Auto-Classification</h4>
    <div class="preview-item">
        <strong>Suggested Category:</strong>
        <span id="suggestedCategory">-</span>
    </div>
    <div class="preview-item">
        <strong>Confidence:</strong>
        <span id="confidence">-</span>
        <div class="confidence-bar">
            <div id="confidenceBar" class="confidence-fill"></div>
        </div>
    </div>
    <div class="preview-item">
        <strong>Matched Keywords:</strong>
        <div id="matchedKeywords"></div>
    </div>
</div>
```

## 🔐 Configuration Options

### Keyword-Only Mode (FREE)

```php
'use_google_api' => false,  // No API costs
'enabled' => true,
'auto_categorization' => true,
'min_confidence' => 30
```

**Pros:**
- ✅ Completely FREE
- ✅ Fast processing
- ✅ No API limits
- ✅ Works offline

**Cons:**
- ❌ Relies on keyword database
- ❌ No entity extraction
- ❌ No advanced NLP features

### Google API Mode (Advanced)

```php
'use_google_api' => true,   // Uses API quota
'api_key' => 'YOUR_KEY',
'enabled' => true
```

**Pros:**
- ✅ Advanced entity recognition
- ✅ Content classification
- ✅ Language detection
- ✅ Sentiment analysis

**Cons:**
- ❌ Costs money (pay per request)
- ❌ Requires internet
- ❌ API quota limits

## 📈 Best Practices

### 1. Add Comprehensive Keywords

```sql
-- Good: Multiple related keywords
INSERT INTO file_category_key_tbl VALUES
(1, 'resolution'), (1, 'resolve'), (1, 'resolved'),
(1, 'whereas'), (1, 'therefore'), (1, 'motion');

-- Better: Include variations and synonyms
INSERT INTO file_category_key_tbl VALUES
(1, 'resolution'), (1, 'resolutions'), 
(1, 'motion'), (1, 'motions'),
(1, 'vote'), (1, 'voting'), (1, 'voted');
```

### 2. Set Appropriate Confidence Threshold

```php
// Conservative (high accuracy, fewer auto-assignments)
'min_confidence' => 70,

// Balanced (good accuracy, moderate auto-assignments)
'min_confidence' => 50,

// Aggressive (more auto-assignments, lower accuracy)
'min_confidence' => 30,
```

### 3. Monitor and Improve

```sql
-- Check classification accuracy
SELECT 
    suggested_category,
    AVG(category_confidence) as avg_confidence,
    COUNT(*) as total_files
FROM file_nlp_analysis_tbl
GROUP BY suggested_category;

-- Find low-confidence classifications
SELECT * FROM file_nlp_analysis_tbl 
WHERE category_confidence < 50
ORDER BY analyzed_at DESC;
```

## 🐛 Troubleshooting

### No Category Suggested

**Cause**: No keywords matched  
**Solution**: Add more keywords to your categories

```sql
-- Check which keywords you have
SELECT fc.file_category, COUNT(fck.keyword) as keyword_count
FROM file_category_tbl fc
LEFT JOIN file_category_key_tbl fck ON fc.file_category_id = fck.file_category_id
GROUP BY fc.file_category_id;
```

### Low Confidence Scores

**Cause**: Too few keywords or very generic keywords  
**Solution**: Add specific, unique keywords for each category

### Text Extraction Fails

**Cause**: Unsupported file format or corrupted file  
**Solution**: System will use filename-based classification as fallback

## 📝 Notes

- Classification happens **automatically** on file upload
- Works **without Google API** using keyword matching
- Results are **cached** in database (no re-processing)
- **Fallback**: Uses filename if text extraction fails
- **Non-blocking**: Upload succeeds even if classification fails

## 🎓 Use Cases

1. **Automatic Organization**: Files sorted into categories automatically
2. **Smart Search**: Find files by matched keywords
3. **Quality Control**: Review low-confidence classifications
4. **Analytics**: Track most common file types
5. **Workflow**: Route files based on category

---

**Created**: November 18, 2025  
**Version**: 2.0 (Google Cloud NLP)  
**Status**: Active Integration (Keyword-Based)
