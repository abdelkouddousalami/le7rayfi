        
        function getQuantity() {
            return parseInt(document.getElementById('quantity').value) || 1;
        }

        async function addToCart(productId, quantity) {
            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const cartBadge = document.querySelector('.badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cartCount;
                        cartBadge.style.display = data.cartCount > 0 ? 'block' : 'none';
                    }
                    
                    showNotification('success', data.message);
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('error', 'Une erreur est survenue');
            }
        }

        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
