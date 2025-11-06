<?php

	session_start();
	require_once("../resources/class.php");
	header("Content-Type: application/json"); // always return JSON

	if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    	http_response_code(403);
    	echo json_encode(["status"=>"error","message"=>"Forbidden"]);
    	exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    	echo json_encode(["error" => "Invalid request method."]);
    	exit;
	}

	function is_ajax_request() {
    	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	// Usage
	if (!is_ajax_request()) {
    	echo json_encode(["error" => "Unauthorized request."]);
    	exit;
	}	

	if(empty($_POST["CALL"])){
		echo json_encode(["error" => "Request invalid"]);
		exit;
	}

	$call = $_POST["CALL"];
	$result = [];

	if($call == 1){
		$data = $_POST['DATA'] ?? [];
        $name = trim($data['NAME'] ?? "");
		
		try {
			EntityManager::createPosition($name);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added new position</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 2){
		$data = $_POST["DATA"] ?? [];
		$name = trim($data["NAME"] ?? "");
		
		try {
			EntityManager::createFileCategory($name);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added new file category</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 3){
		$data = $_POST["DATA"] ?? [];
		$name = trim($data["NAME"] ?? "");
		
		try {
			EntityManager::createTaskCategory($name);
			$result = ["status" => "SUCCESS","msg" => "<span class='success'>Successfully added new task category</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 4){
		$result["data"] = EntityManager::getAllPositions();
		echo json_encode($result);
	}else if($call == 5){
		$result["data"] = EntityManager::getAllFileCategories();
		echo json_encode($result);
	}else if($call == 6){
		$result["data"] = EntityManager::getAllTaskCategories();
		echo json_encode($result);
	}else if($call == 7){
		// Get admin users
		try {
			$userManager = new UserManager();
			$result["data"] = $userManager->getUsersByType('admin');
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 8){
		// Get adviser users
		try {
			$userManager = new UserManager();
			$result["data"] = $userManager->getUsersByType('adviser');
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 9){
		// Get student users
		try {
			$userManager = new UserManager();
			$result["data"] = $userManager->getUsersByType('student');
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 10){
		// Create new user
		$data = $_POST['DATA'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->createUser($data);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User created successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to create user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 11){
		// Update user
		$data = $_POST['DATA'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->updateUser($data);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User updated successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to update user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 12){
		// Delete user
		$data = $_POST['DATA'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->deleteUser($data);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User deleted successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to delete user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 13){
		// Get single user for editing
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$userManager = new UserManager();
			$userData = $userManager->getUserById($user_id);
			$result = ["status" => "SUCCESS", "data" => $userData];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}

		echo json_encode($result);
	}
