document.addEventListener('DOMContentLoaded', async function() {
    // Elements
    const searchInput = document.querySelector('.search-input');
    const productsGrid = document.querySelector('.products-grid');
    const searchExtension = document.querySelector('.search-extension');
    const categoryFilter = document.getElementById('categoryFilter');
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const filterToggle = document.querySelector('.filter-toggle');
    
    let searchTimeout;
    let isSearching = false;

    // Show/hide loading spinner
    function showLoadingSpinner() {
        const existingSpinner = document.querySelector('.search-loading');
        if (!existingSpinner) {
            const spinner = document.createElement('div');
            spinner.className = 'search-loading';
            spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            productsGrid.insertAdjacentElement('beforebegin', spinner);
        }
    }

    function hideLoadingSpinner() {
        const spinner = document.querySelector('.search-loading');
        if (spinner) {
            spinner.remove();
        }
    }    // Search products function
    async function searchProducts(filters = {}) {
        if (isSearching) return;
        isSearching = true;
        showLoadingSpinner();

        try {
            const searchParams = new URLSearchParams();
            Object.entries(filters).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    searchParams.append(key, value);
                }
            });

            const response = await fetch(`get_filtered_products.php?${searchParams.toString()}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            if (data.success && data.products) {
                updateProductsGrid(data.products);
            } else {
                throw new Error(data.message || 'Error fetching products');
            }
        } catch (error) {
            console.error('Search error:', error);
            productsGrid.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    Une erreur s'est produite lors de la recherche. Veuillez réessayer.
                </div>
            `;
        } finally {
            hideLoadingSpinner();
            isSearching = false;
        }
    }

    // Event listeners for filters
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            const filters = getFilters();
            searchProducts(filters);
        }, 300));
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', () => {
            const filters = getFilters();
            searchProducts(filters);
        });
    }    // Get all current filter values
    function getFilters() {
        const filters = {};
        
        // Search text
        if (searchInput && searchInput.value.trim()) {
            filters.search = searchInput.value.trim();
        }

        // Category
        if (categoryFilter && categoryFilter.value && categoryFilter.value !== 'all') {
            filters.category = categoryFilter.value;
        }

        // Price range
        const priceMin = document.getElementById('priceMin');
        const priceMax = document.getElementById('priceMax');
        if (priceMin && priceMin.value) filters.priceMin = priceMin.value;
        if (priceMax && priceMax.value) filters.priceMax = priceMax.value;

        // Specifications
        const specs = ['ram', 'storage', 'processor', 'camera', 'battery'];
        specs.forEach(spec => {
            const element = document.getElementById(spec + 'Filter');
            if (element && element.value) {
                filters[spec] = element.value;
            }
        });

        return filters;
    }    // Update the products grid with search results
    function updateProductsGrid(products) {
        if (!productsGrid) return;
        
        productsGrid.style.opacity = '0';
        setTimeout(() => {
            if (products.length === 0) {
                productsGrid.innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        Aucun produit trouvé
                    </div>
                `;
            } else {
                productsGrid.innerHTML = products.map(product => `
                    <div class="product-card ${isNew(product.created_at) ? 'new' : ''}">
                        <div class="product-image">
                            <img src="${product.image_url}" alt="${product.name}">
                            ${isNew(product.created_at) ? '<span class="new-badge">Nouveau</span>' : ''}
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${product.name}</h3>
                            <div class="product-specs">
                                ${Object.entries(product.specifications || {}).map(([key, value]) => `
                                    <span class="spec"><i class="fas fa-check"></i> ${key}: ${value}</span>
                                `).join('')}
                            </div>
                            <div class="product-price">${formatPrice(product.price)} DH</div>
                            ${product.stock > 0 ? 
                                `<button class="product-button" onclick="addToCart(${product.id})">
                                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                </button>` :
                                `<button class="product-button out-of-stock" disabled>
                                    <i class="fas fa-times"></i> Rupture de stock
                                </button>`
                            }
                        </div>
                    </div>
                `).join('');
            }
            productsGrid.style.opacity = '1';
        }, 300);
    }

    function isNew(createdAt) {
        if (!createdAt) return false;
        const date = new Date(createdAt);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays <= 7;
    }

    function formatPrice(price) {
        return Number(price).toLocaleString('fr-FR');
    }    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }    // Initialize all filter listeners
    function initializeFilters() {
        // Toggle filter sidebar
        if (filterToggle && filterAside) {
            filterToggle.addEventListener('click', () => {
                filterAside.classList.toggle('active');
            });
        }

        // Price range filters
        const priceMin = document.getElementById('priceMin');
        const priceMax = document.getElementById('priceMax');
        
        if (priceMin) {
            priceMin.addEventListener('input', debounce(() => {
                const filters = getFilters();
                searchProducts(filters);
            }, 500));
        }
        
        if (priceMax) {
            priceMax.addEventListener('input', debounce(() => {
                const filters = getFilters();
                searchProducts(filters);
            }, 500));
        }

        // Specification filters
        const specFilters = [
            'ramFilter', 'storageFilter', 'processorFilter',
            'cameraFilter', 'batteryFilter'
        ];

        specFilters.forEach(filterId => {
            const filter = document.getElementById(filterId);
            if (filter) {
                filter.addEventListener('change', () => {
                    const filters = getFilters();
                    searchProducts(filters);
                });
            }
        });

        // Category filter
        if (categoryFilter) {
            const pcFilters = document.getElementById('pc-filters');
            const mobileFilters = document.getElementById('mobile-filters');

            categoryFilter.addEventListener('change', function() {
                const category = this.value;
                
                // Toggle specification filters visibility
                if (pcFilters) {
                    pcFilters.style.display = category === 'laptops' ? 'block' : 'none';
                }
                if (mobileFilters) {
                    mobileFilters.style.display = category === 'smartphones' ? 'block' : 'none';
                }

                // Update products
                const filters = getFilters();
                searchProducts(filters);
            });
        }

        // Reset filters button
        const resetButton = document.getElementById('resetFilters');
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                // Reset all filters
                if (searchInput) searchInput.value = '';
                if (categoryFilter) categoryFilter.value = '';
                if (priceMin) priceMin.value = '';
                if (priceMax) priceMax.value = '';
                
                specFilters.forEach(filterId => {
                    const filter = document.getElementById(filterId);
                    if (filter) filter.value = '';
                });

                // Update products
                searchProducts({});
            });
        }
    }

    // Initialize filters
    initializeFilters();
});