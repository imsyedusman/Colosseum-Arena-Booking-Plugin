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
            <span class="step-dot" data-step="2"></span>
            <span class="step-dot" data-step="3"></span>
            <span class="step-dot" data-step="4"></span>
            <span class="step-dot" data-step="5"></span>
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

        <!-- STEP 2: Select Date -->
        <div class="cab-step" id="cab-step-2">
            <h3 class="cab-step-title">2. Alege Data</h3>
            <div class="cab-date-selector">
                <input type="date" id="cab-date-input" class="cab-input" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="cab-actions">
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="1"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" disabled id="btn-next-2">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 3: Select Time Slot -->
        <div class="cab-step" id="cab-step-3">
            <h3 class="cab-step-title">3. Alege Intervalul Orar</h3>
            <p class="cab-subtitle">Seafișează doar orele disponibile pentru data selectată.</p>
            <div id="cab-slots-container" class="cab-slots-grid">
                <!-- Injected via AJAX -->
            </div>
            <div class="cab-actions mt-4">
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="2"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" disabled id="btn-next-3">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 4: Customer Details -->
        <div class="cab-step" id="cab-step-4">
            <h3 class="cab-step-title">4. Date de Contact</h3>
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
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="3"><i class="fas fa-arrow-left"></i> Înapoi</button>
                <button type="button" class="cab-btn cab-btn-primary cab-next-btn" id="btn-next-4">Următorul Pas <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 5: Payment -->
        <div class="cab-step" id="cab-step-5">
            <h3 class="cab-step-title">5. Mod de Plată</h3>
            
            <div class="cab-summary-box">
                <h4>Sumar Rezervare</h4>
                <p>Pachet: <strong id="sum-service"></strong></p>
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
                <button type="button" class="cab-btn cab-btn-secondary cab-prev-btn" data-prev="4"><i class="fas fa-arrow-left"></i> Înapoi</button>
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
