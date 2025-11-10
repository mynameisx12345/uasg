# File Upload Management - Subadmin Module

## Overview
The file upload management system provides permission-based CRUD operations for subadmins to manage files in the UASG system.

## Features

### 1. Permission-Based Access Control
- **View**: See file list and details
- **Create**: Upload new files
- **Edit**: Modify file information
- **Delete**: Remove files from system

Permissions are checked on both client-side (for UI display) and server-side (for security).

### 2. File Operations

#### Upload Files
- Drag & drop or click to select files
- Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG
- Maximum file size: 10MB
- Required: File category selection
- Optional: Custom title and description

#### View Files
- DataTable with sorting, filtering, and pagination
- Filter by category and date range
- View detailed file information
- Download files

#### Edit Files
- Update file title
- Change category
- Modify description

#### Delete Files
- Requires deletion reason (for audit trail)
- Logged in activity log

### 3. Activity Logging
All file operations are logged in `subadmin_activity_log_tbl`:
- View files
- Upload file
- Update file
- Delete file
- Download file

## File Structure

### Frontend
- **file-uploads.php**: Main page with permission checks
- **js/file-uploads.js**: JavaScript logic and AJAX calls

### Backend
- **ajax.php**: AJAX endpoints (CALL 19-25)
- **resources/objects/main_class.php**: FileManager class
- **resources/objects/permission_class.php**: Permission checking

## AJAX Endpoints

| CALL | Action | Permission Required | Description |
|------|--------|---------------------|-------------|
| 19 | Get Files | view | Retrieve file list with filters |
| 20 | Get Categories | - | Get all file categories |
| 21 | Upload File | create | Upload a new file |
| 22 | Update File | edit | Update file information |
| 23 | Get File Details | view | Get specific file details |
| 24 | Delete File | delete | Delete a file (with reason) |
| 25 | Download File | view | Download file (GET request) |

## Database Tables Used

### file_upload_tbl
Stores uploaded file information:
- file_upload_id
- file_name
- file_path
- file_type
- file_size
- category_id
- uploaded_by
- datetime_uploaded
- google_drive_link (optional)

### file_category_tbl
File categories for organization

### subadmin_permissions_tbl
Permission assignments for subadmins

### subadmin_activity_log_tbl
Audit trail of all file operations

## Security Features

1. **Permission Validation**: Every operation checks permissions
2. **Session Management**: User must be logged in as subadmin
3. **AJAX Request Verification**: Only AJAX requests allowed
4. **File Type Validation**: Only allowed file types can be uploaded
5. **File Size Limits**: Maximum 10MB per file
6. **Activity Logging**: All actions are logged with user ID, IP address, and timestamp

## Usage Example

### Granting Permissions to a Subadmin
```php
// In admin panel, assign permissions:
SubadminPermission::setUserPermissions($userId, [
    [
        'permission_key' => 'file_management',
        'permission_name' => 'File Management',
        'can_view' => 1,
        'can_create' => 1,
        'can_edit' => 1,
        'can_delete' => 0  // Can't delete files
    ]
]);
```

### Checking Permissions in Code
```php
// Server-side (PHP)
$canEdit = SubadminPermission::hasPermission($userId, 'file_management', 'edit');

// Client-side (JavaScript)
if (window.userPermissions.canEdit) {
    // Show edit button
}
```

## UI Components

### File List Tab
- DataTable with file information
- Filter controls (category, date range)
- Action buttons (view, download, edit, delete) based on permissions

### Upload Files Tab
- Upload area with drag & drop
- Category selection (required)
- Title and description (optional)
- File preview before upload

### Modals
- **View Modal**: Display detailed file information
- **Edit Modal**: Update file metadata
- **Delete Modal**: Confirm deletion with required reason

## Permission Badges
Visual indicators showing user's current permissions:
- 🔵 View (blue)
- 🟢 Create (green)
- 🟠 Edit (orange)
- 🔴 Delete (red)

## Integration Points

### Sidebar Navigation
Link added to sidebar.php:
```php
<a href="file-uploads.php" class="nav-link">
    <span class="nav-icon">📁</span>
    <span class="nav-text">File Uploads</span>
</a>
```

### Session Management
Uses SessionManager for authentication:
```php
$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);
```

## Future Enhancements

1. **Bulk Upload**: Upload multiple files at once
2. **File Preview**: Preview documents in browser
3. **Version Control**: Track file revisions
4. **Sharing**: Share files with specific users
5. **Comments**: Add comments to files
6. **Tags**: Tag files for better organization
7. **Advanced Search**: Full-text search in file content

## Troubleshooting

### File Upload Fails
- Check file size (max 10MB)
- Verify file type is allowed
- Ensure upload directory has write permissions
- Check PHP upload_max_filesize and post_max_size settings

### Permission Denied Errors
- Verify user has correct permissions in database
- Check subadmin_permissions_tbl for user's permissions
- Ensure permission_definitions_tbl has 'file_management' entry

### Files Not Appearing
- Check database connection
- Verify files are associated with correct category
- Clear DataTable filters
- Check browser console for JavaScript errors

## Technical Notes

### DataTable Configuration
- Responsive design for mobile devices
- Server-side pagination (25 records per page)
- AJAX loading for better performance
- Custom action buttons based on permissions

### File Upload Process
1. Client validates file type and size
2. FormData object created with file and metadata
3. AJAX POST to CALL 21
4. Server validates permissions
5. FileManager handles upload and storage
6. Activity logged
7. DataTable refreshed

### Permission Check Flow
```
User Action → JavaScript Check → AJAX Request → PHP Permission Validation → 
Database Operation → Activity Logging → Response to Client
```
