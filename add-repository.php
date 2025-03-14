<?php
/**
 * Add Repository Script
 * 
 * This script adds a new repository to the configuration file
 * and generates a secure secret key for it.
 */

// Check if arguments are provided
if ($argc < 4) {
    echo "Usage: php add-repository.php <repo_name> <branch> <deploy_path>\n";
    echo "Example: php add-repository.php my-new-repo main /var/www/html/codelabhaven/projects/my-new-repo\n";
    exit(1);
}

// Get arguments
$repoName = $argv[1];
$branch = $argv[2];
$deployPath = $argv[3];

// Path to the configuration file
$configFile = __DIR__ . '/repositories.json';

// Check if the configuration file exists
if (!file_exists($configFile)) {
    die("Error: Configuration file not found: $configFile\n");
}

// Load the configuration
$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    die("Error: Invalid configuration file\n");
}

// Check if the repository already exists
foreach ($config['repositories'] as $repo) {
    if ($repo['name'] === $repoName) {
        die("Error: Repository '$repoName' already exists in the configuration\n");
    }
}

// Generate a secure random key
function generateSecureKey() {
    return bin2hex(random_bytes(32));
}

// Add the new repository
$newRepo = [
    'name' => $repoName,
    'branch' => $branch,
    'path' => $deployPath,
    'secret' => generateSecureKey(),
    'post_commands' => [
        'chmod 644 *.php *.html *.js *.css'
    ]
];

$config['repositories'][] = $newRepo;

// Save the updated configuration
file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
echo "Repository '$repoName' added to configuration\n";

// Display instructions
echo "\n";
echo "Next steps:\n";
echo "1. Upload the updated repositories.json file to your server\n";
echo "2. Set up a webhook in GitHub for this repository\n";
echo "   - Payload URL: https://codelabhaven.com/webhooks/webhook-handler.php\n";
echo "   - Content type: application/json\n";
echo "   - Secret: {$newRepo['secret']}\n";
echo "   - Events: Just the push event\n";
echo "\n";
echo "Secret key for $repoName: {$newRepo['secret']}\n"; 