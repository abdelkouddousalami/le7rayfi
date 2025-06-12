document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const formGroups = document.querySelectorAll('.form-group');
    
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        setTimeout(() => {
            group.style.transition = 'all 0.5s ease';
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, index * 100);
    });

    const inputs = document.querySelectorAll('.form-group input, .form-group textarea');
    inputs.forEach(input => {
        if (input.value) {
            input.parentElement.classList.add('active');
        }

        input.addEventListener('focus', () => {
            input.parentElement.classList.add('active');
        });

        input.addEventListener('blur', () => {
            if (!input.value) {
                input.parentElement.classList.remove('active');
            }
        });
    });

    const infoItems = document.querySelectorAll('.info-item');
    infoItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            item.style.animation = `fadeInLeft 0.5s ease forwards ${index * 0.2}s`;
        }, 100);
    });

    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('.submit-btn');
        const originalBtnText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        submitBtn.disabled = true;

        const formData = new FormData(this);

        try {
            const response = await fetch('process_contact.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage('success', data.message);
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Message envoyé!';
                submitBtn.style.backgroundColor = '#28a745';
                
                setTimeout(() => {
                    contactForm.reset();
                    formGroups.forEach(group => group.classList.remove('active'));
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.style.backgroundColor = '';
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                showMessage('error', data.message);
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            showMessage('error', 'Une erreur est survenue. Veuillez réessayer.');
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }
    });

    function showMessage(type, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} message-alert`;
        messageDiv.textContent = message;
        
        contactForm.parentElement.insertBefore(messageDiv, contactForm);
        
        setTimeout(() => messageDiv.remove(), 5000);
    }
});

const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);