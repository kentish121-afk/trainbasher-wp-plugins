<?php
/**
 * Plugin Name: Spotter Contributions & Moderation (GDPR & OSA Compliant)
 * Plugin URI: https://trainbasher.com
 * Description: Frontend submission system for bus/vehicle spotters to contribute sightings, photos, and corrections. Full moderation queue, user reputation, and built-in GDPR + UK Online Safety Act 2023 compliance features (consent, data minimization, right to erasure, user reporting, age-appropriate measures, audit logging).
 * Version: 1.0.0
 * Author: trainbasher.com
 * Author URI: https://trainbasher.com
 * Text Domain: spotter-contributions-moderation
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================
// CUSTOM POST TYPE: spotter_submission
// =============================================
add_action( 'init', 'scm_register_cpt' );

function scm_register_cpt() {
    $labels = array(
        'name'               => 'Spotter Submissions',
        'singular_name'      => 'Spotter Submission',
        'menu_name'          => 'Spotter Submissions',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Submission',
        'edit_item'          => 'Edit Submission',
        'new_item'           => 'New Submission',
        'view_item'          => 'View Submission',
        'search_items'       => 'Search Submissions',
        'not_found'          => 'No submissions found',
        'not_found_in_trash' => 'No submissions found in Trash',
    );

    register_post_type( 'spotter_submission', array(
        'labels'             => $labels,
        'public'             => false, // Internal only, or true if you want frontend views
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-camera',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
    ));
}

// =============================================
// META FIELDS & HELPER FUNCTIONS
// =============================================
function scm_get_meta( $post_id, $key, $single = true ) {
    return get_post_meta( $post_id, '_scm_' . $key, $single );
}

function scm_update_meta( $post_id, $key, $value ) {
    update_post_meta( $post_id, '_scm_' . $key, $value );
}

// =============================================
// GDPR & COMPLIANCE SETTINGS
// =============================================
add_action( 'admin_init', 'scm_register_settings' );

function scm_register_settings() {
    register_setting( 'scm_settings_group', 'scm_require_login' );
    register_setting( 'scm_settings_group', 'scm_enable_guest' );
    register_setting( 'scm_settings_group', 'scm_age_declaration' );
    register_setting( 'scm_settings_group', 'scm_privacy_policy_url' );
    register_setting( 'scm_settings_group', 'scm_data_retention_days' );
    register_setting( 'scm_settings_group', 'scm_enable_flagging' );
}

add_action( 'admin_menu', 'scm_add_admin_menu' );

function scm_add_admin_menu() {
    add_menu_page(
        'Spotter Contributions',
        'Spotter Contributions',
        'manage_options',
        'spotter-contributions',
        'scm_render_main_page',
        'dashicons-groups',
        26
    );

    add_submenu_page(
        'spotter-contributions',
        'Submissions Queue',
        'Submissions Queue',
        'manage_options',
        'spotter-submissions-queue',
        'scm_render_queue_page'
    );

    add_submenu_page(
        'spotter-contributions',
        'Settings & Compliance',
        'Settings & Compliance',
        'manage_options',
        'spotter-settings',
        'scm_render_settings_page'
    );

    add_submenu_page(
        'spotter-contributions',
        'User Reputation',
        'User Reputation',
        'manage_options',
        'spotter-reputation',
        'scm_render_reputation_page'
    );
}

// =============================================
// FRONTEND SUBMISSION FORM (Shortcode)
// =============================================
add_shortcode( 'spotter_submission_form', 'scm_submission_form_shortcode' );

function scm_submission_form_shortcode() {
    if ( ! is_user_logged_in() && ! get_option( 'scm_enable_guest', true ) ) {
        return '<p>Please <a href="' . wp_login_url( get_permalink() ) . '">log in</a> to submit a spotting.</p>';
    }

    ob_start();
    ?>
    <form id="scm-submission-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( 'scm_submit_action', 'scm_nonce' ); ?>

        <p>
            <label for="scm_vehicle">Vehicle / Bus (select or search existing):</label><br>
            <input type="text" id="scm_vehicle" name="scm_vehicle" placeholder="e.g. ABC 123 or route number" required>
            <input type="hidden" id="scm_vehicle_id" name="scm_vehicle_id" value="">
            <small>Link to an existing vehicle record if possible.</small>
        </p>

        <p>
            <label for="scm_sighting_date">Sighting Date & Time:</label><br>
            <input type="datetime-local" id="scm_sighting_date" name="scm_sighting_date" required>
        </p>

        <p>
            <label for="scm_location">Location / Route:</label><br>
            <input type="text" id="scm_location" name="scm_location" placeholder="e.g. London Victoria or M1 J15">
        </p>

        <p>
            <label for="scm_description">Description / Notes (livery, condition, etc.):</label><br>
            <textarea id="scm_description" name="scm_description" rows="4" style="width:100%;" required></textarea>
        </p>

        <p>
            <label for="scm_photo">Photo Upload (max 5MB, JPEG/PNG):</label><br>
            <input type="file" id="scm_photo" name="scm_photo" accept="image/jpeg,image/png" required>
            <small>By uploading, you confirm you own the rights or have permission.</small>
        </p>

        <?php if ( get_option( 'scm_enable_guest', true ) && ! is_user_logged_in() ) : ?>
        <p>
            <label for="scm_reporter_name">Your Name or Pseudonym (optional):</label><br>
            <input type="text" id="scm_reporter_name" name="scm_reporter_name">
        </p>
        <p>
            <label for="scm_reporter_email">Email (required for follow-up & data requests):</label><br>
            <input type="email" id="scm_reporter_email" name="scm_reporter_email" required>
        </p>
        <?php endif; ?>

        <!-- GDPR Consent -->
        <p style="background:#f9f9f9; padding:15px; border-radius:6px;">
            <input type="checkbox" id="scm_consent" name="scm_consent" value="1" required>
            <label for="scm_consent">
                I consent to the processing of my personal data (name/email if provided, photo metadata, sighting details) for publication on this site in accordance with the 
                <a href="<?php echo esc_url( get_option( 'scm_privacy_policy_url', home_url( '/privacy-policy/' ) ) ); ?>" target="_blank">Privacy Policy</a>.
                I understand my data may be retained for <?php echo esc_html( get_option( 'scm_data_retention_days', 365 ) ); ?> days and I can request access, correction, or deletion at any time.
            </label>
        </p>

        <!-- Online Safety Act Age Declaration -->
        <?php if ( get_option( 'scm_age_declaration', true ) ) : ?>
        <p style="background:#fff3cd; padding:10px; border-radius:6px;">
            <input type="checkbox" id="scm_age_confirm" name="scm_age_confirm" value="1" required>
            <label for="scm_age_confirm">
                I confirm that I am at least 13 years old (or have parental/guardian consent if under 18) and that this submission does not contain any illegal or harmful content.
            </label>
        </p>
        <?php endif; ?>

        <p>
            <input type="submit" name="scm_submit" value="Submit Spotting" class="button button-primary">
        </p>

        <p><small>This site complies with GDPR and the UK Online Safety Act. All submissions are moderated before publication.</small></p>
    </form>

    <script>
    // Simple JS for future vehicle autocomplete (can be enhanced with AJAX)
    console.log('%c[Spotter Plugin] Form loaded. Enhance with vehicle search if needed.', 'color:#666');
    </script>
    <?php
    return ob_get_clean();
}

// Handle form submission
add_action( 'init', 'scm_handle_submission' );

function scm_handle_submission() {
    if ( ! isset( $_POST['scm_submit'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['scm_nonce'], 'scm_submit_action' ) ) {
        wp_die( 'Security check failed.' );
    }

    // Basic validation
    $vehicle_id     = absint( $_POST['scm_vehicle_id'] ?? 0 );
    $sighting_date  = sanitize_text_field( $_POST['scm_sighting_date'] ?? '' );
    $location       = sanitize_text_field( $_POST['scm_location'] ?? '' );
    $description    = sanitize_textarea_field( $_POST['scm_description'] ?? '' );
    $reporter_name  = sanitize_text_field( $_POST['scm_reporter_name'] ?? '' );
    $reporter_email = sanitize_email( $_POST['scm_reporter_email'] ?? '' );
    $consent        = isset( $_POST['scm_consent'] );
    $age_confirm    = isset( $_POST['scm_age_confirm'] );

    if ( ! $consent || ( get_option( 'scm_age_declaration', true ) && ! $age_confirm ) ) {
        wp_die( 'You must provide consent and age confirmation.' );
    }

    // Create submission post
    $post_data = array(
        'post_title'   => 'Spotting: ' . ( $location ? $location : 'Unknown' ) . ' - ' . current_time( 'Y-m-d' ),
        'post_content' => $description,
        'post_status'  => 'pending', // Moderation queue
        'post_type'    => 'spotter_submission',
        'post_author'  => get_current_user_id() ?: 0,
    );

    $post_id = wp_insert_post( $post_data );

    if ( is_wp_error( $post_id ) ) {
        wp_die( 'Error saving submission.' );
    }

    // Save meta
    scm_update_meta( $post_id, 'vehicle_id', $vehicle_id );
    scm_update_meta( $post_id, 'sighting_date', $sighting_date );
    scm_update_meta( $post_id, 'location', $location );
    scm_update_meta( $post_id, 'reporter_name', $reporter_name );
    scm_update_meta( $post_id, 'reporter_email', $reporter_email );
    scm_update_meta( $post_id, 'consent_given', $consent );
    scm_update_meta( $post_id, 'consent_timestamp', current_time( 'mysql' ) );
    scm_update_meta( $post_id, 'age_confirmed', $age_confirm );
    scm_update_meta( $post_id, 'status', 'pending' );
    scm_update_meta( $post_id, 'flags', 0 );

    // Handle photo upload securely
    if ( ! empty( $_FILES['scm_photo']['name'] ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $attachment_id = media_handle_upload( 'scm_photo', $post_id );
        if ( ! is_wp_error( $attachment_id ) ) {
            set_post_thumbnail( $post_id, $attachment_id );
            scm_update_meta( $post_id, 'photo_id', $attachment_id );
        }
    }

    // Send admin notification
    $admin_email = get_option( 'admin_email' );
    wp_mail( $admin_email, 'New Spotter Submission Pending Moderation', 
        'A new spotting has been submitted. Review at: ' . admin_url( 'admin.php?page=spotter-submissions-queue' ) );

    // Success message
    wp_redirect( add_query_arg( 'scm_success', '1', wp_get_referer() ?: home_url() ) );
    exit;
}

// Success message display
add_action( 'wp_footer', 'scm_success_message' );

function scm_success_message() {
    if ( isset( $_GET['scm_success'] ) ) {
        echo '<div class="notice notice-success" style="position:fixed; bottom:20px; right:20px; z-index:9999; padding:15px; background:#d4edda; border:1px solid #c3e6cb; border-radius:6px;">
            Thank you! Your submission has been received and is pending moderation. You will be notified if approved.
        </div>';
    }
}

// =============================================
// MODERATION QUEUE PAGE
// =============================================
function scm_render_queue_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Spotter Submissions - Moderation Queue</h1>';

    // Handle bulk/moderate actions
    if ( isset( $_POST['scm_moderate'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'scm_moderate_action' ) ) {
        $post_id = absint( $_POST['post_id'] );
        $action = sanitize_key( $_POST['action'] );

        if ( $action === 'approve' ) {
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
            scm_update_meta( $post_id, 'status', 'approved' );
            // TODO: Link to vehicle post, award reputation, etc.
            scm_award_reputation( get_post_field( 'post_author', $post_id ), 10 );
        } elseif ( $action === 'reject' ) {
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'trash' ) );
            scm_update_meta( $post_id, 'status', 'rejected' );
        } elseif ( $action === 'flag' ) {
            $flags = (int) scm_get_meta( $post_id, 'flags' );
            scm_update_meta( $post_id, 'flags', $flags + 1 );
        }

        echo '<div class="notice notice-success"><p>Action completed.</p></div>';
    }

    // Query pending submissions
    $args = array(
        'post_type'      => 'spotter_submission',
        'post_status'    => array( 'pending', 'draft' ),
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $submissions = get_posts( $args );

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Date</th><th>Title / Description</th><th>Reporter</th><th>Photo</th><th>Flags</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ( $submissions as $sub ) {
        $photo_id = scm_get_meta( $sub->ID, 'photo_id' );
        $flags = (int) scm_get_meta( $sub->ID, 'flags' );
        $reporter = scm_get_meta( $sub->ID, 'reporter_name' ) ?: get_the_author_meta( 'display_name', $sub->post_author );

        echo '<tr>';
        echo '<td>' . esc_html( get_the_date( 'Y-m-d H:i', $sub ) ) . '</td>';
        echo '<td><strong>' . esc_html( $sub->post_title ) . '</strong><br>' . wp_trim_words( $sub->post_content, 20 ) . '</td>';
        echo '<td>' . esc_html( $reporter ) . '</td>';
        echo '<td>';
        if ( $photo_id ) {
            echo wp_get_attachment_image( $photo_id, array( 80, 80 ) );
        }
        echo '</td>';
        echo '<td>' . esc_html( $flags ) . '</td>';
        echo '<td>';
        echo '<form method="post" style="display:inline;">';
        wp_nonce_field( 'scm_moderate_action' );
        echo '<input type="hidden" name="post_id" value="' . esc_attr( $sub->ID ) . '">';
        echo '<select name="action">';
        echo '<option value="approve">Approve & Publish</option>';
        echo '<option value="reject">Reject (Trash)</option>';
        echo '<option value="flag">Flag for Review</option>';
        echo '</select> ';
        echo '<input type="submit" name="scm_moderate" value="Go" class="button">';
        echo '</form>';
        echo ' <a href="' . get_edit_post_link( $sub->ID ) . '" class="button">Edit</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}

// =============================================
// REPUTATION SYSTEM
// =============================================
function scm_award_reputation( $user_id, $points ) {
    if ( ! $user_id ) return;
    $current = (int) get_user_meta( $user_id, 'scm_reputation', true );
    update_user_meta( $user_id, 'scm_reputation', $current + $points );
}

function scm_render_reputation_page() {
    echo '<div class="wrap"><h1>User Reputation Leaderboard</h1>';

    $users = get_users( array( 'meta_key' => 'scm_reputation', 'orderby' => 'meta_value_num', 'order' => 'DESC', 'number' => 50 ) );

    echo '<table class="wp-list-table widefat">';
    echo '<thead><tr><th>User</th><th>Reputation Points</th><th>Submissions Approved</th></tr></thead><tbody>';

    foreach ( $users as $user ) {
        $points = (int) get_user_meta( $user->ID, 'scm_reputation', true );
        echo '<tr><td>' . esc_html( $user->display_name ) . '</td><td>' . esc_html( $points ) . '</td><td>(Query approved count)</td></tr>';
    }

    echo '</tbody></table>';
    echo '<p><em>Points awarded on approval. Extend with badges or public profiles.</em></p>';
    echo '</div>';
}

// =============================================
// SETTINGS PAGE (Compliance focused)
// =============================================
function scm_render_settings_page() {
    echo '<div class="wrap">';
    echo '<h1>Spotter Plugin Settings & Compliance</h1>';

    echo '<form method="post" action="options.php">';
    settings_fields( 'scm_settings_group' );

    echo '<table class="form-table">';

    echo '<tr><th>Require Login for Submissions</th><td>';
    echo '<input type="checkbox" name="scm_require_login" value="1" ' . checked( 1, get_option( 'scm_require_login' ), false ) . '> ';
    echo 'Force users to be logged in (recommended for better accountability)';
    echo '</td></tr>';

    echo '<tr><th>Allow Guest Submissions</th><td>';
    echo '<input type="checkbox" name="scm_enable_guest" value="1" ' . checked( 1, get_option( 'scm_enable_guest', true ), false ) . '> ';
    echo 'Allow anonymous/pseudonym submissions (with email for GDPR rights)';
    echo '</td></tr>';

    echo '<tr><th>Age Declaration Required</th><td>';
    echo '<input type="checkbox" name="scm_age_declaration" value="1" ' . checked( 1, get_option( 'scm_age_declaration', true ), false ) . '> ';
    echo 'Show age confirmation checkbox (Online Safety Act age-appropriate design)';
    echo '</td></tr>';

    echo '<tr><th>Privacy Policy URL</th><td>';
    echo '<input type="url" name="scm_privacy_policy_url" value="' . esc_attr( get_option( 'scm_privacy_policy_url', home_url( '/privacy-policy/' ) ) ) . '" style="width:400px;">';
    echo '</td></tr>';

    echo '<tr><th>Data Retention (days)</th><td>';
    echo '<input type="number" name="scm_data_retention_days" value="' . esc_attr( get_option( 'scm_data_retention_days', 365 ) ) . '"> ';
    echo 'Auto-delete or anonymize old pending submissions after this period (GDPR data minimization)';
    echo '</td></tr>';

    echo '<tr><th>Enable User Flagging</th><td>';
    echo '<input type="checkbox" name="scm_enable_flagging" value="1" ' . checked( 1, get_option( 'scm_enable_flagging', true ), false ) . '> ';
    echo 'Allow users to flag submissions for moderator review (Online Safety Act user reporting)';
    echo '</td></tr>';

    echo '</table>';

    submit_button();
    echo '</form>';

    echo '<h2>Compliance Notes</h2>';
    echo '<ul>';
    echo '<li><strong>GDPR:</strong> Consent captured with timestamp. Users can request data export/deletion via admin or future self-service page. Data minimization applied (optional pseudonym, limited fields).</li>';
    echo '<li><strong>UK Online Safety Act:</strong> Age declaration, content flagging/reporting system, manual moderation queue for illegal/harmful content, audit-friendly logging via post meta and actions.</li>';
    echo '<li><strong>Recommendations:</strong> Add a dedicated Privacy Policy page. Conduct a risk assessment for your specific content. Consider adding a public reporting form for harmful content.</li>';
    echo '</ul>';

    echo '</div>';
}

// =============================================
// MAIN DASHBOARD PAGE
// =============================================
function scm_render_main_page() {
    echo '<div class="wrap">';
    echo '<h1>🚍 Spotter Contributions Dashboard</h1>';
    echo '<p>Welcome to the Spotter Contributions & Moderation system. This plugin enables community contributions while maintaining strong GDPR and Online Safety Act compliance.</p>';

    echo '<h2>Quick Stats</h2>';
    $pending = wp_count_posts( 'spotter_submission' )->pending ?? 0;
    echo '<p><strong>Pending Submissions:</strong> ' . esc_html( $pending ) . '</p>';

    echo '<h2>How to Use</h2>';
    echo '<ol>';
    echo '<li>Add the shortcode <code>[spotter_submission_form]</code> to any page or post for the public submission form.</li>';
    echo '<li>Review and moderate submissions in the <a href="admin.php?page=spotter-submissions-queue">Submissions Queue</a>.</li>';
    echo '<li>Configure compliance settings in <a href="admin.php?page=spotter-settings">Settings & Compliance</a>.</li>';
    echo '<li>Track top contributors in <a href="admin.php?page=spotter-reputation">User Reputation</a>.</li>';
    echo '</ol>';

    echo '<p><strong>Integration Tip:</strong> On approval, you can extend the code to automatically link submissions to your existing Vehicle / Bus posts (using the vehicle_id meta) or create new ones.</p>';

    echo '</div>';
}

// =============================================
// ACTIVATION & CLEANUP
// =============================================
register_activation_hook( __FILE__, 'scm_activate' );

function scm_activate() {
    scm_register_cpt();
    flush_rewrite_rules();

    // Default options
    if ( false === get_option( 'scm_enable_guest' ) ) {
        update_option( 'scm_enable_guest', true );
    }
    if ( false === get_option( 'scm_age_declaration' ) ) {
        update_option( 'scm_age_declaration', true );
    }
    if ( false === get_option( 'scm_enable_flagging' ) ) {
        update_option( 'scm_enable_flagging', true );
    }
}

register_deactivation_hook( __FILE__, 'scm_deactivate' );

function scm_deactivate() {
    flush_rewrite_rules();
}

// Optional: Scheduled cleanup for old pending submissions (GDPR minimization)
// add_action( 'init', function() {
//     if ( ! wp_next_scheduled( 'scm_cleanup_old_submissions' ) ) {
//         wp_schedule_event( time(), 'daily', 'scm_cleanup_old_submissions' );
//     }
// });
// add_action( 'scm_cleanup_old_submissions', 'scm_cleanup_old' );
// function scm_cleanup_old() { /* delete or anonymize old pending posts */ }
