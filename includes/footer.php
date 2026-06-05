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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-instagram"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </a>
                        <a href="#" class="footer__social-link" aria-label="Facebook" data-tooltip="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-facebook"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
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
                                Jl. Sidodadi Timur No. 93, Kota Semarang
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

    <!-- ============================================
         CHATBOT WIDGET
         ============================================ -->
    <button class="chatbot-toggle" id="chatbotToggle" aria-label="Tanya Asisten AI">
        <i data-lucide="message-square" style="width: 24px; height: 24px;"></i>
    </button>

    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="chatbot-avatar">
                    <i data-lucide="bot" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #fff;">Asisten IF Barber</h4>
                    <span style="font-size: 11px; color: #4caf50; display: flex; align-items: center; gap: 4px;">
                        <span style="width: 6px; height: 6px; background: #4caf50; border-radius: 50%; display: inline-block;"></span> Online
                    </span>
                </div>
            </div>
            <button class="chatbot-close" id="chatbotClose" aria-label="Tutup Chat">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-msg chatbot-msg--bot">
                <div class="chatbot-bubble">
                    Halo! Saya asisten virtual cerdas IF Barber. Ada yang bisa saya bantu terkait jadwal, layanan, atau harga kami?
                </div>
            </div>
        </div>
        <div class="chatbot-footer">
            <form id="chatbotForm" style="display: flex; gap: 10px; width: 100%;">
                <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Tulis pertanyaan Anda..." autocomplete="off">
                <button type="submit" class="chatbot-send" aria-label="Kirim">
                    <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/chatbot.js"></script>

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
