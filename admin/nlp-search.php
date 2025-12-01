<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
		header("Location: ../index.php");
		exit();
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>NLP File Search</title>
	<link rel="stylesheet" href="../resources/style.css?v=<?= time() ?>">
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
	<script src='../js/all.js?v=<?= time() ?>'></script>
	<script src='../js/jquery.js?v=<?= time() ?>'></script>
	<script src='../js/datatable.js?v=<?= time() ?>'></script>
</head>
<body>
	<?php require_once("header.php");?>
	<header class="topbar">
		<h1>NLP File Search</h1>
		<div class="user-info">
			<span>Welcome, Admin</span>
		</div>
	</header>
	<main class="main">
		<?php require_once("sidebar.php");?>
		<section class="content">
			<div class="card">
				<h2>NLP-Based File Search</h2>
				<div class="compact-form">
					<h3>Search Files by Content</h3>
					<div class="form-columns">
						<div class="form-column">
							<div class="form-section">
								<h4>Search Options</h4>
								<div class="form-row">
									<div class="form-group">
										<label for="nlpSearchWord">Search Word</label>
										<input type="text" id="nlpSearchWord" placeholder="Enter keyword or phrase">
									</div>
									<div class="form-group">
										<label for="nlpCategoryFilter">Category</label>
										<select id="nlpCategoryFilter">
											<option value="">All Categories</option>
										</select>
									</div>
									<div class="form-group form-group-small">
										<label>&nbsp;</label>
										<button type="button" class="btn-primary" id="nlpSearchBtn">Search</button>
									</div>
								</div>
							</div>
						</div>
						<div class="form-column">
							<div class="form-section">
								<h4>Quick Stats</h4>
								<div class="form-row">
									<div class="form-group form-group-small">
										<label>Total Files</label>
										<input type="text" id="nlpTotalFiles" readonly value="0">
									</div>
									<div class="form-group form-group-small">
										<label>Categories</label>
										<input type="text" id="nlpTotalCategories" readonly value="0">
									</div>
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
		</section>
		
		<!-- Loading Modal -->
		<div id='loadingModal' class='modal'>
			<div class='modal-content modal-content-small'>
				<h2>Processing...</h2>
				<div style='text-align: center; margin: 20px 0;'>
					<i class='fas fa-spinner fa-spin' style='font-size: 48px; color: #2196F3;'></i>
				</div>
				<p style='text-align: center;'>Please wait while we process your request.</p>
			</div>
		</div>

		<!-- File Details Modal -->
		<div id="fileDetailsModal" class="modal" style="display: none;">
			<div class="modal-content" style="max-width: 600px;">
				<div class="modal-header">
					<h2>File Details</h2>
					<span class="close-modal" onclick="closeFileDetailsModal()">&times;</span>
				</div>
				<div id="fileDetailsContent" style="padding: 20px 0;">
					<!-- Content will be populated by JavaScript -->
				</div>
				<div style="text-align: right; margin-top: 20px;">
					<button class="btn-secondary" onclick="closeFileDetailsModal()">Close</button>
				</div>
			</div>
		</div>
	</main>

	<!-- Notification Modal -->
	<div id="notificationModal" class="notification-modal">
		<div class="notification-content">
			<i id="notificationIcon" class="fas fa-info-circle"></i>
			<div class="notification-text">
				<h3 id="notificationTitle">Notification</h3>
				<p id="notificationMessage"></p>
			</div>
			<button class="notification-close" onclick="closeNotification()">&times;</button>
		</div>
	</div>

	<style>
		/* Notification Modal Styles */
		.notification-modal {
			display: none;
			position: fixed;
			top: 20px;
			right: 20px;
			z-index: 10000;
			animation: slideIn 0.3s ease-out;
		}

		.notification-modal.notification-show .notification-content {
			animation: slideIn 0.3s ease-out;
		}

		.notification-content {
			display: flex;
			align-items: center;
			gap: 15px;
			background: white;
			padding: 20px 25px;
			border-radius: 12px;
			box-shadow: 0 10px 40px rgba(0,0,0,0.15);
			min-width: 350px;
			max-width: 500px;
			border-left: 5px solid #3b82f6;
		}

		.notification-content i {
			font-size: 28px;
			flex-shrink: 0;
		}

		.notification-text {
			flex: 1;
		}

		.notification-text h3 {
			margin: 0 0 5px 0;
			font-size: 16px;
			font-weight: 600;
			color: #1e293b;
		}

		.notification-text p {
			margin: 0;
			font-size: 14px;
			color: #64748b;
			line-height: 1.5;
		}

		.notification-close {
			background: none;
			border: none;
			font-size: 24px;
			color: #94a3b8;
			cursor: pointer;
			padding: 0;
			width: 30px;
			height: 30px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
			transition: all 0.2s;
			flex-shrink: 0;
		}

		.notification-close:hover {
			background: #f1f5f9;
			color: #475569;
		}

		@keyframes slideIn {
			from {
				transform: translateX(400px);
				opacity: 0;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
	</style>

	<script>
	// File Details Modal functions
	function closeFileDetailsModal() {
		document.getElementById('fileDetailsModal').style.display = 'none';
	}

	function showFileDetails(fileName, fileSize, mimeType, dateUploaded, category, uploadedBy) {
		const modal = document.getElementById('fileDetailsModal');
		const content = document.getElementById('fileDetailsContent');
		
		// Format file size
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

	// Notification Modal System
	function showNotification(message, type = 'info', title = '') {
		const modal = document.getElementById('notificationModal');
		const modalTitle = document.getElementById('notificationTitle');
		const modalMessage = document.getElementById('notificationMessage');
		const modalIcon = document.getElementById('notificationIcon');
		
		// Set icon and title based on type
		const config = {
			success: { icon: 'fa-check-circle', defaultTitle: 'Success', color: '#10b981' },
			error: { icon: 'fa-exclamation-circle', defaultTitle: 'Error', color: '#ef4444' },
			warning: { icon: 'fa-exclamation-triangle', defaultTitle: 'Warning', color: '#f59e0b' },
			info: { icon: 'fa-info-circle', defaultTitle: 'Information', color: '#3b82f6' }
		};
		
		const typeConfig = config[type] || config.info;
		modalIcon.className = `fas ${typeConfig.icon}`;
		modalIcon.style.color = typeConfig.color;
		modalTitle.textContent = title || typeConfig.defaultTitle;
		modalMessage.innerHTML = message; // Use innerHTML to support HTML content
		
		// Show modal
		modal.style.display = 'flex';
		modal.classList.add('notification-show');
		
		// Auto-close after 5 seconds for info, 3 seconds for others
		const duration = type === 'info' ? 5000 : 3000;
		setTimeout(() => {
			closeNotification();
		}, duration);
	}

	function closeNotification() {
		const modal = document.getElementById('notificationModal');
		modal.classList.remove('notification-show');
		setTimeout(() => {
			modal.style.display = 'none';
		}, 300);
	}
	</script>

	<script>
	$(document).ready(function(){
		let nlpFilesTable;
		const currentUserId = <?= $_SESSION['user_id'] ?? 0 ?>;
		// Load categories for filter
		function loadNlpCategories() {
			$.ajax({
				url: 'ajax.php',
				type: 'post',
				data: { CALL: 5 },
				dataType: 'json',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
				},
				success: function(result) {
					if(result.data) {
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
					beforeSend: function(xhr) {
						xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
						$('#loadingModal').show();
					},
					complete: function() {
						$('#loadingModal').hide();
					},
					error: function(xhr, error, thrown) {
						console.error('AJAX Error:', error);
						console.error('Response:', xhr.responseText);
						showNotification('Error loading files: ' + error + '<br>Check console for details', 'error');
					}
				},
				responsive: true,
				scrollY: '50vh',
				scrollCollapse: true,
				paging: true,
				columns: [
					{ data: "file_upload_id" },
					{ data: "file_name" },
					{ data: "file_category" },
					{ data: "mime_type" },
					{ data: null, render: function(data) { return data.fname && data.lname ? data.fname + " " + data.lname : "Unknown"; } },
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
											<i class="fas fa-eye"></i> View Details
										</button>
										<button class="dropdown-item download downloadBtn" 
											data-id="${fileId || ''}" 
											data-path="${filePath}">
											<i class="fas fa-download"></i> Download
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
			
			// Show file details in centered modal
			showFileDetails(fileName, fileSize, mimeType, dateUploaded, category, uploadedBy);
		});

		// Download button
		$(document).on('click', '.downloadBtn', function() {
			const fileId = $(this).data('id');
			const filePath = $(this).data('path');
			
			// If file has a database ID, use normal download
			if (fileId) {
				window.location.href = 'ajax.php?CALL=download&file_id=' + fileId;
			} 
			// Otherwise, use direct file path download
			else if (filePath) {
				window.location.href = 'ajax.php?CALL=download_by_path&file_path=' + encodeURIComponent(filePath);
			} else {
				showNotification('Unable to download file: No file reference found', 'error');
			}
		});
	});

	// Dropdown toggle function (global scope)
	function toggleDropdown(event) {
		event.stopPropagation();
		const dropdownMenu = event.target.nextElementSibling;
		const isOpen = dropdownMenu.classList.contains('show');
		
		// Close all dropdowns
		document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
			menu.classList.remove('show');
		});
		
		// Toggle current dropdown
		if (!isOpen) {
			dropdownMenu.classList.add('show');
		}
	}

	// Close dropdown when clicking outside
	document.addEventListener('click', function(event) {
		if (!event.target.matches('.dropdown-btn')) {
			document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
				menu.classList.remove('show');
			});
		}
	});
	</script>
</body>
</html>
