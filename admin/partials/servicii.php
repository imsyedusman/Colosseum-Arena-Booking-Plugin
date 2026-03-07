<?php
$services = CABA_DB::get_services_with_relations();
$categories = CABA_DB::get_results('categories');
$rooms = CABA_DB::get_results('rooms');
$employees = CABA_DB::get_results('employees');
?>
<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Servicii</h1>
        <button data-target="#modal-service" data-action="add" class="cab-open-modal bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-plus mr-2"></i> Adaugă Serviciu
        </button>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border border-gray-100 overflow-x-auto">
        <table class="cab-datatable w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm">
                    <th class="border-b p-3 font-semibold">Nume</th>
                    <th class="border-b p-3 font-semibold">Categorie</th>
                    <th class="border-b p-3 font-semibold">Cameră</th>
                    <th class="border-b p-3 font-semibold">Angajat</th>
                    <th class="border-b p-3 font-semibold">Durată (min)</th>
                    <th class="border-b p-3 font-semibold">Preț (RON)</th>
                    <th class="border-b p-3 font-semibold text-right">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $services as $srv ): ?>
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                    <td class="p-3 font-medium text-gray-800"><?php echo esc_html($srv['name']); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($srv['category_name'] ? $srv['category_name'] : '-'); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($srv['room_name'] ? $srv['room_name'] : '-'); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($srv['employee_name'] ? $srv['employee_name'] : '-'); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($srv['duration']); ?></td>
                    <td class="p-3 font-semibold text-green-600"><?php echo esc_html($srv['price']); ?></td>
                    <td class="p-3 text-right space-x-2">
                        <button onclick="editSchedules(<?php echo $srv['id']; ?>, '<?php echo esc_attr($srv['name']); ?>')" class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition text-sm" title="Orare">
                            <i class="far fa-clock mr-1"></i> Orare
                        </button>
                        <button onclick="editService(<?php echo htmlspecialchars(json_encode($srv), ENT_QUOTES, 'UTF-8'); ?>)" class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-sm" title="Edit">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button data-id="<?php echo esc_attr($srv['id']); ?>" data-route="delete_service" class="cab-delete-btn px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition text-sm" title="Delete">
                            <i class="fas fa-trash mr-1"></i> Șterge
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Service -->
<div id="modal-service" class="cab-modal hidden fixed inset-0 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Gestionare Serviciu</h3>
            <button class="cab-close-modal text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form class="cab-ajax-form p-6 bg-white">
            <input type="hidden" name="route" value="save_service">
            <input type="hidden" name="id" value="0">
            
            <div class="grid grid-cols-2 gap-5 mb-5">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nume Serviciu <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ex: Petrecere copii VIP">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Categorie</label>
                    <select name="category_id" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="0">Fără categorie</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo esc_html($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cameră Alocată <span class="text-red-500">*</span></label>
                    <select name="room_id" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="">Alege camera...</option>
                        <?php foreach($rooms as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo esc_html($r['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Angajat / Grup</label>
                    <select name="employee_id" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="0">Niciunul</option>
                        <?php foreach($employees as $e): ?>
                            <option value="<?php echo $e['id']; ?>"><?php echo esc_html($e['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Durată (min)</label>
                        <input type="number" name="duration" min="1" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Preț (RON)</label>
                        <input type="number" name="price" step="0.01" min="0" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="cab-close-modal px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">Anulează</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-md transition">Salvează</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Schedules -->
<div id="modal-schedules" class="cab-modal hidden fixed inset-0 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50 shrink-0">
            <h3 class="text-lg font-bold text-gray-800">Orare: <span id="schedule-service-name" class="text-blue-600"></span></h3>
            <button class="cab-close-modal text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form class="cab-ajax-form flex-1 overflow-y-auto p-6 bg-white" id="form-schedules">
            <input type="hidden" name="route" value="save_schedules">
            <input type="hidden" name="service_id" id="schedule-service-id" value="0">
            
            <div class="mb-4 flex justify-between items-center">
                <p class="text-gray-600 text-sm">Adaugă intervalele orare în care acest pachet / serviciu poate fi rezervat.</p>
                <button type="button" onclick="addScheduleRow()" class="px-3 py-1.5 bg-green-100 text-green-700 font-medium rounded hover:bg-green-200 transition text-sm">
                    <i class="fas fa-plus mr-1"></i> Adaugă Interval
                </button>
            </div>
            
            <div id="schedules-container" class="space-y-3">
                <!-- Rows injected here -->
            </div>
            
            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100 pb-2">
                <button type="button" class="cab-close-modal px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">Anulează</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-md transition">Salvează Orarele</button>
            </div>
        </form>
    </div>
</div>

<script>
function editService(srv) {
    jQuery('#modal-service').removeClass('hidden').addClass('flex');
    jQuery('#modal-service input[name="id"]').val(srv.id);
    jQuery('#modal-service input[name="name"]').val(srv.name);
    jQuery('#modal-service select[name="category_id"]').val(srv.category_id);
    jQuery('#modal-service select[name="room_id"]').val(srv.room_id);
    jQuery('#modal-service select[name="employee_id"]').val(srv.employee_id);
    jQuery('#modal-service input[name="duration"]').val(srv.duration);
    jQuery('#modal-service input[name="price"]').val(srv.price);
}

let scheduleIndex = 0;
function addScheduleRow(day_type = 'weekdays', start = '09:00', end = '10:00') {
    let html = `
    <div class="flex items-center gap-4 bg-gray-50 p-3 rounded border border-gray-200 schedule-row">
        <div class="w-1/3">
            <label class="block text-xs text-gray-500 mb-1">Tip Zi</label>
            <select name="schedules[${scheduleIndex}][day_type]" class="w-full border-gray-300 rounded p-2 text-sm outline-none">
                <option value="weekdays" ${day_type==='weekdays'?'selected':''}>În timpul săptămânii (L-V)</option>
                <option value="weekends" ${day_type==='weekends'?'selected':''}>Weekend (S-D)</option>
            </select>
        </div>
        <div class="w-1/4">
            <label class="block text-xs text-gray-500 mb-1">Ora Start</label>
            <input type="time" name="schedules[${scheduleIndex}][start_time]" value="${start}" class="w-full border-gray-300 rounded p-2 text-sm outline-none">
        </div>
        <div class="w-1/4">
            <label class="block text-xs text-gray-500 mb-1">Ora End</label>
            <input type="time" name="schedules[${scheduleIndex}][end_time]" value="${end}" class="w-full border-gray-300 rounded p-2 text-sm outline-none">
        </div>
        <div class="w-auto ml-auto self-end">
            <button type="button" onclick="jQuery(this).closest('.schedule-row').remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fas fa-trash"></i></button>
        </div>
    </div>`;
    jQuery('#schedules-container').append(html);
    scheduleIndex++;
}

function editSchedules(service_id, service_name) {
    jQuery('#modal-schedules').removeClass('hidden').addClass('flex');
    jQuery('#schedule-service-id').val(service_id);
    jQuery('#schedule-service-name').text(service_name);
    jQuery('#schedules-container').empty();
    
    // Fetch existing schedules
    jQuery.post(cab_ajax_obj.ajax_url, {
        action: 'cab_ajax',
        nonce: cab_ajax_obj.nonce,
        route: 'get_schedules',
        service_id: service_id
    }, function(res) {
        if(res.success && res.data.length > 0) {
            res.data.forEach(sch => {
                addScheduleRow(sch.day_type, sch.start_time.substring(0,5), sch.end_time.substring(0,5));
            });
        } else {
            addScheduleRow(); // Add an empty row by default
        }
    });
}
</script>
