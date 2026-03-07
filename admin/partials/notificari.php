<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen border-t border-gray-200">
    <div class="max-w-4xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Notificări Email</h1>
        
        <form class="cab-ajax-form bg-white shadow rounded-lg p-6 border border-gray-100">
            <input type="hidden" name="route" value="save_settings">
            <input type="hidden" name="wc_enabled" value="<?php echo esc_attr(get_option('cab_wc_enabled', 0)); ?>">
            <input type="hidden" name="wc_product_id" value="<?php echo esc_attr(get_option('cab_wc_product_id', 0)); ?>">
            
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresa de email pentru notificări admin</label>
                <input type="email" name="admin_email_address" value="<?php echo esc_attr(get_option('cab_admin_email_address', get_option('admin_email'))); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="admin@colosseum.ro">
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Email Confirmare Client</h3>
                <p class="text-sm text-gray-500 mb-3">Trimis automat clientului când rezervarea este confirmată. Poți folosi <code>{nume_client}</code>, <code>{data_rezervare}</code>, <code>{ora_rezervare}</code>, <code>{nume_serviciu}</code>.</p>
                <textarea name="email_confirm" rows="6" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition font-sans"><?php echo esc_textarea(get_option('cab_email_confirm', "Salut {nume_client},\n\nRezervarea ta pentru {nume_serviciu} a fost confirmată!\nData: {data_rezervare}\nOra: {ora_rezervare}\n\nTe așteptăm!")); ?></textarea>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Notificare Admin Rezervare Nouă</h3>
                <p class="text-sm text-gray-500 mb-3">Trimis tie când se creează o rezervare nouă. Variabile permise: <code>{nume_client}</code>, <code>{data_rezervare}</code>, <code>{ora_rezervare}</code>, <code>{nume_serviciu}</code>, <code>{telefon}</code>.</p>
                <textarea name="email_admin" rows="6" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition font-sans"><?php echo esc_textarea(get_option('cab_email_admin', "Ai o rezervare nouă!\n\nClient: {nume_client}\nTelefon: {telefon}\nServiciu: {nume_serviciu}\nData: {data_rezervare}\nOra: {ora_rezervare}\nStatus plata: {status_plata}")); ?></textarea>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Email Anulare Rezervare</h3>
                <p class="text-sm text-gray-500 mb-3">Trimis clientului dacă rezervarea a fost anulată. Variabile permise: <code>{nume_client}</code>, <code>{data_rezervare}</code>.</p>
                <textarea name="email_cancel" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition font-sans"><?php echo esc_textarea(get_option('cab_email_cancel', "Salut {nume_client},\n\nDin păcate, rezervarea ta din data de {data_rezervare} a fost anulată.\nPentru detalii, te rugăm să ne contactezi.")); ?></textarea>
            </div>
            
            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition">Salvează Șabloanele de Email</button>
            </div>
        </form>
    </div>
</div>
