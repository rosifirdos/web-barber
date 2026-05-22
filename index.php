<?php
/**
 * IF Barber — Landing Page / Company Profile
 * Halaman utama yang menampilkan profil barbershop
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch data from database
$layananList = getLayananAktif($conn);
$barberList = getBarberAktif($conn);

// Map barber photos
$barberPhotos = [
    1 => 'barber-reza.png',
    2 => 'barber-dimas.png',
    3 => 'barber-arief.png',
    4 => 'barber-bayu.png'
];

// Service icons (Lucide icon names)
$serviceIcons = [
    'Regular Haircut' => 'scissors',
    'Premium Haircut' => 'crown',
    'Shaving' => 'flame',
    'Beard Trim & Shape' => 'user',
    'Hair Coloring' => 'palette',
    'Fade Cut' => 'layers',
    'Kids Haircut' => 'baby',
    'Hair Treatment' => 'sparkles'
];

$pageTitle = APP_NAME . ' — ' . APP_TAGLINE;
$pageDesc = 'IF Barber — Barbershop premium terbaik. Booking jadwal potong rambut online, layanan grooming profesional, dan pengalaman premium.';
$activePage = 'home';

include __DIR__ . '/includes/header.php';
?>

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero" id="home">
    <div class="hero__bg">
        <img src="<?= BASE_URL ?>/assets/img/hero-bg.png" alt="IF Barber Interior" class="hero__bg-img">
        <div class="hero__overlay"></div>
    </div>
    <div class="bg-grid"></div>

    <div class="container hero__content">
        <div class="hero__text animate-on-scroll">
            <span class="hero__label">
                <i data-lucide="scissors" style="width:14px;height:14px;"></i>
                Est. 2024 — Premium Barbershop
            </span>
            <h1 class="hero__title">
                Where Style<br>
                Meets <span class="text-accent">Precision</span>
            </h1>
            <p class="hero__subtitle">
                Pengalaman grooming premium dengan barber profesional berpengalaman.
                Booking jadwal Anda sekarang dan rasakan perbedaannya.
            </p>
            <div class="hero__actions">
                <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary btn--lg">
                    <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
                    Book Appointment
                </a>
                <a href="#services" class="btn btn--secondary btn--lg">
                    Explore Services
                </a>
            </div>
        </div>

        <!-- Stats Strip -->
        <div class="hero__stats animate-on-scroll delay-2">
            <div class="hero__stat">
                <span class="hero__stat-value" data-count="2500">0</span>
                <span class="hero__stat-label">Happy Clients</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-value" data-count="<?= count($barberList) ?>"><?= count($barberList) ?></span>
                <span class="hero__stat-label">Expert Barbers</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-value" data-count="<?= count($layananList) ?>"><?= count($layananList) ?></span>
                <span class="hero__stat-label">Services</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-value">4.9<small>★</small></span>
                <span class="hero__stat-label">Rating</span>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="hero__scroll">
        <div class="hero__scroll-line"></div>
        <span>Scroll</span>
    </div>
</section>


<!-- ============================================
     ABOUT SECTION
     ============================================ -->
<section class="section section--alt" id="about">
    <div class="container">
        <div class="about__grid">
            <div class="about__content animate-on-scroll">
                <span class="section__label">About Us</span>
                <h2 class="section__title" style="text-align:left;">
                    Lebih dari Sekedar<br>
                    <span class="text-accent">Potong Rambut</span>
                </h2>
                <h2 class="section__title" style="text-align:left;"></h2>
                <p class="about__text">
                    IF Barber hadir sebagai barbershop premium yang mengutamakan kualitas dan pengalaman pelanggan.
                    Dengan tim barber profesional berpengalaman, kami menjamin setiap kunjungan Anda akan menjadi
                    pengalaman yang tak terlupakan.
                </p>
                <p class="about__text">
                    Didirikan pada tahun 2024, kami berkomitmen untuk menghadirkan layanan grooming terbaik
                    dengan standar internasional namun tetap terjangkau. Kami percaya bahwa setiap pria
                    berhak mendapatkan penampilan terbaik.
                </p>

                <div class="about__features">
                    <div class="about__feature">
                        <div class="about__feature-icon">
                            <i data-lucide="award"></i>
                        </div>
                        <div>
                            <strong>Barber Tersertifikasi</strong>
                            <p>Tim kami telah mengikuti pelatihan profesional</p>
                        </div>
                    </div>
                    <div class="about__feature">
                        <div class="about__feature-icon">
                            <i data-lucide="shield-check"></i>
                        </div>
                        <div>
                            <strong>Produk Premium</strong>
                            <p>Hanya menggunakan produk grooming berkualitas tinggi</p>
                        </div>
                    </div>
                    <div class="about__feature">
                        <div class="about__feature-icon">
                            <i data-lucide="clock"></i>
                        </div>
                        <div>
                            <strong>Booking Online</strong>
                            <p>Reservasi mudah tanpa perlu antre</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="about__visual animate-on-scroll delay-2">
                <div class="about__image-wrapper">
                    <img src="<?= BASE_URL ?>/assets/img/gallery-2.png" alt="Barber Tools" class="about__image">
                    <div class="about__image-accent"></div>
                </div>
                <div class="about__experience glass">
                    <span class="about__exp-number">8+</span>
                    <span class="about__exp-text">Tahun<br>Pengalaman</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================
     SERVICES SECTION
     ============================================ -->
<section class="section" id="services">
    <div class="container">
        <div class="section__header animate-on-scroll">
            <span class="section__label">Our Services</span>
            <h2 class="section__title">Layanan Kami</h2>
            <p class="section__subtitle">
                Berbagai pilihan layanan grooming premium yang disesuaikan dengan kebutuhan dan gaya Anda.
            </p>
        </div>

        <div class="services__grid">
            <?php foreach ($layananList as $index => $layanan): ?>
            <div class="service-card glass animate-on-scroll delay-<?= ($index % 4) + 1 ?>">
                <div class="service-card__icon">
                    <i data-lucide="<?= $serviceIcons[$layanan['nama']] ?? 'scissors' ?>"></i>
                </div>
                <h3 class="service-card__title"><?= e($layanan['nama']) ?></h3>
                <p class="service-card__text"><?= e($layanan['deskripsi']) ?></p>
                <div class="service-card__footer">
                    <span class="service-card__price"><?= formatRupiah($layanan['harga']) ?></span>
                    <span class="service-card__duration">
                        <i data-lucide="clock" style="width:14px;height:14px;"></i>
                        <?= $layanan['durasi_menit'] ?> min
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-3 animate-on-scroll">
            <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary btn--lg">
                <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
                Book Now
            </a>
        </div>
    </div>
</section>


<!-- ============================================
     BARBERS SECTION
     ============================================ -->
<section class="section section--alt" id="barbers">
    <div class="container">
        <div class="section__header animate-on-scroll">
            <span class="section__label">Our Team</span>
            <h2 class="section__title">Meet Our Barbers</h2>
            <p class="section__subtitle">
                Tim barber profesional kami siap memberikan pengalaman grooming terbaik untuk Anda.
            </p>
        </div>

        <div class="barbers__grid">
            <?php foreach ($barberList as $index => $barber): ?>
            <div class="barber-card animate-on-scroll delay-<?= ($index % 4) + 1 ?>">
                <div class="barber-card__image-wrapper">
                    <img
                        src="<?= BASE_URL ?>/assets/img/<?= $barberPhotos[$barber['id']] ?? 'barber-reza.png' ?>"
                        alt="<?= e($barber['nama']) ?>"
                        class="barber-card__image"
                    >
                    <div class="barber-card__overlay">
                        <a href="<?= BASE_URL ?>/booking.php?barber=<?= $barber['id'] ?>" class="btn btn--primary btn--sm">
                            Book with <?= e(explode(' ', $barber['nama'])[0]) ?>
                        </a>
                    </div>
                </div>
                <div class="barber-card__info">
                    <h3 class="barber-card__name"><?= e($barber['nama']) ?></h3>
                    <p class="barber-card__bio"><?= e(substr($barber['bio'], 0, 100)) ?>...</p>
                    <div class="barber-card__skills">
                        <?php
                        $skills = explode(', ', $barber['spesialisasi']);
                        foreach (array_slice($skills, 0, 3) as $skill): ?>
                            <span class="tag"><?= e($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ============================================
     GALLERY SECTION
     ============================================ -->
<section class="section" id="gallery">
    <div class="container">
        <div class="section__header animate-on-scroll">
            <span class="section__label">Gallery</span>
            <h2 class="section__title">Our Space</h2>
            <p class="section__subtitle">
                Intip suasana premium IF Barber yang nyaman dan stylish.
            </p>
        </div>

        <div class="gallery__grid animate-on-scroll">
            <div class="gallery__item gallery__item--large">
                <img src="<?= BASE_URL ?>/assets/img/hero-bg.png" alt="IF Barber Interior" class="gallery__img">
                <div class="gallery__caption">Interior Premium</div>
            </div>
            <div class="gallery__item">
                <img src="<?= BASE_URL ?>/assets/img/gallery-1.png" alt="Haircut Process" class="gallery__img">
                <div class="gallery__caption">Expert Grooming</div>
            </div>
            <div class="gallery__item">
                <img src="<?= BASE_URL ?>/assets/img/gallery-2.png" alt="Barber Tools" class="gallery__img">
                <div class="gallery__caption">Premium Tools</div>
            </div>
            <div class="gallery__item">
                <img src="<?= BASE_URL ?>/assets/img/gallery-3.png" alt="Result" class="gallery__img">
                <div class="gallery__caption">Perfect Result</div>
            </div>
            <div class="gallery__item">
                <img src="<?= BASE_URL ?>/assets/img/gallery-4.png" alt="Lounge Area" class="gallery__img">
                <div class="gallery__caption">Comfortable Lounge</div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================
     TESTIMONIALS SECTION
     ============================================ -->
<section class="section section--alt" id="testimonials">
    <div class="container">
        <div class="section__header animate-on-scroll">
            <span class="section__label">Testimonials</span>
            <h2 class="section__title">What Clients Say</h2>
            <p class="section__subtitle">
                Dengarkan pengalaman pelanggan kami yang telah mempercayakan penampilan mereka kepada IF Barber.
            </p>
        </div>

        <div class="testimonials__grid">
            <div class="testimonial-card glass animate-on-scroll delay-1">
                <div class="testimonial-card__stars">★★★★★</div>
                <p class="testimonial-card__text">
                    "Tempat potong rambut terbaik! Barbernya ramah dan profesional. Hasilnya selalu memuaskan.
                    Sistem booking online-nya juga sangat memudahkan, tidak perlu antre lama."
                </p>
                <div class="testimonial-card__author">
                    <div class="testimonial-card__avatar">AF</div>
                    <div>
                        <strong>Ahmad Fadli</strong>
                        <span class="text-muted text-sm">Pelanggan Tetap</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card glass animate-on-scroll delay-2">
                <div class="testimonial-card__stars">★★★★★</div>
                <p class="testimonial-card__text">
                    "Suasananya keren banget, gelap premium gitu. Barbernya paham banget sama request saya.
                    Fade cut-nya rapi banget. Pasti balik lagi!"
                </p>
                <div class="testimonial-card__author">
                    <div class="testimonial-card__avatar">BS</div>
                    <div>
                        <strong>Budi Santoso</strong>
                        <span class="text-muted text-sm">Mahasiswa</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card glass animate-on-scroll delay-3">
                <div class="testimonial-card__stars">★★★★★</div>
                <p class="testimonial-card__text">
                    "Saya sudah coba berbagai barbershop, tapi IF Barber yang paling konsisten kualitasnya.
                    Hair treatment-nya juga bagus. Rambut jadi lebih sehat."
                </p>
                <div class="testimonial-card__author">
                    <div class="testimonial-card__avatar">RW</div>
                    <div>
                        <strong>Rizky Wijaya</strong>
                        <span class="text-muted text-sm">Profesional</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================
     CTA SECTION
     ============================================ -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card glass animate-on-scroll">
            <div class="cta-card__content">
                <h2 class="cta-card__title">Ready for Your<br><span class="text-accent">Best Look?</span></h2>
                <p class="cta-card__text">
                    Book jadwal potong rambut Anda sekarang. Gratis konsultasi gaya rambut dengan barber profesional kami.
                </p>
                <div class="cta-card__actions">
                    <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary btn--lg">
                        <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
                        Book Appointment
                    </a>
                    <a href="https://wa.me/6281234567890" class="btn btn--secondary btn--lg" target="_blank">
                        <i data-lucide="message-circle" style="width:18px;height:18px;"></i>
                        Chat WhatsApp
                    </a>
                </div>
            </div>
            <div class="cta-card__decor">
                <div class="cta-card__circle"></div>
                <div class="cta-card__circle cta-card__circle--2"></div>
            </div>
        </div>
    </div>
</section>


<!-- Hero Counter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('[data-count]');
    if (counters.length > 0 && 'IntersectionObserver' in window) {
        const obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.count);
                    if (!isNaN(target)) {
                        animateCounter(el, target, 2000);
                    }
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function(c) { obs.observe(c); });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
