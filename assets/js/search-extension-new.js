document.addEventListener('DOMContentLoaded', function() {
    const searchContainer = document.querySelector('.search-container');
    const closeSearchBtn = document.querySelector('.close-search');
    const quickSearch = document.getElementById('quickSearch');
    const quickSearchCategory = document.getElementById('quickSearchCategory');
    const searchResults = document.getElementById('searchResults');
    
    // Add hover class to maintain panel visibility
    searchContainer.addEventListener('mouseenter', function() {
        searchContainer.classList.add('hover');
        // Focus the search input when panel opens
        quickSearch.focus();
    });

    searchContainer.addEventListener('mouseleave', function() {
        searchContainer.classList.remove('hover');
    });

    // Close button functionality
    closeSearchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        searchContainer.classList.remove('hover');
    });
    
    // Initial load of products
    try {
        const response = await fetch('get_filtered_products.php');
        if (!response.ok) throw new Error('Failed to load initial products');
        const data = await response.json();
        if (data.success) {
            updateProductsGrid(data.products);
        }
    } catch (error) {
        console.error('Initial load error:', error);
    }

    // Initialize with filter options
    async function initializeFilters() {
        try {
            const response = await fetch('get_filtered_products.php?get_options=1');
            const data = await response.json();
            
            if (data.success && data.options) {
                // Populate category filter
                if (data.options.categories) {
                    categoryFilter.innerHTML = `
                        <option value="">Toutes les catégories</option>
                        ${data.options.categories.map(cat => 
                            `<option value="${cat.slug}">${cat.name}</option>`
                        ).join('')}
                    `;
                }

                // Set price range placeholders
                if (data.options.price) {
                    priceMin.placeholder = `Min (${data.options.price.min} DH)`;
                    priceMax.placeholder = `Max (${data.options.price.max} DH)`;
                }
            }
        } catch (error) {
            console.error('Error fetching filter options:', error);
        }
    }    // Search and filter products
    async function searchProducts() {
        if (isSearching) return;
        isSearching = true;
        showLoadingSpinner();

        try {
            console.log('Starting product search...');
            
            // Build the query parameters
            const params = new URLSearchParams();

            // Add search if exists
            if (searchInput && searchInput.value.trim()) {
                params.append('search', searchInput.value.trim());
            }

            // Add category if selected
            if (categoryFilter && categoryFilter.value && categoryFilter.value !== '') {
                params.append('category', categoryFilter.value);
            }

            // Add price range if set
            if (priceMin && priceMin.value && !isNaN(priceMin.value)) {
                params.append('priceMin', priceMin.value);
            }
            if (priceMax && priceMax.value && !isNaN(priceMax.value)) {
                params.append('priceMax', priceMax.value);
            }

            console.log('Sending request with params:', params.toString());

            // Make the request
            const response = await fetch('get_filtered_products.php?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                }
            });            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const text = await response.text();
                console.error('Response text:', text);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const text = await response.text();
            console.log('Raw response:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response');
            }

            console.log('Parsed data:', data);

            if (!data) {
                throw new Error('Empty response');
            }

            if (data.success) {
                if (!Array.isArray(data.products)) {
                    console.error('Products is not an array:', data.products);
                    throw new Error('Invalid products data');
                }
                console.log('Updating grid with products:', data.products.length);
                updateProductsGrid(data.products);
            } else {
                throw new Error(data.message || 'Failed to fetch products');
            }
        } catch (error) {
            console.error('Search error:', error);
            showError(error.message || 'An error occurred while searching. Please try again.');
            if (productsGrid) {
                productsGrid.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Une erreur s'est produite: ${error.message}</p>
                    </div>`;
            }
        } finally {
            hideLoadingSpinner();
            isSearching = false;
        }
    }

    function showLoadingSpinner() {
        if (!document.querySelector('.search-loading')) {
            const spinner = document.createElement('div');
            spinner.className = 'search-loading';
            spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            productsGrid.parentElement.insertBefore(spinner, productsGrid);
        }
    }

    function hideLoadingSpinner() {
        const spinner = document.querySelector('.search-loading');
        if (spinner) spinner.remove();
    }

    function showError(message) {
        productsGrid.innerHTML = `
            <div class="no-results">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </div>`;
    }    function updateProductsGrid(products) {
        if (!productsGrid) return;

        if (!Array.isArray(products) || products.length === 0) {
            productsGrid.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Aucun produit trouvé</p>
                </div>`;
            return;
        }

        productsGrid.style.opacity = '0';

        productsGrid.innerHTML = products.map((product, index) => `
            <div class="product-card" data-category="${product.category_slug}">
                <div class="product-image-container">
                    <img src="${product.image_url}" alt="${product.name}" class="product-image">
                    ${product.stock < 5 && product.stock > 0 ? 
                        `<span class="badge stock-badge">Plus que ${product.stock} en stock!</span>` : ''}
                    ${isNew(product.created_at) ? 
                        '<span class="badge new-badge">Nouveau</span>' : ''}
                    ${product.discount > 0 ? 
                        `<span class="badge discount-badge">-${product.discount}%</span>` : ''}
                </div>
                <div class="product-info">
                    <span class="product-category">${product.category_name}</span>
                    <h3 class="product-title">${product.name}</h3>
                    
                    <div class="product-description">
                        <p>${product.description}</p>
                    </div>

                    <div class="product-price-container">
                        ${product.discount > 0 ? `
                            <div class="original-price">${formatPrice(product.price)} DH</div>
                            <div class="discounted-price">
                                ${formatPrice(product.price * (1 - product.discount/100))} DH
                            </div>
                        ` : `
                            <div class="product-price">${formatPrice(product.price)} DH</div>
                        `}
                    </div>
                </div>

                <div class="product-actions">
                    <div class="action-buttons">
                        ${product.stock > 0 ? `
                            <button class="product-button add-to-cart" onclick="addToCart(${product.id})">
                                <i class="fas fa-shopping-cart"></i> Ajouter au panier
                            </button>
                            <button class="product-button add-to-wishlist" onclick="addToWishlist(${product.id})">
                                <i class="fas fa-heart"></i>
                            </button>
                        ` : `
                            <button class="product-button out-of-stock" disabled>
                                <i class="fas fa-times"></i> Rupture de stock
                            </button>
                            <button class="product-button notify-stock">
                                <i class="fas fa-bell"></i> Notifier disponibilité
                            </button>
                        `}
                    </div>
                    <a href="product_details.php?id=${product.id}" class="product-button view-details">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                </div>
            </div>
        `).join('');

        setTimeout(() => {
            productsGrid.style.opacity = '1';
        }, 300);
    }

    // Helper functions
    function isNew(createdAt) {
        return new Date(createdAt) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
    }

    function formatPrice(price) {
        return Number(price).toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function debounce(func, wait) {
        return function executedFunction(...args) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Event listeners
    searchInput.addEventListener('input', debounce(searchProducts, 300));
    categoryFilter.addEventListener('change', searchProducts);
    priceMin.addEventListener('input', debounce(searchProducts, 500));
    priceMax.addEventListener('input', debounce(searchProducts, 500));

    // Initialize filters
    initializeFilters();
});