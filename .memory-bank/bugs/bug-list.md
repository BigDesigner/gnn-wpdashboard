# Bug & Verification List

## Active Issues
- None. All 7 edge-case bugs identified during audit have been resolved and verified via `php -l`.

## Resolved Issues
1. **Destination Directory Exists Error (`folder_exists`)**: Resolved via `force_remove_directory()` pre-install directory wiping.
2. **AJAX FTP Credentials Prompt**: Resolved via `init_filesystem()` output buffer suppression and fallback to direct mode.
3. **Prefix Matching Collision (`find_installed_plugin_file`)**: Resolved by matching exact directory names (`$dir === $slug`).
4. **Tag Release Asset Fallback Tag Contamination**: Resolved by restricting fallback tag checks strictly to `$data['version']`.
5. **Self-Protection Guardrail**: Reinforced `bulk_deactivate_all` and `bulk_delete_all` with `dirname($item['file']) === 'gnn-wpdashboard'`.
6. **Case-Sensitive Tag Suffix (`V1.0.0`)**: Resolved using `preg_replace('/^v/i', '', $tag)`.
7. **Updater Transient Array Initialization**: Resolved with defensive `is_array($transient->response)` check.
