document.addEventListener("DOMContentLoaded", function () {
  const productsGrid = document.querySelector(".products-grid");
  const filterAside = document.querySelector(".filter-aside");
  const filterToggle = document.querySelector(".filter-toggle");

  function loadFilterOptions() {
    fetch("get_filtered_products.php?get_options=1")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          updateFilterOptions(data.options);
        }
      })
      .catch((error) => console.error("Error loading filter options:", error));
  }

  function updateFilterOptions(options) {
    const categoryFilter = document.getElementById("categoryFilter");
    categoryFilter.innerHTML = `
                    <option value="">Toutes les catégories</option>
                    ${options.categories
                      .map(
                        (category) => `
                        <option value="${category}">${
                          category === "pc"
                            ? "Ordinateurs"
                            : category === "mobile"
                            ? "Mobiles"
                            : category === "accessory"
                            ? "Accessoires"
                            : category
                        }</option>
                    `
                      )
                      .join("")}
                `;

    if (options.pc) {
      const ramSelect = document.getElementById("ramFilter");
      const storageSelect = document.getElementById("ssdFilter");
      const processorSelect = document.getElementById("processorFilter");

      if (ramSelect) {
        ramSelect.innerHTML = `
                            <option value="">RAM</option>
                            ${options.pc.ram
                              .sort()
                              .map(
                                (ram) =>
                                  `<option value="${ram}">${ram}</option>`
                              )
                              .join("")}
                        `;
      }

      if (storageSelect) {
        storageSelect.innerHTML = `
                            <option value="">Stockage</option>
                            ${options.pc.storage
                              .sort()
                              .map(
                                (storage) =>
                                  `<option value="${storage}">${storage}</option>`
                              )
                              .join("")}
                        `;
      }

      if (processorSelect) {
        processorSelect.innerHTML = `
                            <option value="">Processeur</option>
                            ${options.pc.processor
                              .sort()
                              .map(
                                (processor) =>
                                  `<option value="${processor}">${processor}</option>`
                              )
                              .join("")}
                        `;
      }
    }

    if (options.mobile) {
      const cameraSelect = document.getElementById("cameraFilter");
      const batterySelect = document.getElementById("batteryFilter");
      const storageSelect = document.getElementById("storageFilter");

      if (cameraSelect) {
        cameraSelect.innerHTML = `
                            <option value="">Appareil photo</option>
                            ${options.mobile.camera
                              .sort()
                              .map(
                                (camera) =>
                                  `<option value="${camera}">${camera}</option>`
                              )
                              .join("")}
                        `;
      }

      if (batterySelect) {
        batterySelect.innerHTML = `
                            <option value="">Batterie</option>
                            ${options.mobile.battery
                              .sort()
                              .map(
                                (battery) =>
                                  `<option value="${battery}">${battery}</option>`
                              )
                              .join("")}
                        `;
      }

      if (storageSelect) {
        storageSelect.innerHTML = `
                            <option value="">Stockage</option>
                            ${options.mobile.storage
                              .sort()
                              .map(
                                (storage) =>
                                  `<option value="${storage}">${storage}</option>`
                              )
                              .join("")}
                        `;
      }
    }

    if (options.price) {
      const priceMin = document.getElementById("priceMin");
      const priceMax = document.getElementById("priceMax");

      if (priceMin && priceMax) {
        priceMin.placeholder = `Min (${Math.floor(options.price.min)} DH)`;
        priceMax.placeholder = `Max (${Math.ceil(options.price.max)} DH)`;
        priceMin.min = Math.floor(options.price.min);
        priceMax.max = Math.ceil(options.price.max);
      }
    }
  }
  const searchInput = document.getElementById("productSearch");
  const categoryFilter = document.getElementById("categoryFilter");
  const pcFilters = document.getElementById("pc-filters");
  const mobileFilters = document.getElementById("mobile-filters");
  const resetFilters = document.getElementById("resetFilters");
  const activeFilters = document.getElementById("activeFilters");
  const priceMin = document.getElementById("priceMin");
  const priceMax = document.getElementById("priceMax");

  let currentFilters = {};
  let searchTimeout;

  const loadingSpinner = document.createElement("div");
  loadingSpinner.className = "loading-spinner";
  loadingSpinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  filterToggle.addEventListener("click", () => {
    filterAside.classList.toggle("active");
  });

  searchInput.addEventListener(
    "input",
    debounce(function (e) {
      currentFilters.search = e.target.value;
      updateActiveFilters();
      fetchFilteredProducts();
    }, 500)
  );
  loadFilterOptions();

  categoryFilter.addEventListener("change", function () {
    currentFilters.category = this.value;
    pcFilters.style.display = this.value === "pc" ? "block" : "none";
    mobileFilters.style.display = this.value === "mobile" ? "block" : "none";

    if (this.value === "pc") {
      ["cameraFilter", "batteryFilter", "storageFilter"].forEach((id) => {
        const element = document.getElementById(id);
        if (element) element.value = "";
        delete currentFilters[id];
      });
    } else if (this.value === "mobile") {
      ["ramFilter", "ssdFilter", "processorFilter"].forEach((id) => {
        const element = document.getElementById(id);
        if (element) element.value = "";
        delete currentFilters[id];
      });
    }

    updateActiveFilters();
    fetchFilteredProducts();
  });

  [priceMin, priceMax].forEach((input) => {
    input.addEventListener("change", function () {
      currentFilters.priceMin = priceMin.value || null;
      currentFilters.priceMax = priceMax.value || null;
      updateActiveFilters();
      fetchFilteredProducts();
    });
  });

  ["ramFilter", "ssdFilter", "processorFilter"].forEach((filterId) => {
    document.getElementById(filterId)?.addEventListener("change", function () {
      currentFilters[filterId] = this.value;
      updateActiveFilters();
      fetchFilteredProducts();
    });
  });

  ["cameraFilter", "batteryFilter", "storageFilter"].forEach((filterId) => {
    document.getElementById(filterId)?.addEventListener("change", function () {
      currentFilters[filterId] = this.value;
      updateActiveFilters();
      fetchFilteredProducts();
    });
  });
  resetFilters.addEventListener("click", function () {
    searchInput.value = "";
    categoryFilter.value = "";
    priceMin.value = "";
    priceMax.value = "";
    document
      .querySelectorAll(".filter-dropdown")
      .forEach((select) => (select.value = ""));

    pcFilters.style.display = "none";
    mobileFilters.style.display = "none";
    currentFilters = {};

    resetFilters.classList.add("spinning");
    setTimeout(() => resetFilters.classList.remove("spinning"), 500);

    loadFilterOptions();
    updateActiveFilters();
    fetchFilteredProducts();
  });

  function updateActiveFilters() {
    activeFilters.innerHTML = "";

    Object.entries(currentFilters).forEach(([key, value]) => {
      if (value && value !== "") {
        const badge = document.createElement("span");
        badge.className = "filter-badge";
        badge.innerHTML = `
                            ${getFilterLabel(key)}: ${value}
                            <i class="fas fa-times" data-filter="${key}"></i>
                        `;
        activeFilters.appendChild(badge);

        badge.querySelector("i").addEventListener("click", function () {
          const filterKey = this.dataset.filter;
          delete currentFilters[filterKey];

          const element = document.getElementById(filterKey);
          if (element) element.value = "";

          updateActiveFilters();
          fetchFilteredProducts();
        });
      }
    });
  }

  function getFilterLabel(key) {
    const labels = {
      search: "Recherche",
      category: "Catégorie",
      priceMin: "Prix min",
      priceMax: "Prix max",
      ramFilter: "RAM",
      ssdFilter: "Stockage PC",
      processorFilter: "Processeur",
      cameraFilter: "Appareil photo",
      batteryFilter: "Batterie",
      storageFilter: "Stockage Mobile",
    };
    return labels[key] || key;
  }

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

  function fetchFilteredProducts() {
    productsGrid.style.opacity = "0.5";
    productsGrid.appendChild(loadingSpinner);

    const queryParams = new URLSearchParams();
    Object.entries(currentFilters).forEach(([key, value]) => {
      if (value) queryParams.append(key, value);
    });

    fetch(`get_filtered_products.php?${queryParams.toString()}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          updateProductsGrid(data.products);
        } else {
          console.error("Error:", data.message);
        }
      })
      .catch((error) => console.error("Error:", error))
      .finally(() => {
        productsGrid.removeChild(loadingSpinner);
        productsGrid.style.opacity = "1";
      });
  }

  function updateProductsGrid(products) {
    productsGrid.style.opacity = "0";

    setTimeout(() => {
      if (products.length === 0) {
        productsGrid.innerHTML = `
                            <div class="no-results">
                                <i class="fas fa-search"></i>
                                <p>Aucun produit ne correspond à vos critères de recherche</p>
                            </div>
                        `;
      } else {
        productsGrid.innerHTML = products
          .map(
            (product) => `
                            <div class="product-card" data-category="${
                              product.category
                            }">
                                <div class="product-image-container">
                                    <img src="${product.image_url}" 
                                         alt="${product.name}" 
                                         class="product-image">
                                    ${
                                      product.stock < 5 && product.stock > 0
                                        ? `<span class="stock-warning">Plus que ${product.stock} en stock!</span>`
                                        : ""
                                    }
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">${
                                      product.name
                                    }</h3>
                                    <p class="product-description">${
                                      product.description
                                    }</p>
                                    <div class="product-specs">
                                        ${product.specs
                                          .map(
                                            (spec) => `
                                            <span class="spec-badge">
                                                <i class="fas fa-${
                                                  spec.includes("RAM")
                                                    ? "memory"
                                                    : spec.includes("Storage")
                                                    ? "hdd"
                                                    : spec.includes("Camera")
                                                    ? "camera"
                                                    : spec.includes("Battery")
                                                    ? "battery-full"
                                                    : "microchip"
                                                }"></i>
                                                ${spec}
                                            </span>
                                        `
                                          )
                                          .join("")}
                                    </div>
                                    <div class="product-price">${Number(
                                      product.price
                                    ).toFixed(2)} DH</div>
                                    ${
                                      product.stock > 0
                                        ? `<a href="#" class="product-button" onclick="addToCart(${product.id})">
                                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                        </a>`
                                        : `<button class="product-button out-of-stock" disabled>
                                            <i class="fas fa-times"></i> Rupture de stock
                                        </button>`
                                    }
                                </div>
                            </div>
                        `
          )
          .join("");
      }
      productsGrid.style.opacity = "1";
    }, 300);
  }

  fetchFilteredProducts();
});

document.addEventListener("DOMContentLoaded", function () {
  const searchToggle = document.getElementById("searchToggle");
  const searchExtension = document.getElementById("searchExtension");
  const quickSearch = document.getElementById("quickSearch");
  const quickSearchCategory = document.getElementById("quickSearchCategory");
  const searchResults = document.getElementById("searchResults");

  searchToggle.addEventListener("click", function () {
    searchExtension.classList.toggle("active");
    quickSearch.focus();
  });

  document.addEventListener("click", function (e) {
    if (
      !searchExtension.contains(e.target) &&
      !searchToggle.contains(e.target)
    ) {
      searchExtension.classList.remove("active");
    }
  });

  quickSearch.addEventListener(
    "input",
    debounce(function () {
      const query = this.value;
      const category = quickSearchCategory.value;

      if (query.length < 3) {
        searchResults.innerHTML = "";
        return;
      }

      setTimeout(() => {
        const products = [
          {
            id: 1,
            name: "MacBook Pro M2",
            category: "pc",
            image_url: "img/best-laptops-20240516-medium.jpg",
            price: 25999,
          },
          {
            id: 2,
            name: "MSI Gaming Laptop",
            category: "pc",
            image_url: "img/laptop.jpg",
            price: 15499,
          },
          {
            id: 3,
            name: "iPhone 15 Pro",
            category: "mobile",
            image_url: "img/phone.png",
            price: 13999,
          },
        ];

        const filteredProducts = products.filter((product) => {
          const inCategory = category === "" || product.category === category;
          const inSearch = product.name
            .toLowerCase()
            .includes(query.toLowerCase());
          return inCategory && inSearch;
        });

        displaySearchResults(filteredProducts);
      }, 300);
    }, 300)
  );

  function displaySearchResults(products) {
    searchResults.innerHTML = "";

    if (products.length === 0) {
      searchResults.innerHTML =
        '<div class="no-results">Aucun résultat trouvé</div>';
      return;
    }

    products.forEach((product) => {
      const div = document.createElement("div");
      div.className = "search-result";
      div.innerHTML = `
                        <img src="${product.image_url}" alt="${product.name}" class="result-image">
                        <div class="result-info">
                            <h4 class="result-title">${product.name}</h4>
                            <div class="result-price">${product.price} DH</div>
                        </div>
                    `;
      div.addEventListener("click", () => {
        window.location.href = `product.php?id=${product.id}`;
      });
      searchResults.appendChild(div);
    });
  }
});