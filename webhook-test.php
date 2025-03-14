<?php
/**
 * Webhook Test File
 * 
 * This file is used to test if the webhook directory is accessible.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output a simple message
echo "GitHub webhook directory is accessible!";

// Display PHP info for debugging
echo "<br><br>Server Information:<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Script: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
?> 