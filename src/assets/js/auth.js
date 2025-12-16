// Shared authentication utilities
let currentUser = null;

// Get user data from cookie (no server call)
function getUserData(callback) {
    // Get from cookie
    const userData = getUserDataFromCookie();
    currentUser = userData;
    console.log('User data from cookie:', userData);

    if (callback && typeof callback === 'function') {
        callback(userData);
    }
    return userData;
}

function isLoggedIn() {
    return currentUser !== null;
}

// Initialize on document ready
$(document).ready(function() {
    getUserData();
    updateHeaderAuth();

});

function updateHeaderAuth() {
   
        const $loginLink = $('#loginLink');
        
        if (currentUser) {
            // User is logged in
            $loginLink.parent().html(`
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> ${escapeHtml(currentUser.name)}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#" id="logoutLink"><i class="bi bi-box-arrow-right"></i> Uitloggen</a></li>
                    </ul>
                </li>
            `);
            
            // Attach logout handler
            $('#logoutLink').on('click', function(e) {
                e.preventDefault();
                logout();
            });
        } else {
            // User is not logged in
            $loginLink.attr('href', '/webshop/login');
        }
  
}

function logout() {
    $.ajax({
        url: '/webshop/api/user/logout',
        method: 'POST',
        success: function() {
            currentUser = null;
            clearUserDataCookie();
            window.location.href = '/webshop/products';
        },
        error: function() {
            // Even on error, clear cookie and redirect
            currentUser = null;
            clearUserDataCookie();
            window.location.href = '/webshop/products';
        }
    });
}

function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

// Cookie helper functions (for backwards compatibility)
function setUserDataCookie(userData) {
    const expires = new Date();
    expires.setDate(expires.getDate() + 30);
    document.cookie = `user_data=${encodeURIComponent(JSON.stringify(userData))}; expires=${expires.toUTCString()}; path=/`;
}

function getUserDataFromCookie() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'user_data') {
            try {
                return JSON.parse(decodeURIComponent(value));
            } catch (e) {
                return null;
            }
        }
    }
    return null;
}

function clearUserDataCookie() {
    document.cookie = 'user_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}
