document.addEventListener('DOMContentLoaded', function() {
    const searchWrapper = document.querySelector('.search-wrapper');
    const quickSearch = document.getElementById('quickSearch');
    const quickSearchCategory = document.getElementById('quickSearchCategory');
    const searchResults = document.getElementById('searchResults');
    
    let searchTimeout;
    let pusher;
    let channel;

    function initializePusher() {
        if (typeof Pusher !== 'undefined' && window.PUSHER_KEY && window.PUSHER_CLUSTER) {
            pusher = new Pusher(window.PUSHER_KEY, {
                cluster: window.PUSHER_CLUSTER
            });
            
            channel = pusher.subscribe('search-channel');
            channel.bind('search-results', function(data) {
                updateSearchResults(data.results);
            });
        }
    }

    searchWrapper.addEventListener('mouseenter', () => {
        setTimeout(() => {
            quickSearch?.focus();
        }, 300);
    });

    searchWrapper.addEventListener('mouseleave', () => {
       
    });

    quickSearch.addEventListener('input', debounce(handleSearch, 500));
    quickSearchCategory.addEventListener('change', handleSearch);

    function handleSearch() {
        const query = quickSearch.value.trim();
        const category = quickSearchCategory.value;

        if (query.length === 0 && !category) {
            searchResults.innerHTML = '';
            return;
        }

        showLoading();
        
        const searchParams = new URLSearchParams({
            search: query,
            category: category
        });

        fetch(`get_filtered_products.php?${searchParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateSearchResults(data.products);
                } else {
                    showNoResults('Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showNoResults('Erreur de connexion');
            });
    }

    function updateSearchResults(products) {
        if (!products || products.length === 0) {
            showNoResults();
            return;
        }

        searchResults.innerHTML = '';
        
        products.forEach((product, index) => {
            const resultItem = createResultItem(product);
            resultItem.style.animationDelay = `${index * 0.1}s`;
            searchResults.appendChild(resultItem);
        });
    }

    function createResultItem(product) {
        const div = document.createElement('div');
        div.className = 'result-item';
        div.style.cursor = 'pointer';
        div.onclick = () => window.location.href = `product_details.php?id=${product.id}`;
        
        div.innerHTML = `
            <img src="${product.image_url}" alt="${product.name}" class="result-image">
            <div class="result-info">
                <h4 class="result-title">${product.name}</h4>
                <span class="result-category">${getCategoryLabel(product.category)}</span>                ${product.specs ? `
                    <div class="specs-section">
                        <div class="specs-title">Spécifications</div>
                        <div class="result-specs">
                            ${product.specs.map(spec => `
                                <span class="spec-badge">
                                    <i class="fas fa-${getSpecIcon(spec)}"></i>
                                    ${spec}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                <div class="result-price">${formatPrice(product.price)} DH</div>
            </div>
        `;

        return div;
    }

    function showLoading() {
        searchResults.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner"></i>
                <p>Recherche en cours...</p>
            </div>
        `;
    }

    function showNoResults(message = 'Aucun produit trouvé') {
        searchResults.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>${message}</p>
            </div>
        `;
    }

    function getCategoryLabel(category) {
        const labels = {
            'pc': 'Ordinateurs',
            'mobile': 'Mobiles',
            'accessory': 'Accessoires'
        };
        return labels[category] || category;
    }

    function getSpecIcon(spec) {
        const iconMap = {
            'RAM': 'memory',
            'Storage': 'hdd',
            'Camera': 'camera',
            'Battery': 'battery-full',
            'Screen': 'mobile',
            'Processor': 'microchip',
            'Graphics': 'tv'
        };

        for (const [key, value] of Object.entries(iconMap)) {
            if (spec.toLowerCase().includes(key.toLowerCase())) {
                return value;
            }
        }
        return 'microchip';
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

    if (typeof Pusher !== 'undefined') {
        initializePusher();
    }
});
