<?php
/**
 * Warbler Migrator - Admin Page Controller
 * Registers the WP admin page and handles export/import form submissions.
 */

if (!defined('ABSPATH')) exit;

class Warbler_Admin_Page {

    public function __construct() {
        add_action('admin_menu',           [$this, 'register_menu'], 15);
        add_action('admin_post_warbler_export', [$this, 'handle_export']);
        add_action('admin_post_warbler_import', [$this, 'handle_import']);
    }

    // -------------------------------------------------------------------------
    // Menu registration
    // -------------------------------------------------------------------------

    public function register_menu() {
        add_submenu_page(
            'ruplin_hub_3_mar',               // Parent slug — appears under Ruplin Hub 3
            'Warbler Migrator',               // Page title
            'Warbler Migrator',               // Menu label
            'manage_options',                 // Capability
            'warbler-migrator',               // Slug
            [$this, 'render_page']
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        require_once dirname(__FILE__) . '/admin-page.php';
    }

    // -------------------------------------------------------------------------
    // Export handler — streams .warbler file to browser
    // -------------------------------------------------------------------------

    public function handle_export() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized.');
        check_admin_referer('warbler_export', 'warbler_nonce');

        $options = [
            'include_themes'  => !empty($_POST['include_themes']),
            'include_plugins' => !empty($_POST['include_plugins']),
            'include_uploads' => !empty($_POST['include_uploads']),
        ];

        $zip_path = Warbler_Exporter::export($options);

        if (is_wp_error($zip_path)) {
            wp_die('Export failed: ' . esc_html($zip_path->get_error_message()));
        }

        $filename = basename($zip_path);
        $filesize = filesize($zip_path);

        // Stream to browser
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Disable output buffering to allow large file streaming
        if (ob_get_level()) ob_end_clean();

        readfile($zip_path);
        @unlink($zip_path);

        // Clean up parent temp dir
        @rmdir(dirname($zip_path));

        exit;
    }

    // -------------------------------------------------------------------------
    // Import handler
    // -------------------------------------------------------------------------

    public function handle_import() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized.');
        check_admin_referer('warbler_import', 'warbler_nonce');

        $redirect_base = admin_url('admin.php?page=warbler-migrator&warbler_tab=import');

        // Validate upload
        if (empty($_FILES['warbler_file']['tmp_name']) || $_FILES['warbler_file']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['warbler_file']['error'])
                ? $this->upload_error_message($_FILES['warbler_file']['error'])
                : 'No file uploaded.';
            wp_redirect($redirect_base . '&warbler_error=' . urlencode($error));
            exit;
        }

        $target_url = isset($_POST['warbler_target_url'])
            ? esc_url_raw(trim($_POST['warbler_target_url']))
            : get_option('siteurl');

        $options = [
            'restore_files' => !empty($_POST['restore_files']),
        ];

        $tmp_path = $_FILES['warbler_file']['tmp_name'];

        $result = Warbler_Importer::import($tmp_path, $target_url, $options);

        if (is_wp_error($result)) {
            wp_redirect($redirect_base . '&warbler_error=' . urlencode($result->get_error_message()));
            exit;
        }

        wp_redirect($redirect_base . '&warbler_imported=1');
        exit;
    }

    // -------------------------------------------------------------------------

    private function upload_error_message($code) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
        ];
        return $messages[$code] ?? 'Unknown upload error (code ' . $code . ').';
    }
}
