<!DOCTYPE html>
<html>
<head>
    <title>ADMIN TEST - DORVE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #000;
            color: #0f0;
            padding: 40px;
            text-align: center;
        }
        h1 {
            font-size: 48px;
            margin: 20px 0;
        }
        .box {
            background: #111;
            border: 2px solid #0f0;
            padding: 30px;
            margin: 20px auto;
            max-width: 600px;
            border-radius: 10px;
        }
        a {
            display: inline-block;
            padding: 15px 30px;
            background: #0f0;
            color: #000;
            text-decoration: none;
            font-weight: bold;
            margin: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>✅ ADMIN DIRECTORY IS ACCESSIBLE!</h1>

    <div class="box">
        <h2>SUCCESS!</h2>
        <p>This file is in <strong>/admin/test-simple.php</strong></p>
        <p>If you can see this, admin directory is working!</p>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

    <div class="box">
        <h2>NOW TRY THESE:</h2>
        <a href="login.php">Go to Login</a>
        <a href="index.php">Go to Dashboard</a>
        <a href="../test-direct.php">Back to Root Test</a>
    </div>

    <div class="box">
        <h2>FILE LOCATION TEST:</h2>
        <p><strong>This file:</strong> <?php echo __FILE__; ?></p>
        <p><strong>Directory:</strong> <?php echo __DIR__; ?></p>
        <p><strong>Script Name:</strong> <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Unknown'; ?></p>
        <p><strong>Request URI:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'Unknown'; ?></p>
    </div>
</body>
</html>
