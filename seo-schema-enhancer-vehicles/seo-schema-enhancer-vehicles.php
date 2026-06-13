<?php
/**
 * Plugin Name: SEO & Schema Enhancer for Vehicles
 * Plugin URI: https://trainbasher.com
 * Description: Automatically adds rich Vehicle schema (JSON-LD) and SEO optimizations for your vehicle posts. Integrates with Yoast SEO and your custom vehicle data.
 * Version: 1.0.0
 * Author: trainbasher.com
 * Author URI: https://trainbasher.com
 * Text Domain: seo-schema-enhancer-vehicles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add JSON-LD Vehicle schema on single vehicle pages
add_action( 'wp_head', 'vse_add_vehicle_schema' );

function vse_add_vehicle_schema() {
    if ( ! is_singular() ) {
        return;
    }

    $post = get_post();
    if ( ! $post ) {
        return;
    }

    // Customize: change post type or add meta condition
    $vehicle_post_types = apply_filters( 'vse_vehicle_post_types', array( 'vehicle', 'bus' ) );

    if ( ! in_array( $post->post_type, $vehicle_post_types, true ) ) {
        return;
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Vehicle',
        'name'     => get_the_title( $post ),
        'url'      => get_permalink( $post ),
        'description' => wp_strip_all_tags( get_the_excerpt( $post ) ?: get_the_title( $post ) ),
    );

    // Add more properties if you have custom fields (example)
    // $schema['vehicleModel'] = get_post_meta( $post->ID, 'model', true );
    // $schema['manufacturer'] = get_post_meta( $post->ID, 'manufacturer', true );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>';
}

// Optional: Add a meta box for manual schema overrides (simple version)
add_action( 'add_meta_boxes', 'vse_add_meta_box' );

function vse_add_meta_box() {
    $post_types = apply_filters( 'vse_vehicle_post_types', array( 'vehicle', 'bus' ) );
    foreach ( $post_types as $pt ) {
        add_meta_box(
            'vse_schema_box',
            'Vehicle SEO & Schema',
            'vse_render_meta_box',
            $pt,
            'side'
        );
    }
}

function vse_render_meta_box( $post ) {
    wp_nonce_field( 'vse_save_schema', 'vse_nonce' );
    $custom_schema = get_post_meta( $post->ID, '_vse_custom_schema', true );
    echo '<p><label for="vse_custom_schema">Custom JSON-LD (advanced):</label></p>';
    echo '<textarea id="vse_custom_schema" name="vse_custom_schema" style="width:100%; height:120px;">' . esc_textarea( $custom_schema ) . '</textarea>';
    echo '<p class="description">Override the auto-generated schema here if needed.</p>';
}

add_action( 'save_post', 'vse_save_meta_box' );

function vse_save_meta_box( $post_id ) {
    if ( ! isset( $_POST['vse_nonce'] ) || ! wp_verify_nonce( $_POST['vse_nonce'], 'vse_save_schema' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['vse_custom_schema'] ) ) {
        update_post_meta( $post_id, '_vse_custom_schema', sanitize_textarea_field( $_POST['vse_custom_schema'] ) );
    }
}

// If Yoast is active, you can hook into its filters for better integration (example placeholder)
// add_filter( 'wpseo_schema_vehicle', 'vse_yoast_vehicle_schema' );
