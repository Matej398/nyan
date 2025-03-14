-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS nyan_game;

-- Use the database
USE nyan_game;

-- Create leaderboard table
CREATE TABLE IF NOT EXISTS leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(15) NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user with appropriate permissions
CREATE USER IF NOT EXISTS 'nyan_user'@'localhost' IDENTIFIED BY 'nyan_password';
GRANT ALL PRIVILEGES ON nyan_game.* TO 'nyan_user'@'localhost';
FLUSH PRIVILEGES;

-- Insert some sample data
INSERT INTO leaderboard (name, score) VALUES 
('Nyan Cat', 100),
('Rainbow', 85),
('Space Cat', 70),
('Star Chaser', 55),
('Pixel Master', 40); 