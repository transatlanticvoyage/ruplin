<?php
/**
 * Blovian Image Entity — Admin Page
 * 
 * UI table grid for wp_blovian_image_entities
 * Admin URL: /wp-admin/admin.php?page=blovian_image_entity_mar
 *
 * @package Ruplin
 * @subpackage BlovianImageEntity
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function render_blovian_image_entity_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'blovian_image_entities';

    // ── Handle AJAX-style inline save (POST) ──────────────────────────────
    if ( isset( $_POST['blovian_action'] ) && check_admin_referer( 'blovian_nonce', 'blovian_nonce_field' ) ) {

        $action = sanitize_text_field( $_POST['blovian_action'] );

        if ( $action === 'create' ) {
            $wpdb->insert( $table, [
                'native_wp_image_post_id' => null,
                'rel_page_post_id'        => null,
                'd_pylon_archetype'       => null,
                'd_post_title'            => null,
                'created_at'              => current_time( 'mysql' ),
                'updated_at'              => current_time( 'mysql' ),
            ] );
            $new_id = $wpdb->insert_id;
            wp_safe_redirect( add_query_arg( [ 'page' => 'blovian_image_entity_mar', 'new_id' => $new_id ], admin_url( 'admin.php' ) ) );
            exit;
        }

        if ( $action === 'save_row' && ! empty( $_POST['entity_id'] ) ) {
            $entity_id = intval( $_POST['entity_id'] );

            $native_img = ( $_POST['native_wp_image_post_id'] !== '' )
                ? intval( $_POST['native_wp_image_post_id'] ) : null;
            $rel_page   = ( $_POST['rel_page_post_id'] !== '' )
                ? intval( $_POST['rel_page_post_id'] ) : null;

            // Sync d_ fields via PHP helper (for hosts without trigger support)
            $d_pylon    = null;
            $d_title    = null;
            if ( $rel_page ) {
                $denorm     = SnefuruPlugin::get_blovian_denormalized_fields( $rel_page );
                $d_pylon    = $denorm['d_pylon_archetype'];
                $d_title    = $denorm['d_post_title'];
            }

            $wpdb->update(
                $table,
                [
                    'native_wp_image_post_id' => $native_img,
                    'rel_page_post_id'        => $rel_page,
                    'd_pylon_archetype'       => $d_pylon,
                    'd_post_title'            => $d_title,
                    'updated_at'              => current_time( 'mysql' ),
                ],
                [ 'entity_id' => $entity_id ]
            );
            wp_safe_redirect( add_query_arg( [ 'page' => 'blovian_image_entity_mar', 'saved' => $entity_id ], admin_url( 'admin.php' ) ) );
            exit;
        }

        if ( $action === 'delete_row' && ! empty( $_POST['entity_id'] ) ) {
            $wpdb->delete( $table, [ 'entity_id' => intval( $_POST['entity_id'] ) ] );
            wp_safe_redirect( add_query_arg( [ 'page' => 'blovian_image_entity_mar', 'deleted' => 1 ], admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    // ── Fetch all rows ────────────────────────────────────────────────────
    $rows = $wpdb->get_results( "SELECT * FROM $table ORDER BY entity_id ASC", ARRAY_A );
    $new_id  = isset( $_GET['new_id'] )  ? intval( $_GET['new_id'] )  : null;
    $saved   = isset( $_GET['saved'] )   ? intval( $_GET['saved'] )   : null;
    $deleted = isset( $_GET['deleted'] ) ? true : false;

    // Column definitions: name → editable by user?
    $columns = [
        'entity_id'                 => false,   // serial PK — read only
        'native_wp_image_post_id'   => true,    // user sets this
        'rel_page_post_id'          => true,    // user sets this
        'd_pylon_archetype'         => false,   // auto-synced — read only
        'd_post_title'              => false,   // auto-synced — read only
        'created_at'                => false,
        'updated_at'                => false,
    ];
    ?>
    <div class="wrap blovian-wrap">
        <h1>blovian_image_entity_mar</h1>

        <?php if ( $deleted ) : ?>
            <div class="notice notice-success is-dismissible"><p>Row deleted.</p></div>
        <?php endif; ?>
        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p>Row <?php echo $saved; ?> saved.</p></div>
        <?php endif; ?>
        <?php if ( $new_id ) : ?>
            <div class="notice notice-info is-dismissible"><p>New row created (entity_id <?php echo $new_id; ?>). Edit it inline below.</p></div>
        <?php endif; ?>

        <?php /* ── Create New button ── */ ?>
        <form method="post" style="margin-bottom:12px;">
            <?php wp_nonce_field( 'blovian_nonce', 'blovian_nonce_field' ); ?>
            <input type="hidden" name="blovian_action" value="create">
            <button type="submit" class="button button-primary">+ Create New (inline)</button>
        </form>

        <?php /* ── Table ── */ ?>
        <div style="overflow-x:auto;">
        <table class="blovian-table">
            <thead>
                <tr>
                    <?php foreach ( $columns as $col => $editable ) : ?>
                        <th><?php echo esc_html( $col ); ?></th>
                    <?php endforeach; ?>
                    <th>actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr>
                    <td colspan="<?php echo count( $columns ) + 1; ?>" style="text-align:center;color:#888;padding:20px;">
                        No rows yet. Click "Create New (inline)" to add one.
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $rows as $row ) :
                    $is_editing = ( $new_id && (int) $row['entity_id'] === $new_id )
                               || isset( $_GET['edit'] ) && (int) $_GET['edit'] === (int) $row['entity_id'];
                ?>
                <tr class="blovian-row<?php echo $is_editing ? ' blovian-row--editing' : ''; ?>"
                    data-id="<?php echo esc_attr( $row['entity_id'] ); ?>">

                    <?php foreach ( $columns as $col => $editable ) :
                        $val = $row[ $col ] ?? '';
                    ?>
                    <td data-col="<?php echo esc_attr( $col ); ?>">
                        <?php if ( $editable ) : ?>
                            <span class="blovian-display"><?php echo esc_html( $val !== '' && $val !== null ? $val : '—' ); ?></span>
                            <input class="blovian-input" type="text"
                                   name="<?php echo esc_attr( $col ); ?>"
                                   value="<?php echo esc_attr( $val ?? '' ); ?>"
                                   style="display:none;width:100%;box-sizing:border-box;" />
                        <?php else : ?>
                            <span style="color:#666;"><?php echo esc_html( $val !== '' && $val !== null ? $val : '—' ); ?></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>

                    <td class="blovian-actions">
                        <?php /* Edit / Save / Cancel buttons */ ?>
                        <button type="button" class="button blovian-btn-edit"
                                data-id="<?php echo esc_attr( $row['entity_id'] ); ?>"
                                <?php echo $is_editing ? 'style="display:none"' : ''; ?>>
                            Edit
                        </button>

                        <form method="post" class="blovian-save-form" style="display:<?php echo $is_editing ? 'inline' : 'none'; ?>;">
                            <?php wp_nonce_field( 'blovian_nonce', 'blovian_nonce_field' ); ?>
                            <input type="hidden" name="blovian_action" value="save_row">
                            <input type="hidden" name="entity_id" value="<?php echo esc_attr( $row['entity_id'] ); ?>">
                            <?php foreach ( $columns as $col => $editable ) : if ( $editable ) : ?>
                                <input type="hidden" class="blovian-hidden-<?php echo esc_attr( $col ); ?>"
                                       name="<?php echo esc_attr( $col ); ?>"
                                       value="<?php echo esc_attr( $row[ $col ] ?? '' ); ?>">
                            <?php endif; endforeach; ?>
                            <button type="submit" class="button button-primary">Save</button>
                            <button type="button" class="button blovian-btn-cancel">Cancel</button>
                        </form>

                        <form method="post" class="blovian-delete-form" style="display:inline;margin-left:4px;">
                            <?php wp_nonce_field( 'blovian_nonce', 'blovian_nonce_field' ); ?>
                            <input type="hidden" name="blovian_action" value="delete_row">
                            <input type="hidden" name="entity_id" value="<?php echo esc_attr( $row['entity_id'] ); ?>">
                            <button type="submit" class="button blovian-btn-delete"
                                    onclick="return confirm('Delete row <?php echo esc_js( $row['entity_id'] ); ?>?');">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div><!-- /overflow-x:auto -->
    </div><!-- /wrap -->

    <style>
    .blovian-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .blovian-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .blovian-table th,
    .blovian-table td {
        border: 1px solid #ccc;
        padding: 7px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .blovian-table th {
        font-size: 16px;
        color: #242424;
        font-weight: 600;
        background: #f9f9f9;
        text-align: left;
    }
    .blovian-table td { color: #333; }
    .blovian-row--editing { background: #fffbe6; }
    .blovian-btn-delete { color: #a00 !important; border-color: #a00 !important; }
    .blovian-input { font-size: 13px; padding: 4px 6px; border: 1px solid #999; border-radius: 3px; }
    </style>

    <script>
    (function() {
        // ── Inline edit toggle ────────────────────────────────────────────
        document.querySelectorAll('.blovian-btn-edit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = btn.closest('tr');
                var id  = btn.dataset.id;

                // Show inputs, hide display spans
                row.querySelectorAll('.blovian-display').forEach(function(s) { s.style.display = 'none'; });
                row.querySelectorAll('.blovian-input').forEach(function(inp) { inp.style.display = 'block'; });

                // Show save form, hide edit button
                btn.style.display = 'none';
                row.querySelector('.blovian-save-form').style.display = 'inline';
            });
        });

        document.querySelectorAll('.blovian-btn-cancel').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = btn.closest('tr');
                row.querySelectorAll('.blovian-display').forEach(function(s) { s.style.display = ''; });
                row.querySelectorAll('.blovian-input').forEach(function(inp) { inp.style.display = 'none'; });
                row.querySelector('.blovian-save-form').style.display = 'none';
                row.querySelector('.blovian-btn-edit').style.display = '';
            });
        });

        // ── Sync input values into hidden fields on save form submit ─────
        document.querySelectorAll('.blovian-save-form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var row = form.closest('tr');
                row.querySelectorAll('.blovian-input').forEach(function(inp) {
                    var col     = inp.name;
                    var hidden  = form.querySelector('.blovian-hidden-' + col);
                    if (hidden) hidden.value = inp.value;
                });
            });
        });

        // ── Auto-open edit mode for newly created rows ────────────────────
        var params = new URLSearchParams(window.location.search);
        var newId  = params.get('new_id');
        if (newId) {
            var editBtn = document.querySelector('.blovian-btn-edit[data-id="' + newId + '"]');
            if (editBtn) editBtn.click();
        }
    })();
    </script>
    <?php
}
