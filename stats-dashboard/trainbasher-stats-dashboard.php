<?php
/**
 * Plugin Name:       TrainBasher Collection Stats
 * Plugin URI:        https://github.com/kentish121-afk/trainbasher-wp-plugins
 * Description:       Beautiful statistics dashboard for the trainbasher.com bus photography collection. Shows totals, top operators, monthly activity, and more. Includes admin page + public shortcode.
 * Version:           1.0.0
 * Author:            Grok for kentish121-afk
 * Author URI:        https://github.com/kentish121-afk
 * License:           GPL v2 or later
 * Text Domain:       trainbasher-stats-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TB_STATS_VERSION', '1.0.0' );
define( 'TB_STATS_PATH', plugin_dir_path( __FILE__ ) );
define( 'TB_STATS_URL', plugin_dir_url( __FILE__ ) );

// Same meta keys as the Search plugin for compatibility
define( 'TB_META_OPERATOR', '_tb_operator' );
define( 'TB_META_FLEET',    '_tb_fleet_no' );
define( 'TB_META_REG',      '_tb_reg' );
define( 'TB_META_DATE',     '_tb_spotted_date' );

/**
 * Enqueue assets for admin and public
 */
function tb_stats_assets( $hook ) {
    // Admin page
    if ( 'tools_page_trainbasher-stats' === $hook || 'index.php' === $hook ) {
        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true );
        wp_enqueue_style( 'tb-stats-admin', TB_STATS_URL . 'assets/css/stats.css', array(), TB_STATS_VERSION );
    }
    
    // Public shortcode
    if ( ! is_admin() ) {
        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true );
        wp_enqueue_style( 'tb-stats-public', TB_STATS_URL . 'assets/css/stats.css', array(), TB_STATS_VERSION );
    }
}
add_action( 'admin_enqueue_scripts', 'tb_stats_assets' );
add_action( 'wp_enqueue_scripts', 'tb_stats_assets' );

/**
 * Admin menu
 */
function tb_stats_admin_menu() {
    add_management_page(
        __( 'TrainBasher Stats', 'trainbasher-stats-dashboard' ),
        __( 'TrainBasher Stats', 'trainbasher-stats-dashboard' ),
        'manage_options',
        'trainbasher-stats',
        'tb_stats_admin_page'
    );
}
add_action( 'admin_menu', 'tb_stats_admin_menu' );

/**
 * Dashboard widget
 */
function tb_stats_dashboard_widget() {
    wp_add_dashboard_widget(
        'tb_stats_widget',
        __( 'TrainBasher Bus Stats', 'trainbasher-stats-dashboard' ),
        'tb_stats_widget_content'
    );
}
add_action( 'wp_dashboard_setup', 'tb_stats_dashboard_widget' );

function tb_stats_widget_content() {
    $stats = tb_stats_get_stats();
    ?>
    <div class="tb-stats-widget">
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div><strong><?php echo esc_html( $stats['total'] ); ?></strong><br><span style="font-size:0.85em;">Total Sightings</span></div>
            <div><strong><?php echo esc_html( $stats['unique_buses'] ); ?></strong><br><span style="font-size:0.85em;">Unique Buses</span></div>
            <div><strong><?php echo esc_html( $stats['operators'] ); ?></strong><br><span style="font-size:0.85em;">Operators</span></div>
        </div>
        <p style="margin-top:12px;"><a href="<?php echo admin_url( 'tools.php?page=trainbasher-stats' ); ?>">View full dashboard →</a></p>
    </div>
    <?php
}

/**
 * Get all stats (cached for performance)
 */
function tb_stats_get_stats() {
    $cache_key = 'tb_stats_data';
    $stats = get_transient( $cache_key );

    if ( false === $stats ) {
        global $wpdb;

        // Total published posts (sightings)
        $total = wp_count_posts()->publish;

        // Unique buses (by registration meta)
        $unique_buses = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
            TB_META_REG
        ) );

        // Number of operators with at least one sighting (from meta)
        $operators = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
            TB_META_OPERATOR
        ) );

        // This month
        $this_month = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p 
             INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id 
             WHERE p.post_type = 'post' AND p.post_status = 'publish' 
             AND m.meta_key = %s AND m.meta_value LIKE %s",
            TB_META_DATE, date( 'Y-m' ) . '%'
        ) );

        // Top 5 operators
        $top_operators = $wpdb->get_results( $wpdb->prepare(
            "SELECT meta_value as operator, COUNT(*) as count 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = %s AND meta_value != '' 
             GROUP BY meta_value ORDER BY count DESC LIMIT 5",
            TB_META_OPERATOR
        ), ARRAY_A );

        // Monthly data for chart (last 12 months)
        $monthly = array();
        for ( $i = 11; $i >= 0; $i-- ) {
            $month = date( 'Y-m', strtotime( "-$i months" ) );
            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p 
                 INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id 
                 WHERE p.post_type = 'post' AND p.post_status = 'publish' 
                 AND m.meta_key = %s AND m.meta_value LIKE %s",
                TB_META_DATE, $month . '%'
            ) );
            $monthly[] = array(
                'month' => date( 'M Y', strtotime( $month ) ),
                'count' => (int) $count
            );
        }

        $stats = array(
            'total'         => (int) $total,
            'unique_buses'  => (int) $unique_buses,
            'operators'     => (int) $operators,
            'this_month'    => (int) $this_month,
            'top_operators' => $top_operators,
            'monthly'       => $monthly,
        );

        set_transient( $cache_key, $stats, 6 * HOUR_IN_SECONDS );
    }

    return $stats;
}

