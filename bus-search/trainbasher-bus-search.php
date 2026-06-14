<?php
/**
 * Plugin Name:       TrainBasher Bus Search
 * Plugin URI:        https://github.com/kentish121-afk/trainbasher-wp-plugins
 * Description:       Advanced search & filter for the trainbasher.com bus photography archive. Includes structured meta fields, one-click migration from existing titles, AJAX search shortcode, and editor meta box.
 * Version:           1.0.0
 * Author:            Grok for kentish121-afk
 * Author URI:        https://github.com/kentish121-afk
 * License:           GPL v2 or later
 * Text Domain:       trainbasher-bus-search
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TB_SEARCH_VERSION', '1.0.0' );
define( 'TB_SEARCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'TB_SEARCH_URL', plugin_dir_url( __FILE__ ) );

// Meta keys (shared with stats plugin)
define( 'TB_META_OPERATOR', '_tb_operator' );
define( 'TB_META_FLEET',    '_tb_fleet_no' );
define( 'TB_META_REG',      '_tb_reg' );
define( 'TB_META_DATE',     '_tb_spotted_date' );
define( 'TB_META_LOCATION', '_tb_location' );

/**
 * Activation hook
 */
function tb_search_activate() {
    // Nothing heavy on activation
}
register_activation_hook( __FILE__, 'tb_search_activate' );

/**
 * Enqueue admin assets
 */
