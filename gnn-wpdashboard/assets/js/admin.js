(function () {
	'use strict';

	let pluginsData = [];
	let activeStatusFilter = 'all';
	let activeCategoryFilter = 'all';
	let lastFocusedElement = null;

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

	function sanitizeSafeUrl(url) {
		if (!url || typeof url !== 'string') {
			return '#';
		}
		const trimmed = url.trim();
		try {
			const parsed = new URL(trimmed, window.location.origin);
			if (parsed.protocol === 'https:' || parsed.protocol === 'http:') {
				return parsed.href;
			}
		} catch (e) {
			return '#';
		}
		return '#';
	}

	function isSupportedMediaUrl(url) {
		const safe = sanitizeSafeUrl(url);
		if (safe === '#') {
			return false;
		}
		try {
			const parsed = new URL(safe);
			return /\.(avif|webp|png|jpe?g|gif|svg)$/i.test(parsed.pathname);
		} catch (e) {
			return false;
		}
	}

	function showToast(message, type = 'info') {
		const container = document.getElementById('gnn-toast-container');
		if (!container) {
			return;
		}
		const toast = document.createElement('div');
		toast.className = 'gnn-toast' + (type === 'error' ? ' gnn-toast-error' : type === 'success' ? ' gnn-toast-success' : '');
		const icon = document.createElement('span');
		icon.className = 'material-symbols-outlined text-base';
		icon.setAttribute('aria-hidden', 'true');
		icon.textContent = type === 'error' ? 'error' : type === 'success' ? 'check_circle' : 'info';

		const text = document.createElement('span');
		text.textContent = message;

		toast.appendChild(icon);
		toast.appendChild(text);
		container.appendChild(toast);

		setTimeout(() => {
			if (toast.parentNode) {
				toast.parentNode.removeChild(toast);
			}
		}, 4500);
	}

	async function postAjax(action, extraParams = {}) {
		const body = new URLSearchParams({
			action: action,
			nonce: GNNWPDashboard.nonce,
			...extraParams
		});

		const response = await fetch(GNNWPDashboard.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: body.toString()
		});

		if (!response.ok) {
			throw new Error('HTTP ' + response.status);
		}
		return response.json();
	}

	function getFavorites() {
		try {
			const favs = localStorage.getItem('gnn_favorites');
			return favs ? JSON.parse(favs) : [];
		} catch (e) {
			return [];
		}
	}

	function isFavorite(slug) {
		return getFavorites().includes(slug);
	}

	function toggleFavorite(slug) {
		let favs = getFavorites();
		if (favs.includes(slug)) {
			favs = favs.filter(s => s !== slug);
		} else {
			favs.push(slug);
		}
		try {
			localStorage.setItem('gnn_favorites', JSON.stringify(favs));
		} catch (e) {}
		updateCounters();
		renderGrid();
	}

	function setActiveTabUI(selector, activeEl, activeClasses, inactiveClasses) {
		document.querySelectorAll(selector).forEach(el => {
			activeClasses.forEach(c => el.classList.remove(c));
			inactiveClasses.forEach(c => el.classList.add(c));
			el.setAttribute('aria-selected', 'false');
		});
		if (activeEl) {
			inactiveClasses.forEach(c => activeEl.classList.remove(c));
			activeClasses.forEach(c => activeEl.classList.add(c));
			activeEl.setAttribute('aria-selected', 'true');
		}
	}

	function setActiveCategoryTab(category) {
		const target = document.querySelector(`.category-tab[data-category="${CSS.escape(category)}"]`);
		setActiveTabUI('.category-tab', target, ['active-cat-tab', 'text-slate-900', 'font-semibold'], ['text-slate-600', 'font-medium']);
	}

	function setActiveStatusTab(status) {
		const target = document.querySelector(`.status-tab[data-status="${CSS.escape(status)}"]`);
		setActiveTabUI('.status-tab', target, ['active-tab', 'text-slate-900', 'font-semibold'], ['text-slate-600', 'font-medium']);
	}

	function jumpToUpdates() {
		activeCategoryFilter = 'all';
		activeStatusFilter = 'updates';
		setActiveCategoryTab('all');
		setActiveStatusTab('updates');
		updateCounters();
		renderGrid();
	}

	async function syncPlugins() {
		const icon = document.getElementById('sync-icon');
		const mobileBtn = document.getElementById('btn-sync-plugins-mobile');
		const mobileIcon = mobileBtn ? mobileBtn.querySelector('.material-symbols-outlined') : null;

		if (icon) icon.classList.add('animate-spin');
		if (mobileIcon) mobileIcon.classList.add('animate-spin');

		try {
			await postAjax('gnn_wpdashboard_clear_cache');
		} catch (e) {
			// Continue to reload plugin list even if cache clear fails.
		} finally {
			await loadPlugins();
			if (icon) icon.classList.remove('animate-spin');
			if (mobileIcon) mobileIcon.classList.remove('animate-spin');
		}
	}

	function closeModal() {
		const modal = document.getElementById('details-modal');
		if (modal) {
			modal.classList.add('hidden');
		}
		if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
			lastFocusedElement.focus();
			lastFocusedElement = null;
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		// Server Limits Notice Dismiss (persistent via localStorage)
		const serverBanner = document.getElementById('gnn-server-limits-banner');
		try {
			if (localStorage.getItem('gnn_dismiss_server_notice') === '1' && serverBanner) {
				serverBanner.classList.add('hidden');
			}
		} catch (e) {}

		const dismissBtn = document.getElementById('btn-dismiss-server-notice');
		if (dismissBtn && serverBanner) {
			dismissBtn.addEventListener('click', function () {
				try {
					localStorage.setItem('gnn_dismiss_server_notice', '1');
				} catch (e) {}
				serverBanner.classList.add('hidden');
			});
		}

		// Notification Banner Click & Keyboard — Jump to Updates
		const updateBanner = document.getElementById('update-notification-banner');
		if (updateBanner) {
			updateBanner.addEventListener('click', jumpToUpdates);
			updateBanner.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					jumpToUpdates();
				}
			});
		}

		loadPlugins();

		// Sync Buttons (Desktop & Mobile)
		const syncBtn = document.getElementById('btn-sync-plugins');
		if (syncBtn) syncBtn.addEventListener('click', syncPlugins);

		const syncMobileBtn = document.getElementById('btn-sync-plugins-mobile');
		if (syncMobileBtn) syncMobileBtn.addEventListener('click', syncPlugins);

		// Search Input
		const searchInput = document.getElementById('search-input');
		if (searchInput) {
			searchInput.addEventListener('input', renderGrid);
		}

		// Status Tab Filters
		document.querySelectorAll('.status-tab').forEach(tab => {
			tab.addEventListener('click', function () {
				const targetStatus = this.dataset.status;
				activeStatusFilter = targetStatus;

				if (targetStatus === 'updates') {
					const catUpdates = pluginsData.filter(p => (activeCategoryFilter === 'all' || p.category === activeCategoryFilter) && p.has_update).length;
					const globalUpdates = pluginsData.filter(p => p.has_update).length;
					if (catUpdates === 0 && globalUpdates > 0) {
						activeCategoryFilter = 'all';
						setActiveCategoryTab('all');
					}
				}

				if (targetStatus === 'favorites') {
					const favs = getFavorites();
					const catFavs = pluginsData.filter(p => (activeCategoryFilter === 'all' || p.category === activeCategoryFilter) && favs.includes(p.slug)).length;
					if (catFavs === 0 && favs.length > 0) {
						activeCategoryFilter = 'all';
						setActiveCategoryTab('all');
					}
				}

				setActiveStatusTab(targetStatus);
				updateCounters();
				renderGrid();
			});
		});

		// Category Tab Filters
		document.querySelectorAll('.category-tab').forEach(tab => {
			tab.addEventListener('click', function () {
				const targetCategory = this.dataset.category;
				activeCategoryFilter = targetCategory;
				setActiveCategoryTab(targetCategory);

				const favs = getFavorites();
				const scopedData = activeCategoryFilter === 'all' ? pluginsData : pluginsData.filter(p => p.category === activeCategoryFilter);
				let matchCount = scopedData.length;
				if (activeStatusFilter === 'active') matchCount = scopedData.filter(p => p.active).length;
				else if (activeStatusFilter === 'updates') matchCount = scopedData.filter(p => p.has_update).length;
				else if (activeStatusFilter === 'inactive') matchCount = scopedData.filter(p => p.installed && !p.active).length;
				else if (activeStatusFilter === 'uninstalled') matchCount = scopedData.filter(p => !p.installed).length;
				else if (activeStatusFilter === 'favorites') matchCount = scopedData.filter(p => favs.includes(p.slug)).length;

				if (matchCount === 0 && scopedData.length > 0) {
					activeStatusFilter = 'all';
					setActiveStatusTab('all');
				}

				updateCounters();
				renderGrid();
			});
		});

		// Modal Close Listeners
		const closeTop = document.getElementById('btn-close-modal');
		const closeBottom = document.getElementById('btn-close-modal-bottom');
		const detailsModal = document.getElementById('details-modal');

		if (closeTop) closeTop.addEventListener('click', closeModal);
		if (closeBottom) closeBottom.addEventListener('click', closeModal);
		if (detailsModal) {
			detailsModal.addEventListener('click', function (e) {
				if (e.target === detailsModal) {
					closeModal();
				}
			});
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && detailsModal && !detailsModal.classList.contains('hidden')) {
				closeModal();
			}
		});

		// Delegate Card Action Buttons
		const gridContainer = document.getElementById('plugin-grid-container');
		if (gridContainer) {
			gridContainer.addEventListener('click', function (e) {
				const favBtn = e.target.closest('.btn-toggle-favorite');
				if (favBtn) {
					e.preventDefault();
					e.stopPropagation();
					toggleFavorite(favBtn.dataset.slug);
					return;
				}

				const installBtn = e.target.closest('.btn-install-plugin');
				if (installBtn) {
					installPlugin(installBtn.dataset.slug, installBtn);
					return;
				}

				const activateBtn = e.target.closest('.btn-activate-plugin');
				if (activateBtn) {
					activatePlugin(activateBtn.dataset.file, activateBtn.dataset.type || 'plugin', activateBtn);
					return;
				}

				const deactivateBtn = e.target.closest('.btn-deactivate-plugin');
				if (deactivateBtn) {
					deactivatePlugin(deactivateBtn.dataset.file, deactivateBtn);
					return;
				}

				const deleteBtn = e.target.closest('.btn-delete-plugin');
				if (deleteBtn) {
					const file = deleteBtn.dataset.file;
					const type = deleteBtn.dataset.type || 'plugin';
					if (window.confirm('Bu öğeyi WordPress sisteminizden silmek istediğinize emin misiniz?')) {
						deletePlugin(file, type, deleteBtn);
					}
					return;
				}

				const detailsBtn = e.target.closest('.btn-open-details');
				if (detailsBtn) {
					const slug = detailsBtn.dataset.slug;
					const item = pluginsData.find(p => p.slug === slug);
					if (item) {
						openModal(item, detailsBtn);
					}
				}
			});
		}

		// Bulk Action Buttons
		const bulkInstall = document.getElementById('btn-bulk-install');
		if (bulkInstall) {
			bulkInstall.addEventListener('click', function () {
				if (window.confirm('Listelenen tüm eklentileri ve temaları kurmak istediğinize emin misiniz?')) {
					bulkAction('gnn_wpdashboard_bulk_install', this, 'Tüm eklenti ve temalar kuruluyor, lütfen bekleyin...');
				}
			});
		}

		const bulkActivate = document.getElementById('btn-bulk-activate');
		if (bulkActivate) {
			bulkActivate.addEventListener('click', function () {
				if (window.confirm('Listelenen tüm eklenti ve temaları etkinleştirmek istediğinize emin misiniz?')) {
					bulkAction('gnn_wpdashboard_bulk_activate', this, 'Tüm ögeler etkinleştiriliyor...');
				}
			});
		}

		const bulkDeactivate = document.getElementById('btn-bulk-deactivate');
		if (bulkDeactivate) {
			bulkDeactivate.addEventListener('click', function () {
				if (window.confirm('Listelenen tüm eklentileri devre dışı bırakmak istediğinize emin misiniz?')) {
					bulkAction('gnn_wpdashboard_bulk_deactivate', this, 'Tüm eklentiler devre dışı bırakılıyor...');
				}
			});
		}

		const bulkDelete = document.getElementById('btn-bulk-delete');
		if (bulkDelete) {
			bulkDelete.addEventListener('click', function () {
				if (window.confirm('DİKKAT: Tüm kurulu GNN eklentileri ve temaları silinecektir. Emin misiniz?')) {
					bulkAction('gnn_wpdashboard_bulk_delete', this, 'Tüm ögeler siliniyor...');
				}
			});
		}
	});

	async function loadPlugins() {
		try {
			const response = await postAjax('gnn_wpdashboard_get_plugins');
			if (response && response.success) {
				pluginsData = response.data || [];
				updateCounters();
				renderGrid();
			} else if (response && response.data) {
				showToast(response.data, 'error');
			}
		} catch (e) {
			showToast('Eklenti listesi yüklenirken sunucu hatası oluştu.', 'error');
		}
	}

	function setText(id, text) {
		const el = document.getElementById(id);
		if (el) el.textContent = text;
	}

	function toggleHidden(id, hide) {
		const el = document.getElementById(id);
		if (!el) return;
		if (hide) el.classList.add('hidden');
		else el.classList.remove('hidden');
	}

	function updateCounters() {
		const favorites = getFavorites();
		const scopedPlugins = activeCategoryFilter === 'all'
			? pluginsData
			: pluginsData.filter(p => p.category === activeCategoryFilter);

		const total = scopedPlugins.length;
		const active = scopedPlugins.filter(p => p.active).length;
		const updates = scopedPlugins.filter(p => p.has_update).length;
		const inactive = scopedPlugins.filter(p => p.installed && !p.active).length;
		const uninstalled = scopedPlugins.filter(p => !p.installed).length;
		const favCount = scopedPlugins.filter(p => favorites.includes(p.slug)).length;

		setText('count-all', total);
		setText('count-active', active);
		setText('count-updates', updates);
		setText('count-inactive', inactive);
		setText('count-uninstalled', uninstalled);
		setText('count-favorites', favCount);

		const canBulkActivate = scopedPlugins.some(p => p.installed && !p.active && p.type === 'plugin');
		const canBulkDeactivate = scopedPlugins.some(p => p.installed && p.active && p.type === 'plugin' && p.slug !== 'gnn-wpdashboard');
		const canBulkDelete = scopedPlugins.some(p => p.installed && p.slug !== 'gnn-wpdashboard' && !(p.type === 'theme' && p.active));

		toggleHidden('btn-bulk-install', uninstalled === 0);
		toggleHidden('btn-bulk-activate', !canBulkActivate);
		toggleHidden('btn-bulk-deactivate', !canBulkDeactivate);
		toggleHidden('btn-bulk-delete', !canBulkDelete);

		const globalUpdates = pluginsData.filter(p => p.has_update).length;
		const heroWrap = document.getElementById('hero-status-icon-wrap');
		const heroIcon = document.getElementById('hero-status-icon');
		const notifText = document.getElementById('update-notification-text');

		if (globalUpdates > 0) {
			const updateItems = pluginsData.filter(p => p.has_update);
			if (globalUpdates === 1) {
				const item = updateItems[0];
				const safeName = escapeHtml(item.name);
				const safeVer = escapeHtml(item.latest_version);
				const typeLabel = item.type === 'theme' ? 'Tema Güncellemesi' : 'Eklenti Güncellemesi';
				setText('update-notification-title', typeLabel + ' Mevcut');
				if (notifText) {
					notifText.innerHTML = `<strong>${safeName}</strong> ${item.type === 'theme' ? 'teması' : 'eklentisi'} için yeni sürüm (v${safeVer}) mevcut. Tıklayarak güncellemeleri görüntüleyin.`;
				}
			} else {
				setText('update-notification-title', 'Güncellemeler Mevcut');
				if (notifText) {
					notifText.innerHTML = `<strong>${globalUpdates}</strong> öge için yeni sürüm mevcut. Tıklayarak güncellemeleri görüntüleyin.`;
				}
			}
			toggleHidden('update-notification-banner', false);

			if (heroWrap) {
				heroWrap.classList.remove('bg-emerald-50', 'text-emerald-600');
				heroWrap.classList.add('bg-amber-50', 'text-amber-600');
			}
			if (heroIcon) heroIcon.textContent = 'system_update';
			setText('hero-status-title', globalUpdates + ' Güncelleme Mevcut');
			setText('hero-status-sub', 'Eklenti/temalarınızı güncelleyin');
		} else {
			toggleHidden('update-notification-banner', true);

			if (heroWrap) {
				heroWrap.classList.remove('bg-amber-50', 'text-amber-600');
				heroWrap.classList.add('bg-emerald-50', 'text-emerald-600');
			}
			if (heroIcon) heroIcon.textContent = 'check_circle';
			setText('hero-status-title', 'Sistem Güncel');
			const selfItem = pluginsData.find(p => p.slug === 'gnn-wpdashboard');
			const selfVer = selfItem && selfItem.installed_version ? selfItem.installed_version : '1.2.1';
			setText('hero-status-sub', 'GNN Dashboard v' + selfVer);
		}
	}

	function renderGrid() {
		const searchEl = document.getElementById('search-input');
		const searchVal = searchEl ? searchEl.value.toLowerCase().trim() : '';
		const container = document.getElementById('plugin-grid-container');
		if (!container) return;

		container.innerHTML = '';
		const favorites = getFavorites();

		const filtered = pluginsData.filter(function (item) {
			if (activeStatusFilter === 'active' && !item.active) return false;
			if (activeStatusFilter === 'updates' && !item.has_update) return false;
			if (activeStatusFilter === 'inactive' && (!item.installed || item.active)) return false;
			if (activeStatusFilter === 'uninstalled' && item.installed) return false;
			if (activeStatusFilter === 'favorites' && !favorites.includes(item.slug)) return false;

			if (activeCategoryFilter !== 'all' && item.category !== activeCategoryFilter) return false;

			if (searchVal !== '') {
				const matchName = item.name.toLowerCase().includes(searchVal);
				const matchDesc = item.description.toLowerCase().includes(searchVal);
				if (!matchName && !matchDesc) return false;
			}

			return true;
		});

		setText('plugin-total-count', filtered.length);

		if (filtered.length === 0) {
			container.innerHTML = `
				<div class="col-span-full py-12 text-center text-on-surface-variant">
					<span class="material-symbols-outlined text-4xl mb-2 text-outline" aria-hidden="true">search_off</span>
					<p class="text-sm font-medium">Kriterlere uygun öğe bulunamadı.</p>
				</div>
			`;
			return;
		}

		const html = filtered.map(createCardHTML).join('');
		container.innerHTML = html;
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
		const safeRepoUrl = escapeHtml(sanitizeSafeUrl(item.repo_url));

		let borderClass = 'border-slate-200/80';
		let versionHTML = '';

		const fav = isFavorite(item.slug);
		const favClass = fav ? 'text-amber-400 filled' : 'text-slate-300 hover:text-amber-400';
		const favTitle = fav ? 'Favorilerden Çıkar' : 'Favorilere Ekle';

		if (item.has_update) {
			borderClass = 'border-amber-300 ring-2 ring-amber-300/40';
			versionHTML = `
				<span class="text-[11px] font-bold text-amber-600 flex items-center gap-0.5">
					<span class="material-symbols-outlined text-xs" aria-hidden="true">arrow_upward</span> v${safeInstalledVer} ➔ v${safeLatestVer}
				</span>
				<button type="button" data-slug="${safeSlug}" class="btn-open-details text-slate-400 hover:text-slate-700 text-[11px] text-left border-none bg-transparent cursor-pointer p-0 font-medium mt-0.5">
					Detaylar
				</button>
			`;
		} else {
			versionHTML = `
				<span class="text-xs text-slate-400 font-medium">Sürüm: ${safeDisplayVer}</span>
				<button type="button" data-slug="${safeSlug}" class="btn-open-details text-slate-400 hover:text-slate-700 text-[11px] text-left border-none bg-transparent cursor-pointer p-0 font-medium mt-0.5">
					Detaylar
				</button>
			`;
		}

		let actionButtonsHTML = '';
		if (item.slug === 'gnn-wpdashboard') {
			if (item.has_update) {
				actionButtonsHTML = `
					<button type="button" data-slug="${safeSlug}" class="btn-install-plugin gnn-btn-action-install bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer active:scale-95">
						<span class="material-symbols-outlined text-sm" aria-hidden="true">upgrade</span> Güncelle
					</button>
				`;
			} else {
				actionButtonsHTML = `
					<span class="gnn-btn-action-active bg-slate-100 text-slate-600 text-xs font-semibold px-3.5 py-1.5 rounded-lg flex items-center gap-1.5">
						<span class="material-symbols-outlined text-xs text-emerald-600" aria-hidden="true">verified</span> Sistem Paneli
					</span>
				`;
			}
		} else if (item.has_update) {
			actionButtonsHTML = `
				<button type="button" data-slug="${safeSlug}" class="btn-install-plugin gnn-btn-action-install bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer active:scale-95">
					<span class="material-symbols-outlined text-sm" aria-hidden="true">upgrade</span> Güncelle
				</button>
			`;
		} else if (item.active) {
			actionButtonsHTML = `
				<button type="button" data-file="${safeFile}" data-type="${safeType}" class="btn-deactivate-plugin gnn-btn-action-active bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all border border-slate-200/80 flex items-center gap-1.5 cursor-pointer" title="Devre Dışı Bırak">
					<span class="material-symbols-outlined text-xs text-emerald-600" aria-hidden="true">check</span> Aktif
				</button>
			`;
		} else if (item.installed) {
			actionButtonsHTML = `
				<div class="flex items-center gap-1.5">
					<button type="button" data-file="${safeFile}" data-type="${safeType}" class="btn-delete-plugin text-slate-400 hover:text-red-600 font-semibold text-xs flex items-center justify-center p-1.5 rounded-lg border-none bg-transparent cursor-pointer transition-colors" title="Kaldır / Sil" aria-label="${safeName} kaldır">
						<span class="material-symbols-outlined text-base" aria-hidden="true">delete</span>
					</button>
					<button type="button" data-file="${safeFile}" data-type="${safeType}" class="btn-activate-plugin bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex items-center gap-1 border-none cursor-pointer active:scale-95">
						Etkinleştir
					</button>
				</div>
			`;
		} else {
			actionButtonsHTML = `
				<button type="button" data-slug="${safeSlug}" class="btn-install-plugin gnn-btn-action-install bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-sm border-none cursor-pointer active:scale-95">
					Şimdi Kur
				</button>
			`;
		}

		const iconMediaHTML = (item.image_url && isSupportedMediaUrl(item.image_url))
			? `<img src="${escapeHtml(sanitizeSafeUrl(item.image_url))}" alt="${safeName}" class="w-10 h-10 object-contain" loading="lazy" />`
			: `<span class="material-symbols-outlined text-3xl" aria-hidden="true">${safeIcon}</span>`;

		return `
			<article class="plugin-card gnn-modern-card bg-white rounded-2xl border ${borderClass} shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between p-6 relative">
				<div class="flex items-center justify-between w-full mb-1">
					<button type="button" data-slug="${safeSlug}" class="btn-toggle-favorite ${favClass} cursor-pointer transition-all material-symbols-outlined text-lg border-none bg-transparent p-0 flex items-center justify-center leading-none hover:scale-125" title="${favTitle}" aria-label="${favTitle}" aria-pressed="${fav ? 'true' : 'false'}">star</button>
					<a href="${safeRepoUrl}" target="_blank" rel="noopener noreferrer" class="gnn-card-github text-slate-400 hover:text-slate-800 transition-colors flex items-center justify-center p-1 rounded-lg hover:bg-slate-100" title="GitHub Deposu" aria-label="${safeName} GitHub Deposu">
						<span class="material-symbols-outlined text-base" aria-hidden="true">code</span>
					</a>
				</div>

				<div class="gnn-card-center-brand flex flex-col items-center justify-center text-center my-3">
					<div class="gnn-brand-icon-box gnn-icon-${safeSlug} w-16 h-16 rounded-2xl flex items-center justify-center mb-3 shadow-sm transition-transform hover:scale-105">
						${iconMediaHTML}
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

	async function installPlugin(slug, btn) {
		btn.disabled = true;
		btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin" aria-hidden="true">progress_activity</span> Yükleniyor...';

		try {
			const res = await postAjax('gnn_wpdashboard_install_plugin', { slug: slug });
			if (res && res.success) {
				window.location.reload();
			} else {
				showToast((res && res.data) || 'Yükleme sırasında hata oluştu.', 'error');
				loadPlugins();
			}
		} catch (e) {
			showToast('Sunucu hatası oluştu.', 'error');
			loadPlugins();
		}
	}

	async function activatePlugin(file, type, btn) {
		btn.disabled = true;
		btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin" aria-hidden="true">progress_activity</span>';

		try {
			const res = await postAjax('gnn_wpdashboard_activate_plugin', { file: file, type: type });
			if (res && res.success) {
				window.location.reload();
			} else {
				showToast((res && res.data) || 'Etkinleştirme sırasında hata oluştu.', 'error');
				loadPlugins();
			}
		} catch (e) {
			showToast('Sunucu hatası oluştu.', 'error');
			loadPlugins();
		}
	}

	async function deactivatePlugin(file, btn) {
		btn.disabled = true;
		btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin" aria-hidden="true">progress_activity</span>';

		try {
			const res = await postAjax('gnn_wpdashboard_deactivate_plugin', { file: file });
			if (res && res.success) {
				window.location.reload();
			} else {
				showToast((res && res.data) || 'Devre dışı bırakma sırasında hata oluştu.', 'error');
				loadPlugins();
			}
		} catch (e) {
			showToast('Sunucu hatası oluştu.', 'error');
			loadPlugins();
		}
	}

	async function deletePlugin(file, type, btn) {
		btn.disabled = true;

		try {
			const res = await postAjax('gnn_wpdashboard_delete_plugin', { file: file, type: type });
			if (res && res.success) {
				window.location.reload();
			} else {
				showToast((res && res.data) || 'Silme sırasında hata oluştu.', 'error');
				loadPlugins();
			}
		} catch (e) {
			showToast('Sunucu hatası oluştu.', 'error');
			loadPlugins();
		}
	}

	function openModal(item, triggerEl) {
		lastFocusedElement = triggerEl || document.activeElement;
		const ver = item.installed && item.installed_version ? item.installed_version : item.latest_version;
		const verPrefix = ver ? 'Sürüm: v' + ver + ' • ' : '';

		setText('modal-icon', item.icon);
		setText('modal-title', item.name);
		setText('modal-sub', verPrefix + item.owner + '/' + item.repo);
		setText('modal-desc', item.description);
		setText('modal-changelog', item.changelog || 'Sürüm notu girilmemiş.');

		const repoLink = document.getElementById('modal-repo-link');
		if (repoLink) {
			repoLink.setAttribute('href', sanitizeSafeUrl(item.repo_url));
		}

		const modal = document.getElementById('details-modal');
		if (modal) {
			modal.classList.remove('hidden');
		}

		const closeBtn = document.getElementById('btn-close-modal');
		if (closeBtn) {
			closeBtn.focus();
		}
	}

	function showBulkOverlay(message) {
		let overlay = document.getElementById('gnn-bulk-overlay');
		if (!overlay) {
			overlay = document.createElement('div');
			overlay.id = 'gnn-bulk-overlay';
			overlay.className = 'fixed inset-0 bg-slate-900/70 backdrop-blur-md z-[9999999] flex flex-col items-center justify-center text-white';
			overlay.setAttribute('role', 'status');
			overlay.setAttribute('aria-live', 'polite');
			overlay.innerHTML = `
				<div class="bg-slate-900 border border-slate-700 p-8 rounded-2xl shadow-2xl flex flex-col items-center gap-4 max-w-sm text-center">
					<span class="material-symbols-outlined text-5xl animate-spin text-emerald-400" aria-hidden="true">progress_activity</span>
					<h3 id="gnn-overlay-title" class="text-lg font-bold text-white m-0">İşlem Gerçekleştiriliyor</h3>
					<p id="gnn-overlay-desc" class="text-xs text-slate-300 m-0">Lütfen bekleyin, bu işlem biraz zaman alabilir...</p>
				</div>
			`;
			document.body.appendChild(overlay);
		} else {
			overlay.classList.remove('hidden');
		}
		if (message) {
			setText('gnn-overlay-desc', message);
		}
	}

	function hideBulkOverlay() {
		const overlay = document.getElementById('gnn-bulk-overlay');
		if (overlay) {
			overlay.classList.add('hidden');
		}
	}

	async function bulkAction(action, btn, message) {
		btn.disabled = true;
		btn.classList.add('opacity-50');
		showBulkOverlay(message);

		try {
			const res = await postAjax(action);
			if (res && res.success) {
				window.location.reload();
			} else {
				hideBulkOverlay();
				showToast((res && res.data) || 'İşlem sırasında bir hata oluştu.', 'error');
				btn.disabled = false;
				btn.classList.remove('opacity-50');
				loadPlugins();
			}
		} catch (e) {
			hideBulkOverlay();
			showToast('Sunucu yanıt vermedi veya zaman aşımına uğradı.', 'error');
			btn.disabled = false;
			btn.classList.remove('opacity-50');
		}
	}
})();
