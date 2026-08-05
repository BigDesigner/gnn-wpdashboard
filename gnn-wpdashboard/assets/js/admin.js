/**
 * GNN WPDashboard Admin JavaScript
 */
(function($) {
	'use strict';

	let pluginsData = [];
	let activeStatusFilter = 'all';
	let activeCategoryFilter = 'all';

	$(document).ready(function() {
		loadPlugins();

		// Sync Button — önce cache temizle, sonra canlı GitHub verisiyle yenile
		$('#btn-sync-plugins, #btn-show-updates').on('click', function() {
			const $icon = $('#sync-icon');
			$icon.addClass('animate-spin');
			$.ajax({
				url: GNNWPDashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'gnn_wpdashboard_clear_cache',
					nonce: GNNWPDashboard.nonce
				},
				complete: function() {
					loadPlugins(function() {
						$icon.removeClass('animate-spin');
					});
				}
			});
		});

		// Search Input
		$('#search-input').on('keyup input', function() {
			renderGrid();
		});

		// Status Tab Filters
		$('.status-tab').on('click', function() {
			$('.status-tab').removeClass('active-tab bg-secondary-container text-on-secondary-container')
				.addClass('text-on-surface-variant hover:bg-surface-container-high');
			$(this).addClass('active-tab bg-secondary-container text-on-secondary-container')
				.removeClass('text-on-surface-variant hover:bg-surface-container-high');

			activeStatusFilter = $(this).data('status');
			renderGrid();
		});

		// Category Tab Filters
		$('.category-tab').on('click', function() {
			$('.category-tab').removeClass('bg-surface-container-low text-primary font-semibold')
				.addClass('text-on-surface-variant hover:bg-surface-container-high');
			$(this).addClass('bg-surface-container-low text-primary font-semibold')
				.removeClass('text-on-surface-variant hover:bg-surface-container-high');

			activeCategoryFilter = $(this).data('category');
			renderGrid();
		});

		// Modal Close Listeners
		$('#btn-close-modal, #btn-close-modal-bottom').on('click', function() {
			$('#details-modal').addClass('hidden');
		});

		// Delegate Card Action Buttons
		$('#plugin-grid-container').on('click', '.btn-install-plugin', function() {
			const slug = $(this).data('slug');
			installPlugin(slug, $(this));
		});

		$('#plugin-grid-container').on('click', '.btn-activate-plugin', function() {
			const file = $(this).data('file');
			const type = $(this).data('type') || 'plugin';
			activatePlugin(file, type, $(this));
		});

		$('#plugin-grid-container').on('click', '.btn-deactivate-plugin', function() {
			const file = $(this).data('file');
			deactivatePlugin(file, $(this));
		});

		$('#plugin-grid-container').on('click', '.btn-delete-plugin', function() {
			const file = $(this).data('file');
			const type = $(this).data('type') || 'plugin';
			if (confirm('Bu öğeyi WordPress sisteminizden silmek istediğinize emin misiniz?')) {
				deletePlugin(file, type, $(this));
			}
		});

		$('#plugin-grid-container').on('click', '.btn-open-details', function() {
			const slug = $(this).data('slug');
			const item = pluginsData.find(p => p.slug === slug);
			if (item) {
				openModal(item);
			}
		});

		// Bulk Action Buttons
		$('#btn-bulk-install').on('click', function() {
			if (confirm('Listelenen tüm eklentileri ve temaları kurmak istediğinize emin misiniz?')) {
				bulkAction('gnn_wpdashboard_bulk_install', $(this), 'Tüm eklenti ve temalar kuruluyor, lütfen bekleyin...');
			}
		});

		$('#btn-bulk-activate').on('click', function() {
			if (confirm('Listelenen tüm eklenti ve temaları etkinleştirmek istediğinize emin misiniz?')) {
				bulkAction('gnn_wpdashboard_bulk_activate', $(this), 'Tüm ögeler etkinleştiriliyor...');
			}
		});

		$('#btn-bulk-deactivate').on('click', function() {
			if (confirm('Listelenen tüm eklentileri devre dışı bırakmak istediğinize emin misiniz?')) {
				bulkAction('gnn_wpdashboard_bulk_deactivate', $(this), 'Tüm eklentiler devre dışı bırakılıyor...');
			}
		});

		$('#btn-bulk-delete').on('click', function() {
			if (confirm('DİKKAT: Tüm kurulu GNN eklentileri ve temaları silinecektir. Emin misiniz?')) {
				bulkAction('gnn_wpdashboard_bulk_delete', $(this), 'Tüm ögeler siliniyor...');
			}
		});
	});

	function loadPlugins(callback) {
		$.ajax({
			url: GNNWPDashboard.ajax_url,
			type: 'POST',
			data: {
				action: 'gnn_wpdashboard_get_plugins',
				nonce: GNNWPDashboard.nonce
			},
			success: function(response) {
				if (response.success) {
					pluginsData = response.data;
					updateCounters();
					renderGrid();
				}
				if (callback) callback();
			},
			error: function() {
				if (callback) callback();
			}
		});
	}

	function updateCounters() {
		let total = pluginsData.length;
		let active = pluginsData.filter(p => p.active).length;
		let updates = pluginsData.filter(p => p.has_update).length;
		let inactive = pluginsData.filter(p => p.installed && !p.active).length;
		let uninstalled = pluginsData.filter(p => !p.installed).length;
		let installedTotal = pluginsData.filter(p => p.installed).length;

		$('#count-all').text(total);
		$('#count-active').text(active);
		$('#count-updates').text(updates);
		$('#count-inactive').text(inactive);
		$('#count-uninstalled').text(uninstalled);

		// Dynamic Bulk Action Buttons Visibility
		// 1. Tümünü Kur: Visible ONLY if there are uninstalled plugins/themes
		if (uninstalled > 0) {
			$('#btn-bulk-install').removeClass('hidden');
		} else {
			$('#btn-bulk-install').addClass('hidden');
		}

		// 2. Tümünü Etkinleştir: Visible ONLY if there are installed plugins/themes that are NOT active
		if (inactive > 0) {
			$('#btn-bulk-activate').removeClass('hidden');
		} else {
			$('#btn-bulk-activate').addClass('hidden');
		}

		// 3. Tümünü Devre Dışı Bırak: Visible ONLY if there are active plugins/themes
		if (active > 0) {
			$('#btn-bulk-deactivate').removeClass('hidden');
		} else {
			$('#btn-bulk-deactivate').addClass('hidden');
		}

		// 4. Tümünü Sil: Visible ONLY if there are installed plugins/themes
		if (installedTotal > 0) {
			$('#btn-bulk-delete').removeClass('hidden');
		} else {
			$('#btn-bulk-delete').addClass('hidden');
		}

		if (updates > 0) {
			const updateItems = pluginsData.filter(p => p.has_update);
			if (updates === 1) {
				const item = updateItems[0];
				if (item.type === 'theme') {
					$('#update-notification-title').text('Tema Güncellemesi Mevcut');
					$('#update-notification-text').html(
						`<strong>${item.name}</strong> teması için yeni sürüm (v${item.latest_version}) mevcut.`
					);
				} else {
					$('#update-notification-title').text('Eklenti Güncellemesi Mevcut');
					$('#update-notification-text').html(
						`<strong>${item.name}</strong> eklentisi için yeni sürüm (v${item.latest_version}) mevcut.`
					);
				}
			} else {
				const hasPlugins = updateItems.some(p => p.type === 'plugin');
				const hasThemes = updateItems.some(p => p.type === 'theme');
				if (hasPlugins && hasThemes) {
					$('#update-notification-title').text('Eklenti ve Tema Güncellemeleri Mevcut');
					$('#update-notification-text').html(
						`<strong>${updates}</strong> öge (eklenti ve tema) için yeni sürüm mevcut.`
					);
				} else if (hasThemes) {
					$('#update-notification-title').text('Tema Güncellemeleri Mevcut');
					$('#update-notification-text').html(
						`<strong>${updates}</strong> tema için yeni sürüm mevcut.`
					);
				} else {
					$('#update-notification-title').text('Eklenti Güncellemeleri Mevcut');
					$('#update-notification-text').html(
						`<strong>${updates}</strong> eklenti için yeni sürüm mevcut.`
					);
				}
			}
			$('#update-notification-banner').removeClass('hidden');
		} else {
			$('#update-notification-banner').addClass('hidden');
		}
	}

	function renderGrid() {
		const searchVal = $('#search-input').val().toLowerCase().trim();
		const $container = $('#plugin-grid-container');
		$container.empty();

		let filtered = pluginsData.filter(function(item) {
			// Status filter
			if (activeStatusFilter === 'active' && !item.active) return false;
			if (activeStatusFilter === 'updates' && !item.has_update) return false;
			if (activeStatusFilter === 'inactive' && (!item.installed || item.active)) return false;
			if (activeStatusFilter === 'uninstalled' && item.installed) return false;

			// Category filter
			if (activeCategoryFilter !== 'all' && item.category !== activeCategoryFilter) return false;

			// Search filter
			if (searchVal !== '') {
				const matchName = item.name.toLowerCase().includes(searchVal);
				const matchDesc = item.description.toLowerCase().includes(searchVal);
				if (!matchName && !matchDesc) return false;
			}

			return true;
		});

		$('#plugin-total-count').text(filtered.length);

		if (filtered.length === 0) {
			$container.html(`
				<div class="col-span-full py-12 text-center text-on-surface-variant">
					<span class="material-symbols-outlined text-4xl mb-2 text-outline">search_off</span>
					<p class="text-sm font-medium">Kriterlere uygun öğe bulunamadı.</p>
				</div>
			`);
			return;
		}

		filtered.forEach(function(item) {
			$container.append(createCardHTML(item));
		});
	}

	function createCardHTML(item) {
		const displayVersion = item.installed && item.installed_version ? item.installed_version : item.latest_version;
		let badgeHTML = `<span class="version-badge-pill">v${displayVersion}</span>`;
		let borderClass = 'border-outline-variant';
		let statusBadge = '';

		if (item.has_update) {
			badgeHTML = `<span class="version-badge-pill version-update-glow">
				<span class="material-symbols-outlined text-[13px]">arrow_upward</span> v${item.installed_version} ➔ v${item.latest_version}
			</span>`;
			borderClass = 'border-2 border-amber-300';
			statusBadge = `<span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-amber-300">Güncelleme Var</span>`;
		} else if (item.active) {
			borderClass = 'border-emerald-300';
			statusBadge = `<span class="bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 border border-emerald-300">
				<span class="material-symbols-outlined text-[13px] font-bold">check_circle</span> Aktif
			</span>`;
		} else if (item.installed) {
			statusBadge = `<span class="bg-gray-100 text-gray-700 text-[11px] font-bold px-2 py-0.5 rounded-full border border-gray-300">Pasif</span>`;
		}

		let actionButtonsHTML = '';
		if (item.slug === 'gnn-wpdashboard') {
			if (item.has_update) {
				actionButtonsHTML = `
					<button data-slug="${item.slug}" class="btn-install-plugin bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 shadow-sm border-none cursor-pointer">
						<span class="material-symbols-outlined text-sm">update</span> Güncelle (v${item.latest_version})
					</button>
				`;
			} else {
				actionButtonsHTML = `
					<span class="text-xs text-gray-500 font-semibold flex items-center gap-1">
						<span class="material-symbols-outlined text-sm text-emerald-600">verified</span> Sistem Paneli
					</span>
				`;
			}
		} else if (item.has_update) {
			actionButtonsHTML = `
				<button data-slug="${item.slug}" class="btn-install-plugin bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 shadow-sm border-none cursor-pointer">
					<span class="material-symbols-outlined text-sm">update</span> Güncelle (v${item.latest_version})
				</button>
			`;
		} else if (item.active) {
			actionButtonsHTML = `
				<button data-file="${item.file}" data-type="${item.type}" class="btn-deactivate-plugin bg-surface-container-high hover:bg-surface-variant text-on-surface px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border border-outline-variant flex items-center gap-1 cursor-pointer">
					<span class="material-symbols-outlined text-sm text-gray-600">pause</span> Devre Dışı Bırak
				</button>
			`;
		} else if (item.installed) {
			actionButtonsHTML = `
				<div class="flex items-center gap-2">
					<button data-file="${item.file}" data-type="${item.type}" class="btn-delete-plugin text-red-600 hover:text-red-800 font-semibold text-xs flex items-center gap-0.5 border-none bg-transparent cursor-pointer">
						<span class="material-symbols-outlined text-sm">delete</span> Sil
					</button>
					<button data-file="${item.file}" data-type="${item.type}" class="btn-activate-plugin bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex items-center gap-1 border-none cursor-pointer">
						<span class="material-symbols-outlined text-sm">play_arrow</span> Etkinleştir
					</button>
				</div>
			`;
		} else {
			actionButtonsHTML = `
				<button data-slug="${item.slug}" class="btn-install-plugin bg-primary hover:bg-primary-container text-on-primary px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">download</span> Şimdi Kur
				</button>
			`;
		}

		return `
			<article class="plugin-card bg-surface-container-lowest rounded-xl border ${borderClass} overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col">
				<div class="h-32 ${item.banner_class || 'bg-surface-container-high'} relative overflow-hidden flex items-center justify-center shadow-inner">
					<span class="material-symbols-outlined text-6xl text-white/20 select-none">${item.icon}</span>
					
					<div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap">
						${badgeHTML}
					</div>

					<a href="${item.repo_url}" target="_blank" class="absolute top-3 right-3 bg-black/40 hover:bg-black/80 text-white p-1.5 rounded-full backdrop-blur-md transition-colors flex items-center justify-center text-white" title="GitHub Reposuna Git">
						<span class="material-symbols-outlined text-base">code</span>
					</a>
				</div>

				<div class="p-5 flex flex-col flex-grow relative">
					<div class="gnn-card-icon-badge">
						<span class="material-symbols-outlined">${item.icon}</span>
					</div>

					<div class="mt-3 flex-grow">
						<div class="flex items-center justify-between">
							<h3 class="text-lg font-bold text-on-surface m-0 mb-1">${item.name}</h3>
							${statusBadge}
						</div>
						<p class="text-xs text-on-surface-variant m-0 mb-2 font-medium">${item.owner}/${item.repo}</p>
						<p class="text-sm text-on-surface-variant line-clamp-2 leading-relaxed m-0">${item.description}</p>
					</div>

					<div class="mt-4 pt-3 border-t border-outline-variant flex items-center justify-between">
						<button data-slug="${item.slug}" class="btn-open-details text-primary font-semibold text-xs hover:underline flex items-center gap-0.5 border-none bg-transparent cursor-pointer">
							Detaylar
						</button>

						${actionButtonsHTML}
					</div>
				</div>
			</article>
		`;
	}

	function installPlugin(slug, $btn) {
		$btn.prop('disabled', true).html('<span class="material-symbols-outlined text-base animate-spin">progress_activity</span> Yükleniyor...');

		$.ajax({
			url: GNNWPDashboard.ajax_url,
			type: 'POST',
			data: {
				action: 'gnn_wpdashboard_install_plugin',
				nonce: GNNWPDashboard.nonce,
				slug: slug
			},
			success: function(res) {
				if (res.success) {
					window.location.reload();
				} else {
					alert(res.data || 'Yükleme sırasında hata oluştu.');
					loadPlugins();
				}
			},
			error: function() {
				alert('Sunucu hatası oluştu.');
				loadPlugins();
			}
		});
	}

	function activatePlugin(file, type, $btn) {
		$btn.prop('disabled', true).html('<span class="material-symbols-outlined text-base animate-spin">progress_activity</span>');

		$.ajax({
			url: GNNWPDashboard.ajax_url,
			type: 'POST',
			data: {
				action: 'gnn_wpdashboard_activate_plugin',
				nonce: GNNWPDashboard.nonce,
				file: file,
				type: type
			},
			success: function(res) {
				if (res.success) {
					window.location.reload();
				} else {
					alert(res.data || 'Etkinleştirme sırasında hata oluştu.');
					loadPlugins();
				}
			}
		});
	}

	function deactivatePlugin(file, $btn) {
		$btn.prop('disabled', true).html('<span class="material-symbols-outlined text-base animate-spin">progress_activity</span>');

		$.ajax({
			url: GNNWPDashboard.ajax_url,
			type: 'POST',
			data: {
				action: 'gnn_wpdashboard_deactivate_plugin',
				nonce: GNNWPDashboard.nonce,
				file: file
			},
			success: function(res) {
				if (res.success) {
					window.location.reload();
				} else {
					alert(res.data || 'Devre dışı bırakma sırasında hata oluştu.');
					loadPlugins();
				}
			}
		});
	}

	function deletePlugin(file, type, $btn) {
		$btn.prop('disabled', true);

		$.ajax({
			url: GNNWPDashboard.ajax_url,
			type: 'POST',
			data: {
				action: 'gnn_wpdashboard_delete_plugin',
				nonce: GNNWPDashboard.nonce,
				file: file,
				type: type
			},
			success: function(res) {
				if (res.success) {
					window.location.reload();
				} else {
					alert(res.data || 'Silme sırasında hata oluştu.');
					loadPlugins();
				}
			}
		});
	}

	function openModal(item) {
		$('#modal-icon').text(item.icon);
		$('#modal-title').text(item.name);
		$('#modal-sub').text('Sürüm: v' + item.latest_version + ' • ' + item.owner + '/' + item.repo);
		$('#modal-desc').text(item.description);
		$('#modal-changelog').text(item.changelog || 'Sürüm notu girilmemiş.');
		$('#modal-repo-link').attr('href', item.repo_url);
		$('#details-modal').removeClass('hidden');
	}

	function showBulkOverlay(message) {
		let $overlay = $('#gnn-bulk-overlay');
		if ($overlay.length === 0) {
			$overlay = $(`
				<div id="gnn-bulk-overlay" class="fixed inset-0 bg-slate-900/70 backdrop-blur-md z-[9999999] flex flex-col items-center justify-center text-white">
					<div class="bg-slate-900 border border-slate-700 p-8 rounded-2xl shadow-2xl flex flex-col items-center gap-4 max-w-sm text-center">
						<span class="material-symbols-outlined text-5xl animate-spin text-emerald-400">progress_activity</span>
						<h3 id="gnn-overlay-title" class="text-lg font-bold text-white m-0">İşlem Gerçekleştiriliyor</h3>
						<p id="gnn-overlay-desc" class="text-xs text-slate-300 m-0">Lütfen bekleyin, bu işlem biraz zaman alabilir...</p>
					</div>
				</div>
			`);
			$('body').append($overlay);
		} else {
			$overlay.removeClass('hidden');
		}
		if (message) {
			$('#gnn-overlay-desc').text(message);
		}
	}

	function hideBulkOverlay() {
		$('#gnn-bulk-overlay').addClass('hidden');
	}

	function bulkAction(action, $btn, message) {
		$btn.prop('disabled', true).addClass('opacity-50');
		showBulkOverlay(message);

		$.ajax({
			url: GNNWPDashboard.ajax_url,
			type: 'POST',
			data: {
				action: action,
				nonce: GNNWPDashboard.nonce
			},
			success: function(res) {
				if (res.success) {
					window.location.reload();
				} else {
					hideBulkOverlay();
					alert(res.data || 'İşlem sırasında bir hata oluştu.');
					loadPlugins();
				}
			},
			error: function() {
				hideBulkOverlay();
				alert('Sunucu yanıt vermedi veya zaman aşımına uğradı.');
				$btn.prop('disabled', false).removeClass('opacity-50');
			}
		});
	}

})(jQuery);
