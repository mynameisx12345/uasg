# Conversational NLP Search Guide

## Overview

The NLP-Based File Search now supports **conversational queries**, allowing you to search for files using natural language instead of just keywords.

## How It Works

The system automatically detects when you're using conversational language and intelligently parses your query to understand:

1. **Intent** - What you want to do (search, show all, count, etc.)
2. **Category** - Which category of files you're looking for
3. **Keywords** - Additional search terms
4. **Action** - Whether to filter, show all, or perform other actions

## Example Queries

### Show All Files by Category

- **"Can you show me all resolutions?"** → Shows all resolution files
- **"Show me all memorandums"** → Shows all memorandum files
- **"Display all amendments"** → Shows all amendment files
- **"List all ordinances"** → Shows all ordinance files
- **"Get me all contracts"** → Shows all contract files

### Search Within Categories

- **"Find resolutions about budget"** → Searches for "budget" in resolution files
- **"Show me memorandums containing policy"** → Searches for "policy" in memorandums
- **"Get amendments with revised"** → Searches for "revised" in amendment files

### Natural Language Variations

The system understands many conversational patterns:

- "Can you..." / "Could you..." / "Would you..."
- "Please..." / "Kindly..."
- "I want to..." / "I need to..."
- "Show me..." / "Give me..." / "Find..."
- "Display..." / "List..." / "Get..."

## Supported Categories

The system recognizes these category terms:

| Category | Recognized Terms |
|----------|-----------------|
| **Resolution** | resolution, resolutions, resolusyon |
| **Memorandum** | memorandum, memorandums, memo, memos |
| **Amendment** | amendment, amendments, revised |
| **Ordinance** | ordinance, ordinances |
| **Contract** | contract, contracts, agreement, agreements |
| **Report** | report, reports, reporting |
| **Minutes** | minutes, meeting minutes, meeting notes |
| **Letter** | letter, letters, correspondence |
| **Proposal** | proposal, proposals |
| **Policy** | policy, policies |

## How to Use

### 1. Navigate to NLP Search

- **Admin**: Navigate to NLP Search page
- **Member**: Navigate to NLP Search page
- **Subadmin**: Navigate to NLP Search page (requires file management permission)

### 2. Enter Your Query

Instead of entering keywords, type a natural question:

```
Can you show me all resolutions?
```

### 3. Click Search

The system will:
- Parse your conversational query
- Identify the category (resolutions)
- Display all matching files
- Show a confirmation message: "Found X Resolution file(s)"

### 4. View Results

Results are displayed in a table with:
- File name
- Category
- File type
- Uploaded by
- Date uploaded
- Actions (View Details, NLP Analysis, Download)

## Traditional Search Still Works

You can still use traditional keyword search:

- Enter a keyword: `budget`
- Select a category from dropdown: `Resolution`
- Click Search

Both methods work seamlessly together!

## Tips for Best Results

### ✅ Do This

- Use clear, simple questions
- Mention the category you're looking for
- Use natural language: "show me all resolutions"
- Combine category + keyword: "find resolutions about budget"

### ❌ Avoid This

- Don't use overly complex sentences
- Don't use ambiguous terms
- Don't mix multiple categories in one query

## Technical Details

### When Conversational Mode Activates

The system automatically uses conversational parsing when:

1. Your query contains trigger words: "show", "find", "get", "all"
2. Your query is longer than 3 words (likely a sentence)
3. You haven't selected a category from the dropdown

### Fallback to Traditional Search

If conversational parsing doesn't detect a category, it falls back to traditional keyword search across all files.

### Case Insensitive

All queries are case-insensitive:
- "Show me all RESOLUTIONS" = "show me all resolutions"
- "MEMORANDUM" = "memorandum"

## Examples by User Role

### Admin Examples

```
"Can you show me all resolutions?"
"Find memorandums uploaded this month"
"Display all contracts"
"Show me recent amendments"
```

### Member Examples

```
"Show me all resolutions I can access"
"Find reports about my department"
"Get me all meeting minutes"
```

### Subadmin Examples

```
"Show me all files I can view"
"Find resolutions I need to review"
"Display all documents in my category"
```

## Response Messages

When you search, you'll see helpful messages:

- **"Found 15 Resolution file(s)"** - All resolutions shown
- **"Found 8 Memorandum file(s) matching 'budget'"** - Memorandums containing "budget"
- **"Found 0 file(s)"** - No matches found

## Browser Console

For debugging, open your browser's Developer Console (F12) to see:

```javascript
Conversational Query Parsed: {
  intent: "search",
  category: "resolution",
  keywords: [],
  action: "show_all",
  show_all: true
}
```

## Troubleshooting

### "No files found"

**Possible reasons:**

1. No files in that category
2. Category name not recognized (check spelling)
3. No files matching your keywords
4. You don't have permission to view files in that category (members/subadmins)

**Solution:**
- Check if category exists
- Try traditional search with dropdown
- Use simpler query: "show me all X"

### "Traditional keyword search used instead"

**Possible reasons:**

1. Query too short
2. No category detected
3. Category already selected in dropdown

**Solution:**
- Make query more specific: "show me all resolutions"
- Don't select category from dropdown when using conversational search

## Advanced Features

### Keyword Extraction

The system automatically removes common "stop words":

- Question words: can, could, would, will
- Articles: a, an, the
- Conjunctions: and, or, but
- Prepositions: in, on, at, to, for

This ensures only meaningful keywords are searched.

### Smart Category Matching

The system uses fuzzy matching:

- "resolution" matches category "Resolution"
- "memo" matches category "Memorandum"
- "minutes" matches category "Meeting Minutes"

## Permissions

Conversational search respects user permissions:

- **Admin**: Sees all files
- **Member**: Sees only files they have permission to access
- **Subadmin**: Sees only files based on their permissions

## Performance

Conversational search is optimized:

- **Fast parsing**: Query parsed in milliseconds
- **Efficient search**: Uses existing database indexes
- **No API calls**: All processing done locally (free!)

## Future Enhancements

Planned improvements:

- Date range queries: "Show me resolutions from last month"
- User filtering: "Show me files uploaded by John"
- Sorting: "Show me recent resolutions"
- Counting: "How many memorandums do we have?"

## Feedback

If you encounter issues or have suggestions:

1. Note the exact query you used
2. Check browser console for errors
3. Try traditional search as alternative
4. Report to system administrator

---

**Happy Searching! 🔍**
