"""
Custom ML Document Classifier
Uses TF-IDF + Scikit-learn for document categorization
Supports training from CSV files and making predictions
"""

import pandas as pd
import numpy as np
import pickle
import json
import os
from datetime import datetime
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split
from sklearn.svm import LinearSVC
from sklearn.naive_bayes import MultinomialNB
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, precision_recall_fscore_support, classification_report
import re

class MLDocumentClassifier:
    """
    Machine Learning Document Classifier
    Trains on CSV data and predicts document categories
    """
    
    def __init__(self, model_type='svm'):
        """
        Initialize classifier
        
        Args:
            model_type: 'svm', 'naive_bayes', or 'random_forest'
        """
        self.model_type = model_type
        self.vectorizer = None
        self.classifier = None
        self.categories = []
        self.metrics = {}
        
    def preprocess_text(self, text):
        """
        Clean and preprocess text for ML
        
        Args:
            text: Raw text string
            
        Returns:
            Cleaned text
        """
        if not text or not isinstance(text, str):
            return ""
        
        # Convert to lowercase
        text = text.lower()
        
        # Remove special characters but keep spaces
        text = re.sub(r'[^a-z0-9\s]', ' ', text)
        
        # Remove extra whitespace
        text = ' '.join(text.split())
        
        return text
    
    def load_csv_data(self, csv_path):
        """
        Load training data from CSV file
        
        Expected CSV format:
        text,category
        "Sample document text...",Resolution
        "Another document...",Amendment
        
        Args:
            csv_path: Path to CSV file
            
        Returns:
            Dictionary with dataset info
        """
        try:
            # Try multiple encodings
            df = None
            encodings = ['utf-8', 'latin-1', 'cp1252', 'iso-8859-1']
            
            for encoding in encodings:
                try:
                    df = pd.read_csv(csv_path, encoding=encoding)
                    break
                except UnicodeDecodeError:
                    continue
            
            if df is None:
                raise ValueError("Could not read CSV file with any supported encoding. Please save your CSV as UTF-8.")
            
            # Validate required columns
            if 'text' not in df.columns or 'category' not in df.columns:
                raise ValueError("CSV must have 'text' and 'category' columns")
            
            # Remove empty rows
            df = df.dropna(subset=['text', 'category'])
            
            # Preprocess text
            df['text'] = df['text'].apply(self.preprocess_text)
            
            # Remove empty text after preprocessing
            df = df[df['text'].str.len() > 0]
            
            # Get unique categories
            categories = df['category'].unique().tolist()
            
            dataset_info = {
                'success': True,
                'total_samples': len(df),
                'categories': categories,
                'categories_count': len(categories),
                'samples_per_category': df['category'].value_counts().to_dict(),
                'dataframe': df
            }
            
            return dataset_info
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def train(self, csv_path, test_size=0.2, random_state=42):
        """
        Train the ML model on CSV data
        
        Args:
            csv_path: Path to training CSV file
            test_size: Proportion of data for testing (default 0.2 = 20%)
            random_state: Random seed for reproducibility
            
        Returns:
            Dictionary with training results
        """
        try:
            # Load data
            data_info = self.load_csv_data(csv_path)
            if not data_info['success']:
                return data_info
            
            df = data_info['dataframe']
            self.categories = data_info['categories']
            
            # Split data
            X_train, X_test, y_train, y_test = train_test_split(
                df['text'], 
                df['category'],
                test_size=test_size,
                random_state=random_state,
                stratify=df['category'] if len(df) > 10 else None
            )
            
            # Create TF-IDF vectorizer
            self.vectorizer = TfidfVectorizer(
                max_features=5000,
                ngram_range=(1, 3),  # Unigrams, bigrams, trigrams
                min_df=1,
                max_df=0.8,
                stop_words='english'
            )
            
            # Fit vectorizer and transform training data
            X_train_tfidf = self.vectorizer.fit_transform(X_train)
            X_test_tfidf = self.vectorizer.transform(X_test)
            
            # Choose classifier based on model type
            if self.model_type == 'svm':
                self.classifier = LinearSVC(random_state=random_state, max_iter=2000)
            elif self.model_type == 'naive_bayes':
                self.classifier = MultinomialNB()
            elif self.model_type == 'random_forest':
                self.classifier = RandomForestClassifier(
                    n_estimators=100,
                    random_state=random_state
                )
            else:
                self.classifier = LinearSVC(random_state=random_state, max_iter=2000)
            
            # Train the model
            self.classifier.fit(X_train_tfidf, y_train)
            
            # Make predictions on test set
            y_pred = self.classifier.predict(X_test_tfidf)
            
            # Calculate metrics
            accuracy = accuracy_score(y_test, y_pred)
            precision, recall, f1, _ = precision_recall_fscore_support(
                y_test, y_pred, average='weighted', zero_division=0
            )
            
            # Store metrics as decimals (0-1 range), not percentages
            self.metrics = {
                'accuracy': round(accuracy, 4),
                'precision': round(precision, 4),
                'recall': round(recall, 4),
                'f1_score': round(f1, 4)
            }
            
            # Get detailed classification report
            report = classification_report(y_test, y_pred, output_dict=True, zero_division=0)
            
            return {
                'success': True,
                'total_samples': len(df),
                'training_samples': len(X_train),
                'test_samples': len(X_test),
                'categories': self.categories,
                'categories_count': len(self.categories),
                'accuracy': self.metrics['accuracy'],
                'precision': self.metrics['precision'],
                'recall': self.metrics['recall'],
                'f1_score': self.metrics['f1_score'],
                'detailed_report': report,
                'model_type': self.model_type
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def predict(self, text, return_probabilities=False):
        """
        Predict category for given text
        
        Args:
            text: Document text to classify
            return_probabilities: Return confidence scores for all categories
            
        Returns:
            Dictionary with prediction results
        """
        try:
            if self.vectorizer is None or self.classifier is None:
                return {
                    'success': False,
                    'error': 'Model not trained yet'
                }
            
            # Preprocess text
            cleaned_text = self.preprocess_text(text)
            
            if not cleaned_text:
                return {
                    'success': False,
                    'error': 'Text is empty after preprocessing'
                }
            
            # Transform text to TF-IDF features
            text_tfidf = self.vectorizer.transform([cleaned_text])
            
            # Predict
            start_time = datetime.now()
            predicted_category = self.classifier.predict(text_tfidf)[0]
            
            # Get decision function scores (confidence)
            if hasattr(self.classifier, 'decision_function'):
                scores_raw = self.classifier.decision_function(text_tfidf)[0]
                
                # Handle binary vs multi-class classification
                if len(self.categories) == 2:
                    # Binary classification: decision_function returns single value
                    # Positive = class 1, Negative = class 0
                    # Convert to probabilities for both classes
                    import math
                    prob_positive = 1 / (1 + math.exp(-scores_raw))  # Sigmoid
                    scores = np.array([1 - prob_positive, prob_positive]) * 100
                else:
                    # Multi-class: one score per class
                    scores = scores_raw
                    
            elif hasattr(self.classifier, 'predict_proba'):
                scores = self.classifier.predict_proba(text_tfidf)[0] * 100
            else:
                scores = np.array([100.0] * len(self.categories))
            
            end_time = datetime.now()
            prediction_time = int((end_time - start_time).total_seconds() * 1000)
            
            # Normalize scores to percentages
            if isinstance(scores, np.ndarray):
                # For SVM: convert decision scores to pseudo-probabilities (if not already done)
                if self.model_type == 'svm' and len(self.categories) > 2:
                    scores_exp = np.exp(scores - np.max(scores))
                    scores = (scores_exp / scores_exp.sum()) * 100
                    
            # Get confidence for predicted category
            predicted_idx = self.categories.index(predicted_category)
            confidence = round(float(scores[predicted_idx]) / 100, 4)  # Convert to 0-1 range
            
            result = {
                'success': True,
                'category': predicted_category,
                'confidence': confidence,
                'prediction_time_ms': prediction_time,
                'model_type': self.model_type
            }
            
            if return_probabilities:
                # Return all category scores
                all_scores = {
                    cat: round(float(score), 2) 
                    for cat, score in zip(self.categories, scores)
                }
                result['all_scores'] = all_scores
            
            return result
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def save_model(self, model_path, vectorizer_path):
        """
        Save trained model and vectorizer to disk
        
        Args:
            model_path: Path to save model (.pkl)
            vectorizer_path: Path to save vectorizer (.pkl)
            
        Returns:
            Dictionary with save status
        """
        try:
            # Create directories if needed
            os.makedirs(os.path.dirname(model_path), exist_ok=True)
            os.makedirs(os.path.dirname(vectorizer_path), exist_ok=True)
            
            # Save model
            with open(model_path, 'wb') as f:
                pickle.dump(self.classifier, f)
            
            # Save vectorizer
            with open(vectorizer_path, 'wb') as f:
                pickle.dump(self.vectorizer, f)
            
            # Save metadata
            metadata = {
                'categories': self.categories,
                'model_type': self.model_type,
                'metrics': self.metrics,
                'saved_at': datetime.now().isoformat()
            }
            
            metadata_path = model_path.replace('.pkl', '_metadata.json')
            with open(metadata_path, 'w') as f:
                json.dump(metadata, f, indent=2)
            
            return {
                'success': True,
                'model_path': model_path,
                'vectorizer_path': vectorizer_path,
                'metadata_path': metadata_path
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def load_model(self, model_path, vectorizer_path):
        """
        Load trained model and vectorizer from disk
        
        Args:
            model_path: Path to model file (.pkl)
            vectorizer_path: Path to vectorizer file (.pkl)
            
        Returns:
            Dictionary with load status
        """
        try:
            # Load model
            with open(model_path, 'rb') as f:
                self.classifier = pickle.load(f)
            
            # Load vectorizer
            with open(vectorizer_path, 'rb') as f:
                self.vectorizer = pickle.load(f)
            
            # Load metadata if exists
            metadata_path = model_path.replace('.pkl', '_metadata.json')
            if os.path.exists(metadata_path):
                with open(metadata_path, 'r') as f:
                    metadata = json.load(f)
                    self.categories = metadata.get('categories', [])
                    self.model_type = metadata.get('model_type', 'svm')
                    self.metrics = metadata.get('metrics', {})
            
            return {
                'success': True,
                'categories': self.categories,
                'model_type': self.model_type,
                'metrics': self.metrics
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }


# Example usage and testing
if __name__ == '__main__':
    print("ML Document Classifier - Test Mode")
    print("=" * 50)
    
    # Example: Create sample CSV for testing
    sample_csv = """text,category
"RESOLUTION NO. 2025-001 WHEREAS the Student Council recognizes the need for improved facilities",Resolution
"AMENDMENT to section 3 of the bylaws regarding membership requirements",Amendment
"Meeting minutes from January 15 2026 attendance and agenda items discussed",Meeting Minutes
"Budget proposal for fiscal year 2026 including allocation of funds",Budget Document
"RESOLVED that this motion be approved by majority vote of the council",Resolution
"AMENDING the previous resolution to include additional provisions",Amendment
"Minutes of the special meeting held on campus regarding student concerns",Meeting Minutes
"Financial report showing expenditures and revenue for the quarter",Budget Document
"""
    
    # Save sample CSV
    with open('sample_training_data.csv', 'w') as f:
        f.write(sample_csv)
    
    # Train model
    classifier = MLDocumentClassifier(model_type='svm')
    result = classifier.train('sample_training_data.csv')
    
    if result['success']:
        print(f"✓ Training successful!")
        print(f"  - Accuracy: {result['accuracy']}%")
        print(f"  - Precision: {result['precision']}%")
        print(f"  - F1 Score: {result['f1_score']}%")
        print(f"  - Categories: {', '.join(result['categories'])}")
        
        # Test prediction
        test_text = "WHEREAS the council proposes new regulations"
        prediction = classifier.predict(test_text, return_probabilities=True)
        
        if prediction['success']:
            print(f"\n✓ Prediction test:")
            print(f"  - Text: '{test_text}'")
            print(f"  - Predicted: {prediction['category']}")
            print(f"  - Confidence: {prediction['confidence']}%")
    else:
        print(f"✗ Training failed: {result['error']}")
