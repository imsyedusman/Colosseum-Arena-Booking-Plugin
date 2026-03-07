document.addEventListener('DOMContentLoaded', function () {

    // State
    const CabState = {
        service_id: 0,
        service_name: '',
        service_price: 0,
        date: '',
        start_time: '',
        end_time: '',
        customer: {
            first_name: '',
            last_name: '',
            email: '',
            phone: ''
        },
        payment_method: 'onsite'
    };

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
                            html += `
                            <div class="cab-service-card" data-id="${srv.id}" data-name="${srv.name}" data-price="${srv.price}">
                                <div class="cab-svc-name">${srv.name}</div>
                                <div class="cab-svc-info">
                                    <span><i class="far fa-clock"></i> ${srv.duration} min</span>
                                    <span class="cab-svc-price">${parseFloat(srv.price).toFixed(2)} RON</span>
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

                            document.getElementById('btn-next-1').disabled = false;
                        });
                    });
                } else {
                    document.getElementById('cab-services-list').innerHTML = '<p>Nu există pachete disponibile.</p>';
                }
            });
    }

    document.getElementById('btn-next-1').addEventListener('click', function () {
        goToStep(2);
    });

    // Step 2 - Date Select
    document.getElementById('cab-date-input').addEventListener('change', function () {
        CabState.date = this.value;
        if (CabState.date) {
            document.getElementById('btn-next-2').disabled = false;
        } else {
            document.getElementById('btn-next-2').disabled = true;
        }
    });

    document.getElementById('btn-next-2').addEventListener('click', function () {
        loadSlots();
        goToStep(3);
    });

    // Step 3 - Slots
    function loadSlots() {
        showLoader();
        document.getElementById('cab-slots-container').innerHTML = '';
        document.getElementById('btn-next-3').disabled = true;
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

                            document.getElementById('btn-next-3').disabled = false;
                        });
                    });
                } else {
                    document.getElementById('cab-slots-container').innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#64748b;">Ne pare rău, nu există locuri disponibile în această zi.</p>';
                }
            });
    }

    document.getElementById('btn-next-3').addEventListener('click', function () {
        goToStep(4);
    });

    // Step 4 - Customer
    document.getElementById('btn-next-4').addEventListener('click', function () {
        let form = document.getElementById('cab-customer-form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        CabState.customer.first_name = document.getElementById('cab-fname').value;
        CabState.customer.last_name = document.getElementById('cab-lname').value;
        CabState.customer.email = document.getElementById('cab-email').value;
        CabState.customer.phone = document.getElementById('cab-phone').value;

        // Prepare summary
        document.getElementById('sum-service').innerText = CabState.service_name;
        document.getElementById('sum-date').innerText = CabState.date.split('-').reverse().join('.');
        document.getElementById('sum-time').innerText = CabState.start_time + ' - ' + CabState.end_time;
        document.getElementById('sum-price').innerText = parseFloat(CabState.service_price).toFixed(2);

        goToStep(5);
    });

    // Step 5 - Payment & Submit
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
                if (parseInt(d.getAttribute('data-step')) <= step) d.classList.add('active');
                else d.classList.remove('active');
            });
            document.getElementById('cab-progress-bar').style.width = (step * 20) + '%';
        } else {
            // success step
            document.getElementById('cab-progress-bar').style.width = '100%';
            document.querySelector('.cab-steps-indicator').style.display = 'none';
        }
    }

    function showLoader() { loader.style.display = 'flex'; }
    function hideLoader() { loader.style.display = 'none'; }
});
