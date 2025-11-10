# Subadmin Permissions System Implementation Guide

## Overview
This guide explains the complete implementation of the Subadmin Permissions System, which allows administrators to assign granular permissions to subadmin users (formerly called "advisers").

## Features Implemented

### 1. Database Schema
**File:** `database_migrations/subadmin_permissions_migration.sql`

**New Tables:**
- `subadmin_permissions_tbl` - Stores permission assignments for each subadmin
- `permission_definitions_tbl` - Defines available permissions
- `subadmin_activity_log_tbl` - Logs subadmin activities

**Changes:**
- User type changed from 'adviser' to 'subadmin' in `user_tbl`

### 2. Permission System Classes
**File:** `resources/objects/permission_class.php`

**Classes:**
- `SubadminPermission` - Manages CRUD operations for permissions
  - `hasPermission()` - Check if user has specific permission
  - `getUserPermissions()` - Get all permissions for a user
  - `setUserPermissions()` - Assign permissions to a user
  - `logActivity()` - Log subadmin actions

- `PermissionDefinition` - Manages permission definitions
  - `getAllActive()` - Get all active permissions
  - `getGroupedByCategory()` - Get permissions organized by category

### 3. AJAX Endpoints
**File:** `admin/ajax_permissions.php`

**Available Calls:**
- **CALL 39** - Get Permission Definitions
- **CALL 40** - Get Subadmin Permissions for specific user
- **CALL 41** - Set/Update Subadmin Permissions
- **CALL 42** - Check if user has specific permission
- **CALL 43** - Get Subadmin Activity Log

### 4. Available Permissions

| Permission Key | Permission Name | Description | Category |
|---------------|-----------------|-------------|----------|
| `task_management` | Task Management | Create, assign, and manage tasks | tasks |
| `task_submission_review` | Task Submission Review | View and review submissions | tasks |
| `file_management` | File Management | Upload and organize files | files |
| `file_approval` | File Approval | Approve/reject file uploads | files |
| `user_view` | User Viewing | View user profiles | users |
| `reports_view` | Reports Viewing | Access reports | reports |
| `reports_generate` | Reports Generation | Generate new reports | reports |
| `notifications_send` | Send Notifications | Send user notifications | notifications |
| `category_management` | Category Management | Manage categories | system |
| `dashboard_analytics` | Dashboard Analytics | View analytics | dashboard |

### 5. Permission Levels

Each permission has 4 action levels:
- **View** (`can_view`) - Can see the feature
- **Create** (`can_create`) - Can add new items
- **Edit** (`can_edit`) - Can modify existing items
- **Delete** (`can_delete`) - Can remove items

## Installation Steps

### Step 1: Run Database Migration
```sql
-- Execute the migration file
SOURCE database_migrations/subadmin_permissions_migration.sql;
```

### Step 2: Update Existing Code

#### A. Rename all "adviser" references to "subadmin"
Files that need updating:
- `admin/users.php` - UI labels ✅ DONE
- `admin/sidebar.php` - Navigation links
- `adviser/` folder → rename to `subadmin/`
- All references in session management
- All UI text and labels

#### B. Add Permission Management UI to users.php

Add after the Subadmin tab content:
```html
<!-- Permission Management Button in Subadmin Table -->
<button class="btn-secondary managePermissionsBtn" 
        data-id="{subadmin_id}" 
        data-name="{subadmin_name}">
    🔐 Manage Permissions
</button>
```

Add Permission Management Modal:
```html
<div id="permissionsModal" class="modal">
  <div class="modal-content modal-large">
    <span class="modal-close" onclick="closePermissionsModal()">&times;</span>
    <h2>Manage Permissions for <span id="permissionUserName"></span></h2>
    
    <div id="permissionsContainer">
      <!-- Permissions will be loaded here -->
    </div>
    
    <div class="form-actions">
      <button class="btn-primary" id="savePermissions">💾 Save Permissions</button>
      <button class="btn-secondary" onclick="closePermissionsModal()">Cancel</button>
    </div>
  </div>
</div>
```

### Step 3: Add JavaScript for Permission Management

```javascript
// Load and display permissions
function openPermissionsModal(userId, userName) {
  $.ajax({
    url: 'ajax.php',
    type: 'post',
    data: {
      CALL: 40, // Get user permissions
      DATA: { USER_ID: userId }
    },
    dataType: 'json',
    success: function(result) {
      if(result.status === 'SUCCESS') {
        displayPermissions(result.data);
        $('#permissionUserName').text(userName);
        $('#permissionsModal').data('user-id', userId);
        $('#permissionsModal').show();
      }
    }
  });
}

// Save permissions
$('#savePermissions').click(function() {
  const userId = $('#permissionsModal').data('user-id');
  const permissions = [];
  
  $('.permission-item').each(function() {
    const $item = $(this);
    permissions.push({
      permission_key: $item.data('key'),
      permission_name: $item.data('name'),
      can_view: $item.find('.perm-view').is(':checked') ? 1 : 0,
      can_create: $item.find('.perm-create').is(':checked') ? 1 : 0,
      can_edit: $item.find('.perm-edit').is(':checked') ? 1 : 0,
      can_delete: $item.find('.perm-delete').is(':checked') ? 1 : 0
    });
  });
  
  $.ajax({
    url: 'ajax.php',
    type: 'post',
    data: {
      CALL: 41, // Set permissions
      DATA: {
        USER_ID: userId,
        PERMISSIONS: permissions
      }
    },
    dataType: 'json',
    success: function(result) {
      openModal(result.status, result.msg);
      if(result.status === 'SUCCESS') {
        closePermissionsModal();
      }
    }
  });
});
```

