$(document).ready(function() {
    // Check if already logged in via session
    getUserData(function(userData) {
        if (userData) {
            window.location.href = '/webshop/products';
        }
    });

    setupValidation();
    setupAlreadyAccount();
    applySubmitEvent();
function setupAlreadyAccount() {
    console.log('Setting up already account checkbox');
    $('#alreadyAccount').on('change', function() {
        console.log('Checkbox changed:', $(this).is(':checked'));
        if ($(this).is(':checked')) {
            $('#name').prop('disabled', true).addClass('bg-light');
            $('#nameError').addClass('d-none');
            $('#name').removeClass('is-invalid');
        } else {
            $('#name').prop('disabled', false).removeClass('bg-light');
        }
    });
}
});

function setupValidation() {
    $('#username').on('input', function() {
        validateUsername();
    });

    $('#name').on('input', function() {
        validateName();
    });
}

function validateUsername() {
    const username = $('#username').val().trim();
    const $field = $('#username');
    const $error = $('#usernameError');
    
    clearError($field, $error);
    
    if (!username) {
        showError($field, $error, 'Gebruikersnaam is verplicht');
        return false;
    }
    
    if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
        showError($field, $error, 'Gebruikersnaam mag alleen letters, cijfers, underscore en streepje bevatten');
        return false;
    }
    
    if (username.length < 3 || username.length > 20) {
        showError($field, $error, 'Gebruikersnaam moet tussen 3 en 20 tekens zijn');
        return false;
    }
    
    return true;
}

function validateName() {
    if ($('#alreadyAccount').is(':checked')) {
        // Skip validation if already has account
        return true;
    }
    if (  $('#alreadyAccount').is(':checked')) {
        return true;
    }
    const name = $('#name').val().trim();
    const $field = $('#name');
    const $error = $('#nameError');
    clearError($field, $error);
    if (!name) {
        showError($field, $error, 'Naam is verplicht');
        return false;
    }
    if (name.length > 100) {
        showError($field, $error, 'Naam mag maximaal 100 tekens zijn');
        return false;
    }
    return true;
}

function showError($field, $error, message) {
    $field.addClass('is-invalid');
    $error.removeClass('d-none').addClass('d-block').text(message);
}

function clearError($field, $error) {
    $field.removeClass('is-invalid');
    $error.addClass('d-none').removeClass('d-block').text('');
}
function applySubmitEvent() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        // Validate all fields
        const usernameValid = validateUsername();
        const nameValid = validateName();
        
        if (!usernameValid || !nameValid) {
            console.log('Validation failed');
            return;
        }

        const username = $('#username').val().trim();
        const name = $('#name').val().trim();
        
        console.log('Attempting login with username:', username);

        // Try to login first
        $.ajax({
            url: '/webshop/api/users/login',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username: username }),
            dataType: 'json',
            success: function(response) {
                console.log('Login response:', response);
                if (response.success && response.data.user) {
                    // Save user data to cookie
                    setUserDataCookie(response.data.user);
                    // User logged in via session
                    showAlert('Welkom terug, ' + response.data.user.name + '!', 'success', function() {
                        window.location.href = '/webshop/products';
                    });
                }
            },
            error: function(xhr) {
                console.log('Login error:', xhr.status, xhr.responseJSON);
                // User not found, try to register
                if (xhr.status === 404 &&$('#alreadyAccount').is(':checked') === false) {
                   console.log('User not found, attempting registration');
                    registerUser(username, name);
                } else {
                    let errorMsg = 'Fout bij inloggen';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showAlert(errorMsg, 'error');
                }
            }
        });
    });
}

function registerUser(username, name) {
    console.log('Attempting registration with:', username, name);
    $.ajax({
        url: '/webshop/api/users/register',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ 
            username: username,
            name: name
        }),
        dataType: 'json',
        success: function(response) {
            console.log('Registration response:', response);
            if (response.success && response.data.user) {
                // Save user data to cookie
                setUserDataCookie(response.data.user);
                // User registered and logged in via session
                showAlert('Account aangemaakt! Welkom, ' + response.data.user.name + '!', 'success', function() {
                    window.location.href = '/webshop/products';
                });
            } else {
                showAlert(response.message || 'Fout bij aanmaken account', 'error');
            }
        },
        error: function(xhr) {
            console.log('Registration error:', xhr.status, xhr.responseJSON);
            let errorMsg = 'Fout bij aanmaken account';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showAlert(errorMsg, 'error');
        }
    });
}

// Cookie helper function
function setUserDataCookie(userData) {
    const expires = new Date();
    expires.setDate(expires.getDate() + 30);
    document.cookie = `user_data=${encodeURIComponent(JSON.stringify(userData))}; expires=${expires.toUTCString()}; path=/`;
}

