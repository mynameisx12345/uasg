# ML Classification System - Setup Guide

## 📋 Prerequisites

1. **Python 3.8 or higher** installed on your system
2. **pip** (Python package manager)
3. **WAMP/XAMPP** server running
4. **Database migration** completed

## 🔧 Installation Steps

### Step 1: Install Python Dependencies

Open Command Prompt or PowerShell and navigate to the ML directory:

```bash
cd c:\wamp64\www\uasg\resources\ml
pip install -r requirements.txt
```

**Required packages:**
- pandas (CSV data handling)
- numpy (Numerical operations)
- scikit-learn (Machine learning algorithms)
- joblib (Model serialization)

### Step 2: Verify Python Installation

Test the ML classifier:

```bash
python ml_classifier.py
```

This should create a sample training dataset and train a test model. You should see output like:

```
Loaded 15 samples across 3 categories
Training LinearSVC model...
Model trained successfully!
Accuracy: 1.00
F1 Score: 1.00
Model saved to: uploads/ml_models/test_model_YYYYMMDD_HHMMSS/
```

### Step 3: Access ML Management Interface

1. Log in as **Admin** user
2. Go to: `http://localhost/uasg/admin/ml-management.php`
3. Or click **🤖 ML Classification** in the sidebar

## 📊 Usage Guide

### Upload Training Dataset

1. Prepare a CSV file with two columns:
   - `text`: Document content or description
   - `category`: Category label

**Example CSV:**
```csv
text,category
"RESOLUTION NO. 2025-001 WHEREAS the University...",Resolution
"AMENDMENT to Article III Section 2...",Amendment
"MEMORANDUM regarding the implementation...",Memorandum
```

2. Click **Upload New Dataset** button
3. Fill in dataset name and description
4. Select your CSV file
5. Click **Upload Dataset**

### Train a Model

1. Click **Train Model** button next to your dataset
2. Enter model name (e.g., "UASG Documents Model v1")
3. Select algorithm:
   - **SVM** (Recommended) - High accuracy, good for text
   - **Naive Bayes** - Fast training, good for large datasets
   - **Random Forest** - Very high accuracy, slower training
4. Set test split (default: 20%)
5. Click **Train Model**
6. Wait for training to complete (may take 1-5 minutes)

### Activate a Model

1. Once trained, click **Activate** button on the model
2. Only one model can be active at a time
3. The active model will be used for automatic document categorization

### Configure Settings

**Classification Method:**
- **NLP Cloud Only** - Use only NLP Cloud API
- **Google NLP Only** - Use only Google NLP API
- **Custom ML Only** - Use only your trained ML model
- **Hybrid** - Try ML first, fallback to NLP Cloud if confidence is low (Recommended)

**ML Confidence Threshold:**
- Minimum confidence percentage (0-100) to accept ML predictions
- Default: 60%
- Higher = More strict (may fallback to NLP Cloud more often)
- Lower = More lenient (trust ML predictions more)

**NLP Fallback:**
- Enable/disable fallback to NLP Cloud when ML confidence is low
- Recommended: Enabled

## 🎯 Performance Tips

### For Best Accuracy:

1. **Use at least 20-50 samples per category**
2. **Balance your dataset** - Similar number of samples for each category
3. **Use descriptive text** - More text content = better predictions
4. **Use consistent categories** - Match your existing file categories exactly

### For Best Performance:

1. **Start with SVM algorithm** - Good balance of speed and accuracy
2. **Use 20% test split** - Standard machine learning practice
3. **Train multiple models** - Compare accuracy and choose the best one
4. **Retrain periodically** - Add new examples and retrain for improved accuracy

## 📁 File Structure

```
c:\wamp64\www\uasg\
├── resources/
│   ├── ml/
│   │   ├── ml_classifier.py       # Python ML engine
│   │   ├── requirements.txt       # Python dependencies
│   │   └── README.md             # This file
│   └── objects/
│       └── ml_service.php        # PHP service wrapper
├── uploads/
│   ├── ml_datasets/              # Uploaded CSV files
│   └── ml_models/                # Trained model files (.pkl)
├── admin/
│   └── ml-management.php         # Admin interface
└── database/
    └── migrations/
        ├── add_ml_classification_tables.sql  # Database migration
        └── test_migration.php                # Migration test page
```

## 🐛 Troubleshooting

### "Python not recognized" error
- Install Python from https://www.python.org/downloads/
- Make sure to check "Add Python to PATH" during installation

### "Module not found" error
- Run: `pip install -r requirements.txt`
- If still fails, try: `python -m pip install pandas numpy scikit-learn`

### "Training failed" error
- Check CSV format (must have `text` and `category` columns)
- Ensure at least 5 samples per category
- Check Python error logs in PHP error log

### Model not appearing as active
- Only one model can be active at a time
- Click **Activate** button on the desired model
- Refresh the page to confirm

## 📞 Support

For issues or questions:
1. Check the migration test page: `http://localhost/uasg/database/migrations/test_migration.php`
2. Check PHP error logs: `c:\wamp64\logs\php_error.log`
3. Check Python output in browser console or PHP logs

## 🚀 Quick Start Example

1. Download the CSV template (click **Download CSV Template** button)
2. Add your own examples to the template
3. Upload the CSV file
4. Train a model with SVM algorithm
5. Activate the trained model
6. Set classification method to "Hybrid"
7. Upload a new document and see automatic categorization!

---

**Version:** 1.0
**Last Updated:** January 2026