### Step 4: Implement Permission Checks in Subadmin Dashboard

In `subadmin/dashboard.php`, add permission checks:

```php
<?php
require_once("../resources/session.php");
require_once("../resources/objects/main_class.php");

$session = SessionManager::getInstance();
$session->requireRole(['subadmin']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'];

// Get user permissions
$userPermissions = SubadminPermission::getUserPermissions($userId);

// Create permission lookup array
$permissions = [];
foreach($userPermissions as $perm) {
    $permissions[$perm['permission_key']] = [
        'view' => $perm['can_view'],
        'create' => $perm['can_create'],
        'edit' => $perm['can_edit'],
        'delete' => $perm['can_delete']
    ];
}

// Helper function to check permission
function hasPermission($key, $action = 'view') {
    global $permissions;
    return isset($permissions[$key]) && $permissions[$key][$action] == 1;
}
?>
```

Then in the HTML:

```php
<?php if(hasPermission('task_management', 'view')): ?>
  <div class="card">
    <h2>Task Management</h2>
    <!-- Task management content -->
    
    <?php if(hasPermission('task_management', 'create')): ?>
      <button class="btn-primary">➕ Create Task</button>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if(hasPermission('task_submission_review', 'view')): ?>
  <div class="card">
    <h2>Task Submissions</h2>
    <!-- Submissions content -->
  </div>
<?php endif; ?>
```

## Usage Examples

### Example 1: Assign Permissions to a Subadmin
```javascript
// Admin assigns permissions to subadmin
const subadminPermissions = [
  {
    permission_key: 'task_management',
    permission_name: 'Task Management',
    can_view: 1,
    can_create: 1,
    can_edit: 1,
    can_delete: 0  // Cannot delete tasks
  },
  {
    permission_key: 'task_submission_review',
    permission_name: 'Task Submission Review',
    can_view: 1,
    can_create: 0,
    can_edit: 1,  // Can review/grade
    can_delete: 0
  }
];

$.ajax({
  url: 'ajax.php',
  type: 'post',
  data: {
    CALL: 41,
    DATA: {
      USER_ID: 5,  // Subadmin user ID
      PERMISSIONS: subadminPermissions
    }
  },
  success: function(result) {
    console.log(result.msg);
  }
});
```

### Example 2: Check Permission Before Action
```javascript
// Before allowing task creation
$.ajax({
  url: 'ajax.php',
  type: 'post',
  data: {
    CALL: 42,
    DATA: {
      USER_ID: currentUserId,
      PERMISSION_KEY: 'task_management',
      ACTION: 'create'
    }
  },
  success: function(result) {
    if(result.data.has_permission) {
      // Show create task form
      openCreateTaskModal();
    } else {
      alert('You do not have permission to create tasks');
    }
  }
});
```

## File Structure

```
uasg/
├── database_migrations/
│   └── subadmin_permissions_migration.sql (NEW)
├── resources/
│   └── objects/
│       ├── permission_class.php (NEW)
│       └── main_class.php (UPDATED)
├── admin/
│   ├── ajax.php (UPDATED)
│   ├── ajax_permissions.php (NEW)
│   └── users.php (UPDATED)
└── subadmin/ (RENAMED FROM adviser/)
    └── dashboard.php (UPDATED)
```

## Testing Checklist

- [ ] Database migration runs successfully
- [ ] Permission definitions are populated
- [ ] Subadmin users show in users.php
- [ ] Permission management modal opens
- [ ] Permissions can be assigned and saved
- [ ] Subadmin dashboard respects permissions
- [ ] Activity log captures actions
- [ ] Mobile notifications work for permission changes
- [ ] All UI text changed from "adviser" to "subadmin"

## Security Considerations

1. **Always check permissions server-side** - Never rely solely on JavaScript
2. **Log all permission changes** - Use SubadminPermission::logActivity()
3. **Validate user type** - Ensure only subadmins get permission checks
4. **Admin override** - Admins should bypass all permission checks
5. **Default deny** - If permission not found, default to no access

## Next Steps

1. Run the database migration
2. Update all "adviser" references to "subadmin" throughout the application
3. Add permission management UI to users.php
4. Update subadmin dashboard with permission checks
5. Test thoroughly with different permission combinations
6. Add mobile notifications for permission changes
7. Create admin documentation for permission management

## Support

For questions or issues, contact the development team.
