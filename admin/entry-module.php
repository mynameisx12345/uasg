<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Entry Modules - UASG</title>
  
  <link rel="stylesheet" href="../resources/style.css">
  <!--link rel='stylesheet' href='../resources/datatable.css'-->
  <link rel='stylesheet' href='https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css'>
  <style>
    .keyword-count {
      background: #4CAF50;
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
    }
    .no-keywords {
      background: #f44336;
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
    }
    .keywords-section {
      border-top: 2px solid #ddd;
      margin-top: 20px;
      padding-top: 20px;
    }
  </style>
  <script src='../js/all.js'></script>
  <script src='../js/jquery.js'></script>
  <script src='../js/datatable.js'></script>
</head>
<body>
  <!-- HEADER -->
   <?php require_once("header.php");?>
   <header class="topbar">
    <h1>Entry Module</h1>
    <div class="user-info">
      <span>Welcome, Admin</span>
    </div>
  </header>
  <!-- MAIN -->
  <main class="main">
    <!-- SIDEBAR -->
    <?php require_once("sidebar.php");?>

    <!-- CONTENT -->
    <section class="content">
      <div class="card">
        <h2>Entry Module</h2>

        <!-- TABS -->
        <div class="tabs">
          <button class="tab-link active" data-tab="positions">Positions</button>
          <button class="tab-link" data-tab="categories">File Categories</button>
          <button class="tab-link" data-tab="offices">Task Categories</button>
        </div>

        <!-- TAB CONTENT: POSITIONS -->
        <div class="tab-content active" id="positions">
          <div class="compact-form">
            <h3>Add New Position</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Position Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="positionName">Position Name</label>
                      <input type="text" id="positionName" name="positionName" placeholder="Enter position..." required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="submit" class="btn-primary" id='saveposition'>Save Position</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <br/>
          <div class="table-container">
            <table class='data-table' id='positiontable'>
              <thead>
                <tr>
                  <th>Position ID</th>
                  <th>Position</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
          </div>
        </div>
        <!-- buttons -- >
          <button class="btn-secondary">Edit</button>
          <button class="btn-primary">Delete</button>
        -->
        <!-- TAB CONTENT: CATEGORIES -->
        <div class="tab-content" id="categories">
          <div class="compact-form">
            <h3>Add New File Category</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Category Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="categoryName">File Category Name</label>
                      <input type="text" id="categoryName" name="categoryName" placeholder="Enter file category..." required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="submit" id='saveFileCategory' class="btn-primary">Save Category</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <br/>
          <div class="table-container">
            <table class='data-table' id='filecategorytable'>
              <thead>
                <tr>
                  <th>File Category ID</th>
                  <th>File Category</th>
                  <th>Keywords</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
          </div>
          
          <!-- Keywords Management Section -->
          <div class="compact-form" style="margin-top: 30px;">
            <h3>Manage Keywords for Auto-Categorization</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Add Keyword</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="keywordCategory">Select Category</label>
                      <select id="keywordCategory" name="keywordCategory" required>
                        <option value="">Select a category...</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="keywordText">Keyword</label>
                      <input type="text" id="keywordText" name="keywordText" placeholder="Enter keyword for auto-detection..." required>
                      <small>Keywords help automatically categorize uploaded files based on filename content</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="submit" id='saveKeyword' class="btn-primary">Add Keyword</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <br/>
          <div class="table-container">
            <h3>Keywords by Category</h3>
            <table class='data-table' id='keywordstable'>
              <thead>
                <tr>
                  <th>Keyword ID</th>
                  <th>Category</th>
                  <th>Keyword</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB CONTENT: OFFICES -->
        <div class="tab-content" id="offices">
          <div class="compact-form">
            <h3>Add New Task Category</h3>
            <div class="form-columns">
              <div class="form-column">
                <div class="form-section">
                  <h4>Task Information</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="taskCategoryName">Task Category</label>
                      <input type="text" id="taskCategoryName" name="taskCategoryName" placeholder="Enter task category..." required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-column">
                <div class="form-section">
                  <h4>Actions</h4>
                  <div class="form-actions">
                    <button type="submit" id='saveTaskCategory' class="btn-primary">Save Category</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <br/>
          <div class="table-container">
            <table class="data-table" id='taskcategory'>
              <thead>
                <tr>
                  <th>Task Category ID</th>
                  <th>Task Category</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
    <div id="updateModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeFormModal()">&times;</span>
        <h2>Data Update Form</h2>
        
        <div class="compact-form">
          <div class="form-section">
            <h4>Update Information</h4>
            <div class="form-row">
              <div class="form-group">
                <label for="recordCategory">Data to Update</label>
                <input type='text' id='option' readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="recordName">Updated Information</label>
                <input type="text" id="updateName" placeholder="Enter updated data" required>
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <button type="button" class="btn-secondary" onclick="closeFormModal()">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <div id="deleteModal" class="modal">
      <div class="modal-content modal-content-small">
        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>
        <h2>Data Delete Confirmation</h2>
        
        <div class="compact-form">
          <div class="form-section">
            <h4>Deletion Details</h4>
            <div class="form-row">
              <div class="form-group">
                <label for="recordCategory">Data to Delete</label>
                <input type='text' id='deletevalue' readonly>
              </div>
            </div>
            <input type='hidden' id='deleteid'>
            <div class="form-row">
              <div class="form-group">
                <label for="reason">Reason for Deletion</label>
                <textarea id='reason' name='reason' rows='4' placeholder="Enter reason for deletion..."></textarea>
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary">Confirm Delete</button>
            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    function openFormModal() {
      document.getElementById("updateModal").style.display = "flex";
    }
    function closeFormModal() {
      document.getElementById("updateModal").style.display = "none";
    }

    function openDeleteModal(){
      document.getElementById("deleteModal").style.display = "flex";
    }

    function closeDeleteModal(){
      document.getElementById("deleteModal").style.display = "none";
    }
  </script>


  <script>
    // Simple tab switcher
    const tabLinks = document.querySelectorAll(".tab-link");
    const tabContents = document.querySelectorAll(".tab-content");

    tabLinks.forEach(link => {
      link.addEventListener("click", () => {
        tabLinks.forEach(l => l.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));

        link.classList.add("active");
        document.getElementById(link.dataset.tab).classList.add("active");
      });
    });
  </script>
  <?php require_once("modal.php");?>
  <script>
    $(document).ready(function(){
      let positiontable;
      let filecategorytable;
      let taskcategory;
      let keywordstable;

      $(document).on("click",".deleteBtn",function(){
        $("#deleteid").val($(this).data('id'));
        $("#deletevalue").val($(this).closest("tr").find("td").eq(0).text());
        openDeleteModal();
      });

      $(document).on("click",".updateBtn",function(){
        $("#option").val($(this).closest("tr").find("td").eq(0).text());
        openFormModal();
      });

      function getAllPositions(){
        positiontable = $("#positiontable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL:4
            },dataType:'json',
          },responsive:true,
          scroll:'50vh',
          scrollCollapse: true, 
          paging: true,
          columns: [
            { data: "position_id" },
            { data: "position" },
            { data: 'position_id',
              render: function(id) {
                  return '<button class="btn-primary updateBtn" data-id="'+id+'" data-table="position_tbl" data-title="Position" title="Update"><i class="fas fa-edit"></i> Update</button> <button class="btn-secondary deleteBtn" data-id="'+id+'" data-table="position_tbl" data-title="Position" title="Delete Position"><i class="far fa-trash-alt"></i> Delete</button>';
              }
            },
            /*{ data: 'id',
              render: function(id){
                  return '<button class="btn-secondary deletePosBtn" data-id="'+id+'" title="Delete Position"><i class="far fa-trash-alt"></i></button>';
              }
            },*/
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ],
          initComplete: function(settings, json) {
            // remove default DT classes if needed
            //$('#positiontable').removeClass('dataTable');
          }
        });
      }

      function getAllFileCategory(){
        filecategorytable = $("#filecategorytable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL:5
            },dataType:'json',
          },
          scroll:'50vh',
          scrollCollapse:true,
          paging:true,
          columns: [
            { data: "file_category_id" },
            { data: "file_category" },
            { data: "keywords", 
              render: function(data) {
                  return data ? '<span class="keyword-count">' + data.split(',').length + ' keywords</span>' : '<span class="no-keywords">No keywords</span>';
              }
            },
            { data: 'file_category_id',
              render: function(id) {
                  return '<button class="btn-primary updateBtn" data-id="'+id+'" data-table="file_category_tbl" data-title="File Category" title="Update"><i class="fas fa-edit"></i> Update</button> <button class="btn-secondary deleteBtn" data-id="'+id+'" data-table="file_category_tbl" data-title="File Category" title="Delete File Category"><i class="far fa-trash-alt"></i> Delete</button>';
              }
            }
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ],
          initComplete: function(settings, json) {
            // Populate category dropdown for keywords
            populateCategoryDropdown();
          }
        });
      }

      function getAllTaskCategory(){
        taskcategory = $("#taskcategory").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL:6
            },dataType:'json',
          },
          scroll:'50vh',
          scrollCollapse:true,
          paging:true,
          columns:[
            { data: "task_category_id" },
            { data: "task_category" },
            { data: 'task_category_id',
              render: function(id) {
                  return '<button class="btn-primary updateBtn" data-id="'+id+'" data-table="task_category_tbl" data-title="Task Category" title="Update"><i class="fas fa-edit"></i> Update</button> <button class="btn-secondary deleteBtn" data-id="'+id+'" data-table="task_category_tbl" data-title="Task Category" title="Delete Task Category"><i class="far fa-trash-alt"></i> Delete</button>';
              }
            },
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ],
          initComplete: function(settings, json) {
            // remove default DT classes if needed
            //$('#positiontable').removeClass('dataTable');
          }
        });
      }

      getAllPositions();
      getAllFileCategory();
      getAllTaskCategory();
      getAllKeywords();

      function populateCategoryDropdown(){
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{CALL:5},
          dataType:'json',
          success:function(result){
            var options = '<option value="">Select a category...</option>';
            if(result.data){
              result.data.forEach(function(category){
                options += '<option value="'+category.file_category_id+'">'+category.file_category+'</option>';
              });
            }
            $("#keywordCategory").html(options);
          }
        });
      }

      function getAllKeywords(){
        keywordstable = $("#keywordstable").DataTable({
          ajax:{
            url:'ajax.php',
            type:'post',
            data:{
              CALL:33
            },dataType:'json',
          },
          scroll:'50vh',
          scrollCollapse:true,
          paging:true,
          columns:[
            { data: "file_category_key_id" },
            { data: "file_category" },
            { data: "keyword" },
            { data: 'file_category_key_id',
              render: function(id) {
                  return '<button class="btn-primary updateKeywordBtn" data-id="'+id+'" title="Update Keyword"><i class="fas fa-edit"></i> Update</button> <button class="btn-secondary deleteKeywordBtn" data-id="'+id+'" title="Delete Keyword"><i class="far fa-trash-alt"></i> Delete</button>';
              }
            },
          ],
          columnDefs: [
            { targets: 0, visible: false, searchable: false } 
          ]
        });
      }

      function reloadAllAjaxTables(){
        positiontable.ajax.reload(null, false);
        filecategorytable.ajax.reload(null, false);
        taskcategory.ajax.reload(null, false);
        keywordstable.ajax.reload(null, false);
        populateCategoryDropdown(); // Refresh dropdown
      }

      function saveData(call,dataArr = []){
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{
            CALL:call,
            DATA:dataArr
          },dataType:'json',
          success:function(result){
            openModal(result.status, result.msg);
            if(result.status == "SUCCESS"){
              $(document).find("input,select,textarea").each(function(){$(this).val('')});
            }
          },complete:function(){
            reloadAllAjaxTables();
          }
        });
      }

      $("#saveposition").click(function(){
        var pos = $("#positionName").val();
        if(pos.trim() === ""){
          openModal("ERROR","Please enter position name");
        }else{
          saveData(1,{NAME:pos});
        }
      });

      $("#saveFileCategory").click(function(){
        var category = $("#categoryName").val();
        if(category.trim() === ""){
          openModal("ERROR","Please enter file category name");
        }else{
          saveData(2,{NAME:category});
        }
      });

      $("#saveTaskCategory").click(function(){
        var taskcategory = $("#taskCategoryName").val();
        if(taskcategory.trim() === ""){
          openModal("ERROR","Please enter task category name");
        }else{
          saveData(3,{NAME:taskcategory});
        }
      });

      // Keywords management
      $("#saveKeyword").click(function(){
        var categoryId = $("#keywordCategory").val();
        var keyword = $("#keywordText").val();
        if(categoryId === ""){
          openModal("ERROR","Please select a category");
        }else if(keyword.trim() === ""){
          openModal("ERROR","Please enter a keyword");
        }else{
          saveKeyword(categoryId, keyword.trim());
        }
      });

      $(document).on("click",".updateKeywordBtn",function(){
        var keywordId = $(this).data('id');
        var currentKeyword = $(this).closest("tr").find("td").eq(2).text();
        var newKeyword = prompt("Enter new keyword:", currentKeyword);
        if(newKeyword && newKeyword.trim() !== "" && newKeyword !== currentKeyword){
          updateKeyword(keywordId, newKeyword.trim());
        }
      });

      $(document).on("click",".deleteKeywordBtn",function(){
        var keywordId = $(this).data('id');
        var keyword = $(this).closest("tr").find("td").eq(2).text();
        var reason = prompt("Enter reason for deleting keyword '" + keyword + "':");
        if(reason && reason.trim() !== ""){
          deleteKeyword(keywordId, reason.trim());
        }
      });

      function saveKeyword(categoryId, keyword){
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{
            CALL:32,
            DATA:{category_id:categoryId, keyword:keyword}
          },dataType:'json',
          success:function(result){
            openModal(result.status, result.msg);
            if(result.status == "SUCCESS"){
              $("#keywordText").val('');
              $("#keywordCategory").val('');
            }
          },complete:function(){
            reloadAllAjaxTables();
          }
        });
      }

      function updateKeyword(keywordId, keyword){
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{
            CALL:34,
            DATA:{keyword_id:keywordId, keyword:keyword}
          },dataType:'json',
          success:function(result){
            openModal(result.status, result.msg);
            
            // Send mobile notification for successful actions
            if (result.status === 'SUCCESS') {
              if (typeof UASGPWAHelper !== 'undefined') {
                if (this.url.includes('CALL:32')) {
                  // Adding keyword
                  UASGPWAHelper.notifySuccess(
                    '✅ Keyword Added',
                    'New keyword has been successfully added to the category.',
                    window.location.href
                  );
                } else if (this.url.includes('CALL:33')) {
                  // Updating keyword
                  UASGPWAHelper.notifySuccess(
                    '📝 Keyword Updated',
                    'The keyword has been successfully updated.',
                    window.location.href
                  );
                }
              }
            } else if (result.status === 'ERROR') {
              // Show error notification
              if (typeof UASGPWAHelper !== 'undefined') {
                UASGPWAHelper.notifyError(
                  '❌ Operation Failed',
                  result.msg || 'An error occurred while processing your request.'
                );
              }
            }
          },complete:function(){
            reloadAllAjaxTables();
          }
        });
      }

      function deleteKeyword(keywordId, reason){
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{
            CALL:35,
            DATA:{keyword_id:keywordId, reason:reason}
          },dataType:'json',
          success:function(result){
            openModal(result.status, result.msg);
            
            // Send mobile notification for successful deletion
            if (result.status === 'SUCCESS') {
              if (typeof UASGPWAHelper !== 'undefined') {
                UASGPWAHelper.notifySuccess(
                  '🗑️ Keyword Deleted',
                  'The keyword has been successfully removed from the category.',
                  window.location.href
                );
              }
            }
          },complete:function(){
            reloadAllAjaxTables();
          }
        });
      }
    });
  </script>
  
  <!-- PWA Scripts -->
  <script src="../js/pwa-helper.js"></script>
</body>
</html>