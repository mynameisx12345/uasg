# UASG Task Management System Setup Guide

## Overview
This system provides comprehensive task management for UASG members with adviser oversight, file submissions, and notification system.

## Database Setup

1. **Import the main database:**
   ```sql
   mysql -u username -p database_name < uasg_db (2).sql
   ```

2. **Add notifications table:**
   ```sql
   mysql -u username -p database_name < notifications_table.sql
   ```

## User Types & Access

### System Administrator (position_id: 3)
- Access: All modules including user management, file management, entry modules
- Files: `admin/users.php`, `admin/file-management.php`, `admin/entry-module.php`

### Adviser (position_id: 1)
- Access: Task creation, submission review, file access based on permissions
- File: `admin/adviser.php`
- Features:
  - Create tasks for UASG members
  - Review submissions (approve/reject)
  - View task statistics
  - Receive notifications for new submissions

### UASG Member (position_id: 2)
- Access: View assigned tasks, submit files, track submission status
- File: `admin/member.php`
- Features:
  - View assigned tasks with deadlines
  - Submit files for tasks
  - Track submission status
  - Receive notifications for new tasks and reviews

## Key Features Implemented

### Task Management
- **AJAX Endpoints (calls 22-31):**
  - Call 22: Create new task
  - Call 23: Get all tasks (for advisers)
  - Call 24: Get tasks for member
  - Call 25: Submit task
  - Call 26: Review submission
  - Call 27: Get submissions
  - Call 28: Delete task
  - Call 29: Get notifications
  - Call 30: Mark notification as read
  - Call 31: Get unread notification count

### Permission System
- File access is controlled by `file_permission_tbl`
- Advisers can only assign tasks for file categories they have permission to
- Members can only submit files for categories they have access to
- Admin controls all permissions via file management module

### Notification System
- Real-time notification count in header
- Auto-refresh every 30 seconds
- Notifications for:
  - New tasks assigned to members
  - New submissions from members to advisers
  - Submission reviews (approve/reject) to members

### File Management Integration
- File uploads integrated with task submissions
- File categories linked to task categories
- Permission-based access control
- File upload history and tracking

## Database Relationships

```
task_tbl -> task_category_tbl (categories)
task_submission_tbl -> task_tbl (submissions)
task_submission_tbl -> file_upload_tbl (files)
file_upload_tbl -> user_tbl (uploaders)
file_permission_tbl -> position_tbl + file_category_tbl (permissions)
notifications_tbl -> user_tbl (recipients)
```

## Testing Workflow

1. **As Admin:**
   - Create positions and file categories via entry module
   - Set file permissions for adviser position
   - Create adviser and member users

2. **As Adviser:**
   - Create tasks in allowed categories
   - Review submitted files
   - Check notifications for new submissions

3. **As Member:**
   - View assigned tasks
   - Submit files for tasks
   - Check submission status and notifications

## Navigation Structure

- **Admin Menu:**
  - Entry Module (positions, categories)
  - User Management (create users)
  - File Management (permissions, uploads)
  - Task Management -> Adviser Dashboard
  - Task Management -> Member Dashboard

- **Sidebar Links:**
  - Entry Module: `admin/entry-module.php`
  - User Management: `admin/users.php`
  - File Management: `admin/file-management.php`
  - Adviser Dashboard: `admin/adviser.php`
  - Member Dashboard: `admin/member.php`

## Session Management (TODO)
Currently uses hardcoded user IDs. Implement proper session management:
- Add login system
- Store user ID, position, and permissions in session
- Update all AJAX calls to use session user ID
- Add role-based access control for pages

## File Upload Integration (TODO)
- Implement actual file upload to server/Google Drive
- Add file download functionality
- Integrate with existing file management system

## Error Handling
- All AJAX endpoints include try-catch blocks
- User-friendly error messages
- Fallback data for failed requests
- Console logging for debugging

## Browser Compatibility
- Modern browsers with JavaScript enabled
- Mobile responsive design
- DataTables for data display
- jQuery for AJAX functionality