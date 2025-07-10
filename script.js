// Global variables
let currentSearchType = 'email';
let isSearching = false;

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Main initialization function
function initializeApp() {
    initializeNavigation();
    initializeSearchInterface();
    initializeAnimations();
    updateRateLimitDisplay();
}

// Navigation functionality
function initializeNavigation() {
    const navToggle = document.getElementById('navToggle');
    const navDropdown = navToggle.closest('.nav-dropdown');
    const navItems = document.querySelectorAll('.nav-item');
    
    // Toggle navigation dropdown
    navToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navDropdown.classList.toggle('active');
    });
    
    // Handle navigation item clicks
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const searchType = this.getAttribute('data-type');
            setSearchType(searchType);
            navDropdown.classList.remove('active');
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        navDropdown.classList.remove('active');
    });
}

// Search interface functionality
function initializeSearchInterface() {
    const typeButtons = document.querySelectorAll('.type-btn');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    // Search type button handlers
    typeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const searchType = this.getAttribute('data-type');
            setSearchType(searchType);
        });
    });
    
    // Search input handlers
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !isSearching) {
            performSearch();
        }
    });
    
    searchInput.addEventListener('input', function() {
        updateSearchButton();
    });
    
    // Search button handler
    searchBtn.addEventListener('click', function() {
        if (!isSearching) {
            performSearch();
        }
    });
    
    // Initialize with email type
    setSearchType('email');
}

// Set search type and update UI
function setSearchType(type) {
    currentSearchType = type;
    
    // Update type buttons
    document.querySelectorAll('.type-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-type') === type) {
            btn.classList.add('active');
        }
    });
    
    // Update input placeholder
    const searchInput = document.getElementById('searchInput');
    const placeholders = {
        email: 'Enter email address...',
        phone: 'Enter phone number...',
        username: 'Enter username...',
        domain: 'Enter domain name...'
    };
    
    searchInput.placeholder = placeholders[type] || 'Enter search query...';
    searchInput.focus();
    
    // Clear previous results
    hideResults();
    updateSearchButton();
}

// Update search button state
function updateSearchButton() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const btnText = searchBtn.querySelector('.btn-text');
    
    if (isSearching) {
        btnText.textContent = 'SEARCHING...';
        searchBtn.disabled = true;
        searchBtn.style.opacity = '0.6';
    } else {
        btnText.textContent = 'SEARCH';
        searchBtn.disabled = false;
        searchBtn.style.opacity = '1';
    }
}

// Perform OSINT search
async function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.trim();
    
    if (!query) {
        showError('Please enter a search query');
        return;
    }
    
    if (isSearching) {
        return;
    }
    
    // Validate input based on type
    if (!validateInput(query, currentSearchType)) {
        showError(`Please enter a valid ${currentSearchType}`);
        return;
    }
    
    // Check rate limit
    if (window.rateLimitRemaining <= 0) {
        showError('Rate limit exceeded. Please wait a minute before searching again.');
        return;
    }
    
    isSearching = true;
    showLoading();
    hideResults();
    updateSearchButton();
    
    try {
        const response = await fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'osint_search',
                type: currentSearchType,
                query: query,
                token: window.searchToken
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Search failed');
        }
        
        // Update rate limit counter
        window.rateLimitRemaining--;
        updateRateLimitDisplay();
        
        // Display results
        displayResults(data, query);
        
    } catch (error) {
        console.error('Search error:', error);
        showError(error.message || 'Search failed. Please try again.');
    } finally {
        isSearching = false;
        hideLoading();
        updateSearchButton();
    }
}

// Validate input based on search type
function validateInput(input, type) {
    const patterns = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^[\+]?[1-9][\d]{7,14}$/,
        username: /^[a-zA-Z0-9_.-]{3,30}$/,
        domain: /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/
    };
    
    return patterns[type] ? patterns[type].test(input) : true;
}

// Show loading animation
function showLoading() {
    const loading = document.getElementById('loading');
    loading.classList.add('show');
    
    // Add typing effect to loading text
    const loadingText = loading.querySelector('.loading-text');
    const messages = [
        'SCANNING TARGET...',
        'GATHERING INTELLIGENCE...',
        'PROCESSING DATA...',
        'ANALYZING RESULTS...',
        'FINALIZING REPORT...'
    ];
    
    let messageIndex = 0;
    const loadingInterval = setInterval(() => {
        if (!isSearching) {
            clearInterval(loadingInterval);
            return;
        }
        
        loadingText.textContent = messages[messageIndex];
        messageIndex = (messageIndex + 1) % messages.length;
    }, 1500);
}

