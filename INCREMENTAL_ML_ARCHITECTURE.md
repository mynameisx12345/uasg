# 🎨 Incremental ML Learning - Visual Architecture

## 📊 System Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                    INCREMENTAL ML LEARNING SYSTEM                    │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│ 1. USER      │
│ UPLOADS FILE │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ 2. TEXT EXTRACTION   │
│ ├─ PDF → Text        │
│ ├─ DOCX → Text       │
│ ├─ XLSX → Text       │
│ └─ CSV → Text        │
└──────┬───────────────┘
       │
       ▼
┌────────────────────────────────────────┐
│ 3. ML PREDICTION                       │
│                                        │
│ Input: Extracted Text                  │
│ ↓                                      │
│ SGDClassifier (Incremental Model)      │
│ ↓                                      │
│ Output: Category + Confidence          │
│                                        │
│ Example:                               │
│ ┌────────────────────────────────────┐ │
│ │ Category: "Report"                 │ │
│ │ Confidence: 85%                    │ │
│ │ Status: High confidence ✓          │ │
│ └────────────────────────────────────┘ │
└──────┬─────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────┐
│ 4. USER INTERFACE                      │
│                                        │
│ ┌────────────────────────────────────┐ │
│ │ Suggested Category: Report ▼       │ │
│ │                                    │ │
│ │ Options:                           │ │
│ │ ○ Report (Suggested) 85% ✓         │ │
│ │ ○ Memorandum                       │ │
│ │ ○ Resolution                       │ │
│ │ ○ Amendment                        │ │
│ │ ○ Others                           │ │
│ │                                    │ │
│ │ [Upload] [Cancel]                  │ │
│ └────────────────────────────────────┘ │
└──────┬─────────────────────────────────┘
       │
       ├─── Confirms ✓ ──────┐
       │                     │
       └─── Corrects ⚠ ─────┤
                             ▼
┌────────────────────────────────────────────────────┐
│ 5. SAVE TO DATABASE                                │
│                                                    │
│ file_upload_tbl:                                   │
│ ├─ file_id: 123                                    │
│ ├─ filename: "Budget_Report_2026.pdf"              │
│ ├─ category: "Report" ← User's final choice        │
│ └─ uploaded_at: 2026-02-08 14:30:00               │
└──────┬─────────────────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────────────────┐
│ 6. QUEUE FOR LEARNING (NEW!)                       │
│                                                    │
│ ml_incremental_training_queue_tbl:                 │
│ ├─ queue_id: 45                                    │
│ ├─ file_upload_id: 123                             │
│ ├─ training_text: "2026 Budget Report..."          │
│ ├─ confirmed_category: "Report"                    │
│ ├─ prediction_confidence: 0.85                     │
│ ├─ status: "pending" ⏳                            │
│ └─ created_at: 2026-02-08 14:30:01                │
└──────┬─────────────────────────────────────────────┘
       │
       │ ⏳ Wait for 10 samples...
       │
       ▼
┌────────────────────────────────────────────────────┐
│ 7. BATCH THRESHOLD REACHED                         │
│                                                    │
│ Pending Samples: 10 ✓                              │
│                                                    │
│ Auto-trigger: processTrainingQueue(10)             │
└──────┬─────────────────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────────────────┐
│ 8. INCREMENTAL TRAINING (AUTOMATIC!)               │
│                                                    │
│ Python: IncrementalMLClassifier                    │
│ ├─ Load current model from disk                    │
│ ├─ Get 10 pending samples from queue              │
│ ├─ Transform texts to TF-IDF vectors              │
│ ├─ classifier.partial_fit(texts, categories)      │
│ ├─ Save updated model to disk                     │
│ └─ Update queue status: pending → processed       │
│                                                    │
│ Duration: ~1-2 seconds ⚡                           │
└──────┬─────────────────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────────────────┐
│ 9. MODEL UPDATED                                   │
│                                                    │
│ ml_models_tbl:                                     │
│ ├─ total_incremental_samples: 150 → 160 (+10)     │
│ ├─ last_incremental_update: 2026-02-08 14:35:00   │
│ └─ is_active: 1 ✓                                  │
│                                                    │
│ 🧠 Model is now SMARTER!                           │
└──────┬─────────────────────────────────────────────┘
       │
       │ REPEAT CYCLE
       │
       ▼
┌────────────────────────────────────────────────────┐
│ 10. NEXT UPLOAD                                    │
│                                                    │
│ Similar document → Better prediction!              │
│                                                    │
│ Before: "Report" 85%                               │
│ After:  "Report" 92% ⬆                             │
└────────────────────────────────────────────────────┘
```

---

## 🔄 Comparison: Traditional vs Incremental

### Traditional ML (Static Model)

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│ Train Once  │ --> │ Use Forever  │ --> │ No Improvement │
└─────────────┘     └──────────────┘     └─────────────┘
                                               │
                                               │ Need improvement?
                                               ▼
                                         ┌──────────────┐
                                         │ FULL RETRAIN │
                                         │ (Time-consuming) │
                                         └──────────────┘
```

