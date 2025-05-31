// Authentication functions
function login(username, password, role) {
    // Get users from database
    const users = getUsers();
    
    // Find user with matching username, password, and role
    const user = users.find(u => 
        u.username === username && 
        u.password === password && 
        u.role === role
    );
    
    if (user) {
        // Set user session
        setCurrentUser(user);
        return true;
    }
    
    return false;
}

function logout() {
    // Remove user from session storage
    sessionStorage.removeItem('currentUser');
    localStorage.removeItem('currentUser');
}

function isLoggedIn() {
    // Check if user exists in session
    return !!getCurrentUser();
}

function getCurrentUser() {
    // Get current user from session storage or local storage
    const sessionUser = sessionStorage.getItem('currentUser');
    const localUser = localStorage.getItem('currentUser');
    
    if (sessionUser) {
        return JSON.parse(sessionUser);
    } else if (localUser) {
        return JSON.parse(localUser);
    }
    
    return null;
}

function setCurrentUser(user) {
    // Save user to session storage
    sessionStorage.setItem('currentUser', JSON.stringify(user));
    
    // If remember me is checked, save to local storage as well
    const rememberMe = document.getElementById('remember')?.checked;
    if (rememberMe) {
        localStorage.setItem('currentUser', JSON.stringify(user));
    }
}

function changePassword(userId, oldPassword, newPassword) {
    // Get users from database
    const users = getUsers();
    
    // Find user with matching ID and password
    const userIndex = users.findIndex(u => u.id === userId && u.password === oldPassword);
    
    if (userIndex !== -1) {
        // Update password
        users[userIndex].password = newPassword;
        
        // Save updated users
        setUsers(users);
        return true;
    }
    
    return false;
}

// Event Listeners for Login Page
document.addEventListener('DOMContentLoaded', function() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        // Tab switching
        const tabBtns = document.querySelectorAll('.tab-btn');
        let currentRole = 'admin'; // Default role
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs
                tabBtns.forEach(b => b.classList.remove('active'));
                
                // Add active class to current tab
                this.classList.add('active');
                
                // Set current role
                currentRole = this.dataset.role;
            });
        });
        
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (login(username, password, currentRole)) {
                // Show success message
                showNotification('Login successful. Redirecting...', 'success');
                
                // Redirect based on role
                setTimeout(() => {
                    switch(currentRole) {
                        case 'admin':
                            window.location.href = 'pages/admin.html';
                            break;
                        case 'doctor':
                            window.location.href = 'pages/doctor.html';
                            break;
                        case 'patient':
                            window.location.href = 'pages/patient.html';
                            break;
                    }
                }, 1000);
            } else {
                // Show error message
                showNotification('Invalid username or password', 'error');
            }
        });
    }
    
    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Logout user
            logout();
            
            // Show success message
            showNotification('Logged out successfully', 'success');
            
            // Redirect to login page
            setTimeout(() => {
                window.location.href = '../index.html';
            }, 1000);
        });
    }
});

// Utility function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    
    if (notification) {
        // Set message and type
        notification.textContent = message;
        notification.className = 'notification';
        notification.classList.add(type);
        notification.classList.add('show');
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }
}

// Initialize current date
document.addEventListener('DOMContentLoaded', function() {
    const currentDateElement = document.getElementById('currentDate');
    
    if (currentDateElement) {
        const now = new Date();
        currentDateElement.textContent = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
});