function tb_search_admin_assets( $hook ) {
    if ( 'settings_page_trainbasher-search' !== $hook && 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'tb-search-admin', TB_SEARCH_URL . 'assets/css/admin.css', array(), TB_SEARCH_VERSION );
    wp_enqueue_script( 'tb-search-admin', TB_SEARCH_URL . 'assets/js/admin.js', array( 'jquery' ), TB_SEARCH_VERSION, true );

    wp_localize_script( 'tb-search-admin', 'tbSearch', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tb_search_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'tb_search_admin_assets' );

/**
 * Enqueue public assets
 */
function tb_search_public_assets() {
    wp_enqueue_style( 'tb-search-public', TB_SEARCH_URL . 'assets/css/public.css', array(), TB_SEARCH_VERSION );
    wp_enqueue_script( 'tb-search-public', TB_SEARCH_URL . 'assets/js/public.js', array( 'jquery' ), TB_SEARCH_VERSION, true );

    wp_localize_script( 'tb-search-public', 'tbSearchPublic', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tb_search_public_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'tb_search_public_assets' );

/**
 * Add meta box for Bus Details
 */
function tb_search_add_meta_box() {
    add_meta_box(
        'tb_bus_details',
        __( 'Bus Sighting Details', 'trainbasher-bus-search' ),
        'tb_search_meta_box_callback',
        'post',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'tb_search_add_meta_box' );

function tb_search_meta_box_callback( $post ) {
    wp_nonce_field( 'tb_search_meta_box', 'tb_search_meta_box_nonce' );

    $operator = get_post_meta( $post->ID, TB_META_OPERATOR, true );
    $fleet    = get_post_meta( $post->ID, TB_META_FLEET, true );
    $reg      = get_post_meta( $post->ID, TB_META_REG, true );
    $date     = get_post_meta( $post->ID, TB_META_DATE, true );
    $location = get_post_meta( $post->ID, TB_META_LOCATION, true );

    ?>
    <p>
        <label for="tb_operator"><strong><?php esc_html_e( 'Operator', 'trainbasher-bus-search' ); ?></strong></label><br>
        <input type="text" id="tb_operator" name="tb_operator" value="<?php echo esc_attr( $operator ); ?>" style="width:100%;" placeholder="e.g. National Express West Midlands">
    </p>
    <p>
        <label for="tb_fleet"><strong><?php esc_html_e( 'Fleet Number', 'trainbasher-bus-search' ); ?></strong></label><br>
        <input type="text" id="tb_fleet" name="tb_fleet" value="<?php echo esc_attr( $fleet ); ?>" style="width:100%;" placeholder="e.g. 4773">
    </p>
    <p>
        <label for="tb_reg"><strong><?php esc_html_e( 'Registration', 'trainbasher-bus-search' ); ?></strong></label><br>
        <input type="text" id="tb_reg" name="tb_reg" value="<?php echo esc_attr( $reg ); ?>" style="width:100%;" placeholder="e.g. BV57XKT">
    </p>
    <p>
        <label for="tb_date"><strong><?php esc_html_e( 'Spotted Date', 'trainbasher-bus-search' ); ?></strong></label><br>
        <input type="date" id="tb_date" name="tb_date" value="<?php echo esc_attr( $date ); ?>" style="width:100%;">
    </p>
    <p>
        <label for="tb_location"><strong><?php esc_html_e( 'Location / Notes', 'trainbasher-bus-search' ); ?></strong></label><br>
        <input type="text" id="tb_location" name="tb_location" value="<?php echo esc_attr( $location ); ?>" style="width:100%;" placeholder="e.g. Birmingham city centre">
    </p>
    <p class="description"><?php esc_html_e( 'These fields power the advanced search and stats. Fill them in for new posts!', 'trainbasher-bus-search' ); ?></p>
    <?php
}

/**
 * Save meta box data
 */
function tb_search_save_meta_box( $post_id ) {
    if ( ! isset( $_POST['tb_search_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['tb_search_meta_box_nonce'], 'tb_search_meta_box' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = array(
        TB_META_OPERATOR => 'tb_operator',
        TB_META_FLEET    => 'tb_fleet',
        TB_META_REG      => 'tb_reg',
        TB_META_DATE     => 'tb_date',
        TB_META_LOCATION => 'tb_location',
    );

    foreach ( $fields as $meta_key => $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            $value = sanitize_text_field( $_POST[ $field ] );
            update_post_meta( $post_id, $meta_key, $value );
        }
    }
}
add_action( 'save_post', 'tb_search_save_meta_box' );

/**
 * Admin menu page
 */
function tb_search_admin_menu() {
    add_options_page(
        __( 'TrainBasher Bus Search', 'trainbasher-bus-search' ),
        __( 'TrainBasher Search', 'trainbasher-bus-search' ),
        'manage_options',
        'trainbasher-search',
        'tb_search_settings_page'
    );
}
add_action( 'admin_menu', 'tb_search_admin_menu' );

function tb_search_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'TrainBasher Bus Search Settings', 'trainbasher-bus-search' ); ?></h1>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php esc_html_e( 'Migrate Existing Posts', 'trainbasher-bus-search' ); ?></h2>
            <p><?php esc_html_e( 'This tool scans your existing posts and extracts bus details from the title (e.g. "National Express West Midlands · 4773 BV57XKT · Jan 30, 2026"). It populates the structured meta fields so the search works great on historical content.', 'trainbasher-bus-search' ); ?></p>
            <p><strong><?php esc_html_e( 'Recommended:', 'trainbasher-bus-search' ); ?></strong> <?php esc_html_e( 'Run this once after activating the plugin. It is safe to run multiple times.', 'trainbasher-bus-search' ); ?></p>
            
            <button type="button" id="tb-migrate-btn" class="button button-primary button-hero">
                <?php esc_html_e( 'Start Migration', 'trainbasher-bus-search' ); ?>
            </button>
            
            <div id="tb-migrate-progress" style="margin-top: 15px; display: none;">
                <div class="progress-bar" style="background:#f0f0f0; height:20px; border-radius:4px; overflow:hidden;">
                    <div id="tb-progress-fill" style="background:#2271b1; height:100%; width:0%; transition: width 0.3s;"></div>
                </div>
                <p id="tb-progress-text" style="margin-top:8px;"></p>
            </div>
            
            <div id="tb-migrate-results" style="margin-top:15px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 30px;">
            <h2><?php esc_html_e( 'How to Use', 'trainbasher-bus-search' ); ?></h2>
            <ol>
                <li><?php esc_html_e( 'Run the migration above (important for existing content).', 'trainbasher-bus-search' ); ?></li>
                <li><?php esc_html_e( 'Create a new page (e.g. "Find a Bus" or "Bus Search") and paste this shortcode:', 'trainbasher-bus-search' ); ?></li>
                <li><code style="background:#f0f0f0; padding:4px 8px; border-radius:3px;">[trainbasher_bus_search]</code></li>
                <li><?php esc_html_e( 'Publish the page and add it to your menu.', 'trainbasher-bus-search' ); ?></li>
                <li><?php esc_html_e( 'For new posts: Fill in the "Bus Sighting Details" box in the sidebar when editing.', 'trainbasher-bus-search' ); ?></li>
            </ol>
        </div>
    </div>
    <?php
}

/**
 * AJAX handler for migration
 */
function tb_search_ajax_migrate() {
    check_ajax_referer( 'tb_search_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }

    $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
    $limit  = 50; // Process 50 posts per request

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'offset'         => $offset,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'fields'         => 'ids',
    );

    $post_ids = get_posts( $args );
    $processed = 0;
    $updated = 0;

    foreach ( $post_ids as $post_id ) {
        $title = get_the_title( $post_id );
        $parsed = tb_search_parse_title( $title );

        if ( $parsed ) {
            if ( ! empty( $parsed['operator'] ) ) {
                update_post_meta( $post_id, TB_META_OPERATOR, sanitize_text_field( $parsed['operator'] ) );
            }
            if ( ! empty( $parsed['fleet'] ) ) {
                update_post_meta( $post_id, TB_META_FLEET, sanitize_text_field( $parsed['fleet'] ) );
            }
            if ( ! empty( $parsed['reg'] ) ) {
                update_post_meta( $post_id, TB_META_REG, sanitize_text_field( $parsed['reg'] ) );
            }
            if ( ! empty( $parsed['date'] ) ) {
                update_post_meta( $post_id, TB_META_DATE, sanitize_text_field( $parsed['date'] ) );
            }
            $updated++;
        }
        $processed++;
    }

    $total_posts = wp_count_posts()->publish;
    $next_offset = $offset + $limit;
    $done = ( $next_offset >= $total_posts );

    wp_send_json_success( array(
        'processed' => $processed,
        'updated'   => $updated,
        'offset'    => $next_offset,
        'done'      => $done,
        'total'     => $total_posts,
    ) );
}
add_action( 'wp_ajax_tb_migrate_posts', 'tb_search_ajax_migrate' );

/**
 * Parse bus details from common title patterns
 */
function tb_search_parse_title( $title ) {
    $title = trim( $title );

    // Common pattern: "Operator Name · 4773 BVXXABC · 30 Jan 2026" or similar
    $patterns = array(
        // Operator · Fleet Reg · Date
        '/^(.+?)\s*·\s*(\d+)\s+([A-Z0-9]{2,}\s?[A-Z0-9]{3,})\s*·\s*(.+)$/i',
        // Operator - Fleet Reg - Date
        '/^(.+?)\s*[-\u2013]\s*(\d+)\s+([A-Z0-9]{2,}\s?[A-Z0-9]{3,})\s*[-\u2013]\s*(.+)$/i',
        // Operator Fleet Reg Date (no separator)
        '/^([A-Za-z][A-Za-z\s&]+?)\s+(\d{2,5})\s+([A-Z]{1,3}\d{1,4}[A-Z]{0,3})\s+(.{5,})$/i',
    );

    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $title, $matches ) ) {
            return array(
                'operator' => trim( $matches[1] ),
                'fleet'    => trim( $matches[2] ),
                'reg'      => strtoupper( trim( $matches[3] ) ),
                'date'     => tb_search_normalize_date( trim( $matches[4] ) ),
            );
        }
    }

    return false;
}

