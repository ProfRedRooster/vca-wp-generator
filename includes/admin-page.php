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

        echo '<div class="updated"><p>Succesvol aangepast in de database</p></div>';
    }

    if ( isset( $_POST['delete'] ) ) {
        $id = intval( $_POST['id'] );
        $wpdb->delete( $table_name, array( 'id' => $id ) );
        echo '<div class="updated"><p>Het is succesvol uit de database verwijderd</p></div>';
    }

    $results = $wpdb->get_results( "SELECT * FROM $table_name" );
    ?>

    <div class="wrap">
        <h1>VCA Gen Admin Page</h1>
        <h2>Existing Data</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Name</th>
                    <th style="width: 10%;">Photo</th>
                    <th style="width: 20%;">Certification Level</th>
                    <th style="width: 15%;">Certificaatnummer</th>
                    <th style="width: 10%;">Geldigheidsdatum</th>
                    <th style="width: 10%;">Afgifte datum:</th>
                    <th style="width: 15%;">Actions</th>
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
                            <select id="certification_level_<?php echo esc_attr( $row->id ); ?>" name="certification_level" required>
                                <option value="">Selecteer behaald certificaat</option>
                                <option value="B-VCA" <?php selected( $row->certification_level, 'B-VCA' ); ?>>B-VCA</option>
                                <option value="VCA-VOL" <?php selected( $row->certification_level, 'VCA-VOL' ); ?>>VCA-VOL</option>
                            </select>
                        </td>
                        <td>
                        <input type="text" id="certificaatnummer_<?php echo esc_attr( $row->id ); ?>" name="certificaatnummer" value="<?php echo esc_attr( $row->certificaatnummer ); ?>" required>
                        </td>
                        <td>
                        <input type="date" id="geldigheidsdatum_<?php echo esc_attr( $row->id ); ?>" name="geldigheidsdatum" value="<?php echo esc_attr( $row->geldigheidsdatum ); ?>" required>
                        </td>
                        <td>
                        <input type="date" id="start_date_<?php echo esc_attr( $row->id ); ?>" name="start_date" value="<?php echo esc_attr( $row->start_date ); ?>" required>
                        </td>
                        <td>
                        <input type="submit" name="update" value="Update" class="button button-primary">
                        <button type="button" class="button button-secondary" onclick="generateBusinessCard(<?php echo esc_attr( $row->id ); ?>)">Genereer VCA kaart</button>
                        <input type="hidden" name="id" value="<?php echo esc_attr( $row->id ); ?>">
                        <input type="submit" name="delete" value="Delete" class="button button-danger" onclick="return confirm('Weet je zeker dat je dit uit de database wilt verwijderen? dit is permanent (dat is erg lang)');">
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
        $background_color = imagecolorallocate($image, 31, 93, 170);
        imagefilledrectangle($image, 0, 0, $width_px, $height_px, $background_color);
        $text_color = imagecolorallocate($image, 255, 255, 255);

        $font_path = __DIR__ . '/Arial.ttf'; // Ensure this path is correct

        if (file_exists($font_path)) {
            imagettftext($image, 70, 0, 30, 100, $text_color, $font_path, $user_data->certification_level);
            imagettftext($image, 30, 0, 30, 200, $text_color, $font_path, "Naam: " . $user_data->name);
            imagettftext($image, 30, 0, 30, 280, $text_color, $font_path, "Geldig tot: " . $user_data->geldigheidsdatum);
            imagettftext($image, 30, 0, 30, 360, $text_color, $font_path, "Afnamedatum: " . $user_data->start_date);
            imagettftext($image, 30, 0, 30, 600, $text_color, $font_path, "Certificaatnummer: " . $user_data->certificaatnummer);
        } else {
            imagestring($image, 5, 10, 30, "Font file not found.", $text_color);
        }

        // Add user photo to the business card
        if (!empty($user_data->photo_url)) {
            $photo = imagecreatefromjpeg($user_data->photo_url);
            if ($photo) {
                // Correct orientation if needed
                $exif = exif_read_data($user_data->photo_url);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $photo = imagerotate($photo, 180, 0);
                            break;
                        case 6:
                            $photo = imagerotate($photo, -90, 0);
                            break;
                        case 8:
                            $photo = imagerotate($photo, 90, 0);
                            break;
                    }
                }

                $photo_width = imagesx($photo);
                $photo_height = imagesy($photo);
                $photo_dest_width = 200;
                $photo_dest_height = ($photo_height / $photo_width) * $photo_dest_width;
                $photo_x = 700; 
                $photo_y = 150; 
                imagecopyresampled($image, $photo, $photo_x, $photo_y, 0, 0, $photo_dest_width, $photo_dest_height, $photo_width, $photo_height);
                imagedestroy($photo);
            }
        }

        // Add MARVEL logo to the business card
        $logo_path = __DIR__ . '/logo.png'; 
        if (file_exists($logo_path)) {
            $logo = imagecreatefrompng($logo_path);
            if ($logo) {
                $logo_width = imagesx($logo);
                $logo_height = imagesy($logo);
                $logo_dest_width = 300;
                $logo_dest_height = ($logo_height / $logo_width) * $logo_dest_width;
                imagecopyresampled($image, $logo, $width_px - $logo_dest_width - 20, $height_px - $logo_dest_height - 20, 0, 0, $logo_dest_width, $logo_dest_height, $logo_width, $logo_height);
                imagedestroy($logo);
            }
        }

        ob_start();
        imagejpeg($image);
        $image_data = ob_get_clean();
        imagedestroy($image);

        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="vca_kaart_' . $user_data->id . '.jpg"');
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