/**
 * GNN WPDashboard Admin JavaScript
 */
(function($) {
	'use strict';

	let pluginsData = [];
	let activeStatusFilter = 'all';
	let activeCategoryFilter = 'all';

	/**
	 * Escape HTML special characters to prevent DOM XSS.
	 *
	 * @param {*} str Raw input value.
	 * @return {string} Escaped string.
	 */
	function escapeHtml(str) {
		if (str === null || str === undefined) {
			return '';
		}
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	$(document).ready(function() {
		loadPlugins();

		// Sync Button — clear cache first, then reload live GitHub data
		$('#btn-sync-plugins').on('click', function() {
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
			$('.status-tab').removeClass('active-tab text-slate-900 font-semibold').addClass('text-slate-600 font-medium');
			$(this).addClass('active-tab text-slate-900 font-semibold').removeClass('text-slate-600 font-medium');

			activeStatusFilter = $(this).data('status');
			renderGrid();
		});

		// Category Tab Filters
		$('.category-tab').on('click', function() {
			$('.category-tab').removeClass('active-cat-tab text-slate-900 font-semibold').addClass('text-slate-600 font-medium');
			$(this).addClass('active-cat-tab text-slate-900 font-semibold').removeClass('text-slate-600 font-medium');

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

		let canBulkActivate = pluginsData.filter(p => p.installed && !p.active && p.type === 'plugin').length > 0;
		let canBulkDeactivate = pluginsData.filter(p => p.installed && p.active && p.type === 'plugin' && p.slug !== 'gnn-wpdashboard').length > 0;
		let canBulkDelete = pluginsData.filter(p => p.installed && p.slug !== 'gnn-wpdashboard' && !(p.type === 'theme' && p.active)).length > 0;

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

		// 2. Tümünü Etkinleştir: Visible ONLY if there are installed plugins that are NOT active (themes excluded)
		if (canBulkActivate) {
			$('#btn-bulk-activate').removeClass('hidden');
		} else {
			$('#btn-bulk-activate').addClass('hidden');
		}

		// 3. Tümünü Devre Dışı Bırak: Visible ONLY if there are active plugins other than gnn-wpdashboard
		if (canBulkDeactivate) {
			$('#btn-bulk-deactivate').removeClass('hidden');
		} else {
			$('#btn-bulk-deactivate').addClass('hidden');
		}

		// 4. Tümünü Sil: Visible ONLY if there are deletable installed plugins/themes other than gnn-wpdashboard
		if (canBulkDelete) {
			$('#btn-bulk-delete').removeClass('hidden');
		} else {
			$('#btn-bulk-delete').addClass('hidden');
		}

		if (updates > 0) {
			const updateItems = pluginsData.filter(p => p.has_update);
			if (updates === 1) {
				const item = updateItems[0];
				const safeName = escapeHtml(item.name);
				const safeVer = escapeHtml(item.latest_version);
				if (item.type === 'theme') {
					$('#update-notification-title').text('Tema Güncellemesi Mevcut');
					$('#update-notification-text').html(
						`<strong>${safeName}</strong> teması için yeni sürüm (v${safeVer}) mevcut.`
					);
				} else {
					$('#update-notification-title').text('Eklenti Güncellemesi Mevcut');
					$('#update-notification-text').html(
						`<strong>${safeName}</strong> eklentisi için yeni sürüm (v${safeVer}) mevcut.`
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

			// Update Hero Status Widget
			$('#hero-status-icon-wrap').removeClass('bg-emerald-50 text-emerald-600').addClass('bg-amber-50 text-amber-600');
			$('#hero-status-icon').text('system_update');
			$('#hero-status-title').text(updates + ' Güncelleme Mevcut');
			$('#hero-status-sub').text('Eklenti/temalarınızı güncelleyin');
		} else {
			$('#update-notification-banner').addClass('hidden');

			// Update Hero Status Widget
			$('#hero-status-icon-wrap').removeClass('bg-amber-50 text-amber-600').addClass('bg-emerald-50 text-emerald-600');
			$('#hero-status-icon').text('check_circle');
			$('#hero-status-title').text('Sistem Güncel');
			const selfItem = pluginsData.find(p => p.slug === 'gnn-wpdashboard');
			const selfVer = selfItem && selfItem.installed_version ? selfItem.installed_version : '1.2.0';
			$('#hero-status-sub').text('GNN Dashboard v' + selfVer);
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
		const safeDisplayVer = escapeHtml(displayVersion);
		const safeInstalledVer = escapeHtml(item.installed_version);
		const safeLatestVer = escapeHtml(item.latest_version);
		const safeSlug = escapeHtml(item.slug);
		const safeFile = escapeHtml(item.file);
		const safeType = escapeHtml(item.type);
		const safeName = escapeHtml(item.name);
		const safeOwner = escapeHtml(item.owner);
		const safeRepo = escapeHtml(item.repo);
		const safeDesc = escapeHtml(item.description);
		const safeIcon = escapeHtml(item.icon);
		const safeRepoUrl = escapeHtml(item.repo_url);

		let borderClass = 'border-slate-200/80';
		let versionHTML = '';

		if (item.has_update) {
			borderClass = 'border-amber-300 ring-2 ring-amber-300/40';
			versionHTML = `
				<span class="text-[11px] font-bold text-amber-600 flex items-center gap-0.5">
					<span class="material-symbols-outlined text-xs">arrow_upward</span> v${safeInstalledVer} ➔ v${safeLatestVer}
				</span>
				<button data-slug="${safeSlug}" class="btn-open-details text-slate-400 hover:text-slate-700 text-[11px] text-left border-none bg-transparent cursor-pointer p-0 font-medium mt-0.5">
					Detaylar
				</button>
			`;
		} else {
			versionHTML = `
				<span class="text-xs text-slate-400 font-medium">Sürüm: ${safeDisplayVer}</span>
				<button data-slug="${safeSlug}" class="btn-open-details text-slate-400 hover:text-slate-700 text-[11px] text-left border-none bg-transparent cursor-pointer p-0 font-medium mt-0.5">
					Detaylar
				</button>
			`;
		}

		let actionButtonsHTML = '';
		if (item.slug === 'gnn-wpdashboard') {
			if (item.has_update) {
				actionButtonsHTML = `
					<button data-slug="${safeSlug}" class="btn-install-plugin gnn-btn-action-install bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer active:scale-95">
						<span class="material-symbols-outlined text-sm">upgrade</span> Güncelle
					</button>
				`;
			} else {
				actionButtonsHTML = `
					<span class="gnn-btn-action-active bg-slate-100 text-slate-600 text-xs font-semibold px-3.5 py-1.5 rounded-lg flex items-center gap-1.5">
						<span class="material-symbols-outlined text-xs text-emerald-600">verified</span> Sistem Paneli
					</span>
				`;
			}
		} else if (item.has_update) {
			actionButtonsHTML = `
				<button data-slug="${safeSlug}" class="btn-install-plugin gnn-btn-action-install bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer active:scale-95">
					<span class="material-symbols-outlined text-sm">upgrade</span> Güncelle
				</button>
			`;
		} else if (item.active) {
			actionButtonsHTML = `
				<button data-file="${safeFile}" data-type="${safeType}" class="btn-deactivate-plugin gnn-btn-action-active bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all border border-slate-200/80 flex items-center gap-1.5 cursor-pointer" title="Devre Dışı Bırak">
					<span class="material-symbols-outlined text-xs text-emerald-600">check</span> Aktif
				</button>
			`;
		} else if (item.installed) {
			actionButtonsHTML = `
				<div class="flex items-center gap-1.5">
					<button data-file="${safeFile}" data-type="${safeType}" class="btn-delete-plugin text-slate-400 hover:text-red-600 font-semibold text-xs flex items-center justify-center p-1.5 rounded-lg border-none bg-transparent cursor-pointer transition-colors" title="Kaldır / Sil">
						<span class="material-symbols-outlined text-base">delete</span>
					</button>
					<button data-file="${safeFile}" data-type="${safeType}" class="btn-activate-plugin bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex items-center gap-1 border-none cursor-pointer active:scale-95">
						Etkinleştir
					</button>
				</div>
			`;
		} else {
			actionButtonsHTML = `
				<button data-slug="${safeSlug}" class="btn-install-plugin gnn-btn-action-install bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer active:scale-95">
					Şimdi Kur
				</button>
			`;
		}

		return `
			<article class="plugin-card gnn-modern-card bg-white rounded-2xl border ${borderClass} shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between p-6 relative">
				<div class="flex items-center justify-between w-full mb-1">
					<span class="gnn-card-star text-slate-300 hover:text-amber-400 cursor-pointer transition-colors material-symbols-outlined text-lg" title="Favorilere Ekle">star</span>
					<a href="${safeRepoUrl}" target="_blank" class="gnn-card-github text-slate-400 hover:text-slate-800 transition-colors flex items-center justify-center p-1 rounded-lg hover:bg-slate-100" title="GitHub Deposu">
						<span class="material-symbols-outlined text-base">code</span>
					</a>
				</div>

				<div class="gnn-card-center-brand flex flex-col items-center justify-center text-center my-3">
					<div class="gnn-brand-icon-box gnn-icon-${safeSlug} w-16 h-16 rounded-2xl flex items-center justify-center mb-3 shadow-sm transition-transform hover:scale-105">
						<span class="material-symbols-outlined text-3xl">${safeIcon}</span>
					</div>

					<h3 class="text-base font-bold text-slate-900 m-0 text-center tracking-tight leading-snug">${safeName}</h3>
					<p class="text-[11px] text-slate-400 font-medium m-0 mt-0.5">${safeOwner}/${safeRepo}</p>
					<p class="text-xs text-slate-500 text-center line-clamp-2 leading-relaxed m-0 mt-2 h-9 overflow-hidden">${safeDesc}</p>
				</div>

				<div class="gnn-card-footer mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
					<div class="flex flex-col">
						${versionHTML}
					</div>

					<div>
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
			},
			error: function() {
				alert('Sunucu hatası oluştu.');
				loadPlugins();
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
			},
			error: function() {
				alert('Sunucu hatası oluştu.');
				loadPlugins();
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
			},
			error: function() {
				alert('Sunucu hatası oluştu.');
				loadPlugins();
			}
		});
	}

	function openModal(item) {
		const ver = item.installed && item.installed_version ? item.installed_version : item.latest_version;
		const verPrefix = ver ? 'Sürüm: v' + ver + ' • ' : '';
		$('#modal-icon').text(item.icon);
		$('#modal-title').text(item.name);
		$('#modal-sub').text(verPrefix + item.owner + '/' + item.repo);
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
