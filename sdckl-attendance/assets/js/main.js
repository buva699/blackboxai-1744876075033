// Main JavaScript file for SDCKL Attendance System

// DOM Elements
const loginForm = document.getElementById('loginForm');
const errorAlert = document.getElementById('errorAlert');
const errorMessage = document.getElementById('errorMessage');

// Demo credentials
const DEMO_CREDENTIALS = {
    username: 'admin',
    password: 'admin123'
};

// Handle login form submission
loginForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Validate credentials (demo purposes)
    if (username === DEMO_CREDENTIALS.username && password === DEMO_CREDENTIALS.password) {
        // Store login status
        localStorage.setItem('isLoggedIn', 'true');
        
        // Redirect to dashboard
        window.location.href = 'dashboard.html';
    } else {
        // Show error message
        showError('Invalid username or password');
    }
});

// Function to show error message
function showError(message) {
    errorMessage.textContent = message;
    errorAlert.classList.remove('hidden');
    
    // Hide error after 3 seconds
    setTimeout(() => {
        errorAlert.classList.add('hidden');
    }, 3000);
}

// Check login status on page load
function checkLoginStatus() {
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    const currentPage = window.location.pathname;

    // If not logged in and trying to access protected pages
    if (!isLoggedIn && !currentPage.includes('index.html')) {
        window.location.href = 'index.html';
    }
    
    // If logged in and on login page, redirect to dashboard
    if (isLoggedIn && currentPage.includes('index.html')) {
        window.location.href = 'dashboard.html';
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    checkLoginStatus();
});

// Logout function
window.logout = function() {
    localStorage.removeItem('isLoggedIn');
    window.location.href = 'index.html';
};
