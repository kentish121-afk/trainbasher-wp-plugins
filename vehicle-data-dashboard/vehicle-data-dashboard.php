<?php
/**
 * Plugin Name: Vehicle Data Dashboard & Analytics
 * Plugin URI: https://trainbasher.com
 * Description: Powerful admin dashboard for monitoring vehicle data, stats, recent imports, and analytics. Works seamlessly with your Bus Vehicle Info, importers, and custom post types.
 * Version: 1.0.0
 * Author: trainbasher.com
 * Author URI: https://trainbasher.com
 * Text Domain: vehicle-data-dashboard
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add admin menu
add_action( 'admin_menu', 'vdd_add_admin_menu' );

function vdd_add_admin_menu() {
    add_menu_page(
        __( 'Vehicle Dashboard', 'vehicle-data-dashboard' ),
        __( 'Vehicle Dashboard', 'vehicle-data-dashboard' ),
        'manage_options',
        'vehicle-dashboard',
        'vdd_render_dashboard_page',
        'dashicons-chart-pie',
        25
    );
}

// Render the dashboard
 function vdd_render_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">🚍 Vehicle Data Dashboard & Analytics</h1>';
    echo '<p>Monitor your bus and vehicle data at a glance. Integrates with your custom importers and post types.</p>';

    // Basic Stats Section
    echo '<div class="vdd-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">';
    
    // Total Vehicles (customize post type as needed)
    $post_type = apply_filters( 'vdd_vehicle_post_type', 'vehicle' );
    $total_vehicles = wp_count_posts( $post_type );
    $published = isset( $total_vehicles->publish ) ? $total_vehicles->publish : 0;
    echo '<div class="vdd-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
    echo '<h3>Total Vehicles</h3>';
    echo '<p style="font-size: 2em; margin: 10px 0; color: #2271b1;">' . esc_html( $published ) . '</p>';
    echo '</div>';

    // Recent updates (last 7 days)
    $recent_args = array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'date_query'     => array(
            array(
                'after' => '7 days ago',
            ),
        ),
    );
    $recent_query = new WP_Query( $recent_args );
    $recent_count = $recent_query->found_posts;
    echo '<div class="vdd-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
    echo '<h3>Updated in Last 7 Days</h3>';
    echo '<p style="font-size: 2em; margin: 10px 0; color: #00a32a;">' . esc_html( $recent_count ) . '</p>';
    echo '</div>';

    echo '</div>';

    // Recent Vehicles Table
    echo '<h2>Recently Updated Vehicles</h2>';
    $recent_vehicles = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => 10,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );

    if ( ! empty( $recent_vehicles ) ) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Title</th><th>Modified</th><th>Status</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ( $recent_vehicles as $vehicle ) {
            echo '<tr>';
            echo '<td><a href="' . get_edit_post_link( $vehicle->ID ) . '">' . esc_html( $vehicle->post_title ) . '</a></td>';
            echo '<td>' . esc_html( human_time_diff( strtotime( $vehicle->post_modified ) ) ) . ' ago</td>';
            echo '<td>' . esc_html( ucfirst( $vehicle->post_status ) ) . '</td>';
            echo '<td><a href="' . get_permalink( $vehicle->ID ) . '" target="_blank">View</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No recent vehicles found. Check your post type filter or importers.</p>';
    }

    // Placeholder for future charts / analytics
    echo '<h2 style="margin-top: 40px;">Analytics Overview</h2>';
    echo '<p><em>Future enhancements: Charts for views (integrates with Post Views Counter), import trends, and more. Extend via hooks.</em></p>';
    echo '<div id="vdd-chart-placeholder" style="height: 300px; background: #f0f0f1; display: flex; align-items: center; justify-content: center; border-radius: 8px;">';
    echo '<p>Chart.js ready — add your data here!</p>';
    echo '</div>';

    echo '</div>';
}

// Enqueue scripts for future charts
add_action( 'admin_enqueue_scripts', 'vdd_enqueue_scripts' );

function vdd_enqueue_scripts( $hook ) {
    if ( 'toplevel_page_vehicle-dashboard' === $hook ) {
        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true );
        // You can add custom JS here for charts
    }
}

// Filter to allow other plugins to change the vehicle post type
// Usage: add_filter( 'vdd_vehicle_post_type', function() { return 'bus'; } );

// Activation hook example
register_activation_hook( __FILE__, 'vdd_activate' );

function vdd_activate() {
    // Flush rewrite rules or set defaults if needed
    flush_rewrite_rules();
}
