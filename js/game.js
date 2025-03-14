// game.js

const gameCanvas = document.getElementById("gameCanvas");
const gameCtx = gameCanvas ? gameCanvas.getContext("2d") : null;
const bgCanvas = document.getElementById("backgroundCanvas");
const bgCtx = bgCanvas ? bgCanvas.getContext("2d") : null;

if (!gameCtx || !bgCtx) {
    console.error("Canvas context not initialized. Check canvas IDs in index.html.");
} else {
    gameCanvas.style.imageRendering = "pixelated";
    gameCtx.imageSmoothingEnabled = false;
    bgCanvas.style.imageRendering = "pixelated";
    bgCtx.imageSmoothingEnabled = false;
}

const scale = 2;
const speed = 1000;
let lastTime = performance.now();

let nyanX = 100 * scale;
let nyanY = gameCanvas ? (gameCanvas.height / 2 - 20) * scale : 0;
let velocity = 0;
const gravity = 0.4 * scale;
const jump = -7 * scale;
let frameIndex = 0;
let frameTimer = 0;
const frameDelay = 2;
let tiltAngle = 0;

const frameWidth = 74;
const frameHeight = 42;
const displayWidth = frameWidth * 1.2 * scale;
const displayHeight = frameHeight * 1.2 * scale;
const totalFrames = 12;

let pipes = [];
const pipeWidth = 60 * scale;
const pipeGap = 200 * scale;
let pipeSpeed = 2.5 * scale;
let pipeSpawnTimer = 0;
const pipeSpawnInterval = 100;

let score = 0;
let highScore = parseInt(localStorage.getItem('nyanHighScore') || '0');
let gameOver = false;
let gameStarted = false;
let crashTime = 0;
const restartDelay = 500;
let animationFrameId = null;
let showLeaderboard = false;
let showNamePrompt = false;
let playerName = "";

let leaderboard = []; // Initialize empty, will fetch from server
const maxLeaderboardEntries = 5;

// Fetch initial leaderboard from server
fetch('/nyan/get_leaderboard.php')
    .then(response => response.json())
    .then(data => leaderboard = data)
    .catch(error => console.error('Error fetching initial leaderboard:', error));

const nyanCatImg = new Image();
nyanCatImg.src = "assets/nyan-sprite.png";
nyanCatImg.onerror = () => console.error("Failed to load nyan-sprite.png");

const namePrompt = document.getElementById("namePrompt");
const nameInput = document.getElementById("nameInput");
const submitNameBtn = document.getElementById("submitName");

const starStyles = [
    [[0,0,1,1,1,0,0],[0,0,1,1,1,0,0],[1,1,1,1,1,1,1],[1,1,1,1,1,1,1],[1,1,1,1,1,1,1],[0,0,1,1,1,0,0],[0,0,1,1,1,0,0]],
    [[0,0,0,1,0,0,0],[0,0,0,1,0,0,0],[0,0,1,1,1,0,0],[1,1,1,1,1,1,1],[0,0,1,1,1,0,0],[0,0,0,1,0,0,0],[0,0,0,1,0,0,0]],
    [[0,0,1,1,1,0,0],[0,1,0,0,0,1,0],[1,0,0,0,0,0,1],[1,0,0,0,0,0,1],[1,0,0,0,0,0,1],[0,1,0,0,0,1,0],[0,0,1,1,1,0,0]]
];

function resizeBackgroundCanvas() {
    if (bgCanvas) {
        bgCanvas.width = window.innerWidth;
        bgCanvas.height = window.innerHeight;
        bgStars.length = 0;
        const starDensity = 0.00005;
        const starCount = Math.floor(bgCanvas.width * bgCanvas.height * starDensity);
        for (let i = 0; i < starCount; i++) {
            const size = Math.floor(Math.random() * 11) + 5;
            bgStars.push({
                x: Math.floor(Math.random() * bgCanvas.width),
                y: Math.floor(Math.random() * bgCanvas.height),
                baseSize: size,
                style: starStyles[Math.floor(Math.random() * starStyles.length)],
                phase: Math.random() * Math.PI * 2
            });
        }
    }
}

const bgStars = [];
resizeBackgroundCanvas();
window.addEventListener('resize', resizeBackgroundCanvas);

