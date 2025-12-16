const urlParams = new URLSearchParams(window.location.search);
const productId = urlParams.get('id');

$(document).ready(function() {
    loadProduct();
    setupFieldValidation();
    prefillUserData();
    applySubmitEvent();
});

// Validation rules for each field
const validationRules = {
    reviewUsername: {
        fieldId: 'reviewUsername',
        errorId: 'usernameError',
        validate: function(value) {
            const errors = [];
            
            if (!value) {
                errors.push('Gebruikersnaam is verplicht');
            } else {
                if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
                    errors.push('Gebruikersnaam mag alleen letters, cijfers, underscore en streepje bevatten');
                }
                if (value.length < 3) {
                    errors.push('Gebruikersnaam moet minimaal 3 tekens zijn');
                }
                if (value.length > 20) {
                    errors.push('Gebruikersnaam mag maximaal 20 tekens zijn');
                }
            }
            
            return errors.length > 0 ? errors : null;
        }
    },
    reviewName: {
        fieldId: 'reviewName',
        errorId: 'nameError',
        validate: function(value) {
            const errors = [];
            
            if (!value) {
                errors.push('Naam is verplicht');
            } else {
                if (value.length > 100) {
                    errors.push('Naam mag maximaal 100 tekens zijn');
                }
            }
            
            return errors.length > 0 ? errors : null;
        }
    },
    reviewScore: {
        fieldId: 'reviewScore',
        errorId: 'scoreError',
        validate: function(value) {
            const errors = [];
            
            if (!value) {
                errors.push('Selecteer een beoordeling');
            }
            
            return errors.length > 0 ? errors : null;
        }
    },
    reviewText: {
        fieldId: 'reviewText',
        errorId: 'textError',
        validate: function(value) {
            const errors = [];
            
            if (!value) {
                errors.push('Beoordelingstekst is verplicht');
            } else {
                if (value.length > 1000) {
                    errors.push('Beoordelingstekst mag maximaal 1000 tekens zijn');
                }
            }
            
            return errors.length > 0 ? errors : null;
        }
    }
};

function setupFieldValidation() {
    // Attach validation to all fields
    Object.keys(validationRules).forEach(function(fieldId) {
        const rule = validationRules[fieldId];
        const $field = $('#' + fieldId);
        
        // Validate on input (real-time as user types)
        $field.on('input change', function() {
            validateField(fieldId);
        });
    });
}

function prefillUserData() {
    const userData = getUserData();

    if (userData) {
        $('#reviewUsername').val(userData.username).prop('disabled', true).addClass('bg-light');
        $('#reviewName').val(userData.name).prop('disabled', true).addClass('bg-light');
        const $success = $('#reviewUsername').parent().find('small.text-success');
        $success.removeClass('d-none');
        $success.html('<i class="bi bi-check-circle-fill"></i> Ingelogd als ' + escapeHtml(userData.username));
    }
}

function validateField(fieldId) {
    const rule = validationRules[fieldId];
    const value = $('#' + fieldId).val().trim();
    
    clearFieldError(rule.fieldId, rule.errorId);
    
    const errors = rule.validate(value);
    if (errors) {
        showError(rule.fieldId, rule.errorId, errors);
        return false;
    }
    return true;
}

function validateAllFields() {
    let isValid = true;
    
    Object.keys(validationRules).forEach(function(fieldId) {
        if (!validateField(fieldId)) {
            isValid = false;
        }
    });
    
    return isValid;
}
function applySubmitEvent(){
$('#reviewForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validate all fields
    if (!validateAllFields()) {
        return;
    }

    const reviewData = {
        username: $('#reviewUsername').val().trim(),
        name: $('#reviewName').val().trim(),
        product_id: productId,
        score: parseInt($('#reviewScore').val()),
        text: $('#reviewText').val().trim()
    };

    $.ajax({
        url: '/webshop/api/reviews',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(reviewData),
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                showAlert('Beoordeling succesvol verzonden!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                $('#reviewForm')[0].reset();
                loadProduct();
                prefillUserData();
            } else {
                showAlert(data.message || 'Fout bij verzenden beoordeling', 'error');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Fout bij verzenden beoordeling';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showAlert(errorMsg, 'error');
        }
    });
});
}
function showError(fieldId, errorId, errors) {
    $('#' + fieldId).addClass('is-invalid');
    
    // If errors is an array, join them with line breaks
    const errorMessage = Array.isArray(errors) ? errors.join('; ') : errors;
    
    $('#' + errorId).removeClass('d-none').addClass('d-block').html(errorMessage);
}

function clearFieldError(fieldId, errorId) {
    $('#' + fieldId).removeClass('is-invalid');
    $('#' + errorId).addClass('d-none').removeClass('d-block').text('');
}

function loadProduct() {
    if (!productId) {
        window.location.href = '/webshop/products';
        return;
    }

    $.ajax({
        url: `/webshop/api/product?id=${productId}`,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                displayProduct(data.data);
                $('#loading').addClass('d-none');
                $('#productDetails').removeClass('d-none');
            } else {
                showAlert('Product niet gevonden', 'error');
                window.location.href = '/webshop/products';
            }
        },
        error: function(xhr, status, error) {
            showAlert('Fout bij laden product: ' + error, 'error');
            console.error(xhr.responseText);
        }
    });
}

function displayProduct(data) {
    $('#productName').text(data.product.name);
    $('#productDescription').text(data.product.description || 'Geen beschrijving beschikbaar');
    $('#productPrice').text('€' + parseFloat(data.product.price).toFixed(2));
    $('#productClicks').text(data.product.click);
    $('#productRating').html(renderStars(data.avg_rating) + ' <span class="ms-2">' + data.avg_rating.toFixed(1) + ' / 5</span>');
    $('#reviewCount').text(`(${data.review_count} beoordelingen)`);

    displayReviews(data.reviews);
}

function displayReviews(reviews) {
    const $reviewsList = $('#reviewsList').empty();

    if (reviews.length === 0) {
        $reviewsList.append(
            $('<p>').addClass('text-muted').text('Nog geen beoordelingen. Wees de eerste om te beoordelen!')
        );
        return;
    }

    // Define review template structure - easy to see and maintain
    const reviewTemplate = $(`
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between">
                <h6 class="review-name"></h6>
                <div class="review-stars"></div>
            </div>
            <small class="text-muted review-username"></small>
            <p class="mt-2 mb-1 review-text"></p>
            <small class="text-muted review-date"></small>
        </div>
    `);

    reviews.forEach(review => {
        // Clone template and populate with data
        const $review = reviewTemplate.clone();
        
        $review.find('.review-name').text(review.name || '');
        $review.find('.review-stars').html(renderStars(review.score));
        $review.find('.review-username').text(review.username ? '@' + review.username : 'Onbekend');
        $review.find('.review-text').text(review.text);
        $review.find('.review-date').text(new Date(review.created_at).toLocaleDateString());

        $reviewsList.append($review);
    });
}

function renderStars(rating) {
    let html = '';
    for (let i = 0; i < 5; i++) {
        html += i < rating 
            ? '<i class="bi bi-star-fill text-warning"></i>' 
            : '<i class="bi bi-star text-warning"></i>';
    }
    return html;
}

function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}


