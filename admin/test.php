<?php
// Simple test file to verify admin directory is accessible
echo "ADMIN DIRECTORY IS ACCESSIBLE! ✅";
echo "<br><br>";
echo "File: " . __FILE__;
echo "<br>";
echo "Directory: " . __DIR__;
echo "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A');
echo "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A');
echo "<br><br>";
echo "<a href='/admin/login.php'>Try Login Page</a>";
echo " | ";
echo "<a href='/admin/index.php'>Try Admin Dashboard</a>";
