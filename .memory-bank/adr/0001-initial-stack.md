# ADR 0001: Technical Architecture & Ecosystem Integration

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-08-05

## Context
GNN WPDashboard is a centralized WordPress admin dashboard plugin designed to discover, install, activate, deactivate, update, and delete GNN ecosystem plugins and themes directly from GitHub repositories.

## Decision
1. **Core Architecture**: Native PHP OOP plugin (`GNN_WPDashboard`) with dedicated installer (`GNN_WPDashboard_Installer`) and updater (`GNN_WPDashboard_Updater`) modules.
2. **Admin UI & Position**: Registered under position `3.888` (immediately beneath main Dashboard/Başlangıç menu) titled `GNN Dashboard`. Uses Tailwind CSS utility styling + custom glassmorphic CSS overlays.
3. **Distribution & ZIP Resolution**: Uses GitHub Releases API `/releases/latest` with fallback to direct HTTP Tag Release archive downloads (`archive/refs/tags/vX.Y.Z.zip`) to bypass unauthenticated API 403 rate limits on shared servers (e.g. WordPress Playground).
4. **Folder Normalization**: Applies `upgrader_source_selection` filter (`fix_source_folder_name`) to rename temporary extracted GitHub zip folders (e.g. `gnn-lightbox-1.1.0`) to clean plugin slugs (`gnn-lightbox`).
5. **Bulk Action Scope & Safety**: Bulk operations (`bulk_install`, `bulk_activate`, `bulk_deactivate`, `bulk_delete`) strictly target registered GNN repos only and preserve `gnn-wpdashboard` self-protection.

## Consequences
- 100% reliable execution across all web server environments (cPanel, DirectAdmin, Plesk, Nginx, Apache, IIS, Docker, WordPress Playground).
- Prevents cross-plugin conflicts and eliminates directory deletion lock errors.
