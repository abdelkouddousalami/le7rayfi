<footer class="footer">
    <div class="footer-wave"></div>
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-links">
                <h3>À propos de nous</h3>
                <p style="color: #ecf0f1; line-height: 1.6;">Nous fournissons des produits électroniques et accessoires de haute qualité pour améliorer votre style de vie numérique. Faites-nous confiance pour les dernières solutions technologiques.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h3>Liens rapides</h3>
                <ul>
                    <li><a href="index.php"><i class="fas fa-chevron-right"></i>Accueil</a></li>
                    <li><a href="boutique.php"><i class="fas fa-chevron-right"></i>Produits</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>À propos</a></li>
                    <li><a href="contact.php"><i class="fas fa-chevron-right"></i>Contact</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Politique de confidentialité</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Nos services</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Téléphones & Tablettes</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Ordinateurs portables</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Accessoires</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Services de réparation</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Support</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Informations de contact</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i>123 Rue du Commerce, Marrakech</li>
                    <li><i class="fas fa-phone"></i>+212 5XX-XXXXXX</li>
                    <li><i class="fas fa-envelope"></i>info@hagroup.com</li>
                    <li><i class="fas fa-clock"></i>Lun - Sam: 9:00 - 20:00</li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<a href="https://wa.me/212500000000" class="whatsapp-button">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }
});
</script>