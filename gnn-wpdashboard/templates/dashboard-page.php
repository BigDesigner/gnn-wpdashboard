<?php
/**
 * Dashboard Page Template — GNN WPDashboard
 *
 * @package GNN_WPDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap gnn-wpdashboard-wrap font-body text-on-surface">
	<!-- Main Plugin Dashboard Wrapper -->
	<div class="w-full max-w-[1400px] mx-auto flex flex-col gap-6 my-4">

		<!-- Top Page Header -->
		<header class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
			<div>
				<h1 class="text-2xl font-bold text-on-surface flex items-center gap-2.5 m-0 p-0 leading-none">
					<span class="material-symbols-outlined text-primary text-3xl">grid_view</span>
					GNN Dashboard
				</h1>
				<p class="text-xs text-on-surface-variant mt-1 mb-0">Eklentilerinizi tek bir panelden kurun, güncelleyin ve yönetin.</p>
			</div>

			<div class="flex items-center gap-3 w-full sm:w-auto">
				<!-- Search Input -->
				<div class="search-box-container flex-grow sm:flex-grow-0 sm:min-w-[280px]">
					<span class="material-symbols-outlined search-icon text-lg">search</span>
					<input class="bg-surface-container-low border border-outline-variant rounded-xl text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 w-full" id="search-input" placeholder="<?php esc_attr_e( 'Eklentilerde ara...', 'gnn-wpdashboard' ); ?>" type="text"/>
				</div>

				<!-- Sync / Refresh Button -->
				<button id="btn-sync-plugins" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-sm shrink-0 active:scale-95 border-none cursor-pointer">
					<span class="material-symbols-outlined text-lg" id="sync-icon">refresh</span>
					<span><?php esc_html_e( 'Güncellemeleri Kontrol Et', 'gnn-wpdashboard' ); ?></span>
				</button>
			</div>
		</header>

		<!-- Notification Banner (If Updates Exist) -->
		<div id="update-notification-banner" class="hidden bg-amber-50 border border-amber-200 text-amber-900 rounded-xl p-4 flex items-center justify-between shadow-sm">
			<div class="flex items-center gap-3">
				<div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
					<span class="material-symbols-outlined text-xl">system_update</span>
				</div>
				<div>
					<h4 id="update-notification-title" class="font-bold text-sm m-0"><?php esc_html_e( 'Güncelleme Mevcut', 'gnn-wpdashboard' ); ?></h4>
					<p id="update-notification-text" class="text-xs text-amber-800 m-0"></p>
				</div>
			</div>
		</div>

		<!-- Main Content Layout -->
		<div class="gnn-wpdashboard-main-layout flex flex-col lg:flex-row gap-6">

			<!-- Sidebar Filters -->
			<aside class="gnn-wpdashboard-sidebar w-full lg:w-64 shrink-0 flex flex-col gap-4">
				
				<!-- Status Filters -->
				<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 shadow-sm flex flex-col gap-2">
					<h2 class="text-xs font-bold tracking-wider text-outline mb-1"><?php esc_html_e( 'DURUM FİLTRESİ', 'gnn-wpdashboard' ); ?></h2>
					
					<button data-status="all" class="status-tab active-tab flex items-center justify-between bg-secondary-container text-on-secondary-container rounded-lg px-3 py-2 text-sm font-semibold transition-all border-none cursor-pointer">
						<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">apps</span> <?php esc_html_e( 'Tüm Eklentiler', 'gnn-wpdashboard' ); ?></span>
						<span id="count-all" class="bg-surface-container-lowest text-on-surface text-xs px-2 py-0.5 rounded-full font-bold">0</span>
					</button>

					<button data-status="active" class="status-tab flex items-center justify-between text-on-surface-variant hover:bg-surface-container-high rounded-lg px-3 py-2 text-sm font-medium transition-all border-none cursor-pointer">
						<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg text-emerald-600">check_circle</span> <?php esc_html_e( 'Kurulu & Aktif', 'gnn-wpdashboard' ); ?></span>
						<span id="count-active" class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-bold">0</span>
					</button>

					<button data-status="updates" class="status-tab flex items-center justify-between text-on-surface-variant hover:bg-surface-container-high rounded-lg px-3 py-2 text-sm font-medium transition-all border-none cursor-pointer">
						<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg text-amber-600">upgrade</span> <?php esc_html_e( 'Güncelleme Var', 'gnn-wpdashboard' ); ?></span>
						<span id="count-updates" class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-bold">0</span>
					</button>

					<button data-status="inactive" class="status-tab flex items-center justify-between text-on-surface-variant hover:bg-surface-container-high rounded-lg px-3 py-2 text-sm font-medium transition-all border-none cursor-pointer">
						<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg text-gray-500">pause_circle</span> <?php esc_html_e( 'Pasif', 'gnn-wpdashboard' ); ?></span>
						<span id="count-inactive" class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full font-bold">0</span>
					</button>
				</div>

				<!-- Categories Filter -->
				<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 shadow-sm flex flex-col gap-2">
					<h2 class="text-xs font-bold tracking-wider text-outline mb-1"><?php esc_html_e( 'KATEGORİLER', 'gnn-wpdashboard' ); ?></h2>
					
					<button data-category="all" class="category-tab flex items-center gap-2 text-primary font-semibold text-sm bg-surface-container-low px-3 py-1.5 rounded-lg border-none cursor-pointer text-left">
						<span class="material-symbols-outlined text-base">extension</span> <?php esc_html_e( 'Tümü', 'gnn-wpdashboard' ); ?>
					</button>
					<button data-category="SEO & Pazarlama" class="category-tab flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg text-sm transition-all border-none cursor-pointer text-left">
						<span class="material-symbols-outlined text-base">search_check</span> SEO & Pazarlama
					</button>
					<button data-category="Güvenlik & Analiz" class="category-tab flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg text-sm transition-all border-none cursor-pointer text-left">
						<span class="material-symbols-outlined text-base">shield</span> Güvenlik & Analiz
					</button>
					<button data-category="Medya & Dosya" class="category-tab flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg text-sm transition-all border-none cursor-pointer text-left">
						<span class="material-symbols-outlined text-base">folder_zip</span> Medya & Dosya
					</button>
					<button data-category="Araçlar & Network" class="category-tab flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg text-sm transition-all border-none cursor-pointer text-left">
						<span class="material-symbols-outlined text-base">travel_explore</span> Araçlar & Network
					</button>
					<button data-category="Temalar" class="category-tab flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg text-sm transition-all border-none cursor-pointer text-left">
						<span class="material-symbols-outlined text-base">palette</span> Tüm Temalar
					</button>
				</div>

			</aside>

			<!-- Plugin Grid Section -->
			<section class="gnn-wpdashboard-content flex-grow flex flex-col gap-4">

				<!-- Counter & Bulk Actions Bar -->
				<div class="flex justify-between items-center bg-surface-container-lowest px-4 py-3 rounded-xl border border-outline-variant text-sm flex-wrap gap-3">
					<span class="text-on-surface-variant font-medium"><?php esc_html_e( 'Toplam', 'gnn-wpdashboard' ); ?> <strong id="plugin-total-count">0</strong> <?php esc_html_e( 'eklenti listeleniyor', 'gnn-wpdashboard' ); ?></span>
					<div class="flex items-center gap-2 flex-wrap">
						<button id="btn-bulk-install" class="hidden bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 border-none cursor-pointer shadow-sm">
							<span class="material-symbols-outlined text-sm">download_for_offline</span> <?php esc_html_e( 'Tümünü Kur', 'gnn-wpdashboard' ); ?>
						</button>
						<button id="btn-bulk-activate" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 border-none cursor-pointer shadow-sm">
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

				<!-- Grid Container for Dynamic Cards -->
				<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="plugin-grid-container">
					<!-- Loading Skeleton -->
					<div class="col-span-full py-12 text-center text-on-surface-variant flex flex-col items-center gap-3">
						<span class="material-symbols-outlined text-4xl animate-spin text-primary">progress_activity</span>
						<p class="text-sm font-medium"><?php esc_html_e( 'Eklenti listesi yükleniyor...', 'gnn-wpdashboard' ); ?></p>
					</div>
				</div>
			</section>

		</div>
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
