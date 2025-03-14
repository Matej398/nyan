<?php
// Load projects from JSON
$projectsFile = 'data/projects.json';
$projects = [];
$projectCount = 0;
if (file_exists($projectsFile)) {
    $json = file_get_contents($projectsFile);
    $projects = json_decode($json, true);
    $projectCount = is_array($projects) ? count($projects) : 0;
}

// Database connection for /projects/nyan/
$leaderboardData = [];
if (strpos($_SERVER['REQUEST_URI'], '/projects/nyan/') !== false) {
    $conn = new mysqli("localhost", "root", "I8o.7UPKaxrx1tzbU/Ei", "mywebsite"); // Your local password
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query("SELECT name, score FROM leaderboard ORDER BY score DESC LIMIT 5");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $leaderboardData[] = $row;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - CodeLabHaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>
<body>
    <header class="header">
        <a href="/" class="header-title">
            <img src="/images/logo.svg" alt="CodeLabHaven Logo" class="header-logo">
        </a>
        <nav>
            <a href="/projects.php" class="active">projects<sup><?php echo $projectCount; ?></sup></a>
            <a href="/about.php">about</a>
        </nav>
    </header>
    <main class="main">
        <div class="cursor-wrapper">
            <div class="cursor" id="cursor"></div>
        </div>
        <section class="projects-page">
            <h1 class="main-title">projects</h1>
            <div class="projects-list">
                <?php foreach ($projects as $project): ?>
                    <div class="project-title-link">
                        <a href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank"><?php echo strtolower($project['title']); ?></a>
                    </div>
                <?php endforeach; ?>

                <?php
                // Display leaderboard for /projects/nyan/
                if (strpos($_SERVER['REQUEST_URI'], '/projects/nyan/') !== false) {
                    echo "<h2>Leaderboard</h2>";
                    if (!empty($leaderboardData)) {
                        foreach ($leaderboardData as $row) {
                            echo "Name: " . htmlspecialchars($row['name']) . " - Score: " . $row['score'] . "<br>";
                        }
                    } else {
                        echo "No leaderboard data available.";
                    }
                }
                ?>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="footer-text">
            ©2025 codelabhaven<span class="dot">.</span> design by <a href="https://x.com/TheBlecky398" target="_blank">blecky398</a>
        </div>
        <div class="footer-right">
            powered by grok3
        </div>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>