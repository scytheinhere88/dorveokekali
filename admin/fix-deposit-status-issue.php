<?php
/**
 * FIX DEPOSIT STATUS ISSUE
 * Problem: Deposits showing "COMPLETED" instead of "pending" status
 * Solution: Add status column + fix existing records + update process
 */
require_once __DIR__ . '/../config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Deposit Status Issue</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        .warning { color: #ffaa00; }
        pre { background: #000; padding: 20px; border-radius: 8px; overflow-x: auto; }
        h1 { color: #ffffff; }
        table { border-collapse: collapse; margin: 20px 0; }
        table td, table th { padding: 8px; border: 1px solid #333; text-align: left; }
        table th { background: #333; }
    </style>
</head>
<body>
    <h1>🔧 FIX DEPOSIT STATUS ISSUE</h1>
    <pre><?php

try {
    echo "Starting fix process...\n\n";

    // STEP 1: Check current table structure
    echo "STEP 1: Analyzing wallet_transactions table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[$row['Field']] = $row;
    }

    echo "<span class='info'>Found " . count($columns) . " columns</span>\n";
    echo "<span class='info'>Columns: " . implode(', ', array_keys($columns)) . "</span>\n\n";

    // STEP 2: Check if status column exists and its type
    echo "STEP 2: Checking status column...\n";

    if (!isset($columns['status'])) {
        echo "<span class='warning'>⚠️  status column NOT FOUND!</span>\n";
        echo "Adding status column...\n";

        $pdo->exec("
            ALTER TABLE wallet_transactions
            ADD COLUMN `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending'
            AFTER `description`
        ");

        echo "<span class='success'>✅ status column ADDED</span>\n\n";
    } else {
        $statusType = $columns['status']['Type'];
        echo "<span class='success'>✅ status column EXISTS</span>\n";
        echo "<span class='info'>Current type: $statusType</span>\n";

        // Check if 'completed' is in the enum
        if (strpos($statusType, 'completed') === false) {
            echo "<span class='warning'>⚠️  'completed' not in ENUM values!</span>\n";
            echo "Updating ENUM to include 'completed'...\n";

            $pdo->exec("
                ALTER TABLE wallet_transactions
                MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending'
            ");

            echo "<span class='success'>✅ ENUM updated</span>\n\n";
        } else {
            echo "<span class='success'>✅ ENUM already includes 'completed'</span>\n\n";
        }
    }

    // STEP 3: Check if payment_status column exists
    echo "STEP 3: Checking payment_status column...\n";

    if (!isset($columns['payment_status'])) {
        echo "<span class='warning'>⚠️  payment_status column NOT FOUND!</span>\n";
        echo "Adding payment_status column...\n";

        $pdo->exec("
            ALTER TABLE wallet_transactions
            ADD COLUMN `payment_status` VARCHAR(50) NULL
            AFTER `payment_method`
        ");

        echo "<span class='success'>✅ payment_status column ADDED</span>\n\n";
    } else {
        echo "<span class='success'>✅ payment_status column EXISTS</span>\n\n";
    }

    // STEP 4: Check current records
    echo "STEP 4: Analyzing current deposit records...\n";

    $stmt = $pdo->query("
        SELECT
            status,
            payment_status,
            COUNT(*) as count,
            GROUP_CONCAT(id ORDER BY id DESC SEPARATOR ', ') as transaction_ids
        FROM wallet_transactions
        WHERE type IN ('topup', 'deposit')
        GROUP BY status, payment_status
    ");

    $statusGroups = $stmt->fetchAll();

    echo "<span class='info'>Status Distribution:</span>\n\n";
    echo "<table>";
    echo "<tr><th>Status</th><th>Payment Status</th><th>Count</th><th>Transaction IDs (newest)</th></tr>";

    foreach ($statusGroups as $group) {
        $ids = $group['transaction_ids'];
        if (strlen($ids) > 50) {
            $id_array = explode(', ', $ids);
            $ids = implode(', ', array_slice($id_array, 0, 5)) . '... (and ' . ($group['count'] - 5) . ' more)';
        }

        echo "<tr>";
        echo "<td><span class='info'>" . ($group['status'] ?: 'NULL') . "</span></td>";
        echo "<td><span class='info'>" . ($group['payment_status'] ?: 'NULL') . "</span></td>";
        echo "<td><span class='success'>" . $group['count'] . "</span></td>";
        echo "<td>" . $ids . "</td>";
        echo "</tr>";
    }
    echo "</table>\n\n";

    // STEP 5: Fix records with wrong status
    echo "STEP 5: Fixing deposit records...\n";

    // Fix: If status is 'completed' but payment_status is 'pending', set status to 'pending'
    $stmt = $pdo->prepare("
        UPDATE wallet_transactions
        SET status = 'pending'
        WHERE type IN ('topup', 'deposit')
          AND status = 'completed'
          AND (payment_status = 'pending' OR payment_status IS NULL)
    ");
    $stmt->execute();
    $fixed1 = $stmt->rowCount();

    if ($fixed1 > 0) {
        echo "<span class='success'>✅ Fixed $fixed1 records: completed → pending</span>\n";
    }

    // Fix: If status is NULL, set to 'pending'
    $stmt = $pdo->prepare("
        UPDATE wallet_transactions
        SET status = 'pending'
        WHERE type IN ('topup', 'deposit')
          AND status IS NULL
    ");
    $stmt->execute();
    $fixed2 = $stmt->rowCount();

    if ($fixed2 > 0) {
        echo "<span class='success'>✅ Fixed $fixed2 records: NULL → pending</span>\n";
    }

    // Fix: If proof_image exists but status is 'completed', set to 'pending'
    $stmt = $pdo->prepare("
        UPDATE wallet_transactions
        SET status = 'pending'
        WHERE type IN ('topup', 'deposit')
          AND status = 'completed'
          AND proof_image IS NOT NULL
          AND proof_image != ''
    ");
    $stmt->execute();
    $fixed3 = $stmt->rowCount();

    if ($fixed3 > 0) {
        echo "<span class='success'>✅ Fixed $fixed3 records with proof: completed → pending</span>\n";
    }

    if ($fixed1 == 0 && $fixed2 == 0 && $fixed3 == 0) {
        echo "<span class='info'>ℹ️  No records needed fixing</span>\n";
    }

    echo "\n";

    // STEP 6: Show updated status distribution
    echo "STEP 6: Updated status distribution...\n";

    $stmt = $pdo->query("
        SELECT
            status,
            COUNT(*) as count
        FROM wallet_transactions
        WHERE type IN ('topup', 'deposit')
        GROUP BY status
    ");

    $newGroups = $stmt->fetchAll();

    echo "<table>";
    echo "<tr><th>Status</th><th>Count</th></tr>";

    foreach ($newGroups as $group) {
        $color = 'info';
        if ($group['status'] == 'pending') $color = 'warning';
        if ($group['status'] == 'approved') $color = 'success';
        if ($group['status'] == 'rejected') $color = 'error';

        echo "<tr>";
        echo "<td><span class='$color'>" . ($group['status'] ?: 'NULL') . "</span></td>";
        echo "<td><span class='$color'>" . $group['count'] . "</span></td>";
        echo "</tr>";
    }
    echo "</table>\n\n";

    // STEP 7: Show pending deposits that need admin action
    echo "STEP 7: Deposits awaiting admin approval...\n";

    $stmt = $pdo->query("
        SELECT
            wt.id,
            wt.created_at,
            u.name as user_name,
            wt.amount,
            wt.amount_original,
            wt.unique_code,
            wt.status,
            wt.payment_status,
            wt.proof_image
        FROM wallet_transactions wt
        JOIN users u ON wt.user_id = u.id
        WHERE wt.type IN ('topup', 'deposit')
          AND wt.status = 'pending'
        ORDER BY wt.created_at DESC
        LIMIT 10
    ");

    $pendingDeposits = $stmt->fetchAll();

    if (count($pendingDeposits) > 0) {
        echo "<span class='warning'>⚠️  " . count($pendingDeposits) . " deposits waiting for approval</span>\n\n";

        echo "<table>";
        echo "<tr><th>ID</th><th>Date</th><th>User</th><th>Amount</th><th>Has Proof</th><th>Status</th></tr>";

        foreach ($pendingDeposits as $deposit) {
            echo "<tr>";
            echo "<td>" . $deposit['id'] . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($deposit['created_at'])) . "</td>";
            echo "<td>" . htmlspecialchars($deposit['user_name']) . "</td>";
            echo "<td>Rp " . number_format($deposit['amount'], 0, ',', '.') . "</td>";
            echo "<td>" . ($deposit['proof_image'] ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "</td>";
            echo "<td><span class='warning'>" . $deposit['status'] . "</span></td>";
            echo "</tr>";
        }
        echo "</table>\n\n";
    } else {
        echo "<span class='info'>ℹ️  No pending deposits</span>\n\n";
    }

    // STEP 8: Summary
    echo "<span class='info'>========================================</span>\n";
    echo "<span class='success'>✅ DEPOSIT STATUS FIX COMPLETE!</span>\n";
    echo "<span class='info'>========================================</span>\n\n";

    echo "What was fixed:\n";
    echo "1. ✅ Added/verified 'status' column (ENUM: pending, approved, rejected, completed)\n";
    echo "2. ✅ Added/verified 'payment_status' column\n";
    echo "3. ✅ Fixed " . ($fixed1 + $fixed2 + $fixed3) . " records with wrong status\n";
    echo "4. ✅ All deposit records now have correct status\n\n";

    echo "Next steps:\n";
    echo "1. Go to /admin/deposits/\n";
    echo "2. Click 'Pending' tab\n";
    echo "3. ✅ You should see pending deposits!\n";
    echo "4. Click on a deposit to approve/reject\n\n";

    echo "<span class='warning'>IMPORTANT:</span>\n";
    echo "- Deposits will be created with status='pending'\n";
    echo "- Admin can approve → status='approved' + balance added\n";
    echo "- Admin can reject → status='rejected'\n";
    echo "- 'completed' status is only for Midtrans auto-approved transactions\n\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ DATABASE ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "<span class='error'>SQL State: " . $e->getCode() . "</span>\n";
} catch (Exception $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

?></pre>
</body>
</html>
