<?php
require_once __DIR__ . '/../config.php';

echo "<h1>CREATING NEW ADMIN USERS</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #f5f5f5; }
      .success { color: green; padding: 10px; background: #d4edda; margin: 10px 0; border-radius: 5px; }
      .error { color: red; padding: 10px; background: #f8d7da; margin: 10px 0; border-radius: 5px; }
      .info { color: blue; padding: 10px; background: #d1ecf1; margin: 10px 0; border-radius: 5px; }
      </style>";

try {
    // Admin 1: admin1@dorve / Qwerty889*
    echo "<h2>Creating Admin 1...</h2>";

    // Check if exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin1@dorve']);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "<div class='info'>❌ Email admin1@dorve already exists! Updating password...</div>";

        $hashedPassword = password_hash('Qwerty889*', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', email_verified = 1 WHERE email = ?");
        $stmt->execute([$hashedPassword, 'admin1@dorve']);

        echo "<div class='success'>✅ Admin 1 UPDATED!<br>";
        echo "📧 Email: admin1@dorve<br>";
        echo "🔑 Password: Qwerty889*<br>";
        echo "👤 Name: " . htmlspecialchars($existing['name']) . "</div>";
    } else {
        $hashedPassword = password_hash('Qwerty889*', PASSWORD_DEFAULT);
        $referralCode = 'ADM1-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, referral_code, created_at)
                              VALUES (?, ?, ?, 'admin', 1, ?, NOW())");
        $stmt->execute(['Admin Dorve 1', 'admin1@dorve', $hashedPassword, $referralCode]);

        echo "<div class='success'>✅ Admin 1 CREATED!<br>";
        echo "📧 Email: admin1@dorve<br>";
        echo "🔑 Password: Qwerty889*<br>";
        echo "👤 Name: Admin Dorve 1</div>";
    }

    // Admin 2: admin2@dorve / MajuTerus88*
    echo "<h2>Creating Admin 2...</h2>";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin2@dorve']);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "<div class='info'>❌ Email admin2@dorve already exists! Updating password...</div>";

        $hashedPassword = password_hash('MajuTerus88*', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', email_verified = 1 WHERE email = ?");
        $stmt->execute([$hashedPassword, 'admin2@dorve']);

        echo "<div class='success'>✅ Admin 2 UPDATED!<br>";
        echo "📧 Email: admin2@dorve<br>";
        echo "🔑 Password: MajuTerus88*<br>";
        echo "👤 Name: " . htmlspecialchars($existing['name']) . "</div>";
    } else {
        $hashedPassword = password_hash('MajuTerus88*', PASSWORD_DEFAULT);
        $referralCode = 'ADM2-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, referral_code, created_at)
                              VALUES (?, ?, ?, 'admin', 1, ?, NOW())");
        $stmt->execute(['Admin Dorve 2', 'admin2@dorve', $hashedPassword, $referralCode]);

        echo "<div class='success'>✅ Admin 2 CREATED!<br>";
        echo "📧 Email: admin2@dorve<br>";
        echo "🔑 Password: MajuTerus88*<br>";
        echo "👤 Name: Admin Dorve 2</div>";
    }

    // Verify passwords
    echo "<h2>Password Verification Test</h2>";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin1@dorve']);
    $user1 = $stmt->fetch();

    if ($user1 && password_verify('Qwerty889*', $user1['password'])) {
        echo "<div class='success'>✅ Admin 1 password verification: PASSED</div>";
    } else {
        echo "<div class='error'>❌ Admin 1 password verification: FAILED</div>";
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin2@dorve']);
    $user2 = $stmt->fetch();

    if ($user2 && password_verify('MajuTerus88*', $user2['password'])) {
        echo "<div class='success'>✅ Admin 2 password verification: PASSED</div>";
    } else {
        echo "<div class='error'>❌ Admin 2 password verification: FAILED</div>";
    }

    // Show all admins
    echo "<h2>All Admin Users in Database</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; background: white;'>";
    echo "<tr style='background: #333; color: white;'>
          <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Created</th></tr>";

    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC");
    while ($admin = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $admin['id'] . "</td>";
        echo "<td>" . htmlspecialchars($admin['name']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($admin['email']) . "</strong></td>";
        echo "<td>" . $admin['role'] . "</td>";
        echo "<td>" . ($admin['email_verified'] ? '✅' : '❌') . "</td>";
        echo "<td>" . $admin['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div style='margin-top: 30px; padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px;'>";
    echo "<h2 style='color: #856404;'>🎉 ADMIN ACCOUNTS READY!</h2>";
    echo "<p style='font-size: 18px; color: #333;'><strong>Login sekarang dengan:</strong></p>";
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Option 1:</strong><br>";
    echo "📧 Email: <code style='background: white; padding: 2px 5px;'>admin1@dorve</code><br>";
    echo "🔑 Password: <code style='background: white; padding: 2px 5px;'>Qwerty889*</code>";
    echo "</div>";
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Option 2:</strong><br>";
    echo "📧 Email: <code style='background: white; padding: 2px 5px;'>admin2@dorve</code><br>";
    echo "🔑 Password: <code style='background: white; padding: 2px 5px;'>MajuTerus88*</code>";
    echo "</div>";
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='/admin/simple-login.php' style='display: inline-block; padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>🔐 LOGIN SEKARANG (Simple Login)</a>";
    echo "<a href='/admin/login.php' style='display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>🚀 LOGIN (Normal)</a>";
    echo "</div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
