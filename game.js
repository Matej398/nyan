// game.js

// Get the canvases and contexts
const gameCanvas = document.getElementById("gameCanvas");
const gameCtx = gameCanvas.getContext("2d");
const bgCanvas = document.getElementById("backgroundCanvas");
const bgCtx = bgCanvas.getContext("2d");

// Optimize canvases for pixel-perfect rendering
gameCanvas.style.imageRendering = "pixelated";
gameCtx.imageSmoothingEnabled = false;
bgCanvas.style.imageRendering = "pixelated";
bgCtx.imageSmoothingEnabled = false;

// Scale factor for doubled resolution
const scale = 2;

// Animation timing
const speed = 1000; // 1s pulsation cycle

// Nyan Cat properties
let nyanX = 100 * scale;
let nyanY = (gameCanvas.height / 2 - 20) * scale;
let velocity = 0;
const gravity = 0.15 * scale;
const jump = -4 * scale;
let frameIndex = 0;
let frameTimer = 0;
const frameDelay = 5;
let tiltAngle = 0;

// Nyan Cat sprite dimensions
const frameWidth = 74; // Native sprite size
const frameHeight = 42;
const displayWidth = frameWidth * 1.2 * scale; // 20% larger
const displayHeight = frameHeight * 1.2 * scale; // 20% larger
const totalFrames = 12;

// Obstacles (rainbow pipes)
let pipes = [];
const pipeWidth = 60 * scale;
const pipeGap = 200 * scale; // Vertical gap
let pipeSpeed = 1 * scale;
let pipeSpawnTimer = 0;
const pipeSpawnInterval = 200; // Horizontal gap

// Game state
let score = 0;
let highScore = 0;
let gameOver = false;
let gameStarted = false;
let crashTime = 0;
const restartDelay = 500;
let animationFrameId = null;
let showLeaderboard = false;
let showNamePrompt = false; // New flag for name input
let playerName = "";

// Leaderboard storage
let leaderboard = JSON.parse(localStorage.getItem("nyanLeaderboard")) || [];
const maxLeaderboardEntries = 5;

// Load Nyan Cat sprite sheet
const nyanCatImg = new Image();
nyanCatImg.src = "./nyan-sprite.png";

// Name input popup elements
const namePrompt = document.getElementById("namePrompt");
const nameInput = document.getElementById("nameInput");
const submitNameBtn = document.getElementById("submitName");

// Star patterns (7x7 grids)
const starStyles = [
    // Star 1: Bold Cross
    [
        [0, 0, 1, 1, 1, 0, 0],
        [0, 0, 1, 1, 1, 0, 0],
        [1, 1, 1, 1, 1, 1, 1],
        [1, 1, 1, 1, 1, 1, 1],
        [1, 1, 1, 1, 1, 1, 1],
        [0, 0, 1, 1, 1, 0, 0],
        [0, 0, 1, 1, 1, 0, 0]
    ],
    // Star 2: Thin Cross
    [
        [0, 0, 0, 1, 0, 0, 0],
        [0, 0, 0, 1, 0, 0, 0],
        [0, 0, 1, 1, 1, 0, 0],
        [1, 1, 1, 1, 1, 1, 1],
        [0, 0, 1, 1, 1, 0, 0],
        [0, 0, 0, 1, 0, 0, 0],
        [0, 0, 0, 1, 0, 0, 0]
    ],
    // Star 3: Diamond Cross
    [
        [0, 0, 1, 1, 1, 0, 0],
        [0, 1, 0, 0, 0, 1, 0],
        [1, 0, 0, 0, 0, 0, 1],
        [1, 0, 0, 0, 0, 0, 1],
        [1, 0, 0, 0, 0, 0, 1],
        [0, 1, 0, 0, 0, 1, 0],
        [0, 0, 1, 1, 1, 0, 0]
    ]
];

// Random star arrays
const bgStars = [];
const gameStars = [];
for (let i = 0; i < 100; i++) { // 100 stars for background
    const size = Math.floor(Math.random() * 11) + 5; // Reduced from 7-21 to 5-15
    bgStars.push({
        x: Math.floor(Math.random() * bgCanvas.width),
        y: Math.floor(Math.random() * bgCanvas.height),
        baseSize: size,
        style: starStyles[Math.floor(Math.random() * starStyles.length)],
        phase: Math.random() * Math.PI * 2
    });
}
for (let i = 0; i < 6; i++) { // 6 stars for game
    const size = Math.floor(Math.random() * 11) + 5; // Reduced from 7-21 to 5-15
    gameStars.push({
        x: Math.floor(Math.random() * gameCanvas.width),
        y: Math.floor(Math.random() * gameCanvas.height),
        baseSize: size,
        style: starStyles[Math.floor(Math.random() * starStyles.length)],
        phase: Math.random() * Math.PI * 2
    });
}

