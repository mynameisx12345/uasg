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
          <div class="alert alert-info">
            <h3>File Categories Removed</h3>
            <p>File categorization is now handled automatically using NLP (Natural Language Processing) analysis. Categories are assigned based on file content, not predefined categories.</p>
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
      let taskcategory;

      $(document).on("click",".deleteBtn",function(){
        var id = $(this).data('id');
        var table = $(this).data('table');
        var title = $(this).data('title');
        var dataValue = $(this).closest("tr").find("td").eq(0).text(); // Get the first visible column value
        
        $("#deleteid").val(id);
        $("#deletevalue").val(dataValue);
        $("#deleteModal").data('table', table);
        $("#deleteModal").data('title', title);
        openDeleteModal();
      });

      $(document).on("click",".updateBtn",function(){
        var id = $(this).data('id');
        var table = $(this).data('table');
        var title = $(this).data('title');
        var currentValue = $(this).closest("tr").find("td").eq(0).text(); // Get the first visible column value
        
        $("#option").val(currentValue);
        $("#updateName").val(currentValue);
        $("#updateModal").data('id', id);
        $("#updateModal").data('table', table);
        $("#updateModal").data('title', title);
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

      // getAllFileCategory function and all file category/keyword handling code removed

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
      getAllTaskCategory();

      function reloadAllAjaxTables(){
        positiontable.ajax.reload(null, false);
        taskcategory.ajax.reload(null, false);
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

      $("#saveTaskCategory").click(function(){
        var taskcategory = $("#taskCategoryName").val();
        if(taskcategory.trim() === ""){
          openModal("ERROR","Please enter task category name");
        }else{
          saveData(3,{NAME:taskcategory});
        }
      });

      // Handle "Save Changes" button click in update modal
      $("#updateModal").on("click", ".btn-primary", function(){
        var newValue = $("#updateName").val().trim();
        
        if(newValue === ""){
          openModal("ERROR", "Please enter a value");
          return;
        }
        
        // Generic update
        var id = $("#updateModal").data('id');
        var table = $("#updateModal").data('table');
        var title = $("#updateModal").data('title');
        
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{
            CALL:64, // Generic update handler
            DATA:{
              id: id,
              table: table,
              value: newValue,
              title: title
            }
          },dataType:'json',
          success:function(result){
            openModal(result.status, result.msg);
            if(result.status == "SUCCESS"){
              closeFormModal();
              $("#updateName").val('');
              $("#option").val('');
            }
          },complete:function(){
            reloadAllAjaxTables();
          }
        });
      });

      // Handle "Confirm Delete" button click in delete modal
      $("#deleteModal").on("click", ".btn-primary", function(){
        var id = $("#deleteid").val();
        var reason = $("#reason").val().trim();
        
        if(reason === ""){
          openModal("ERROR", "Please enter a reason for deletion");
          return;
        }
        
        // Generic delete
        var table = $("#deleteModal").data('table');
        var title = $("#deleteModal").data('title');
        
        $.ajax({
          url:'ajax.php',
          type:'post',
          data:{
            CALL:65, // Generic delete handler
            DATA:{
              id: id,
              table: table,
              reason: reason,
              title: title
            }
          },dataType:'json',
          success:function(result){
            openModal(result.status, result.msg);
            if(result.status == "SUCCESS"){
              closeDeleteModal();
              $("#deleteid").val('');
              $("#deletevalue").val('');
              $("#reason").val('');
            }
          },complete:function(){
            reloadAllAjaxTables();
          }
        });
      });
    });
  </script>
</body>
</html>