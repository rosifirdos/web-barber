/**
 * IF Barber — Booking Form JavaScript
 * Multi-step wizard, time slot fetching, summary updates
 */

document.addEventListener('DOMContentLoaded', function () {

    var currentStep = 1;
    var selectedData = {
        layanan: null,
        layananName: '',
        layananPrice: 0,
        layananDuration: 0,
        barber: null,
        barberName: '',
        tanggal: '',
        jam: ''
    };

    // ============================================
    // Step Navigation
    // ============================================
    window.goToStep = function (step) {
        // Validate before advancing
        if (step > currentStep) {
            if (!validateStep(currentStep)) return;
        }

        // Hide current
        var currentPanel = document.getElementById('step' + currentStep);
        if (currentPanel) currentPanel.classList.remove('active');

        // Show target
        var targetPanel = document.getElementById('step' + step);
        if (targetPanel) targetPanel.classList.add('active');

        // Update step indicators
        var stepEls = document.querySelectorAll('.booking-step');
        stepEls.forEach(function (el) {
            var s = parseInt(el.dataset.step);
            el.classList.remove('active', 'completed');
            if (s === step) el.classList.add('active');
            if (s < step) el.classList.add('completed');
        });

        // Update step lines
        var lineEls = document.querySelectorAll('.booking-step__line');
        lineEls.forEach(function (line, idx) {
            if (idx < step - 1) {
                line.classList.add('completed');
            } else {
                line.classList.remove('completed');
            }
        });

        currentStep = step;

        // Update summary on step 4
        if (step === 4) updateSummary();

        // Scroll to top of form
        var header = document.querySelector('.booking-header');
        if (header) {
            header.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Re-init Lucide icons
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    function validateStep(step) {
        if (step === 1) {
            var selected = document.querySelector('input[name="layanan_id"]:checked');
            if (!selected) {
                showToast('Silakan pilih layanan terlebih dahulu.', 'warning');
                return false;
            }
            return true;
        }
        if (step === 2) {
            var selected = document.querySelector('input[name="barber_id"]:checked');
            if (!selected) {
                showToast('Silakan pilih barber terlebih dahulu.', 'warning');
                return false;
            }
            return true;
        }
        if (step === 3) {
            if (!selectedData.tanggal || !selectedData.jam) {
                showToast('Silakan pilih tanggal dan jam terlebih dahulu.', 'warning');
                return false;
            }
            return true;
        }
        return true;
    }

    // ============================================
    // Step 1: Service Selection
    // ============================================
    var serviceCards = document.querySelectorAll('.service-select-card');
    var btnStep1 = document.getElementById('btnStep1Next');

    serviceCards.forEach(function (card) {
        card.addEventListener('click', function () {
            // Deselect all
            serviceCards.forEach(function (c) { c.classList.remove('selected'); });
            // Select this
            this.classList.add('selected');
            var radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            selectedData.layanan = radio.value;
            selectedData.layananName = this.dataset.name;
            selectedData.layananPrice = parseFloat(this.dataset.price);
            selectedData.layananDuration = parseInt(this.dataset.duration);

            if (btnStep1) btnStep1.disabled = false;
        });
    });

    // ============================================
    // Step 2: Barber Selection
    // ============================================
    var barberCards = document.querySelectorAll('.barber-select-card');
    var btnStep2 = document.getElementById('btnStep2Next');

    barberCards.forEach(function (card) {
        card.addEventListener('click', function () {
            barberCards.forEach(function (c) { c.classList.remove('selected'); });
            this.classList.add('selected');
            var radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            selectedData.barber = radio.value;
            selectedData.barberName = this.dataset.name;

            if (btnStep2) btnStep2.disabled = false;
        });

        // Pre-select if URL param
        var radio = card.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            card.classList.add('selected');
            selectedData.barber = radio.value;
            selectedData.barberName = card.dataset.name;
            if (btnStep2) btnStep2.disabled = false;
        }
    });

    // ============================================
    // Step 3: Date & Time Selection
    // ============================================
    var dateInput = document.getElementById('inputTanggal');
    var timeslotGrid = document.getElementById('timeslotGrid');
    var jamInput = document.getElementById('inputJam');
    var btnStep3 = document.getElementById('btnStep3Next');

    if (dateInput) {
        dateInput.addEventListener('change', function () {
            var tanggal = this.value;
            if (!tanggal) return;

            selectedData.tanggal = tanggal;
            selectedData.jam = '';
            if (jamInput) jamInput.value = '';
            if (btnStep3) btnStep3.disabled = true;

            // Check if Sunday
            var date = new Date(tanggal + 'T00:00:00');
            if (date.getDay() === 0) {
                timeslotGrid.innerHTML = '<div class="timeslot-empty">' +
                    '<i data-lucide="calendar-off" style="width:32px;height:32px;opacity:0.3;"></i>' +
                    '<p>Maaf, barbershop tutup pada hari Minggu.</p></div>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            // Show loading
            timeslotGrid.innerHTML = '<div class="timeslot-loading">' +
                '<div class="spinner"></div> Memuat jadwal...</div>';

            // Fetch booked slots
            var barberId = selectedData.barber || 0;
            fetch(BASE_URL + '/api/get_slots.php?barber_id=' + barberId + '&tanggal=' + tanggal)
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP error ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    if (data.closed) {
                        timeslotGrid.innerHTML = '<div class="timeslot-empty">' +
                            '<i data-lucide="calendar-off" style="width:32px;height:32px;opacity:0.3;"></i>' +
                            '<p>Barbershop tutup pada hari tersebut.</p></div>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        return;
                    }
                    renderTimeSlots(data.booked || []);
                })
                .catch(function (err) {
                    console.error('API Error:', err);
                    // Fallback: render all slots as available, or show error
                    renderTimeSlots([]);
                });
        });
    }

    function renderTimeSlots(bookedSlots) {
        try {
            if (!timeslotGrid) return;

            // Try to get timeslots from PHP-generated global, fallback to JS generation
            var slots = window.ALL_TIMESLOTS;
            if (!slots || !Array.isArray(slots) || slots.length === 0) {
                // Fallback: generate 09:00 - 21:00 with 30min intervals in JS
                slots = [];
                for (var h = 9; h < 21; h++) {
                    slots.push(String(h).padStart(2, '0') + ':00');
                    slots.push(String(h).padStart(2, '0') + ':30');
                }
            }

            var now = new Date();
            // Get local date as YYYY-MM-DD instead of UTC
            var localDateStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            var isToday = selectedData.tanggal === localDateStr;
            var currentHour = now.getHours();
            var currentMin = now.getMinutes();

            var html = '';
            slots.forEach(function (slot) {
                var isBooked = (bookedSlots || []).indexOf(slot) !== -1;

                // If today, disable past time slots
                var isPast = false;
                if (isToday) {
                    var parts = slot.split(':');
                    var slotHour = parseInt(parts[0]);
                    var slotMin = parseInt(parts[1]);
                    if (slotHour < currentHour || (slotHour === currentHour && slotMin <= currentMin)) {
                        isPast = true;
                    }
                }

                var disabledClass = (isBooked || isPast) ? ' timeslot--disabled' : '';
                var label = isBooked ? 'Terisi' : (isPast ? 'Lewat' : slot);

                html += '<button type="button" class="timeslot' + disabledClass + '" ' +
                    'data-time="' + slot + '" ' +
                    (isBooked || isPast ? 'disabled' : '') + '>' +
                    '<span class="timeslot__time">' + slot + '</span>' +
                    '<span class="timeslot__status">' + label + '</span>' +
                    '</button>';
            });

            timeslotGrid.innerHTML = html;

            // Bind click
            timeslotGrid.querySelectorAll('.timeslot:not(.timeslot--disabled)').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    // Deselect all
                    timeslotGrid.querySelectorAll('.timeslot').forEach(function (b) {
                        b.classList.remove('timeslot--selected');
                    });
                    this.classList.add('timeslot--selected');
                    selectedData.jam = this.dataset.time;
                    if (jamInput) jamInput.value = this.dataset.time;
                    if (btnStep3) btnStep3.disabled = false;
                });
            });
        } catch (e) {
            console.error("Render TimeSlots Error:", e);
            timeslotGrid.innerHTML = '<div class="timeslot-empty"><p>Terjadi kesalahan sistem saat memuat jadwal.</p></div>';
        }
    }

    // ============================================
    // Step 4: Summary
    // ============================================
    function updateSummary() {
        var el;

        el = document.getElementById('summaryLayanan');
        if (el) el.textContent = selectedData.layananName || '-';

        el = document.getElementById('summaryBarber');
        if (el) el.textContent = selectedData.barberName || '-';

        el = document.getElementById('summaryTanggal');
        if (el) el.textContent = selectedData.tanggal ? formatDateID(selectedData.tanggal) : '-';

        el = document.getElementById('summaryJam');
        if (el) el.textContent = selectedData.jam ? selectedData.jam + ' WIB' : '-';

        el = document.getElementById('summaryDurasi');
        if (el) el.textContent = selectedData.layananDuration ? selectedData.layananDuration + ' menit' : '-';

        el = document.getElementById('summaryTotal');
        if (el) el.textContent = selectedData.layananPrice ? formatRupiahJS(selectedData.layananPrice) : '-';
    }

    // ============================================
    // Form Submission Validation
    // ============================================
    var bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function (e) {
            var nama = document.getElementById('inputNama');
            var hp = document.getElementById('inputHP');

            if (!nama || !nama.value.trim()) {
                e.preventDefault();
                showToast('Nama lengkap wajib diisi.', 'error');
                nama.focus();
                return;
            }

            if (!hp || !hp.value.trim()) {
                e.preventDefault();
                showToast('Nomor HP wajib diisi.', 'error');
                hp.focus();
                return;
            }

            if (!/^[0-9]{10,15}$/.test(hp.value.trim())) {
                e.preventDefault();
                showToast('Format nomor HP tidak valid (10-15 digit angka).', 'error');
                hp.focus();
                return;
            }

            // Disable submit button
            var btn = document.getElementById('btnSubmit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<div class="spinner"></div> Memproses...';
            }
        });
    }

    // ============================================
    // Utilities
    // ============================================
    function formatRupiahJS(num) {
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    function formatDateID(dateStr) {
        var hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        var bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        var d = new Date(dateStr + 'T00:00:00');
        return hari[d.getDay()] + ', ' + d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }

    function showToast(message, type) {
        // Remove existing toast
        var existing = document.querySelector('.toast-message');
        if (existing) existing.remove();

        var icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
        var toast = document.createElement('div');
        toast.className = 'toast-message toast-' + (type || 'info');
        
        var iconSpan = document.createElement('span');
        iconSpan.className = 'toast-icon';
        iconSpan.textContent = icons[type] || 'ℹ';
        
        var textSpan = document.createElement('span');
        textSpan.textContent = message;

        toast.appendChild(iconSpan);
        toast.appendChild(textSpan);

        document.body.appendChild(toast);

        // Auto-remove
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 4000);
    }

});
