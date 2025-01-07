<?php
function vca_gen_shortcode() {
    ob_start();
    ?>
    <style>
        #vca-gen-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #vca-gen-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        #vca-gen-form input[type="text"],
        #vca-gen-form input[type="file"],
        #vca-gen-form select,
        #vca-gen-form input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        #vca-gen-form input[type="submit"] {
            background-color: #1F5DAA;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        #vca-gen-form input[type="submit"]:hover {
            background-color: #005177;
        }
    </style>
    <form id="vca-gen-form" method="post" enctype="multipart/form-data">
        <label for="name">Your Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="photo">Upload Passport Photo:</label>
        <input type="file" id="photo" name="photo" accept="image/*" required>
        
        <input type="submit" name="vca_gen_submit" value="Submit">
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('vca_gen_form', 'vca_gen_shortcode');
?>