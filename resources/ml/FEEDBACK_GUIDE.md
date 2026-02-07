# 🔍 ML Feedback Review System - User Guide

## 📋 Overview

The ML Feedback Review system allows administrators to review ML predictions, accept/reject them, and use accepted predictions to continuously improve the ML models.

---

## 🚀 Quick Start

### Access the Feedback Page

**URL:** `http://localhost/uasg/admin/ml-feedback.php`

**Or:** Click **"🔍 ML Feedback Review"** in the admin sidebar

---

## 📊 Dashboard Overview

### Statistics Cards (Top)

| Card | Description |
|------|-------------|
| **Total Predictions** | All ML predictions in last 30 days |
| **Accepted** | Predictions marked as correct |
| **Rejected** | Predictions marked as incorrect |
| **Pending Review** | Predictions awaiting your review |

---

## 🔍 Filters

### Status Filter
- **Pending Review** - Not yet reviewed (default)
- **Accepted** - Marked as correct
- **Rejected** - Marked as incorrect
- **All** - Show everything

### Confidence Level Filter
- **All Levels** - Show all predictions
- **High (≥80%)** - Most confident predictions
- **Medium (60-80%)** - Moderately confident
- **Low (<60%)** - Less confident predictions

### Category Filter
- Filter by specific predicted category
- Shows only categories that have predictions

### Date Range Filter
- **Last 7 Days** - Recent predictions
- **Last 30 Days** - Monthly view (default)
- **Last 90 Days** - Quarterly view
- **All Time** - Everything

---

## ✅ Review Actions

### For Each Prediction:

#### 1. Accept Prediction ✅
**When to use:** ML predicted correctly

**What happens:**
- Marks prediction as `was_accepted = 1`
- Stores the category as `actual_category`
- Available for export and retraining

**Example:**
```
File: resolution_2025_050.pdf
ML Predicted: "Resolution" (94.2%)
Text: "RESOLUTION NO. 2025-050 WHEREAS..."

Action: Click "Accept" ✅
Result: Added to training pool
```

---

#### 2. Reject Prediction ❌
**When to use:** ML predicted incorrectly and you don't want to fix it

**What happens:**
- Marks prediction as `was_accepted = 0`
- Will NOT be included in training data
- Useful for filtering out bad predictions

**Example:**
```
File: random_note.pdf
ML Predicted: "Resolution" (52.3%)
Text: "Just a random note about lunch..."

Action: Click "Reject" ❌
Result: Excluded from training
```

---

#### 3. Edit Category 📝
**When to use:** ML predicted wrong category, but you want to correct it

**What happens:**
- Opens modal to enter correct category
- Marks as accepted with corrected category
- Updates file's actual category in database
- Used for retraining with correct label

**Example:**
```
File: memo_2025_010.pdf
ML Predicted: "Resolution" (67.8%)
Text: "MEMORANDUM regarding new policies..."

Action: Click "Edit Category" 📝
Enter: "Memorandum"
Result: Accepted as "Memorandum" for training
```

---

## 🎯 Bulk Actions

### 1. Export Accepted as CSV
**Button:** "📥 Export Accepted as CSV (X items)"

**What it does:**
- Downloads CSV file with all accepted predictions
- Format: `text,category`
- Filename: `ml_feedback_YYYY-MM-DD.csv`

**Use case:**
- Manual review of accepted data
- Backup of training data
- Combine with other datasets

**Example CSV Output:**
```csv
text,category
"RESOLUTION NO. 2025-050 WHEREAS the Board...","Resolution"
"AMENDMENT to Article III Section 2...","Amendment"
"MEMORANDUM regarding the implementation...","Memorandum"
```

---

### 2. Retrain with Accepted Data
**Button:** "🔄 Retrain with Accepted Data"

**What it does:**
1. Collects all accepted predictions
2. Creates new training dataset automatically
3. Trains new ML model
4. Activates the new model
5. Redirects to ML Management page

**Process:**
```
Accepted Predictions
    ↓
Generate CSV automatically
    ↓
Create dataset "Feedback Training Data YYYY-MM-DD"
    ↓
Train model "Feedback Model YYYY-MM-DD HH:MM"
    ↓
Activate new model
    ↓
Old model deactivated, new model now predicts
```

**Requirements:**
- At least 10 accepted predictions (recommended)
- Predictions must have extracted text

**Result:**
- New model with improved accuracy
- Uses default algorithm from ML Settings
- 20% test split automatically

---

### 3. Accept All High Confidence
**Button:** "✅ Accept All High Confidence (≥90%)"

**What it does:**
- Automatically accepts all pending predictions with ≥90% confidence
- Bulk operation - saves time
- Good for high-quality predictions

**Example:**
```
Pending Predictions:
- File 1: Resolution (95.2%) ← Accepted
- File 2: Amendment (92.1%) ← Accepted
- File 3: Memorandum (88.3%) ← Not accepted (< 90%)
- File 4: Resolution (91.8%) ← Accepted

Result: 3 predictions accepted automatically
```

---

## 📈 Workflow Examples

### Scenario 1: Weekly Review

**Monday morning:**
1. Go to ML Feedback page
2. Filter: Status = "Pending", Date = "Last 7 Days"
3. Click "Accept All High Confidence (≥90%)" - 15 accepted
4. Review remaining medium confidence (60-80%)
5. Accept good ones, reject bad ones, edit wrong categories
6. Total: 25 predictions reviewed

