# Quick Start: Testing Google NLP Auto-Categorization

## 🚀 Quick Setup (5 Minutes)

### Step 1: Add Keywords to Database
Run this SQL in your MySQL:

```sql
-- Add keywords for Resolutions category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(1, 'resolution'),
(1, 'resolutions'),
(1, 'resolved'),
(1, 'motion'),
(1, 'motions'),
(1, 'vote'),
(1, 'voting'),
(1, 'council'),
(1, 'whereas'),
(1, 'therefore'),
(1, 'be it resolved'),
(1, 'motion carried'),
(1, 'unanimously');

-- Add keywords for Minutes category (adjust category_id as needed)
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(2, 'minutes'),
(2, 'meeting'),
(2, 'attendance'),
(2, 'agenda'),
(2, 'discussion'),
(2, 'present'),
(2, 'absent'),
(2, 'adjournment');

-- Add keywords for Letters category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(3, 'dear'),
(3, 'sincerely'),
(3, 'respectfully'),
(3, 'yours truly'),
(3, 'letter'),
(3, 'correspondence');

-- Add keywords for Amendments category
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(4, 'amendment'),
(4, 'amend'),
(4, 'modify'),
(4, 'change'),
(4, 'constitution'),
(4, 'bylaw'),
(4, 'article'),
(4, 'section');
```

### Step 2: Create Test Documents

**Test Document 1: Resolution**
Create a Word document named `test-resolution.docx`:
```
RESOLUTION NO. 2025-001

WHEREAS the Student Council recognizes the need for improved facilities;
WHEREAS this motion has been discussed in previous meetings;
WHEREAS the vote was unanimous in favor of this proposal;

THEREFORE BE IT RESOLVED that this resolution be approved and implemented.

Motion carried.
```

**Test Document 2: Meeting Minutes**
Create `test-minutes.docx`:
```
MEETING MINUTES
Student Council Meeting - November 18, 2025

Attendance:
Present: John Doe, Jane Smith, Bob Johnson
Absent: Alice Williams

Agenda:
1. Call to order
2. Discussion of budget proposal
3. Vote on new resolution

The meeting was adjourned at 5:00 PM.
```

**Test Document 3: Letter**
Create `test-letter.docx`:
```
Dear Members,

I am writing this letter to formally request your consideration of the proposal.

Respectfully,
John Doe

Yours truly,
Student Council President
```

### Step 3: Test Each User Type

#### **🔧 Admin Test:**
1. Login as admin at `http://localhost/uasg/admin/`
2. Go to **File Management** → **Upload Files** tab
3. Notice: No category dropdown (removed!)
4. Select `test-resolution.docx`
5. Click **Upload File**
6. Expected Result:
   ```
   ✅ File uploaded successfully! 
   File auto-categorized with 85.5% confidence.
   ```

#### **👤 Member Test:**
1. Login as member at `http://localhost/uasg/member/`
2. Go to **Dashboard** → **Upload Files** tab
3. Notice: "Auto-Categorization Preview" section
4. Select `test-minutes.docx`
5. Click **Upload File**
6. Expected Result:
   ```
   ✅ File uploaded successfully!
   
   🤖 Auto-categorized as: Minutes
   📊 Confidence: 78.3%
   ✅ Category automatically assigned
   ```

#### **👔 Subadmin Test:**
1. Login as subadmin at `http://localhost/uasg/subadmin/`
2. Go to **File Uploads** → **Upload Files** tab
3. Notice: No category dropdown (removed!)
4. Select `test-letter.docx`
5. Click **Upload File**
6. Expected Result:
   ```
   ✅ File uploaded successfully!
   
   🤖 Auto-categorized as: Letters
   📊 Confidence: 92.1%
   ✅ Category automatically assigned
   ```

---

## 📊 Verify Results in Database

After uploading, check the NLP analysis:

```sql
-- View latest NLP analysis
SELECT 
    f.file_name,
    n.suggested_category,
    n.category_confidence,
    n.keywords,
    n.word_count,
    n.analyzed_at
FROM file_nlp_analysis_tbl n
JOIN file_upload_tbl f ON n.file_upload_id = f.file_upload_id
ORDER BY n.analyzed_at DESC
LIMIT 5;
```

Expected output:
```
file_name              | suggested_category | confidence | keywords
-----------------------|-------------------|------------|------------------
test-resolution.docx   | Resolutions       | 85.50      | ["resolution","motion","vote","whereas"]
test-minutes.docx      | Minutes           | 78.30      | ["minutes","meeting","attendance","agenda"]
test-letter.docx       | Letters           | 92.10      | ["dear","letter","respectfully","sincerely"]
```

---

## 🎯 Where to Upload Files

### **Admin:**
📍 `http://localhost/uasg/admin/file-management.php`
- Click "Upload Files" tab
- No category dropdown shown
- Auto-categorization enabled

### **Member:**
📍 `http://localhost/uasg/member/dashboard.php`
- Click "Upload Files" tab
- Shows "Auto-Categorization Preview"
- Optional manual override available

### **Subadmin:**
📍 `http://localhost/uasg/subadmin/file-uploads.php`
- Click "Upload Files" tab
- No category dropdown shown
- Shows NLP classification preview

---

## 🔍 Troubleshooting

### Problem: "No category suggested"
**Solution:** Add more keywords to your category
```sql
SELECT fc.file_category, COUNT(fck.keyword) as keyword_count
FROM file_category_tbl fc
LEFT JOIN file_category_key_tbl fck ON fc.file_category_id = fck.file_category_id
GROUP BY fc.file_category_id;
```

### Problem: Low confidence scores
**Solution:** Add specific, unique keywords
```sql
-- Add more targeted keywords
INSERT INTO file_category_key_tbl (file_category_id, keyword) VALUES
(1, 'be it resolved'),
(1, 'motion carried'),
(1, 'unanimous vote');
```

### Problem: Wrong category assigned
**Solution:** 
1. Check which keywords matched:
   ```sql
   SELECT keywords FROM file_nlp_analysis_tbl ORDER BY analyzed_at DESC LIMIT 1;
   ```
2. Add conflicting keywords to correct category
3. Remove generic keywords that cause confusion

---

## 📈 Check NLP Performance

```sql
-- Average confidence by category
SELECT 
    suggested_category,
    AVG(category_confidence) as avg_confidence,
    MIN(category_confidence) as min_confidence,
    MAX(category_confidence) as max_confidence,
    COUNT(*) as total_files
FROM file_nlp_analysis_tbl
GROUP BY suggested_category
ORDER BY avg_confidence DESC;
```

---

## ✅ Success Criteria

You'll know it's working when:

1. ✅ Category dropdown is gone from upload forms
2. ✅ Files upload without selecting category
3. ✅ Success message shows auto-categorization results
4. ✅ Database shows NLP analysis records
5. ✅ Files are correctly categorized (70%+ confidence)
6. ✅ No errors in browser console

---

## 🎓 Tips for Best Results

1. **Use specific keywords:** "resolution" better than "document"
2. **Include variations:** "motion", "motions", "moved"
3. **Add phrases:** "be it resolved", "motion carried"
4. **Start with 5-10 keywords per category**
5. **Monitor and refine:** Check low-confidence classifications

---

## 📞 Need Help?

- Check `GOOGLE_NLP_INTEGRATION_GUIDE.md` for detailed documentation
- Review `NLP_AUTO_CATEGORIZATION_UPDATE.md` for implementation details
- Check browser console for JavaScript errors
- Check PHP error logs for server-side issues

---

**Happy Testing! 🚀**
