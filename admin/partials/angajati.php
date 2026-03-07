<?php
$employees = CABA_DB::get_results( 'employees' );
?>
<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Angajați / Grupe Personale</h1>
        <button data-target="#modal-employee" data-action="add" class="cab-open-modal bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-plus mr-2"></i> Adaugă Angajat
        </button>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
        <table class="cab-datatable w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm">
                    <th class="border-b p-3 font-semibold">ID</th>
                    <th class="border-b p-3 font-semibold">Nume</th>
                    <th class="border-b p-3 font-semibold">Email</th>
                    <th class="border-b p-3 font-semibold">Telefon</th>
                    <th class="border-b p-3 font-semibold text-right">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $employees as $emp ): ?>
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                    <td class="p-3 text-gray-500 text-sm"><?php echo esc_html($emp['id']); ?></td>
                    <td class="p-3 font-medium text-gray-800"><?php echo esc_html($emp['name']); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($emp['email']); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($emp['phone']); ?></td>
                    <td class="p-3 text-right space-x-3">
                        <button onclick="editEmployee(<?php echo htmlspecialchars(json_encode($emp), ENT_QUOTES, 'UTF-8'); ?>)" class="text-blue-500 hover:text-blue-700 transition" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button data-id="<?php echo esc_attr($emp['id']); ?>" data-route="delete_employee" class="cab-delete-btn text-red-500 hover:text-red-700 transition" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal-employee" class="cab-modal hidden fixed inset-0 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Gestionare Angajat</h3>
            <button class="cab-close-modal text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form class="cab-ajax-form p-6 bg-white">
            <input type="hidden" name="route" value="save_employee">
            <input type="hidden" name="id" value="0">
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nume <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Ex: Party Bookings">
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Ex: staff@colosseum.ro">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Telefon</label>
                <input type="text" name="phone" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="cab-close-modal px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">Anulează</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-md transition">Salvează</button>
            </div>
        </form>
    </div>
</div>

<script>
function editEmployee(emp) {
    jQuery('#modal-employee').removeClass('hidden').addClass('flex');
    jQuery('#modal-employee input[name="id"]').val(emp.id);
    jQuery('#modal-employee input[name="name"]').val(emp.name);
    jQuery('#modal-employee input[name="email"]').val(emp.email);
    jQuery('#modal-employee input[name="phone"]').val(emp.phone);
}
</script>
