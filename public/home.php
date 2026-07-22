<?php
/**
 * Example home page
 * 
 * This is served when the visitor's country is allowed.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Country Block</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            line-height: 1.6;
            color: #333;
        }
        h1 { color: #667eea; }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Welcome to Country Block</h1>
    <p>Your access has been authorized based on your country location.</p>
    
    <h2>About This System</h2>
    <p>This website uses geo-blocking to restrict access to specific countries. Your IP address has been matched against your country's location, and you are authorized to view this content.</p>
    
    <h2>Getting Started</h2>
    <p>To customize this page or add your application logic, edit <code>public/home.php</code></p>
</body>
</html>