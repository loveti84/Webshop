// Shared state
let allProducts = [];
let currentView = 'card'; // 'card' or 'table'
let currentPage = 1;
let itemsPerPage = 16;
let totalPages = 1;


//event handlerts
$(document).ready(function() {
    loadProducts();
    apllyEvents();
});

function apllyEvents(){
$('#searchBtn').on('click', function() {
    currentPage = 1;
    applyFilters();
});

$('#searchInput').on('keypress', function(e) {
    if (e.key === 'Enter') {
        $('#searchBtn').click();
    }
});

$('#minRating').on('change', function() {
    currentPage = 1;
    applyFilters();
});

$('#sortBy').on('change', function() {
    currentPage = 1;
    applyFilters();
});

$('#sortOrder').on('change', function() {
    currentPage = 1;
    applyFilters();
});

$('#itemsPerPage').on('change', function() {
    itemsPerPage = parseInt($(this).val());
    currentPage = 1;
    applyFilters();
});

$('#resetFilters').on('click', function() {
    $('#searchInput').val('');
    $('#minRating').val('0');
    $('#sortBy').val('created_at');
    $('#sortOrder').val('desc');
    $('#itemsPerPage').val('12');
    itemsPerPage = 12;
    currentPage = 1;
    loadProducts();
});

$('#cardViewBtn').on('click', function() {
    if (currentView !== 'card') {
        currentView = 'card';
        $('#cardViewBtn').addClass('active');
        $('#tableViewBtn').removeClass('active');
        renderProducts(allProducts);
    }
});

$('#tableViewBtn').on('click', function() {
    if (currentView !== 'table') {
        currentView = 'table';
        $('#tableViewBtn').addClass('active');
        $('#cardViewBtn').removeClass('active');
        renderProducts(allProducts);
    }
});
}
// Helper functions

function applyFilters() {
    const keyword = $('#searchInput').val().trim();
    const minRating = $('#minRating').val();
    const sortBy = $('#sortBy').val() || 'created_at';
    const sortOrder = $('#sortOrder').val() || 'desc';

    // If no filters or sorting, load all products with default sort
    if (!keyword && minRating === '0' && sortBy === 'created_at' && sortOrder === 'desc') {
        loadProducts();
        return;
    }

    $('#loading').removeClass('d-none');
    $('#productsGrid').html('');
    $('#emptyState').addClass('d-none');
    $('#resultsCount').addClass('d-none');
    $('#pagination').addClass('d-none');
    
    $.ajax({
        url: '/webshop/api/products/filter',
        method: 'GET',
        data: { 
            q: keyword,
            min_rating: minRating,
            sort_by: sortBy,
            sort_order: sortOrder,
            page: currentPage,
            per_page: itemsPerPage
        },
        dataType: 'json',
        success: function(data) {
            $('#loading').addClass('d-none');
            if (data.success && data.data.length > 0) {
                allProducts = data.data;
                totalPages = data.pagination.total_pages;
                renderProducts(data.data);
                showResultsCount(data.pagination.total);
                renderPagination(data.pagination);
            } else {
                $('#emptyState').removeClass('d-none');
            }
        },
        error: function(xhr, status, error) {
            console.log(xhr);
            $('#loading').addClass('d-none');
            //show the error message fron the backend validation
            showAlert('Fout bij filteren: ' + xhr.responseJSON.message, 'error');
        }
    });
}

function loadProducts() {
    $('#loading').removeClass('d-none');
    $('#productsGrid').html('');
    $('#emptyState').addClass('d-none');
    $('#resultsCount').addClass('d-none');
    $('#pagination').addClass('d-none');

    $.ajax({
        url: '/webshop/api/products',
        method: 'GET',
        data: {
            page: currentPage,
            per_page: itemsPerPage
        },
        dataType: 'json',
        success: function(data) {
            $('#loading').addClass('d-none');
            if (data.success && data.data.length > 0) {
                allProducts = data.data;
                totalPages = data.pagination.total_pages;
                renderProducts(data.data);
                showResultsCount(data.pagination.total);
                renderPagination(data.pagination);
            } else {
                $('#emptyState').removeClass('d-none');
            }
        },
        error: function(xhr, status, error) {
            $('#loading').addClass('d-none');
            showAlert('Fout bij laden producten: ' + error, 'error');
            console.error(xhr.responseText);
        }
    });
}

