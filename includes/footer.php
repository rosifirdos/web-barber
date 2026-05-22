    </main>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer__grid">
                <!-- Brand -->
                <div>
                    <div class="footer__brand">IF Barber</div>
                    <p class="footer__desc">
                        Barbershop premium dengan pelayanan terbaik. Kami mengutamakan kualitas, kenyamanan, dan kepuasan setiap pelanggan.
                    </p>
                    <div class="footer__social">
                        <a href="#" class="footer__social-link" aria-label="Instagram" data-tooltip="Instagram">
                            <i data-lucide="instagram"></i>
                        </a>
                        <a href="#" class="footer__social-link" aria-label="Facebook" data-tooltip="Facebook">
                            <i data-lucide="facebook"></i>
                        </a>
                        <a href="https://wa.me/6281234567890" class="footer__social-link" aria-label="WhatsApp" data-tooltip="WhatsApp">
                            <i data-lucide="message-circle"></i>
                        </a>
                        <a href="#" class="footer__social-link" aria-label="TikTok" data-tooltip="TikTok">
                            <i data-lucide="music-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="footer__title">Quick Links</h4>
                    <ul class="footer__links">
                        <li><a href="<?= BASE_URL ?>/#home" class="footer__link">Home</a></li>
                        <li><a href="<?= BASE_URL ?>/#about" class="footer__link">About Us</a></li>
                        <li><a href="<?= BASE_URL ?>/#services" class="footer__link">Services</a></li>
                        <li><a href="<?= BASE_URL ?>/booking.php" class="footer__link">Booking</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h4 class="footer__title">Services</h4>
                    <ul class="footer__links">
                        <li><a href="#" class="footer__link">Haircut</a></li>
                        <li><a href="#" class="footer__link">Shaving</a></li>
                        <li><a href="#" class="footer__link">Coloring</a></li>
                        <li><a href="#" class="footer__link">Treatment</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="footer__title">Contact</h4>
                    <ul class="footer__links">
                        <li>
                            <span class="footer__link" style="cursor:default;">
                                <i data-lucide="map-pin" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                                Jl. Contoh No. 123, Kota
                            </span>
                        </li>
                        <li>
                            <a href="tel:+6281234567890" class="footer__link">
                                <i data-lucide="phone" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                                0812-3456-7890
                            </a>
                        </li>
                        <li>
                            <span class="footer__link" style="cursor:default;">
                                <i data-lucide="clock" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                                <?= JAM_BUKA ?> - <?= JAM_TUTUP ?>
                            </span>
                        </li>
                        <li>
                            <span class="footer__link" style="cursor:default;">
                                <i data-lucide="calendar-off" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                                Tutup hari Minggu
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer__bottom">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
                <p>Crafted with <span style="color:var(--color-error);">♥</span> by IF Barber Team</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>

    <!-- Init Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
