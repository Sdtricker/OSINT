<?php
session_start();

// Rate limiting and security measures
$ip = $_SERVER['REMOTE_ADDR'];
$current_time = time();
$minute_key = floor($current_time / 60);

// Initialize rate limiting
if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = [];
}

if (!isset($_SESSION['rate_limit'][$ip])) {
    $_SESSION['rate_limit'][$ip] = [];
}

if (!isset($_SESSION['rate_limit'][$ip][$minute_key])) {
    $_SESSION['rate_limit'][$ip][$minute_key] = 0;
}

// Clean old rate limit entries
foreach ($_SESSION['rate_limit'][$ip] as $key => $count) {
    if ($key < $minute_key - 1) {
        unset($_SESSION['rate_limit'][$ip][$key]);
    }
}

// Check rate limit (10 requests per minute)
if ($_SESSION['rate_limit'][$ip][$minute_key] >= 10) {
    http_response_code(429);
    die(json_encode(['error' => 'Rate limit exceeded. Try again later.']));
}

// Anti-scraping token (changes every minute)
$token_key = 'token_' . $minute_key;
if (!isset($_SESSION[$token_key])) {
    $_SESSION[$token_key] = bin2hex(random_bytes(32));
}
$current_token = $_SESSION[$token_key];

// Handle OSINT search requests
if (isset($_POST['action']) && $_POST['action'] === 'osint_search') {
    // Verify anti-scraping token
    if (!isset($_POST['token']) || $_POST['token'] !== $current_token) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid security token']));
    }
    
    // Increment rate limit counter
    $_SESSION['rate_limit'][$ip][$minute_key]++;
    
    $type = $_POST['type'] ?? '';
    $query = $_POST['query'] ?? '';
    
    if (empty($type) || empty($query)) {
        die(json_encode(['error' => 'Missing parameters']));
    }
    
    // Sanitize input
    $query = filter_var($query, FILTER_SANITIZE_STRING);
    
    // API endpoints mapping
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
    
    // Rotate user agents to avoid detection
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ];
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => $user_agents[array_rand($user_agents)],
            'header' => "Accept: application/json\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        die(json_encode(['error' => 'API request failed or timed out']));
    }
    
    $data = json_decode($response, true);
    
    // Remove credits and author information from response
    if (is_array($data)) {
        removeCredits($data);
    }
    
    header('Content-Type: application/json');
    echo json_encode($data ?: ['error' => 'Invalid API response']);
    exit;
}

// Function to recursively remove credit information
function removeCredits(&$array) {
    if (!is_array($array)) return;
    
    $credit_keys = ['credit', 'credits', 'author', 'developer', 'created_by', 'powered_by', 'api_by'];
    
    foreach ($array as $key => &$value) {
        if (is_string($key)) {
            foreach ($credit_keys as $credit_key) {
                if (stripos($key, $credit_key) !== false) {
                    unset($array[$key]);
                    continue 2;
                }
            }
        }
        
        if (is_array($value)) {
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
    <title>NGYT777GG INFO - OSINT Intelligence Platform</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <meta name="description" content="Professional OSINT platform for educational purposes">
</head>
<body>
    <!-- Animated background -->
    <div class="matrix-bg" id="matrixBg"></div>
    
    <!-- Header section -->
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <div class="logo">
                    <span class="logo-text">NGYT777GG</span>
                    <div class="logo-glow"></div>
                </div>
                <div class="logo-subtitle">INTELLIGENCE PLATFORM</div>
            </div>
            
            <nav class="navigation">
                <div class="nav-dropdown">
                    <button class="nav-toggle" id="navToggle">
                        <span class="nav-text">🔍 OSINT TOOLS</span>
                        <div class="arrow" id="navArrow">▼</div>
                    </button>
                    <div class="nav-menu" id="navMenu">
                        <a href="#" class="nav-item" data-type="email">
                            <span class="nav-icon">📧</span>
                            <span class="nav-label">EMAIL SEARCH</span>
                        </a>
                        <a href="#" class="nav-item" data-type="phone">
                            <span class="nav-icon">📱</span>
                            <span class="nav-label">PHONE SEARCH</span>
                        </a>
                        <a href="#" class="nav-item" data-type="username">
                            <span class="nav-icon">👤</span>
                            <span class="nav-label">USERNAME SEARCH</span>
                        </a>
                        <a href="#" class="nav-item" data-type="domain">
                            <span class="nav-icon">🌐</span>
                            <span class="nav-label">DOMAIN SEARCH</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main content -->
    <main class="main-content">
        <div class="container">
            <div class="search-section">
                <div class="search-container">
                    <h1 class="main-title">
                        <span class="title-main">OSINT</span>
                        <span class="title-sub">INFORMATION GATHERING</span>
                    </h1>
                    
                    <div class="search-box">
                        <div class="search-type-selector">
                            <button class="type-btn active" data-type="email">
                                <span class="btn-icon">📧</span>
                                <span class="btn-label">EMAIL</span>
                            </button>
                            <button class="type-btn" data-type="phone">
                                <span class="btn-icon">📱</span>
                                <span class="btn-label">PHONE</span>
                            </button>
                            <button class="type-btn" data-type="username">
                                <span class="btn-icon">👤</span>
                                <span class="btn-label">USERNAME</span>
                            </button>
                            <button class="type-btn" data-type="domain">
                                <span class="btn-icon">🌐</span>
                                <span class="btn-label">DOMAIN</span>
                            </button>
                        </div>
                        
                        <div class="input-group">
                            <div class="input-wrapper">
                                <input type="text" id="searchInput" class="search-input" placeholder="Enter email address..." autocomplete="off">
                                <div class="input-glow"></div>
                            </div>
                            <button class="search-btn" id="searchBtn">
                                <span class="btn-text">SEARCH</span>
                                <span class="btn-icon-search">🔍</span>
                                <div class="btn-glow"></div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Loading animation -->
                    <div class="loading" id="loading">
                        <div class="loading-text">SCANNING TARGET...</div>
                        <div class="loading-bar">
                            <div class="loading-fill"></div>
                        </div>
                        <div class="loading-dots">
                            <span>.</span><span>.</span><span>.</span>
                        </div>
                    </div>
                    
                    <!-- Results container -->
                    <div class="results-container" id="resultsContainer">
                        <div class="results-header">
                            <h3>🎯 SEARCH RESULTS</h3>
                            <div class="results-status" id="resultsStatus"></div>
                        </div>
                        <div class="results-content" id="resultsContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer section -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="disclaimer">
                    <span class="warning-icon">⚠️</span>
                    <span class="disclaimer-text">EDUCATIONAL PURPOSE ONLY - USE RESPONSIBLY</span>
                </div>
                <a href="https://t.me/+FNstNY_ooV1lYzdl" target="_blank" class="join-btn" rel="noopener">
                    <span class="btn-icon">📢</span>
                    <span class="btn-text">JOIN OUR CHANNEL</span>
                    <div class="btn-glow"></div>
                    <div class="btn-pulse"></div>
                </a>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        window.searchToken = '<?php echo $current_token; ?>';
        window.rateLimitRemaining = <?php echo 10 - $_SESSION['rate_limit'][$ip][$minute_key]; ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>