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
		<div id='loadingModal' class='modal'>
			<div class='modal-content modal-content-small'>
				<h2>Processing...</h2>
				<div style='text-align: center; margin: 20px 0;'>
					<i class='fas fa-spinner fa-spin' style='font-size: 48px; color: #2196F3;'></i>
				</div>
				<p style='text-align: center;'>Please wait while we process your request.</p>
			</div>
		</div>
	</main>
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
					{ data: "file_upload_id", render: function(id, type, row) {
						return `<button class="btn-secondary viewBtn" data-id="${id}"><i class="fas fa-eye"></i> View</button>`;
					} }
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
		// View file button
		$(document).on("click", ".viewBtn", function() {
			const fileId = $(this).data('id');
			// Optionally, show file details or download
			window.location.href = 'ajax.php?CALL=download&file_id=' + fileId;
		});
	});
	</script>
</body>
</html>