// Hide loading animation
function hideLoading() {
    const loading = document.getElementById('loading');
    loading.classList.remove('show');
}

// Display search results
function displayResults(data, query) {
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsContent = document.getElementById('resultsContent');
    const resultsStatus = document.getElementById('resultsStatus');
    
    resultsContainer.classList.add('show');
    
    // Set status
    resultsStatus.textContent = `Query: ${query} | Type: ${currentSearchType.toUpperCase()}`;
    
    // Clear previous results
    resultsContent.innerHTML = '';
    
    if (data.error) {
        showError(data.error);
        return;
    }
    
    // Check if data is empty or no results
    if (!data || typeof data !== 'object' || Object.keys(data).length === 0) {
        resultsContent.innerHTML = `
            <div class="result-item">
                <div class="result-key">No Data Found</div>
                <div class="result-value">No information available for this ${currentSearchType}</div>
            </div>
        `;
        return;
    }
    
    // Display results recursively
    displayResultsRecursive(data, resultsContent);
    
    // Add animation to result items
    setTimeout(() => {
        const resultItems = resultsContent.querySelectorAll('.result-item');
        resultItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('fade-in');
            }, index * 100);
        });
    }, 100);
}

// Recursively display results
function displayResultsRecursive(obj, container, prefix = '') {
    for (const [key, value] of Object.entries(obj)) {
        const resultItem = document.createElement('div');
        resultItem.className = 'result-item';
        
        const resultKey = document.createElement('div');
        resultKey.className = 'result-key';
        resultKey.textContent = prefix + formatKey(key);
        
        const resultValue = document.createElement('div');
        resultValue.className = 'result-value';
        
        if (typeof value === 'object' && value !== null) {
            if (Array.isArray(value)) {
                resultValue.textContent = value.length > 0 ? value.join(', ') : 'No data';
            } else {
                // For nested objects, create a sub-container
                const subContainer = document.createElement('div');
                subContainer.style.marginLeft = '15px';
                subContainer.style.marginTop = '10px';
                displayResultsRecursive(value, subContainer, '→ ');
                resultValue.appendChild(subContainer);
            }
        } else {
            resultValue.textContent = value || 'No data';
            
            // Make URLs clickable
            if (typeof value === 'string' && (value.startsWith('http://') || value.startsWith('https://'))) {
                const link = document.createElement('a');
                link.href = value;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = value;
                link.style.color = '#00ff00';
                link.style.textDecoration = 'underline';
                resultValue.innerHTML = '';
                resultValue.appendChild(link);
            }
        }
        
        resultItem.appendChild(resultKey);
        resultItem.appendChild(resultValue);
        container.appendChild(resultItem);
    }
}

// Format key names for display
function formatKey(key) {
    return key
        .replace(/_/g, ' ')
        .replace(/([A-Z])/g, ' $1')
        .replace(/^./, str => str.toUpperCase())
        .trim();
}

// Show error message
function showError(message) {
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsContent = document.getElementById('resultsContent');
    const resultsStatus = document.getElementById('resultsStatus');
    
    resultsContainer.classList.add('show');
    resultsStatus.textContent = 'Error occurred';
    
    resultsContent.innerHTML = `
        <div class="error-message">
            <strong>⚠️ ERROR:</strong> ${message}
        </div>
    `;
}

// Hide results
function hideResults() {
    const resultsContainer = document.getElementById('resultsContainer');
    resultsContainer.classList.remove('show');
}

// Update rate limit display
function updateRateLimitDisplay() {
    const rateLimitDisplay = document.querySelector('.rate-limit-display');
    if (rateLimitDisplay) {
        rateLimitDisplay.textContent = `Searches remaining: ${window.rateLimitRemaining}`;
    }
}

// Initialize animations and visual effects
function initializeAnimations() {
    // Matrix background effect
    createMatrixEffect();
    
    // Add entrance animations
    setTimeout(() => {
        document.querySelector('.header').classList.add('slide-in-left');
        document.querySelector('.main-content').classList.add('fade-in');
        document.querySelector('.footer').classList.add('fade-in');
    }, 300);
}

