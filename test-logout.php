<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Test - UASG</title>
    <script src="js/jquery.js"></script>
</head>
<body>
    <h1>Logout Functionality Test</h1>
    
    <h2>Test AJAX Logout (POST)</h2>
    <button onclick="testAjaxLogout()">Test AJAX Logout</button>
    
    <h2>Test Direct Logout (GET)</h2>
    <a href="auth.php?action=logout">Test Direct Logout Link</a>
    
    <div id="result"></div>
    
    <script>
        function testAjaxLogout() {
            $.ajax({
                url: 'auth.php',
                type: 'POST',
                data: { action: 'logout' },
                dataType: 'json',
                success: function(response) {
                    $('#result').html('<div style="color: green;">AJAX Logout Success: ' + response.message + '</div>');
                    console.log('Response:', response);
                    if (response.success) {
                        setTimeout(function() {
                            window.location.href = response.redirect_url;
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    $('#result').html('<div style="color: red;">AJAX Logout Error: ' + error + '</div>');
                    console.error('Error:', xhr.responseText);
                }
            });
        }
    </script>
</body>
</html>