<!DOCTYPE html>
<html>
<head>
    <title>Test Plate API</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Plate Detection API Test</h1>
    
    <div>
        <input type="file" id="imageInput" accept="image/*">
        <button id="testApiBtn">Test API</button>
    </div>
    
    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>
    
    <script>
        $(document).ready(function() {
            $('#testApiBtn').click(function() {
                const fileInput = document.getElementById('imageInput');
                if (!fileInput.files.length) {
                    alert('Please select an image first');
                    return;
                }
                
                const file = fileInput.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imageData = e.target.result;
                    
                    $('#result').html('<p>Sending request...</p>');
                    
                    $.ajax({
                        url: '{{ url("api/detect-plate") }}',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            image: imageData
                        },
                        success: function(response) {
                            $('#result').html('<h3>Success:</h3><pre>' + JSON.stringify(response, null, 2) + '</pre>');
                        },
                        error: function(xhr, status, error) {
                            $('#result').html('<h3>Error:</h3><p>' + status + ': ' + error + '</p><pre>' + xhr.responseText + '</pre>');
                        }
                    });
                };
                
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>