<?php
global $wpdb;
// Count total bookings
$total_bookings = $wpdb->get_var("SELECT COUNT(id) FROM " . CABA_DB::get_table_name('bookings'));
$total_revenue = $wpdb->get_var("SELECT SUM(total_amount) FROM " . CABA_DB::get_table_name('bookings') . " WHERE status = 'confirmed'");
$total_customers = $wpdb->get_var("SELECT COUNT(id) FROM " . CABA_DB::get_table_name('customers'));
$latest_bookings = $wpdb->get_results("SELECT b.*, s.name as service_name, c.first_name, c.last_name FROM " . CABA_DB::get_table_name('bookings') . " b LEFT JOIN " . CABA_DB::get_table_name('services') . " s ON b.service_id = s.id LEFT JOIN " . CABA_DB::get_table_name('customers') . " c ON b.customer_id = c.id ORDER BY b.id DESC LIMIT 5", ARRAY_A);
?>
<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 mt-1">Bun venit în sistemul de rezervări Colosseum.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Total Rezervări</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo intval($total_bookings); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Venituri (RON)</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo number_format(floatval($total_revenue), 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Total Clienți</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo intval($total_customers); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Ultimele 5 Rezervări</h3>
        
        <?php if($latest_bookings): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-gray-500 text-sm border-b">
                        <th class="py-3 font-semibold">Data Rezervare</th>
                        <th class="py-3 font-semibold">Client</th>
                        <th class="py-3 font-semibold">Serviciu</th>
                        <th class="py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($latest_bookings as $lb): ?>
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="py-3 text-gray-800 font-medium"><?php echo esc_html(date('d.m.Y H:i', strtotime($lb['booking_date'].' '.$lb['start_time']))); ?></td>
                        <td class="py-3 text-gray-600"><?php echo esc_html($lb['first_name'] . ' ' . $lb['last_name']); ?></td>
                        <td class="py-3 text-gray-600"><?php echo esc_html($lb['service_name']); ?></td>
                        <td class="py-3">
                            <?php if($lb['status'] == 'confirmed'): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Confirmat</span>
                            <?php elseif($lb['status'] == 'pending_payment_online'): ?>
                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">Plată online</span>
                            <?php elseif($lb['status'] == 'expired'): ?>
                                <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Expirat</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Anulat</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-right">
            <a href="?page=colosseum-booking-rezervari" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">Vezi toate rezervările &rarr;</a>
        </div>
        <?php else: ?>
            <p class="text-gray-500 italic">Nu există rezervări momentan.</p>
        <?php endif; ?>
    </div>
</div>