function tb_search_normalize_date( $date_str ) {
    // Try to convert common date formats to Y-m-d
    $timestamp = strtotime( $date_str );
    if ( $timestamp ) {
        return date( 'Y-m-d', $timestamp );
    }
    return $date_str; // fallback
}

/**
 * Shortcode: [trainbasher_bus_search]
 */
function tb_search_shortcode( $atts ) {
    ob_start();
    ?>
    <div class="tb-search-container">
        <form id="tb-search-form" class="tb-search-form">
            <div class="tb-filters">
                <div class="tb-filter-group">
                    <label for="tb-filter-operator"><?php esc_html_e( 'Operator', 'trainbasher-bus-search' ); ?></label>
                    <input type="text" id="tb-filter-operator" name="operator" placeholder="e.g. Diamond Bus or National Express">
                </div>
                <div class="tb-filter-group">
                    <label for="tb-filter-fleet"><?php esc_html_e( 'Fleet Number', 'trainbasher-bus-search' ); ?></label>
                    <input type="text" id="tb-filter-fleet" name="fleet" placeholder="e.g. 4773">
                </div>
                <div class="tb-filter-group">
                    <label for="tb-filter-reg"><?php esc_html_e( 'Registration', 'trainbasher-bus-search' ); ?></label>
                    <input type="text" id="tb-filter-reg" name="reg" placeholder="e.g. BV57XKT">
                </div>
                <div class="tb-filter-group">
                    <label for="tb-filter-date-from"><?php esc_html_e( 'Date From', 'trainbasher-bus-search' ); ?></label>
                    <input type="date" id="tb-filter-date-from" name="date_from">
                </div>
                <div class="tb-filter-group">
                    <label for="tb-filter-date-to"><?php esc_html_e( 'Date To', 'trainbasher-bus-search' ); ?></label>
                    <input type="date" id="tb-filter-date-to" name="date_to">
                </div>
                <div class="tb-filter-group tb-filter-keyword">
                    <label for="tb-filter-keyword"><?php esc_html_e( 'Keyword', 'trainbasher-bus-search' ); ?></label>
                    <input type="text" id="tb-filter-keyword" name="s" placeholder="Search in titles & content">
                </div>
            </div>
            <button type="submit" class="button button-primary tb-search-btn">
                <?php esc_html_e( 'Search Buses', 'trainbasher-bus-search' ); ?>
            </button>
            <button type="button" id="tb-clear-filters" class="button"><?php esc_html_e( 'Clear', 'trainbasher-bus-search' ); ?></button>
        </form>

        <div id="tb-search-results" class="tb-search-results">
            <p class="tb-results-count"></p>
            <div class="tb-results-grid"></div>
            <div class="tb-pagination"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'trainbasher_bus_search', 'tb_search_shortcode' );

