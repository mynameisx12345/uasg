# 🤖 ML Classification System - Complete Integration Guide

## ✅ How It Works

### Automatic File Categorization Flow

When you upload a file to the system, here's what happens:

```
1. User uploads file → 
2. System checks ML settings → 
3. Classification happens → 
4. File is categorized and stored
```

### Classification Methods

The system now supports **4 classification methods** (configured in ML Settings):

#### 1️⃣ **NLP Cloud Only** (`nlpcloud`)
- Uses NLP Cloud API for categorization
- Cost: Uses API credits per file
- Accuracy: Good for general categories
- Speed: ~2-5 seconds

#### 2️⃣ **Google NLP Only** (`google_nlp`)
- Uses Google Cloud Natural Language API
- Cost: Free tier available
- Accuracy: Good for entity extraction
- Speed: ~1-3 seconds

#### 3️⃣ **Custom ML Only** (`custom_ml`)
- Uses your trained ML model
- Cost: **FREE** (no API calls)
- Accuracy: High for your specific categories
- Speed: **~0.1-0.5 seconds** (very fast!)
- ⚠️ Requires: Trained and activated model

#### 4️⃣ **Hybrid** (Recommended) (`hybrid`)
- **First**: Try your custom ML model
- **Then**: If ML confidence < threshold, use NLP Cloud
- Cost: Minimal API usage (only when ML is uncertain)
- Accuracy: Best of both worlds
- Speed: Fast when ML works, slower when falls back

---

## 🎯 ML Classification Process

### Step-by-Step Breakdown:

**When a file is uploaded:**

1. **Extract Text** from the uploaded document (PDF, DOCX, etc.)

2. **Check ML Settings:**
   - Classification Method: `custom_ml` or `hybrid`?
   - ML Confidence Threshold: e.g., `60%`
   - NLP Fallback Enabled: `Yes/No`

3. **ML Prediction** (if ML is enabled):
   ```
   Input: "RESOLUTION NO. 2025-001 WHEREAS the University..."
   
   ML Model Predicts:
   - Category: "Resolution"
   - Confidence: 95.2%
   - Alternative predictions: Amendment (3.1%), Memorandum (1.2%)
   ```

4. **Decision Logic:**
   ```
   IF confidence >= threshold (95.2% >= 60%):
       ✅ USE ML PREDICTION
       - Category: "Resolution"
       - Method: "custom_ml"
       - Model ID: 5
   
   ELSE IF fallback enabled:
       ⚠️ FALLBACK TO NLP CLOUD
       - Use NLP Cloud API
       - Method: "hybrid_nlp_fallback"
   
   ELSE:
       ❌ USE ML ANYWAY
       - Even if low confidence
   ```

5. **Store Results:**
   ```sql
   INSERT INTO file_upload_tbl (
       category_tag = 'Resolution',
       category_score = 0.952,
       classification_method = 'custom_ml',
       ml_model_id = 5
   )
   ```

6. **Track Prediction** (for analytics):
   ```sql
   INSERT INTO ml_prediction_history_tbl (
       model_id = 5,
       file_upload_id = 123,
       predicted_category = 'Resolution',
       confidence_score = 0.952,
       prediction_time_ms = 124
   )
   ```

---

## 📊 Example Scenarios

### Scenario 1: High Confidence ML Prediction ✅

**Settings:**
- Method: `hybrid`
- Threshold: `60%`
- Fallback: `Enabled`

**File Upload:** "RESOLUTION NO. 2025-045..."

**Process:**
1. Extract text: ✅ Success (2,543 words)
2. ML prediction: ✅ "Resolution" (97.8% confidence)
3. Check threshold: 97.8% >= 60% ✅
4. **Result: Use ML prediction**
5. **Cost: $0 (no API call)**
6. **Time: 0.2 seconds**

---

### Scenario 2: Low Confidence - Fallback to NLP ⚠️

**Settings:**
- Method: `hybrid`
- Threshold: `60%`
- Fallback: `Enabled`

**File Upload:** "Regarding the proposed changes to..."

**Process:**
1. Extract text: ✅ Success (421 words)
2. ML prediction: ⚠️ "Amendment" (52.3% confidence)
3. Check threshold: 52.3% < 60% ❌
4. Fallback enabled: ✅
5. **Fallback: Use NLP Cloud**
6. NLP prediction: ✅ "Memorandum" (85.4%)
7. **Result: Use NLP prediction**
8. **Cost: 1 NLP Cloud API call**
9. **Time: 3.1 seconds**

---

### Scenario 3: ML Only (No Fallback) 🚀

**Settings:**
- Method: `custom_ml`
- Threshold: `60%`
- Fallback: `Disabled`

**File Upload:** "LETTER to the Board of Regents..."

**Process:**
1. Extract text: ✅ Success (1,234 words)
2. ML prediction: ✅ "Letter" (72.1% confidence)
3. **Result: Use ML prediction**
4. **Cost: $0 (no API call)**
5. **Time: 0.15 seconds**

---

## ⚙️ Configuration Guide

### Recommended Settings:

**For Maximum Cost Savings:**
```
Classification Method: Hybrid
ML Confidence Threshold: 50%
NLP Fallback: Enabled
```
✅ Most files categorized with ML (free)
✅ NLP Cloud only for uncertain cases
✅ Good accuracy

