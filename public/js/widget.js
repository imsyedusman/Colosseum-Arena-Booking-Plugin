document.addEventListener('DOMContentLoaded', function () {

    // State
    const CabState = {
        service_id: 0,
        service_name: '',
        service_price: 0,
        min_p: 1,
        max_p: 20,
        is_per_person: 0,
        participants_count: 1,
        pricing_options: null,
        pricing_option_index: -1,
        date: '',
        start_time: '',
        end_time: '',
        customer: {
            first_name: '',
            last_name: '',
            email: '',
            phone: ''
        },
        payment_method: 'onsite',
        schedules: []
    };

    let fpInstance = null;

    const loader = document.getElementById('cab-loader');

    // Init
    loadServices();

    // Navigation Binding
    document.querySelectorAll('.cab-prev-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let targetStep = this.getAttribute('data-prev');
            goToStep(targetStep);
        });
    });

    // Step 1 - Load Services
    function loadServices() {
        showLoader();
        fetch(cab_public_obj.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'cab_frontend_ajax',
                nonce: cab_public_obj.nonce,
                route: 'get_services'
            })
        })
            .then(r => r.json())
            .then(res => {
                hideLoader();
                if (res.success && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(cat => {
                        html += `
                        <div class="cab-cat-header" style="font-weight:700; color:var(--cab-primary); margin-top:0.5rem;">${cat.name}</div>
                    `;
                        cat.services.forEach(srv => {
                            let pricingOptsAttr = srv.pricing_options ? srv.pricing_options.replace(/"/g, '&quot;') : '';
                            let schedulesAttr = srv.schedules ? JSON.stringify(srv.schedules).replace(/"/g, '&quot;') : '[]';
                            html += `
                            <div class="cab-service-card" 
                                data-id="${srv.id}" 
                                data-name="${srv.name}" 
                                data-price="${srv.price}"
                                data-min-p="${srv.min_participants || 1}"
                                data-max-p="${srv.max_participants || 20}"
                                data-per-person="${srv.is_per_person || 0}"
                                data-options="${pricingOptsAttr}"
                                data-schedules="${schedulesAttr}">
                                <div class="cab-svc-name">${srv.name}</div>
                                <div class="cab-svc-info">
                                    <span><i class="far fa-clock"></i> ${srv.duration} min</span>
                                    <span class="cab-svc-price">${parseFloat(srv.price).toFixed(2)} RON${srv.is_per_person == 1 ? '/pers' : ''}</span>
                                </div>
                            </div>
                        `;
                        });
                    });
                    document.getElementById('cab-services-list').innerHTML = html;

                    // Bind toggle
                    document.querySelectorAll('.cab-service-card').forEach(card => {
                        card.addEventListener('click', function () {
                            document.querySelectorAll('.cab-service-card').forEach(c => c.classList.remove('selected'));
                            this.classList.add('selected');

                            CabState.service_id = this.getAttribute('data-id');
                            CabState.service_name = this.getAttribute('data-name');
                            CabState.service_price = this.getAttribute('data-price');
                            CabState.min_p = parseInt(this.getAttribute('data-min-p'), 10);
                            CabState.max_p = parseInt(this.getAttribute('data-max-p'), 10);
                            CabState.is_per_person = parseInt(this.getAttribute('data-per-person'), 10);

                            let optionsRaw = this.getAttribute('data-options');
                            CabState.pricing_options = optionsRaw ? JSON.parse(optionsRaw) : null;

                            let schedulesRaw = this.getAttribute('data-schedules');
                            CabState.schedules = schedulesRaw ? JSON.parse(schedulesRaw) : [];

                            CabState.participants_count = CabState.min_p;
                            CabState.pricing_option_index = -1;

                            document.getElementById('btn-next-1').disabled = false;

                            initFlatpickr();
                        });
                    });
                } else {
                    document.getElementById('cab-services-list').innerHTML = '<p>Nu există pachete disponibile.</p>';
                }
            });
    }

    document.getElementById('btn-next-1').addEventListener('click', function () {
        if (CabState.is_per_person || (CabState.pricing_options && CabState.pricing_options.length > 0)) {
            // Setup Step 1b
            document.getElementById('cab-min-max-label').innerText = `Min ${CabState.min_p} - Max ${CabState.max_p}`;
            document.getElementById('cab-participants-input').value = CabState.participants_count;
            document.getElementById('cab-participants-input').min = CabState.min_p;
            document.getElementById('cab-participants-input').max = CabState.max_p;

            if (CabState.is_per_person) {
                document.getElementById('cab-participants-group').style.display = 'block';
            } else {
                document.getElementById('cab-participants-group').style.display = 'none';
            }

            if (CabState.pricing_options && CabState.pricing_options.length > 0) {
                document.getElementById('cab-options-group').style.display = 'block';
                let optHtml = '';
                CabState.pricing_options.forEach((opt, idx) => {
                    optHtml += `
                    <label class="cab-pricing-option">
                        <input type="radio" name="cab-svc-opt" value="${idx}">
                        <div class="cab-option-content">
                            <strong>${opt.name}</strong>
                            <span>${parseFloat(opt.price).toFixed(2)} RON${CabState.is_per_person ? '/pers' : ''}</span>
                        </div>
                    </label>
                    `;
                });
                document.getElementById('cab-pricing-options-list').innerHTML = optHtml;

                // Bind options
                document.querySelectorAll('input[name="cab-svc-opt"]').forEach(rad => {
                    rad.addEventListener('change', function () {
                        CabState.pricing_option_index = parseInt(this.value, 10);
                        checkStep1bValid();
                    });
                });
            } else {
                document.getElementById('cab-options-group').style.display = 'none';
            }

            checkStep1bValid();
            goToStep('1b');
        } else {
            // Skip directly to Date logic
            goToStep(2);
        }
    });

    function checkStep1bValid() {
        let valid = true;
        if (CabState.pricing_options && CabState.pricing_options.length > 0 && CabState.pricing_option_index === -1) {
            valid = false;
        }
        document.getElementById('btn-next-1b').disabled = !valid;
    }

    // Step 1b Listeners
    document.getElementById('cab-p-minus').addEventListener('click', function () {
        if (CabState.participants_count > CabState.min_p) {
            CabState.participants_count--;
            document.getElementById('cab-participants-input').value = CabState.participants_count;
        }
    });

    document.getElementById('cab-p-plus').addEventListener('click', function () {
        if (CabState.participants_count < CabState.max_p) {
            CabState.participants_count++;
            document.getElementById('cab-participants-input').value = CabState.participants_count;
        }
    });

    document.getElementById('cab-participants-input').addEventListener('change', function () {
        let val = parseInt(this.value, 10);
        if (isNaN(val) || val < CabState.min_p) val = CabState.min_p;
        if (val > CabState.max_p) val = CabState.max_p;
        CabState.participants_count = val;
        this.value = val;
    });

    document.getElementById('btn-next-1b').addEventListener('click', function () {
        goToStep(2);
    });

    // Step 2 - Date & Time Select
    function initFlatpickr() {
        let hasWeekdays = CabState.schedules.some(s => s.day_type === 'weekdays');
        let hasWeekends = CabState.schedules.some(s => s.day_type === 'weekends');

        if (fpInstance) {
            fpInstance.destroy();
        }

        fpInstance = flatpickr("#cab-date-input", {
            locale: "ro",
            minDate: "today",
            inline: true,
            disable: [
                function (date) {
                    if (CabState.schedules.length === 0) return true; // Disable all if no schedules

                    let dayNum = date.getDay(); // 0 is Sunday, 1 is Monday...
                    let isWeekend = (dayNum === 0 || dayNum === 6);
                    let daysMap = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    let dayName = daysMap[dayNum];

                    let isAllowed = false;
                    for (let sch of CabState.schedules) {
                        let dt = sch.day_type;
                        if (dt === 'daily') isAllowed = true;
                        else if (dt === 'weekdays' && !isWeekend) isAllowed = true;
                        else if (dt === 'weekends' && isWeekend) isAllowed = true;
                        else if (dt === dayName) isAllowed = true;
                    }

                    return !isAllowed; // return true to DISABLE the date
                }
            ],
            onChange: function (selectedDates, dateStr, instance) {
                CabState.date = dateStr;
                CabState.start_time = '';
                CabState.end_time = '';
                document.getElementById('btn-next-2').disabled = true;

                if (CabState.date) {
                    document.getElementById('cab-slots-wrapper').style.display = 'block';
                    loadSlots();
                } else {
                    document.getElementById('cab-slots-wrapper').style.display = 'none';
                }
            }
        });
    }

    function loadSlots() {
        showLoader();
        document.getElementById('cab-slots-container').innerHTML = '';
        document.getElementById('btn-next-2').disabled = true;
        CabState.start_time = '';
        CabState.end_time = '';

        fetch(cab_public_obj.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'cab_frontend_ajax',
                nonce: cab_public_obj.nonce,
                route: 'get_slots',
                service_id: CabState.service_id,
                date: CabState.date
            })
        })
            .then(r => r.json())
            .then(res => {
                hideLoader();
                if (res.success && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(slot => {
                        html += `
                        <div class="cab-slot-item" data-start="${slot.start_time}" data-end="${slot.end_time}">
                            ${slot.label}
                        </div>
                    `;
                    });
                    document.getElementById('cab-slots-container').innerHTML = html;

                    // Bind Selection
                    document.querySelectorAll('.cab-slot-item').forEach(item => {
                        item.addEventListener('click', function () {
                            document.querySelectorAll('.cab-slot-item').forEach(i => i.classList.remove('selected'));
                            this.classList.add('selected');

                            CabState.start_time = this.getAttribute('data-start');
                            CabState.end_time = this.getAttribute('data-end');

                            document.getElementById('btn-next-2').disabled = false;
                        });
                    });
                } else {
                    document.getElementById('cab-slots-container').innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#64748b;">Ne pare rău, nu există locuri disponibile în această zi.</p>';
                }
            });
    }

    document.getElementById('btn-next-2').addEventListener('click', function () {
        goToStep(3);
    });

    // Step 3 - Customer
    document.getElementById('btn-next-3').addEventListener('click', function () {
        let form = document.getElementById('cab-customer-form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        CabState.customer.first_name = document.getElementById('cab-fname').value;
        CabState.customer.last_name = document.getElementById('cab-lname').value;
        CabState.customer.email = document.getElementById('cab-email').value;
        CabState.customer.phone = document.getElementById('cab-phone').value;

        // Prepare summary (Calculate final price)
        let fp = CabState.service_price;
        let optName = '-';
        if (CabState.pricing_option_index >= 0 && CabState.pricing_options && CabState.pricing_options[CabState.pricing_option_index]) {
            fp = CabState.pricing_options[CabState.pricing_option_index].price;
            optName = CabState.pricing_options[CabState.pricing_option_index].name;
        }
        if (CabState.is_per_person) {
            fp = fp * CabState.participants_count;
        }

        document.getElementById('sum-service').innerText = CabState.service_name;
        document.getElementById('sum-participants').innerText = CabState.is_per_person ? CabState.participants_count : '-';
        document.getElementById('sum-participants-row').style.display = CabState.is_per_person ? 'block' : 'none';

        document.getElementById('sum-option').innerText = optName;
        document.getElementById('sum-option-row').style.display = (CabState.pricing_options && CabState.pricing_options.length > 0) ? 'block' : 'none';

        document.getElementById('sum-date').innerText = CabState.date.split('-').reverse().join('.');
        document.getElementById('sum-time').innerText = CabState.start_time + ' - ' + CabState.end_time;
        document.getElementById('sum-price').innerText = parseFloat(fp).toFixed(2);

        goToStep(4);
    });

    // Step 4 - Payment & Submit
    document.querySelectorAll('input[name="cab-payment"]').forEach(radio => {
        radio.addEventListener('change', function () {
            CabState.payment_method = this.value;
        });
    });

    document.getElementById('btn-submit-booking').addEventListener('click', function () {
        showLoader();

        let fd = new URLSearchParams({
            action: 'cab_frontend_ajax',
            nonce: cab_public_obj.nonce,
            route: 'submit_booking',
            service_id: CabState.service_id,
            date: CabState.date,
            start_time: CabState.start_time,
            end_time: CabState.end_time,
            participants_count: CabState.participants_count,
            pricing_option_index: CabState.pricing_option_index,
            payment_method: CabState.payment_method,
            first_name: CabState.customer.first_name,
            last_name: CabState.customer.last_name,
            email: CabState.customer.email,
            phone: CabState.customer.phone
        });

        fetch(cab_public_obj.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: fd
        })
            .then(r => r.json())
            .then(res => {
                hideLoader();
                if (res.success) {
                    if (res.data && res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        document.getElementById('cab-success-msg').innerText = res.data.message || 'Rezervare trimisă cu succes!';
                        goToStep('success');
                    }
                } else {
                    alert('Eroare: ' + res.data);
                }
            });
    });

    // Utils
    function goToStep(step) {
        document.querySelectorAll('.cab-step').forEach(s => s.classList.remove('active'));
        document.getElementById('cab-step-' + step).classList.add('active');

        if (typeof step === 'number') {
            document.querySelectorAll('.step-dot').forEach(d => {
                let sVal = d.getAttribute('data-step') === '1b' ? 1.5 : parseInt(d.getAttribute('data-step'), 10);
                if (sVal <= step) d.classList.add('active');
                else d.classList.remove('active');
            });
            document.getElementById('cab-progress-bar').style.width = (step * 25) + '%';
        } else {
            // success step
            document.getElementById('cab-progress-bar').style.width = '100%';
            document.querySelector('.cab-steps-indicator').style.display = 'none';
        }
    }

    function showLoader() { loader.style.display = 'flex'; }
    function hideLoader() { loader.style.display = 'none'; }
});