/**
 * AJAX handler for public search
 */
function tb_search_ajax_search() {
    check_ajax_referer( 'tb_search_public_nonce', 'nonce' );

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'paged'          => isset( $_POST['paged'] ) ? max( 1, intval( $_POST['paged'] ) ) : 1,
        'orderby'        => 'meta_value',
        'meta_key'       => TB_META_DATE,
        'order'          => 'DESC',
    );

    $meta_query = array( 'relation' => 'AND' );
    $tax_query  = array();

    // Operator
    if ( ! empty( $_POST['operator'] ) ) {
        $meta_query[] = array(
            'key'     => TB_META_OPERATOR,
            'value'   => sanitize_text_field( $_POST['operator'] ),
            'compare' => 'LIKE',
        );
    }

    // Fleet
    if ( ! empty( $_POST['fleet'] ) ) {
        $meta_query[] = array(
            'key'     => TB_META_FLEET,
            'value'   => sanitize_text_field( $_POST['fleet'] ),
            'compare' => 'LIKE',
        );
    }

    // Registration
    if ( ! empty( $_POST['reg'] ) ) {
        $meta_query[] = array(
            'key'     => TB_META_REG,
            'value'   => strtoupper( sanitize_text_field( $_POST['reg'] ) ),
            'compare' => 'LIKE',
        );
    }

    // Date range
    if ( ! empty( $_POST['date_from'] ) || ! empty( $_POST['date_to'] ) ) {
        $date_query = array( 'key' => TB_META_DATE, 'compare' => 'BETWEEN' );
        if ( ! empty( $_POST['date_from'] ) ) $date_query['value'][] = sanitize_text_field( $_POST['date_from'] );
        if ( ! empty( $_POST['date_to'] ) )   $date_query['value'][] = sanitize_text_field( $_POST['date_to'] );
        if ( count( $date_query['value'] ) === 1 ) {
            $date_query['compare'] = '=';
        }
        $meta_query[] = $date_query;
    }

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    // Keyword search
    if ( ! empty( $_POST['s'] ) ) {
        $args['s'] = sanitize_text_field( $_POST['s'] );
    }

    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) {
        echo '<div class="tb-results-grid-inner">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $operator = get_post_meta( get_the_ID(), TB_META_OPERATOR, true );
            $fleet    = get_post_meta( get_the_ID(), TB_META_FLEET, true );
            $reg      = get_post_meta( get_the_ID(), TB_META_REG, true );
            $date     = get_post_meta( get_the_ID(), TB_META_DATE, true );

            ?>
            <div class="tb-result-card">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="tb-result-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="tb-result-content">
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php if ( $operator || $fleet || $reg ) : ?>
                        <div class="tb-bus-meta">
                            <?php if ( $operator ) : ?><span class="tb-operator"><?php echo esc_html( $operator ); ?></span><?php endif; ?>
                            <?php if ( $fleet ) : ?> <span class="tb-fleet">#<?php echo esc_html( $fleet ); ?></span><?php endif; ?>
                            <?php if ( $reg ) : ?> <span class="tb-reg"><?php echo esc_html( $reg ); ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $date ) : ?>
                        <div class="tb-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ); ?></div>
                    <?php endif; ?>
                    <div class="tb-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 18 ); ?></div>
                </div>
            </div>
            <?php
        }
        echo '</div>';

        // Simple pagination
        $total_pages = $query->max_num_pages;
        if ( $total_pages > 1 ) {
            echo '<div class="tb-pagination">';
            for ( $i = 1; $i <= $total_pages; $i++ ) {
                $active = ( $i == $args['paged'] ) ? 'active' : '';
                echo '<button class="tb-page-btn ' . $active . '" data-page="' . $i . '">' . $i . '</button>';
            }
            echo '</div>';
        }
    } else {
        echo '<p class="tb-no-results">' . esc_html__( 'No buses found matching your filters. Try broadening your search.', 'trainbasher-bus-search' ) . '</p>';
    }

    wp_reset_postdata();

    $html = ob_get_clean();

    wp_send_json_success( array(
        'html'  => $html,
        'found' => $query->found_posts,
    ) );
}
add_action( 'wp_ajax_tb_search_buses', 'tb_search_ajax_search' );
add_action( 'wp_ajax_nopriv_tb_search_buses', 'tb_search_ajax_search' );

