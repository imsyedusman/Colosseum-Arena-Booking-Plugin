<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen border-t border-gray-200">
    <div class="max-w-4xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Setări Sistem</h1>
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded shadow-sm">
            <h3 class="text-blue-800 font-bold mb-1"><i class="fas fa-code mr-1"></i> Shortcode Rezervări</h3>
            <p class="text-blue-700 text-sm">Pentru a afișa formularul de rezervare pe site-ul dvs., copiați și inserați următorul shortcode pe orice pagină (ex: Elementor, Gutenberg, secțiune Text):</p>
            <code class="block bg-white p-2 mt-2 border border-blue-200 rounded text-blue-900 font-mono font-bold text-md">[colosseum_booking_widget]</code>
        </div>

        <form class="cab-ajax-form bg-white shadow rounded-lg p-6 border border-gray-100 mb-8">
            <input type="hidden" name="route" value="save_settings">
            
            <!-- Hidden email fields to persist them when saving settings -->
            <input type="hidden" name="email_confirm" value="<?php echo esc_attr(get_option('cab_email_confirm')); ?>">
            <input type="hidden" name="email_admin" value="<?php echo esc_attr(get_option('cab_email_admin')); ?>">
            <input type="hidden" name="email_cancel" value="<?php echo esc_attr(get_option('cab_email_cancel')); ?>">
            <input type="hidden" name="admin_email_address" value="<?php echo esc_attr(get_option('cab_admin_email_address')); ?>">
            
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Setări WooCommerce Plată Online</h3>
            
            <div class="mb-5 flex items-center">
                <input type="checkbox" name="wc_enabled" value="1" id="wc_enabled" <?php checked(get_option('cab_wc_enabled', 0), 1); ?> class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition">
                <label for="wc_enabled" class="ml-3 block text-sm font-semibold text-gray-700">Activează Redirecționarea WooCommerce pentru clienții care aleg "Plătesc Online"</label>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">ID Produs WooCommerce</label>
                <input type="number" name="wc_product_id" value="<?php echo esc_attr(get_option('cab_wc_product_id', 0)); ?>" class="w-full sm:w-1/3 border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ex: 123">
                <p class="text-xs text-gray-500 mt-2">Dacă folosiți un produs dummy pentru a prelua prețul programatic, introduceți ID-ul lui. (Lăsați 0 dacă generați produs virtual dinamic sau dacă nu folosiți WC).</p>
            </div>

            <div class="flex justify-start pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition">Salvează Setările</button>
            </div>
        </form>
        
        <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Date Demo (Seed)</h3>
            <p class="text-sm text-gray-600 mb-4">Folosiți acest buton pentru a adăuga pachetele și configurațiile implicite (Categorii, Camere, Angajați, Servicii, Orare).</p>
            
            <div class="flex gap-3 mt-4 border-t pt-4">
                <button type="button" id="cab-btn-seed" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow transition">
                    <i class="fas fa-database mr-2"></i> Adaugă date demo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Default built-in Seed
    $('#cab-btn-seed').on('click', function() {
        Swal.fire({
            title: 'Confirmare import',
            text: "Sunteți sigur că doriți să adăugați datele demo implicite?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Da, adaugă',
			cancelButtonText: 'Anulează'
        }).then((result) => {
            if(result.isConfirmed) {
                Swal.fire({
					title: 'Se procesează...',
					text: 'Așteptați importul structurilor din fișier.',
					allowOutsideClick: false,
					didOpen: () => { Swal.showLoading(); }
				});
                
                $.post(cab_ajax_obj.ajax_url, {
                    action: 'cab_ajax',
                    nonce: cab_ajax_obj.nonce,
                    route: 'import_seed_data'
                }, function(res) {
                    Swal.fire('Succes', res.data, 'success').then(() => location.reload());
                });
            }
        });
    });
});
</script>
