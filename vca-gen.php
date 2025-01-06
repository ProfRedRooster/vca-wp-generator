<?php
/**
 * Plugin Name: VCA Gen
 * Description: A plugin to generate VCA certification cards.
 * Version: 0.1
 * Author: Rohan de Graaf for MARVEL Concultancy
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/form-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-page.php';

// Function to create database tables
function vca_gen_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'vca_gen_users';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        photo_url varchar(255) NOT NULL,
        certification_level varchar(50) NOT NULL,
        certificaatnummer varchar(50) NOT NULL,
        geldigheidsdatum date NOT NULL,
        start_date date NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Hook to run the function upon plugin activation
register_activation_hook( __FILE__, 'vca_gen_create_tables' );

// Register the shortcode
function vca_gen_register_shortcodes() {
    add_shortcode( 'vca_gen_form', 'vca_gen_shortcode' ); // Corrected function name
}
add_action( 'init', 'vca_gen_register_shortcodes' );

// Enqueue scripts and styles
function vca_gen_enqueue_scripts() {
    wp_enqueue_style( 'vca-gen-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
    wp_enqueue_script( 'vca-gen-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'), null, true );
}
add_action( 'wp_enqueue_scripts', 'vca_gen_enqueue_scripts' );
?>