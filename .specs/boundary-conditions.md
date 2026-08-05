# Boundary Conditions & Security Contract

- **Confidence**: Verified

## Security & Escaping Rules
1. **Input Sanitization**: All AJAX input parameters (`slug`, `file`, `type`) must be sanitized using `sanitize_text_field()` or checked against `$default_repos` keys.
2. **Nonce Verification**: All AJAX calls must verify nonces using `check_ajax_referer('gnn_wpdashboard_nonce', 'nonce')`.
3. **Capability Control**: AJAX operations require `current_user_can('install_plugins')` or `current_user_can('manage_options')`.
4. **Self-Protection Guardrail**: `gnn-wpdashboard` itself must NEVER be deactivated or deleted by bulk action functions.
5. **Direct Download Asset Enforcement**: Tag Release ZIPs are preferred over raw main branch zips to guarantee stability across environments.