// Input handling
document.addEventListener("keydown", (e) => {
    if (e.code === "Space") {
        e.preventDefault();
        if (!gameStarted && !showLeaderboard && !showNamePrompt) {
            gameStarted = true;
        } else if (!gameOver && !showLeaderboard && !showNamePrompt) {
            velocity = jump;
        } else if (Date.now() - crashTime > restartDelay) {
            if (showLeaderboard) {
                showLeaderboard = false;
                resetGame();
            } else if (gameOver && !showNamePrompt) {
                showNamePrompt = true;
                namePrompt.style.display = "block";
                nameInput.focus();
            }
        }
    } else if (e.code === "Enter") {
        e.preventDefault();
        if (!gameStarted && !showLeaderboard && !showNamePrompt) {
            showLeaderboard = true; // Show leaderboard from start screen
        } else if (showNamePrompt) {
            submitName();
        }
    } else if (e.code === "Escape") {
        e.preventDefault();
        if (gameOver && !showLeaderboard && !showNamePrompt && Date.now() - crashTime > restartDelay) {
            resetGame();
        }
    }
});
gameCanvas.addEventListener("click", () => {
    if (!gameStarted && !showLeaderboard && !showNamePrompt) {
        gameStarted = true;
    } else if (!gameOver && !showLeaderboard && !showNamePrompt) {
        velocity = jump;
    } else if (Date.now() - crashTime > restartDelay) {
        if (showLeaderboard) {
            showLeaderboard = false;
            resetGame();
        } else if (gameOver && !showNamePrompt) {
            showNamePrompt = true;
            namePrompt.style.display = "block";
            nameInput.focus();
        }
    }
});

// Handle name submission
submitNameBtn.addEventListener("click", submitName);
function submitName() {
    playerName = nameInput.value.trim() || "Nyanonymous"; // Default if empty
    namePrompt.style.display = "none";
    showNamePrompt = false;
    updateLeaderboard();
    showLeaderboard = true;
    nameInput.value = ""; // Clear input
}

// Generate pipes
function spawnPipe() {
    const pipeHeight = Math.floor(Math.random() * (gameCanvas.height - pipeGap - 150 * scale)) + 75 * scale;
    pipes.push({
        x: gameCanvas.width,
        topHeight: pipeHeight,
        passed: false
    });
}

// Update leaderboard with name
function updateLeaderboard() {
    leaderboard.push({ name: playerName, score: score });
    leaderboard.sort((a, b) => b.score - a.score); // Sort descending
    leaderboard = leaderboard.slice(0, maxLeaderboardEntries); // Keep top 5
    localStorage.setItem("nyanLeaderboard", JSON.stringify(leaderboard));
}

// Reset game
function resetGame() {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    nyanX = 100 * scale;
    nyanY = gameCanvas.height / 2 - displayHeight / 2;
    velocity = 0;
    pipes = [];
    if (score > highScore) highScore = score;
    score = 0;
    gameOver = false;
    gameStarted = false;
    pipeSpawnTimer = 0;
    crashTime = 0;
    frameIndex = 0;
    frameTimer = 0;
    tiltAngle = 0;
    playerName = "";
    update();
}

// Draw rainbow pipe with rounded ends
function drawRainbowPipe(x, topHeight) {
    const colors = ["#FF0000", "#FF7F00", "#FFFF00", "#00FF00", "#0000FF", "#4B0082", "#8B00FF"];
    const stripeHeightTop = Math.ceil(topHeight / colors.length) + 2 * scale;
    const bottomY = topHeight + pipeGap;
    const bottomHeight = Math.ceil((gameCanvas.height - bottomY) / colors.length) + 2 * scale;
    const radius = pipeWidth / 2;

    for (let i = 0; i < colors.length; i++) {
        gameCtx.fillStyle = colors[i];
        gameCtx.fillRect(Math.floor(x), Math.floor(i * stripeHeightTop), pipeWidth, stripeHeightTop);
    }
    gameCtx.fillStyle = colors[0];
    gameCtx.beginPath();
    gameCtx.arc(Math.floor(x) + radius, 0, radius, Math.PI, 0);
    gameCtx.fill();

    for (let i = 0; i < colors.length; i++) {
        gameCtx.fillStyle = colors[colors.length - 1 - i];
        gameCtx.fillRect(Math.floor(x), Math.floor(bottomY + (i * bottomHeight)), pipeWidth, bottomHeight);
    }
    gameCtx.fillStyle = colors[colors.length - 1];
    gameCtx.beginPath();
    gameCtx.arc(Math.floor(x) + radius, gameCanvas.height, radius, 0, Math.PI);
    gameCtx.fill();
}

