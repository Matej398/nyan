<?php
/**
 * Secret Key Generator
 * 
 * This script generates secure secret keys for GitHub webhooks
 * and updates the repositories.json configuration file.
 */

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

// Generate a secure random key
function generateSecureKey() {
    return bin2hex(random_bytes(32));
}

// Update the configuration with new secret keys
foreach ($config['repositories'] as &$repo) {
    $repo['secret'] = generateSecureKey();
    echo "Generated secret for {$repo['name']}\n";
}

// Save the updated configuration
file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
echo "Configuration updated with new secret keys\n";

// Display instructions
echo "\n";
echo "Next steps:\n";
echo "1. Upload the updated repositories.json file to your server\n";
echo "2. Set up webhooks in GitHub for each repository\n";
echo "   - Payload URL: https://codelabhaven.com/webhooks/webhook-handler.php\n";
echo "   - Content type: application/json\n";
echo "   - Secret: Use the generated secret for each repository\n";
echo "   - Events: Just the push event\n";
echo "\n";
echo "Secret keys for each repository:\n";
foreach ($config['repositories'] as $repo) {
    echo "{$repo['name']}: {$repo['secret']}\n";
} 