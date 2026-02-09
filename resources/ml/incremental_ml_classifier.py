"""
Incremental ML Document Classifier
Uses SGDClassifier for online/incremental learning
Learns continuously from uploaded files without full retraining
"""

import pandas as pd
import numpy as np
import pickle
import json
import os
from datetime import datetime
from sklearn.feature_extraction.text import TfidfVectorizer, HashingVectorizer
from sklearn.linear_model import SGDClassifier
from sklearn.metrics import accuracy_score, precision_recall_fscore_support
import re

class IncrementalMLClassifier:
    """
    Incremental Learning Document Classifier
    Supports partial_fit for continuous learning without full retraining
    """
    
    def __init__(self):
        """
        Initialize incremental classifier
        Uses SGDClassifier (supports partial_fit) instead of LinearSVC
        """
        # Use HashingVectorizer for consistent feature space (doesn't need retraining)
        # Alternative: Use TfidfVectorizer and periodically update it
        self.use_hashing = False  # Set to True for pure online learning
        
        if self.use_hashing:
            # HashingVectorizer: fixed feature space, no vocabulary storage
            self.vectorizer = HashingVectorizer(
                n_features=2**14,  # 16384 features
                ngram_range=(1, 3),
                stop_words='english',
                alternate_sign=False
            )
        else:
            # TfidfVectorizer: better performance, needs periodic vocabulary updates
            self.vectorizer = TfidfVectorizer(
                max_features=5000,
                ngram_range=(1, 3),
                min_df=1,
                max_df=0.8,
                stop_words='english'
            )
        
        # SGDClassifier supports partial_fit for incremental learning
        self.classifier = SGDClassifier(
            loss='hinge',  # SVM-like behavior
            penalty='l2',
            alpha=0.0001,
            max_iter=1000,
            tol=1e-3,
            random_state=42,
            warm_start=True  # Allows continuing training
        )
        
        self.categories = []
        self.metrics = {}
        self.is_fitted = False
        self.total_samples_trained = 0
        self.last_update = None
        
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
    
    def initial_train(self, csv_path, test_split=0.2):
        """
        Initial training from CSV file (first-time setup)
        After this, use incremental_train for continuous learning
        
        Args:
            csv_path: Path to initial training CSV file
            test_split: Proportion for testing (default 0.2)
            
        Returns:
            Dictionary with training results
        """
        try:
            # Load CSV data
            df = None
            encodings = ['utf-8', 'latin-1', 'cp1252']
            
            for encoding in encodings:
                try:
                    df = pd.read_csv(csv_path, encoding=encoding)
                    break
                except UnicodeDecodeError:
                    continue
            
            if df is None:
                raise ValueError("Could not read CSV file")
            
            # Validate columns
            if 'text' not in df.columns or 'category' not in df.columns:
                raise ValueError("CSV must have 'text' and 'category' columns")
            
            # Clean data
            df = df.dropna(subset=['text', 'category'])
            df['text'] = df['text'].apply(self.preprocess_text)
            df = df[df['text'].str.len() > 0]
            
            # Get unique categories
            self.categories = sorted(df['category'].unique().tolist())
            
            # Split data
            from sklearn.model_selection import train_test_split
            X_train, X_test, y_train, y_test = train_test_split(
                df['text'], 
                df['category'],
                test_size=test_split,
                random_state=42,
                stratify=df['category'] if len(df) > 10 else None
            )
            
            # Fit vectorizer on training data
            if not self.use_hashing:
                X_train_vec = self.vectorizer.fit_transform(X_train)
            else:
                X_train_vec = self.vectorizer.transform(X_train)
            
            # Train classifier with all categories known
            self.classifier.partial_fit(
                X_train_vec, 
                y_train,
                classes=self.categories  # Must provide all possible classes
            )
            
            self.is_fitted = True
            self.total_samples_trained = len(X_train)
            self.last_update = datetime.now()
            
            # Test on validation set
            X_test_vec = self.vectorizer.transform(X_test)
            y_pred = self.classifier.predict(X_test_vec)
            
            # Calculate metrics
            accuracy = accuracy_score(y_test, y_pred)
            precision, recall, f1, _ = precision_recall_fscore_support(
                y_test, y_pred, average='weighted', zero_division=0
            )
            
            self.metrics = {
                'accuracy': round(accuracy, 4),
                'precision': round(precision, 4),
                'recall': round(recall, 4),
                'f1_score': round(f1, 4)
            }
            
            return {
                'success': True,
                'training_type': 'initial',
                'total_samples': len(df),
                'training_samples': len(X_train),
                'test_samples': len(X_test),
                'categories': self.categories,
                'categories_count': len(self.categories),
                'accuracy': self.metrics['accuracy'],
                'precision': self.metrics['precision'],
                'recall': self.metrics['recall'],
                'f1_score': self.metrics['f1_score']
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def incremental_train(self, texts, categories):
        """
        Incrementally train on new samples (continuous learning)
        This is the key method for learning from uploaded files
        
        Args:
            texts: List of document texts (or single text string)
            categories: List of categories (or single category string)
            
        Returns:
            Dictionary with update results
        """
        try:
            if not self.is_fitted:
                return {
                    'success': False,
                    'error': 'Model must be initially trained first. Use initial_train().'
                }
            
            # Convert single samples to lists
            if isinstance(texts, str):
                texts = [texts]
            if isinstance(categories, str):
                categories = [categories]
            
            if len(texts) != len(categories):
                raise ValueError("Number of texts must match number of categories")
            
            # Preprocess texts
            cleaned_texts = [self.preprocess_text(text) for text in texts]
            
            # Remove empty texts
            valid_samples = [(t, c) for t, c in zip(cleaned_texts, categories) if t]
            if not valid_samples:
                return {
                    'success': False,
                    'error': 'No valid text samples after preprocessing'
                }
            
            cleaned_texts, categories = zip(*valid_samples)
            
            # Check for new categories
            new_categories = set(categories) - set(self.categories)
            if new_categories:
                # Add new categories to known classes
                self.categories.extend(sorted(new_categories))
                self.categories = sorted(self.categories)
            
            # Transform texts
            if not self.use_hashing and hasattr(self.vectorizer, 'vocabulary_'):
                # TfidfVectorizer: check if we need to update vocabulary
                # For now, use existing vocabulary (new words ignored)
                X_vec = self.vectorizer.transform(cleaned_texts)
            else:
                X_vec = self.vectorizer.transform(cleaned_texts)
            
            # Incremental training with partial_fit
            self.classifier.partial_fit(X_vec, categories)
            
            # Update tracking
            self.total_samples_trained += len(cleaned_texts)
            self.last_update = datetime.now()
            
            return {
                'success': True,
                'training_type': 'incremental',
                'samples_added': len(cleaned_texts),
                'total_samples_trained': self.total_samples_trained,
                'categories': self.categories,
                'categories_count': len(self.categories),
                'new_categories_added': list(new_categories) if new_categories else [],
                'last_update': self.last_update.isoformat()
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
            return_probabilities: Return confidence scores
            
        Returns:
            Dictionary with prediction results
        """
        try:
            if not self.is_fitted:
                return {
                    'success': False,
                    'error': 'Model not trained yet'
                }
            
            # Preprocess
            cleaned_text = self.preprocess_text(text)
            if not cleaned_text:
                return {
                    'success': False,
                    'error': 'Text is empty after preprocessing'
                }
            
            # Transform
            text_vec = self.vectorizer.transform([cleaned_text])
            
            # Predict
            start_time = datetime.now()
            predicted_category = self.classifier.predict(text_vec)[0]
            
            # Get decision scores
            decision_scores = self.classifier.decision_function(text_vec)[0]
            
            # Convert to pseudo-probabilities
            if len(self.categories) == 2:
                # Binary classification
                import math
                prob_positive = 1 / (1 + math.exp(-decision_scores))
                scores = np.array([1 - prob_positive, prob_positive]) * 100
            else:
                # Multi-class: softmax
                scores_exp = np.exp(decision_scores - np.max(decision_scores))
                scores = (scores_exp / scores_exp.sum()) * 100
            
            end_time = datetime.now()
            prediction_time = int((end_time - start_time).total_seconds() * 1000)
            
            # Get confidence for predicted category
            predicted_idx = self.categories.index(predicted_category)
            confidence = round(float(scores[predicted_idx]) / 100, 4)
            
            result = {
                'success': True,
                'category': predicted_category,
                'confidence': confidence,
                'prediction_time_ms': prediction_time,
                'total_samples_trained': self.total_samples_trained,
                'last_model_update': self.last_update.isoformat() if self.last_update else None
            }
            
            if return_probabilities:
                all_scores = {
                    cat: round(float(score) / 100, 4) 
                    for cat, score in zip(self.categories, scores)
                }
                result['all_confidences'] = all_scores
            
            return result
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def save_model(self, filepath):
        """
        Save model to file (pickle format)
        
        Args:
            filepath: Path to save model
            
        Returns:
            Success status
        """
        try:
            model_data = {
                'vectorizer': self.vectorizer,
                'classifier': self.classifier,
                'categories': self.categories,
                'metrics': self.metrics,
                'is_fitted': self.is_fitted,
                'total_samples_trained': self.total_samples_trained,
                'last_update': self.last_update.isoformat() if self.last_update else None,
                'use_hashing': self.use_hashing
            }
            
            with open(filepath, 'wb') as f:
                pickle.dump(model_data, f)
            
            return {
                'success': True,
                'filepath': filepath
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    def load_model(self, filepath):
        """
        Load model from file
        
        Args:
            filepath: Path to model file
            
        Returns:
            Success status
        """
        try:
            with open(filepath, 'rb') as f:
                model_data = pickle.load(f)
            
            self.vectorizer = model_data['vectorizer']
            self.classifier = model_data['classifier']
            self.categories = model_data['categories']
            self.metrics = model_data.get('metrics', {})
            self.is_fitted = model_data.get('is_fitted', True)
            self.total_samples_trained = model_data.get('total_samples_trained', 0)
            self.use_hashing = model_data.get('use_hashing', False)
            
            last_update_str = model_data.get('last_update')
            if last_update_str:
                self.last_update = datetime.fromisoformat(last_update_str)
            
            return {
                'success': True,
                'filepath': filepath,
                'categories': self.categories,
                'total_samples_trained': self.total_samples_trained
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }


# CLI interface for testing
if __name__ == '__main__':
    import sys
    import argparse
    
    parser = argparse.ArgumentParser(description='Incremental ML Classifier')
    parser.add_argument('action', choices=['initial_train', 'incremental_train', 'predict', 'save', 'load'])
    parser.add_argument('--csv', help='CSV file for initial training')
    parser.add_argument('--text', help='Text to classify')
    parser.add_argument('--category', help='Category for incremental training')
    parser.add_argument('--model', help='Model file path')
    
    args = parser.parse_args()
    
    classifier = IncrementalMLClassifier()
    
    if args.action == 'initial_train':
        if not args.csv:
            print(json.dumps({'success': False, 'error': 'CSV file required'}))
            sys.exit(1)
        
        result = classifier.initial_train(args.csv)
        print(json.dumps(result, indent=2))
        
        if result['success'] and args.model:
            classifier.save_model(args.model)
    
    elif args.action == 'incremental_train':
        if not args.model or not args.text or not args.category:
            print(json.dumps({'success': False, 'error': 'Model, text, and category required'}))
            sys.exit(1)
        
        classifier.load_model(args.model)
        result = classifier.incremental_train(args.text, args.category)
        print(json.dumps(result, indent=2))
        
        if result['success']:
            classifier.save_model(args.model)
    
    elif args.action == 'predict':
        if not args.model or not args.text:
            print(json.dumps({'success': False, 'error': 'Model and text required'}))
            sys.exit(1)
        
        classifier.load_model(args.model)
        result = classifier.predict(args.text, return_probabilities=True)
        print(json.dumps(result, indent=2))
    
    elif args.action == 'save':
        if not args.model:
            print(json.dumps({'success': False, 'error': 'Model path required'}))
            sys.exit(1)
        
        result = classifier.save_model(args.model)
        print(json.dumps(result, indent=2))
    
    elif args.action == 'load':
        if not args.model:
            print(json.dumps({'success': False, 'error': 'Model path required'}))
            sys.exit(1)
        
        result = classifier.load_model(args.model)
        print(json.dumps(result, indent=2))
