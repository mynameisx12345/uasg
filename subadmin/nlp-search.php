<?php
session_start();
require_once("../resources/session.php");

// Require subadmin/adviser role
$session = SessionManager::getInstance();
$session->requireRole(['Adviser', 'adviser', 'Subadmin', 'subadmin']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId) {
    header("Location: ../index.php");
    exit;
}

// Check if user has permission to view files
require_once("../resources/objects/permission_class.php");
$canView = SubadminPermission::hasPermission($userId, 'file_management', 'view');

if (!$canView) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>NLP File Search - Subadmin</title>
	<link rel="stylesheet" href="../resources/style.css">
	<link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
	<style>
		/* Dropdown Menu Styles */
		.dropdown-container {
			position: relative;
			display: inline-block;
		}
		.dropdown-btn {
			background: #6c757d;
			color: white;
			border: none;
			padding: 0.4rem 0.6rem;
			font-size: 1.2rem;
			cursor: pointer;
			border-radius: 4px;
			line-height: 1;
		}
		.dropdown-btn:hover {
			background: #5a6268;
		}
		.dropdown-menu {
			display: none;
			position: absolute;
			right: 0;
			top: 100%;
			background: white;
			min-width: 140px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			border-radius: 4px;
			z-index: 1000;
			margin-top: 4px;
		}
		.dropdown-menu.show {
			display: block;
		}
		.dropdown-item {
			display: block;
			width: 100%;
			padding: 0.5rem 1rem;
			text-align: left;
			border: none;
			background: none;
			cursor: pointer;
			font-size: 0.9rem;
			transition: background 0.2s;
			border-bottom: 1px solid #f0f0f0;
		}
		.dropdown-item:last-child {
			border-bottom: none;
		}
		.dropdown-item:hover {
			background: #f8f9fa;
		}
		.dropdown-item.view { color: #007bff; }
		.dropdown-item.download { color: #28a745; }
	</style>
	<script src='../js/all.js'></script>
	<script src='../js/jquery.js'></script>
	<script src='../js/datatable.js'></script>
</head>
<body>
	<div class="dashboard-container">
		<?php require_once("sidebar.php");?>
		
		<div class="main-content">
			<?php require_once("header.php");?>
			
			<div class="dashboard-content">
				<div class="card">
					<h2>NLP-Based File Search</h2>
					<div class="compact-form">
						<h3>Search Files by Content</h3>
						<div class="form-columns">
							<div class="form-column">
								<div class="form-section">
									<h4>Search Options</h4>
									<div class="form-group">
										<label for="nlpSearchWord">Search Word</label>
										<input type="text" id="nlpSearchWord" name="search_word" placeholder="Enter keyword or phrase">
									</div>
									<div class="form-group">
										<label for="nlpCategoryFilter">Category</label>
										<select id="nlpCategoryFilter" name="category_filter">
											<option value="">All Categories</option>
										</select>
									</div>
									<div class="form-group">
										<button type="button" class="btn-primary" id="nlpSearchBtn">
											<i class="fa fa-search"></i> Search
										</button>
									</div>
								</div>
							</div>
							<div class="form-column">
								<div class="form-section">
									<h4>Quick Stats</h4>
									<div class="form-group">
										<label for="nlpTotalFiles">Total Files</label>
										<input type="text" id="nlpTotalFiles" name="total_files" readonly value="0">
									</div>
									<div class="form-group">
										<label for="nlpTotalCategories">Categories</label>
										<input type="text" id="nlpTotalCategories" name="total_categories" readonly value="0">
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="table-container">
						<table class='data-table' id='nlpFilesTable'>
							<thead>
								<tr>
									<th>ID</th>
									<th>File Name</th>
									<th>Category</th>
									<th>Type</th>
									<th>Uploaded By</th>
									<th>Date Uploaded</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Loading Modal -->
	<div id='loadingModal' class='modal'>
		<div class='modal-content modal-content-small'>
			<h2>Processing...</h2>
			<div style='text-align: center; margin: 20px 0;'>
				<i class='fas fa-spinner fa-spin' style='font-size: 48px; color: #2196F3;'></i>
			</div>
			<p style='text-align: center;'>Please wait while we search files.</p>
		</div>
	</div>

	<!-- File Details Modal -->
	<div id="fileDetailsModal" class="modal" style="display: none;">
		<div class="modal-content" style="max-width: 600px;">
			<div class="modal-header">
				<h2>File Details</h2>
				<span class="close" onclick="closeFileDetailsModal()">&times;</span>
			</div>
			<div id="fileDetailsContent" style="padding: 20px 0;">
				<!-- Content will be populated by JavaScript -->
			</div>
			<div class="modal-footer">
				<button class="btn-secondary" onclick="closeFileDetailsModal()">Close</button>
			</div>
		</div>
	</div>

	<!-- Notification Modal -->
	<div id="notificationModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<h2 id="notificationTitle">Notification</h2>
				<span class="close" onclick="closeNotificationModal()">&times;</span>
			</div>
			<div class="modal-body">
				<p id="notificationMessage"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn-primary" onclick="closeNotificationModal()">OK</button>
			</div>
		</div>
	</div>

	<script>
	// Modal functions
	function closeFileDetailsModal() {
		document.getElementById('fileDetailsModal').style.display = 'none';
	}

	function showFileDetails(fileName, fileSize, mimeType, dateUploaded, category, uploadedBy) {
		const modal = document.getElementById('fileDetailsModal');
		const content = document.getElementById('fileDetailsContent');
		
		const formatSize = (bytes) => {
			if (bytes === 0) return '0 Bytes';
			const k = 1024;
			const sizes = ['Bytes', 'KB', 'MB', 'GB'];
			const i = Math.floor(Math.log(bytes) / Math.log(k));
			return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
		};
		
		content.innerHTML = `
			<table style="width: 100%; border-collapse: collapse;">
				<tr style="border-bottom: 1px solid #eee;">
					<td style="padding: 12px 0; font-weight: 600; color: #555; width: 40%;">File Name:</td>
					<td style="padding: 12px 0; color: #333;">${fileName}</td>
				</tr>
				<tr style="border-bottom: 1px solid #eee;">
					<td style="padding: 12px 0; font-weight: 600; color: #555;">File Size:</td>
					<td style="padding: 12px 0; color: #333;">${formatSize(fileSize)}</td>
				</tr>
				<tr style="border-bottom: 1px solid #eee;">
					<td style="padding: 12px 0; font-weight: 600; color: #555;">File Type:</td>
					<td style="padding: 12px 0; color: #333;">${mimeType}</td>
				</tr>
				<tr style="border-bottom: 1px solid #eee;">
					<td style="padding: 12px 0; font-weight: 600; color: #555;">Date Uploaded:</td>
					<td style="padding: 12px 0; color: #333;">${dateUploaded}</td>
				</tr>
				<tr style="border-bottom: 1px solid #eee;">
					<td style="padding: 12px 0; font-weight: 600; color: #555;">Category:</td>
					<td style="padding: 12px 0; color: #333;">${category || 'N/A'}</td>
				</tr>
				<tr>
					<td style="padding: 12px 0; font-weight: 600; color: #555;">Uploaded By:</td>
					<td style="padding: 12px 0; color: #333;">${uploadedBy || 'Unknown'}</td>
				</tr>
			</table>
		`;
		
		modal.style.display = 'flex';
	}

	function closeNotificationModal() {
		document.getElementById('notificationModal').style.display = 'none';
	}

	window.openModal = function(status, message) {
		const title = status === 'SUCCESS' ? 'Success' : 
					  status === 'ERROR' ? 'Error' : 
					  status === 'WARNING' ? 'Warning' : 'Information';
		document.getElementById('notificationTitle').textContent = title;
		document.getElementById('notificationMessage').innerHTML = message;
		
		const modal = document.getElementById('notificationModal');
		modal.className = 'modal ' + status.toLowerCase();
		modal.style.display = 'flex';
	};

	// Dropdown toggle function
	function toggleDropdown(event) {
		event.stopPropagation();
		const dropdownMenu = event.target.nextElementSibling;
		const isOpen = dropdownMenu.classList.contains('show');
		
		document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
			menu.classList.remove('show');
		});
		
		if (!isOpen) {
			dropdownMenu.classList.add('show');
		}
	}

	document.addEventListener('click', function(event) {
		if (!event.target.matches('.dropdown-btn')) {
			document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
				menu.classList.remove('show');
			});
		}
	});

	$(document).ready(function(){
		let nlpFilesTable;
		const currentUserId = <?= $userId ?>;
		
		// Load categories
		function loadNlpCategories() {
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: { CALL: 20 }, // Get file categories (existing call)
				dataType: 'json',
				success: function(result) {
					if(result.status === 'SUCCESS' && result.data) {
						let options = '<option value="">All Categories</option>';
						result.data.forEach(category => {
							options += `<option value="${category.file_category_id}">${category.file_category}</option>`;
						});
						$('#nlpCategoryFilter').html(options);
						$('#nlpTotalCategories').val(result.data.length);
					}
				}
			});
		}
		loadNlpCategories();
		
		// Initialize DataTable
		function initNlpFilesTable(searchWord = '', categoryId = '') {
			if(nlpFilesTable) {
				nlpFilesTable.destroy();
				$('#nlpFilesTable tbody').empty();
			}
			
			nlpFilesTable = $('#nlpFilesTable').DataTable({
				ajax: {
					url: 'ajax.php',
					type: 'POST',
					data: {
						CALL: 'nlp_search_files',
						SEARCH_WORD: searchWord,
						CATEGORY_ID: categoryId
					},
					dataType: 'json',
					dataSrc: 'data',
					beforeSend: function() {
						$('#loadingModal').show();
					},
					complete: function() {
						$('#loadingModal').hide();
					},
					error: function(xhr, error, thrown) {
						console.error('AJAX Error:', error);
						openModal('ERROR', 'Error loading files: ' + error);
					}
				},
				responsive: true,
				paging: true,
				pageLength: 25,
				columns: [
					{ data: "file_upload_id" },
					{ data: "file_name" },
					{ data: "file_category" },
					{ data: "mime_type" },
					{ data: null, render: function(data) { 
						return data.fname && data.lname ? data.fname + " " + data.lname : "Unknown"; 
					}},
					{ data: "datetime_uploaded", render: d => d ? new Date(d).toLocaleDateString() : "N/A" },
					{ 
						data: null,
						orderable: false,
						render: function(data, type, row) {
							const fileId = row.file_upload_id;
							const filePath = row.file_path || '';
							const fileName = row.file_name || '';
							const fileSize = row.file_size || 0;
							const mimeType = row.mime_type || '';
							const dateUploaded = row.datetime_uploaded ? new Date(row.datetime_uploaded).toLocaleString() : 'N/A';
							const category = row.file_category || '';
							const uploadedBy = (row.fname && row.lname) ? row.fname + " " + row.lname : "Unknown";
							
							return `
								<div class="dropdown-container">
									<button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
									<div class="dropdown-menu">
										<button class="dropdown-item view viewBtn" 
											data-id="${fileId || ''}" 
											data-path="${filePath}" 
											data-name="${fileName}"
											data-size="${fileSize}"
											data-mime="${mimeType}"
											data-date="${dateUploaded}"
											data-category="${category}"
											data-uploader="${uploadedBy}">
											<i class="fa fa-eye"></i> View Details
										</button>
										<button class="dropdown-item download downloadBtn" 
											data-id="${fileId || ''}" 
											data-path="${filePath}">
											<i class="fa fa-download"></i> Download
										</button>
									</div>
								</div>
							`;
						}
					}
				],
				columnDefs: [{ targets: 0, visible: false }],
				language: { emptyTable: "No files found" },
				initComplete: function(settings, json) {
					$('#nlpTotalFiles').val(json.data ? json.data.length : 0);
				}
			});
		}
		
		// Initial load
		initNlpFilesTable();
		
		// Search button
		$('#nlpSearchBtn').click(function(){
			const searchWord = $('#nlpSearchWord').val().trim();
			const categoryId = $('#nlpCategoryFilter').val();
			initNlpFilesTable(searchWord, categoryId);
		});

		// View Details button
		$(document).on('click', '.viewBtn', function() {
			const fileName = $(this).data('name');
			const fileSize = $(this).data('size');
			const mimeType = $(this).data('mime');
			const dateUploaded = $(this).data('date');
			const category = $(this).data('category');
			const uploadedBy = $(this).data('uploader');
			
			showFileDetails(fileName, fileSize, mimeType, dateUploaded, category, uploadedBy);
		});

		// Download button
		$(document).on('click', '.downloadBtn', function() {
			const fileId = $(this).data('id');
			const filePath = $(this).data('path');
			
			if (fileId) {
				window.location.href = 'ajax.php?CALL=download&file_id=' + fileId;
			} else if (filePath) {
				window.location.href = 'ajax.php?CALL=download_by_path&file_path=' + encodeURIComponent(filePath);
			} else {
				openModal('ERROR', 'Unable to download file: No file reference found');
			}
		});
	});
	</script>
	
	<!-- Include modals -->
	<?php require_once('modals.php'); ?>
	
</body>
</html>