function renderProducts(products) {
    
    if (currentView === 'card') {
        renderCardView(products);
    } else {
        renderTableView(products);
    }
}

function renderCardView(products) {
    $('#productsGrid').removeClass('d-none').empty();
    $('#productsTable').addClass('d-none');

    // Define card template structure once - easy to see and maintain
    const cardTemplate = $(`
        <div class="col">
            <div class="card h-100 shadow-sm product-card" style="cursor: pointer;">
                <div class="card-body">
                    <h5 class="card-title"></h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="h5 mb-0 text-primary card-price"></span>
                        <div class="rating-stars card-rating"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted card-views">
                            <i class="bi bi-eye"></i> <span class="view-count"></span> weergaven
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a class="btn btn-primary btn-sm w-100 card-link">
                        <i class="bi bi-box-arrow-up-right"></i> Bekijk Details
                    </a>
                </div>
            </div>
        </div>
    `);

    // Efficient: one DOM update after the loop, fragment is never part of DOM
    const $fragment = $(document.createDocumentFragment());

    products.forEach(product => {
        // Clone the template and populate with data
        const $card = cardTemplate.clone();

        // Set data using jQuery selectors - safe with .text() and .attr()
        $card.find('.product-card').attr('data-product-id', product.id);
        $card.find('.card-title').text(product.name);
        $card.find('.card-price').text('€' + parseFloat(product.price).toFixed(2));
        $card.find('.card-rating')
            .append(renderStars(product.avg_rating || 0))
            .append($('<small>').addClass('text-muted').text(` (${product.review_count || 0})`));
        $card.find('.view-count').text(product.click);
        $card.find('.card-link').attr('href', '/webshop/product?id=' + product.id);

        // Add click event for the card
        $card.find('.product-card').on('click', function(e) {
            if (!$(e.target).closest('a').length) {
                window.location.href = '/webshop/product?id=' + product.id;
            }
        });

        $fragment.append($card);
    });

    $('#productsGrid').append($fragment);
}

