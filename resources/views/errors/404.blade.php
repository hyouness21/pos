<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f3f4f6; color: #111827; }
        .spinner { width: 40px; height: 40px; border: 4px solid #e0e7ff; border-top-color: #4f46e5; border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="spinner"></div>
    <script>setTimeout(function(){ location.href='/'; }, 1500);</script>
</body>
</html>
