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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cameră Alocată</label>
                    <select name="room_id" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="0">Fără cameră</option>
                        <?php foreach($rooms as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo esc_html($r['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Angajat / Grup <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="">Alege angajat...</option>
                        <?php foreach($employees as $e): ?>
                            <option value="<?php echo $e['id']; ?>"><?php echo esc_html($e['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Durată implicită (min)</label>
                        <input type="number" name="duration" min="1" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Preț implicit (RON)</label>
                        <input type="number" name="price" step="0.01" min="0" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>

                <div class="col-span-2 mb-5 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-end;">
                        <div style="flex: 1; min-width: 150px;">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Min. Participanți</label>
                            <input type="number" name="min_participants" min="1" value="1" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition bg-white">
                        </div>
                        <div style="flex: 1; min-width: 150px;">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Max. Participanți</label>
                            <input type="number" name="max_participants" min="1" value="20" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 outline-none transition bg-white">
                        </div>
                        <div style="flex: 1; min-width: 200px; padding-bottom: 2px;">
                            <label class="flex items-center cursor-pointer bg-white p-3 rounded-lg border border-gray-300 shadow-sm hover:bg-gray-50 transition">
                                <input type="checkbox" name="is_per_person" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-700 font-semibold">Preț pe persoană (Se adaugă per participant.)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 mb-4 border-t pt-5">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-semibold text-gray-700">Orare și Disponibilitate (Obligatoriu)</label>
                        <button type="button" onclick="addScheduleRow()" class="text-xs px-3 py-1 bg-green-100 text-green-700 font-medium rounded hover:bg-green-200 transition">
                            <i class="fas fa-plus mr-1"></i> Adaugă Interval
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Definiți zilele și intervalele orare în care acest serviciu poate fi rezervat.</p>
                    <div id="schedules-container" class="space-y-4 max-h-96 overflow-y-auto pr-2 pb-2">
                        <!-- Options injected here -->
                    </div>
                </div>

                <div class="col-span-2 mb-2 border-t pt-5">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-semibold text-gray-700">Opțiuni Pachete / Prețuri (Opțional)</label>
                        <button type="button" onclick="addPricingOption()" class="text-xs px-3 py-1 bg-green-100 text-green-700 font-medium rounded hover:bg-green-200 transition">
                            <i class="fas fa-plus mr-1"></i> Adaugă Opțiune
                        </button>
                    </div>
                    
                    <div id="pricing-options-container" class="space-y-2 max-h-48 overflow-y-auto">
                        <!-- Options injected here -->
                    </div>
                    
                    <!-- Hidden field to store JSON string for submission -->
                    <input type="hidden" name="pricing_options" id="cab_pricing_options_hidden" value="">
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
function editService(srv) {
    jQuery('#modal-service').removeClass('hidden').addClass('flex');
    jQuery('#modal-service input[name="id"]').val(srv.id);
    jQuery('#modal-service input[name="name"]').val(srv.name);
    jQuery('#modal-service select[name="category_id"]').val(srv.category_id);
    jQuery('#modal-service select[name="room_id"]').val(srv.room_id);
    jQuery('#modal-service select[name="employee_id"]').val(srv.employee_id);
    jQuery('#modal-service input[name="duration"]').val(srv.duration);
    jQuery('#modal-service input[name="price"]').val(srv.price);
    
    jQuery('#modal-service input[name="min_participants"]').val(srv.min_participants || 1);
    jQuery('#modal-service input[name="max_participants"]').val(srv.max_participants || 20);
    jQuery('#modal-service input[name="is_per_person"]').prop('checked', srv.is_per_person == 1);
    
    // Clear Options
    jQuery('#pricing-options-container').empty();
    jQuery('#cab_pricing_options_hidden').val('');
    
    if (srv.pricing_options) {
        try {
            let opts = JSON.parse(srv.pricing_options);
            if (Array.isArray(opts)) {
                opts.forEach(o => addPricingOption(o.name, o.duration, o.price));
            }
        } catch(e) {}
    }

    // Load Schedules dynamically
    jQuery('#schedules-container').empty();
    jQuery.post(cab_ajax_obj.ajax_url, {
        action: 'cab_ajax',
        nonce: cab_ajax_obj.nonce,
        route: 'get_schedules',
        service_id: srv.id
    }, function(res) {
        scheduleIndex = 0;
        if(res.success && res.data.length > 0) {
            res.data.forEach(sch => {
                addScheduleRow(sch.day_type, sch.start_time.substring(0,5), sch.end_time.substring(0,5), sch.breaks);
            });
        } else {
            addScheduleRow(); // Add an empty row by default
        }
    });
}

function addPricingOption(name = '', duration = '', price = '') {
    let html = `
    <div class="flex items-center gap-2 bg-gray-50 p-2 rounded border border-gray-200 pricing-option-row">
        <div class="w-1/3">
            <input type="text" class="po-name w-full border-gray-300 rounded p-2 text-sm outline-none" placeholder="Nume opțiune (ex: 1 Joc)" value="${name}">
        </div>
        <div class="w-1/3">
            <input type="number" class="po-duration w-full border-gray-300 rounded p-2 text-sm outline-none" placeholder="Durată min. (ex: 20)" value="${duration}">
        </div>
        <div class="w-1/3">
            <input type="number" step="0.01" class="po-price w-full border-gray-300 rounded p-2 text-sm outline-none" placeholder="Preț (RON)" value="${price}">
        </div>
        <div class="w-auto">
            <button type="button" onclick="jQuery(this).closest('.pricing-option-row').remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fas fa-trash"></i></button>
        </div>
    </div>`;
    jQuery('#pricing-options-container').append(html);
}

jQuery(document).ready(function() {
    jQuery('#modal-service form').on('submit', function() {
        let options = [];
        jQuery('.pricing-option-row').each(function() {
            let n = jQuery(this).find('.po-name').val();
            let d = jQuery(this).find('.po-duration').val();
            let p = jQuery(this).find('.po-price').val();
            if(n && (p !== '')) {
                options.push({
                    name: n,
                    duration: parseInt(d) || 0,
                    price: parseFloat(p) || 0
                });
            }
        });
        jQuery('#cab_pricing_options_hidden').val(options.length ? JSON.stringify(options) : '');

        // Gather breaks for each schedule row
        jQuery('.schedule-row').each(function() {
            let breaks = [];
            jQuery(this).find('.break-row').each(function() {
                let bs = jQuery(this).find('.break-start').val();
                let be = jQuery(this).find('.break-end').val();
                if (bs && be) {
                    breaks.push({ start: bs, end: be });
                }
            });
            jQuery(this).find('.breaks-hidden-input').val(JSON.stringify(breaks));
        });
    });

    // Reset container when clicking Adauga Serviciu
    jQuery('.cab-open-modal[data-action="add"]').on('click', function(){
        jQuery('#schedules-container').empty();
        jQuery('#pricing-options-container').empty();
        scheduleIndex = 0;
        addScheduleRow(); // Default new schedule row for new service
    });
});

let scheduleIndex = 0;
function addScheduleRow(day_type = 'weekdays', start = '09:00', end = '10:00', breaks_json = '') {
    let breaksHtml = '';
    if (breaks_json) {
        try {
            let breaks = JSON.parse(breaks_json);
            if (Array.isArray(breaks)) {
                breaks.forEach(b => {
                    breaksHtml += createBreakRowHtml(b.start, b.end);
                });
            }
        } catch (e) {}
    }

    let html = `
    <div class="bg-gray-50 p-4 rounded border border-gray-200 schedule-row relative">
        <div class="flex items-center gap-2 mb-4">
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Tip Zi / Zile</label>
                <select name="schedules[${scheduleIndex}][day_type]" class="w-full border-gray-300 rounded p-2 text-sm outline-none bg-white">
                    <option value="daily" ${day_type==='daily'?'selected':''}>Zilnic (L-D)</option>
                    <option value="weekdays" ${day_type==='weekdays'?'selected':''}>Săptămână (L-V)</option>
                    <option value="weekends" ${day_type==='weekends'?'selected':''}>Weekend (S-D)</option>
                    <option value="monday" ${day_type==='monday'?'selected':''}>Luni</option>
                    <option value="tuesday" ${day_type==='tuesday'?'selected':''}>Marți</option>
                    <option value="wednesday" ${day_type==='wednesday'?'selected':''}>Miercuri</option>
                    <option value="thursday" ${day_type==='thursday'?'selected':''}>Joi</option>
                    <option value="friday" ${day_type==='friday'?'selected':''}>Vineri</option>
                    <option value="saturday" ${day_type==='saturday'?'selected':''}>Sâmbătă</option>
                    <option value="sunday" ${day_type==='sunday'?'selected':''}>Duminică</option>
                </select>
            </div>
            <div class="w-1/4">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Start</label>
                <input type="time" name="schedules[${scheduleIndex}][start_time]" value="${start}" class="w-full border-gray-300 rounded p-2 text-sm outline-none bg-white">
            </div>
            <div class="w-1/4">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">End</label>
                <input type="time" name="schedules[${scheduleIndex}][end_time]" value="${end}" class="w-full border-gray-300 rounded p-2 text-sm outline-none bg-white">
            </div>
            <div class="pt-5">
                <button type="button" onclick="jQuery(this).closest('.schedule-row').remove()" class="text-red-400 hover:text-red-600 p-2 transition-colors"><i class="fas fa-trash"></i></button>
            </div>
        </div>

        <div class="breaks-section border-t border-dashed border-gray-200 pt-3">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pauze / Breaks</span>
                <button type="button" onclick="addBreakRowToSchedule(this)" class="text-[10px] px-2 py-0.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors uppercase font-bold">
                    + Adaugă Pauză
                </button>
            </div>
            <div class="breaks-list space-y-2">
                ${breaksHtml}
            </div>
            <input type="hidden" name="schedules[${scheduleIndex}][breaks]" class="breaks-hidden-input" value='${breaks_json}'>
        </div>
    </div>`;
    jQuery('#schedules-container').append(html);
    scheduleIndex++;
}

function createBreakRowHtml(start = '', end = '') {
    return `
    <div class="flex items-center gap-2 break-row bg-white p-2 rounded border border-gray-100 shadow-sm">
        <label class="text-[10px] text-gray-400 uppercase font-bold">De la:</label>
        <input type="time" class="break-start border-gray-200 rounded p-1 text-xs outline-none bg-gray-50" value="${start}">
        <label class="text-[10px] text-gray-400 uppercase font-bold ml-2">Până la:</label>
        <input type="time" class="break-end border-gray-200 rounded p-1 text-xs outline-none bg-gray-50" value="${end}">
        <button type="button" onclick="jQuery(this).closest('.break-row').remove()" class="text-red-300 hover:text-red-500 ml-auto p-1"><i class="fas fa-times-circle"></i></button>
    </div>`;
}

function addBreakRowToSchedule(btn) {
    let row = createBreakRowHtml();
    jQuery(btn).closest('.breaks-section').find('.breaks-list').append(row);
}


</script>
