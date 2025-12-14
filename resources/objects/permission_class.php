<?php
/**
 * Subadmin Permission Management Classes
 * Handles granular permission control for subadmin users
 */

require_once("db_config.php");
require_once("main_class.php");

/**
 * SubadminPermission Class
 * Manages permissions for subadmin users
 */
class SubadminPermission extends Main {
	public function __construct($data = []) {
		parent::__construct('subadmin_permissions_tbl', $data);
	}
	
	/**
	 * Check if a subadmin has a specific permission
	 */
	public static function hasPermission($userId, $permissionKey, $action = 'view') {
		try {
			$db = Database::getInstance()->getConnection();
			
			$actionColumn = match($action) {
				'view' => 'can_view',
				'create' => 'can_create',
				'edit' => 'can_edit',
				'delete' => 'can_delete',
				default => 'can_view'
			};
			
			$query = "SELECT $actionColumn FROM subadmin_permissions_tbl 
					  WHERE user_id = :user_id AND permission_key = :permission_key";
			
			$stmt = $db->prepare($query);
			$stmt->execute([
				':user_id' => $userId,
				':permission_key' => $permissionKey
			]);
			
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result && $result[$actionColumn] == 1;
			
		} catch (Exception $e) {
			error_log("Permission check error: " . $e->getMessage());
			return false;
		}
	}
	
	/**
	 * Get all permissions for a subadmin
	 */
	public static function getUserPermissions($userId) {
		try {
			$db = Database::getInstance()->getConnection();
			
			$query = "SELECT * FROM subadmin_permissions_tbl
					  WHERE user_id = :user_id
					  ORDER BY permission_name";
			
			$stmt = $db->prepare($query);
			$stmt->execute([':user_id' => $userId]);
			
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		} catch (Exception $e) {
			throw new Exception("Failed to get user permissions: " . $e->getMessage());
		}
	}
	
	/**
	 * Set multiple permissions for a subadmin
	 */
	public static function setUserPermissions($userId, $permissions) {
		try {
			$db = Database::getInstance()->getConnection();
			$db->beginTransaction();
			
			// Delete existing permissions
			$deleteQuery = "DELETE FROM subadmin_permissions_tbl WHERE user_id = :user_id";
			$deleteStmt = $db->prepare($deleteQuery);
			$deleteStmt->execute([':user_id' => $userId]);
			
			// Insert new permissions
			$insertQuery = "INSERT INTO subadmin_permissions_tbl 
							(user_id, permission_key, permission_name, can_view, can_create, can_edit, can_delete)
							VALUES (:user_id, :permission_key, :permission_name, :can_view, :can_create, :can_edit, :can_delete)";
			$insertStmt = $db->prepare($insertQuery);
			
			foreach ($permissions as $permission) {
				$insertStmt->execute([
					':user_id' => $userId,
					':permission_key' => $permission['permission_key'],
					':permission_name' => $permission['permission_name'],
					':can_view' => $permission['can_view'] ?? 0,
					':can_create' => $permission['can_create'] ?? 0,
					':can_edit' => $permission['can_edit'] ?? 0,
					':can_delete' => $permission['can_delete'] ?? 0
				]);
			}
			
			$db->commit();
			return true;
			
		} catch (Exception $e) {
			$db->rollBack();
			throw new Exception("Failed to set user permissions: " . $e->getMessage());
		}
	}
	
	/**
	 * Log subadmin activity
	 */
	public static function logActivity($userId, $action, $permissionKey = null, $details = null) {
		try {
			$db = Database::getInstance()->getConnection();
			
			$query = "INSERT INTO subadmin_activity_log_tbl 
					  (user_id, action, permission_key, details, ip_address)
					  VALUES (:user_id, :action, :permission_key, :details, :ip_address)";
			
			$stmt = $db->prepare($query);
			$stmt->execute([
				':user_id' => $userId,
				':action' => $action,
				':permission_key' => $permissionKey,
				':details' => $details,
				':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
			]);
			
			return true;
			
		} catch (Exception $e) {
			error_log("Activity logging error: " . $e->getMessage());
			return false;
		}
	}
}

/**
 * PermissionDefinition Class
 * Manages available permission definitions
 */
class PermissionDefinition extends Main {
	public function __construct($data = []) {
		parent::__construct('subadmin_permissions_tbl', $data);
	}
	
	/**
	 * Get all active permission definitions
	 * Returns the available permission keys and names
	 */
	public static function getAllActive() {
		try {
			// Return predefined permission modules
			return [
				[
					'permission_key' => 'file_management',
					'permission_name' => 'File Management',
					'permission_category' => 'Content Management',
					'is_active' => 1
				],
				[
					'permission_key' => 'task_management',
					'permission_name' => 'Task Management',
					'permission_category' => 'Task System',
					'is_active' => 1
				],
				[
					'permission_key' => 'user_management',
					'permission_name' => 'User Management',
					'permission_category' => 'Administration',
					'is_active' => 1
				],
				[
					'permission_key' => 'entry_module',
					'permission_name' => 'Entry Module',
					'permission_category' => 'Content Management',
					'is_active' => 1
				]
			];
			
		} catch (Exception $e) {
			throw new Exception("Failed to get permission definitions: " . $e->getMessage());
		}
	}
	
	/**
	 * Get permissions grouped by category
	 */
	public static function getGroupedByCategory() {
		try {
			$permissions = self::getAllActive();
			$grouped = [];
			
			foreach ($permissions as $permission) {
				$category = $permission['permission_category'];
				if (!isset($grouped[$category])) {
					$grouped[$category] = [];
				}
				$grouped[$category][] = $permission;
			}
			
			return $grouped;
			
		} catch (Exception $e) {
			throw new Exception("Failed to group permissions: " . $e->getMessage());
		}
	}
}
?>