// Draw text with black border (modified to accept color)
function drawTextWithBorder(text, x, y, fontSize, color = "#FFFFFF") {
    gameCtx.font = `${fontSize * scale}px 'Pixelify Sans'`;
    gameCtx.lineWidth = 10;
    gameCtx.strokeStyle = "#000000";
    gameCtx.fillStyle = color;
    gameCtx.strokeText(text, x * scale, y * scale);
    gameCtx.fillText(text, x * scale, y * scale);
}

// Draw pulsating stars
function drawStars(ctx, stars, canvasWidth, canvasHeight) {
    ctx.fillStyle = "#FFFFFF";
    const time = Date.now();
    stars.forEach(star => {
        const twinkle = Math.sin((time / speed) * Math.PI * 2 + star.phase) * 0.5 + 0.5;
        const sizeFactor = 0.5 + twinkle * 0.5;
        const ps = Math.floor(star.baseSize * sizeFactor * (canvasWidth === gameCanvas.width ? scale : 1) / 7);
        const centerX = star.x;
        const centerY = star.y;
        const offset = ps * 3;

        for (let y = 0; y < 7; y++) {
            for (let x = 0; x < 7; x++) {
                if (star.style[y][x] === 1) {
                    ctx.fillRect(
                        centerX - offset + x * ps,
                        centerY - offset + y * ps,
                        ps,
                        ps
                    );
                }
            }
        }
    });
}

// Draw leaderboard (centered, with names)
function drawLeaderboard() {
    const boxWidth = 300 * scale;
    const boxHeight = 400 * scale;
    const boxX = (gameCanvas.width - boxWidth) / 2;
    const boxY = (gameCanvas.height - boxHeight) / 2;

    gameCtx.fillStyle = "rgba(0, 51, 102, 0.8)";
    gameCtx.fillRect(boxX, boxY, boxWidth, boxHeight);

    const titleText = "Leaderboard";
    gameCtx.font = `${32 * scale}px 'Pixelify Sans'`;
    const titleWidth = gameCtx.measureText(titleText).width;
    const titleX = boxX + (boxWidth - titleWidth) / 2;
    drawTextWithBorder(titleText, titleX / scale, (boxY / scale) + 50, 32);

    leaderboard.forEach((entry, index) => {
        const nameText = `${index + 1}. ${entry.name}`;
        const scoreText = `Score: ${entry.score}`;
        gameCtx.font = `${22 * scale}px 'Pixelify Sans'`; // Larger name font
        const nameWidth = gameCtx.measureText(nameText).width;
        const nameX = boxX + (boxWidth - nameWidth) / 2;
        drawTextWithBorder(nameText, nameX / scale, (boxY / scale) + 100 + index * 50, 22, "#fd98fd");

        gameCtx.font = `${18 * scale}px 'Pixelify Sans'`;
        const scoreWidth = gameCtx.measureText(scoreText).width;
        const scoreX = boxX + (boxWidth - scoreWidth) / 2;
        drawTextWithBorder(scoreText, scoreX / scale, (boxY / scale) + 125 + index * 50, 18);
    });

    const promptText = "Space/Click for Menu";
    gameCtx.font = `${18 * scale}px 'Pixelify Sans'`;
    const promptWidth = gameCtx.measureText(promptText).width;
    const promptX = boxX + (boxWidth - promptWidth) / 2;
    drawTextWithBorder(promptText, promptX / scale, (boxY / scale) + 370, 18);
}

