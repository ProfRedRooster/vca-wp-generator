<?php
// Admin page for VCA Gen plugin

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vca_gen_admin_menu() {
    add_menu_page(
        'VCA Gen Admin',
        'VCA Gen',
        'manage_options',
        'vca-gen-admin',
        'vca_gen_admin_page',
        'dashicons-admin-generic',
        6
    );
}
add_action( 'admin_menu', 'vca_gen_admin_menu' );

function vca_gen_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vca_gen_users';

    if ( isset( $_POST['update'] ) ) {
        $id = intval( $_POST['id'] );
        $certification_level = sanitize_text_field( $_POST['certification_level'] );
        $certificaatnummer = sanitize_text_field( $_POST['certificaatnummer'] );
        $geldigheidsdatum = sanitize_text_field( $_POST['geldigheidsdatum'] );
        $start_date = sanitize_text_field( $_POST['start_date'] );

        $wpdb->update(
            $table_name,
            array(
                'certification_level' => $certification_level,
                'certificaatnummer' => $certificaatnummer,
                'geldigheidsdatum' => $geldigheidsdatum,
                'start_date' => $start_date,
            ),
            array( 'id' => $id )
        );

        echo '<div class="updated"><p>Data updated successfully!</p></div>';
    }

    if ( isset( $_POST['delete'] ) ) {
        $id = intval( $_POST['id'] );
        $wpdb->delete( $table_name, array( 'id' => $id ) );
        echo '<div class="updated"><p>Data deleted successfully!</p></div>';
    }

    $results = $wpdb->get_results( "SELECT * FROM $table_name" );
    ?>

    <div class="wrap">
        <h1>VCA Gen Admin Page</h1>
        <h2>Existing Data</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Photo</th>
                    <th>Certification Level</th>
                    <th>Certificaatnummer</th>
                    <th>Geldigheidsdatum</th>
                    <th>Start Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $results as $row ) : ?>
                    <tr>
                    <form method="post" action="">
                        <td><?php echo esc_html( $row->id ); ?></td>
                        <td><?php echo esc_html( $row->name ); ?></td>
                        <td><img src="<?php echo esc_url( $row->photo_url ); ?>" alt="User Photo" style="max-width: 100px; height: auto;"></td>
                        <td>
                        <input type="text" id="certification_level_<?php echo esc_attr( $row->id ); ?>" name="certification_level" value="<?php echo esc_attr( $row->certification_level ); ?>" required>
                        </td>
                        <td>
                        <input type="text" id="certificaatnummer_<?php echo esc_attr( $row->id ); ?>" name="certificaatnummer" value="<?php echo esc_attr( $row->certificaatnummer ); ?>" required>
                        </td>
                        <td>
                        <input type="date" id="geldigheidsdatum_<?php echo esc_attr( $row->id ); ?>" name="geldigheidsdatum" value="<?php echo esc_attr( $row->geldigheidsdatum ); ?>" required>
                        </td>
                        <td>
                        <input type="date" id="start_date_<?php echo esc_attr( $row->id ); ?>" name="Afgiftedatum" value="<?php echo esc_attr( $row->start_date ); ?>" required>
                        </td>
                        <td>
                        <input type="submit" name="update" value="Update" class="button button-primary">
                        <button type="button" class="button button-secondary" onclick="generateBusinessCard(<?php echo esc_attr( $row->id ); ?>)">Genereer VCA kaart</button>
                        <input type="hidden" name="id" value="<?php echo esc_attr( $row->id ); ?>">
                        <input type="submit" name="delete" value="Delete" class="button button-danger" onclick="return confirm('Are you sure you want to delete this item?');">
                       </td>                
                    </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        function generateBusinessCard(userId) {
            window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=generate_business_card&id=' + userId;
        }
    </script>

    <?php
}

add_action('wp_ajax_generate_business_card', 'vca_gen_generate_business_card');
add_action('wp_ajax_nopriv_generate_business_card', 'vca_gen_generate_business_card');

function vca_gen_generate_business_card() {
    if (!isset($_GET['id'])) {
        return;
    }

    $id = intval($_GET['id']);
    global $wpdb;
    $table_name = $wpdb->prefix . 'vca_gen_users';
    $user_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );

    if ( $user_data ) {
        // Generate business card JPG
        $width_mm = 85.3;
        $height_mm = 53.7;
        $dpi = 300;
        $width_px = ($width_mm / 25.4) * $dpi;
        $height_px = ($height_mm / 25.4) * $dpi;

        $image = imagecreatetruecolor($width_px, $height_px);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width_px, $height_px, $background_color);
        $text_color = imagecolorallocate($image, 0, 0, 0);

        $font_path = __DIR__ . '/Arial.ttf'; // Ensure this path is correct

        if (file_exists($font_path)) {
            imagettftext($image, 12, 0, 10, 20, $text_color, $font_path, "Name: " . $user_data->name);
            imagettftext($image, 12, 0, 10, 50, $text_color, $font_path, "Certification Level: " . $user_data->certification_level);
            imagettftext($image, 12, 0, 10, 80, $text_color, $font_path, "Certificaatnummer: " . $user_data->certificaatnummer);
            imagettftext($image, 12, 0, 10, 110, $text_color, $font_path, "Geldigheidsdatum: " . $user_data->geldigheidsdatum);
            imagettftext($image, 12, 0, 10, 140, $text_color, $font_path, "Afnamedatum " . $user_data->start_date);
        } else {
            imagestring($image, 5, 10, 20, "Font file not found.", $text_color);
        }

        // Add user photo to the business card
        if (!empty($user_data->photo_url)) {
            $photo = imagecreatefromjpeg($user_data->photo_url);
            if ($photo) {
                $photo_width = imagesx($photo);
                $photo_height = imagesy($photo);
                $photo_dest_width = 100;
                $photo_dest_height = ($photo_height / $photo_width) * $photo_dest_width;
                imagecopyresampled($image, $photo, $width_px - $photo_dest_width - 10, 10, 0, 0, $photo_dest_width, $photo_dest_height, $photo_width, $photo_height);
                imagedestroy($photo);
            }
        }

        // Add VCA logo to the business card
        $logo_path = __DIR__ . '/logo.png'; 
        if (file_exists($logo_path)) {
            $logo = imagecreatefrompng($logo_path);
            if ($logo) {
                $logo_width = imagesx($logo);
                $logo_height = imagesy($logo);
                $logo_dest_width = 400;
                $logo_dest_height = ($logo_height / $logo_width) * $logo_dest_width;
                imagecopyresampled($image, $logo, 10, $height_px - $logo_dest_height - 10, 0, 0, $logo_dest_width, $logo_dest_height, $logo_width, $logo_height);
                imagedestroy($logo);
            }
        }

        ob_start();
        imagejpeg($image);
        $image_data = ob_get_clean();
        imagedestroy($image);

        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="business_card_' . $user_data->id . '.jpg"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($image_data));
        echo $image_data;
        exit;
    }
}
?>