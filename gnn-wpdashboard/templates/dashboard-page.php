<?php
/**
 * Dashboard Page Template — GNN WPDashboard
 * Modern SaaS Layout
 *
 * @package GNN_WPDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap gnn-wpdashboard-wrap font-body text-slate-800">
	<!-- Main Wrapper -->
	<div class="w-full max-w-[1440px] mx-auto flex flex-col gap-6 my-4">

		<!-- 1. Top Navigation Bar -->
		<nav class="gnn-topbar bg-white border border-slate-200/80 rounded-2xl px-6 py-3.5 shadow-sm flex flex-col lg:flex-row justify-between items-center gap-4">
			
			<!-- Brand & Status Pill -->
			<div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-start">
				<div class="flex items-center gap-2.5">
					<div class="gnn-brand-logo flex items-center justify-center font-bold text-white text-lg tracking-wider rounded-xl">
						GNN
					</div>
					<div class="flex flex-col">
						<span class="font-bold text-slate-900 text-sm leading-tight">GNN Dashboard</span>
						<span class="text-[11px] text-slate-400 font-medium">WordPress Ecosystem</span>
					</div>
				</div>
				<span class="gnn-registered-badge flex items-center gap-1 text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
					<span class="material-symbols-outlined text-xs">verified</span>
					<?php esc_html_e( 'AKTİF / REGISTERED', 'gnn-wpdashboard' ); ?>
				</span>
			</div>

			<!-- Category Tabs (Horizontal Top Navigation) -->
			<div class="gnn-nav-tabs flex items-center gap-1.5 overflow-x-auto w-full lg:w-auto pb-1 lg:pb-0 scrollbar-none">
				<button data-category="all" class="category-tab active-cat-tab flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">apps</span>
					<span><?php esc_html_e( 'Tümü', 'gnn-wpdashboard' ); ?></span>
				</button>
				<button data-category="SEO & Pazarlama" class="category-tab flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">search_check</span>
					<span>SEO & Pazarlama</span>
				</button>
				<button data-category="Güvenlik & Analiz" class="category-tab flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">shield</span>
					<span>Güvenlik & Analiz</span>
				</button>
				<button data-category="Medya & Dosya" class="category-tab flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">folder_zip</span>
					<span>Medya & Dosya</span>
				</button>
				<button data-category="Araçlar & Network" class="category-tab flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">travel_explore</span>
					<span>Araçlar & Network</span>
				</button>
				<button data-category="Temalar" class="category-tab flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span class="material-symbols-outlined text-base">palette</span>
					<span>Temalar</span>
				</button>
			</div>

			<!-- Quick Refresh Button -->
			<div class="hidden lg:flex items-center">
				<button id="btn-sync-plugins" class="gnn-btn-sync bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-2 border-none cursor-pointer shadow-sm active:scale-95">
					<span class="material-symbols-outlined text-base" id="sync-icon">refresh</span>
					<span><?php esc_html_e( 'Güncellemeleri Kontrol Et', 'gnn-wpdashboard' ); ?></span>
				</button>
			</div>
		</nav>

		<!-- 2. Sub-Header Hero Section -->
		<div class="gnn-hero-section flex flex-col md:flex-row justify-between items-start md:items-center gap-4 py-2">
			<div class="flex items-center gap-4">
				<div class="gnn-hero-icon-box w-14 h-14 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-center text-slate-700">
					<span class="material-symbols-outlined text-3xl">extension</span>
				</div>
				<div>
					<div class="text-xs text-slate-400 font-medium flex items-center gap-1.5 mb-1">
						<span>GNN</span>
						<span>/</span>
						<span class="text-slate-600 font-semibold"><?php esc_html_e( 'Eklentiler & Temalar', 'gnn-wpdashboard' ); ?></span>
					</div>
					<h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 m-0 tracking-tight leading-none">
						<?php esc_html_e( 'Eklentiler & Temalar', 'gnn-wpdashboard' ); ?>
					</h1>
				</div>
			</div>

			<!-- Hero Right: Dynamic System Status Card -->
			<div class="gnn-status-card bg-white border border-slate-200/80 rounded-2xl px-5 py-3 shadow-sm flex items-center gap-3.5 w-full md:w-auto">
				<div id="hero-status-icon-wrap" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
					<span class="material-symbols-outlined text-2xl" id="hero-status-icon">check_circle</span>
				</div>
				<div>
					<h4 id="hero-status-title" class="font-bold text-xs text-slate-900 m-0">
						<?php esc_html_e( 'Sistem Güncel', 'gnn-wpdashboard' ); ?>
					</h4>
					<p id="hero-status-sub" class="text-[11px] text-slate-500 m-0 font-medium">
						GNN Dashboard v<?php echo esc_html( GNN_WPDASHBOARD_VERSION ); ?>
					</p>
				</div>
			</div>
		</div>

		<!-- 3. Notice / Server Information Banner -->
		<div class="gnn-notice-banner bg-blue-50/70 border border-blue-200/70 text-blue-900 rounded-xl px-4 py-3 flex items-center gap-3 text-xs">
			<span class="material-symbols-outlined text-blue-600 text-lg shrink-0">info</span>
			<span class="flex-grow font-medium leading-relaxed">
				<?php esc_html_e( 'Sunucu limitleri: Sunucunuzun bellek ve yürütme limitlerine göre yalnızca ihtiyacınız olan eklentileri aktif tutmanız önerilir.', 'gnn-wpdashboard' ); ?>
			</span>
		</div>

		<!-- Update Available Notification Banner -->
		<div id="update-notification-banner" class="hidden bg-amber-50 border border-amber-200 text-amber-900 rounded-xl p-4 flex items-center justify-between shadow-sm">
			<div class="flex items-center gap-3">
				<div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
					<span class="material-symbols-outlined text-xl">system_update</span>
				</div>
				<div>
					<h4 id="update-notification-title" class="font-bold text-sm m-0"><?php esc_html_e( 'Güncelleme Mevcut', 'gnn-wpdashboard' ); ?></h4>
					<p id="update-notification-text" class="text-xs text-amber-800 m-0 font-medium"></p>
				</div>
			</div>
		</div>

		<!-- 4. Filter Toolbar: Status Segmented Control + Search Box -->
		<div class="gnn-filter-toolbar bg-white border border-slate-200/80 rounded-2xl p-3 shadow-sm flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3">
			
			<!-- Status Filter Segmented Control -->
			<div class="gnn-status-segmented flex items-center gap-1 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
				<button data-status="all" class="status-tab active-tab flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border-none cursor-pointer">
					<span><?php esc_html_e( 'Tümü', 'gnn-wpdashboard' ); ?></span>
					<span id="count-all" class="counter-badge bg-slate-100 text-slate-700 text-[10px] px-1.5 py-0.2 rounded-full font-bold">0</span>
				</button>

				<button data-status="uninstalled" class="status-tab flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span><?php esc_html_e( 'Hazır', 'gnn-wpdashboard' ); ?></span>
					<span id="count-uninstalled" class="counter-badge bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded-full font-bold">0</span>
				</button>

				<button data-status="active" class="status-tab flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span><?php esc_html_e( 'Aktif', 'gnn-wpdashboard' ); ?></span>
					<span id="count-active" class="counter-badge bg-emerald-100 text-emerald-800 text-[10px] px-1.5 py-0.2 rounded-full font-bold">0</span>
				</button>

				<button data-status="inactive" class="status-tab flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span><?php esc_html_e( 'Pasif', 'gnn-wpdashboard' ); ?></span>
					<span id="count-inactive" class="counter-badge bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded-full font-bold">0</span>
				</button>

				<button data-status="updates" class="status-tab flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all border-none cursor-pointer">
					<span><?php esc_html_e( 'Güncelleme', 'gnn-wpdashboard' ); ?></span>
					<span id="count-updates" class="counter-badge bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.2 rounded-full font-bold">0</span>
				</button>
			</div>

			<!-- Search & Mobile Refresh -->
			<div class="flex items-center gap-2">
				<div class="search-box-container flex-grow md:w-64">
					<span class="material-symbols-outlined search-icon text-base">search</span>
					<input class="bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-900 focus:bg-white w-full transition-all" id="search-input" placeholder="<?php esc_attr_e( 'Eklentilerde ara...', 'gnn-wpdashboard' ); ?>" type="text"/>
				</div>
				<button class="lg:hidden bg-slate-900 text-white p-2.5 rounded-xl border-none cursor-pointer flex items-center justify-center" onclick="jQuery('#btn-sync-plugins').trigger('click');">
					<span class="material-symbols-outlined text-base">refresh</span>
				</button>
			</div>
		</div>

		<!-- 5. Sub-Bar: Counter & Bulk Actions -->
		<div class="flex justify-between items-center px-1 text-xs">
			<span class="text-slate-500 font-medium">
				<?php esc_html_e( 'Toplam', 'gnn-wpdashboard' ); ?> <strong id="plugin-total-count" class="text-slate-900 font-bold">0</strong> <?php esc_html_e( 'öğe listeleniyor', 'gnn-wpdashboard' ); ?>
			</span>
			<div class="flex items-center gap-2 flex-wrap">
				<button id="btn-bulk-install" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 border-none cursor-pointer shadow-sm">
					<span class="material-symbols-outlined text-sm">download_for_offline</span> <?php esc_html_e( 'Tümünü Kur', 'gnn-wpdashboard' ); ?>
				</button>
				<button id="btn-bulk-activate" class="hidden bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 border-none cursor-pointer shadow-sm">
					<span class="material-symbols-outlined text-sm">play_circle</span> <?php esc_html_e( 'Tümünü Etkinleştir', 'gnn-wpdashboard' ); ?>
				</button>
				<button id="btn-bulk-deactivate" class="hidden bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 border-none cursor-pointer shadow-sm">
					<span class="material-symbols-outlined text-sm">pause_circle</span> <?php esc_html_e( 'Tümünü Devre Dışı Bırak', 'gnn-wpdashboard' ); ?>
				</button>
				<button id="btn-bulk-delete" class="hidden bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 border-none cursor-pointer shadow-sm">
					<span class="material-symbols-outlined text-sm">delete_forever</span> <?php esc_html_e( 'Tümünü Sil', 'gnn-wpdashboard' ); ?>
				</button>
			</div>
		</div>

		<!-- 6. Card Grid Container (3-Column Layout) -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="plugin-grid-container">
			<!-- Loading Skeleton -->
			<div class="col-span-full py-16 text-center text-slate-500 flex flex-col items-center gap-3">
				<span class="material-symbols-outlined text-4xl animate-spin text-slate-800">progress_activity</span>
				<p class="text-xs font-semibold"><?php esc_html_e( 'Eklenti ve temalar yükleniyor...', 'gnn-wpdashboard' ); ?></p>
			</div>
		</div>

		<!-- 7. Dashboard Footer -->
		<footer class="mt-8 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500 font-medium">
			<div>
				<span>© <?php echo esc_html( date( 'Y' ) ); ?> GNN Ecosystem. All rights reserved.</span>
			</div>
			<div class="flex items-center gap-4">
				<a href="https://github.com/BigDesigner" target="_blank" class="text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1 no-underline">
					<span class="material-symbols-outlined text-sm">code</span> GitHub
				</a>
				<span class="text-slate-300">•</span>
				<span class="text-slate-400">GNN WPDashboard v<?php echo esc_html( GNN_WPDASHBOARD_VERSION ); ?></span>
			</div>
		</footer>

	</div>
</div>

<!-- Plugin Details Modal -->
<div id="details-modal" class="hidden">
	<div class="gnn-modal-content">
		<div class="gnn-modal-header">
			<div class="flex items-center gap-3">
				<div class="gnn-modal-icon-badge">
					<span class="material-symbols-outlined" id="modal-icon">extension</span>
				</div>
				<div>
					<h3 id="modal-title" class="gnn-modal-title"></h3>
					<p id="modal-sub" class="gnn-modal-sub"></p>
				</div>
			</div>
			<button id="btn-close-modal" class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg border-none bg-transparent cursor-pointer flex items-center justify-center">
				<span class="material-symbols-outlined text-xl">close</span>
			</button>
		</div>

		<!-- Content -->
		<div class="gnn-modal-body">
			<p id="modal-desc" class="m-0 font-medium"></p>
			
			<div class="gnn-modal-changelog-box">
				<h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider m-0 mb-2"><?php esc_html_e( 'Sürüm Notları (Changelog):', 'gnn-wpdashboard' ); ?></h4>
				<div id="modal-changelog" class="text-slate-600 leading-relaxed"></div>
			</div>
		</div>

		<!-- Footer Actions -->
		<div class="gnn-modal-footer">
			<a id="modal-repo-link" href="#" target="_blank" class="text-xs text-primary font-semibold hover:underline flex items-center gap-1">
				<span class="material-symbols-outlined text-sm">open_in_new</span> GitHub Repository
			</a>
			<button id="btn-close-modal-bottom" class="gnn-modal-close-btn">
				<?php esc_html_e( 'Kapat', 'gnn-wpdashboard' ); ?>
			</button>
		</div>
	</div>
</div>
