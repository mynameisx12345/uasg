<?php
	// Prevent any output before JSON. Turn off display_errors so PHP notices/warnings
	// do NOT get emitted into the AJAX response (they corrupt JSON and break DataTables).
	error_reporting(E_ALL);
	ini_set('display_errors', 0); // Do not display errors in output
	ini_set('log_errors', 1); // Log errors instead
	
	// Prevent caching of AJAX responses
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	ob_start();
	
	session_start();
	require_once("../resources/objects/db_config.php");
	require_once("../resources/objects/main_class.php");

	if (!isset($_SESSION['user_id'])) {
		http_response_code(401);
		echo json_encode(['error' => 'Authentication required', 'session_expired' => true]);
		exit;
	}

	// Enforce 30-minute idle session timeout for AJAX calls
	$timeout = 1800;
	if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
		session_unset();
		session_destroy();
		http_response_code(401);
		echo json_encode(['error' => 'Session expired', 'session_expired' => true]);
		exit;
	}
	$_SESSION['last_activity'] = time();
	
	// Check if this is a download request (GET allowed for downloads)
	if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['CALL'])) {
		if ($_GET['CALL'] === 'download') {
			// ...existing code...
			$fileId = $_GET['file_id'] ?? 0;
			$userId = $_SESSION['user_id'] ?? null;
			try {
				$fileManager = new FileManager();
				$fileManager->downloadFile($fileId, $userId);
				exit;
			} catch(Exception $e) {
				header("Content-Type: application/json");
				echo json_encode(["status" => "ERROR", "msg" => "Download failed: " . $e->getMessage()]);
				exit;
			}
		}
		if ($_GET['CALL'] === 'ml_operations' && ($_GET['action'] ?? '') === 'download_dataset') {
			$datasetId = intval($_GET['dataset_id'] ?? 0);
			$db = Database::getInstance();
			$dataset = $db->selectOne("SELECT * FROM ml_training_datasets_tbl WHERE dataset_id = ?", [$datasetId]);
			$filePath = __DIR__ . '/../' . ($dataset['file_path'] ?? '');
			if ($dataset && !empty($dataset['file_path']) && file_exists($filePath)) {
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="' . basename($dataset['file_path']) . '"');
				readfile($filePath);
			} else {
				header("HTTP/1.0 404 Not Found");
				echo "Dataset file not found.";
			}
			exit;
		}
		// Secure file view for NLP search
		if ($_GET['CALL'] === 'view_file') {
			ob_end_clean();
			$filePath = $_GET['file_path'] ?? '';
			$allowedDirs = [
				realpath(__DIR__ . '/../uploads/files'),
				realpath(__DIR__ . '/uploads'),
				realpath(__DIR__ . '/../member/uploads'),
				realpath(__DIR__ . '/../subadmin/uploads')
			];
			$realFilePath = realpath($filePath);
			$isAllowed = false;
			foreach ($allowedDirs as $dir) {
				if ($realFilePath && $dir && strpos($realFilePath, $dir) === 0) {
					$isAllowed = true;
					break;
				}
			}
			if ($isAllowed && file_exists($realFilePath)) {
				$mimeType = mime_content_type($realFilePath);
				// Look up original filename from DB; fall back to system basename
				$db = Database::getInstance();
				$sysName = basename($realFilePath);
				$fnRow = $db->selectOne("SELECT original_filename FROM file_upload_tbl WHERE file_name = ? LIMIT 1", [$sysName]);
				$displayName = (!empty($fnRow['original_filename'])) ? $fnRow['original_filename'] : $sysName;
				header('Content-Type: ' . $mimeType);
				header('Content-Disposition: inline; filename="' . $displayName . '"');
				readfile($realFilePath);
				exit;
			} else {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'ERROR', 'msg' => 'File not found or access denied']);
				exit;
			}
		}
		
		// Download file by path for NLP search results
		if ($_GET['CALL'] === 'download_by_path') {
			ob_end_clean();
			$filePath = $_GET['file_path'] ?? '';
			$allowedDirs = [
				realpath(__DIR__ . '/../uploads/files'),
				realpath(__DIR__ . '/uploads'),
				realpath(__DIR__ . '/../member/uploads'),
				realpath(__DIR__ . '/../subadmin/uploads')
			];
			$realFilePath = realpath($filePath);
			$isAllowed = false;
			foreach ($allowedDirs as $dir) {
				if ($realFilePath && $dir && strpos($realFilePath, $dir) === 0) {
					$isAllowed = true;
					break;
				}
			}
			if ($isAllowed && file_exists($realFilePath)) {
				$mimeType = mime_content_type($realFilePath);
				// Look up original filename from DB; fall back to system basename
				$db = Database::getInstance();
				$sysName = basename($realFilePath);
				$fnRow = $db->selectOne("SELECT original_filename FROM file_upload_tbl WHERE file_name = ? LIMIT 1", [$sysName]);
				$displayName = (!empty($fnRow['original_filename'])) ? $fnRow['original_filename'] : $sysName;
				header('Content-Type: ' . $mimeType);
				header('Content-Disposition: attachment; filename="' . $displayName . '"');
				header('Content-Length: ' . filesize($realFilePath));
				readfile($realFilePath);
				exit;
			} else {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'ERROR', 'msg' => 'File not found or access denied']);
				exit;
			}
		}
		
		// Download ML Training Dataset Template
		if ($_GET['CALL'] === 'download_template') {
			ob_end_clean();

			// Sample sentence templates per category (for building placeholder rows)
			$sampleTemplates = [
				'Resolution'  => 'This is a sample resolution regarding the budget approval for the fiscal year',
				'Memorandum'  => 'This memorandum is to inform all staff about the upcoming policy changes',
				'Ordinance'   => 'Ordinance establishing regulations for local business operations',
				'Amendment'   => 'Amendment to the existing bylaws regarding membership requirements',
				'Contract'    => 'Contract agreement between the municipality and the contractor',
				'Minutes'     => 'Minutes of the meeting held on January 15, 2026',
				'Letter'      => 'Official letter addressed to the department head',
				'Proposal'    => 'Proposal for the new community development project',
			];

			// Fetch all distinct category_tag values from the system
			$dbTemplate = Database::getInstance();
			$systemCategories = $dbTemplate->select("
				SELECT DISTINCT category_tag
				FROM file_upload_tbl
				WHERE category_tag IS NOT NULL AND TRIM(category_tag) != ''
				ORDER BY category_tag ASC
			");

			// Merge system categories into sampleTemplates (add new ones with a generic placeholder)
			foreach ($systemCategories as $row) {
				$cat = trim($row['category_tag']);
				if ($cat !== '' && !isset($sampleTemplates[$cat])) {
					$sampleTemplates[$cat] = 'Sample document text for the ' . $cat . ' category';
				}
			}

			// Build CSV — two example rows per category so the model has enough variety
			$csvContent = "text,category\n";
			foreach ($sampleTemplates as $category => $sampleText) {
				$csvContent .= '"' . str_replace('"', '""', $sampleText) . '",' . $category . "\n";
				$csvContent .= '"' . str_replace('"', '""', 'Another example of a ' . strtolower($category) . ' document in this system') . '",' . $category . "\n";
			}

			// Set headers for CSV download
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename="ml_training_template.csv"');
			header('Content-Length: ' . strlen($csvContent));

			echo $csvContent;
			exit;
		}
	}
	
	// Clear any previous output and set headers for normal AJAX
	ob_end_clean();
	ob_start(); // Start fresh buffer
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

	// Check if user is logged in and is admin (allow subadmin/adviser for report calls)
	$allowedReportCalls = [53, 54, 55, 56];
	$currentCall = intval($_POST['CALL'] ?? 0);
	if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
		echo json_encode(["error" => "Authentication required. Please login as admin."]);
		exit;
	}
	if ($_SESSION['user_type'] !== 'admin' && !in_array($currentCall, $allowedReportCalls)) {
		echo json_encode(["error" => "Authentication required. Please login as admin."]);
		exit;
	}
	// Dashboard Statistics (CALL: 100)
	if (isset($_POST['CALL']) && $_POST['CALL'] == 100) {
		try {
			$db = Database::getInstance();
			
		// Total Files
		$totalFiles = $db->selectOne("SELECT COUNT(*) as count FROM file_upload_tbl")['count'] ?? 0;
		
		// Total File Categories (unique category tags from uploaded files)
		$totalCategories = $db->selectOne("SELECT COUNT(DISTINCT category_tag) as count FROM file_upload_tbl WHERE category_tag IS NOT NULL")['count'] ?? 0;

		// Total Task Categories
		$totalTaskCategories = $db->selectOne("SELECT COUNT(*) as count FROM task_category_tbl")['count'] ?? 0;
		
		// Total Users (excluding admin)
		$totalUsers = $db->selectOne("SELECT COUNT(*) as count FROM user_tbl WHERE user_type != 'admin'")['count'] ?? 0;			// Pending Tasks
			$pendingTasks = $db->selectOne("SELECT COUNT(*) as count FROM task_tbl WHERE task_status = 'active' AND task_id NOT IN (SELECT task_id FROM task_submission_tbl)")['count'] ?? 0;
			
			// Active Members (students)
			$activeMembers = $db->selectOne("SELECT COUNT(*) as count FROM user_tbl WHERE user_type = 'student' AND is_active = 1")['count'] ?? 0;
			
			// Total Advisers/Subadmins
			$totalAdvisers = $db->selectOne("SELECT COUNT(*) as count FROM user_tbl WHERE user_type = 'subadmin' AND is_active = 1")['count'] ?? 0;
			
			// Task Submissions
			$totalSubmissions = $db->selectOne("SELECT COUNT(*) as count FROM task_tbl t WHERE t.task_status = 'active' AND EXISTS (SELECT 1 FROM task_submission_tbl ts WHERE ts.task_id = t.task_id AND ts.check_status = 'approved')")['count'] ?? 0;
			
		// Recent Activity (last 10)
		$recentActivity = $db->select("
			SELECT 
				'file_upload' as type,
				fu.datetime_uploaded as date,
				CONCAT(p.fname, ' ', p.lname) as user_name,
				'Uploaded' as action,
				COALESCE(NULLIF(fu.original_filename, ''), fu.file_name) as item,
				fu.category_tag as category
			FROM file_upload_tbl fu
			LEFT JOIN user_tbl u ON fu.uploaded_by = u.user_id
			LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
			ORDER BY fu.datetime_uploaded DESC
			LIMIT 10
		") ?? [];			echo json_encode([
				'success' => true,
				'data' => [
					'totalFiles' => $totalFiles,
					'totalCategories' => $totalCategories,
					'totalTaskCategories' => $totalTaskCategories,
					'totalUsers' => $totalUsers,
					'pendingTasks' => $pendingTasks,
					'activeMembers' => $activeMembers,
					'totalAdvisers' => $totalAdvisers,
					'totalSubmissions' => $totalSubmissions,
					'recentActivity' => $recentActivity
				]
			]);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'msg' => 'Error fetching dashboard data: ' . $e->getMessage()]);
		}
		exit;
	}

	// File Statistics by Category (CALL: 101)
	if (isset($_POST['CALL']) && $_POST['CALL'] == 101) {
		try {
			$db = Database::getInstance();
			$stats = $db->select("
				SELECT 
					COALESCE(fu.category_tag, 'Uncategorized') as category,
					COUNT(fu.file_upload_id) as count,
					SUM(fu.file_size) as total_size
				FROM file_upload_tbl fu
				WHERE fu.category_tag IS NOT NULL
				GROUP BY fu.category_tag
				ORDER BY count DESC
			") ?? [];
			
			echo json_encode(['success' => true, 'data' => $stats]);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
		}
		exit;
	}

	// User Activity Statistics (CALL: 102)
	if (isset($_POST['CALL']) && $_POST['CALL'] == 102) {
		try {
			$db = Database::getInstance();
			$stats = $db->select("
				SELECT 
					CONCAT(p.fname, ' ', p.lname) as user_name,
					u.user_type,
					COUNT(fu.file_upload_id) as uploads,
					MAX(fu.datetime_uploaded) as last_upload
				FROM user_tbl u
				LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
				LEFT JOIN file_upload_tbl fu ON u.user_id = fu.uploaded_by
				WHERE u.user_type != 'admin' AND u.is_active = 1
				GROUP BY u.user_id, p.fname, p.lname, u.user_type
				ORDER BY uploads DESC
				LIMIT 10
			") ?? [];
			
			echo json_encode(['success' => true, 'data' => $stats]);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
		}
		exit;
	}
	
	if(empty($_POST["CALL"])){
		echo json_encode(["error" => "Request invalid"]);
		exit;
	}

	$call = $_POST["CALL"];
	$result = [];

	if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['CALL']) && $_GET['CALL'] === 'view_file') {
		$filePath = $_GET['file_path'] ?? '';
		if ($filePath && file_exists($filePath)) {
			$mimeType = mime_content_type($filePath);
			header('Content-Type: ' . $mimeType);
			header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
			readfile($filePath);
			exit;
		} else {
			echo json_encode(['status' => 'ERROR', 'msg' => 'File not found']);
			exit;
		}
	}

	// NLP-based file search

	if($call === 'nlp_search_files') {
		try {
			// Clean output buffer before processing
			if (ob_get_level()) ob_clean();
			
			$searchWord = $_POST['SEARCH_WORD'] ?? '';
			$categoryId = $_POST['CATEGORY_ID'] ?? '';
			
			// Check if conversational mode is enabled
			$useConversational = !empty($searchWord) && (
				stripos($searchWord, 'show') !== false || 
				stripos($searchWord, 'find') !== false || 
				stripos($searchWord, 'get') !== false ||
				stripos($searchWord, 'all') !== false ||
				str_word_count($searchWord) > 3 // Likely a question/sentence
			);
			
			if ($useConversational && empty($categoryId)) {
				// Use conversational NLP processing
				require_once(__DIR__ . '/../resources/objects/conversational_nlp_service.php');
				$searchData = ConversationalNLPService::processConversationalSearch($searchWord, null);
				
				header('Content-Type: application/json');
				echo json_encode([
					"status" => "SUCCESS", 
					"data" => $searchData['results'],
					"message" => ConversationalNLPService::generateResponseMessage($searchData),
					"parsed" => $searchData['parsed_query']
				]);
				exit;
			} else {
				// Use traditional keyword search
				$fileManager = new FileManager();
				$results = $fileManager->searchFilesByContent($searchWord, $categoryId, null); // null = admin sees all
				
				header('Content-Type: application/json');
				echo json_encode(["status" => "SUCCESS", "data" => $results]);
				exit;
			}
		} catch (Exception $e) {
			if (ob_get_level()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage(), "data" => []]);
			exit;
		}
	}

	// Get all file categories
	if($call === 'get_categories') {
		try {
			$categories = EntityManager::getAllFileCategories();
			header('Content-Type: application/json');
			echo json_encode(["status" => "SUCCESS", "data" => $categories]);
			exit;
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage(), "data" => []]);
			exit;
		}
	}

	// Get NLP analysis for a file
	if($call === "smart_search") {
		$query = trim($_POST["query"] ?? "");
		if(strlen($query) < 2) { echo json_encode(["success"=>false,"error"=>"Query too short"]); exit; }
		$stopWords = ["the","is","it","a","an","and","or","but","in","on","at","to","for","of","with","by","from","this","that","these","those","was","were","be","been","being","have","has","had","do","does","did","will","would","could","should","may","might","shall","can","are","am","not","no","so","if","then","than","also","just","only","very","too","its","my","your","our","their","his","her","we","they","he","she","i","you","me","us","them","all","each","every","both","few","more","most","other","some","such","into","over","after","before","between","under","about","up","out","off","down","through","during","until","while","where","when","how","what","which","who","whom","why"];
		$words = preg_split("/\s+/", strtolower($query));
		$keywords = array_values(array_filter($words, function($w) use ($stopWords) { return strlen($w) >= 2 && !in_array($w, $stopWords); }));
		if(empty($keywords)) { echo json_encode(["success"=>false,"error"=>"No meaningful keywords found"]); exit; }
		$db = Database::getInstance();
		$allFiles = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, u.user_name as uploaded_by, n.extracted_text FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id LEFT JOIN file_nlp_analysis_tbl n ON f.file_upload_id = n.file_upload_id WHERE n.extracted_text IS NOT NULL");
		$results = [];
		foreach($allFiles as $file) {
			$text = strtolower(substr($file["extracted_text"], 0, 10000));
			$matchedWords = [];
			$fname = strtolower($file["original_filename"]);
			foreach($keywords as $kw) { if(strpos($text, $kw) !== false || strpos($fname, $kw) !== false) $matchedWords[] = $kw; }
			if(!empty($matchedWords)) {
				$file["matched_keywords"] = $matchedWords;
				$file["match_count"] = count($matchedWords);
				$file["total_keywords"] = count($keywords);
			$idx = strpos(strtolower($file["extracted_text"]), $matchedWords[0]);
			$start = max(0, $idx - 60);
			$file["snippet"] = substr($file["extracted_text"], $start, 200);
			unset($file["extracted_text"]);
				$results[] = $file;
			}
		}
		usort($results, function($a,$b){ return $b["match_count"] - $a["match_count"]; });
		echo json_encode(["success"=>true, "data"=>array_slice($results,0,50), "keywords"=>$keywords]);
		exit;
	}

	if($call === "global_search") {
		$q = "%" . trim($_POST["query"] ?? "") . "%";
		$db = Database::getInstance();
		$results = [];
		$files = $db->select("SELECT file_upload_id, original_filename, category_tag FROM file_upload_tbl WHERE original_filename LIKE ? LIMIT 5", [$q]);
		foreach($files as $f) $results[] = ["type"=>"file","id"=>$f["file_upload_id"],"icon"=>"📄","title"=>$f["original_filename"],"meta"=>$f["category_tag"]??"File"];
		$users = $db->select("SELECT u.user_id, u.user_name, u.user_type FROM user_tbl u WHERE u.user_name LIKE ? LIMIT 5", [$q]);
		foreach($users as $u) $results[] = ["type"=>"user","id"=>$u["user_id"],"icon"=>"👤","title"=>$u["user_name"],"meta"=>ucfirst($u["user_type"])];
		$tasks = $db->select("SELECT task_id, task_title FROM task_tbl WHERE task_title LIKE ? LIMIT 5", [$q]);
		foreach($tasks as $t) $results[] = ["type"=>"task","id"=>$t["task_id"],"icon"=>"📋","title"=>$t["task_title"],"meta"=>"Task"];
		echo json_encode(["success"=>true,"results"=>$results]);
		exit;
	}

	if($call === "conversational_smart_search") {
		$message = trim($_POST["message"] ?? "");
		$history = json_decode($_POST["history"] ?? "[]", true);
		if(strlen($message) < 2) { echo json_encode(["success"=>false,"error"=>"Message too short"]); exit; }

		// Intent detection: category filter, date filter, keyword extraction
		$msg = strtolower($message);
		$db = Database::getInstance();
		$stopWords = ["the","is","it","a","an","and","or","but","in","on","at","to","for","of","with","by","from","this","that","these","those","was","were","be","been","being","have","has","had","do","does","did","will","would","could","should","may","might","shall","can","are","am","not","no","so","if","then","than","also","just","only","very","too","its","my","your","our","their","his","her","we","they","he","she","i","you","me","us","them","all","each","every","both","few","more","most","other","some","such","find","show","get","search","look","any","files","file","documents","document","give","list","about","related","regarding"];

		// Detect category intent (dynamic from DB)
		$catRows = $db->select("SELECT category_name, category_slug FROM category_tbl");
		$categoryMap = [];
		foreach($catRows as $row) {
			$name = strtolower($row["category_name"]);
			$slug = strtolower($row["category_slug"]);
			$categoryMap[$name] = $slug;
			$categoryMap[$slug] = $slug;
			// Add plural forms
			if(substr($name, -1) !== 's') $categoryMap[$name . "s"] = $slug;
			if(substr($slug, -1) !== 's') $categoryMap[$slug . "s"] = $slug;
		}
		$detectedCategory = null;
		foreach($categoryMap as $term=>$cat) { if(strpos($msg, $term) !== false) { $detectedCategory = $cat; break; } }

		// Detect date intent
		$dateFilter = null;
		if(preg_match("/today/", $msg)) $dateFilter = "today";
		if(preg_match("/yesterday/", $msg)) $dateFilter = "yesterday";
		if(preg_match("/this week/", $msg)) $dateFilter = "this_week";
		if(preg_match("/last week/", $msg)) $dateFilter = "last_week";
		if(preg_match("/this month|recent|latest/", $msg)) $dateFilter = "this_month";
		if(preg_match("/last month/", $msg)) $dateFilter = "last_month";

		// Detect uploader intent
		$uploaderFilter = null;
		if(preg_match("/(?:uploaded?\s+by|by|from)\s+(\w+)/", $msg, $um)) {
			$uploaderFilter = $um[1];
		}

		// Detect doc type / mime intent
		$mimeFilter = null;
		$mimeMap = ["pdf"=>"pdf","word"=>"word","docx"=>"word","doc"=>"word","text"=>"text","txt"=>"text"];
		foreach($mimeMap as $term=>$mime) { if(preg_match("/\b".$term."\b/", $msg)) { $mimeFilter = $mime; break; } }

		// Extract keywords (exclude category terms, date terms, attribute terms)
		$words = preg_split("/\s+/", $msg);
		$catTerms = array_keys($categoryMap);
		$excludeTerms = array_merge($catTerms, ["today","yesterday","week","month","last","recent","latest","uploaded","upload","pdf","word","docx","doc","text","txt","admin","subadmin","member"]);
		if($uploaderFilter) $excludeTerms[] = $uploaderFilter;
		$keywords = array_values(array_filter($words, function($w) use ($stopWords, $excludeTerms) {
			if(strlen($w) < 3 || in_array($w, $stopWords) || in_array($w, $excludeTerms)) return false;
			// Also check singular form (strip trailing 's')
			$singular = rtrim($w, 's');
			if(in_array($singular, $excludeTerms)) return false;
			return true;
		}));

		// Also pull keywords from recent history for context (only if no filters detected)
		$hasFilters = $detectedCategory || $uploaderFilter || $mimeFilter || $dateFilter;
		if(empty($keywords) && !$hasFilters && !empty($history)) {
			$lastUser = array_filter($history, function($h){ return $h["role"]==="user"; });
			$lastMsg = end($lastUser);
			if($lastMsg) {
				$prevWords = preg_split("/\s+/", strtolower($lastMsg["text"]));
				$keywords = array_values(array_filter($prevWords, function($w) use ($stopWords) { return strlen($w) >= 3 && !in_array($w, $stopWords); }));
			}
		}

		// Build query with optional category filter
		$sql = "SELECT f.file_upload_id, f.original_filename, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, u.user_name as uploaded_by, n.extracted_text FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id LEFT JOIN file_nlp_analysis_tbl n ON f.file_upload_id = n.file_upload_id WHERE 1=1";
		$params = [];
		if(!empty($keywords)) { $sql .= " AND n.extracted_text IS NOT NULL"; }

		if($detectedCategory) {
			$catSearch = str_replace("-", " ", $detectedCategory);
			$sql .= " AND (LOWER(f.category_tag) LIKE ? OR LOWER(f.category_tag) LIKE ?)";
			$params[] = "%".$catSearch."%";
			$params[] = "%".str_replace(" ","-",$catSearch)."%";
		}
		if($dateFilter === "today") {
			$sql .= " AND DATE(f.datetime_uploaded) = CURDATE()";
		} elseif($dateFilter === "yesterday") {
			$sql .= " AND DATE(f.datetime_uploaded) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
		} elseif($dateFilter === "this_week") {
			$sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
		} elseif($dateFilter === "last_week") {
			$sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND f.datetime_uploaded < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
		} elseif($dateFilter === "this_month") {
			$sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
		} elseif($dateFilter === "last_month") {
			$sql .= " AND f.datetime_uploaded >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND f.datetime_uploaded < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
		}
		if($uploaderFilter) {
			$sql .= " AND (LOWER(u.user_name) LIKE ? OR LOWER(u.user_type) LIKE ?)";
			$params[] = "%".$uploaderFilter."%";
			$params[] = "%".$uploaderFilter."%";
		}
		if($mimeFilter) {
			$sql .= " AND f.mime_type LIKE ?";
			$params[] = "%".$mimeFilter."%";
		}

		$sql .= " ORDER BY f.datetime_uploaded DESC LIMIT 100";

		try {
			$allFiles = $db->select($sql, $params);
		} catch(Exception $e) {
			echo json_encode(["success"=>false,"error"=>"DB Error: ".$e->getMessage()]);
			exit;
		}
		$results = [];

		foreach($allFiles as $file) {
			$text = strtolower(substr($file["extracted_text"] ?? "", 0, 10000));
			$fname = strtolower($file["original_filename"]);
			$matchedWords = [];
			foreach($keywords as $kw) { if(strpos($text, $kw) !== false || strpos($fname, $kw) !== false) $matchedWords[] = $kw; }

			// If no keywords but filters matched, include all
			$hasFilters = $detectedCategory || $uploaderFilter || $mimeFilter || $dateFilter;
			if(!empty($matchedWords) || (empty($keywords) && $hasFilters)) {
				$file["matched_keywords"] = $matchedWords;
				$file["match_count"] = empty($keywords) ? 1 : count($matchedWords);
				$file["total_keywords"] = empty($keywords) ? 1 : count($keywords);
				$rawText = $file["extracted_text"] ?? "";
				$idx = !empty($matchedWords) ? (strpos(strtolower($rawText), $matchedWords[0]) ?: 0) : 0;
				$start = max(0, $idx - 60);
				$file["snippet"] = substr($rawText, $start, 200);
				unset($file["extracted_text"]);
				$results[] = $file;
			}
		}
		usort($results, function($a,$b){ return $b["match_count"] - $a["match_count"]; });
		$results = array_slice($results, 0, 10);

		// Generate conversational reply
		$count = count($results);
		if($count > 0) {
			$context = [];
			if($detectedCategory) $context[] = "in " . ucwords(str_replace("-"," ",$detectedCategory));
			if($uploaderFilter) $context[] = "uploaded by " . ucfirst($uploaderFilter);
			if($dateFilter) { $dateLabels = ["today"=>"today","yesterday"=>"yesterday","this_week"=>"this week","last_week"=>"last week","this_month"=>"this month","last_month"=>"last month"]; $context[] = $dateLabels[$dateFilter] ?? ""; }
			if($mimeFilter) $context[] = strtoupper($mimeFilter) . " files";
			$ctxStr = !empty($context) ? " (" . implode(", ", $context) . ")" : "";
			if(!empty($keywords)) {
				$reply = "Found {$count} file" . ($count>1?"s":"") . "{$ctxStr} matching \"" . implode(", ", $keywords) . "\":";
			} else {
				$reply = "Here are {$count} file" . ($count>1?"s":"") . "{$ctxStr}:";
			}
		} else {
			$reply = "I couldn't find any files matching that. Try different keywords, or filter by category, uploader, date (yesterday, this week), or doc type (pdf, docx).";
		}

		echo json_encode(["success"=>true, "reply"=>$reply, "files"=>$results, "debug"=>["category"=>$detectedCategory,"uploader"=>$uploaderFilter,"date"=>$dateFilter,"mime"=>$mimeFilter,"keywords"=>$keywords]]);
		exit;
	}

	if($call === "file_explorer") {
		$action = $_POST["action"] ?? "";
		$db = Database::getInstance();
		if ($action === "get_folders") {
			$folders = $db->select("SELECT c.category_id, c.category_name, COUNT(f.file_upload_id) as file_count FROM category_tbl c LEFT JOIN file_upload_tbl f ON c.category_id = f.category_id GROUP BY c.category_id, c.category_name ORDER BY c.category_name");
			$result = ["success" => true, "folders" => $folders];
		} elseif ($action === "get_files") {
			$categoryId = intval($_POST["category_id"] ?? 0);
			$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.category_id = ? ORDER BY f.datetime_uploaded DESC", [$categoryId]);
			$result = ["success" => true, "files" => $files];
		} elseif ($action === "search") {
			$query = "%" . ($_POST["query"] ?? "") . "%";
			$overriddenOnly = ($_POST["overridden_only"] ?? "0") === "1";
			$where = "f.original_filename LIKE ?";
			if ($overriddenOnly) $where .= " AND f.is_overridden = 1";
			$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE $where ORDER BY f.datetime_uploaded DESC LIMIT 50", [$query]);
			$result = ["success" => true, "files" => $files];
		} elseif ($action === "get_overridden") {
			$files = $db->select("SELECT f.file_upload_id, f.original_filename, f.file_name, f.file_path, f.file_size, f.mime_type, f.category_tag, f.datetime_uploaded, f.is_overridden, f.original_category_tag, u.user_name as uploaded_by FROM file_upload_tbl f LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id WHERE f.is_overridden = 1 ORDER BY f.datetime_uploaded DESC");
			$result = ["success" => true, "files" => $files];
		} elseif ($action === "delete_file") {
			$fileId = intval($_POST["file_id"] ?? 0);
			if(!$fileId) { $result = ["success"=>false,"error"=>"Invalid file ID"]; }
			else {
				$file = $db->selectOne("SELECT file_path FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
				if($file) {
					$filePath = "../" . $file["file_path"];
					if(file_exists($filePath)) unlink($filePath);
					$db->execute("DELETE FROM file_nlp_analysis_tbl WHERE file_upload_id = ?", [$fileId]);
					$db->execute("DELETE FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
					$result = ["success"=>true];
				} else {
					$result = ["success"=>false,"error"=>"File not found"];
				}
			}
		} else {
			$result = ["success" => false, "error" => "Invalid action"];
		}
		echo json_encode($result);
		exit;
	}

	if($call === 'reclassify_file') {
		$fileId = intval($_POST['file_id'] ?? 0);
		$newCategory = trim($_POST['new_category'] ?? '');
		if(!$fileId || !$newCategory) { echo json_encode(['success'=>false,'error'=>'Missing parameters']); exit; }
		// Check if manual override is allowed
		$_ovr = Database::getInstance()->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = 'allow_manual_override'");
		if(($_ovr['setting_value'] ?? '1') !== '1') { echo json_encode(['success'=>false,'error'=>'Manual override is currently disabled by administrator']); exit; }
		try {
			$db = Database::getInstance();
			$file = $db->selectOne("SELECT * FROM file_upload_tbl WHERE file_upload_id = ?", [$fileId]);
			if(!$file) throw new Exception('File not found');

			$oldCategory = $file['category_tag'];
			$originalCategory = $file['original_category_tag'] ?? $oldCategory;

			// Determine override status (revert if matching original)
			$isOverridden = ($newCategory !== $originalCategory) ? 1 : 0;

			// Get/create new category
			$fileManager = new Main('file_upload_tbl');
			$newSlug = strtolower(trim($newCategory));
			$newSlug = preg_replace('/[^a-z0-9]+/', '-', $newSlug);
			$newSlug = trim($newSlug, '-');
			$catRow = $db->selectOne("SELECT category_id FROM category_tbl WHERE category_name = ?", [$newCategory]);
			if($catRow) { $newCategoryId = $catRow['category_id']; }
			else { $newCategoryId = $db->insert("INSERT INTO category_tbl (category_name, category_slug, description) VALUES (?, ?, 'User reclassification')", [$newCategory, $newSlug]); }

			// Move physical file
			$oldPath = '../' . $file['file_path'];
			$newDir = '../uploads/files/' . $newSlug . '/';
			if(!is_dir($newDir)) mkdir($newDir, 0755, true);
			$newPath = $newDir . $file['file_name'];
			if(file_exists($oldPath)) rename($oldPath, $newPath);
			$newDbPath = 'uploads/files/' . $newSlug . '/' . $file['file_name'];

			// Update DB
			$db->execute("UPDATE file_upload_tbl SET category_id = ?, category_tag = ?, file_path = ?, is_overridden = ?, original_category_tag = ? WHERE file_upload_id = ?",
				[$newCategoryId, $newCategory, $newDbPath, $isOverridden, $originalCategory, $fileId]);

			// Queue for incremental learning
			$nlp = $db->selectOne("SELECT extracted_text FROM file_nlp_analysis_tbl WHERE file_upload_id = ?", [$fileId]);
			if($nlp && !empty($nlp['extracted_text'])) {
				require_once '../resources/objects/ml_service_incremental.php';
				$incr = new IncrementalMLService();
				$incr->learnFromUpload($fileId, $nlp['extracted_text'], $newCategory, null);
			}

			$result = ['success'=>true,'new_category'=>$newCategory,'is_overridden'=>$isOverridden];
		} catch(Exception $e) {
			$result = ['success'=>false,'error'=>$e->getMessage()];
		}
		echo json_encode($result);
		exit;
	}


	if($call === 'get_nlp_analysis') {
		try {
			$fileId = $_POST['file_id'] ?? 0;
			
			$fileManager = new FileManager();
			$result = $fileManager->getFileNLPAnalysis($fileId, null); // null = admin access
			
			header('Content-Type: application/json');
			echo json_encode($result);
			exit;
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
			exit;
		}
	}

	// NLP analysis before upload (for preview)
	if($call === 'nlp_analyze') {
		require_once '../resources/objects/ml_service.php';
		require_once '../resources/objects/text_extractor.php';
		
		if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
			try {
				$file = $_FILES['file'];
				$tmpPath = $file['tmp_name'];
				$mimeType = $file['type'];

				// Extract text from file
				$textExtractor = new TextExtractor();
				$extractResult = $textExtractor->extractText($tmpPath, $mimeType);
				
				// Handle both array and string responses from TextExtractor
				if (is_array($extractResult)) {
					if (!$extractResult['success']) {
						throw new Exception($extractResult['error'] ?? "Could not extract text from file");
					}
					$extractedText = $extractResult['text'] ?? '';
				} else {
					$extractedText = $extractResult;
				}
				
				if (empty($extractedText)) {
					throw new Exception("Could not extract text from file");
				}

				// Use ML model for classification
				$mlService = new MLClassificationService();
				$prediction = $mlService->predict($extractedText);

				if ($prediction['success']) {
					// Check confidence threshold - read from saved settings
					$confidence = $prediction['confidence'] ?? 0;
					$confidenceThreshold = floatval($mlService->getSetting('ml_confidence_threshold') ?? 60) / 100;
					if ($confidence < $confidenceThreshold) {
					
					// Low confidence - check NLP fallback setting
					$nlpFallbackEnabled = ($mlService->getSetting("nlp_fallback_enabled") ?? "1") === "1";
					if ($nlpFallbackEnabled) {
						require_once "../resources/objects/nlpcloud_service.php";
						$nlp = new NLPCloudService();
						$nlpResult = $nlp->analyzeFile($tmpPath, $mimeType);
						if ($nlpResult && ($nlpResult["success"] ?? false)) {
							$category = $nlpResult["category_tag"] ?? "Others";
							$score = $nlpResult["category_score"] ?? 0;
							$fallbackReason = "ML confidence low (" . round($confidence * 100, 2) . "%). Used NLP Cloud fallback.";
						} else {
							$category = "Others";
							$score = round($confidence * 100, 2);
							$fallbackReason = "ML confidence low. NLP fallback also failed.";
						}
					} else {
						$category = "Others";
						$score = round($confidence * 100, 2);
						$fallbackReason = "Low confidence prediction (NLP fallback disabled)";
					}
					} else {
						// Good confidence - use ML prediction
						$category = $prediction['category'] ?? 'Others';
						$score = round($confidence * 100, 2);
						$fallbackReason = null;
					}
					
					$result = [
						'status' => 'SUCCESS',
						'category' => $category,
						'score' => $score,
						'confidence' => $score,
						'original_prediction' => $prediction['category'] ?? null,
						'all_scores' => $prediction['all_scores'] ?? [],
						'top_keywords' => $prediction['top_keywords'] ?? [],
						'model_name' => $prediction['model_name'] ?? '',
						'confidence_threshold' => round($confidenceThreshold * 100, 2),
						'fallback_used' => $category === 'Others',
						'fallback_reason' => $fallbackReason,
						'keywords' => [], // ML doesn't extract keywords
						'entities' => [], // ML doesn't extract entities
						'sentiment' => [], // ML doesn't analyze sentiment
						'word_count' => str_word_count($extractedText),
						'extracted_text' => substr($extractedText, 0, 1000), // First 1000 chars
						'nlp_analysis' => [
							'provider' => ($fallbackReason && strpos($fallbackReason, 'NLP Cloud') !== false) ? 'NLP Cloud (Fallback)' : 'ML Model',
							'model_id' => $prediction['model_id'] ?? null,
							'suggested_category' => $category,
							'category_confidence' => $score,
							'original_prediction' => $prediction['category'] ?? null,
							'fallback_used' => $category === 'Others',
							'word_count' => str_word_count($extractedText),
							'extracted_text' => substr($extractedText, 0, 1000)
						]
					];
				} else {
					// ML prediction completely failed - use "Others"
					$result = [
						'status' => 'SUCCESS', // Still success, but with fallback
						'category' => 'Others',
						'score' => 0,
						'confidence' => 0,
						'fallback_used' => true,
						'fallback_reason' => 'ML prediction failed: ' . ($prediction['error'] ?? 'Unknown error'),
						'keywords' => [],
						'entities' => [],
						'sentiment' => [],
						'word_count' => str_word_count($extractedText),
						'extracted_text' => substr($extractedText, 0, 1000),
						'nlp_analysis' => [
							'provider' => 'ML Model (Fallback)',
							'model_id' => null,
							'suggested_category' => 'Others',
							'category_confidence' => 0,
							'fallback_used' => true,
							'error' => $prediction['error'] ?? 'ML model error',
							'word_count' => str_word_count($extractedText),
							'extracted_text' => substr($extractedText, 0, 1000)
						]
					];
				}
			} catch(Exception $e) {
				$result = ["status" => "ERROR", "msg" => "ML analysis failed: " . $e->getMessage()];
			}
		} else {
			$result = ["status" => "ERROR", "msg" => "No file uploaded for ML analysis."];
		}
		echo json_encode($result);
		exit;
	}

	if($call == 1){
		$data = $_POST['DATA'] ?? [];
        $name = trim($data['NAME'] ?? "");
        $access_restriction = (int)($data['ACCESS_RESTRICTION'] ?? 2);
		
		try {
			EntityManager::createPosition($name, $access_restriction);
			$result = ["status" => "SUCCESS","msg" => "Position added successfully"];
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
		try {
			$positions = EntityManager::getAllPositions() ?: [];
			// For DataTables, return just the data array wrapped in data property
			$result = ["data" => $positions];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 5){
		// Get file categories from category_tbl (NEW SYSTEM)
		try {
			$db = Database::getInstance();
			$categories = $db->select("
				SELECT category_id as file_category_id, category_name as file_category, category_slug,
				       (SELECT COUNT(*) FROM file_upload_tbl WHERE category_id = c.category_id) as file_count
				FROM category_tbl c
				ORDER BY category_name ASC
			");
			$result = ["data" => $categories ?: []];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 6){
		try {
			$taskCategories = EntityManager::getAllTaskCategories() ?: [];
			$result = ["data" => $taskCategories];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 7){
		// Get admin users
		try {
			$userManager = new UserManager();
			$users = $userManager->getUsersByType('admin');
			$result = ["data" => $users ? $users : []];
		} catch(Exception $e) {
			error_log("Error in CALL 7: " . $e->getMessage());
			$result = ["data" => []];
		}
		echo json_encode($result);
	}else if($call == 8){
		// Get subadmin users with roles
		try {
			$userManager = new UserManager();
			$users = $userManager->getUsersByTypeWithRoles('subadmin');
			$result = ["data" => $users ? $users : []];
		} catch(Exception $e) {
			$result = ["data" => []];
		}
		echo json_encode($result);
	}else if($call == 9){
		// Get student users
		try {
			$userManager = new UserManager();
			$users = $userManager->getUsersByType('student');
			$result = ["data" => $users ? $users : []];
		} catch(Exception $e) {
			$result = ["data" => []];
		}
		echo json_encode($result);
	}else if($call == 10){
		// Create new user with enhanced data handling
		$data = [
			'user_name' => $_POST['user_name'] ?? '',
			'password' => $_POST['password'] ?? '',
			'fname' => $_POST['fname'] ?? '',
			'mname' => $_POST['mname'] ?? '',
			'lname' => $_POST['lname'] ?? '',
			'auxname' => $_POST['auxname'] ?? '',
			'gender' => $_POST['gender'] ?? '',
			'birthdate' => $_POST['birthdate'] ?? '',
			'contact_number' => $_POST['contact_number'] ?? '',
			'email' => $_POST['email'] ?? '',
			'user_type' => $_POST['user_type'] ?? '',
			'subadmin_role' => $_POST['subadmin_role'] ?? '',
			'position_id' => $_POST['position_id'] ?? null,
			'permissions' => $_POST['permissions'] ?? []
		];
		
		try {
			$userManager = new UserManager();
			$userId = $userManager->createUser($data);
			
			// Handle sub-admin permissions
			if ($data['user_type'] === 'subadmin' && !empty($data['permissions'])) {
				$userManager->setUserPermissions($userId, $data['permissions']);
			}
			
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User created successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to create user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 11){
		// Update user with enhanced data handling
		$data = [
			'user_id' => $_POST['user_id'] ?? 0,
			'user_name' => $_POST['user_name'] ?? '',
			'password' => $_POST['password'] ?? '',
			'fname' => $_POST['fname'] ?? '',
			'mname' => $_POST['mname'] ?? '',
			'lname' => $_POST['lname'] ?? '',
			'auxname' => $_POST['auxname'] ?? '',
			'gender' => $_POST['gender'] ?? '',
			'birthdate' => $_POST['birthdate'] ?? '',
			'contact_number' => $_POST['contact_number'] ?? '',
			'email' => $_POST['email'] ?? '',
			'user_type' => $_POST['user_type'] ?? '',
			'subadmin_role' => $_POST['subadmin_role'] ?? '',
			'position_id' => $_POST['position_id'] ?? null,
			'permissions' => $_POST['permissions'] ?? []
		];
		
		try {
			$userManager = new UserManager();
			$userManager->updateUser($data);
			
			// Handle sub-admin permissions
			if ($data['user_type'] === 'subadmin' && !empty($data['permissions'])) {
				$userManager->setUserPermissions($data['user_id'], $data['permissions']);
			}
			
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User updated successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to update user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 12){
		// Delete user with enhanced data handling
		$user_id = $_POST['user_id'] ?? 0;
		$user_type = $_POST['user_type'] ?? '';
		
		try {
			$userManager = new UserManager();
			$userManager->deleteUser($user_id, $user_type);
			$result = ["status" => "SUCCESS", "msg" => "<span class='success'>User deleted successfully</span>"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to delete user: " . $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 13){
		// Get single user for editing with enhanced data
		$user_id = $_POST['user_id'] ?? ($_POST['USER_ID'] ?? 0);
		
		try {
			$userManager = new UserManager();
			$userData = $userManager->getUserById($user_id);
			
			// Add sub-admin role if applicable
			if ($userData && $userData['user_type'] === 'subadmin') {
				$userData['subadmin_role'] = $userManager->getSubadminRole($user_id);
			}
			
			$result = ["status" => "SUCCESS", "data" => $userData];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}

		echo json_encode($result);
	}else if($call == 14){
		// Get all files - Admin sees ALL files regardless of uploader
		try {
			$fileManager = new FileManager();
			// Pass null to get all files (no user filter for admin)
			$files = $fileManager->getAllFiles(null);
			$result = ["data" => $files ?: []];
		} catch(Exception $e) {
			$result = ["data" => [], "status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 15){
		// Get user permissions for sub-admin
		$user_id = $_POST['USER_ID'] ?? 0;
		$categoryid = (isset($_POST['CATEGORY_ID']) && trim($_POST['CATEGORY_ID']) !== '') ? trim($_POST['CATEGORY_ID']) : null;
		try {
			$fileManager = new FileManager();
			//$result["data"] = $fileManager->getFilteredFiles($categoryId, $userId = null);
			//$permissions = $userManager->getUserPermissions($user_id);
			$result = ["status"=>"SUCCESS", "data"=> $fileManager->getFilteredFiles($categoryid, $user_id) ?: []];
			//$result = ["status" => "SUCCESS", "data" => $permissions ?: []];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage(), "data" => []];
		}
		echo json_encode($result);
	}else if($call == 16){
		// Upload file with Google NLP auto-categorization and user-confirmed category
		
		if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
			$data = [
				'file' => $_FILES['file'],
				'uploaded_by' => $_POST['uploaded_by'] ?? ($_SESSION['user_id'] ?? 0),
				'category_tag' => $_POST['category_tag'] ?? 'Uncategorized',
				'category_score' => $_POST['category_score'] ?? 0,
				'nlp_analysis' => $_POST['nlp_analysis'] ?? null,
				'manual_override' => isset($_POST['manual_override']) && $_POST['manual_override'] == '1',
				'original_category' => $_POST['original_category'] ?? null,
				'file_path' => null // Will be set by FileManager
			];
		} else {
			$data = $_POST['DATA'] ?? [];
		}
		try {
			$fileManager = new Main('file_upload_tbl');
			if (!method_exists($fileManager, 'uploadFile')) {
				error_log('FileManager class does not have uploadFile method!');
				$result = ["status" => "ERROR", "msg" => "Internal error: uploadFile method missing."];
			} else {
				$uploadResult = $fileManager->uploadFile($data);
				$result = $uploadResult;
				if(!$uploadResult['success']) {
					error_log('File upload failed: ' . ($uploadResult['error'] ?? 'Unknown error'));
					$result = ["success" => false, "msg" => "File upload failed: " . ($uploadResult['error'] ?? 'Unknown error')];
				}
			}		
		} catch(Exception $e) {
			error_log('Exception during file upload: ' . $e->getMessage());
			$result = ["success" => false, "msg" => "Failed to upload file: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 39){
		// Update user permissions for sub-admin
		$user_id = $_POST['user_id'] ?? 0;
		$permissions = $_POST['permissions'] ?? [];
		
		try {
			$userManager = new UserManager();
			$userManager->setUserPermissions($user_id, $permissions);
			$result = ["status" => "SUCCESS", "msg" => "Permissions updated successfully"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to update permissions: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 17){
		// Delete file
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->deleteFile($data['file_id'], $data['user_id'], $data['reason']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to delete file: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 18){
		// Set file permission
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->setFilePermission($data['position_id'], $data['category_id']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to set permission: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 19){
		// Remove file permission
		$data = $_POST['DATA'] ?? [];
		
		try {
			$fileManager = new FileManager();
			$result = $fileManager->removeFilePermission($data['position_id'], $data['category_id']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => "Failed to remove permission: " . $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 20){
		// Get file permissions
		try {
			$fileManager = new FileManager();
			$permissions = $fileManager->getFilePermissions();
			$result = ["data" => $permissions ?: []];
		} catch(Exception $e) {
			$result = ["data" => [], "status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 21){
		// Get user accessible categories
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$fileManager = new FileManager();
			$result["data"] = $fileManager->getUserAccessibleCategories($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 22){
		// Create new task
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->createTask($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 23){
		// Get all tasks (for advisers)
		try {
			$taskManager = new TaskManager();
			$result["data"] = $taskManager->getTasks();
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 24){
		// Get tasks for member
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$taskManager = new TaskManager();
			$result["data"] = $taskManager->getTasksForMember($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 25){
		// Submit task
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->submitTask($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 26){
		// Review submission
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->reviewSubmission($data);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 27){
		// Get submissions for task
		$task_id = $_POST['TASK_ID'] ?? null;
		
		try {
			$taskManager = new TaskManager();
			$result["data"] = $taskManager->getSubmissions($task_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 28){
		// Delete task
		$data = $_POST['DATA'] ?? [];
		
		try {
			$taskManager = new TaskManager();
			$result = $taskManager->deleteTask($data['task_id'], $data['reason']);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 29){
		// Get notifications
		$user_id = $_POST['USER_ID'] ?? 0;
		$unread_only = $_POST['UNREAD_ONLY'] ?? false;
		
		try {
			$notificationManager = new NotificationManager();
			$result["data"] = $notificationManager->getNotifications($user_id, $unread_only);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 30){
		// Mark notification as read
		$notification_id = $_POST['NOTIFICATION_ID'] ?? 0;
		
		try {
			$notificationManager = new NotificationManager();
			$result = $notificationManager->markAsRead($notification_id);
			$result = ["status" => "SUCCESS", "msg" => "Notification marked as read"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
		
	}else if($call == 31){
		// Get unread notification count
		$user_id = $_POST['USER_ID'] ?? 0;
		
		try {
			$notificationManager = new NotificationManager();
			$result["count"] = $notificationManager->getUnreadCount($user_id);
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 32){
		// Mark all notifications as read
		$user_id = $_POST['USER_ID'] ?? ($_SESSION['user_id'] ?? 0);
		try {
			Database::getInstance()->execute("UPDATE notifications_tbl SET is_read=1 WHERE user_id=? AND is_read=0", [$user_id]);
			echo json_encode(["status"=>"SUCCESS"]);
		} catch(Exception $e) {
			echo json_encode(["status"=>"ERROR"]);
		}

}else if($call == 36){
		// File categorization analysis
		$filename = $_POST['FILENAME'] ?? '';
		
		if(empty($filename)) {
			echo json_encode(["status" => "ERROR", "msg" => "Filename is required"]);
			exit;
		}
		
		try {
			$fileAnalyzer = new FileAnalyzer();
			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			$mimeType = 'application/octet-stream';
			
			$analysis = $fileAnalyzer->analyzeFileContent($filename, $mimeType, $extension);
			
			$result = [
				"status" => "SUCCESS",
				"data" => $analysis,
				"msg" => "File analyzed successfully"
			];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}
	
	// CALL 37: Send Mobile Notification
	else if($call == 37) {
		try {
			$data = $_POST['DATA'] ?? [];
			$title = trim($data['TITLE'] ?? "UASG Notification");
			$message = trim($data['MESSAGE'] ?? "");
			$type = trim($data['TYPE'] ?? "info"); // info, success, warning, error
			$url = trim($data['URL'] ?? "");
			$userId = $data['USER_ID'] ?? $_SESSION['user_id'];
			
			if(empty($message)) {
				throw new Exception("Notification message is required");
			}
			
			// Save notification to database for future push notification system
			$notification_data = [
				'user_id' => $userId,
				'title' => $title,
				'message' => $message,
				'type' => $type,
				'url' => $url,
				'sent_at' => date('Y-m-d H:i:s'),
				'read_status' => 0
			];
			
			// For now, we'll just return the notification data
			// In a full implementation, you'd save this to a notifications table
			// and use a push notification service
			
			$result = [
				"status" => "SUCCESS",
				"data" => [
					"notification" => $notification_data,
					"mobile_optimized" => true,
					"supports_vibration" => true,
					"supports_actions" => true
				],
				"msg" => "Mobile notification prepared successfully"
			];
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 40){
		try {
			$taskManager = new TaskManager();
			$tasks = $taskManager->getTasks(); // fetch all tasks
			echo json_encode(['data' => $tasks]);
		} catch(Exception $e) {
			echo json_encode(['data' => [], 'error' => $e->getMessage()]);
		}
		exit;
	}else if($call == 42){
		try {
			$taskManager = new TaskManager();
			$taskId = $_POST['task_id'];
			$tasks = $taskManager->getTasks(); // returns all tasks
			$task = array_filter($tasks, fn($t) => $t['task_id'] == $taskId);
			$task = array_values($task)[0] ?? null;
			if (!$task) {
				echo json_encode(['status' => 'ERROR', 'msg' => 'Task not found']);
			} else {
				echo json_encode(['status' => 'SUCCESS', 'data' => $task]);
			}
		} catch(Exception $e) {
			echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
		}
		exit;
	}else if($call == 43){
		$data = [
			'task_id' => $_POST['task_id'] ?? null,
			'task_category_id' => $_POST['task_category_id'] ?? null,
			'task_title' => $_POST['task_title'] ?? null,
			'task_description' => $_POST['task_description'] ?? null,
			'task_deadline' => $_POST['task_deadline'] ?? null
		];

		try {
			$taskManager = new TaskManager();
			$result = $taskManager->updateTask($data);
			if ($result['status'] === 'SUCCESS' && !empty($data['task_id'])) {
				$_t = Database::getInstance()->selectOne("SELECT assigned_to,task_title,task_description FROM task_tbl WHERE task_id=?",[$data['task_id']]);
				if ($_t && !empty($_t['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_t['assigned_to'],'type'=>'task_updated','title'=>'Task Updated','message'=>"Task '{$_t['task_title']}' has been updated. {$_t['task_description']}",'related_id'=>$data['task_id']]);
			}
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 44){
		// Delete task
		$task_id = $_POST['task_id'] ?? null;
		$reason  = $_POST['reason']  ?? 'Deleted by admin';

		if (!$task_id) {
			echo json_encode(["status" => "ERROR", "msg" => "Task ID is required"]);
		} else {
			try {
				$taskManager = new TaskManager();
				$_td = Database::getInstance()->selectOne("SELECT assigned_to,task_title FROM task_tbl WHERE task_id=?",[$task_id]);
				$result = $taskManager->deleteTask($task_id, $reason);
				if (isset($result['status']) && $result['status']==='SUCCESS' && $_td && !empty($_td['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_td['assigned_to'],'type'=>'task_deleted','title'=>'Task Deleted','message'=>"Task '{$_td['task_title']}' has been deleted by admin.",'related_id'=>$task_id]);
			} catch(Exception $e) {
				$result = ["status" => "ERROR", "msg" => $e->getMessage()];
			}
			echo json_encode($result);
		}
	}else if($call == 47){
		// Get active members (students)
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					u.user_id,
					u.user_name,
					p.fname,
					p.mname,
					p.lname,
					p.auxname,
					p.email,
					p.contact_number,
					(SELECT COUNT(*) FROM task_submission_tbl ts 
					 JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id 
					 WHERE f.uploaded_by = u.user_id) as submission_count,
					(SELECT COUNT(*) FROM file_upload_tbl f WHERE f.uploaded_by = u.user_id) as upload_count
				FROM user_tbl u
				JOIN profile_tbl p ON u.profile_id = p.profile_id
				WHERE u.user_type = 'student' 
				AND (u.is_active = 1 OR u.is_active IS NULL)
				ORDER BY p.lname, p.fname
			");
			$stmt->execute();
			$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $members]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 48){
		// Get inactive members (students)
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					u.user_id,
					u.user_name,
					p.fname,
					p.mname,
					p.lname,
					p.auxname,
					p.email,
					p.contact_number,
					u.deactivated_at
				FROM user_tbl u
				JOIN profile_tbl p ON u.profile_id = p.profile_id
				WHERE u.user_type = 'student' 
				AND u.is_active = 0
				ORDER BY u.deactivated_at DESC
			");
			$stmt->execute();
			$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $members]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 49){
		// Get member's task submissions
		$userId = $_POST['user_id'] ?? 0;
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				SELECT 
					ts.task_submission_id,
					t.task_id,
					t.task_title,
					COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name,
					f.file_upload_id,
					ts.check_status,
					f.datetime_uploaded as submitted_at
				FROM task_submission_tbl ts
				JOIN task_tbl t ON ts.task_id = t.task_id
				JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id
				WHERE f.uploaded_by = :user_id
				ORDER BY f.datetime_uploaded DESC
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["status" => "SUCCESS", "data" => $submissions]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 50){
		// Get member's all uploads
		$userId = $_POST['user_id'] ?? 0;
		
		try {
			$conn = Database::getInstance()->getConnection();
			
		$stmt = $conn->prepare("
			SELECT 
				f.file_upload_id,
				COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name,
				f.category_tag as category_name,
				f.mime_type,
				f.datetime_uploaded
			FROM file_upload_tbl f
			WHERE f.uploaded_by = :user_id
			ORDER BY f.datetime_uploaded DESC
		");
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->execute();
		$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);			echo json_encode(["status" => "SUCCESS", "data" => $uploads]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 51){
		// Deactivate member
		$userId = $_POST['user_id'] ?? 0;
		$reason = $_POST['reason'] ?? '';
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				UPDATE user_tbl 
				SET is_active = 0, 
					deactivated_at = NOW(),
					deactivation_reason = :reason
				WHERE user_id = :user_id AND user_type = 'student'
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Member deactivated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 52){
		// Reactivate member
		$userId = $_POST['user_id'] ?? 0;
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("
				UPDATE user_tbl 
				SET is_active = 1, 
					deactivated_at = NULL,
					deactivation_reason = NULL
				WHERE user_id = :user_id AND user_type = 'student'
			");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Member reactivated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 53){
		// Get tasks report with filters
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$sql = "
				SELECT 
					t.task_id,
					tc.task_category as category_name,
					t.task_title,
					t.task_description,
					t.task_deadline,
					COUNT(ts.task_submission_id) as submission_count
				FROM task_tbl t
				LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id
				LEFT JOIN task_submission_tbl ts ON t.task_id = ts.task_id
			";
			
			$where = [];
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$where[] = "DATE_FORMAT(t.task_deadline, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$where[] = "YEAR(t.task_deadline) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$where[] = "t.task_deadline BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			if (!empty($where)) {
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$sql .= " GROUP BY t.task_id ORDER BY t.task_deadline DESC";
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $tasks]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 54){
		// Get uploads report with filters
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$sql = "
				SELECT 
				f.file_upload_id,
				COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name,
				f.category_tag as category_name,
				CONCAT(p.fname, ' ', p.lname) as uploader_name,
				f.datetime_uploaded,
				f.mime_type
			FROM file_upload_tbl f
			LEFT JOIN user_tbl u ON f.uploaded_by = u.user_id
			LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id
		";			$where = [];
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$where[] = "DATE_FORMAT(f.datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$where[] = "YEAR(f.datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$where[] = "DATE(f.datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			if (!empty($where)) {
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$sql .= " ORDER BY f.datetime_uploaded DESC";
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $uploads]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 55){
		// Get submissions report with filters
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$sql = "
				SELECT 
					ts.task_submission_id,
					t.task_id,
					t.task_title,
					t.task_status,
					CONCAT(p.fname, ' ', p.lname) as student_name,
					COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name,
					f.file_upload_id,
					f.datetime_uploaded as submitted_at,
					ts.check_status
				FROM task_submission_tbl ts
				JOIN task_tbl t ON ts.task_id = t.task_id
				JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id
				JOIN user_tbl u ON f.uploaded_by = u.user_id
				JOIN profile_tbl p ON u.profile_id = p.profile_id
			";
			
			$where = [];
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$where[] = "DATE_FORMAT(f.datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$where[] = "YEAR(f.datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$where[] = "DATE(f.datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			if (!empty($where)) {
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$sql .= " ORDER BY f.datetime_uploaded DESC";
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			echo json_encode(["data" => $submissions]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 56){
		// Get statistics for dashboard
		$filters = $_POST['filters'] ?? ['type' => 'all'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Total tasks
			$sql = "SELECT COUNT(*) as total FROM task_tbl";
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$sql .= " WHERE DATE_FORMAT(task_deadline, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$sql .= " WHERE YEAR(task_deadline) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$sql .= " WHERE task_deadline BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$totalTasks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Total uploads
			$sql = "SELECT COUNT(*) as total FROM file_upload_tbl";
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$sql .= " WHERE DATE_FORMAT(datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$sql .= " WHERE YEAR(datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$sql .= " WHERE DATE(datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$totalUploads = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Total submissions
			$sql = "SELECT COUNT(*) as total FROM task_submission_tbl ts JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id";
			$params = [];
			
			if ($filters['type'] === 'month' && !empty($filters['month'])) {
				$sql .= " WHERE DATE_FORMAT(f.datetime_uploaded, '%Y-%m') = :month";
				$params[':month'] = $filters['month'];
			} elseif ($filters['type'] === 'year' && !empty($filters['year'])) {
				$sql .= " WHERE YEAR(f.datetime_uploaded) = :year";
				$params[':year'] = $filters['year'];
			} elseif ($filters['type'] === 'date' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
				$sql .= " WHERE DATE(f.datetime_uploaded) BETWEEN :date_from AND :date_to";
				$params[':date_from'] = $filters['date_from'];
				$params[':date_to'] = $filters['date_to'];
			}
			
			$stmt = $conn->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$totalSubmissions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Active members
			$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_tbl WHERE user_type = 'student' AND (is_active = 1 OR is_active IS NULL)");
			$stmt->execute();
			$activeMembers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
			
			// Tasks by category
			$stmt = $conn->prepare("SELECT tc.task_category as category, COUNT(t.task_id) as count FROM task_category_tbl tc LEFT JOIN task_tbl t ON tc.task_category_id = t.task_category_id GROUP BY tc.task_category_id");
			$stmt->execute();
			$tasksByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		// Uploads by category
		$stmt = $conn->prepare("SELECT COALESCE(f.category_tag, 'Uncategorized') as category, COUNT(f.file_upload_id) as count FROM file_upload_tbl f GROUP BY f.category_tag");
		$stmt->execute();
		$uploadsByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);			echo json_encode([
				"status" => "SUCCESS",
				"data" => [
					"total_tasks" => $totalTasks,
					"total_uploads" => $totalUploads,
					"total_submissions" => $totalSubmissions,
					"active_members" => $activeMembers,
					"tasks_by_category" => $tasksByCategory,
					"uploads_by_category" => $uploadsByCategory
				]
			]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 57){
		// Get task details with submission info for View modal
		$taskId = $_POST['task_id'] ?? 0;
		try {
			$db = Database::getInstance();
			$task = $db->selectOne("SELECT t.*, tc.task_category, CONCAT(p.fname, ' ', p.lname) as assigned_member_name FROM task_tbl t LEFT JOIN task_category_tbl tc ON t.task_category_id = tc.task_category_id LEFT JOIN user_tbl u ON t.assigned_to = u.user_id LEFT JOIN profile_tbl p ON u.profile_id = p.profile_id WHERE t.task_id = ?", [$taskId]);
			if (!$task) { echo json_encode(['status' => 'ERROR', 'msg' => 'Task not found']); exit; }
			$submissions = $db->select("SELECT ts.task_submission_id, ts.check_status, COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name, f.file_upload_id, f.datetime_uploaded as submitted_at, CONCAT(sp.fname, ' ', sp.lname) as student_name FROM task_submission_tbl ts INNER JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id INNER JOIN user_tbl su ON ts.submitted_by = su.user_id INNER JOIN profile_tbl sp ON su.profile_id = sp.profile_id WHERE ts.task_id = ? ORDER BY f.datetime_uploaded DESC", [$taskId]) ?: [];
			$task['submissions'] = $submissions;
			echo json_encode(['status' => 'SUCCESS', 'data' => $task]);
		} catch(Exception $e) {
			echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
		}
		exit;
	}else if($call == 58){
		// Change password
		$userId = $_SESSION['user_id'];
		$currentPassword = $_POST['current_password'] ?? '';
		$newPassword = $_POST['new_password'] ?? '';
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Verify current password
			$stmt = $conn->prepare("SELECT pass_word FROM user_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if (!$user || !password_verify($currentPassword, $user['pass_word'])) {
				echo json_encode(["status" => "ERROR", "msg" => "Current password is incorrect"]);
				exit;
			}
			
			// Update password
			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
			$stmt = $conn->prepare("UPDATE user_tbl SET pass_word = :password WHERE user_id = :user_id");
			$stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Password updated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 59){
		// Save security questions
		$userId = $_SESSION['user_id'];
		$question1 = $_POST['question1'] ?? '';
		$answer1 = password_hash($_POST['answer1'] ?? '', PASSWORD_DEFAULT);
		$question2 = $_POST['question2'] ?? '';
		$answer2 = password_hash($_POST['answer2'] ?? '', PASSWORD_DEFAULT);
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Check if security questions already exist
			$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_security_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
			
			if ($exists) {
				$stmt = $conn->prepare("UPDATE user_security_tbl SET question1 = :q1, answer1 = :a1, question2 = :q2, answer2 = :a2 WHERE user_id = :user_id");
			} else {
				$stmt = $conn->prepare("INSERT INTO user_security_tbl (user_id, question1, answer1, question2, answer2) VALUES (:user_id, :q1, :a1, :q2, :a2)");
			}
			
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':q1', $question1, PDO::PARAM_STR);
			$stmt->bindParam(':a1', $answer1, PDO::PARAM_STR);
			$stmt->bindParam(':q2', $question2, PDO::PARAM_STR);
			$stmt->bindParam(':a2', $answer2, PDO::PARAM_STR);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Security questions saved successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 60){
		// Save recovery email
		$userId = $_SESSION['user_id'];
		$recoveryEmail = $_POST['recovery_email'] ?? '';
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			// Check if record exists
			$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_security_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
			
			if ($exists) {
				$stmt = $conn->prepare("UPDATE user_security_tbl SET recovery_email = :email WHERE user_id = :user_id");
			} else {
				$stmt = $conn->prepare("INSERT INTO user_security_tbl (user_id, recovery_email) VALUES (:user_id, :email)");
			}
			
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':email', $recoveryEmail, PDO::PARAM_STR);
			$stmt->execute();
			
			echo json_encode(["status" => "SUCCESS", "msg" => "Recovery email updated successfully"]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 61){
		// Get security settings
		$userId = $_SESSION['user_id'];
		
		try {
			$conn = Database::getInstance()->getConnection();
			
			$stmt = $conn->prepare("SELECT question1, answer1, question2, answer2, recovery_email FROM user_security_tbl WHERE user_id = :user_id");
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$settings = $stmt->fetch(PDO::FETCH_ASSOC);
			
			// Remove actual answers from response (only send questions)
			if ($settings) {
				unset($settings['answer1']);
				unset($settings['answer2']);
			}
			
			echo json_encode(["status" => "SUCCESS", "data" => $settings]);
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}else if($call == 62){
		// Get NLP analysis for a file
		$fileId = $_POST['file_id'] ?? 0;
		
		try {
			$fileManager = new FileManager();
			$analysis = $fileManager->getNLPAnalysis($fileId);
			
			if ($analysis) {
				echo json_encode(["status" => "SUCCESS", "data" => $analysis]);
			} else {
				echo json_encode(["status" => "ERROR", "msg" => "No NLP analysis found for this file"]);
			}
		} catch(Exception $e) {
			echo json_encode(["status" => "ERROR", "msg" => $e->getMessage()]);
		}
	}if ($_POST['CALL'] == 100) {
		$db = Database::getInstance();
		$users = $db->select("
			SELECT 
				u.user_id,
				CONCAT(p.fname, ' ', p.lname) AS full_name
			FROM user_tbl u
			JOIN profile_tbl p ON u.profile_id = p.profile_id
			JOIN position_tbl pos ON u.position_id = pos.position_id
			WHERE pos.position = 'Student Government Member'
		");

		echo json_encode(["status"=>"SUCCESS","data"=>$users]);
		exit;
	}else if($call == 63){
		// Get all Student Government Members for task assignment
		try {
			$db = Database::getInstance();
			$members = $db->select("
				SELECT 
					u.user_id,
					CONCAT(p.fname, ' ', p.lname) as full_name,
					pos.position
				FROM user_tbl u
				INNER JOIN profile_tbl p ON u.profile_id = p.profile_id
				INNER JOIN position_tbl pos ON u.position_id = pos.position_id
				WHERE u.user_type = 'student'
				ORDER BY p.fname, p.lname
			") ?: [];
			
			$result = ["status" => "SUCCESS", "data" => $members];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage(), "data" => []];
		}
		echo json_encode($result);
	}else if($call == 41){
		// Get all task submissions for admin
		try {
			$db = Database::getInstance();
			$submissions = $db->select("
				SELECT 
					ts.task_submission_id,
					t.task_id,
					t.task_title,
					t.task_status,
					t.task_deadline,
					t.assigned_to,
					CONCAT(p.fname, ' ', p.lname) as student_name,
					COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name,
					f.file_upload_id,
					ts.check_status,
					f.datetime_uploaded as submitted_at,
					ts.submitted_by,
					(SELECT COUNT(*) FROM task_submission_tbl s2 WHERE s2.task_id = t.task_id) as submission_count,
					(SELECT COUNT(*) FROM task_submission_tbl s2 WHERE s2.task_id = t.task_id AND LOWER(s2.check_status) = 'approved') as approved_count,
					(SELECT COUNT(*) FROM task_submission_tbl s2 WHERE s2.task_id = t.task_id AND LOWER(s2.check_status) = 'rejected') as rejected_count
				FROM task_submission_tbl ts
				INNER JOIN task_tbl t ON ts.task_id = t.task_id
				INNER JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id
				INNER JOIN user_tbl u ON ts.submitted_by = u.user_id
				INNER JOIN profile_tbl p ON u.profile_id = p.profile_id
				ORDER BY f.datetime_uploaded DESC
			") ?: [];
			
			echo json_encode(["data" => $submissions]);
		} catch(Exception $e) {
			echo json_encode(["data" => [], "error" => $e->getMessage()]);
		}
	}else if($call == 45){
		// Get single submission details
		$submissionId = $_POST['submission_id'] ?? 0;
		
		try {
			$db = Database::getInstance();
			$submission = $db->selectOne("
				SELECT 
					ts.task_submission_id,
					t.task_id,
					t.task_title,
					CONCAT(p.fname, ' ', p.lname) as student_name,
					COALESCE(NULLIF(f.original_filename, ''), f.file_name) as file_name,
					f.file_upload_id,
					ts.check_status,
					f.datetime_uploaded as submitted_at
				FROM task_submission_tbl ts
				INNER JOIN task_tbl t ON ts.task_id = t.task_id
				INNER JOIN file_upload_tbl f ON ts.file_upload_id = f.file_upload_id
				INNER JOIN user_tbl u ON ts.submitted_by = u.user_id
				INNER JOIN profile_tbl p ON u.profile_id = p.profile_id
				WHERE ts.task_submission_id = ?
			", [$submissionId]);
			
			if($submission) {
				$result = ["status" => "SUCCESS", "data" => $submission];
			} else {
				$result = ["status" => "ERROR", "msg" => "Submission not found"];
			}
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 46){
		// Approve submission by updating status to "Approved"
		if (!empty($_POST['approve_by_task'])) {
			$taskId = $_POST['task_id'] ?? 0;
			try {
				$db = Database::getInstance();
				$db->execute("UPDATE task_submission_tbl SET check_status = 'Approved' WHERE task_id = ? AND LOWER(check_status) = 'pending'", [$taskId]);
				$_at = $db->selectOne("SELECT t.assigned_to,t.task_title,t.task_description FROM task_tbl t WHERE t.task_id=?",[$taskId]);
				if ($_at && !empty($_at['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_at['assigned_to'],'type'=>'task_approved','title'=>'Task Approved','message'=>"Your submission for '{$_at['task_title']}' has been approved. {$_at['task_description']}",'related_id'=>$taskId]);
				echo json_encode(['status' => 'SUCCESS', 'msg' => 'Task approved']);
			} catch(Exception $e) {
				echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
			}
			exit;
		}
		$submissionId = $_POST['submission_id'] ?? 0;
		
		try {
			$db = Database::getInstance();
			
			// Get submission details before updating
			$submission = $db->selectOne("
				SELECT ts.*, t.task_title 
				FROM task_submission_tbl ts
				INNER JOIN task_tbl t ON ts.task_id = t.task_id
				WHERE ts.task_submission_id = ?
			", [$submissionId]);
			
			if(!$submission) {
				throw new Exception("Submission not found");
			}
			
			// Update check_status to "Approved" instead of deleting
			$updated = $db->execute("
				UPDATE task_submission_tbl 
				SET check_status = 'Approved' 
				WHERE task_submission_id = ?
			", [$submissionId]);
			
			if($updated) {
				$result = ["status" => "SUCCESS", "msg" => "Submission approved successfully"];
			} else {
				throw new Exception("Failed to approve submission");
			}
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 'transfer_task'){
		// Transfer task to another member
		try {
			$taskId = $_POST['task_id'] ?? 0;
			$newAssignedTo = $_POST['new_assigned_to'] ?? 0;
			$transferReason = $_POST['transfer_reason'] ?? '';
			
			if(!$taskId || !$newAssignedTo) {
				throw new Exception("Task ID and new assignee are required");
			}
			
			if(empty($transferReason)) {
				throw new Exception("Transfer reason is required");
			}
			
			$db = Database::getInstance();
			
			// Get current task info
			$task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ?", [$taskId]);
			if(!$task) {
				throw new Exception("Task not found");
			}
			
			// Get new assignee info
			$newMember = $db->selectOne("
				SELECT u.user_id, p.fname, p.lname 
				FROM user_tbl u 
				JOIN profile_tbl p ON u.profile_id = p.profile_id 
				WHERE u.user_id = ?
			", [$newAssignedTo]);
			
			if(!$newMember) {
				throw new Exception("New assignee not found");
			}
			
			// Update task with new assignee and status
			$updated = $db->execute("
				UPDATE task_tbl 
				SET assigned_to = ?
				WHERE task_id = ?
			", [$newAssignedTo, $taskId]);
			
			if($updated) {
				// Log the transfer in deletion table for audit trail
				$logData = json_encode([
					'task_id' => $taskId,
					'task_title' => $task['task_title'],
					'old_assigned_to' => $task['assigned_to'],
					'new_assigned_to' => $newAssignedTo,
					'new_assignee_name' => $newMember['fname'] . ' ' . $newMember['lname'],
					'transfer_reason' => $transferReason,
					'transferred_by' => $_SESSION['user_id'] ?? 0,
					'transferred_at' => date('Y-m-d H:i:s')
				]);
				
				$db->execute("
					INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted)
					VALUES (?, ?, 'task_tbl', NOW())
				", [$logData, 'Task Transfer: ' . $transferReason]);
				
				// Send notifications
				$notificationManager = new NotificationManager();
				$newMemberFullName = $newMember['fname'] . ' ' . $newMember['lname'];
				
				// Notify the old assignee that their task was transferred away
				if (!empty($task['assigned_to']) && $task['assigned_to'] != $newAssignedTo) {
					$notificationManager->createNotification([
						'user_id' => $task['assigned_to'],
						'type' => 'task_transferred',
						'title' => 'Task Transferred Away',
						'message' => "Task '{$task['task_title']}' has been transferred to {$newMemberFullName}. Reason: {$transferReason}",
						'related_id' => $taskId
					]);
				}
				
				// Notify the new assignee that a task was transferred to them
				$notificationManager->createNotification([
					'user_id' => $newAssignedTo,
					'type' => 'task_transferred',
					'title' => 'Task Transferred to You',
					'message' => "Task '{$task['task_title']}' has been transferred to you. Reason: {$transferReason}",
					'related_id' => $taskId
				]);
				
				$result = ["status" => "SUCCESS", "msg" => "Task transferred successfully to " . $newMemberFullName];
			} else {
				throw new Exception("Failed to transfer task");
			}
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 'extend_deadline'){
		try {
			$taskId      = intval($_POST['task_id'] ?? 0);
			$newDeadline = trim($_POST['new_deadline'] ?? '');
			$reason      = trim($_POST['reason'] ?? '');
			if (!$taskId || !$newDeadline) throw new Exception("Task ID and new deadline are required");
			if (empty($reason)) throw new Exception("Reason is required");
			if ($newDeadline <= date('Y-m-d')) throw new Exception("New deadline must be in the future");
			$db = Database::getInstance();
			$task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ?", [$taskId]);
			if (!$task) throw new Exception("Task not found");
			$db->execute("UPDATE task_tbl SET task_deadline = ?, task_status = 'active' WHERE task_id = ?", [$newDeadline, $taskId]);
			$logData = json_encode([
				'task_id'      => $taskId,
				'task_title'   => $task['task_title'],
				'old_deadline' => $task['task_deadline'],
				'new_deadline' => $newDeadline,
				'reason'       => $reason,
				'extended_by'  => $_SESSION['user_id'] ?? 0,
				'extended_at'  => date('Y-m-d H:i:s')
			]);
			$db->execute("INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted) VALUES (?, ?, 'task_tbl', NOW())",
				[$logData, 'Deadline Extended: ' . $reason]);
			if (!empty($task['assigned_to'])) {
				$notificationManager = new NotificationManager();
				$notificationManager->createNotification([
					'user_id'    => $task['assigned_to'],
					'type'       => 'task_deadline_extended',
					'title'      => 'Task Deadline Extended',
					'message'    => "The deadline for '{$task['task_title']}' has been extended to {$newDeadline}. Reason: {$reason}",
					'related_id' => $taskId
				]);
			}
			$result = ["status" => "SUCCESS", "msg" => "Deadline extended to {$newDeadline}"];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 'reject_submission_by_task'){
		$taskId = $_POST['task_id'] ?? 0;
		try {
			$db = Database::getInstance();
			$_rj = $db->selectOne("SELECT t.assigned_to,t.task_title,t.task_description,s.submitted_by FROM task_tbl t LEFT JOIN task_submission_tbl s ON s.task_id=t.task_id AND LOWER(s.check_status)='pending' WHERE t.task_id=?",[$taskId]);
			$db->execute("DELETE FROM task_submission_tbl WHERE task_id = ? AND LOWER(check_status) = 'pending'", [$taskId]);
			if ($_rj && !empty($_rj['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_rj['assigned_to'],'type'=>'submission_rejected','title'=>'Submission Moved to Pending','message'=>"Your submission for '{$_rj['task_title']}' has been moved back to pending. {$_rj['task_description']}",'related_id'=>$taskId]);
			echo json_encode(['status' => 'SUCCESS', 'msg' => 'Submission rejected, task moved to pending']);
		} catch(Exception $e) {
			echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
		}
		exit;
	}else if($call == 'revert_to_pending'){
		$taskId = $_POST['task_id'] ?? 0;
		try {
			$db = Database::getInstance();
			$_rv = $db->selectOne("SELECT t.assigned_to,t.task_title,t.task_description FROM task_tbl t WHERE t.task_id=?",[$taskId]);
			$db->execute("UPDATE task_submission_tbl SET check_status = 'pending' WHERE task_id = ? AND LOWER(check_status) = 'approved'", [$taskId]);
			if ($_rv && !empty($_rv['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$_rv['assigned_to'],'type'=>'task_reverted','title'=>'Task Moved to Awaiting Review','message'=>"Task '{$_rv['task_title']}' has been moved back to awaiting review. {$_rv['task_description']}",'related_id'=>$taskId]);
			echo json_encode(['status' => 'SUCCESS', 'msg' => 'Reverted to awaiting review']);
		} catch(Exception $e) {
			echo json_encode(['status' => 'ERROR', 'msg' => $e->getMessage()]);
		}
		exit;
	}else if($call == 'close_cancel_task'){
		// Close or cancel a task
		try {
			$taskId = $_POST['task_id'] ?? 0;
			$action = $_POST['action'] ?? ''; // 'close' or 'cancel'
			$reason = $_POST['reason'] ?? '';
			
			if(!$taskId) {
				throw new Exception("Task ID is required");
			}
			
			if(!in_array($action, ['close', 'cancel'])) {
				throw new Exception("Invalid action. Must be 'close' or 'cancel'");
			}
			
			if(empty($reason)) {
				throw new Exception("Reason is required");
			}
			
			$db = Database::getInstance();
			
			// Get task info
			$task = $db->selectOne("SELECT * FROM task_tbl WHERE task_id = ?", [$taskId]);
			if(!$task) {
				throw new Exception("Task not found");
			}
			
			// Determine new status
			$newStatus = $action === 'close' ? 'closed' : 'cancelled';
			
			// Update task status
			$updated = $db->execute("
				UPDATE task_tbl 
				SET task_status = ?
				WHERE task_id = ?
			", [$newStatus, $taskId]);
			
			if($updated) {
				// Log the action in deletion table for audit trail
				$logData = json_encode([
					'task_id' => $taskId,
					'task_title' => $task['task_title'],
					'task_category_id' => $task['task_category_id'],
					'assigned_to' => $task['assigned_to'],
					'action' => $action,
					'new_status' => $newStatus,
					'reason' => $reason,
					'actioned_by' => $_SESSION['user_id'] ?? 0,
					'actioned_at' => date('Y-m-d H:i:s')
				]);
				
				$db->execute("
					INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted)
					VALUES (?, ?, 'task_tbl', NOW())
				", [$logData, ucfirst($action) . ' Task: ' . $reason]);
				
				$actionText = $action === 'close' ? 'closed' : 'cancelled';
				$result = ["status" => "SUCCESS", "msg" => "Task {$actionText} successfully"];
				if (!empty($task['assigned_to'])) (new NotificationManager())->createNotification(['user_id'=>$task['assigned_to'],'type'=>'task_'.$action,'title'=>'Task '.ucfirst($actionText),'message'=>"Task '{$task['task_title']}' has been {$actionText}. Reason: {$reason}",'related_id'=>$taskId]);
			} else {
				throw new Exception("Failed to update task status");
			}
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 64){
		// Generic update handler for entry module
		try {
			$data = $_POST['DATA'] ?? [];
			$id = intval($data['id'] ?? 0);
			$table = trim($data['table'] ?? '');
			$value = trim($data['value'] ?? '');
			$title = trim($data['title'] ?? 'Entry');
			
			// Validate input
			if($id <= 0) {
				throw new Exception("Invalid ID");
			}
			if(empty($table)) {
				throw new Exception("Invalid table");
			}
			if(empty($value)) {
				throw new Exception("Value cannot be empty");
			}
			
			// Whitelist allowed tables for security
			$allowedTables = [
				'position_tbl' => ['column' => 'position', 'id_column' => 'position_id'],
				'file_category_tbl' => ['column' => 'file_category', 'id_column' => 'file_category_id'],
				'task_category_tbl' => ['column' => 'task_category', 'id_column' => 'task_category_id']
			];
			
			if(!isset($allowedTables[$table])) {
				throw new Exception("Unauthorized table access");
			}
			
			$column = $allowedTables[$table]['column'];
			$idColumn = $allowedTables[$table]['id_column'];
			
			$db = Database::getInstance();
			
			// Check if entry exists
			$exists = $db->selectOne("SELECT * FROM {$table} WHERE {$idColumn} = ?", [$id]);
			if(!$exists) {
				throw new Exception("{$title} not found");
			}
			
			// Build update query — include access_restriction for position_tbl
			if($table === 'position_tbl' && isset($data['access_restriction'])) {
				$accessRestriction = (int)$data['access_restriction'];
				if(!in_array($accessRestriction, [1, 2, 3])) {
					throw new Exception("Invalid access restriction value. Must be 1, 2, or 3.");
				}
				$updated = $db->execute(
					"UPDATE {$table} SET {$column} = ?, access_restriction = ? WHERE {$idColumn} = ?",
					[$value, $accessRestriction, $id]
				);
			} else {
				$updated = $db->execute(
					"UPDATE {$table} SET {$column} = ? WHERE {$idColumn} = ?",
					[$value, $id]
				);
			}
			
			if($updated) {
				$result = ["status" => "SUCCESS", "msg" => "{$title} updated successfully"];
			} else {
				throw new Exception("Failed to update {$title}");
			}
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 65){
		// Generic delete handler for entry module
		try {
			$data = $_POST['DATA'] ?? [];
			$id = intval($data['id'] ?? 0);
			$table = trim($data['table'] ?? '');
			$reason = trim($data['reason'] ?? '');
			$title = trim($data['title'] ?? 'Entry');
			
			// Validate input
			if($id <= 0) {
				throw new Exception("Invalid ID");
			}
			if(empty($table)) {
				throw new Exception("Invalid table");
			}
			if(empty($reason)) {
				throw new Exception("Deletion reason is required");
			}
			
			// Whitelist allowed tables for security
			$allowedTables = [
				'position_tbl' => ['column' => 'position', 'id_column' => 'position_id'],
				'file_category_tbl' => ['column' => 'file_category', 'id_column' => 'file_category_id'],
				'task_category_tbl' => ['column' => 'task_category', 'id_column' => 'task_category_id']
			];
			
			if(!isset($allowedTables[$table])) {
				throw new Exception("Unauthorized table access");
			}
			
			$column = $allowedTables[$table]['column'];
			$idColumn = $allowedTables[$table]['id_column'];
			
			$db = Database::getInstance();
			
			// Get entry data before deletion for logging
			$entry = $db->selectOne("SELECT * FROM {$table} WHERE {$idColumn} = ?", [$id]);
			if(!$entry) {
				throw new Exception("{$title} not found");
			}
			
			// Log the deletion to deleted_record_tbl
			$deletionData = json_encode($entry);
			$logged = $db->execute(
				"INSERT INTO deleted_record_tbl (data_deleted, reason_for_deletion, table_origin, datetime_deleted) VALUES (?, ?, ?, NOW())",
				[$deletionData, $reason, $table]
			);
			
			if(!$logged) {
				throw new Exception("Failed to log deletion");
			}
			
			// Delete the entry
			$deleted = $db->execute("DELETE FROM {$table} WHERE {$idColumn} = ?", [$id]);
			
			if($deleted) {
				$result = ["status" => "SUCCESS", "msg" => "{$title} deleted successfully"];
			} else {
				throw new Exception("Failed to delete {$title}");
			}
			
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 66){
		// Get distinct category_tag values from file_upload_tbl (only categories with actual files)
		try {
			$db = Database::getInstance();
			$categories = $db->select("
				SELECT 
					category_tag,
					COUNT(file_upload_id) as file_count
				FROM file_upload_tbl
				WHERE category_tag IS NOT NULL
				  AND TRIM(category_tag) != ''
				GROUP BY category_tag
				ORDER BY category_tag ASC
			");
			$result = ["data" => $categories ?: []];
		} catch(Exception $e) {
			$result = ["data" => [], "error" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 67){
		// Get positions for subadmins (access_restriction = 2) from position_tbl
		try {
			$db = Database::getInstance();
			$positions = $db->select(
				"SELECT position_id, position FROM position_tbl WHERE access_restriction = 2 ORDER BY position ASC"
			);
			$result = ["status" => "SUCCESS", "data" => $positions ?: []];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "data" => [], "msg" => $e->getMessage()];
		}
		echo json_encode($result);
	}else if($call == 68){
		try {
			$userManager = new UserManager();
			$users = $userManager->getAllUsers();
			$result = ["data" => $users ?: []];
		} catch(Exception $e) {
			$result = ["data" => []];
		}
		echo json_encode($result);
	}else if($call == 69){
		$user_id = $_POST['user_id'] ?? 0;
		try {
			$userManager = new UserManager();
			$permissions = $userManager->getUserPermissions($user_id);
			$result = ["status" => "SUCCESS", "data" => $permissions ?: []];
		} catch(Exception $e) {
			$result = ["status" => "ERROR", "data" => []];
		}
		echo json_encode($result);
		exit;
	}

	// ML Classification System AJAX Handlers
	if ($_POST['CALL'] === 'ml_operations') {
		require_once("../resources/objects/ml_service.php");
		
		$action = $_POST['action'] ?? '';
		$mlService = new MLClassificationService();
		$result = ['success' => false];
		
		try {
			switch($action) {
			case 'upload_ml_dataset':
				// Validate file upload
				if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
					throw new Exception("No file uploaded or upload error");
				}
				
				$datasetName = $_POST['dataset_name'] ?? '';
				$description = $_POST['description'] ?? '';
				
				if (empty($datasetName)) {
					throw new Exception("Dataset name is required");
				}
				
				// Upload and validate dataset
				$uploadResult = $mlService->uploadDataset(
					$_FILES['csv_file'],
					$datasetName,
					$description,
					$_SESSION['user_id']
				);
				
				// Check if upload was successful
				if (!$uploadResult['success']) {
					throw new Exception($uploadResult['error'] ?? 'Upload failed');
				}
				
				$result = [
					'success' => true,
					'dataset_id' => $uploadResult['dataset_id'] ?? 0,
					'dataset_name' => $datasetName,
					'total_samples' => $uploadResult['total_samples'] ?? 0,
					'categories_count' => $uploadResult['categories_count'] ?? 0,
					'message' => 'Dataset uploaded successfully'
				];
				break;				case 'train_ml_model':
					$datasetId = intval($_POST['dataset_id'] ?? 0);
					$modelName = $_POST['model_name'] ?? '';
					$algorithm = $_POST['algorithm'] ?? 'svm';
					$testSize = floatval($_POST['test_size'] ?? 20) / 100;
					
					if ($datasetId <= 0 || empty($modelName)) {
						throw new Exception("Invalid parameters");
					}
					
					// Train the model
					$trainResult = $mlService->trainModel(
						$datasetId,
						$modelName,
						$algorithm,
						$_SESSION['user_id'],
						$testSize
					);
					
					// Check if training was successful
					if (!isset($trainResult['success']) || !$trainResult['success']) {
						throw new Exception($trainResult['error'] ?? 'Training failed');
					}
					
					$result = [
						'success' => true,
						'model_id' => $trainResult['model_id'] ?? 0,
						'model_name' => $modelName,
						'accuracy' => $trainResult['accuracy'] ?? 0,
						'precision' => $trainResult['precision'] ?? 0,
						'recall' => $trainResult['recall'] ?? 0,
						'f1_score' => $trainResult['f1_score'] ?? 0,
						'categories_count' => $trainResult['categories_count'] ?? 0,
						'message' => 'Model trained successfully'
					];
					break;
					
				case 'activate_ml_model':
					$modelId = intval($_POST['model_id'] ?? 0);
					
					if ($modelId <= 0) {
						throw new Exception("Invalid model ID");
					}
					
					if ($mlService->activateModel($modelId)) {
						$result = [
							'success' => true,
							'message' => 'Model activated successfully'
						];
					} else {
						throw new Exception("Failed to activate model");
					}
					break;
					
				case 'delete_ml_model':
					$modelId = intval($_POST['model_id'] ?? 0);
					
					if ($modelId <= 0) {
						throw new Exception("Invalid model ID");
					}
					
					$deleteResult = $mlService->deleteModel($modelId);
					if ($deleteResult['success'] ?? false) {
						$result = [
							'success' => true,
							'message' => 'Model deleted successfully'
						];
					} else {
						throw new Exception($deleteResult['error'] ?? "Failed to delete model");
					}
					break;
					
				case 'delete_ml_dataset':
					$datasetId = intval($_POST['dataset_id'] ?? 0);
					
					if ($datasetId <= 0) {
						throw new Exception("Invalid dataset ID");
					}
					
					// Delete dataset and associated models
					$db = Database::getInstance();
					
					// Get dataset file path
					$dataset = $db->selectOne("SELECT file_path FROM ml_training_datasets_tbl WHERE dataset_id = ?", [$datasetId]);
					
					if (!$dataset) {
						throw new Exception("Dataset not found");
					}
					
					// Delete associated models first
					$models = $db->select("SELECT model_id FROM ml_models_tbl WHERE dataset_id = ?", [$datasetId]);
					foreach ($models as $model) {
						$mlService->deleteModel($model['model_id']);
					}
					
					// Delete dataset file
					$filePath = __DIR__ . '/../' . $dataset['file_path'];
					if (file_exists($filePath)) {
						unlink($filePath);
					}
					
					// Delete dataset record
					$db->execute("DELETE FROM ml_training_datasets_tbl WHERE dataset_id = ?", [$datasetId]);
					
					$result = [
						'success' => true,
						'message' => 'Dataset deleted successfully'
					];
					break;
					
				case 'view_dataset':
					$filePath = __DIR__ . '/../' . ($_POST['file_path'] ?? '');
					if (!file_exists($filePath)) throw new Exception('Dataset file not found');
					$data = [];
					$fp = fopen($filePath, 'r');
					$header = fgetcsv($fp);
					while (($row = fgetcsv($fp)) !== false) {
						if (count($row) >= 2 && !empty(trim($row[0]))) {
							$data[] = ['text' => trim($row[0]), 'category' => trim($row[1])];
						}
					}
					fclose($fp);
					$result = ['success' => true, 'data' => $data];
					break;
				
				case 'generate_dataset_from_files':
					require_once '../resources/objects/text_extractor.php';
					$mode = $_POST['mode'] ?? 'new';
					$files = $_FILES['files'] ?? null;
					$cats = $_POST['categories'] ?? [];
					if (!$files || empty($files['name'][0])) throw new Exception('No files uploaded');
					$textExtractor = new TextExtractor();
					$samples = [];
					$trainingDir = __DIR__ . '/../uploads/training_files/';
					for ($i = 0; $i < count($files['name']); $i++) {
						if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
						$tmpPath = $files['tmp_name'][$i];
						$origName = $files['name'][$i];
						$mimeType = $files['type'][$i];
						$cat = $cats[$i] ?? 'Others';
						$catSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($cat)), '-');
						$catDir = $trainingDir . $catSlug . '/';
						if (!is_dir($catDir)) mkdir($catDir, 0755, true);
						$storedName = uniqid() . '_' . $origName;
						move_uploaded_file($tmpPath, $catDir . $storedName);
						$extractResult = $textExtractor->extractText($catDir . $storedName, $mimeType);
						if (is_array($extractResult) && ($extractResult['success'] ?? false)) {
							$samples[] = ['text' => $extractResult['text'], 'category' => $cat];
						} elseif (is_string($extractResult) && !empty($extractResult)) {
							$samples[] = ['text' => $extractResult, 'category' => $cat];
						}
					}
					if (empty($samples)) throw new Exception('No text could be extracted from uploaded files');
					$dbInst = Database::getInstance();
					if ($mode === 'append') {
						$dsId = intval($_POST['dataset_id'] ?? 0);
						$activeDs = $dsId ? $dbInst->selectOne("SELECT * FROM ml_training_datasets_tbl WHERE dataset_id = ?", [$dsId]) : $dbInst->selectOne("SELECT * FROM ml_training_datasets_tbl ORDER BY dataset_id DESC LIMIT 1");
						if (!$activeDs) throw new Exception('No active dataset found');
						$csvPath = __DIR__ . '/../' . $activeDs['file_path'];
						$fp = fopen($csvPath, 'a');
						foreach ($samples as $s) { fputcsv($fp, [$s['text'], $s['category']]); }
						fclose($fp);
						$newTotal = intval($activeDs['total_samples']) + count($samples);
						$existingCats = json_decode($activeDs['categories'], true) ?: [];
						foreach ($samples as $s) { if (!in_array($s['category'], $existingCats)) $existingCats[] = $s['category']; }
						$dbInst->execute("UPDATE ml_training_datasets_tbl SET total_samples = ?, categories_count = ?, categories = ? WHERE dataset_id = ?", [$newTotal, count($existingCats), json_encode($existingCats), $activeDs['dataset_id']]);
						$result = ['success' => true, 'message' => 'Appended ' . count($samples) . ' samples to existing dataset', 'total_samples' => $newTotal];
					} else {
						$filename = uniqid('dataset_', true) . '_' . time() . '.csv';
						$csvDir = __DIR__ . '/../uploads/ml_datasets/';
						if (!is_dir($csvDir)) mkdir($csvDir, 0755, true);
						$csvPath = $csvDir . $filename;
						$fp = fopen($csvPath, 'w');
						fputcsv($fp, ['text', 'category']);
						foreach ($samples as $s) { fputcsv($fp, [$s['text'], $s['category']]); }
						fclose($fp);
						$allCats = array_values(array_unique(array_column($samples, 'category')));
						$dsId = $dbInst->insert("INSERT INTO ml_training_datasets_tbl (dataset_name, file_path, total_samples, categories_count, categories, uploaded_by, description) VALUES (?, ?, ?, ?, ?, ?, ?)", ['Generated from Files ' . date('Y-m-d H:i'), 'uploads/ml_datasets/' . $filename, count($samples), count($allCats), json_encode($allCats), $_SESSION['user_id'], 'Auto-generated from uploaded training files']);
						$result = ['success' => true, 'message' => 'Created new dataset with ' . count($samples) . ' samples', 'total_samples' => count($samples), 'dataset_id' => $dsId];
					}
					break;
				
				case 'get_incremental_queue':
					$filterStatus = $_POST['filter_status'] ?? '';
					$filterCategory = $_POST['filter_category'] ?? '';
					$where = '1=1';
					$params = [];
					if ($filterStatus) { $where .= ' AND status = ?'; $params[] = $filterStatus; }
					if ($filterCategory) { $where .= ' AND confirmed_category = ?'; $params[] = $filterCategory; }
					$rows = Database::getInstance()->select("SELECT * FROM ml_incremental_training_queue_tbl WHERE $where ORDER BY created_at DESC", $params);
					$result = ['success' => true, 'data' => $rows];
					break;
				case 'save_ml_settings':
					$settings = [
						'classification_method' => $_POST['classification_method'] ?? 'hybrid',
						'ml_confidence_threshold' => $_POST['ml_confidence_threshold'] ?? 60,
						'default_ml_algorithm' => $_POST['default_ml_algorithm'] ?? 'svm',
						'nlp_fallback_enabled' => $_POST['nlp_fallback_enabled'] ?? '1',
						'incremental_learning_enabled' => $_POST['incremental_learning_enabled'] ?? '0',
						'incremental_batch_size' => $_POST['incremental_batch_size'] ?? '10'
					];
					
					foreach ($settings as $key => $value) {
						$mlService->updateSetting($key, $value, $_SESSION['user_id']);
					}
					
					$result = [
						'success' => true,
						'message' => 'Settings saved successfully'
					];
					break;
					
				// ML Feedback Operations
				case 'accept_prediction':
					$predictionId = intval($_POST['prediction_id'] ?? 0);
					$actualCategory = $_POST['actual_category'] ?? '';
					
					if ($predictionId <= 0) {
						throw new Exception("Invalid prediction ID");
					}
					
					$updated = $db->execute(
						"UPDATE ml_prediction_history_tbl 
						 SET was_accepted = 1, actual_category = ? 
						 WHERE prediction_id = ?",
						[$actualCategory, $predictionId]
					);
					
					$result = [
						'success' => $updated,
						'message' => $updated ? 'Prediction accepted' : 'Failed to accept prediction'
					];
					break;
					
				case 'reject_prediction':
					$predictionId = intval($_POST['prediction_id'] ?? 0);
					
					if ($predictionId <= 0) {
						throw new Exception("Invalid prediction ID");
					}
					
					$updated = $db->execute(
						"UPDATE ml_prediction_history_tbl 
						 SET was_accepted = 0 
						 WHERE prediction_id = ?",
						[$predictionId]
					);
					
					$result = [
						'success' => $updated,
						'message' => $updated ? 'Prediction rejected' : 'Failed to reject prediction'
					];
					break;
					
				case 'edit_prediction_category':
					$predictionId = intval($_POST['prediction_id'] ?? 0);
					$fileUploadId = intval($_POST['file_upload_id'] ?? 0);
					$correctCategory = $_POST['correct_category'] ?? '';
					
					if ($predictionId <= 0 || $fileUploadId <= 0 || empty($correctCategory)) {
						throw new Exception("Invalid parameters");
					}
					
					// Update prediction as accepted with corrected category
					$db->execute(
						"UPDATE ml_prediction_history_tbl 
						 SET was_accepted = 1, actual_category = ? 
						 WHERE prediction_id = ?",
						[$correctCategory, $predictionId]
					);
					
					// Also update the file's actual category
					$db->execute(
						"UPDATE file_upload_tbl 
						 SET category_tag = ? 
						 WHERE file_upload_id = ?",
						[$correctCategory, $fileUploadId]
					);
					
					$result = [
						'success' => true,
						'message' => 'Category corrected and accepted'
					];
					break;
					
				case 'accept_all_high_confidence':
					$updated = $db->execute(
						"UPDATE ml_prediction_history_tbl 
						 SET was_accepted = 1, actual_category = predicted_category 
						 WHERE was_accepted IS NULL 
						   AND confidence_score >= 0.90"
					);
					
					$count = $db->selectOne(
						"SELECT ROW_COUNT() as count"
					)['count'] ?? 0;
					
					$result = [
						'success' => true,
						'count' => $count,
						'message' => "Accepted $count high-confidence predictions"
					];
					break;
					
				case 'export_accepted_predictions':
					// Get all accepted predictions
					$predictions = $db->select("
						SELECT 
							nlp.extracted_text as text,
							mlp.actual_category as category
						FROM ml_prediction_history_tbl mlp
						JOIN file_upload_tbl f ON mlp.file_upload_id = f.file_upload_id
						JOIN file_nlp_analysis_tbl nlp ON f.file_upload_id = nlp.file_upload_id
						WHERE mlp.was_accepted = 1
						  AND nlp.extracted_text IS NOT NULL
						  AND nlp.extracted_text != ''
						ORDER BY mlp.predicted_at DESC
					");
					
					if (empty($predictions)) {
						throw new Exception("No accepted predictions to export");
					}
					
					// Generate CSV
					$csv = "text,category\n";
					foreach ($predictions as $pred) {
						$text = str_replace('"', '""', $pred['text']); // Escape quotes
						$category = str_replace('"', '""', $pred['category']);
						$csv .= "\"$text\",\"$category\"\n";
					}
					
					// Send as download
					header('Content-Type: text/csv');
					header('Content-Disposition: attachment; filename="ml_feedback_' . date('Y-m-d') . '.csv"');
					echo $csv;
					exit;
					
				case 'retrain_with_feedback':
					// Get accepted predictions and create temporary dataset
					$predictions = $db->select("
						SELECT 
							nlp.extracted_text as text,
							mlp.actual_category as category
						FROM ml_prediction_history_tbl mlp
						JOIN file_upload_tbl f ON mlp.file_upload_id = f.file_upload_id
						JOIN file_nlp_analysis_tbl nlp ON f.file_upload_id = nlp.file_upload_id
						WHERE mlp.was_accepted = 1
						  AND nlp.extracted_text IS NOT NULL
						  AND nlp.extracted_text != ''
					");
					
					if (empty($predictions)) {
						throw new Exception("No accepted predictions available for retraining");
					}
					
					// Create temporary CSV file
					$tempDir = __DIR__ . '/../uploads/ml_datasets/';
					$tempFile = $tempDir . 'feedback_' . time() . '.csv';
					
					$csv = "text,category\n";
					foreach ($predictions as $pred) {
						$text = str_replace('"', '""', $pred['text']);
						$category = str_replace('"', '""', $pred['category']);
						$csv .= "\"$text\",\"$category\"\n";
					}
					
					file_put_contents($tempFile, $csv);
					
					// Upload as new dataset
					$uploadResult = $mlService->uploadDataset(
						[
							'tmp_name' => $tempFile,
							'name' => 'feedback_' . time() . '.csv',
							'size' => filesize($tempFile),
							'error' => UPLOAD_ERR_OK
						],
						'Feedback Training Data ' . date('Y-m-d'),
						$_SESSION['user_id'],
						'Automatically generated from accepted ML predictions'
					);
					
					// Train new model
					$algorithm = $mlService->getSetting('default_ml_algorithm') ?? 'svm';
					$trainResult = $mlService->trainModel(
						$uploadResult['dataset_id'],
						'Feedback Model ' . date('Y-m-d H:i'),
						$algorithm,
						$_SESSION['user_id'],
						0.2
					);
					
					// Clean up temp file
					unlink($tempFile);
					
					// Activate the new model
					$mlService->activateModel($trainResult['model_id']);
					
					$result = [
						'success' => true,
						'model_id' => $trainResult['model_id'],
						'accuracy' => $trainResult['accuracy'],
						'training_samples' => $uploadResult['total_samples'],
						'message' => 'Model retrained and activated successfully'
					];
					break;
					
				case 'process_ml_queue':
					// Process incremental ML training queue
					require_once '../resources/objects/ml_service_incremental.php';
					$incrementalML = new IncrementalMLService();
					
					$batchSize = $_POST['batch_size'] ?? 20; // Process 20 by default
					$result = $incrementalML->processTrainingQueue($batchSize);
					break;
					
				default:
					throw new Exception("Unknown action: $action");
			}
			
		} catch (Exception $e) {
			$result = [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
		
		header('Content-Type: application/json');
		echo json_encode($result);
		exit;
	}




	// Include permission management endpoints
	// System Settings
	if (isset($_POST['CALL']) && $_POST['CALL'] === 'save_setting') {
		$key = $_POST['key'] ?? '';
		$value = $_POST['value'] ?? '';
		$allowed = ['show_upload_disclaimer', 'allow_manual_override', 'override_roles_file_explorer', 'override_roles_batch_upload'];
		if (!in_array($key, $allowed)) {
			echo json_encode(['status' => 'ERROR', 'msg' => 'Invalid setting']);
			exit;
		}
		$db = Database::getInstance();
		$db->execute("INSERT INTO system_settings_tbl (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()", [$key, $value, $value]);
		echo json_encode(['status' => 'SUCCESS']);
		exit;
	}

	if (isset($_POST['CALL']) && $_POST['CALL'] === 'get_setting') {
		$key = $_POST['key'] ?? '';
		$db = Database::getInstance();
		$row = $db->selectOne("SELECT setting_value FROM system_settings_tbl WHERE setting_key = ?", [$key]);
		echo json_encode(['status' => 'SUCCESS', 'value' => $row['setting_value'] ?? null]);
		exit;
	}

	include_once('ajax_permissions.php');