### Incremental ML (Learning Model)

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ Initial Train│ --> │ Use + Learn  │ --> │ Auto-Update  │ --> │ Better & Better │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
                           │                      ▲                     │
                           │  Every 10 uploads    │                     │
                           └──────────────────────┘                     │
                                                                        │
                                                      CONTINUOUS LOOP <─┘
```

---

## 📊 Data Flow Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         DATABASE LAYER                           │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐
│  file_upload_tbl     │  │  ml_models_tbl       │  │  ml_incremental_     │
│                      │  │                      │  │  training_queue_tbl  │
├──────────────────────┤  ├──────────────────────┤  ├──────────────────────┤
│ file_id              │  │ model_id             │  │ queue_id             │
│ filename             │  │ model_name           │  │ file_upload_id (FK)  │
│ category ✓           │  │ is_incremental ✓     │  │ training_text        │
│ uploaded_at          │  │ total_incremental_   │  │ confirmed_category   │
│ uploaded_by          │  │   samples            │  │ confidence           │
└──────────────────────┘  │ last_incremental_    │  │ status (pending/     │
                          │   update             │  │  processed)          │
                          └──────────────────────┘  └──────────────────────┘
                                    │                        │
                                    │                        │
                                    ▼                        ▼
┌─────────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER (PHP)                     │
└─────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ IncrementalMLService                   │
├────────────────────────────────────────┤
│ + trainIncrementalModel()              │
│ + addTrainingSample()                  │
│ + processTrainingQueue()               │
│ + learnFromUpload()                    │
│ + getIncrementalStats()                │
└──────────┬─────────────────────────────┘
           │
           │ Calls Python via shell_exec
           │
           ▼
┌─────────────────────────────────────────────────────────────────┐
│                      ML LAYER (Python)                           │
└─────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ IncrementalMLClassifier                │
├────────────────────────────────────────┤
│ - vectorizer: TfidfVectorizer          │
│ - classifier: SGDClassifier            │
│ - categories: List[str]                │
│                                        │
│ + initial_train(csv_path)              │
│ + incremental_train(texts, categories) │ ← KEY METHOD!
│ + predict(text)                        │
│ + save_model(path)                     │
│ + load_model(path)                     │
└────────────────────────────────────────┘
           │
           │ Uses
           ▼
┌────────────────────────────────────────┐
│ Scikit-learn Libraries                 │
├────────────────────────────────────────┤
│ SGDClassifier                          │
│ ├─ loss='hinge' (SVM-like)             │
│ ├─ partial_fit() ← Incremental!        │
│ └─ warm_start=True                     │
│                                        │
│ TfidfVectorizer                        │
│ ├─ max_features=5000                   │
│ └─ ngram_range=(1,3)                   │
└────────────────────────────────────────┘
```

---

## 🎯 Queue Processing Flow

```
User uploads file
       │
       ▼
┌──────────────────┐
│ Queue Status:    │
│ Pending: 1       │
└──────────────────┘
       │
       │ (9 more uploads...)
       ▼
┌──────────────────┐
│ Queue Status:    │
│ Pending: 10 ✓    │
└──────────────────┘
       │
       │ AUTO-TRIGGER
       ▼
┌──────────────────────────────┐
│ processTrainingQueue(10)     │
│                              │
│ 1. SELECT * WHERE status=    │
│    'pending' LIMIT 10        │
│                              │
│ 2. Extract texts & categories│
│                              │
│ 3. Python: partial_fit()     │
│                              │
│ 4. UPDATE status='processed' │
│                              │
│ 5. UPDATE total_incremental_ │
│    samples += 10             │
└──────────────────────────────┘
       │
       ▼
┌──────────────────┐
│ Queue Status:    │
│ Pending: 0       │
│ Processed: 10    │
└──────────────────┘
       │
       │ (Cycle repeats)
       ▼
Next 10 uploads...
```

---

## 📈 Learning Improvement Timeline

```
Week 0: Initial Model Trained
│
│ Accuracy: 75%
│ Categories: 8
│ Samples: 100
│
├─── Week 1: 50 uploads confirmed
│    │
│    │ +5 batches processed
│    │ Model updated 5 times
│    │
│    ├─ Accuracy: 77% (+2%)
│    └─ Total samples: 150
│
├─── Week 2: 100 uploads confirmed
│    │
│    │ +10 batches processed
│    │ Model updated 10 times
│    │
│    ├─ Accuracy: 80% (+5%)
│    └─ Total samples: 200
│
├─── Month 1: 500 uploads confirmed
│    │
│    │ +50 batches processed
│    │ Model updated 50 times
│    │
│    ├─ Accuracy: 85% (+10%)
│    └─ Total samples: 600
│
└─── Month 3: 1500 uploads confirmed
     │
     │ +150 batches processed
     │ Model updated 150 times
     │
     ├─ Accuracy: 90% (+15%)
     └─ Total samples: 1600
     
     🎯 HIGHLY CUSTOMIZED TO YOUR DOCUMENTS!
```

