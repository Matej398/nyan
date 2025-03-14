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