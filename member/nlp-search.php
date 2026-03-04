<?php
session_start();
require_once("../resources/session.php");

// Require member role
$session = SessionManager::getInstance();
$session->requireRole(['Member', 'member', 'Student', 'student']);

$currentUser = $session->getUserData();
$userId = $currentUser['user_id'] ?? null;

if (!$userId) {
    header("Location: ../index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>NLP File Search - Member</title>
	<link rel="stylesheet" href="../resources/style.css">
  <link rel="stylesheet" href="../resources/theme-overrides.css">
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
		.dropdown-item.nlp-analysis { color: #6f42c1; }
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
										<input type="text" id="nlpSearchWord" name="search_word" placeholder="Try: 'show me all resolutions' or 'find memorandums'">
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
	
	<!-- NLP Analysis Modal -->
	<div id="nlpAnalysisModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
		<div style="background:white; max-width:900px; width:90%; max-height:90vh; overflow:auto; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
			<div style="padding:1.5rem; border-bottom:1px solid #dee2e6; display:flex; justify-content:space-between; align-items:center;">
				<h3 style="margin:0;">NLP Analysis Report</h3>
				<button onclick="closeNlpAnalysisModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#999;">&times;</button>
			</div>
			<div id="nlpAnalysisContent" style="padding:1.5rem;"></div>
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

	function openModal(status, message) {
		const title = status === 'SUCCESS' ? 'Success' : 
					  status === 'ERROR' ? 'Error' : 
					  status === 'WARNING' ? 'Warning' : 'Information';
		document.getElementById('notificationTitle').textContent = title;
		document.getElementById('notificationMessage').innerHTML = message;
		
		const modal = document.getElementById('notificationModal');
		modal.className = 'modal ' + status.toLowerCase();
		modal.style.display = 'flex';
	}

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

	// NLP Analysis Modal Functions
	function closeNlpAnalysisModal() {
		document.getElementById('nlpAnalysisModal').style.display = 'none';
	}

	window.displayNlpAnalysis = function(response, fileName) {
		const analysis = response.analysis || {};
		const file = response.file || {};
		const keywords = analysis.keywords || [];
		const entities = analysis.entities || [];
		const sentiment = analysis.sentiment || {};
		
		let html = `<div style="font-family:Arial,sans-serif;"><div style="background:#f8f9fa;padding:1rem;margin-bottom:1rem;border-radius:4px;"><h4 style="margin:0 0 0.5rem 0;color:#333;">${fileName}</h4><div style="font-size:0.9rem;color:#666;"><strong>Category:</strong> ${file.category_name || 'N/A'} | <strong>Uploaded:</strong> ${file.created_at || 'N/A'}</div></div>`;
		
		if (file.suggested_category_name) {
			const confidence = parseFloat(file.confidence_score || 0);
			const barColor = confidence >= 80 ? '#28a745' : confidence >= 60 ? '#ffc107' : '#dc3545';
			html += `<div style="margin-bottom:1.5rem;"><h5 style="color:#495057;margin-bottom:0.75rem;">Categorization Analysis</h5><div style="background:#f8f9fa;padding:1rem;border-radius:4px;"><div style="margin-bottom:0.5rem;"><strong>Suggested Category:</strong> ${file.suggested_category_name}</div><div style="margin-bottom:0.5rem;"><strong>Confidence:</strong> ${confidence.toFixed(2)}%</div><div style="background:#e9ecef;border-radius:4px;height:20px;overflow:hidden;"><div style="background:${barColor};height:100%;width:${confidence}%;transition:width 0.3s;"></div></div></div></div>`;
		}
		
		if (keywords.length > 0) {
			html += `<div style="margin-bottom:1.5rem;"><h5 style="color:#495057;margin-bottom:0.75rem;">Keywords</h5><div style="display:flex;flex-wrap:wrap;gap:0.5rem;">`;
			keywords.forEach(kw => { html += `<span style="background:#007bff;color:white;padding:0.4rem 0.8rem;border-radius:20px;font-size:0.85rem;">${kw}</span>`; });
			html += `</div></div>`;
		}
		
		if (entities.length > 0) {
			html += `<div style="margin-bottom:1.5rem;"><h5 style="color:#495057;margin-bottom:0.75rem;">Entities</h5><div style="display:flex;flex-wrap:wrap;gap:0.5rem;">`;
			entities.forEach(ent => { html += `<span style="background:#ffc107;color:#333;padding:0.4rem 0.8rem;border-radius:20px;font-size:0.85rem;">${ent.text} <small style="opacity:0.7;">(${ent.type})</small></span>`; });
			html += `</div></div>`;
		}
		
		if (Object.keys(sentiment).length > 0) {
			html += `<div style="margin-bottom:1.5rem;"><h5 style="color:#495057;margin-bottom:0.75rem;">Sentiment</h5><pre style="background:#f8f9fa;padding:1rem;border-radius:4px;overflow:auto;font-size:0.85rem;">${JSON.stringify(sentiment, null, 2)}</pre></div>`;
		}
		
		const wordCount = analysis.word_count || 0;
		const provider = analysis.nlp_provider || 'Unknown';
		const processingTime = analysis.processing_time_ms || 0;
		html += `<div style="margin-bottom:1.5rem;"><h5 style="color:#495057;margin-bottom:0.75rem;">Statistics</h5><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;"><div style="background:#f8f9fa;padding:1rem;border-radius:4px;text-align:center;"><div style="font-size:1.5rem;font-weight:bold;color:#007bff;">${wordCount}</div><div style="font-size:0.85rem;color:#666;">Words</div></div><div style="background:#f8f9fa;padding:1rem;border-radius:4px;text-align:center;"><div style="font-size:1.2rem;font-weight:bold;color:#28a745;">${provider}</div><div style="font-size:0.85rem;color:#666;">Provider</div></div><div style="background:#f8f9fa;padding:1rem;border-radius:4px;text-align:center;"><div style="font-size:1.5rem;font-weight:bold;color:#6f42c1;">${processingTime}ms</div><div style="font-size:0.85rem;color:#666;">Processing</div></div></div></div>`;
		
		if (analysis.extracted_text) {
			const preview = analysis.extracted_text.substring(0, 500) + (analysis.extracted_text.length > 500 ? '...' : '');
			html += `<div style="margin-bottom:1rem;"><h5 style="color:#495057;margin-bottom:0.75rem;">Extracted Text Preview</h5><div style="background:#f8f9fa;padding:1rem;border-radius:4px;max-height:200px;overflow:auto;font-family:monospace;font-size:0.85rem;white-space:pre-wrap;">${preview}</div></div>`;
		}
		
		html += `</div>`;
		document.getElementById('nlpAnalysisContent').innerHTML = html;
	};

	$(document).ready(function(){
		let nlpFilesTable;
		const currentUserId = <?= $userId ?>;
		
		// Load categories
		function loadNlpCategories() {
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: { CALL: 'get_categories' },
				dataType: 'json',
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
					dataSrc: function(json) {
						// Display conversational response message if available
						if (json.message) {
							openModal('SUCCESS', json.message);
							
							// Show parsed query info if available
							if (json.parsed) {
								console.log('Conversational Query Parsed:', json.parsed);
								if (json.parsed.category) {
									$('#nlpSearchWord').attr('placeholder', 'Understood: Looking for ' + json.parsed.category + ' files');
								}
							}
						}
						return json.data;
					},
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
										<button class="dropdown-item nlp-analysis viewNlpBtn" 
											data-id="${fileId || ''}" 
											data-name="${fileName}">
											<i class="fa fa-brain"></i> NLP Analysis
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

		// View NLP Analysis button
		$(document).on('click', '.viewNlpBtn', function() {
			const fileId = $(this).data('id');
			const fileName = $(this).data('name');
			
			if(fileId) {
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {
						CALL: 'get_nlp_analysis',
						file_id: fileId
					},
					dataType: 'json',
					beforeSend: function() {
						$('#loadingModal').show();
					},
					success: function(response) {
						$('#loadingModal').hide();
						
						if(response.status === 'SUCCESS' || response.status === 'WARNING') {
							displayNlpAnalysis(response, fileName);
							document.getElementById('nlpAnalysisModal').style.display = 'flex';
						} else {
							openModal('ERROR', response.msg || 'Failed to load NLP analysis');
						}
					},
					error: function(xhr, status, error) {
						$('#loadingModal').hide();
						console.error('NLP Analysis error:', xhr.responseText);
						openModal('ERROR', 'Failed to load NLP analysis. Please try again.');
					}
				});
			}
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
</body>
</html>