/**
 * Admin stats page
 */
function tb_stats_admin_page() {
    $stats = tb_stats_get_stats();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'TrainBasher Collection Statistics', 'trainbasher-stats-dashboard' ); ?></h1>
        <p><?php esc_html_e( 'Overview of your bus photography archive on trainbasher.com', 'trainbasher-stats-dashboard' ); ?></p>

        <div class="tb-stats-cards" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin:30px 0;">
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['total'] ); ?></div>
                <div class="label">Total Sightings</div>
            </div>
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['unique_buses'] ); ?></div>
                <div class="label">Unique Buses (by reg)</div>
            </div>
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['operators'] ); ?></div>
                <div class="label">Operators Covered</div>
            </div>
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['this_month'] ); ?></div>
                <div class="label">Sightings This Month</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-top:40px;">
            <!-- Top Operators -->
            <div>
                <h2>Top Operators</h2>
                <table class="widefat striped">
                    <thead>
                        <tr><th>Operator</th><th style="text-align:right;">Sightings</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats['top_operators'] as $op ) : ?>
                            <tr>
                                <td><?php echo esc_html( $op['operator'] ); ?></td>
                                <td style="text-align:right;"><strong><?php echo esc_html( $op['count'] ); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Monthly Chart -->
            <div>
                <h2>Monthly Activity (Last 12 Months)</h2>
                <canvas id="tb-monthly-chart" width="400" height="200"></canvas>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('tb-monthly-chart');
                if (ctx && typeof Chart !== 'undefined') {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode( array_column( $stats['monthly'], 'month' ) ); ?>,
                            datasets: [{
                                label: 'Sightings',
                                data: <?php echo json_encode( array_column( $stats['monthly'], 'count' ) ); ?>,
                                borderColor: '#2271b1',
                                backgroundColor: 'rgba(34, 113, 177, 0.1)',
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            });
        </script>

        <p style="margin-top:40px; color:#666;">
            <?php esc_html_e( 'Stats are cached for 6 hours. The numbers improve significantly after running the migration tool in the Bus Search plugin.', 'trainbasher-stats-dashboard' ); ?>
        </p>
    </div>
    <?php
}

/**
 * Public shortcode [trainbasher_stats]
 */
function tb_stats_shortcode() {
    $stats = tb_stats_get_stats();
    ob_start();
    ?>
    <div class="tb-public-stats">
        <div class="tb-stats-cards" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:15px; margin-bottom:30px;">
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['total'] ); ?></div>
                <div class="label">Total Sightings</div>
            </div>
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['unique_buses'] ); ?></div>
                <div class="label">Unique Buses</div>
            </div>
            <div class="tb-stat-card">
                <div class="number"><?php echo esc_html( $stats['operators'] ); ?></div>
                <div class="label">Operators</div>
            </div>
        </div>

        <h3 style="margin-top:30px;">Monthly Sightings (Last 12 Months)</h3>
        <canvas id="tb-public-monthly-chart" style="max-height:320px;"></canvas>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('tb-public-monthly-chart');
                if (ctx && typeof Chart !== 'undefined') {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode( array_column( $stats['monthly'], 'month' ) ); ?>,
                            datasets: [{
                                label: 'Sightings per Month',
                                data: <?php echo json_encode( array_column( $stats['monthly'], 'count' ) ); ?>,
                                backgroundColor: '#2271b1'
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'trainbasher_stats', 'tb_stats_shortcode' );

/**
 * Basic CSS for stats
 */
function tb_stats_create_css() {
    $css_dir = TB_STATS_PATH . 'assets/css/';
    if ( ! file_exists( $css_dir ) ) {
        wp_mkdir_p( $css_dir );
    }
}
register_activation_hook( __FILE__, 'tb_stats_create_css' );