const gameStars = [];
if (gameCanvas) {
    for (let i = 0; i < 6; i++) {
        const size = Math.floor(Math.random() * 11) + 5;
        gameStars.push({
            x: Math.floor(Math.random() * gameCanvas.width),
            y: Math.floor(Math.random() * gameCanvas.height),
            baseSize: size,
            style: starStyles[Math.floor(Math.random() * starStyles.length)],
            phase: Math.random() * Math.PI * 2
        });
    }
}

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
            } else if (gameOver && !showNamePrompt && namePrompt) {
                showNamePrompt = true;
                namePrompt.style.display = "block";
                nameInput?.focus();
            }
        }
    } else if (e.code === "Enter") {
        e.preventDefault();
        if (!gameStarted && !showLeaderboard && !showNamePrompt) {
            showLeaderboard = true;
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

gameCanvas?.addEventListener("click", () => {
    if (!gameStarted && !showLeaderboard && !showNamePrompt) {
        gameStarted = true;
    } else if (!gameOver && !showLeaderboard && !showNamePrompt) {
        velocity = jump;
    } else if (Date.now() - crashTime > restartDelay) {
        if (showLeaderboard) {
            showLeaderboard = false;
            resetGame();
        } else if (gameOver && !showNamePrompt && namePrompt) {
            showNamePrompt = true;
            namePrompt.style.display = "block";
            nameInput?.focus();
        }
    }
});

submitNameBtn?.addEventListener("click", submitName);
function submitName() {
    playerName = nameInput?.value.trim() || "Nyanonymous";
    if (namePrompt) namePrompt.style.display = "none";
    showNamePrompt = false;
    updateLeaderboard();
    showLeaderboard = true;
    if (nameInput) nameInput.value = "";
}

function spawnPipe() {
    const pipeHeight = Math.floor(Math.random() * (gameCanvas.height - pipeGap - 150 * scale)) + 75 * scale;
    pipes.push({
        x: gameCanvas.width,
        topHeight: pipeHeight,
        passed: false
    });
}

function updateLeaderboard() {
    const newEntry = { name: playerName, score: score };
    fetch('/nyan/save_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: playerName, score: score })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh leaderboard from server
            fetch('/nyan/get_leaderboard.php')
                .then(response => response.json())
                .then(data => {
                    leaderboard = data;
                    showLeaderboard = true;
                    showNamePrompt = false;
                    if (namePrompt) namePrompt.style.display = "none";
                })
                .catch(error => console.error('Error refreshing leaderboard:', error));
        } else {
            console.error('Failed to save score:', data.error);
        }
    })
    .catch(error => console.error('Error saving score:', error));
}

function resetGame() {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    nyanX = 100 * scale;
    nyanY = gameCanvas ? gameCanvas.height / 2 - displayHeight / 2 : 0;
    velocity = 0;
    pipes = [];
    if (score > highScore) {
        highScore = score;
        localStorage.setItem('nyanHighScore', highScore.toString());
    }
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

function drawTextWithBorder(text, x, y, fontSize, color = "#FFFFFF") {
    if (gameCtx) {
        gameCtx.font = `${fontSize * scale}px 'Pixelify Sans'`;
        gameCtx.lineWidth = 10;
        gameCtx.strokeStyle = "#000000";
        gameCtx.fillStyle = color;
        gameCtx.strokeText(text, x * scale, y * scale);
        gameCtx.fillText(text, x * scale, y * scale);
    }
}

function drawStars(ctx, stars, canvasWidth, canvasHeight) {
    if (ctx) {
        ctx.fillStyle = "#FFFFFF";
        const time = Date.now();
        stars.forEach(star => {
            const twinkle = Math.sin((time / speed) * Math.PI * 2 + star.phase) * 0.5 + 0.5;
            const sizeFactor = 0.5 + twinkle * 0.5;
            const ps = Math.floor(star.baseSize * sizeFactor * (canvasWidth === gameCanvas?.width ? scale : 1) / 7);
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
}

function drawLeaderboard() {
    if (!gameCtx) return;
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
        gameCtx.font = `${22 * scale}px 'Pixelify Sans'`;
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

function update() {
    if (!gameCtx || !bgCtx) {
        console.log("ERROR: Canvas context not initialized!");
        return;
    }

    const now = performance.now();
    const deltaTime = (now - lastTime) / 16.67;
    lastTime = now;

    bgCtx.fillStyle = "#003366";
    bgCtx.fillRect(0, 0, bgCanvas.width, bgCanvas.height);
    gameCtx.fillStyle = "#003366";
    gameCtx.fillRect(0, 0, gameCanvas.width, gameCanvas.height);

    drawStars(bgCtx, bgStars, bgCanvas.width, bgCanvas.height);
    drawStars(gameCtx, gameStars, gameCanvas.width, gameCanvas.height);

    if (showLeaderboard) {
        drawLeaderboard();
    } else if (showNamePrompt) {
        // Name prompt handled by HTML
    } else if (!gameStarted) {
        nyanY = gameCanvas.height / 2 - displayHeight / 2 + Math.sin(Date.now() * 0.002) * 10 * scale;
    } else if (gameOver) {
        velocity += gravity * deltaTime;
        nyanY += velocity * deltaTime;
        if (nyanY >= gameCanvas.height - displayHeight) {
            nyanY = gameCanvas.height - displayHeight;
            velocity = 0;
        }
        tiltAngle = Math.min(tiltAngle + 0.05 * deltaTime, Math.PI / 4);
    } else {
        velocity += gravity * deltaTime;
        nyanY += velocity * deltaTime;
        if (nyanY >= gameCanvas.height - displayHeight) {
            nyanY = gameCanvas.height - displayHeight;
            gameOver = true;
            crashTime = Date.now();
        }
        if (nyanY < 0) nyanY = 0;

        pipeSpawnTimer += deltaTime;
        if (pipeSpawnTimer >= pipeSpawnInterval) {
            spawnPipe();
            pipeSpawnTimer = 0;
        }
    }

    if (!showLeaderboard && !showNamePrompt) {
        pipes.forEach((pipe) => {
            if (!gameOver && gameStarted) pipe.x -= pipeSpeed * deltaTime;
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
                frameTimer += deltaTime;
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

console.log("Game starting...");
update();