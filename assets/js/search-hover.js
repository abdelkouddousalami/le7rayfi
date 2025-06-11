document.addEventListener('DOMContentLoaded', function() {
    const searchContainer = document.querySelector('.search-container');
    const closeSearchBtn = document.querySelector('.close-search');
    const quickSearch = document.getElementById('quickSearch');
    const quickSearchCategory = document.getElementById('quickSearchCategory');
    const searchResults = document.getElementById('searchResults');
    
    let searchTimeout;
    let currentRequest = null;

    // Add hover class to maintain panel visibility
    searchContainer.addEventListener('mouseenter', function() {
        searchContainer.classList.add('hover');
        quickSearch.focus();
    });

    searchContainer.addEventListener('mouseleave', function() {
        searchContainer.classList.remove('hover');
    });

    // Close button functionality
    closeSearchBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        searchContainer.classList.remove('hover');
    });

    // Prevent clicks inside search panel from closing it
    searchContainer.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Search functionality with debounce
    quickSearch?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        const category = quickSearchCategory?.value || '';

        if (query.length < 2) {
            searchResults.style.opacity = '0';
            setTimeout(() => {
                searchResults.innerHTML = '';
                searchResults.style.opacity = '1';
            }, 300);
            return;
        }

        searchTimeout = setTimeout(() => {
            // Show loading state
            searchResults.style.opacity = '0';
            setTimeout(() => {
                searchResults.innerHTML = `
                    <div class="search-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Recherche en cours...</p>
                    </div>
                `;
                searchResults.style.opacity = '1';
            }, 300);

            // Cancel previous request if it exists
            if (currentRequest) {
                currentRequest.abort();
            }

            // Create new AbortController for this request
            const controller = new AbortController();
            currentRequest = controller;

            // Prepare search parameters
            const params = new URLSearchParams({
                search: query,
                quickSearch: '1'
            });
            
            if (category) {
                params.append('category', category);
            }

            // Make the API call
            fetch(`get_filtered_products.php?${params.toString()}`, {
                signal: controller.signal
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Une erreur est survenue');
                }
                
                searchResults.style.opacity = '0';
                setTimeout(() => {
                    displaySearchResults(data.products);
                    searchResults.style.opacity = '1';
                }, 300);
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    return; // Request was aborted, do nothing
                }
                
                console.error('Search error:', error);
                searchResults.style.opacity = '0';
                setTimeout(() => {
                    searchResults.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>${error.message}</p>
                        </div>
                    `;
                    searchResults.style.opacity = '1';
                }, 300);
            });
        }, 300);
    });

    // Update search when category changes
    quickSearchCategory?.addEventListener('change', function() {
        if (quickSearch.value.length >= 2) {
            quickSearch.dispatchEvent(new Event('input'));
        }
    });

    // Display search results
    function displaySearchResults(products) {
        if (!products || products.length === 0) {
            searchResults.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Aucun résultat trouvé</p>
                    <small>Essayez avec d'autres mots-clés</small>
                </div>
            `;
            return;
        }

        searchResults.innerHTML = products.map(product => `
            <div class="search-result" onclick="window.location.href='product_details.php?id=${product.id}'">
                <img src="${product.image_url || 'img/default-product.jpg'}" 
                     alt="${product.name}" 
                     class="result-image"
                     onerror="this.src='img/default-product.jpg'">
                <div class="result-info">
                    <h4>${product.name}</h4>
                    <div class="result-price">
                        <i class="fas fa-tag"></i>
                        ${parseFloat(product.price).toLocaleString('fr-FR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })} DH
                    </div>
                    <div class="result-category">
                        <i class="fas fa-folder"></i>
                        ${product.category_name || 'Non catégorisé'}
                    </div>
                </div>
            </div>
        `).join('');
    }
});
