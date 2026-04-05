<?php
/**
 * Nut Journey API
 *
 * REST API endpoints for the Peanut-to-Cashew complete journey process.
 * Namespace: nut-journey/v1
 *
 * Routes:
 *   POST /wp-json/nut-journey/v1/phase1-import
 *   POST /wp-json/nut-journey/v1/phase2-hardcoded
 *   POST /wp-json/nut-journey/v1/phase3-sanitized
 *   POST /wp-json/nut-journey/v1/phase4-deploy
 *   POST /wp-json/nut-journey/v1/run-full-journey
 */

class Ruplin_Nut_Journey_API {

    const NAMESPACE = 'nut-journey/v1';
    const PEANUT_SOURCE_BASE = '/Users/kylecampbell/Documents/repos/peanut_page_outputs/';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    // =========================================================
    // Route Registration
    // =========================================================

    public function register_routes() {

        // Phase 1 — copy peanut folder into hazelnut-holdings and create DB row
        register_rest_route( self::NAMESPACE, '/phase1-import', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'phase1_import' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'folder_name' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Peanut folder name, e.g. "115 - chimney-exp_com"',
                ),
            ),
        ) );

        // Phase 2 — generate html_file_w_hardcoded_references for a hazelnut item
        register_rest_route( self::NAMESPACE, '/phase2-hardcoded', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'phase2_hardcoded' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'item_id' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'description'       => 'wp_hazelnut_items.item_id',
                ),
            ),
        ) );

        // Phase 3 — generate file3 sanitized HTML + dependency header code
        register_rest_route( self::NAMESPACE, '/phase3-sanitized', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'phase3_sanitized' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'item_id' => array(
                    'required'    => true,
                    'type'        => 'integer',
                    'description' => 'wp_hazelnut_items.item_id',
                ),
            ),
        ) );

        // Phase 4 — deploy sanitized HTML + deps to wp_pylons and wp_zen_orbitposts
        register_rest_route( self::NAMESPACE, '/phase4-deploy', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'phase4_deploy' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'post_id' => array(
                    'required'    => true,
                    'type'        => 'integer',
                    'description' => 'Target WordPress post ID',
                ),
                'sanitized_html' => array(
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'Sanitized HTML body content for cashew_html_expanse',
                ),
                'deps_html' => array(
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'CSS/JS dependency tags for ferret_header_code',
                ),
            ),
        ) );

        // Full journey — runs all four phases in one call
        register_rest_route( self::NAMESPACE, '/run-full-journey', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'run_full_journey' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'folder_name' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Peanut folder name, e.g. "115 - chimney-exp_com"',
                ),
                'post_id' => array(
                    'required'    => true,
                    'type'        => 'integer',
                    'description' => 'Target WordPress post ID',
                ),
            ),
        ) );
    }

    // =========================================================
    // Permission
    // =========================================================

    public function check_permission() {
        return current_user_can( 'manage_options' );
    }

    // =========================================================
    // Phase 1 — Import peanut folder into hazelnut-holdings
    // =========================================================

    public function phase1_import( WP_REST_Request $request ) {
        $folder_name = $request->get_param( 'folder_name' );

        $source_path = self::PEANUT_SOURCE_BASE . $folder_name;
        $dest_path   = WP_CONTENT_DIR . '/hazelnut-holdings/' . $folder_name . '/';

        if ( ! is_dir( $source_path ) ) {
            return new WP_Error( 'source_not_found', 'Peanut source folder not found: ' . $source_path, array( 'status' => 404 ) );
        }

        // Ensure hazelnut-holdings directory exists
        if ( ! file_exists( WP_CONTENT_DIR . '/hazelnut-holdings/' ) ) {
            wp_mkdir_p( WP_CONTENT_DIR . '/hazelnut-holdings/' );
        }

        // Copy folder
        $this->copy_directory( $source_path, $dest_path );

        // Log to DB and return new item_id
        $item_id = $this->log_hazelnut_upload(
            $folder_name,
            null,
            '/wp-content/hazelnut-holdings/' . $folder_name . '/',
            'completed',
            $source_path
        );

        if ( ! $item_id ) {
            return new WP_Error( 'db_insert_failed', 'Folder copied but DB insert failed.', array( 'status' => 500 ) );
        }

        return rest_ensure_response( array(
            'success'     => true,
            'message'     => 'Phase 1 complete: folder imported.',
            'item_id'     => $item_id,
            'folder_name' => $folder_name,
            'dest_path'   => $dest_path,
        ) );
    }

    // =========================================================
    // Phase 2 — Generate hardcoded-references HTML file
    // =========================================================

    public function phase2_hardcoded( WP_REST_Request $request ) {
        global $wpdb;

        $item_id    = intval( $request->get_param( 'item_id' ) );
        $table_name = $wpdb->prefix . 'hazelnut_items';

        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE item_id = %d", $item_id ) );

        if ( ! $item || ! $item->main_html_file ) {
            return new WP_Error( 'item_not_found', 'Hazelnut item not found or has no main HTML file.', array( 'status' => 404 ) );
        }

        $base_path          = WP_CONTENT_DIR . '/hazelnut-holdings/' . $item->folder_name . '/';
        $original_file_path = $base_path . $item->main_html_file;

        if ( ! file_exists( $original_file_path ) ) {
            return new WP_Error( 'html_file_missing', 'Original HTML file not found on disk.', array( 'status' => 404 ) );
        }

        $html_content = file_get_contents( $original_file_path );
        if ( $html_content === false ) {
            return new WP_Error( 'read_failed', 'Could not read original HTML file.', array( 'status' => 500 ) );
        }

        $transformed_html = $this->transform_references_to_absolute( $html_content, $item->upload_path, $item->main_html_file );

        $path_info    = pathinfo( $item->main_html_file );
        $new_filename = $path_info['dirname'] . '/' . $path_info['filename'] . '_hardcoded_refs.html';
        $new_filename = ltrim( str_replace( '//', '/', $new_filename ), '/' );
        $new_file_path = $base_path . $new_filename;

        $new_file_dir = dirname( $new_file_path );
        if ( ! is_dir( $new_file_dir ) ) {
            wp_mkdir_p( $new_file_dir );
        }

        if ( file_put_contents( $new_file_path, $transformed_html ) === false ) {
            return new WP_Error( 'write_failed', 'Could not write hardcoded-references HTML file.', array( 'status' => 500 ) );
        }

        $wpdb->update(
            $table_name,
            array(
                'html_file_w_hardcoded_references' => $new_filename,
                'updated_at'                       => current_time( 'mysql' ),
            ),
            array( 'item_id' => $item_id )
        );

        return rest_ensure_response( array(
            'success'      => true,
            'message'      => 'Phase 2 complete: hardcoded-references file generated.',
            'item_id'      => $item_id,
            'new_filename' => $new_filename,
        ) );
    }

    // =========================================================
    // Phase 3 — Generate sanitized file3 + dependency header
    // =========================================================

    public function phase3_sanitized( WP_REST_Request $request ) {
        global $wpdb;

        $item_id    = intval( $request->get_param( 'item_id' ) );
        $table_name = $wpdb->prefix . 'hazelnut_items';

        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE item_id = %d", $item_id ) );

        if ( ! $item || ! $item->html_file_w_hardcoded_references ) {
            return new WP_Error( 'item_not_ready', 'Hazelnut item not found or phase 2 not yet run.', array( 'status' => 404 ) );
        }

        $base_path          = WP_CONTENT_DIR . '/hazelnut-holdings/' . $item->folder_name . '/';
        $hardcoded_file_path = $base_path . $item->html_file_w_hardcoded_references;

        if ( ! file_exists( $hardcoded_file_path ) ) {
            return new WP_Error( 'hardcoded_file_missing', 'Hardcoded-references HTML file not found on disk.', array( 'status' => 404 ) );
        }

        $html_content = file_get_contents( $hardcoded_file_path );
        if ( $html_content === false ) {
            return new WP_Error( 'read_failed', 'Could not read hardcoded-references file.', array( 'status' => 500 ) );
        }

        $extraction_result = $this->extract_dependencies_and_sanitize( $html_content );

        $path_info    = pathinfo( $item->html_file_w_hardcoded_references );
        $base_name    = str_replace( '_hardcoded_refs', '', $path_info['filename'] );
        $new_filename = $path_info['dirname'] . '/' . $base_name . '_sanitized.html';
        $new_filename = ltrim( str_replace( '//', '/', $new_filename ), '/' );
        $new_file_path = $base_path . $new_filename;

        $new_file_dir = dirname( $new_file_path );
        if ( ! is_dir( $new_file_dir ) ) {
            wp_mkdir_p( $new_file_dir );
        }

        if ( file_put_contents( $new_file_path, $extraction_result['sanitized_html'] ) === false ) {
            return new WP_Error( 'write_failed', 'Could not write sanitized HTML file.', array( 'status' => 500 ) );
        }

        $wpdb->update(
            $table_name,
            array(
                'file3_w_hardcoded_refs_sanitized'    => $new_filename,
                'file3_dependencies_html'             => $extraction_result['dependencies_html'],
                'file3_extracted_dependencies_json'   => json_encode( $extraction_result['dependencies_json'] ),
                'file3_extracted_sanitization_metadata' => json_encode( $extraction_result['metadata'] ),
                'updated_at'                          => current_time( 'mysql' ),
            ),
            array( 'item_id' => $item_id )
        );

        return rest_ensure_response( array(
            'success'        => true,
            'message'        => 'Phase 3 complete: sanitized file and dependencies generated.',
            'item_id'        => $item_id,
            'new_filename'   => $new_filename,
            'sanitized_html' => $extraction_result['sanitized_html'],
            'deps_html'      => $extraction_result['dependencies_html'],
        ) );
    }

    // =========================================================
    // Phase 4 — Deploy to wp_pylons + wp_zen_orbitposts
    // =========================================================

    public function phase4_deploy( WP_REST_Request $request ) {
        global $wpdb;

        $post_id        = intval( $request->get_param( 'post_id' ) );
        $sanitized_html = $request->get_param( 'sanitized_html' );
        $deps_html      = $request->get_param( 'deps_html' );

        $pylons_table    = $wpdb->prefix . 'pylons';
        $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';

        // ── Update or insert wp_pylons ────────────────────────
        $pylon_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT pylon_id FROM {$pylons_table} WHERE rel_wp_post_id = %d",
            $post_id
        ) );

        $pylon_data = array(
            'cashew_html_expanse'            => wp_unslash( $sanitized_html ),
            'staircase_page_template_desired' => 'vibrantcashew',
            'updated_at'                     => current_time( 'mysql' ),
        );

        if ( $pylon_exists ) {
            $wpdb->update( $pylons_table, $pylon_data, array( 'rel_wp_post_id' => $post_id ) );
        } else {
            $pylon_data['rel_wp_post_id'] = $post_id;
            $wpdb->insert( $pylons_table, $pylon_data );
        }

        // ── Ensure wp_zen_orbitposts table exists ─────────────
        $orbit_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$orbitposts_table'" ) === $orbitposts_table;

        if ( ! $orbit_table_exists ) {
            $wpdb->query( "CREATE TABLE IF NOT EXISTS $orbitposts_table (
                rel_wp_post_id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
                ferret_header_code LONGTEXT DEFAULT NULL,
                ferret_header_code_2 LONGTEXT DEFAULT NULL,
                ferret_footer_code LONGTEXT DEFAULT NULL
            ) " . $wpdb->get_charset_collate() );
        }

        // ── Update or insert wp_zen_orbitposts ────────────────
        $orbit_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT rel_wp_post_id FROM {$orbitposts_table} WHERE rel_wp_post_id = %d",
            $post_id
        ) );

        $orbit_data = array( 'ferret_header_code' => wp_unslash( $deps_html ) );

        if ( $orbit_exists ) {
            $wpdb->update( $orbitposts_table, $orbit_data, array( 'rel_wp_post_id' => $post_id ) );
        } else {
            $orbit_data['rel_wp_post_id'] = $post_id;
            $wpdb->insert( $orbitposts_table, $orbit_data );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'Phase 4 complete: content deployed to WordPress post.',
            'post_id' => $post_id,
            'staircase_page_template_desired' => 'vibrantcashew',
        ) );
    }

    // =========================================================
    // Full Journey — runs all four phases in sequence
    // =========================================================

    public function run_full_journey( WP_REST_Request $request ) {
        $folder_name = $request->get_param( 'folder_name' );
        $post_id     = intval( $request->get_param( 'post_id' ) );
        $log         = array();

        // ── Phase 1 ───────────────────────────────────────────
        $p1_request = new WP_REST_Request( 'POST' );
        $p1_request->set_param( 'folder_name', $folder_name );
        $p1_response = $this->phase1_import( $p1_request );

        if ( is_wp_error( $p1_response ) ) {
            return new WP_Error( 'phase1_failed', 'Phase 1 failed: ' . $p1_response->get_error_message(), array( 'status' => 500 ) );
        }
        $p1_data = $p1_response->get_data();
        $item_id = $p1_data['item_id'];
        $log[]   = 'Phase 1 complete — item_id: ' . $item_id;

        // ── Phase 2 ───────────────────────────────────────────
        $p2_request = new WP_REST_Request( 'POST' );
        $p2_request->set_param( 'item_id', $item_id );
        $p2_response = $this->phase2_hardcoded( $p2_request );

        if ( is_wp_error( $p2_response ) ) {
            return new WP_Error( 'phase2_failed', 'Phase 2 failed: ' . $p2_response->get_error_message(), array( 'status' => 500 ) );
        }
        $log[] = 'Phase 2 complete — hardcoded file generated.';

        // ── Phase 3 ───────────────────────────────────────────
        $p3_request = new WP_REST_Request( 'POST' );
        $p3_request->set_param( 'item_id', $item_id );
        $p3_response = $this->phase3_sanitized( $p3_request );

        if ( is_wp_error( $p3_response ) ) {
            return new WP_Error( 'phase3_failed', 'Phase 3 failed: ' . $p3_response->get_error_message(), array( 'status' => 500 ) );
        }
        $p3_data        = $p3_response->get_data();
        $sanitized_html = $p3_data['sanitized_html'];
        $deps_html      = $p3_data['deps_html'];
        $log[]          = 'Phase 3 complete — sanitized HTML and deps extracted.';

        // ── Phase 4 ───────────────────────────────────────────
        $p4_request = new WP_REST_Request( 'POST' );
        $p4_request->set_param( 'post_id', $post_id );
        $p4_request->set_param( 'sanitized_html', $sanitized_html );
        $p4_request->set_param( 'deps_html', $deps_html );
        $p4_response = $this->phase4_deploy( $p4_request );

        if ( is_wp_error( $p4_response ) ) {
            return new WP_Error( 'phase4_failed', 'Phase 4 failed: ' . $p4_response->get_error_message(), array( 'status' => 500 ) );
        }
        $log[] = 'Phase 4 complete — deployed to post ' . $post_id . '.';

        return rest_ensure_response( array(
            'success'     => true,
            'message'     => 'Full journey complete.',
            'folder_name' => $folder_name,
            'post_id'     => $post_id,
            'item_id'     => $item_id,
            'log'         => $log,
        ) );
    }

    // =========================================================
    // Private helpers (ported from hazelnut-items-admin.php)
    // =========================================================

    /**
     * Recursively copy a directory.
     */
    private function copy_directory( $source, $dest ) {
        if ( ! is_dir( $dest ) ) {
            mkdir( $dest, 0755, true );
        }
        $dir = opendir( $source );
        while ( ( $file = readdir( $dir ) ) !== false ) {
            if ( $file === '.' || $file === '..' ) {
                continue;
            }
            $src_path = $source . '/' . $file;
            $dst_path = $dest . '/' . $file;
            if ( is_dir( $src_path ) ) {
                $this->copy_directory( $src_path, $dst_path );
            } else {
                copy( $src_path, $dst_path );
            }
        }
        closedir( $dir );
    }

    /**
     * Find the main HTML file up to 2 levels deep inside a folder.
     */
    private function find_main_html_file( $base_path ) {
        $root_html = glob( $base_path . '*.html' );
        if ( ! empty( $root_html ) ) {
            return basename( $root_html[0] );
        }
        $subdirs = glob( $base_path . '*', GLOB_ONLYDIR );
        foreach ( $subdirs as $subdir ) {
            $subdir_html = glob( $subdir . '/*.html' );
            if ( ! empty( $subdir_html ) ) {
                return basename( $subdir ) . '/' . basename( $subdir_html[0] );
            }
            $second_dirs = glob( $subdir . '/*', GLOB_ONLYDIR );
            foreach ( $second_dirs as $second_dir ) {
                $second_html = glob( $second_dir . '/*.html' );
                if ( ! empty( $second_html ) ) {
                    return basename( $subdir ) . '/' . basename( $second_dir ) . '/' . basename( $second_html[0] );
                }
            }
        }
        return null;
    }

    /**
     * Insert a row into wp_hazelnut_items and return the new item_id.
     */
    private function log_hazelnut_upload( $folder_name, $zip_filename, $upload_path, $status, $source_path = null ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        $full_path  = WP_CONTENT_DIR . '/hazelnut-holdings/' . $folder_name . '/';

        $file_count = 0;
        $total_size = 0;
        if ( is_dir( $full_path ) ) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator( $full_path, RecursiveDirectoryIterator::SKIP_DOTS ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ( $iterator as $file ) {
                if ( $file->isFile() ) {
                    $file_count++;
                    $total_size += $file->getSize();
                }
            }
        }

        $main_html = $this->find_main_html_file( $full_path );

        $wpdb->insert( $table_name, array(
            'upload_date'          => current_time( 'mysql' ),
            'folder_name'          => $folder_name,
            'original_zip_filename' => $zip_filename,
            'upload_path'          => $upload_path,
            'main_html_file'       => $main_html,
            'total_files_count'    => $file_count,
            'total_size_bytes'     => $total_size,
            'upload_status'        => $status,
            'source_local_path'    => $source_path,
            'is_active'            => 1,
            'created_at'           => current_time( 'mysql' ),
            'updated_at'           => current_time( 'mysql' ),
        ) );

        return $wpdb->insert_id ?: null;
    }

    /**
     * Rewrite relative asset references in HTML to absolute hazelnut-holdings URLs.
     */
    private function transform_references_to_absolute( $html, $upload_path, $main_html_file ) {
        $html_dir = dirname( $main_html_file );
        $html_dir = ( $html_dir === '.' ) ? '' : $html_dir . '/';
        $base_url = rtrim( $upload_path, '/' );

        $patterns = array(
            '/src=["\'](?!http|https|\/\/|data:|#)([^"\']*)["\']/',
            '/href=["\'](?!http|https|\/\/|data:|#|mailto:|tel:)([^"\']*)["\']/',
            '/url\(["\']?(?!http|https|\/\/|data:)([^"\']*)["\']?\)/',
            '/srcset=["\']([^"\']*)["\']/',
        );

        foreach ( $patterns as $index => $pattern ) {
            if ( $index < 3 ) {
                $html = preg_replace_callback( $pattern, function ( $matches ) use ( $base_url, $html_dir ) {
                    $original = $matches[0];
                    $path     = $matches[1];
                    if ( empty( $path ) || strpos( $path, '#' ) === 0 ) {
                        return $original;
                    }
                    if ( strpos( $path, '../' ) === 0 ) {
                        $abs = $base_url . '/' . substr( $path, 3 );
                    } elseif ( strpos( $path, '/' ) === 0 ) {
                        $abs = $path;
                    } else {
                        $abs = $base_url . '/' . $html_dir . $path;
                    }
                    $abs = preg_replace( '#/+#', '/', $abs );
                    if ( strpos( $original, 'src=' ) === 0 )  return 'src="' . $abs . '"';
                    if ( strpos( $original, 'href=' ) === 0 ) return 'href="' . $abs . '"';
                    if ( strpos( $original, 'url(' ) === 0 )  return 'url(' . $abs . ')';
                    return $original;
                }, $html );
            } else {
                $html = preg_replace_callback( $pattern, function ( $matches ) use ( $base_url, $html_dir ) {
                    $sources     = explode( ',', $matches[1] );
                    $new_sources = array();
                    foreach ( $sources as $source ) {
                        $source = trim( $source );
                        if ( preg_match( '/^([^\s]+)(\s+.*)?$/', $source, $m ) ) {
                            $path       = $m[1];
                            $descriptor = isset( $m[2] ) ? $m[2] : '';
                            if ( preg_match( '#^(https?://|//)#', $path ) ) {
                                $new_sources[] = $source;
                                continue;
                            }
                            if ( strpos( $path, '../' ) === 0 ) {
                                $abs = $base_url . '/' . substr( $path, 3 );
                            } elseif ( strpos( $path, '/' ) === 0 ) {
                                $abs = $path;
                            } else {
                                $abs = $base_url . '/' . $html_dir . $path;
                            }
                            $abs           = preg_replace( '#/+#', '/', $abs );
                            $new_sources[] = $abs . $descriptor;
                        } else {
                            $new_sources[] = $source;
                        }
                    }
                    return 'srcset="' . implode( ', ', $new_sources ) . '"';
                }, $html );
            }
        }

        return $html;
    }

    /**
     * Extract CSS/JS dependencies from HTML and return sanitized body + deps header string.
     */
    private function extract_dependencies_and_sanitize( $html ) {
        $dependencies = array(
            'stylesheets'    => array(),
            'scripts'        => array(),
            'inline_styles'  => array(),
            'inline_scripts' => array(),
        );
        $removed_elements = array();

        if ( preg_match_all( '/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $m ) ) {
            foreach ( $m[0] as $tag ) {
                if ( preg_match( '/href=["\']([^"\']+)["\']/', $tag, $h ) ) {
                    $dependencies['stylesheets'][] = $h[1];
                }
            }
        }
        if ( preg_match_all( '/<script[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i', $html, $m ) ) {
            foreach ( $m[1] as $src ) {
                $dependencies['scripts'][] = $src;
            }
        }
        if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/si', $html, $m ) ) {
            foreach ( $m[1] as $style ) {
                $trimmed = trim( $style );
                if ( ! empty( $trimmed ) ) $dependencies['inline_styles'][] = $trimmed;
            }
        }
        if ( preg_match_all( '/<script(?![^>]*src=)[^>]*>(.*?)<\/script>/si', $html, $m ) ) {
            foreach ( $m[1] as $script ) {
                $trimmed = trim( $script );
                if ( ! empty( $trimmed ) ) $dependencies['inline_scripts'][] = $trimmed;
            }
        }

        // Sanitize
        $sanitized = $html;
        $sanitized = preg_replace( '/<!DOCTYPE[^>]*>/i', '', $sanitized );
        $removed_elements[] = 'DOCTYPE';
        $sanitized = preg_replace( '/<!--.*?-->/s', '', $sanitized );
        if ( preg_match( '/<body[^>]*>(.*?)<\/body>/si', $sanitized, $m ) ) {
            $sanitized = $m[1];
            $removed_elements[] = 'body wrapper';
        }
        $sanitized = preg_replace( '/<script\b[^>]*>.*?<\/script>/si', '', $sanitized );
        $removed_elements[] = 'script tags';
        $sanitized = preg_replace( '/<style\b[^>]*>.*?<\/style>/si', '', $sanitized );
        $removed_elements[] = 'style tags';
        $sanitized = preg_replace( '/<link\b[^>]*>/i', '', $sanitized );
        $removed_elements[] = 'link tags';
        $sanitized = preg_replace( '/<meta\b[^>]*>/i', '', $sanitized );
        $removed_elements[] = 'meta tags';
        $sanitized = preg_replace( '/<title\b[^>]*>.*?<\/title>/si', '', $sanitized );
        $removed_elements[] = 'title tag';
        $sanitized = preg_replace( '/<\/?html[^>]*>/i', '', $sanitized );
        $removed_elements[] = 'html tag';
        $sanitized = preg_replace( '/<head\b[^>]*>.*?<\/head>/si', '', $sanitized );
        $removed_elements[] = 'head section';
        $sanitized = preg_replace( '/<header\b[^>]*>.*?<\/header>/si', '', $sanitized );
        $sanitized = preg_replace( '/<footer\b[^>]*>.*?<\/footer>/si', '', $sanitized );
        $sanitized = preg_replace( '/<nav\b[^>]*>.*?<\/nav>/si', '', $sanitized );
        $sanitized = preg_replace( '/<aside\b[^>]*>.*?<\/aside>/si', '', $sanitized );
        $removed_elements = array_merge( $removed_elements, array( 'header', 'footer', 'nav', 'aside' ) );
        if ( preg_match( '/<(main|article|section|div)\s+(?:id|class)=["\'](?:main|content|main-content|page-content|site-content|primary)["\'][^>]*>(.*?)<\/\1>/si', $sanitized, $m ) ) {
            $sanitized = $m[2];
        }
        $sanitized = preg_replace( '/\s+/', ' ', $sanitized );
        $sanitized = preg_replace( '/>\s+</', '><', $sanitized );
        $sanitized = trim( $sanitized );

        // Build deps HTML string
        $deps_html = '';
        if ( ! empty( $dependencies['stylesheets'] ) ) {
            $deps_html .= "<!-- CSS Dependencies -->\n";
            foreach ( $dependencies['stylesheets'] as $sheet ) {
                $deps_html .= '<link rel="stylesheet" href="' . esc_attr( $sheet ) . '">' . "\n";
            }
        }
        if ( ! empty( $dependencies['inline_styles'] ) ) {
            $deps_html .= "\n<!-- Inline Styles -->\n";
            foreach ( $dependencies['inline_styles'] as $style ) {
                $deps_html .= "<style>\n" . $style . "\n</style>\n";
            }
        }
        if ( ! empty( $dependencies['scripts'] ) ) {
            $deps_html .= "\n<!-- JS Dependencies -->\n";
            foreach ( $dependencies['scripts'] as $script ) {
                $deps_html .= '<script src="' . esc_attr( $script ) . '"></script>' . "\n";
            }
        }
        if ( ! empty( $dependencies['inline_scripts'] ) ) {
            $deps_html .= "\n<!-- Inline Scripts -->\n";
            foreach ( $dependencies['inline_scripts'] as $script ) {
                $deps_html .= "<script>\n" . $script . "\n</script>\n";
            }
        }

        $metadata = array(
            'removed_elements'  => $removed_elements,
            'sanitized_date'    => current_time( 'mysql' ),
            'dependency_counts' => array(
                'stylesheets'    => count( $dependencies['stylesheets'] ),
                'scripts'        => count( $dependencies['scripts'] ),
                'inline_styles'  => count( $dependencies['inline_styles'] ),
                'inline_scripts' => count( $dependencies['inline_scripts'] ),
            ),
        );

        return array(
            'sanitized_html'   => $sanitized,
            'dependencies_html' => $deps_html,
            'dependencies_json' => $dependencies,
            'metadata'          => $metadata,
        );
    }
}

// Instantiate
new Ruplin_Nut_Journey_API();
