# NGYT777GG INFO - OSINT Intelligence Platform

🔍 A professional OSINT (Open Source Intelligence) search platform built with PHP, featuring a Minecraft-inspired UI design for educational purposes.

## ✨ Features

- **Multi-Type OSINT Search**: Email, Phone, Username, and Domain lookups
- **Minecraft-Style UI**: Black background with glowing green text and pixelated design
- **Advanced Security**: Rate limiting, anti-scraping protection, and session tokens
- **Modern Animations**: Matrix-style background effects and smooth transitions
- **Responsive Design**: Works perfectly on desktop and mobile devices
- **Real-time Results**: Fast API integration with clean JSON data display

## 🔧 Technologies Used

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Design**: Orbitron & Space Mono fonts, CSS animations
- **Security**: Session-based rate limiting, CSRF protection

## 🚀 Installation

1. **Clone/Download** the project files to your web server
2. **Ensure PHP 7.4+** is installed with the following extensions:
   - `json`
   - `session`
   - `filter`
3. **Configure your web server** to serve the project directory
4. **Access** `index.php` in your browser

### Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Internet connection for API calls

## 🎮 Usage

1. **Select Search Type**: Choose from Email, Phone, Username, or Domain
2. **Enter Query**: Type your search target in the input field
3. **Click Search**: Wait for the OSINT results to load
4. **View Results**: Browse through the gathered intelligence data

### Keyboard Shortcuts
- `Enter`: Perform search
- `Escape`: Close dropdown/results
- `Ctrl+F`: Focus search input
- `1-4`: Select search type (1=Email, 2=Phone, 3=Username, 4=Domain)

## 🛡️ Security Features

- **Rate Limiting**: 10 requests per IP per minute
- **Anti-Scraping**: Dynamic session tokens that change every minute
- **Input Validation**: Sanitized and validated user inputs
- **CSRF Protection**: Secure token-based request verification
- **User Agent Rotation**: Prevents API detection

## 🎨 UI Features

- **Matrix Background**: Animated falling characters effect
- **Glowing Elements**: Neon green borders and text shadows
- **Smooth Animations**: Hover effects, loading animations, and transitions
- **Responsive Layout**: Adapts to all screen sizes
- **Copy Functionality**: Click any result value to copy to clipboard

## 📱 Mobile Support

The platform is fully responsive and includes:
- Touch-friendly navigation
- Optimized layouts for mobile screens
- Gesture support for interactions
- Mobile-optimized animations

## ⚖️ Legal Notice

**⚠️ EDUCATIONAL PURPOSE ONLY**

This platform is designed for educational and research purposes only. Users must:
- Comply with all applicable laws and regulations
- Respect privacy and data protection rights
- Use the tool responsibly and ethically
- Not use for malicious or illegal activities

## 🔗 API Integration

The platform integrates with OSINT APIs from `glonova.in` for:
- Email intelligence gathering
- Phone number lookups
- Username investigations
- Domain analysis

All API responses are automatically cleaned of credit information and properly formatted for display.

## 🐛 Troubleshooting

### Common Issues

1. **Rate Limit Exceeded**
   - Wait 1 minute before making new requests
   - Each IP is limited to 10 searches per minute

2. **API Timeout**
   - Check your internet connection
   - API might be temporarily unavailable

3. **Invalid Input**
   - Ensure your search query matches the selected type format
   - Use proper email format for email searches

### Browser Compatibility
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 📈 Performance

The platform is optimized for performance with:
- Lazy loading of animations
- Efficient DOM manipulation
- Optimized CSS animations
- Minimal HTTP requests

## 🤝 Contributing

This is an educational project. Feel free to:
- Report bugs and issues
- Suggest improvements
- Share educational use cases

## 📄 License

Educational use only. Not for commercial purposes.

## 📞 Support

For educational support and questions, join our Telegram channel:
[Join Channel](https://t.me/+FNstNY_ooV1lYzdl)

---

*Built with ❤️ for cybersecurity education*