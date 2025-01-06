<?php
function vca_gen_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vca_gen_submit'])) {
        // Include the WordPress file for handling uploads
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        // Validate input fields
        if (empty($_POST['name']) || empty($_FILES['photo']['name'])) {
            return; // Handle error (e.g., set an error message)
        }

        $name = sanitize_text_field($_POST['name']);
        $uploaded_file = $_FILES['photo'];

        // Handle file upload
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $photo_url = $movefile['url'];

            global $wpdb;
            $table_name = $wpdb->prefix . 'vca_gen_users';

            // Insert data into the database
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'photo_url' => $photo_url
                ) // Added missing closing parenthesis here
            );
        } else {
            // Handle upload error (e.g., set an error message)
        }
    }
}

add_action('init', 'vca_gen_handle_form_submission');
?>