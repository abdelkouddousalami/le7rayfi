// Constants and Utility Functions
const CATEGORY_LABELS = {
    'pc': 'Ordinateurs',
    'laptop': 'Ordinateurs Portables',
    'mobile': 'Mobiles',
    'smartphone': 'Smartphones',
    'accessory': 'Accessoires'
};

// Utility function for category labels
function getCategoryLabel(category) {
    return CATEGORY_LABELS[category] || category;
}

// Debounce utility function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // Cart functionality
    window.addToCart = async function(productId) {
        try {
            const response = await fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            });
            const data = await response.json();
            
            if (data.success) {
                // Update cart count badge
                const cartBadge = document.querySelector('.badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cartCount;
                }
                
                // Show success message using a custom notification instead of alert
                showNotification('success', data.message);
            } else {
                // Show error message
                showNotification('error', data.message);
                
                // Redirect to login if not authenticated
                if (data.message.includes('connecter')) {
                    window.location.href = 'auth.php';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Une erreur est survenue');
        }
    };

    // Make addToCart function globally available
    window.addToCart = addToCart;

    // Cache all filter elements
    const elements = {
        productsGrid: document.querySelector('.products-grid'),
        priceMin: document.getElementById('priceMin'),
        priceMax: document.getElementById('priceMax'),
        search: document.getElementById('productSearch'),
        category: document.getElementById('categoryFilter'),
        resetBtn: document.getElementById('resetFilters'),
        pcFilters: document.getElementById('pc-filters'),
        mobileFilters: document.getElementById('mobile-filters'),
        activeFilters: document.getElementById('activeFilters'),
        // PC specific filters
        ram: document.getElementById('ramFilter'),
        ssd: document.getElementById('ssdFilter'),
        processor: document.getElementById('processorFilter'),
        // Mobile specific filters
        camera: document.getElementById('cameraFilter'),
        battery: document.getElementById('batteryFilter'),
        storage: document.getElementById('storageFilter')
    };

    // Function to get current filter values
    function getFilterValues() {
        const filters = new URLSearchParams();

        // Add price filters - only if they are valid
        const priceMin = parseFloat(elements.priceMin.value);
        const priceMax = parseFloat(elements.priceMax.value);

        if (!isNaN(priceMin) && priceMin >= 0) {
            filters.append('priceMin', priceMin.toString());
        }
        if (!isNaN(priceMax) && priceMax >= 0) {
            filters.append('priceMax', priceMax.toString());
        }

        // Add category filter
        if (elements.category.value) {
            filters.append('category', elements.category.value);
        }

        // Add search term
        if (elements.search.value.trim()) {
            filters.append('search', elements.search.value.trim());
        }

        // Add category-specific filters
        if (elements.category.value === 'pc') {
            if (elements.ram?.value) filters.append('ramFilter', elements.ram.value);
            if (elements.ssd?.value) filters.append('ssdFilter', elements.ssd.value);
            if (elements.processor?.value) filters.append('processorFilter', elements.processor.value);
        } else if (elements.category.value === 'mobile') {
            if (elements.camera?.value) filters.append('cameraFilter', elements.camera.value);
            if (elements.battery?.value) filters.append('batteryFilter', elements.battery.value);
            if (elements.storage?.value) filters.append('storageFilter', elements.storage.value);
        }

        return filters;
    }

    // Function to update products display
    async function updateProducts() {
        // Show loading state
        elements.productsGrid.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';

        try {
            const filters = getFilterValues();
            const response = await fetch(`get_filtered_products.php?${filters.toString()}`);
            const data = await response.json();

            if (data.success) {
                if (data.products.length === 0) {
                    elements.productsGrid.innerHTML = `
                        <div class="no-products">
                            <i class="fas fa-search"></i>
                            <p>Aucun produit ne correspond à vos critères</p>
                        </div>`;
                } else {
                    displayProducts(data.products);
                }
                updateActiveFilters();
            } else {
                elements.productsGrid.innerHTML = `
                    <div class="error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>${data.message || 'Une erreur est survenue'}</p>
                    </div>`;
            }
        } catch (error) {
            elements.productsGrid.innerHTML = `
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Une erreur est survenue lors du chargement des produits</p>
                </div>`;
            console.error('Error:', error);
        }
    }

    // Function to display products
    function displayProducts(products) {
        elements.productsGrid.innerHTML = products.map(product => {
            let specsList = '';
            if (product.category === 'pc' || product.category === 'laptop') {
                specsList = `
                    <div class="product-specs">
                        ${product.ram ? `<span><i class="fas fa-memory"></i> ${product.ram}</span>` : ''}
                        ${product.storage ? `<span><i class="fas fa-hdd"></i> ${product.storage}</span>` : ''}
                        ${product.processor ? `<span><i class="fas fa-microchip"></i> ${product.processor}</span>` : ''}
                    </div>`;
            } else if (product.category === 'mobile' || product.category === 'smartphone') {
                specsList = `
                    <div class="product-specs">
                        ${product.storage ? `<span><i class="fas fa-hdd"></i> ${product.storage}</span>` : ''}
                        ${product.camera ? `<span><i class="fas fa-camera"></i> ${product.camera}</span>` : ''}
                        ${product.battery ? `<span><i class="fas fa-battery-full"></i> ${product.battery}</span>` : ''}
                    </div>`;
            }

            return `
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="${product.image_url}" alt="${product.name}" class="product-image">
                        ${product.stock < 5 ? '<span class="stock-badge">Stock limité</span>' : ''}
                    </div>
                    <div class="product-info">
                        <span class="product-category">${getCategoryLabel(product.category)}</span>
                        <h3 class="product-title">${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        ${specsList}
                        <div class="product-price">${Number(product.price).toLocaleString('fr-FR')} DH</div>
                        <div class="product-actions">
                            ${product.stock > 0 ? `
                                <button onclick="addToCart(${product.id})" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                </button>` :
                                `<button disabled class="out-of-stock-btn">
                                    <i class="fas fa-times-circle"></i> Rupture de stock
                                </button>`
                            }
                        </div>
                    </div>
                </div>`;
        }).join('');
    }

    // Function to update active filters display
    function updateActiveFilters() {
        elements.activeFilters.innerHTML = '';
        const activeFilters = [];

        // Price filter
        const priceMin = parseFloat(elements.priceMin.value);
        const priceMax = parseFloat(elements.priceMax.value);
        if (!isNaN(priceMin) || !isNaN(priceMax)) {
            activeFilters.push({
                label: `Prix: ${isNaN(priceMin) ? '0' : priceMin} - ${isNaN(priceMax) ? '∞' : priceMax} DH`,
                clear: () => {
                    elements.priceMin.value = '';
                    elements.priceMax.value = '';
                    updateProducts();
                }
            });
        }

        // Category filter
        if (elements.category.value) {
            activeFilters.push({
                label: `Catégorie: ${getCategoryLabel(elements.category.value)}`,
                clear: () => {
                    elements.category.value = '';
                    elements.pcFilters.style.display = 'none';
                    elements.mobileFilters.style.display = 'none';
                    updateProducts();
                }
            });
        }

        // Add other active filters based on category
        if (elements.category.value === 'pc') {
            if (elements.ram?.value) {
                activeFilters.push({
                    label: `RAM: ${elements.ram.value}`,
                    clear: () => {
                        elements.ram.value = '';
                        updateProducts();
                    }
                });
            }

            if (elements.ssd?.value) {
                activeFilters.push({
                    label: `Stockage: ${elements.ssd.value}`,
                    clear: () => {
                        elements.ssd.value = '';
                        updateProducts();
                    }
                });
            }

            if (elements.processor?.value) {
                activeFilters.push({
                    label: `Processeur: ${elements.processor.value}`,
                    clear: () => {
                        elements.processor.value = '';
                        updateProducts();
                    }
                });
            }
        } else if (elements.category.value === 'mobile') {
            if (elements.camera?.value) {
                activeFilters.push({
                    label: `Caméra: ${elements.camera.value}`,
                    clear: () => {
                        elements.camera.value = '';
                        updateProducts();
                    }
                });
            }

            if (elements.battery?.value) {
                activeFilters.push({
                    label: `Batterie: ${elements.battery.value}`,
                    clear: () => {
                        elements.battery.value = '';
                        updateProducts();
                    }
                });
            }

            if (elements.storage?.value) {
                activeFilters.push({
                    label: `Stockage: ${elements.storage.value}`,
                    clear: () => {
                        elements.storage.value = '';
                        updateProducts();
                    }
                });
            }
        }

        // Create and append filter tags
        activeFilters.forEach(filter => {
            const tag = document.createElement('span');
            tag.className = 'filter-tag';
            tag.innerHTML = `
                ${filter.label}
                <button class="remove-filter" onclick="(${filter.clear.toString()})()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            elements.activeFilters.appendChild(tag);
        });
    }

    // Add event listeners with debouncing
    let debounceTimer;
    const debounce = (callback, time) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(callback, time);
    };

    // Price validation and filter event
    function validatePriceInput(input) {
        const value = parseFloat(input.value);
        if (isNaN(value) || value < 0) {
            input.value = '';
        }
    }

    // Price filter events
    elements.priceMin.addEventListener('input', (e) => {
        validatePriceInput(e.target);
        debounce(updateProducts, 500);
    });

    elements.priceMax.addEventListener('input', (e) => {
        validatePriceInput(e.target);
        debounce(updateProducts, 500);
    });

    // Category filter event
    elements.category.addEventListener('change', () => {
        // Toggle specification filters visibility
        elements.pcFilters.style.display = elements.category.value === 'pc' ? 'block' : 'none';
        elements.mobileFilters.style.display = elements.category.value === 'mobile' ? 'block' : 'none';
        updateProducts();
    });

    // Search input event
    elements.search.addEventListener('input', () => debounce(updateProducts, 500));

    // Add change event listeners to all specification filters
    ['ram', 'ssd', 'processor', 'camera', 'battery', 'storage'].forEach(filter => {
        elements[filter]?.addEventListener('change', updateProducts);
    });

    // Reset filters event
    elements.resetBtn.addEventListener('click', () => {
        // Reset all input values
        elements.priceMin.value = '';
        elements.priceMax.value = '';
        elements.search.value = '';
        elements.category.value = '';
        
        // Reset all specification filters
        ['ram', 'ssd', 'processor', 'camera', 'battery', 'storage'].forEach(filter => {
            if (elements[filter]) elements[filter].value = '';
        });

        // Hide specification filters
        elements.pcFilters.style.display = 'none';
        elements.mobileFilters.style.display = 'none';

        // Update products
        updateProducts();
    });

    // Function to get category label
        // Function to show custom notifications
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Initial load
    updateProducts();
});