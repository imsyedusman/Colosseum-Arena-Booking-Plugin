<?php
$bookings = CABA_DB::get_bookings_with_relations();
$services = CABA_DB::get_results('services');
$customers = CABA_DB::get_results('customers');
?>
<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Rezervări</h1>
        <button data-target="#modal-booking" data-action="add" class="cab-open-modal bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-plus mr-2"></i> Adaugă Rezervare
        </button>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border border-gray-100 overflow-x-auto">
        <table class="cab-datatable w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm">
                    <th class="border-b p-3 font-semibold">ID</th>
                    <th class="border-b p-3 font-semibold">Data & Ora</th>
                    <th class="border-b p-3 font-semibold">Client</th>
                    <th class="border-b p-3 font-semibold">Serviciu</th>
                    <th class="border-b p-3 font-semibold text-center">Partic.</th>
                    <th class="border-b p-3 font-semibold">Status</th>
                    <th class="border-b p-3 font-semibold">Plată</th>
                    <th class="border-b p-3 font-semibold">Total(RON)</th>
                    <th class="border-b p-3 font-semibold text-right">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $bookings as $b ): ?>
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                    <td class="p-3 text-gray-500 text-sm">#<?php echo esc_html($b['id']); ?></td>
                    <td class="p-3 font-medium text-gray-800">
                        <?php echo esc_html(date('d.m.Y', strtotime($b['booking_date']))); ?><br>
                        <span class="text-xs text-gray-500"><?php echo esc_html(substr($b['start_time'],0,5) . ' - ' . substr($b['end_time'],0,5)); ?></span>
                    </td>
                    <td class="p-3">
                        <div class="font-medium text-gray-800"><?php echo esc_html($b['first_name'] . ' ' . $b['last_name']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo esc_html($b['email']); ?></div>
                    </td>
                    <td class="p-3 text-gray-700 font-medium text-sm"><?php echo esc_html($b['service_name'] ? $b['service_name'] : '-'); ?></td>
                    <td class="p-3 font-semibold text-center text-sm"><?php echo esc_html($b['participants_count']); ?></td>
                    <td class="p-3">
                        <?php if($b['status'] == 'confirmed'): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Confirmat</span>
                        <?php elseif($b['status'] == 'pending_payment_online'): ?>
                            <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">Plată Online</span>
                        <?php elseif($b['status'] == 'expired'): ?>
                            <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Expirat</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Anulat</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-gray-600 text-sm capitalize"><?php echo esc_html($b['payment_method']); ?></td>
                    <td class="p-3 font-semibold text-green-600"><?php echo esc_html($b['total_amount']); ?></td>
                    <td class="p-3 text-right space-x-2">
                        <button onclick="editBooking(<?php echo htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8'); ?>)" class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button data-id="<?php echo esc_attr($b['id']); ?>" data-route="cancel_booking" class="cab-delete-btn px-2 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 transition text-sm" title="Anuleaza">
                            <i class="fas fa-ban"></i>
                        </button>
                        <button data-id="<?php echo esc_attr($b['id']); ?>" data-route="delete_booking" class="cab-delete-btn px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition text-sm" title="Sterge permanent">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Booking -->
<div id="modal-booking" class="cab-modal hidden fixed inset-0 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Gestionare Rezervare</h3>
            <button class="cab-close-modal text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form class="cab-ajax-form p-6 bg-white">
            <input type="hidden" name="route" value="save_booking">
            <input type="hidden" name="id" value="0">
            
            <div class="grid grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Serviciu <span class="text-red-500">*</span></label>
                    <select name="service_id" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="">Alege serviciu...</option>
                        <?php foreach($services as $srv): ?>
                            <option value="<?php echo $srv['id']; ?>"><?php echo esc_html($srv['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Client <span class="text-red-500">*</span></label>
                    <select name="customer_id" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="">Alege client...</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo esc_html($c['first_name'] . ' ' . $c['last_name'] . ' (' . $c['email'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data <span class="text-red-500">*</span></label>
                    <input type="date" name="booking_date" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start</label>
                        <input type="time" name="start_time" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">End</label>
                        <input type="time" name="end_time" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="pending_payment_online">Plată Online</option>
                        <option value="confirmed">Confirmat</option>
                        <option value="expired">Expirat</option>
                        <option value="cancelled">Anulat</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Metodă Plată</label>
                    <select name="payment_method" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="onsite">On-site</option>
                        <option value="online">Online (WooCommerce)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Total (RON)</label>
                    <input type="number" step="0.01" name="total_amount" value="0.00" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Participanți</label>
                    <input type="number" name="participants_count" min="1" value="1" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="cab-close-modal px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">Anulează</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-md transition">Salvează</button>
            </div>
        </form>
    </div>
</div>

<script>
function editBooking(b) {
    jQuery('#modal-booking').removeClass('hidden').addClass('flex');
    jQuery('#modal-booking input[name="id"]').val(b.id);
    jQuery('#modal-booking select[name="service_id"]').val(b.service_id);
    jQuery('#modal-booking select[name="customer_id"]').val(b.customer_id);
    jQuery('#modal-booking input[name="booking_date"]').val(b.booking_date);
    jQuery('#modal-booking input[name="start_time"]').val(b.start_time.substring(0,5));
    jQuery('#modal-booking input[name="end_time"]').val(b.end_time.substring(0,5));
    jQuery('#modal-booking select[name="status"]').val(b.status);
    jQuery('#modal-booking select[name="payment_method"]').val(b.payment_method);
    jQuery('#modal-booking input[name="total_amount"]').val(b.total_amount);
    jQuery('#modal-booking input[name="participants_count"]').val(b.participants_count || 1);
}
</script>