**For Best Accuracy:**
```
Classification Method: Hybrid
ML Confidence Threshold: 70%
NLP Fallback: Enabled
```
✅ High confidence in predictions
✅ More NLP Cloud usage for edge cases
⚠️ Higher API costs

**For Fastest Speed:**
```
Classification Method: Custom ML Only
ML Confidence Threshold: 40%
NLP Fallback: Disabled
```
✅ Always uses ML (0.1-0.5s)
✅ No API calls
⚠️ May have lower accuracy for uncommon documents

---

## 📈 Improving ML Accuracy

### Training Tips:

**1. Collect Real Examples**
Export your existing categorized files:
```sql
SELECT original_filename, category_tag, extracted_text 
FROM file_upload_tbl f
JOIN file_nlp_analysis_tbl n ON f.file_upload_id = n.file_upload_id
WHERE category_tag IN ('Resolution', 'Amendment', 'Memorandum')
LIMIT 50 PER category;
```

**2. Create Balanced Dataset**
```csv
text,category
"RESOLUTION NO. 2025-001...",Resolution
"RESOLUTION NO. 2025-002...",Resolution
"RESOLUTION NO. 2025-003...",Resolution
... (at least 20-30 per category)

"AMENDMENT to Article...",Amendment
"AMENDMENT Section 3...",Amendment
... (at least 20-30 per category)
```

**3. Train Multiple Models**
- Train with SVM: Good for general use
- Train with Naive Bayes: Fast, good for large datasets
- Train with Random Forest: Highest accuracy

**4. Compare and Activate Best**
Look at the metrics:
- **Accuracy**: Overall correctness (aim for >80%)
- **Precision**: When it predicts a category, how often is it right? (aim for >85%)
- **Recall**: How many actual documents of each category did it find? (aim for >80%)
- **F1 Score**: Balance of precision and recall (aim for >80%)

---

## 🔍 Monitoring ML Performance

### Check Prediction History:

```sql
SELECT 
    m.model_name,
    COUNT(*) as total_predictions,
    AVG(confidence_score) as avg_confidence,
    AVG(prediction_time_ms) as avg_time_ms,
    SUM(CASE WHEN was_accepted THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN was_accepted THEN 0 ELSE 1 END) as rejected
FROM ml_prediction_history_tbl h
JOIN ml_models_tbl m ON h.model_id = m.model_id
WHERE h.predicted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY m.model_id;
```

### Red Flags:
- **Low average confidence (<60%)**: Retrain with more samples
- **High rejection rate**: Model not fitting your documents well
- **Long prediction time (>1s)**: Model too complex or Python slow

---

## 🛠️ Troubleshooting

### "ML prediction failed"
**Cause:** Python not installed or ML model not trained
**Fix:** 
1. Install Python: `pip install -r requirements.txt`
2. Train a model in ML Management page
3. Activate the trained model

### "Classification method: hybrid_nlp_fallback"
**Cause:** ML confidence was below threshold
**Fix:** 
- Lower the confidence threshold (e.g., 50% instead of 60%)
- OR retrain model with more examples
- This is normal for documents outside your training data

### Files always using NLP Cloud
**Cause:** No active ML model
**Fix:**
1. Go to ML Management
2. Check if a model is Active (green checkmark)
3. If not, click "Activate" on your best model

---

## 💡 Best Practices

1. **Start with Hybrid mode** - Safe fallback while you build your dataset

2. **Train with real data** - Use actual documents from your system

3. **Aim for 30+ samples per category** - More data = better accuracy

4. **Retrain periodically** - Add new examples every few months

5. **Monitor fallback rate** - If >50% files fall back to NLP, retrain

6. **Test before going ML-only** - Use Hybrid first, then switch to Custom ML when confident

---

## 📊 Database Schema Reference

### file_upload_tbl (Updated)
```sql
classification_method VARCHAR(50)    -- 'custom_ml', 'nlpcloud', 'hybrid', 'hybrid_nlp_fallback'
ml_model_id INT                      -- ID of ML model used (if applicable)
```

### ml_prediction_history_tbl
```sql
prediction_id INT AUTO_INCREMENT
model_id INT                         -- Which model made the prediction
file_upload_id INT                   -- Which file was categorized
predicted_category VARCHAR(255)      -- What the model predicted
confidence_score DECIMAL(5,4)        -- How confident (0.0000-1.0000)
prediction_time_ms INT               -- How long it took
was_accepted BOOLEAN                 -- Was this prediction accepted?
actual_category VARCHAR(255)         -- What was the actual category (for training)
predicted_at DATETIME                -- When the prediction happened
```

---

## 🎓 Summary

**Before ML:**
- Every file → NLP Cloud API call ($$$)
- 2-5 seconds per file
- Limited to predefined categories

**After ML:**
- Most files → Your ML model (FREE ✨)
- 0.1-0.5 seconds per file
- Custom categories trained on YOUR documents
- NLP Cloud only for uncertain cases
- Save money + Faster processing + Better accuracy

**Your trained ML model learns from YOUR documents and categorizes new files automatically!**

---

**Ready to try it?**
1. Access: `http://localhost/uasg/admin/ml-management.php`
2. Upload training CSV
3. Train a model
4. Activate it
5. Upload a test file and watch it get categorized automatically! 🚀