// Game loop
function update() {
    if (!gameCtx || !bgCtx) {
        console.log("ERROR: Canvas context not initialized!");
        return;
    }

    bgCtx.fillStyle = "#003366";
    bgCtx.fillRect(0, 0, bgCanvas.width, bgCanvas.height);
    gameCtx.fillStyle = "#003366";
    gameCtx.fillRect(0, 0, gameCanvas.width, gameCanvas.height);

    drawStars(bgCtx, bgStars, bgCanvas.width, bgCanvas.height);
    drawStars(gameCtx, gameStars, gameCanvas.width, gameCanvas.height);

    if (showLeaderboard) {
        drawLeaderboard();
    } else if (showNamePrompt) {
        // Name prompt handled by HTML overlay, just pause game rendering
    } else if (!gameStarted) {
        nyanY = gameCanvas.height / 2 - displayHeight / 2 + Math.sin(Date.now() * 0.002) * 10 * scale;
    } else if (gameOver) {
        velocity += gravity;
        nyanY += velocity;
        if (nyanY >= gameCanvas.height - displayHeight) {
            nyanY = gameCanvas.height - displayHeight;
            velocity = 0;
        }
        tiltAngle = Math.min(tiltAngle + 0.05, Math.PI / 4);
    } else {
        velocity += gravity;
        nyanY += velocity;
        if (nyanY >= gameCanvas.height - displayHeight) {
            nyanY = gameCanvas.height - displayHeight;
            gameOver = true;
            crashTime = Date.now();
        }
        if (nyanY < 0) nyanY = 0;

        pipeSpawnTimer++;
        if (pipeSpawnTimer >= pipeSpawnInterval) {
            spawnPipe();
            pipeSpawnTimer = 0;
        }
    }

    if (!showLeaderboard && !showNamePrompt) {
        pipes.forEach((pipe) => {
            if (!gameOver && gameStarted) pipe.x -= pipeSpeed;
            drawRainbowPipe(pipe.x, pipe.topHeight);
        });

        const pipesToRemove = [];
        pipes.forEach((pipe, index) => {
            if (
                !gameOver &&
                gameStarted &&
                nyanX + displayWidth > pipe.x &&
                nyanX < pipe.x + pipeWidth &&
                (nyanY < pipe.topHeight || nyanY + displayHeight > pipe.topHeight + pipeGap)
            ) {
                gameOver = true;
                crashTime = Date.now();
            }
            if (pipe.x + pipeWidth < nyanX && !pipe.passed) {
                score++;
                pipe.passed = true;
            }
            if (pipe.x < -pipeWidth) {
                pipesToRemove.push(index);
            }
        });

        for (let i = pipesToRemove.length - 1; i >= 0; i--) {
            pipes.splice(pipesToRemove[i], 1);
        }

        if (nyanCatImg.complete && nyanCatImg.naturalWidth !== 0) {
            if (!gameOver) {
                frameTimer++;
                if (frameTimer >= frameDelay) {
                    frameIndex = (frameIndex + 1) % totalFrames;
                    frameTimer = 0;
                }
            }
            gameCtx.save();
            gameCtx.translate(nyanX + displayWidth / 2, nyanY + displayHeight / 2);
            gameCtx.rotate(tiltAngle);
            gameCtx.drawImage(
                nyanCatImg,
                frameIndex * frameWidth,
                0,
                frameWidth,
                frameHeight,
                -displayWidth / 2,
                -displayHeight / 2,
                displayWidth,
                displayHeight
            );
            gameCtx.restore();
        } else {
            gameCtx.fillStyle = "#FF00FF";
            gameCtx.fillRect(nyanX, nyanY, displayWidth, displayHeight);
        }

        drawTextWithBorder("Score: " + score, 10, 30, 24);
        drawTextWithBorder("High Score: " + highScore, 10, 60, 24);
        drawTextWithBorder("Best Score: " + (leaderboard[0]?.score || 0), 10, 90, 24, "#fd98fd");

        if (gameOver) {
            const gameOverText = "Game Over!";
            gameCtx.font = `${32 * scale}px 'Pixelify Sans'`;
            const gameOverWidth = gameCtx.measureText(gameOverText).width;
            const gameOverX = (gameCanvas.width - gameOverWidth) / 2 / scale;
            drawTextWithBorder(gameOverText, gameOverX, 280, 32);

            const leaderboardPrompt = "Space/Click for Leaderboard";
            gameCtx.font = `${18 * scale}px 'Pixelify Sans'`;
            const leaderboardWidth = gameCtx.measureText(leaderboardPrompt).width;
            const leaderboardX = (gameCanvas.width - leaderboardWidth) / 2 / scale;
            drawTextWithBorder(leaderboardPrompt, leaderboardX, 310, 18);

            const restartPrompt = "Esc for Restart";
            const restartWidth = gameCtx.measureText(restartPrompt).width;
            const restartX = (gameCanvas.width - restartWidth) / 2 / scale;
            drawTextWithBorder(restartPrompt, restartX, 340, 18);
        } else if (!gameStarted) {
            const startText = "Space/Click to Start";
            gameCtx.font = `${18 * scale}px 'Pixelify Sans'`;
            const startWidth = gameCtx.measureText(startText).width;
            const startX = (gameCanvas.width - startWidth) / 2 / scale;
            drawTextWithBorder(startText, startX, 400, 18);

            const leaderboardText = "Press Enter for Leaderboard";
            const leaderboardWidth = gameCtx.measureText(leaderboardText).width;
            const leaderboardX = (gameCanvas.width - leaderboardWidth) / 2 / scale;
            drawTextWithBorder(leaderboardText, leaderboardX, 430, 18);
        }
    }

    animationFrameId = requestAnimationFrame(update);
}

// Start game loop
console.log("Game starting...");
update();