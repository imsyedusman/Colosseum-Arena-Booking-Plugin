<div class="cab-wrapper" id="cab-booking-widget">
    
    <!-- Loading Overlay -->
    <div class="cab-loader" id="cab-loader" style="display: none;">
        <div class="cab-spinner"></div>
    </div>

    <!-- Header / Steps Indicator -->
    <div class="cab-header">
        <h2 class="cab-title">Rezervă o Petrecere</h2>
        <div class="cab-progress-container">
            <div class="cab-progress-bar" id="cab-progress-bar"></div>
        </div>
        <div class="cab-steps-indicator">
            <span class="step-dot active" data-step="1"></span>
            <span class="step-dot" data-step="1b"></span>
            <span class="step-dot" data-step="2"></span>
            <span class="step-dot" data-step="3"></span>
            <span class="step-dot" data-step="4"></span>
        </div>
    </div>

    <div class="cab-body">
        
        <!-- STEP 1: Select Service -->
        <div class="cab-step active" id="cab-step-1">
            <h3 class="cab-step-title">1. Alege Pachetul</h3>
            <div id="cab-services-list" class="cab-grid-list">
                <!-- Injected via AJAX -->
            </div>
            <div class="cab-actions">
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" disabled id="btn-next-1">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 1b: Activity Details (Participants & Options) -->
        <div class="cab-step" id="cab-step-1b">
            <h3 class="cab-step-title">1b. Detalii Activitate</h3>
            <div id="cab-activity-details" class="cab-form">
                <div class="cab-form-group mb-6" id="cab-participants-group">
                    <label>Număr participanți (<span id="cab-min-max-label"></span>)</label>
                    <div class="cab-participants-selector">
                        <button type="button" class="cab-p-btn" id="cab-p-minus">-</button>
                        <input type="number" id="cab-participants-input" class="cab-input" value="1">
                        <button type="button" class="cab-p-btn" id="cab-p-plus">+</button>
                    </div>
                </div>
                
                <div class="cab-form-group" id="cab-options-group">
                    <label>Alege Opțiunea</label>
                    <div id="cab-pricing-options-list" class="cab-options-grid">
                        <!-- Injected via JS -->
                    </div>
                </div>
            </div>
            <div class="cab-actions">
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="1"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" id="btn-next-1b">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 2: Select Date & Time -->
        <div class="cab-step" id="cab-step-2">
            <h3 class="cab-step-title">2. Alege Data și Ora</h3>
            <div class="cab-date-selector flex justify-center mb-6">
                <!-- Flatpickr will render the calendar here -->
                <input type="text" id="cab-date-input" class="cab-input hidden">
            </div>
            
            <div id="cab-slots-wrapper" style="display:none; margin-top: 1.5rem;">
                <p class="cab-subtitle" style="margin-bottom: 0.75rem;">Ore disponibile pentru data selectată:</p>
                <div id="cab-slots-container" class="cab-slots-grid">
                    <!-- Injected via AJAX -->
                </div>
            </div>

            <div class="cab-actions mt-4">
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" id="btn-prev-2" data-prev="1"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" disabled id="btn-next-2">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 3: Customer Details -->
        <div class="cab-step" id="cab-step-3">
            <h3 class="cab-step-title">3. Date de Contact</h3>
            <form id="cab-customer-form" class="cab-form">
                <div class="cab-form-row">
                    <div class="cab-form-group">
                        <label>Prenume *</label>
                        <input type="text" id="cab-fname" class="cab-input" required>
                    </div>
                    <div class="cab-form-group">
                        <label>Nume de familie *</label>
                        <input type="text" id="cab-lname" class="cab-input" required>
                    </div>
                </div>
                <div class="cab-form-row">
                    <div class="cab-form-group">
                        <label>Email *</label>
                        <input type="email" id="cab-email" class="cab-input" required>
                    </div>
                    <div class="cab-form-group">
                        <label>Telefon *</label>
                        <input type="tel" id="cab-phone" class="cab-input" required>
                    </div>
                </div>
            </form>
            <div class="cab-actions">
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="2"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" id="btn-next-3">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 4: Payment -->
        <div class="cab-step" id="cab-step-4">
            <h3 class="cab-step-title">4. Mod de Plată</h3>
            
            <div class="cab-summary-box">
                <h4>Sumar Rezervare</h4>
                <p>Pachet: <strong id="sum-service"></strong></p>
                <div id="sum-participants-row"><p>Participanți: <strong id="sum-participants">1</strong></p></div>
                <div id="sum-option-row"><p>Opțiune: <strong id="sum-option"></strong></p></div>
                <p>Data: <strong id="sum-date"></strong></p>
                <p>Ora: <strong id="sum-time"></strong></p>
                <p>Total: <strong id="sum-price"></strong> RON</p>
            </div>

            <div class="cab-payment-methods">
                <label class="cab-payment-option selected">
                    <input type="radio" name="cab-payment" value="onsite" checked>
                    <div class="cab-payment-content">
                        <i class="fas fa-store"></i>
                        <span>Plata la locație</span>
                    </div>
                </label>
                <label class="cab-payment-option">
                    <input type="radio" name="cab-payment" value="online">
                    <div class="cab-payment-content">
                        <i class="fas fa-credit-card"></i>
                        <span>Plata Online (Card)</span>
                    </div>
                </label>
            </div>

            <div class="cab-actions">
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="3"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-success" id="btn-submit-booking"><i class="fas fa-check-circle"></i> Finalizează Rezervarea</button>
            </div>
        </div>

        <!-- Success Message -->
        <div class="cab-step" id="cab-step-success">
            <div class="cab-success-content">
                <i class="fas fa-check-circle cab-success-icon"></i>
                <h3>Rezervare Trimisă!</h3>
                <p id="cab-success-msg">Vă mulțumim pentru rezervare. Veți primi un email de confirmare în scurt timp.</p>
            </div>
        </div>

    </div>
</div>
