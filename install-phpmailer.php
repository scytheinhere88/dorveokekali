<?php
/**
 * PHPMailer Auto Installer
 * Script untuk install PHPMailer otomatis tanpa composer
 */

set_time_limit(300); // 5 minutes

$vendor_dir = __DIR__ . '/vendor';
$phpmailer_dir = $vendor_dir . '/phpmailer/phpmailer';
$github_url = 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip';
$zip_file = $vendor_dir . '/phpmailer-master.zip';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Install PHPMailer - Dorve.id</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1a1a1a;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 12px;
        }
        .status {
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-weight: 600;
        }
        .success {
            background: #D1FAE5;
            color: #065F46;
            border: 2px solid #10B981;
        }
        .error {
            background: #FEE2E2;
            color: #991B1B;
            border: 2px solid #EF4444;
        }
        .info {
            background: #DBEAFE;
            color: #1E40AF;
            border: 2px solid #3B82F6;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .progress {
            background: #FEF3C7;
            color: #92400E;
            border: 2px solid #F59E0B;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            margin: 10px 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .btn:hover {
            background: #2d2d2d;
        }
        .btn-green {
            background: #10B981;
        }
        .btn-green:hover {
            background: #059669;
        }
        code {
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        ul {
            line-height: 2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 PHPMailer Auto Installer</h1>

        <?php
        // Check if already installed
        if (file_exists($phpmailer_dir . '/src/PHPMailer.php')) {
            echo '<div class="status success">';
            echo '✅ <strong>PHPMailer sudah ter-install!</strong><br><br>';
            echo 'Location: <code>' . $phpmailer_dir . '</code><br>';
            echo 'Version: Latest from GitHub';
            echo '</div>';

            echo '<div class="info">';
            echo '<strong>✨ Next Steps:</strong><br>';
            echo '<ol>';
            echo '<li>Setup Gmail App Password</li>';
            echo '<li>Update config di <code>/includes/email-helper.php</code></li>';
            echo '<li>Test dengan <code>/test-email.php</code></li>';
            echo '</ol>';
            echo '</div>';

            echo '<a href="/test-email.php" class="btn btn-green">Test Email System →</a>';
            echo '<a href="/" class="btn">← Back to Home</a>';
        } else {
            // Install form
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
                echo '<div class="progress">🔄 Installing PHPMailer...</div>';

                try {
                    // Step 1: Create directories
                    echo '<div class="status info">Step 1: Creating directories...</div>';
                    if (!is_dir($vendor_dir)) {
                        mkdir($vendor_dir, 0755, true);
                    }
                    if (!is_dir($phpmailer_dir)) {
                        mkdir($phpmailer_dir, 0755, true);
                    }
                    echo '<div class="status success">✅ Directories created</div>';

                    // Step 2: Download ZIP
                    echo '<div class="status info">Step 2: Downloading PHPMailer from GitHub...</div>';

                    // Use CURL or file_get_contents
                    if (function_exists('curl_init')) {
                        $ch = curl_init($github_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $zip_content = curl_exec($ch);
                        $error = curl_error($ch);
                        curl_close($ch);

                        if ($error) {
                            throw new Exception('CURL Error: ' . $error);
                        }
                    } else {
                        $zip_content = @file_get_contents($github_url);
                    }

                    if (!$zip_content) {
                        throw new Exception('Failed to download PHPMailer');
                    }

                    file_put_contents($zip_file, $zip_content);
                    echo '<div class="status success">✅ Downloaded ' . number_format(strlen($zip_content)) . ' bytes</div>';

                    // Step 3: Extract ZIP
                    echo '<div class="status info">Step 3: Extracting files...</div>';

                    $zip = new ZipArchive();
                    if ($zip->open($zip_file) === TRUE) {
                        $zip->extractTo($vendor_dir);
                        $zip->close();

                        // Move files to correct location
                        $extracted_dir = $vendor_dir . '/PHPMailer-master';
                        if (is_dir($extracted_dir)) {
                            // Copy src folder
                            $src_dir = $extracted_dir . '/src';
                            $dest_dir = $phpmailer_dir . '/src';

                            if (!is_dir($dest_dir)) {
                                mkdir($dest_dir, 0755, true);
                            }

                            $files = scandir($src_dir);
                            foreach ($files as $file) {
                                if ($file != '.' && $file != '..') {
                                    copy($src_dir . '/' . $file, $dest_dir . '/' . $file);
                                }
                            }

                            // Copy autoload
                            if (file_exists($extracted_dir . '/src/PHPMailer.php')) {
                                // Create simple autoload
                                $autoload_content = "<?php\n";
                                $autoload_content .= "spl_autoload_register(function(\$class) {\n";
                                $autoload_content .= "    if (strpos(\$class, 'PHPMailer') === 0) {\n";
                                $autoload_content .= "        \$file = __DIR__ . '/phpmailer/phpmailer/src/' . str_replace('PHPMailer\\\\PHPMailer\\\\', '', \$class) . '.php';\n";
                                $autoload_content .= "        if (file_exists(\$file)) require_once \$file;\n";
                                $autoload_content .= "    }\n";
                                $autoload_content .= "});\n";

                                file_put_contents($vendor_dir . '/autoload.php', $autoload_content);
                            }

                            // Cleanup
                            deleteDirectory($extracted_dir);
                            unlink($zip_file);
                        }

                        echo '<div class="status success">✅ Files extracted successfully</div>';
                    } else {
                        throw new Exception('Failed to open ZIP file');
                    }

                    // Step 4: Verify
                    echo '<div class="status info">Step 4: Verifying installation...</div>';

                    if (file_exists($phpmailer_dir . '/src/PHPMailer.php')) {
                        echo '<div class="status success">';
                        echo '<strong>🎉 PHPMailer berhasil ter-install!</strong><br><br>';
                        echo 'Location: <code>' . $phpmailer_dir . '</code>';
                        echo '</div>';

                        echo '<div class="info">';
                        echo '<strong>✨ Next Steps:</strong><br>';
                        echo '<ol>';
                        echo '<li>Setup Gmail App Password</li>';
                        echo '<li>Update config di <code>/includes/email-helper.php</code></li>';
                        echo '<li>Test dengan <code>/test-email.php</code></li>';
                        echo '</ol>';
                        echo 'Baca panduan lengkap: <a href="/EMAIL-SETUP-GUIDE.md">EMAIL-SETUP-GUIDE.md</a>';
                        echo '</div>';

                        echo '<a href="/test-email.php" class="btn btn-green">Test Email System →</a>';
                    } else {
                        throw new Exception('Installation failed - PHPMailer.php not found');
                    }

                } catch (Exception $e) {
                    echo '<div class="status error">';
                    echo '❌ <strong>Installation Failed!</strong><br><br>';
                    echo 'Error: ' . htmlspecialchars($e->getMessage());
                    echo '</div>';

                    echo '<div class="info">';
                    echo '<strong>🔧 Manual Installation:</strong><br>';
                    echo '1. Download: <a href="' . $github_url . '" target="_blank">PHPMailer ZIP</a><br>';
                    echo '2. Extract ke: <code>' . $phpmailer_dir . '</code><br>';
                    echo '3. Pastikan ada file: <code>vendor/phpmailer/phpmailer/src/PHPMailer.php</code>';
                    echo '</div>';
                }

                echo '<br><a href="/" class="btn">← Back to Home</a>';

            } else {
                // Show install button
                echo '<div class="info">';
                echo '<strong>📋 What will be installed:</strong><br>';
                echo '<ul>';
                echo '<li>PHPMailer (Latest version from GitHub)</li>';
                echo '<li>Location: <code>' . $phpmailer_dir . '</code></li>';
                echo '<li>Size: ~500KB</li>';
                echo '</ul>';
                echo '</div>';

                echo '<div class="status info">';
                echo '<strong>ℹ️ Requirements:</strong><br>';
                echo '<ul>';
                echo '<li>PHP ZipArchive extension</li>';
                echo '<li>CURL or allow_url_fopen enabled</li>';
                echo '<li>Write permission to /vendor folder</li>';
                echo '</ul>';
                echo '</div>';

                // Check requirements
                $can_install = true;
                $errors = [];

                if (!class_exists('ZipArchive')) {
                    $errors[] = 'ZipArchive extension not available';
                    $can_install = false;
                }

                if (!is_writable(__DIR__)) {
                    $errors[] = 'Directory not writable';
                    $can_install = false;
                }

                if (!empty($errors)) {
                    echo '<div class="status error">';
                    echo '<strong>❌ Requirements not met:</strong><br>';
                    foreach ($errors as $error) {
                        echo '• ' . $error . '<br>';
                    }
                    echo '</div>';

                    echo '<div class="info">';
                    echo '<strong>Alternative:</strong> Install via Composer<br>';
                    echo '<code>composer require phpmailer/phpmailer</code>';
                    echo '</div>';
                } else {
                    echo '<form method="POST">';
                    echo '<button type="submit" name="install" class="btn btn-green">🚀 Install PHPMailer Now</button>';
                    echo '</form>';
                }

                echo '<a href="/" class="btn">← Cancel</a>';
            }
        }

        // Helper function
        function deleteDirectory($dir) {
            if (!is_dir($dir)) return;
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . '/' . $file;
                is_dir($path) ? deleteDirectory($path) : unlink($path);
            }
            rmdir($dir);
        }
        ?>
    </div>
</body>
</html>
