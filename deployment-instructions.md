# GitHub Webhook Deployment System Setup Instructions

This document provides step-by-step instructions for setting up an automatic deployment system that will update your websites whenever you push to GitHub.

## Files You Need

1. `webhook-handler.php` - The main script that processes GitHub webhooks
2. `repositories.json` - Configuration file for your repositories
3. `generate-secrets.php` - Script to generate secure secret keys
4. `add-repository.php` - Script to add new repositories to the configuration

## Step 1: Local Setup

1. Create a new folder on your computer called `deployment-system`
2. Save all the provided files in this folder

## Step 2: Generate Secret Keys

1. Open a command prompt or terminal
2. Navigate to the `deployment-system` folder
3. Run the generate-secrets script:
   ```
   php generate-secrets.php
   ```
4. This will update the `repositories.json` file with secure secret keys for each repository
5. Make note of the secret keys for each repository - you'll need them when setting up the webhooks in GitHub

## Step 3: Server Setup

1. SSH into your VPS:
   ```
   ssh root@145.223.99.106
   ```

2. Create the necessary directories:
   ```
   mkdir -p /var/www/webhooks
   mkdir -p /var/www/deployment-config
   ```

3. Upload the files to your server:
   - Upload `webhook-handler.php` to `/var/www/webhooks/`
   - Upload the updated `repositories.json` to `/var/www/deployment-config/`

   You can use SCP, SFTP, or any file transfer method you prefer:
   ```
   scp webhook-handler.php root@145.223.99.106:/var/www/webhooks/
   scp repositories.json root@145.223.99.106:/var/www/deployment-config/
   ```

4. Set proper permissions:
   ```
   chmod 644 /var/www/webhooks/webhook-handler.php
   chmod 644 /var/www/deployment-config/repositories.json
   touch /var/www/webhooks/webhook-log.txt
   chmod 666 /var/www/webhooks/webhook-log.txt
   ```

5. Make sure the web server user (www-data) has permission to execute git commands:
   ```
   # Add www-data to your user group
   usermod -a -G root www-data

   # Make sure your project directories are accessible
   chmod 775 /var/www/html/codelabhaven/projects
   ```

## Step 4: Configure Web Server

1. Create a symbolic link to make the webhook handler accessible via the web:
   ```
   ln -s /var/www/webhooks/webhook-handler.php /var/www/html/webhooks/webhook-handler.php
   ```

   Or if you prefer to copy:
   ```
   mkdir -p /var/www/html/webhooks
   cp /var/www/webhooks/webhook-handler.php /var/www/html/webhooks/
   ```

2. Make sure the directory has the correct permissions:
   ```
   chmod 755 /var/www/html/webhooks
   ```

## Step 5: Set Up GitHub Webhooks

For each repository:

1. Go to your GitHub repository (e.g., https://github.com/Matej398/nyan-game)
2. Click on "Settings" > "Webhooks" > "Add webhook"
3. Fill in the form:
   - Payload URL: `https://codelabhaven.com/webhooks/webhook-handler.php`
   - Content type: `application/json`
   - Secret: Use the generated secret for this repository (from Step 2)
   - Which events would you like to trigger this webhook?: Select "Just the push event"
   - Active: Check this box
4. Click "Add webhook"

## Step 6: Test the Webhook

1. Make a small change to your repository
2. Commit and push the change
3. Check if the changes were automatically deployed to your server
4. Check the log file for any errors:
   ```
   cat /var/www/webhooks/webhook-log.txt
   ```

## Adding New Repositories in the Future

1. On your local computer, navigate to the `deployment-system` folder
2. Run the add-repository script:
   ```
   php add-repository.php new-repo-name main /var/www/html/codelabhaven/projects/new-repo-name
   ```
3. Upload the updated `repositories.json` to your server:
   ```
   scp repositories.json root@145.223.99.106:/var/www/deployment-config/
   ```
4. Set up a webhook in GitHub using the new secret key

## Troubleshooting

- Check the log file: `/var/www/webhooks/webhook-log.txt`
- Make sure the web server user has permission to execute git commands
- Verify that the webhook is properly configured in GitHub
- Check that the repository paths in `repositories.json` are correct 