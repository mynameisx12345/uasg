	
	// CALL 39: Get Permission Definitions
	else if($call == 39) {
		try {
			$groupedPermissions = PermissionDefinition::getGroupedByCategory();
			
			$result = [
				"status" => "SUCCESS",
				"data" => $groupedPermissions,
				"msg" => "Permission definitions retrieved successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 40: Get Subadmin Permissions
	else if($call == 40) {
		try {
			$data = $_POST['DATA'] ?? [];
			$userId = intval($data['USER_ID'] ?? 0);
			
			if($userId <= 0) {
				throw new Exception("Invalid user ID");
			}
			
			$permissions = SubadminPermission::getUserPermissions($userId);
			
			$result = [
				"status" => "SUCCESS",
				"data" => $permissions,
				"msg" => "User permissions retrieved successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 41: Set Subadmin Permissions
	else if($call == 41) {
		try {
			$data = $_POST['DATA'] ?? [];
			$userId = intval($data['USER_ID'] ?? 0);
			$permissions = $data['PERMISSIONS'] ?? [];
			
			if($userId <= 0) {
				throw new Exception("Invalid user ID");
			}
			
			if(empty($permissions)) {
				throw new Exception("No permissions provided");
			}
			
			// Validate that the user is a subadmin
			$user = EntityManager::getSingle('user_tbl', 'user_id', $userId);
			if(!$user || $user['user_type'] !== 'subadmin') {
				throw new Exception("User is not a subadmin");
			}
			
			SubadminPermission::setUserPermissions($userId, $permissions);
			
			// Log the action
			SubadminPermission::logActivity(
				$_SESSION['user_id'],
				"Set permissions for user ID: {$userId}",
				null,
				json_encode(['target_user' => $userId, 'permissions_count' => count($permissions)])
			);
			
			$result = [
				"status" => "SUCCESS",
				"msg" => "Permissions updated successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 42: Check Subadmin Permission
	else if($call == 42) {
		try {
			$data = $_POST['DATA'] ?? [];
			$userId = intval($data['USER_ID'] ?? $_SESSION['user_id']);
			$permissionKey = trim($data['PERMISSION_KEY'] ?? '');
			$action = trim($data['ACTION'] ?? 'view');
			
			if(empty($permissionKey)) {
				throw new Exception("Permission key is required");
			}
			
			$hasPermission = SubadminPermission::hasPermission($userId, $permissionKey, $action);
			
			$result = [
				"status" => "SUCCESS",
				"data" => [
					"has_permission" => $hasPermission,
					"user_id" => $userId,
					"permission_key" => $permissionKey,
					"action" => $action
				],
				"msg" => $hasPermission ? "Permission granted" : "Permission denied"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 43: Get Subadmin Activity Log
	else if($call == 43) {
		try {
			$data = $_POST['DATA'] ?? [];
			$userId = intval($data['USER_ID'] ?? 0);
			$limit = intval($data['LIMIT'] ?? 50);
			
			$db = Database::getInstance()->getConnection();
			
			$query = "SELECT sal.*, u.full_name 
					  FROM subadmin_activity_log_tbl sal
					  LEFT JOIN user_tbl u ON sal.user_id = u.user_id";
			
			if($userId > 0) {
				$query .= " WHERE sal.user_id = :user_id";
			}
			
			$query .= " ORDER BY sal.created_at DESC LIMIT :limit";
			
			$stmt = $db->prepare($query);
			if($userId > 0) {
				$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
			}
			$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
			$stmt->execute();
			
			$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$result = [
				"status" => "SUCCESS",
				"data" => $activities,
				"msg" => "Activity log retrieved successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
