<?php
/**
 * Quick PHPMailer Installer - One Click!
 * Download & setup PHPMailer tanpa ribet
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$vendor_dir = __DIR__ . '/vendor';
$phpmailer_dir = $vendor_dir . '/phpmailer/phpmailer';

// Step 1: Create directories
echo "📁 Creating directories...\n";
if (!is_dir($phpmailer_dir)) {
    mkdir($phpmailer_dir, 0755, true);
}
if (!is_dir($phpmailer_dir . '/src')) {
    mkdir($phpmailer_dir . '/src', 0755, true);
}
echo "✅ Directories created\n\n";

// Step 2: Download PHPMailer files from GitHub (specific version)
echo "📥 Downloading PHPMailer v6.9.1...\n";
$github_base = 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/';

$files = [
    'PHPMailer.php',
    'SMTP.php',
    'Exception.php',
    'POP3.php',
    'OAuth.php'
];

$downloaded = 0;
foreach ($files as $file) {
    $url = $github_base . $file;
    $dest = $phpmailer_dir . '/src/' . $file;

    echo "  → Downloading $file... ";

    $content = @file_get_contents($url);
    if ($content !== false) {
        file_put_contents($dest, $content);
        echo "✅ (" . number_format(strlen($content)) . " bytes)\n";
        $downloaded++;
    } else {
        echo "❌ FAILED\n";
    }
}

echo "\n📊 Downloaded $downloaded / " . count($files) . " files\n\n";

// Step 3: Create autoloader
echo "🔧 Creating autoloader...\n";
$autoload_content = '<?php
/**
 * Simple autoloader for PHPMailer
 */
spl_autoload_register(function ($class) {
    $prefix = \'PHPMailer\\\\PHPMailer\\\\\';
    $base_dir = __DIR__ . \'/phpmailer/phpmailer/src/\';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace(\'\\\\\', \'/\', $relative_class) . \'.php\';

    if (file_exists($file)) {
        require $file;
    }
});
';

file_put_contents($vendor_dir . '/autoload.php', $autoload_content);
echo "✅ Autoloader created\n\n";

// Step 4: Test installation
echo "🧪 Testing PHPMailer...\n";
require_once $vendor_dir . '/autoload.php';

if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer class loaded successfully!\n\n";
    echo "🎉 INSTALLATION COMPLETE!\n\n";
    echo "Next steps:\n";
    echo "1. Test email: /diagnose-email.php\n";
    echo "2. Try register akun baru!\n";
} else {
    echo "❌ PHPMailer class not found!\n";
    echo "Installation may have failed.\n";
}
