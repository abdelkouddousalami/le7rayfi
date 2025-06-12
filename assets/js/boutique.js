document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filters-sidebar');
    const productsGrid = document.querySelector('.products-grid');
    const categoryInputs = document.querySelectorAll('input[name="category"]');
    const sortSelect = document.getElementById('sortBy');
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
    const priceMinInput = document.getElementById('priceMin');
    const priceMaxInput = document.getElementById('priceMax');
    const minPriceDisplay = document.getElementById('minPrice');
    const maxPriceDisplay = document.getElementById('maxPrice');
    const applyFiltersBtn = document.querySelector('.apply-filters');
    const laptopFilters = document.getElementById('laptopFilters');
    const phoneFilters = document.getElementById('phoneFilters');
    
    categoryInputs.forEach(input => {
        input.addEventListener('change', function() {
            const category = this.value;
            laptopFilters.style.display = 'none';
            phoneFilters.style.display = 'none';
            
            if (category === 'laptops' || category === 'desktops') {
                laptopFilters.style.display = 'block';
            } else if (category === 'smartphones' || category === 'tablets') {
                phoneFilters.style.display = 'block';
            }
        });
    });

    function updatePriceRanges(min, max) {
        priceMinInput.value = min;
        priceMaxInput.value = max;
        minPriceDisplay.value = min;
        maxPriceDisplay.value = max;
    }

    priceMinInput.addEventListener('input', function() {
        const min = parseInt(this.value);
        const max = parseInt(priceMaxInput.value);
        if (min > max) {
            updatePriceRanges(min, min);
        } else {
            updatePriceRanges(min, max);
        }
        debounceUpdateProducts();
    });    priceMaxInput.addEventListener('input', function() {
        const min = parseInt(priceMinInput.value);
        const max = parseInt(this.value);
        if (max < min) {
            updatePriceRanges(max, max);
        } else {
            updatePriceRanges(min, max);
        }
        debounceUpdateProducts();
    });

    let debounceTimer;
    function debounceUpdateProducts() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateProductsDisplay, 500);
    }

    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', debounceUpdateProducts);
    });

    minPriceDisplay.addEventListener('input', function() {
        priceMinInput.value = this.value;
        if (parseInt(this.value) > parseInt(maxPriceDisplay.value)) {
            maxPriceDisplay.value = this.value;
            priceMaxInput.value = this.value;
        }
    });

    maxPriceDisplay.addEventListener('input', function() {
        priceMaxInput.value = this.value;
        if (parseInt(this.value) < parseInt(minPriceDisplay.value)) {
            minPriceDisplay.value = this.value;
            priceMinInput.value = this.value;
        }
    });

    applyFiltersBtn.addEventListener('click', updateProductsDisplay);

    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('active');
                    const heartIcon = this.querySelector('i');
                    heartIcon.style.color = data.inWishlist ? '#ff4444' : '';
                }
            });
        });
    });

    document.querySelectorAll('.cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produit ajouté au panier!');
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cartCount;
                    }
                }
            });
        });
    });

    function showLoading() {
        loadingOverlay.classList.add('active');
    }

    function hideLoading() {
        loadingOverlay.classList.remove('active');
    }

    function sortProducts(products, sortBy) {
        const productsArray = Array.from(products);
        
        switch(sortBy) {
            case 'price-low':
                return productsArray.sort((a, b) => 
                    parseFloat(a.dataset.price) - parseFloat(b.dataset.price)
                );
            case 'price-high':
                return productsArray.sort((a, b) => 
                    parseFloat(b.dataset.price) - parseFloat(a.dataset.price)
                );
            case 'name':
                return productsArray.sort((a, b) => 
                    a.dataset.name.localeCompare(b.dataset.name)
                );
            case 'newest':
                return productsArray.sort((a, b) => 
                    parseInt(b.dataset.date) - parseInt(a.dataset.date)
                );
            default:
                return productsArray;
        }
    }    function updateProductsDisplay() {
        showLoading();
        
        const selectedCategory = document.querySelector('input[name="category"]:checked').value;
        const minPrice = parseInt(priceMinInput.value);
        const maxPrice = parseInt(priceMaxInput.value);
        const sortValue = sortSelect.value;
        
        const selectedRam = Array.from(document.querySelectorAll('input[name="ram[]"]:checked')).map(el => el.value);
        const selectedProcessors = Array.from(document.querySelectorAll('input[name="processor[]"]:checked')).map(el => el.value);
        const selectedStorage = Array.from(document.querySelectorAll('input[name="storage[]"]:checked')).map(el => el.value);
        const selectedCamera = Array.from(document.querySelectorAll('input[name="camera[]"]:checked')).map(el => el.value);
        
        const products = document.querySelectorAll('.product-card');
        const filteredProducts = Array.from(products).filter(product => {
            const productCategory = product.dataset.category;
            const productPrice = parseInt(product.dataset.price);
            const productRam = product.dataset.ram;
            const productProcessor = product.dataset.processor;
            const productStorage = product.dataset.storage;
            const productCamera = product.dataset.camera;
            
            const categoryMatch = selectedCategory === 'all' || productCategory === selectedCategory;
            const priceMatch = productPrice >= minPrice && productPrice <= maxPrice;

            const ramMatch = selectedRam.length === 0 || selectedRam.includes(productRam);
            const processorMatch = selectedProcessors.length === 0 || selectedProcessors.includes(productProcessor);
            const storageMatch = selectedStorage.length === 0 || selectedStorage.includes(productStorage);
            const cameraMatch = selectedCamera.length === 0 || selectedCamera.includes(productCamera);

            return categoryMatch && priceMatch && ramMatch && processorMatch && storageMatch && cameraMatch;
        });

        const sortedProducts = sortProducts(filteredProducts, sortValue);
        
        products.forEach(product => {
            product.style.display = 'none';
        });

        sortedProducts.forEach(product => {
            product.style.display = '';
        });

        setTimeout(hideLoading, 300); 
    }

    // Update event listeners
    sortSelect.addEventListener('change', updateProductsDisplay);
});