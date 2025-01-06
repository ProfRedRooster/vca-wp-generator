<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Get the option name or custom table name if used
global $wpdb;
$table_name = $wpdb->prefix . 'vca_gen_data';

// Delete the data from the database
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Optionally, delete any options related to the plugin
delete_option( 'vca_gen_options' );
?>