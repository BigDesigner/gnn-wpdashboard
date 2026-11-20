# ADR 0002: Modern SaaS UI, Vanilla JS, WP 7.1 Standards & Security Hardening

- **Status**: Accepted
- **Supersedes**: [ADR 0001](0001-initial-stack.md)
- **Confidence**: Verified
- **Date**: 2026-11-20

## Context
Since `v1.0.0`, GNN WPDashboard evolved through multiple security, compatibility, and UI/UX iterations culminating in `v1.2.4`:
1. WordPress 6.7–7.1 introduced strict JIT translation loading (`init` hook requirement) and raised the minimum PHP floor to PHP 7.4 (WP 7.0+).
2. The admin interface was redesigned from a sidebar + geometric banner layout into a modern SaaS 3-column layout with horizontal category navigation, synchronized status counters, persistent favorites, and WCAG 2.1 accessibility.
3. Security and updater audits required eliminating jQuery, enforcing granular capabilities, validating package download hosts (`github.com` / `.githubusercontent.com`), and supporting core WP update hooks (`pre_install` / `post_install`).

## Decision
1. **WordPress & PHP Baseline**: Declare `Requires at least: 6.7`, `Tested up to: 7.1`, and `Requires PHP: 7.4` in plugin headers and `GNN_WPDashboard_Updater::plugin_info()`. Load translations on `init` (priority `1`) with compiled `.mo` binary (`0x950412de`).
2. **Admin Menu Position**: Move from `3.888` to GNN ecosystem standard **`79.001`** (`wpdashboard-settings`).
3. **Zero-jQuery Vanilla JS & Accessibility**: Rewrite `assets/js/admin.js` in dependency-free ES6+ (`fetch()` + `URLSearchParams`), add `sanitizeSafeUrl()`, `.avif`/`.webp` media URL validation, non-blocking toast notifications, `100dvh`/`inset: 0` modal viewport, `:focus-visible`, `ESC` close, and `Tab`/`Shift+Tab` focus trapping.
4. **Granular Authorization & Package Validation**: Enforce `manage_options` alongside specific capabilities (`install_plugins`, `activate_plugins`, `delete_plugins`, `switch_themes`, `delete_themes`) on AJAX endpoints. Validate all release ZIP URLs via `sanitize_package_url()` (`esc_url_raw` HTTPS + `github.com` / `.githubusercontent.com` host suffix check) and cache failures negatively for 5 minutes.
5. **Updater Lifecycle**: Preserve pre-update active state via `upgrader_pre_install` (`pre_install()`) and normalize folder names + refresh plugin cache in `upgrader_post_install` (`post_install()`).
6. **Catalog & CI/CD**: Track **11** GNN repositories (including `gnn-logos` added in `v1.2.3`) and run automated releases on `actions/checkout@v7`, `actions/upload-artifact@v7`, and `softprops/action-gh-release@v3`.

## Consequences
- Full compatibility with WordPress 6.7–7.1 and PHP 7.4–8.4+ without `_load_textdomain_just_in_time` notices.
- Reduced frontend attack surface and zero jQuery runtime dependency.
- Reliable self-updates from both the GNN Dashboard UI and standard WordPress Plugins/Updates screens.
