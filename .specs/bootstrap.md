# Project Bootstrap & Verification Specification

- **Project Name**: GNN WPDashboard
- **Ecosystem**: WordPress Plugin
- **Primary Language**: PHP 7.4+
- **Frontend Stack**: Vanilla JavaScript (jQuery), HTML5, Tailwind CSS
- **Confidence**: Verified

## Environment & Requirements
- **PHP**: 7.4 or higher (`Verified`)
- **WordPress**: 5.8 or higher (`Verified`)
- **Web Servers Supported**: Apache, Nginx, IIS, LiteSpeed, Docker, WordPress Playground (`Verified`)

## Installation & Verification Commands
- **PHP Syntax Verification**:
  ```bash
  php -l gnn-wpdashboard/gnn-wpdashboard.php
  php -l gnn-wpdashboard/includes/class-gnn-wpdashboard-installer.php
  php -l gnn-wpdashboard/includes/class-gnn-wpdashboard-updater.php
  php -l gnn-wpdashboard/includes/class-gnn-silent-upgrader-skin.php
  php -l gnn-wpdashboard/templates/dashboard-page.php
  ```
