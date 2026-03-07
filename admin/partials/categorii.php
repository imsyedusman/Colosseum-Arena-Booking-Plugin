<?php
$categories = CABA_DB::get_results( 'categories' );
?>
<div class="wrap cab-wrap p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Categorii</h1>
        <button data-target="#modal-category" data-action="add" class="cab-open-modal bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-plus mr-2"></i> Adaugă Categorie
        </button>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
        <table class="cab-datatable w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm">
                    <th class="border-b p-3 font-semibold">ID</th>
                    <th class="border-b p-3 font-semibold">Nume</th>
                    <th class="border-b p-3 font-semibold">Descriere</th>
                    <th class="border-b p-3 font-semibold text-right">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $categories as $cat ): ?>
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                    <td class="p-3 text-gray-500 text-sm"><?php echo esc_html($cat['id']); ?></td>
                    <td class="p-3 font-medium text-gray-800"><?php echo esc_html($cat['name']); ?></td>
                    <td class="p-3 text-gray-600 text-sm"><?php echo esc_html($cat['description']); ?></td>
                    <td class="p-3 text-right space-x-3">
                        <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8'); ?>)" class="text-blue-500 hover:text-blue-700 transition" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button data-id="<?php echo esc_attr($cat['id']); ?>" data-route="delete_category" class="cab-delete-btn text-red-500 hover:text-red-700 transition" title="Delete">
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
<div id="modal-category" class="cab-modal hidden fixed inset-0 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Gestionare Categorie</h3>
            <button class="cab-close-modal text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form class="cab-ajax-form p-6 bg-white">
            <input type="hidden" name="route" value="save_category">
            <input type="hidden" name="id" value="0">
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nume Categorie <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Ex: Petreceri copii">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Descriere</label>
                <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="O scurtă descriere a categoriei..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" class="cab-close-modal px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">Anulează</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-md transition">Salvează</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(cat) {
    jQuery('#modal-category').removeClass('hidden').addClass('flex');
    jQuery('#modal-category input[name="id"]').val(cat.id);
    jQuery('#modal-category input[name="name"]').val(cat.name);
    jQuery('#modal-category textarea[name="description"]').val(cat.description);
}
</script>
