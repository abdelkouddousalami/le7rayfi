document.addEventListener('DOMContentLoaded', function() {
    const searchContainer = document.querySelector('.search-container');
    const closeSearchBtn = document.querySelector('.close-search');
    const quickSearch = document.getElementById('quickSearch');
    const quickSearchCategory = document.getElementById('quickSearchCategory');
    const searchResults = document.getElementById('searchResults');
    const dynamicFiltersContainer = document.getElementById('dynamicFiltersContainer');
    const activeFiltersContainer = document.getElementById('activeFilters');
    
    let searchTimeout;
    let currentRequest = null;
    let filterOptions = {}; 
    let activeFilters = {}; 

    searchContainer.addEventListener('mouseenter', function() {
        searchContainer.classList.add('hover');
        quickSearch.focus();
    });

    searchContainer.addEventListener('mouseleave', function() {
        searchContainer.classList.remove('hover');
    });

    closeSearchBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        searchContainer.classList.remove('hover');
    });

    searchContainer.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    async function initializeFilters() {
        try {
            console.log('Chargement des options de filtres...');
            const response = await fetch('get_filtered_products.php?get_options=1');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.options) {
                filterOptions = data.options;
                console.log('Options de filtres chargées:', filterOptions);
                console.log('Catégories disponibles:', filterOptions.categories);
                console.log('Structure dynamique:', filterOptions.dynamic_structure);
                
                populateCategorySelect();
            } else {
                throw new Error(data.message || 'Erreur lors du chargement des options');
            }
        } catch (error) {
            console.error('Erreur lors de l\'initialisation des filtres:', error);
            showError('Erreur lors du chargement des options de filtres');
        }
    }

    function populateCategorySelect() {
        if (!quickSearchCategory || !filterOptions.categories) return;
        
        const currentValue = quickSearchCategory.value;
        quickSearchCategory.innerHTML = '<option value="">Toutes les catégories</option>';
        
        filterOptions.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.slug;
            option.textContent = category.name;
            if (category.slug === currentValue) {
                option.selected = true;
            }
            quickSearchCategory.appendChild(option);
        });
    }

    quickSearchCategory?.addEventListener('change', function() {
        const selectedCategory = this.value;
        console.log('Catégorie sélectionnée:', selectedCategory);
        
        resetSpecificationFilters();
        
        generateDynamicFilters(selectedCategory);
        
        performSearch();
    });

    function generateDynamicFilters(categorySlug) {
        if (!dynamicFiltersContainer) return;
        
        dynamicFiltersContainer.classList.remove('show');
        
        if (!categorySlug || categorySlug === '') {
            dynamicFiltersContainer.innerHTML = '';
            return;
        }
        
        dynamicFiltersContainer.innerHTML = `
            <div class="filters-loading">
                <i class="fas fa-spinner fa-spin"></i>
                Chargement des filtres...
            </div>
        `;
        dynamicFiltersContainer.classList.add('show');
        
        setTimeout(() => {
            generateFiltersContent(categorySlug);
        }, 300);
    }

    function generateFiltersContent(categorySlug) {
        console.log('Génération des filtres pour:', categorySlug);
        console.log('Options disponibles pour cette catégorie:', filterOptions[categorySlug]);


        const categoryOptions = filterOptions[categorySlug];
        const dynamicStructure = filterOptions.dynamic_structure;
    
        if (!categoryOptions || !dynamicStructure || !dynamicStructure[categorySlug]) {
            dynamicFiltersContainer.innerHTML = `
                <div class="no-options">
                    <i class="fas fa-info-circle"></i>
                    Aucun filtre spécifique disponible pour cette catégorie.
                </div>
            `;
            return;
        }
    
        let filtersHTML = `
            <h4 class="filters-title">
                <i class="fas fa-filter"></i>
                Filtres spécifiques
            </h4>
            <div class="filter-group">
        `;
    
        const fieldsForCategory = dynamicStructure[categorySlug];
        const fieldLabels = {
            'brand': { label: 'Marque', icon: 'fas fa-tag' },
            'model': { label: 'Modèle', icon: 'fas fa-barcode' },
            'ram': { label: 'RAM', icon: 'fas fa-memory' },
            'storage': { label: 'Stockage', icon: 'fas fa-hdd' },
            'processor': { label: 'Processeur', icon: 'fas fa-microchip' },
            'graphics_card': { label: 'Carte Graphique', icon: 'fas fa-tv' },
            'camera': { label: 'Appareil photo', icon: 'fas fa-camera' },
            'battery': { label: 'Batterie', icon: 'fas fa-battery-full' },
            'screen_size': { label: 'Taille Écran', icon: 'fas fa-expand' },
            'os': { label: 'Système OS', icon: 'fas fa-desktop' },
            'color': { label: 'Couleur', icon: 'fas fa-palette' },
            'network': { label: 'Réseau', icon: 'fas fa-wifi' }
        };
    
        fieldsForCategory.forEach(field => {
            if (categoryOptions[field] && categoryOptions[field].length > 0) {
                const fieldConfig = fieldLabels[field] || { label: field, icon: 'fas fa-cog' };
                filtersHTML += createSelectField(field, fieldConfig.label, fieldConfig.icon, categoryOptions[field]);
            }
        });
        
        filtersHTML += '</div>';
        
        const priceOptions = filterOptions.price;
        if (priceOptions) {
            filtersHTML += `
                <h4 class="filters-title">
                    <i class="fas fa-tag"></i>
                    Fourchette de prix
                </h4>
                <div class="price-group">
                    <div class="filter-field">
                        <label for="priceMin">
                            <i class="fas fa-arrow-down"></i>
                            Prix minimum
                        </label>
                        <input type="number" id="priceMin" name="priceMin" 
                               placeholder="Min (${Math.floor(priceOptions.min)} DH)"
                               min="${Math.floor(priceOptions.min)}" 
                               max="${Math.ceil(priceOptions.max)}">
                    </div>
                    <div class="filter-field">
                        <label for="priceMax">
                            <i class="fas fa-arrow-up"></i>
                            Prix maximum
                        </label>
                        <input type="number" id="priceMax" name="priceMax" 
                               placeholder="Max (${Math.ceil(priceOptions.max)} DH)"
                               min="${Math.floor(priceOptions.min)}" 
                               max="${Math.ceil(priceOptions.max)}">
                    </div>
                </div>
            `;
        }
        
        filtersHTML += `
            <button type="button" class="apply-filters-btn" id="applyFiltersBtn">
                <i class="fas fa-search"></i>
                Appliquer les filtres
            </button>
            <button type="button" class="reset-filters-btn" id="resetFiltersBtn">
                <i class="fas fa-undo"></i>
                Réinitialiser
            </button>
        `;
        
        dynamicFiltersContainer.innerHTML = filtersHTML;
        
        attachFilterEventListeners();
    }

    function createSelectField(name, label, icon, options) {
        const uniqueOptions = [...new Set(options)].filter(option => option && option.trim() !== '');
        
        if (uniqueOptions.length === 0) return '';
        
        let html = `
            <div class="filter-field">
                <label for="${name}Filter">
                    <i class="${icon}"></i>
                    ${label}
                </label>
                <select id="${name}Filter" name="${name}">
                    <option value="">Tous</option>
        `;
        
        uniqueOptions.sort().forEach(option => {
            html += `<option value="${option}">${option}</option>`;
        });
        
        html += `
                </select>
            </div>
        `;
        
        return html;
    }

    // === ATTACHEMENT DES EVENT LISTENERS ===
    function attachFilterEventListeners() {
        // Bouton d'application
        const applyBtn = document.getElementById('applyFiltersBtn');
        if (applyBtn) {
            applyBtn.addEventListener('click', performSearch);
        }
        
        // Bouton de reset
        const resetBtn = document.getElementById('resetFiltersBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', resetAllFilters);
        }
        
        // Event listeners pour tous les champs de filtre
        const filterInputs = dynamicFiltersContainer.querySelectorAll('select, input');
        filterInputs.forEach(input => {
            if (input.type === 'number') {
                input.addEventListener('input', debounce(performSearch, 800));
            } else {
                input.addEventListener('change', performSearch);
            }
        });
    }

    // === RECHERCHE PRINCIPALE ===
    async function performSearch() {
        if (currentRequest) {
            currentRequest.abort();
        }
        
        showSearchLoading();
        
        try {
            const params = buildSearchParams();
            console.log('Paramètres de recherche:', params.toString());
            
            const controller = new AbortController();
            currentRequest = controller;
            
            const response = await fetch(`get_filtered_products.php?${params.toString()}`, {
                signal: controller.signal
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Erreur lors de la recherche');
            }
            
            displaySearchResults(data.products);
            updateActiveFiltersDisplay();
            
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            
            console.error('Erreur de recherche:', error);
            showError(error.message);
        } finally {
            hideSearchLoading();
            currentRequest = null;
        }
    }

    function buildSearchParams() {
        const params = new URLSearchParams();
        
        if (quickSearch && quickSearch.value.trim()) {
            params.append('search', quickSearch.value.trim());
            activeFilters.search = quickSearch.value.trim();
        } else {
            delete activeFilters.search;
        }
        
        if (quickSearchCategory && quickSearchCategory.value) {
            params.append('category', quickSearchCategory.value);
            activeFilters.category = quickSearchCategory.value;
        } else {
            delete activeFilters.category;
        }
        
        const filterInputs = dynamicFiltersContainer.querySelectorAll('select, input');
        filterInputs.forEach(input => {
            if (input.value && input.value.trim() !== '') {
                console.log(`Filter ${input.name}: "${input.value}" (original)`, `"${input.value.trim()}" (trimmed)`);
                params.append(input.name, input.value.trim());
                activeFilters[input.name] = input.value.trim();
            } else {
                delete activeFilters[input.name];
            }
        });
        
        params.append('quickSearch', '1');
        
        return params;
    }

    function displaySearchResults(products) {
        if (!products || products.length === 0) {
            searchResults.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Aucun résultat trouvé</p>
                    <small>Essayez avec d'autres critères</small>
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
                    ${generateProductSpecs(product)}
                </div>
            </div>
        `).join('');
    }

    // === GÉNÉRATION DES SPÉCIFICATIONS PRODUIT ===
    function generateProductSpecs(product) {
        const specs = [];
        if (product.ram) specs.push(`<i class="fas fa-memory"></i> ${product.ram}`);
        if (product.storage) specs.push(`<i class="fas fa-hdd"></i> ${product.storage}`);
        if (product.processor) specs.push(`<i class="fas fa-microchip"></i> ${product.processor}`);
        if (product.camera) specs.push(`<i class="fas fa-camera"></i> ${product.camera}`);
        if (product.battery) specs.push(`<i class="fas fa-battery-full"></i> ${product.battery}`);
        
        if (specs.length === 0) return '';
        
        return `
            <div class="result-specs">
                ${specs.slice(0, 2).map(spec => `<span class="spec-badge">${spec}</span>`).join('')}
            </div>
        `;
    }

    function updateActiveFiltersDisplay() {
        if (!activeFiltersContainer) return;
        
        const filterCount = Object.keys(activeFilters).length;
        
        if (filterCount === 0) {
            activeFiltersContainer.innerHTML = '';
            quickSearchCategory.classList.remove('has-filters');
            return;
        }
        
        quickSearchCategory.classList.add('has-filters');
        
        const filterTags = Object.entries(activeFilters).map(([key, value]) => {
            const label = getFilterLabel(key);
            let displayValue = value;
            
            if (key === 'category') {
                const categoryData = filterOptions.categories?.find(cat => cat.slug === value);
                displayValue = categoryData ? categoryData.name : value;
            }
            
            return `
                <div class="filter-tag">
                    ${label}: ${displayValue}
                    <button class="remove-tag" onclick="removeFilter('${key}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }).join('');
        
        activeFiltersContainer.innerHTML = filterTags;
    }

    function getFilterLabel(key) {
        const labels = {
            search: 'Recherche',
            category: 'Catégorie',
            priceMin: 'Prix min',
            priceMax: 'Prix max',
            brand: 'Marque',
            model: 'Modèle',
            ram: 'RAM',
            storage: 'Stockage',
            processor: 'Processeur',
            graphics_card: 'Carte Graphique',
            camera: 'Appareil photo',
            battery: 'Batterie',
            screen_size: 'Taille Écran',
            os: 'Système OS',
            color: 'Couleur',
            network: 'Réseau'
        };
        return labels[key] || key;
    }

    // === SUPPRESSION D'UN FILTRE ===
    window.removeFilter = function(filterKey) {
        delete activeFilters[filterKey];
        
        // Réinitialiser le champ correspondant
        if (filterKey === 'search') {
            quickSearch.value = '';
        } else if (filterKey === 'category') {
            quickSearchCategory.value = '';
            generateDynamicFilters('');
        } else {
            const input = document.getElementById(filterKey + 'Filter') || document.getElementById(filterKey);
            if (input) input.value = '';
        }
        
        performSearch();
    };

    // === RESET DE TOUS LES FILTRES ===
    function resetAllFilters() {
        // Réinitialiser les champs
        if (quickSearch) quickSearch.value = '';
        if (quickSearchCategory) quickSearchCategory.value = '';
        
        // Réinitialiser les filtres actifs
        activeFilters = {};
        
        // Cacher les filtres dynamiques
        if (dynamicFiltersContainer) {
            dynamicFiltersContainer.classList.remove('show');
            dynamicFiltersContainer.innerHTML = '';
        }
        
        // Effectuer une recherche vide
        performSearch();
    }

    // === RESET DES FILTRES DE SPÉCIFICATIONS ===
    function resetSpecificationFilters() {
        ['brand', 'model', 'ram', 'storage', 'processor', 'graphics_card', 'camera', 'battery', 
         'screen_size', 'os', 'color', 'network', 'priceMin', 'priceMax'].forEach(key => {
            delete activeFilters[key];
        });
    }

    // === RECHERCHE TEXTUELLE AVEC DEBOUNCE ===
    quickSearch?.addEventListener('input', debounce(performSearch, 500));

    // === UTILITAIRES ===
    function debounce(func, wait) {
        return function executedFunction(...args) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function showSearchLoading() {
        searchResults.innerHTML = `
            <div class="search-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Recherche en cours...</p>
            </div>
        `;
    }

    function hideSearchLoading() {
        // Le loading sera remplacé par les résultats
    }

    function showError(message) {
        searchResults.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </div>
        `;
    }

    // === INITIALISATION ===
    initializeFilters();
});