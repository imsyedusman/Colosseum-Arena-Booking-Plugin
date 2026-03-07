(function( $ ) {
	'use strict';

	$(document).ready(function() {
		
		// Initialize DataTables
		if ( $('.cab-datatable').length ) {
			$('.cab-datatable').DataTable({
				language: {
					url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ro.json'
				}
			});
		}

		// Generic Modal Logic (Open)
		$(document).on('click', '.cab-open-modal', function(e) {
			e.preventDefault();
			var target = $(this).data('target');
			
			// Reset form if it is an Add action
			if ( $(this).data('action') === 'add' ) {
				$(target).find('form')[0].reset();
				$(target).find('input[name="id"]').val('0');
			}
			
			$(target).removeClass('hidden').addClass('flex');
		});

		// Modal Logic (Close)
		$(document).on('click', '.cab-close-modal', function(e) {
			e.preventDefault();
			$(this).closest('.cab-modal').removeClass('flex').addClass('hidden');
		});
		
		// Generic Delete Action with SweetAlert
		$(document).on('click', '.cab-delete-btn', function(e) {
			e.preventDefault();
			var route = $(this).data('route');
			var id = $(this).data('id');
			var row = $(this).closest('tr');
			
			Swal.fire({
				title: 'Ești sigur?',
				text: "Această acțiune este ireversibilă!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Da, șterge!',
				cancelButtonText: 'Anulează'
			}).then((result) => {
				if (result.isConfirmed) {
					$.post(cab_ajax_obj.ajax_url, {
						action: 'cab_ajax',
						nonce: cab_ajax_obj.nonce,
						route: route,
						id: id
					}, function(res) {
						if (res.success) {
							Swal.fire('Șters!', res.data, 'success').then(() => {
								location.reload();
							});
						} else {
							Swal.fire('Eroare', res.data, 'error');
						}
					});
				}
			});
		});

		// Generic Form Submit Action
		$(document).on('submit', '.cab-ajax-form', function(e) {
			e.preventDefault();
			var form = $(this);
			var data = form.serialize() + '&action=cab_ajax&nonce=' + cab_ajax_obj.nonce;
			
			$.post(cab_ajax_obj.ajax_url, data, function(res) {
				if (res.success) {
					Swal.fire('Succes!', res.data, 'success').then(() => {
						location.reload();
					});
				} else {
					Swal.fire('Eroare', res.data, 'error');
				}
			});
		});

	});

})( jQuery );
