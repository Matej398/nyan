# Nyan Game

A simple Nyan Cat-inspired game with a leaderboard system.

## Database Setup Instructions

To set up the database on your VPS, follow these steps:

1. Upload all the PHP files and the SQL file to your server in the `/projects/nyan/` directory.

2. Make sure you have the following database credentials set up on your VPS:
   - Database name: `u775386788_nyan`
   - Username: `u775386788_nyanuser`
   - Password: `PxBeoY5Ei#xB`
   - Host: `localhost`

3. You have two options to import the database:

   **Option 1:** Visit `https://codelabhaven.com/projects/nyan/import_db.php` in your browser to create the database table and import the sample data directly from PHP.

   **Option 2:** Visit `https://codelabhaven.com/projects/nyan/import_sql.php` to import the database from the SQL file.

4. Visit `https://codelabhaven.com/projects/nyan/test_db.php` to verify that the database connection is working correctly.

5. If everything is set up correctly, you should be able to play the game and see the leaderboard at `https://codelabhaven.com/projects/nyan/`.

## Manual Database Import

If you prefer to import the database manually:

1. Connect to your database using phpMyAdmin or a similar tool.
2. Create a new database named `u775386788_nyan` if it doesn't exist.
3. Import the `nyan_database.sql` file.

## Troubleshooting

If you encounter any issues:

1. Check the database credentials in `db_config.php`.
2. Make sure the PHP files have the correct permissions (usually 644).
3. Check the server error logs for any PHP or MySQL errors.
4. If you're getting 404 errors, make sure the files are in the correct directory.

## Files

- `index.html` - The main game page
- `game.js` - The game logic
- `styles.css` - CSS styles for the game
- `db_config.php` - Database configuration
- `get_leaderboard.php` - API endpoint to fetch leaderboard data
- `save_score.php` - API endpoint to save new scores
- `test_db.php` - Tool to test database connection
- `import_db.php` - Tool to import the database schema and data from PHP
- `nyan_database.sql` - SQL file containing the database schema and data
- `import_sql.php` - Tool to import the database from the SQL file

# GitHub Webhook Deployment System

This system automatically deploys your GitHub repositories to your server whenever you push changes.

## Files

- `webhook-handler.php` - The main script that processes GitHub webhooks
- `repositories.json` - Configuration file for your repositories
- `generate-secrets.php` - Script to generate secure secret keys
- `README.md` - This file

## Setup Instructions

### 1. Server Setup

1. Create the necessary directories on your server:

```bash
mkdir -p /var/www/webhooks
mkdir -p /var/www/deployment-config
```

2. Upload the files to your server:

```bash
# Upload webhook-handler.php to /var/www/webhooks/
# Upload repositories.json to /var/www/deployment-config/
```

3. Set proper permissions:

```bash
chmod 644 /var/www/webhooks/webhook-handler.php
chmod 644 /var/www/deployment-config/repositories.json
```

4. Make sure the web server user (www-data) has permission to execute git commands:

```bash
# Add www-data to your user group
usermod -a -G your_username www-data

# Make sure your project directories are accessible
chmod 775 /var/www/html/codelabhaven/projects
```

### 2. Generate Secret Keys

1. Run the `generate-secrets.php` script locally:

```bash
php generate-secrets.php
```

2. Upload the updated `repositories.json` file to your server:

```bash
# Upload to /var/www/deployment-config/repositories.json
```

### 3. Set Up GitHub Webhooks

For each repository:

1. Go to your GitHub repository (e.g., https://github.com/Matej398/nyan-game)
2. Click on "Settings" > "Webhooks" > "Add webhook"
3. Fill in the form:
   - Payload URL: `https://codelabhaven.com/webhooks/webhook-handler.php`
   - Content type: `application/json`
   - Secret: Use the generated secret for this repository
   - Which events would you like to trigger this webhook?: Select "Just the push event"
   - Active: Check this box
4. Click "Add webhook"

### 4. Test the Webhook

1. Make a small change to your repository
2. Commit and push the change
3. Check if the changes were automatically deployed to your server
4. Check the log file for any errors:

```bash
cat /var/www/webhooks/webhook-log.txt
```

## Adding New Repositories

1. Add the new repository to `repositories.json`
2. Generate a new secret key
3. Set up a webhook in GitHub using the new secret key

## Troubleshooting

- Check the log file: `/var/www/webhooks/webhook-log.txt`
- Make sure the web server user has permission to execute git commands
- Verify that the webhook is properly configured in GitHub
- Check that the repository paths in `repositories.json` are correct 