---

## 🔧 Component Interaction Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                          FRONTEND                                │
└─────────────────────────────────────────────────────────────────┘
           │
           │ AJAX POST
           ▼
┌─────────────────────────────────────────────────────────────────┐
│ admin/ajax.php (PHP Endpoint)                                    │
├─────────────────────────────────────────────────────────────────┤
│ if ($_POST['action'] == 'nlp_analyze') {                         │
│     $mlService = new IncrementalMLService();                     │
│     $prediction = $mlService->predict($text);                    │
│                                                                  │
│     // After user confirms category:                            │
│     $mlService->learnFromUpload(                                 │
│         $fileId, $text, $confirmedCategory, $confidence          │
│     );                                                           │
│ }                                                                │
└──────────┬──────────────────────────────────────────────────────┘
           │
           │ PHP → Python Bridge
           ▼
┌─────────────────────────────────────────────────────────────────┐
│ resources/objects/ml_service_incremental.php                     │
├─────────────────────────────────────────────────────────────────┤
│ public function learnFromUpload($fileId, $text, $category) {    │
│     // 1. Add to queue                                          │
│     $this->addTrainingSample($text, $category, $fileId);        │
│                                                                  │
│     // 2. Check if batch size reached                           │
│     if ($pendingCount >= 10) {                                  │
│         $this->processTrainingQueue(10);                        │
│     }                                                            │
│ }                                                                │
│                                                                  │
│ public function processTrainingQueue($batchSize) {              │
│     // 1. Get pending samples from DB                           │
│     // 2. Call Python: incremental_train                        │
│     // 3. Update queue status to 'processed'                    │
│     // 4. Update model stats                                    │
│ }                                                                │
└──────────┬──────────────────────────────────────────────────────┘
           │
           │ shell_exec()
           ▼
┌─────────────────────────────────────────────────────────────────┐
│ resources/ml/incremental_ml_classifier.py                        │
├─────────────────────────────────────────────────────────────────┤
│ def incremental_train(self, texts, categories):                  │
│     # 1. Preprocess texts                                       │
│     cleaned = [self.preprocess_text(t) for t in texts]          │
│                                                                  │
│     # 2. Transform to TF-IDF vectors                            │
│     X_vec = self.vectorizer.transform(cleaned)                  │
│                                                                  │
│     # 3. Incremental learning (KEY!)                            │
│     self.classifier.partial_fit(X_vec, categories)              │
│                                                                  │
│     # 4. Update tracking                                        │
│     self.total_samples_trained += len(texts)                    │
│                                                                  │
│     return {'success': True}                                    │
└──────────┬──────────────────────────────────────────────────────┘
           │
           │ Scikit-learn
           ▼
┌─────────────────────────────────────────────────────────────────┐
│ SGDClassifier.partial_fit()                                      │
├─────────────────────────────────────────────────────────────────┤
│ - Updates model weights incrementally                            │
│ - Doesn't forget previous knowledge                             │
│ - Fast: O(n) time complexity                                    │
│ - Memory efficient: No full dataset reload                      │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Dashboard Visualization

```
┌──────────────────────────────────────────────────────────────────┐
│ 🧠 Incremental ML Learning Dashboard                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│ ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────┐ │
│ │ 📊 Total    │  │ ✅ Processed │  │ ⏳ Pending   │  │ 📅 Last │ │
│ │ Samples     │  │ Samples     │  │ Samples     │  │ Update  │ │
│ │             │  │             │  │             │  │         │ │
│ │    1,250    │  │    1,240    │  │      10     │  │ Feb 8   │ │
│ │             │  │             │  │             │  │ 3:45 PM │ │
│ └─────────────┘  └─────────────┘  └─────────────┘  └─────────┘ │
│                                                                  │
│ ┌──────────────────────────────────────────────────────────────┐ │
│ │ 📊 Category Distribution                                     │ │
│ ├──────────────────────────────────────────────────────────────┤ │
│ │ Report       ████████████████████ (450 - 36%)               │ │
│ │ Resolution   ███████████████ (380 - 31%)                    │ │
│ │ Memorandum   ███████████ (280 - 23%)                        │ │
│ │ Amendment    ████ (130 - 10%)                               │ │
│ └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│ [ Process Queue ] [ View Queue ] [ Refresh Stats ]              │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## ✅ Success Indicators

```
✓ Database Migration Complete
    └─ ml_incremental_training_queue_tbl exists
    
✓ Initial Model Trained
    └─ ml_models_tbl.is_incremental = 1
    
✓ Incremental Learning Enabled
    └─ ml_settings_tbl.incremental_learning_enabled = 1
    
✓ Queue Receiving Samples
    └─ SELECT COUNT(*) FROM ml_incremental_training_queue_tbl
    
✓ Auto-Processing Working
    └─ status changes from 'pending' to 'processed'
    
✓ Model Improving
    └─ total_incremental_samples increasing
    
✓ Predictions Getting Better
    └─ confidence scores increasing over time
```

---

*Visual architecture documentation - February 8, 2026*
