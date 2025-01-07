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

        // Rename the file to the name of the person
        $file_extension = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);
        $new_file_name = sanitize_file_name($name) . '.' . $file_extension;
        $uploaded_file['name'] = $new_file_name;

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
                )
            );
            wp_redirect(home_url('/thank-you'));
            exit;
        } else {
            // Handle upload error (e.g., set an error message)
        }
    }
}

add_action('init', 'vca_gen_handle_form_submission');
?>