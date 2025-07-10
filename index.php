<?php
session_start();

// Rate limiting - max 10 requests per IP per minute
$ip = $_SERVER['REMOTE_ADDR'];
$current_time = time();
$minute_key = floor($current_time / 60);

if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = [];
}

if (!isset($_SESSION['rate_limit'][$ip])) {
    $_SESSION['rate_limit'][$ip] = [];
}

if (!isset($_SESSION['rate_limit'][$ip][$minute_key])) {
    $_SESSION['rate_limit'][$ip][$minute_key] = 0;
}

// Clean old entries
foreach ($_SESSION['rate_limit'][$ip] as $key => $count) {
    if ($key < $minute_key - 1) {
        unset($_SESSION['rate_limit'][$ip][$key]);
    }
}

// Check rate limit
if ($_SESSION['rate_limit'][$ip][$minute_key] >= 10) {
    http_response_code(429);
    die(json_encode(['error' => 'Rate limit exceeded. Try again later.']));
}

// Anti-scraping: Generate new session token every minute
$token_key = 'token_' . $minute_key;
if (!isset($_SESSION[$token_key])) {
    $_SESSION[$token_key] = bin2hex(random_bytes(32));
}

$current_token = $_SESSION[$token_key];

// Handle AJAX requests
if (isset($_POST['action']) && $_POST['action'] === 'osint_search') {
    // Verify token
    if (!isset($_POST['token']) || $_POST['token'] !== $current_token) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid token']));
    }
    
    $_SESSION['rate_limit'][$ip][$minute_key]++;
    
    $type = $_POST['type'] ?? '';
    $query = $_POST['query'] ?? '';
    
    if (empty($type) || empty($query)) {
        die(json_encode(['error' => 'Missing parameters']));
    }
    
    // Sanitize input
    $query = filter_var($query, FILTER_SANITIZE_STRING);
    
    // API endpoints
    $endpoints = [
        'phone' => 'https://glonova.in/osint.php/?phone=',
        'username' => 'https://glonova.in/osint.php/?username=',
        'domain' => 'https://glonova.in/osint.php/?domin=',
        'email' => 'https://glonova.in/osint.php/?email='
    ];
    
    if (!isset($endpoints[$type])) {
        die(json_encode(['error' => 'Invalid search type']));
    }
    
    $url = $endpoints[$type] . urlencode($query);
    
    // Make API request with timeout and user agent rotation
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
    ];
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => $user_agents[array_rand($user_agents)]
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        die(json_encode(['error' => 'API request failed']));
    }
    
    $data = json_decode($response, true);
    
    // Remove credits from JSON response
    if (is_array($data)) {
        removeCredits($data);
    }
    
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function removeCredits(&$array) {
    if (!is_array($array)) return;
    
    foreach ($array as $key => &$value) {
        if (is_string($key) && (
            stripos($key, 'credit') !== false ||
            stripos($key, 'credits') !== false ||
            stripos($key, 'author') !== false ||
            stripos($key, 'developer') !== false
        )) {
            unset($array[$key]);
        } elseif (is_array($value)) {
            removeCredits($value);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGYT777GG INFO - OSINT Search</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="matrix-bg"></div>
    
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <div class="logo">NGYT777GG</div>
                <div class="logo-subtitle">INFO</div>
            </div>
            
            <nav class="navigation">
                <div class="nav-dropdown">
                    <button class="nav-toggle" id="navToggle">
                        <span>OSINT TOOLS</span>
                        <div class="arrow">▼</div>
                    </button>
                    <div class="nav-menu" id="navMenu">
                        <a href="#" class="nav-item" data-type="email">📧 Email Search</a>
                        <a href="#" class="nav-item" data-type="phone">📱 Phone Search</a>
                        <a href="#" class="nav-item" data-type="username">👤 Username Search</a>
                        <a href="#" class="nav-item" data-type="domain">🌐 Domain Search</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="search-section">
                <div class="search-container">
                    <h1 class="main-title">OSINT INFORMATION GATHERING</h1>
                    
                    <div class="search-box">
                        <div class="search-type-selector">
                            <button class="type-btn active" data-type="email">📧 EMAIL</button>
                            <button class="type-btn" data-type="phone">📱 PHONE</button>
                            <button class="type-btn" data-type="username">👤 USERNAME</button>
                            <button class="type-btn" data-type="domain">🌐 DOMAIN</button>
                        </div>
                        
                        <div class="input-group">
                            <input type="text" id="searchInput" class="search-input" placeholder="Enter email address...">
                            <button class="search-btn" id="searchBtn">
                                <span class="btn-text">SEARCH</span>
                                <div class="btn-glow"></div>
                            </button>
                        </div>
                    </div>
                    
                    <div class="loading" id="loading">
                        <div class="loading-text">SCANNING...</div>
                        <div class="loading-bar">
                            <div class="loading-fill"></div>
                        </div>
                    </div>
                    
                    <div class="results-container" id="resultsContainer">
                        <div class="results-header">
                            <h3>SEARCH RESULTS</h3>
                        </div>
                        <div class="results-content" id="resultsContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p class="disclaimer">⚠️ Educational Purpose Only - Use Responsibly</p>
                <a href="https://t.me/+FNstNY_ooV1lYzdl" target="_blank" class="join-btn">
                    <span class="btn-icon">📢</span>
                    <span class="btn-text">JOIN OUR CHANNEL</span>
                    <div class="btn-glow"></div>
                </a>
            </div>
        </div>
    </footer>

    <script>
        const searchToken = '<?php echo $current_token; ?>';
    </script>
    <script src="script.js"></script>
</body>
</html>