// Create matrix-like background effect
function createMatrixEffect() {
    const matrixBg = document.getElementById('matrixBg');
    
    // Create floating particles
    for (let i = 0; i < 20; i++) {
        setTimeout(() => {
            createMatrixParticle(matrixBg);
        }, i * 200);
    }
    
    // Continuously create new particles
    setInterval(() => {
        if (Math.random() < 0.3) {
            createMatrixParticle(matrixBg);
        }
    }, 1000);
}

// Create individual matrix particle
function createMatrixParticle(container) {
    const particle = document.createElement('div');
    particle.style.position = 'absolute';
    particle.style.color = '#00ff00';
    particle.style.fontSize = Math.random() * 20 + 10 + 'px';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.top = '-50px';
    particle.style.opacity = Math.random() * 0.7 + 0.3;
    particle.style.zIndex = '-1';
    particle.style.fontFamily = 'monospace';
    particle.style.pointerEvents = 'none';
    
    // Random matrix characters
    const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
    particle.textContent = chars[Math.floor(Math.random() * chars.length)];
    
    container.appendChild(particle);
    
    // Animate particle
    const duration = Math.random() * 10000 + 5000;
    const animation = particle.animate([
        { transform: 'translateY(-50px)', opacity: 0 },
        { transform: 'translateY(50px)', opacity: 1 },
        { transform: `translateY(${window.innerHeight + 50}px)`, opacity: 0 }
    ], {
        duration: duration,
        easing: 'linear'
    });
    
    animation.onfinish = () => {
        particle.remove();
    };
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // ESC to close dropdown
    if (e.key === 'Escape') {
        const navDropdown = document.querySelector('.nav-dropdown');
        navDropdown.classList.remove('active');
        hideResults();
    }
    
    // Ctrl+F to focus search
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
    }
    
    // Number keys to select search type
    const typeKeys = ['1', '2', '3', '4'];
    const types = ['email', 'phone', 'username', 'domain'];
    
    if (typeKeys.includes(e.key)) {
        const index = typeKeys.indexOf(e.key);
        setSearchType(types[index]);
    }
});

// Handle window resize for responsive animations
window.addEventListener('resize', function() {
    // Adjust animations for mobile
    const isMobile = window.innerWidth <= 768;
    document.body.setAttribute('data-mobile', isMobile);
});

// Add copy functionality for results
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('result-value')) {
        const text = e.target.textContent;
        if (text && text !== 'No data') {
            navigator.clipboard.writeText(text).then(() => {
                // Show copy feedback
                const originalText = e.target.textContent;
                e.target.textContent = '✓ Copied!';
                e.target.style.color = '#00aa00';
                
                setTimeout(() => {
                    e.target.textContent = originalText;
                    e.target.style.color = '#00ff00';
                }, 1000);
            }).catch(() => {
                console.log('Copy failed');
            });
        }
    }
});

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
        }, 100);
    });
}

// Add visual feedback for form interactions
function addInteractionFeedback() {
    const interactiveElements = document.querySelectorAll('button, input, .nav-item');
    
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
}

// Initialize interaction feedback
addInteractionFeedback();

// Console art for developers
console.log(`
    ███╗   ██╗ ██████╗ ██╗   ██╗████████╗███████╗███████╗███████╗ ██████╗  ██████╗ 
    ████╗  ██║██╔════╝ ╚██╗ ██╔╝╚══██╔══╝╚════██║╚════██║╚════██║██╔════╝ ██╔════╝ 
    ██╔██╗ ██║██║  ███╗ ╚████╔╝    ██║   ███████║███████║███████║██║  ███╗██║  ███╗
    ██║╚██╗██║██║   ██║  ╚██╔╝     ██║   ╚════██║╚════██║╚════██║██║   ██║██║   ██║
    ██║ ╚████║╚██████╔╝   ██║      ██║   ███████║███████║███████║╚██████╔╝╚██████╔╝
    ╚═╝  ╚═══╝ ╚═════╝    ╚═╝      ╚═╝   ╚══════╝╚══════╝╚══════╝ ╚═════╝  ╚═════╝ 
    
    🔍 OSINT Intelligence Platform - Educational Use Only
    ⚡ Built with security and performance in mind
    🛡️ Rate limited and anti-scraping protected
`);