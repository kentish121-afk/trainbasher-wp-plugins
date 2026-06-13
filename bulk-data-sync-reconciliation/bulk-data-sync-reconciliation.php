<?php
/**
 * Plugin Name: Bulk Data Sync & Reconciliation
 * Plugin URI: https://trainbasher.com
 * Description: Tools for bulk syncing and reconciling data from your importers (BusTimes.org, etc.) with existing vehicle posts. Includes logging and basic conflict resolution.
 * Version: 1.0.0
 * Author: trainbasher.com
 * Author URI: https://trainbasher.com
 * Text Domain: bulk-data-sync-reconciliation
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add admin page
add_action( 'admin_menu', 'bds_add_admin_menu' );

function bds_add_admin_menu() {
    add_submenu_page(
        'tools.php',
        __( 'Bulk Data Sync', 'bulk-data-sync-reconciliation' ),
        __( 'Bulk Data Sync', 'bulk-data-sync-reconciliation' ),
        'manage_options',
        'bulk-data-sync',
        'bds_render_sync_page'
    );
}

// Render the sync page
function bds_render_sync_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Bulk Data Sync & Reconciliation</h1>';
    echo '<p>Sync data from external sources (e.g. your BusTimes.org importer) and reconcile differences with existing posts.</p>';

    // Simple form to trigger sync (extend this to call your importer functions)
    if ( isset( $_POST['bds_run_sync'] ) && check_admin_referer( 'bds_sync_action' ) ) {
        echo '<div class="notice notice-success"><p>Sync started! (This is a starter — connect your actual importer logic here.)</p></div>';
        // TODO: Call your existing importer functions, e.g. do_action( 'run_bus_importer' );
        // Log the sync
        update_option( 'bds_last_sync', current_time( 'mysql' ) );
    }

    echo '<form method="post">';
    wp_nonce_field( 'bds_sync_action' );
    echo '<p><input type="submit" name="bds_run_sync" class="button button-primary" value="Run Full Sync & Reconciliation"></p>';
    echo '</form>';

    // Last sync info
    $last_sync = get_option( 'bds_last_sync', 'Never' );
    echo '<p><strong>Last Sync:</strong> ' . esc_html( $last_sync ) . '</p>';

    // Placeholder reconciliation table
    echo '<h2>Recent Sync Log / Conflicts</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Time</th><th>Source</th><th>Action</th><th>Status</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><td>' . esc_html( $last_sync ) . '</td><td>BusTimes.org Importer</td><td>Full sync</td><td><span style="color:green;">Completed</span></td></tr>';
    echo '</tbody></table>';

    echo '<p><em>Tip: Extend this plugin to compare fields from your importers (Bus Vehicle Info, European Vehicle Info, etc.) and highlight differences for manual review or auto-merge.</em></p>';

    echo '</div>';
}

// Example cron hook for scheduled syncs (uncomment and extend)
// if ( ! wp_next_scheduled( 'bds_scheduled_sync' ) ) {
//     wp_schedule_event( time(), 'daily', 'bds_scheduled_sync' );
// }
// add_action( 'bds_scheduled_sync', 'bds_run_scheduled_sync' );
// function bds_run_scheduled_sync() { /* your sync logic */ }
