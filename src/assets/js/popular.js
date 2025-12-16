function loadPopularProducts() {
    $.ajax({
        url: '/webshop/api/products/popular',
        method: 'GET',
        data: { limit: 10 },
        dataType: 'json',
        success: function(data) {
            $('#loading').addClass('d-none');
            if (data.success && data.data.length > 0) {
                renderProducts(data.data);
            }
        },
        error: function(xhr, status, error) {
            $('#loading').addClass('d-none');
            alert('Fout bij laden producten: ' + error);
        }
    });
}

function renderProducts(products) {
    const grid = document.getElementById('popularGrid');
    grid.innerHTML = products.map((product, index) => `
        <div class="col">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title">${escapeHtml(product.name)}</h5>
                        <span class="badge bg-danger">#${index + 1}</span>
                    </div>
                    <p class="card-text text-muted small">${escapeHtml(product.description || 'Geen beschrijving')}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h5 mb-0 text-primary">€${parseFloat(product.price).toFixed(2)}</span>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-eye-fill"></i> <strong>${product.click}</strong> weergaven
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a href="/webshop/product?id=${escapeAttr(product.id)}" class="btn btn-primary btn-sm w-100">
                        Bekijk Details
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function escapeAttr(text) {
    if (text == null) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/'/g, '&#39;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

loadPopularProducts();