/**
 * Add "Other sightings of this bus" to single post (basic version)
 */
function tb_search_single_bus_history( $content ) {
    if ( ! is_single() || ! is_main_query() ) {
        return $content;
    }

    $reg = get_post_meta( get_the_ID(), TB_META_REG, true );
    if ( ! $reg ) {
        return $content;
    }

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 6,
        'post__not_in'   => array( get_the_ID() ),
        'meta_query'     => array(
            array(
                'key'     => TB_META_REG,
                'value'   => $reg,
                'compare' => '=',
            ),
        ),
    );

    $related = new WP_Query( $args );

    if ( $related->have_posts() ) {
        $content .= '<div class="tb-related-buses"><h3>' . esc_html__( 'Other sightings of this bus', 'trainbasher-bus-search' ) . '</h3><ul>';
        while ( $related->have_posts() ) {
            $related->the_post();
            $content .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }
        $content .= '</ul></div>';
        wp_reset_postdata();
    }

    return $content;
}
add_filter( 'the_content', 'tb_search_single_bus_history' );

/**
 * Create assets directory and basic CSS/JS on activation (simple approach)
 */
function tb_search_create_assets() {
    $css_dir = TB_SEARCH_PATH . 'assets/css/';
    $js_dir  = TB_SEARCH_PATH . 'assets/js/';

    if ( ! file_exists( $css_dir ) ) {
        wp_mkdir_p( $css_dir );
    }
    if ( ! file_exists( $js_dir ) ) {
        wp_mkdir_p( $js_dir );
    }
}
register_activation_hook( __FILE__, 'tb_search_create_assets' );

// Note: In a real deployment you would include the CSS and JS files.
// For this version, the plugin works with inline styles + core WP styles.
// You can add custom CSS/JS files later for polished design.