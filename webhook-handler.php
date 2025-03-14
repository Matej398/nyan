<?php
/**
 * GitHub Webhook Handler
 * 
 * This script handles webhooks from GitHub for multiple repositories.
 * It verifies the webhook signature, identifies the repository,
 * and deploys the code to the appropriate location.
 */

// Set error reporting for debugging (you can disable this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for debugging
$logFile = __DIR__ . '/webhook-log.txt';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logMessage('Webhook received');

// Get the raw POST data
$payload = file_get_contents('php://input');
if (empty($payload)) {
    logMessage('Error: No payload received');
    http_response_code(400);
    die('No payload received');
}

// Get headers
$headers = getallheaders();
$signature = isset($headers['X-Hub-Signature-256']) ? $headers['X-Hub-Signature-256'] : '';
$event = isset($headers['X-GitHub-Event']) ? $headers['X-GitHub-Event'] : '';
$delivery = isset($headers['X-GitHub-Delivery']) ? $headers['X-GitHub-Delivery'] : '';

logMessage("Event: $event, Delivery: $delivery");

// Only process push events
if ($event !== 'push') {
    logMessage('Ignoring non-push event: ' . $event);
    http_response_code(200);
    die('Ignoring non-push event');
}

// Parse the JSON payload
$data = json_decode($payload, true);
if (!$data) {
    logMessage('Error: Invalid JSON payload');
    http_response_code(400);
    die('Invalid JSON payload');
}

// Get repository information
$repoName = isset($data['repository']['name']) ? $data['repository']['name'] : '';
$repoOwner = isset($data['repository']['owner']['name']) ? $data['repository']['owner']['name'] : '';
$branch = isset($data['ref']) ? str_replace('refs/heads/', '', $data['ref']) : '';

logMessage("Repository: $repoOwner/$repoName, Branch: $branch");

// Load repository configuration
$configFile = __DIR__ . '/../deployment-config/repositories.json';
if (!file_exists($configFile)) {
    logMessage("Error: Configuration file not found: $configFile");
    http_response_code(500);
    die('Configuration file not found');
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    logMessage('Error: Invalid configuration file');
    http_response_code(500);
    die('Invalid configuration file');
}

// Find repository configuration
$repoConfig = null;
foreach ($config['repositories'] as $repo) {
    if ($repo['name'] === $repoName) {
        $repoConfig = $repo;
        break;
    }
}

if (!$repoConfig) {
    logMessage("Error: Repository not configured: $repoName");
    http_response_code(404);
    die('Repository not configured');
}

// Verify the signature
$secret = $repoConfig['secret'];
$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $expectedSignature)) {
    logMessage('Error: Invalid signature');
    http_response_code(403);
    die('Invalid signature');
}

// Only deploy if the branch matches
if ($branch !== $repoConfig['branch']) {
    logMessage("Ignoring push to non-deployment branch: $branch");
    http_response_code(200);
    die("Ignoring push to non-deployment branch: $branch");
}

// Deploy the repository
$deployPath = $repoConfig['path'];
logMessage("Deploying to: $deployPath");

// Check if the repository directory exists
if (!is_dir($deployPath)) {
    // Clone the repository if it doesn't exist
    $gitUrl = "https://github.com/{$repoOwner}/{$repoName}.git";
    $command = "git clone --branch {$repoConfig['branch']} {$gitUrl} {$deployPath} 2>&1";
    
    logMessage("Cloning repository: $command");
    exec($command, $output, $returnCode);
    
    $outputStr = implode("\n", $output);
    logMessage("Clone output: $outputStr");
    
    if ($returnCode !== 0) {
        logMessage("Error: Failed to clone repository: $outputStr");
        http_response_code(500);
        die("Failed to clone repository: $outputStr");
    }
} else {
    // Use hard reset instead of pull to avoid merge conflicts
    $currentDir = getcwd();
    chdir($deployPath);
    
    // Fetch the latest changes
    $command = "git fetch origin {$repoConfig['branch']} 2>&1";
    logMessage("Fetching repository: $command");
    exec($command, $output, $returnCode);
    
    $outputStr = implode("\n", $output);
    logMessage("Fetch output: $outputStr");
    
    if ($returnCode !== 0) {
        logMessage("Error: Failed to fetch repository: $outputStr");
        http_response_code(500);
        die("Failed to fetch repository: $outputStr");
    }
    
    // Hard reset to the latest commit on the branch
    $command = "git reset --hard origin/{$repoConfig['branch']} 2>&1";
    logMessage("Resetting repository: $command");
    exec($command, $output, $returnCode);
    
    $outputStr = implode("\n", $output);
    logMessage("Reset output: $outputStr");
    
    chdir($currentDir);
    
    if ($returnCode !== 0) {
        logMessage("Error: Failed to reset repository: $outputStr");
        http_response_code(500);
        die("Failed to reset repository: $outputStr");
    }
}

// Run post-deployment commands if any
if (isset($repoConfig['post_commands']) && !empty($repoConfig['post_commands'])) {
    $currentDir = getcwd();
    chdir($deployPath);
    
    foreach ($repoConfig['post_commands'] as $command) {
        logMessage("Running post-deployment command: $command");
        exec($command . " 2>&1", $output, $returnCode);
        
        $outputStr = implode("\n", $output);
        logMessage("Command output: $outputStr");
        
        if ($returnCode !== 0) {
            logMessage("Warning: Command failed: $command, Output: $outputStr");
        }
    }
    
    chdir($currentDir);
}

// Success response
logMessage("Deployment successful for $repoName");
http_response_code(200);
echo "Deployment successful for $repoName"; 