**End of Month:**
1. Filter: Status = "Accepted", Date = "Last 30 Days"
2. Click "Retrain with Accepted Data"
3. Wait for training to complete (~2-5 minutes)
4. New model activated with 100+ new examples
5. Improved accuracy from 85% → 91%!

---

### Scenario 2: Quality Control

**Find low-quality predictions:**
1. Filter: Confidence = "Low (<60%)"
2. Review each prediction
3. Most are incorrect → Reject all
4. This filters them out of training data

**Find high-quality predictions:**
1. Filter: Confidence = "High (≥80%)"
2. Most are correct → Accept All
3. These improve your model quickly

---

### Scenario 3: Category-Specific Review

**Focus on one category:**
1. Filter: Category = "Resolution"
2. Review all Resolution predictions
3. Accept correct ones, reject/edit wrong ones
4. Repeat for other categories

**Benefits:**
- Systematic review
- Ensure category consistency
- Find patterns in predictions

---

## 🎓 Best Practices

### 1. Regular Reviews
✅ **Do:** Review weekly
✅ **Do:** Accept high-confidence predictions quickly
✅ **Do:** Fix wrong categories instead of rejecting
❌ **Don't:** Let pending pile up for months

### 2. Quality over Quantity
✅ **Do:** Only accept truly correct predictions
✅ **Do:** Edit categories when ML is close but wrong
✅ **Do:** Reject clearly bad predictions
❌ **Don't:** Accept everything blindly

### 3. Retrain Periodically
✅ **Do:** Retrain monthly with 50+ accepted predictions
✅ **Do:** Check new model accuracy after retraining
✅ **Do:** Keep old models as backup
❌ **Don't:** Retrain with only 5-10 predictions

### 4. Monitor Patterns
✅ **Do:** Notice which categories have low confidence
✅ **Do:** Add more training examples for weak categories
✅ **Do:** Track accuracy improvements over time
❌ **Don't:** Ignore consistent prediction errors

---

## 📊 Performance Tracking

### Check Your Improvement

**Initial Model (Month 1):**
```
Training samples: 150 (50 per category)
Accuracy: 85%
Accepted predictions: 0
```

**After 1 Month:**
```
Filter: Status = Accepted, Date = Last 30 Days
Accepted: 75 predictions
Click "Retrain with Accepted Data"
```

**New Model (Month 2):**
```
Training samples: 225 (150 original + 75 feedback)
Accuracy: 89% ← Improved!
```

**After 3 Months:**
```
Training samples: 375 (continuous feedback)
Accuracy: 93% ← Even better!
```

---

## 🔧 Troubleshooting

### "No predictions to export"
**Cause:** No accepted predictions
**Fix:** Accept some predictions first using Accept button

### "Retraining failed"
**Cause:** Not enough accepted predictions (< 10)
**Fix:** Accept more predictions before retraining

### "Export shows same data multiple times"
**Cause:** Accepted same prediction twice
**Fix:** Normal behavior - each acceptance is recorded

### Predictions not showing
**Cause:** No files uploaded yet, or filters too restrictive
**Fix:** 
- Upload some files first
- Change filters to "All" / "All Time"

---

## 💡 Advanced Tips

### 1. Progressive Training
```
Week 1: Accept 20 high-confidence predictions
Week 2: Accept 30 more
Week 3: Accept 25 more
Week 4: Retrain with 75 total → Better model!
```

### 2. Category Balance
Make sure to accept predictions from ALL categories:
```
✅ Good:
- Resolution: 25 accepted
- Amendment: 22 accepted
- Memorandum: 20 accepted

❌ Bad:
- Resolution: 60 accepted
- Amendment: 3 accepted
- Memorandum: 2 accepted
```

### 3. Export for Backup
- Export accepted predictions monthly
- Store CSV files as backup
- Can manually combine with original training data

### 4. A/B Testing
1. Keep old model active
2. Retrain with feedback → creates new model
3. Compare accuracy metrics
4. Activate better model

---

## 📋 Database Tables

### ml_prediction_history_tbl Updates

| Column | Before Review | After Accept | After Reject |
|--------|---------------|--------------|--------------|
| `was_accepted` | NULL | 1 | 0 |
| `actual_category` | NULL | Category name | NULL |

**Query to check your progress:**
```sql
SELECT 
    DATE(predicted_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN was_accepted = 1 THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN was_accepted = 0 THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN was_accepted IS NULL THEN 1 ELSE 0 END) as pending
FROM ml_prediction_history_tbl
WHERE predicted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(predicted_at)
ORDER BY date DESC;
```

---

## 🎯 Success Metrics

### Track These:

1. **Acceptance Rate**
   - Target: > 70% of predictions accepted
   - Formula: `(Accepted / Total) * 100`

2. **High Confidence Rate**
   - Target: > 60% predictions with ≥80% confidence
   - Shows model quality

3. **Model Improvement**
   - Track accuracy after each retrain
   - Target: +3-5% accuracy per retrain

4. **Review Speed**
   - Target: Review within 7 days
   - Keeps feedback loop fast

---

## 🚀 Summary

**The ML Feedback System allows you to:**

✅ **Review** ML predictions easily
✅ **Accept** correct predictions for training
✅ **Reject** incorrect predictions
✅ **Edit** wrong categories to correct them
✅ **Export** accepted data as CSV
✅ **Retrain** automatically with feedback
✅ **Improve** model accuracy continuously

**Result:** Your ML model learns from real usage and gets smarter over time!

**Access:** `http://localhost/uasg/admin/ml-feedback.php`

---

**Happy reviewing! 🎉**