function renderTableView(products) {
    $('#productsGrid').addClass('d-none');
    $('#productsTable').removeClass('d-none');

    const $tbody = $('#productsTableBody').empty();

    const rowTemplate = $(`
        <tr class="product-row" style="cursor: pointer;">
            <td>
                <strong class="product-name"></strong>
                <button class="btn btn-sm btn-link p-0 ms-2 description-popover-btn" 
                        data-bs-toggle="popover" 
                        data-bs-placement="right" 
                        data-bs-trigger="hover focus"
                        tabindex="0">
                    <i class="bi bi-info-circle text-primary"></i>
                </button>
            </td>
            <td class="text-primary">
                <strong class="product-price"></strong>
            </td>
            <td class="product-rating">
                <small class="text-muted review-count"></small>
            </td>
            <td>
                <small class="text-muted product-clicks">
                    <i class="bi bi-eye"></i> <span class="click-count"></span>
                </small>
            </td>
            <td>
                <a class="btn btn-primary btn-sm product-link">
                    <i class="bi bi-box-arrow-up-right"></i> Details
                </a>
            </td>
        </tr>
    `);

    products.forEach(product => {
        // Clone template and populate with data
        const $row = rowTemplate.clone();

        // Set data using jQuery selectors - safe with .text() and .attr()
        $row.attr('data-product-id', product.id);
        $row.find('.product-name').text(product.name);
        $row.find('.description-popover-btn').attr('data-bs-content', product.description || 'Geen beschrijving');
        $row.find('.product-price').text('€' + parseFloat(product.price).toFixed(2));
        $row.find('.product-rating').prepend(renderStars(product.avg_rating || 0));
        $row.find('.review-count').text(` (${product.review_count || 0})`);
        $row.find('.click-count').text(product.click);
        $row.find('.product-link').attr('href', '/webshop/product?id=' + product.id);

        // Add click event for the row
        $row.on('click', function(e) {
            if (!$(e.target).closest('a, button').length) {
                window.location.href = '/webshop/product?id=' + product.id;
            }
        });

        $tbody.append($row);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
}



function renderStars(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5 ? 1 : 0;
    const emptyStars = 5 - fullStars - halfStar;
    
    let $stars = $('<span></span>');
    
    for (let i = 0; i < fullStars; i++) {
        $stars.append($('<i>').addClass('bi bi-star-fill text-warning'));
    }
    
    if (halfStar) {
        $stars.append($('<i>').addClass('bi bi-star-half text-warning'));
    }
    
    for (let i = 0; i < emptyStars; i++) {
        $stars.append($('<i>').addClass('bi bi-star text-warning'));
    }
    
    return $stars.html();
}

function showResultsCount(count) {
    let text = `${count} ${count === 1 ? 'product' : 'producten'} gevonden`;
    $('#countText').text(text);
    $('#resultsCount').removeClass('d-none');
}

function renderPagination(pagination) {
    const $pagination = $('#paginationContainer').empty();
    
    if (pagination.total_pages <= 1) {
        $('#pagination').addClass('d-none');
        return;
    }
    
    $('#pagination').removeClass('d-none');
    
    const $nav = $('<nav>').attr('aria-label', 'Product pagination');
    const $ul = $('<ul>').addClass('pagination justify-content-center mb-0');
    
    // Previous button
    const $prevLi = $('<li>').addClass('page-item').toggleClass('disabled', pagination.current_page === 1);
    const $prevLink = $('<a>').addClass('page-link').attr('href', '#').html('&laquo;');
    if (pagination.current_page > 1) {
        $prevLink.on('click', function(e) {
            e.preventDefault();
            currentPage = pagination.current_page - 1;
            applyFilters();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    $prevLi.append($prevLink);
    $ul.append($prevLi);
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, pagination.current_page - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(pagination.total_pages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
        const $firstLi = $('<li>').addClass('page-item');
        const $firstLink = $('<a>').addClass('page-link').attr('href', '#').text('1');
        $firstLink.on('click', function(e) {
            e.preventDefault();
            currentPage = 1;
            applyFilters();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        $firstLi.append($firstLink);
        $ul.append($firstLi);
        
        if (startPage > 2) {
            $ul.append($('<li>').addClass('page-item disabled').append($('<span>').addClass('page-link').text('...')));
        }
    }
    
    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
        const $li = $('<li>').addClass('page-item').toggleClass('active', i === pagination.current_page);
        const $link = $('<a>').addClass('page-link').attr('href', '#').text(i);
        
        if (i !== pagination.current_page) {
            $link.on('click', function(e) {
                e.preventDefault();
                currentPage = i;
                applyFilters();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        
        $li.append($link);
        $ul.append($li);
    }
    
    // Last page
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            $ul.append($('<li>').addClass('page-item disabled').append($('<span>').addClass('page-link').text('...')));
        }
        
        const $lastLi = $('<li>').addClass('page-item');
        const $lastLink = $('<a>').addClass('page-link').attr('href', '#').text(pagination.total_pages);
        $lastLink.on('click', function(e) {
            e.preventDefault();
            currentPage = pagination.total_pages;
            applyFilters();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        $lastLi.append($lastLink);
        $ul.append($lastLi);
    }
    
    // Next button
    const $nextLi = $('<li>').addClass('page-item').toggleClass('disabled', pagination.current_page === pagination.total_pages);
    const $nextLink = $('<a>').addClass('page-link').attr('href', '#').html('&raquo;');
    if (pagination.current_page < pagination.total_pages) {
        $nextLink.on('click', function(e) {
            e.preventDefault();
            currentPage = pagination.current_page + 1;
            applyFilters();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    $nextLi.append($nextLink);
    $ul.append($nextLi);
    
    $nav.append($ul);
    $pagination.append($nav);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
