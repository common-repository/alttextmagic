<?php

/**
 * Plugin Name: Alt Text Magic
 * Plugin URI: https://alttextmagic.com/
 * Description: Automatically generates descriptive alternative text for images upon upload.
 * Version: 1.0.6
 * Requires at least: 4.2.0
 * Requires PHP: 7.2
 * Author: Minegap LLC
 * Author URI: https://minegap.ai/
 * License: GPL v2 or later
 * Text Domain: alt-text-magic
 * Domain Path: /languages
 */


/**
 * Enqueue the necessary scripts and styles for the plugin.
 *
 * Adds jquery, toast, and Alt Text Magic utils/css to the page.
 * 
 * @return null
 */
function alt_text_magic_enqueue_libraries()
{
    wp_enqueue_script('jquery');
    wp_enqueue_style('toast', plugins_url('css/toastify.css', __FILE__));
    wp_enqueue_script('toast', plugins_url('js/toastify.js', __FILE__));
    wp_enqueue_script('alt_text_magic_utils_js', plugins_url('js/alt_text_magic_utils.js', __FILE__));
    wp_enqueue_style('alt_text_magic_css', plugins_url('css/atm-global.css', __FILE__));
}

/**
 * Activation hook.
 * 
 * Alt Text Magic persistent data is added to the options table
 * when the plugin is activated.
 * 
 * @return null
 */
function alt_text_magic_activate()
{
    // Add initial values for Alt Text Magic in the options table.
    update_option('alt_text_magic_account_type', 'personal');
    update_option('alt_text_magic_image_credit_count', 0);
    update_option('alt_text_magic_image_credit_limit', 0);
    update_option('alt_text_magic_monthly_image_count', 0);
    update_option('alt_text_magic_monthly_image_limit', 0);
    update_option('alt_text_magic_status', 'active');
    update_option('alt_text_magic_batch_in_progress', false);
    update_option('alt_text_magic_batch_current_idx', 0);
    update_option('alt_text_magic_batch_total_images', 0);
    update_option('alt_text_magic_batch_result', array());
    update_option('alt_text_magic_total_images', 0);
    update_option('alt_text_magic_images_missing_alt_text', 0);
    update_option('alt_text_magic_bulk_suggestions', array());
    update_option('alt_text_magic_batch_timestamp', false);
    update_option('alt_text_magic_image_limit_notification_dismissed', false);
    update_option('alt_text_magic_invalid_api_key_notification_dismissed', false);
    update_option('alt_text_magic_language', 'en-US');
    update_option('alt_text_magic_generate_on_upload', '1');
}
// Register activation hook.
register_activation_hook(__FILE__, 'alt_text_magic_activate');


/**
 * Deactivation hook.
 * 
 * Alt Text Magic persistent data is removed from the options table
 * when the plugin is deactivated.
 * 
 * @return null
 */
function alt_text_magic_deactivate()
{
    // Cleanup options added by Alt Text Magic.
    delete_option('alt_text_magic_account_type');
    delete_option('alt_text_magic_image_credit_count');
    delete_option('alt_text_magic_image_credit_limit');
    delete_option('alt_text_magic_monthly_image_count');
    delete_option('alt_text_magic_monthly_image_limit');
    delete_option('alt_text_magic_status');
    delete_option('alt_text_magic_batch_in_progress');
    delete_option('alt_text_magic_batch_current_idx');
    delete_option('alt_text_magic_batch_total_images');
    delete_option('alt_text_magic_batch_result');
    delete_option('alt_text_magic_total_images');
    delete_option('alt_text_magic_images_missing_alt_text');
    delete_option('alt_text_magic_bulk_suggestions');
    delete_option('alt_text_magic_batch_timestamp');
    delete_option('alt_text_magic_image_limit_notification_dismissed');
    delete_option('alt_text_magic_invalid_api_key_notification_dismissed');
    delete_option('alt_text_magic_language');
    delete_option('alt_text_magic_generate_on_upload');
}
// Register deactivation hook.
register_deactivation_hook(__FILE__, 'alt_text_magic_deactivate');

/**
 * Checks to see if the image type is supported.
 *
 * AltTextMagic support 'jpg', 'jpeg', 'jpe', 'gif', 'png', and 'webp'.
 * 
 * @param int      $post_ID    ID of the post
 *
 * @return bool
 */
function alt_text_magic_is_image_supported($post_ID)
{
    $attachment_url = wp_get_attachment_url($post_ID);
    if ($attachment_url === false) {
        return false;
    }
    if (!alt_text_magic_is_well_formed_filename($attachment_url)) {
        return false;
    }

    $ext = pathinfo($attachment_url, PATHINFO_EXTENSION);
    $image_exts = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'webp');
    return in_array($ext, $image_exts, true);
}

/**
 * Add alt text to all $post_IDs whose posts are images in $chunk.
 *
 * This function is the main function for the plugin. It is called
 * when an image attachment is added or when a library update is performed.
 *
 * @param array<int>   $chunk      array of post IDs to add alt text to
 * @param string       $call_type  context of the call ('single' or 'bulk')
 *
 * @return array                   Response data with the results of the alt text addition.
 */
function alt_text_magic_change_alt($chunk, $call_type)
{
    $success = false;
    $request_body = array();
    $im_data_list = array();

    $api_key = get_option('alt_text_magic_api_key');
    if (!$api_key) {
        $request_body = array('message' => 'invalid API key');
        return array('success' => false, 'response' => $request_body);
    }

    foreach ($chunk as $post_ID) {
        if (alt_text_magic_is_image_supported($post_ID)) {
            $post_thumbnail_src = wp_get_attachment_url($post_ID);
            $im = file_get_contents($post_thumbnail_src);
            $imdata = base64_encode($im);
            $im_data_list[] = $imdata;
        }
    }

    $language = get_option('alt_text_magic_language');
    if ($language === false) {
        $language = 'en-US';
    }

    $url = 'https://api.alttextmagic.com/predict';
    $body = array('api_key' => $api_key, 'images' => $im_data_list, 'target_language' => $language);

    $args = array(
        'method'      => 'POST',
        'mode'        => 'cors',
        'headers'     => array(
            'Content-Type'  => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ),
        'body'        => json_encode($body),
        'timeout'     => 120,
        'sslcertificates' => dirname(__FILE__) . '/data/cacert.pem'
    );

    $request = wp_remote_post($url, $args);
    $status = wp_remote_retrieve_response_code($request);
    $success = !(is_wp_error($request) || $status != 200);

    if ($success) {
        $response = wp_remote_retrieve_body($request);
        $response = json_decode($response, true);
        $request_body = json_decode($request['body']);

        // Update alt texts for images in $chunk.
        for ($i = 0; $i < count($response['captions']); $i++) {
            // Index into captions and chunk to get the caption and post_ID.
            $caption = $response['captions'][$i];
            $post_ID = $chunk[$i];
            update_post_meta($post_ID, '_wp_attachment_image_alt', $caption);
        }

        // Update bulk recent suggestions if the $call_type is 'bulk'.
        if ($call_type == 'bulk') {
            $bulk_suggestions = get_option('alt_text_magic_bulk_suggestions');
            if (!$bulk_suggestions) {
                $bulk_suggestions = array();
            }

            for ($i = 0; $i < count($response['captions']); $i++) {
                // Index into captions and chunk to get the caption and post_ID.
                $caption = $response['captions'][$i];
                $post_ID = $chunk[$i];

                array_push($bulk_suggestions, array(
                    'title' => get_the_title($post_ID),
                    'suggestion' => $caption,
                    'url' =>  wp_get_attachment_url($post_ID),
                    'post_ID' => $post_ID,
                ));
            }

            update_option('alt_text_magic_bulk_suggestions', $bulk_suggestions);
        }

        // Update account data.
        $account_type = $request_body->account_type;
        $image_credit_count = $request_body->image_credit_count;
        $image_credit_limit = $request_body->image_credit_limit;
        $monthly_image_count = $request_body->monthly_image_count;
        $monthly_image_limit = $request_body->monthly_image_limit;
        $updated_at = $request_body->updated_at;

        update_option('alt_text_magic_account_type', $account_type);
        update_option('alt_text_magic_image_credit_count', $image_credit_count);
        update_option('alt_text_magic_image_credit_limit', $image_credit_limit);
        update_option('alt_text_magic_monthly_image_count', $monthly_image_count);
        update_option('alt_text_magic_monthly_image_limit', $monthly_image_limit);
        update_option('alt_text_magic_status', $status);
        update_option('alt_text_magic_batch_timestamp', $updated_at);
    } else {
        if ($status === '') {
            $request_body = array('response' => array('message' => 'cURL error'));
        } else if (is_wp_error($request)) {
            $request_body = array('response' => array('message' => 'WP error'));
        } else {
            $request_body = json_decode($request['body']);
            if ($request_body === null) {
                $request_body = array('response' => array('message' => 'Request body is not JSON'));
            } else if (!property_exists($request_body, 'message')) {
                $request_body = array('response' => array('message' => 'Request body did not contain a message'));
            } else if ($request_body->message == 'insufficient image count or credits') {
                update_option('alt_text_magic_image_limit_notification_dismissed', false);
            }
        }
    }

    return array('success' => $success, 'response' => $request_body);
}

/**
 * Checks to see if the filename is well-formed.
 *
 * A well-formed filename is one that does not contain any spaces.
 *
 * @param int      $filename    the filename to check
 *
 * @return bool    true if the filename is well-formed, false otherwise
 */
function alt_text_magic_is_well_formed_filename($filename)
{
    // The param $filename is from wp_get_attachment_url($post_ID), which can return boolean false.
    if ($filename === false) {
        return false;
    }
    // Check if $filename is a string.
    if (is_string($filename) === false) {
        return false;
    }
    // If the filename contains any spaces after trim(), return false.
    $filename = trim($filename);
    if (strpos($filename, ' ') !== false) {
        return false;
    }
    return true;
}


/**
 * Adds alt text to only images when an attachment is added.
 *
 * Calls alt_text_magic_change_alt() to add alt text to the image
 * and passes a $call_type of "single" as the second argument as
 * this call is made when an image is added to the media library
 * and not when a library update is performed.
 *
 * @param int      $post_ID    ID of the attachment that was added
 *
 * @return null
 */
function alt_text_magic_on_add_attachment($post_ID)
{
    $generate_on_upload = get_option('alt_text_magic_generate_on_upload', null);
    // Generate on upload setting is turned off, so return before setting alt text.
    if ($generate_on_upload === '0') {
        return null;
    }

    if (alt_text_magic_is_image_supported($post_ID)) {
        $chunk = array($post_ID);
        alt_text_magic_change_alt($chunk, "single");
    }

    return null;
}
// Add action for when an attachment is added.
add_action('add_attachment', 'alt_text_magic_on_add_attachment', 10, 1);


/**
 * Add notices when Alt Text Magic needs to display information to the user.
 * 
 * Notifications are added when the API Key is invalid or the user
 * has run out of credits. Notifications are only displayed on 
 * index.php and upload.php.
 *
 * @return null
 */
function alt_text_magic_admin_notices()
{
    global $pagenow;
    // Alt Text Magic src icon.
    $icon_src = plugin_dir_url(__FILE__) . 'assets/chicken-icon-gr.png';

    wp_enqueue_script('jquery');

    // Only display notifications on index.php and upload.php.
    if ($pagenow == 'index.php' || $pagenow == 'upload.php') {
        $info = alt_text_magic_get_info();

        if (!$info->success) {
            $notification_dismissed = get_option('alt_text_magic_invalid_api_key_notification_dismissed');
            if (!$notification_dismissed && $info->message == 'invalid API key') {
?>
                <div class="notice notice-error" id="alt-text-magic-invalid-api-key-notice" style="position: relative;">
                    <p>
                        <img src=<?php _e($icon_src) ?> width='16' height='16' style="vertical-align: -4px;" />
                        <?php _e('Alt Text Magic - API Key is invalid.'); ?>
                    </p>
                    <button onclick="let altTextMagicNotice = getElementById('alt-text-magic-invalid-api-key-notice'); altTextMagicNotice.style.display = 'none'; jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action: 'alt_text_magic_dismiss_notification', notification_type: 'invalid_api_key', nonce: '<?php echo wp_create_nonce('dismiss_notification_nonce') ?>' });" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>
            <?php
            }
        } else {
            $image_credit_count = $info->image_credit_count;
            $image_credit_limit = $info->image_credit_limit;
            $monthly_image_count = $info->monthly_image_count;
            $monthly_image_limit = $info->monthly_image_limit;
            $notification_dismissed = get_option('alt_text_magic_image_limit_notification_dismissed');

            if (!$notification_dismissed && $image_credit_count >= $image_credit_limit && $monthly_image_count >= $monthly_image_limit) {
            ?>
                <div class="notice notice-error" id="alt-text-magic-limit-notice" style="position: relative;">
                    <p>
                        <img src=<?php _e($icon_src) ?> width='16' height='16' style="vertical-align: -4px;" />
                        <?php _e('Alt Text Magic - You have reached your alt text limit. Sign in to your <a href="https://user.alttextmagic.com/" target="_blank">account</a> to upgrade your subscription or purchase alt text credits.'); ?>
                    </p>
                    <button onclick="let altTextMagicNotice = getElementById('alt-text-magic-limit-notice'); altTextMagicNotice.style.display = 'none'; jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action: 'alt_text_magic_dismiss_notification', notification_type: 'image_credit_limit', nonce: '<?php echo wp_create_nonce('dismiss_notification_nonce') ?>' });" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>
    <?php
            }
        }
    }
}
// Add action for admin notices.
add_action('admin_notices', 'alt_text_magic_admin_notices');

function alt_text_magic_enqueue_media_script()
{
    global $pagenow;

    if ($pagenow == 'upload.php' || $pagenow == 'post.php') {
        wp_enqueue_style('toast', plugins_url('css/toastify.css', __FILE__));
        wp_enqueue_script('toast', plugins_url('js/toastify.js', __FILE__));
        wp_enqueue_script('alt_text_magic_utils_js', plugins_url('js/alt_text_magic_utils.js', __FILE__));
        // Use media.css instead of global.css as it is not an Alt Text Magic page.
        wp_enqueue_style('alt_text_magic_css', plugins_url('/css/media.css', __FILE__));

        wp_enqueue_script('alt_text_magic_js', plugins_url('/js/media.js', __FILE__));
        alt_text_magic_add_nonces();
        alt_text_magic_add_resources();
    }
}
add_action('admin_enqueue_scripts', 'alt_text_magic_enqueue_media_script');

/**
 * Add the Alt Text Magic menu items to the admin menu.
 *
 * If there is no API Key, or the API Key is invalid, only the
 * account menu item is added so that the user can add an API Key.
 * If the API Key is valid, all of the menu items are added.
 * 
 * @return null
 */
function alt_text_magic_plugin_setup_menu()
{
    wp_cache_flush();
    wp_cache_delete('alt_text_magic_api_key');
    $api_key = get_option('alt_text_magic_api_key');
    $api_key_is_invalid = get_option('alt_text_magic_api_key_is_invalid');
    remove_menu_page('alt-text-magic-plugin');
    if (!$api_key || $api_key_is_invalid) {
        add_menu_page('Alt Text Magic Dashboard', 'Alt Text Magic', 'manage_options', 'alt-text-magic-plugin', 'alt_text_magic_account', plugin_dir_url(__FILE__) . 'assets/chicken-icon-gr.png');
    } else if ($api_key) {
        add_menu_page('Alt Text Magic Dashboard', 'Alt Text Magic', 'manage_options', 'alt-text-magic-plugin', 'alt_text_magic_dashboard', plugin_dir_url(__FILE__) . 'assets/chicken-icon-gr.png');
        add_submenu_page('alt-text-magic-plugin', 'Alt Text Magic Dashboard', 'Dashboard', 'manage_options', 'alt-text-magic-plugin', 'alt_text_magic_dashboard');
        add_submenu_page('alt-text-magic-plugin', 'Alt Text Magic Library Updater', 'Library Updater', 'manage_options', 'alt-text-magic-bulk', 'alt_text_magic_bulk');
        add_submenu_page('alt-text-magic-plugin', 'Alt Text Magic Account', 'Account', 'manage_options', 'alt-text-magic-account', 'alt_text_magic_account');
    }
}
// Add action for admin menu.
add_action('admin_menu', 'alt_text_magic_plugin_setup_menu');

/**
 * Adds nonces to Alt Text Magic admin pages for AJAX requests.
 * 
 * Calls wp_localize_script to add a nonce object containing nonces
 * for AJAX calls.
 * 
 * @return null
 */
function alt_text_magic_add_nonces()
{
    wp_localize_script('alt_text_magic_js', 'alt_text_magic_nonce_obj', array(
        'state_nonce' => wp_create_nonce('state_nonce'),
        'info_nonce' => wp_create_nonce('info_nonce'),
        'set_api_key_nonce' => wp_create_nonce('set_api_key_nonce'),
        'dismiss_notification_nonce' => wp_create_nonce('dismiss_notification_nonce'),
        'change_language_nonce' => wp_create_nonce('change_language_nonce'),
        'get_image_posts_nonce' => wp_create_nonce('get_image_posts_nonce'),
        'chunk_change_alt_text_nonce' => wp_create_nonce('chunk_change_alt_text_nonce'),
        'set_generate_on_upload_nonce' => wp_create_nonce('set_generate_on_upload_nonce'),
    ));
}

/**
 * Adds Alt Text Magic resources.
 * 
 * Calls wp_localize_script to add resources, such as icons, to the
 * supported plugin pages.
 * 
 * @return null
 */
function alt_text_magic_add_resources()
{
    wp_localize_script('alt_text_magic_js', 'alt_text_magic_resources', array(
        'altTextMagicIconSrc' => plugin_dir_url(__FILE__) . 'assets/chicken-icon-gr.png'
    ));
}

/**
 * Includes the dashboard page.
 * 
 * Adds the dashboard php and dashboard js files.
 * 
 * @return null
 */
function alt_text_magic_dashboard()
{
    alt_text_magic_enqueue_libraries();
    include(plugin_dir_path(__FILE__) . "includes/dashboard.php");
    wp_enqueue_script('alt_text_magic_js', plugins_url('/js/dashboard.js', __FILE__));
    alt_text_magic_add_nonces();
}


/**
 * Includes the library updater page.
 * 
 * Adds the library updater php and library updater js files.
 * 
 * @return null
 */
function alt_text_magic_bulk()
{
    alt_text_magic_enqueue_libraries();
    include(plugin_dir_path(__FILE__) . "includes/library_updater.php");
    wp_enqueue_script('alt_text_magic_js', plugins_url('/js/library_updater.js', __FILE__));
    alt_text_magic_add_nonces();
}

/**
 * Includes the account page.
 * 
 * Adds the account php and account js files.
 * 
 * @return null
 */
function alt_text_magic_account()
{
    alt_text_magic_enqueue_libraries();
    include(plugin_dir_path(__FILE__) . "includes/account.php");
    wp_enqueue_script('alt_text_magic_js', plugins_url('/js/account.js', __FILE__));
    alt_text_magic_add_nonces();
}

/**
 * Includes the wp version not supported page.
 * 
 * Adds the wp version not supported php file.
 * 
 * @return null
 */
function alt_text_magic_wp_version_not_supported()
{
    include(plugin_dir_path(__FILE__) . "includes/wp_version_not_supported.php");
}

/**
 * Checks to see if the API Key is valid.
 *
 * An API Key is valid if it is of length 14 and 
 * contains only letters, numbers, underscores, and hyphens.
 * 
 * @return boolean true if the API Key is valid, false if not.
 */
function alt_text_magic_is_api_key_valid($api_key)
{
    if (strlen($api_key) != 14) {
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $api_key)) {
        return false;
    }

    return true;
}

/**
 * Set API Key ajax callback.
 *
 * This is used to set the user's API Key in the options table.
 * 
 * @return null
 */
function alt_text_magic_set_api_key_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'set_api_key_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }
    if (isset($_POST['apiKey'])) {
        // Sanitize the API Key.
        $sanitized_api_key = sanitize_text_field($_POST['apiKey']);
        // Check if the API Key is valid.
        if (alt_text_magic_is_api_key_valid(($sanitized_api_key))) {
            update_option('alt_text_magic_api_key', $sanitized_api_key);

            $info = alt_text_magic_get_info();
            if (!$info->success) {
                if ($info->message == 'invalid API key') {
                    update_option('alt_text_magic_invalid_api_key_notification_dismissed', false);
                }
            }

            update_option('alt_text_magic_api_key_is_invalid', false);
            echo json_encode(array('success' => true));
        } else {
            update_option('alt_text_magic_api_key', '');
            update_option('alt_text_magic_api_key_is_invalid', false);
            echo json_encode(array('success' => false));
        }
    } else {
        update_option('alt_text_magic_api_key', '');
        update_option('alt_text_magic_api_key_is_invalid', false);
        echo json_encode(array('success' => false));
    }

    wp_die();
}
// Add action for set_api_key ajax callback.
add_action('wp_ajax_alt_text_magic_set_api_key', 'alt_text_magic_set_api_key_handler');

/**
 * Set generate on upload handler.
 *
 * This is used to set the user's generate on upload setting in the options table.
 * 
 * @return null
 */
function alt_text_magic_set_generate_on_upload_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'set_generate_on_upload_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }
    if (isset($_POST['generateOnUpload'])) {
        $generate_on_upload = sanitize_text_field($_POST['generateOnUpload']);
        if (!($generate_on_upload === '0' || $generate_on_upload === '1')) {
            echo json_encode(array('success' => false));
            wp_die();
        }

        update_option('alt_text_magic_generate_on_upload', $generate_on_upload);
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false));
    }

    wp_die();
}
// Add action for set_generate_on_upload ajax callback.
add_action('wp_ajax_alt_text_magic_set_generate_on_upload', 'alt_text_magic_set_generate_on_upload_handler');

/**
 * Sets total images and the number of images missing alt text in
 * the options table.
 * 
 * This is called from the library updater to display the number of
 * images that have alt text and the number of images that do not.
 *
 * @return null
 */
function alt_text_magic_set_images_info()
{
    $total_images = 0;
    $images_missing_alt_text = 0;

    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'post_mime_type' => 'image',
    );
    $posts = get_posts($args);

    foreach ($posts as $post) {
        if (alt_text_magic_is_image_supported($post->ID)) {
            $total_images++;
            $current_alt_tag = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
            if ($current_alt_tag == '') {
                $images_missing_alt_text++;
            }
        }
    }

    update_option('alt_text_magic_total_images', $total_images);
    update_option('alt_text_magic_images_missing_alt_text', $images_missing_alt_text);
}

/**
 * Gets the state of the plugin.
 * 
 * Gets the state of the plugin from the options table and returns
 * it as a JSON string.
 *
 * @return null
 */
function alt_text_magic_get_state_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'state_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }
    wp_cache_flush();

    /*
     * Update total images and images missing alt text if
     * the post request passes the update_images_info param.
     */
    if (isset($_POST['update_images_info'])) {
        $update_images_info = filter_var($_POST['update_images_info'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($update_images_info !== NULL && $update_images_info) {
            alt_text_magic_set_images_info();
        }
    }

    $batch_result_needs_clear = false;
    if (get_option('alt_text_magic_batch_result') !== false) {
        $batch_result = get_option('alt_text_magic_batch_result');
        if (count($batch_result) > 0) {
            $batch_result_needs_clear = true;
        }
    }

    echo json_encode(
        array(
            'api_key' => get_option('alt_text_magic_api_key'),
            'account_type' => get_option('alt_text_magic_account_type'),
            'image_credit_count' => get_option('alt_text_magic_image_credit_count'),
            'image_credit_limit' => get_option('alt_text_magic_image_credit_limit'),
            'monthly_image_count' => get_option('alt_text_magic_monthly_image_count'),
            'monthly_image_limit' => get_option('alt_text_magic_monthly_image_limit'),
            'status' => get_option('alt_text_magic_status'),
            'batch_in_progress' => get_option('alt_text_magic_batch_in_progress'),
            'batch_current_idx' => get_option('alt_text_magic_batch_current_idx'),
            'batch_total_images' => get_option('alt_text_magic_batch_total_images'),
            'batch_result' => get_option('alt_text_magic_batch_result'),
            'total_images' => get_option('alt_text_magic_total_images'),
            'images_missing_alt_text' => get_option('alt_text_magic_images_missing_alt_text'),
            'bulk_suggestions' => get_option('alt_text_magic_bulk_suggestions'),
            'batch_timestamp' => get_option('alt_text_magic_batch_timestamp'),
            'language' => get_option('alt_text_magic_language'),
            'generate_on_upload' => get_option('alt_text_magic_generate_on_upload'),
        )
    );

    if ($batch_result_needs_clear) {
        update_option('alt_text_magic_batch_result', array());
    }

    wp_die();
}
// Add action for get_state ajax callback.
add_action('wp_ajax_alt_text_magic_get_state', 'alt_text_magic_get_state_handler');


/**
 * Gets the current account information for the user.
 * 
 * Calls get_info() to get the user's account information and
 * returns it as a JSON string.
 *
 * @return null
 */
function alt_text_magic_info_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'info_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }
    echo json_encode(alt_text_magic_get_info());
    wp_die();
}
// Add action for info ajax callback.
add_action('wp_ajax_alt_text_magic_info', 'alt_text_magic_info_handler');

/**
 * Gets the account information for the user.
 * 
 * Makes a POST request to /api/info to get the user's information
 * then updates the options table with that information and returns it.
 *
 * @return stdClass
 */
function alt_text_magic_get_info()
{
    $api_key = get_option('alt_text_magic_api_key');

    // If there is no api key, return.
    if (!$api_key) {
        $obj = new stdClass;
        $obj->success = false;
        $obj->message = 'no API key';
        return $obj;
    } else {
        $url = 'https://user.alttextmagic.com/api/info';
        $body = array('api_key' => $api_key);

        $args = array(
            'method'      => 'POST',
            'mode'        => 'cors',
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ),
            'body'        => json_encode($body),
            'sslcertificates' => dirname(__FILE__) . '/data/cacert.pem',
        );

        $request = wp_remote_post($url, $args);

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            update_option('alt_text_magic_api_key_is_invalid', true);
            $obj = new stdClass;
            $obj->success = false;
            $obj->message = 'invalid API key';
            return $obj;
        } else {
            $request_body = json_decode($request['body']);
            $request_body->success = true;
            $account_type = $request_body->account_type;
            $image_credit_count = $request_body->image_credit_count;
            $image_credit_limit = $request_body->image_credit_limit;
            $monthly_image_count = $request_body->monthly_image_count;
            $monthly_image_limit = $request_body->monthly_image_limit;

            update_option('alt_text_magic_api_key_is_invalid', false);
            update_option('alt_text_magic_account_type', $account_type);
            update_option('alt_text_magic_image_credit_count', $image_credit_count);
            update_option('alt_text_magic_image_credit_limit', $image_credit_limit);
            update_option('alt_text_magic_monthly_image_count', $monthly_image_count);
            update_option('alt_text_magic_monthly_image_limit', $monthly_image_limit);

            return $request_body;
        }
    }
}

function alt_text_magic_get_image_posts_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'get_image_posts_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }

    $overwrite_alt_tags = filter_var($_POST['overwrite_alt_tags'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($overwrite_alt_tags === NULL) {
        $response = array('response' => 'invalid option: overwrite_alt_tags.');
        echo json_encode(
            array(
                'batch_completed' => false,
                'bulk_cancelled' => false,
                'ran_out_of_credits' => false,
                'response' => $response,
            )
        );
        wp_die();
    }

    // Clear bulk suggestions as a new batch is being started.
    update_option('alt_text_magic_bulk_suggestions', array());

    $posts_that_are_images = array();
    $posts_missing_alt_text = array();
    $total_missing_alt_text = 0;
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'post_mime_type' => 'image',
    );
    $posts = get_posts($args);

    foreach ($posts as $post) {
        if (alt_text_magic_is_image_supported($post->ID)) {
            // Get current alt tag of the image.
            $current_alt_tag = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
            if ($current_alt_tag == '') {
                $posts_missing_alt_text[$post->ID] = true;
                $total_missing_alt_text++;
            }
            // If current alt tag is empty or if overwrite alt tags is checked.
            if ($current_alt_tag == '' or $overwrite_alt_tags) {
                // Append to images.
                $posts_that_are_images[] = $post->ID;
            }
        }
    }

    $image_credit_count = get_option('alt_text_magic_image_credit_count');
    $image_credit_limit = get_option('alt_text_magic_image_credit_limit');
    $monthly_image_count = get_option('alt_text_magic_monthly_image_count');
    $monthly_image_limit = get_option('alt_text_magic_monthly_image_limit');

    echo json_encode(array(
        'posts_that_are_images' => $posts_that_are_images,
        'posts_missing_alt_text' => $posts_missing_alt_text,
        'total_missing_alt_text' => $total_missing_alt_text,
        'image_credit_count' => $image_credit_count,
        'image_credit_limit' => $image_credit_limit,
        'monthly_image_count' => $monthly_image_count,
        'monthly_image_limit' => $monthly_image_limit
    ));
    wp_die();
}
// Add action for get image posts handler.
add_action('wp_ajax_alt_text_magic_get_image_posts', 'alt_text_magic_get_image_posts_handler');

function alt_text_magic_chunk_change_alt_text_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'chunk_change_alt_text_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }

    $chunk = sanitize_text_field($_POST['chunk']);
    $chunk = json_decode($chunk, true);

    if (!$chunk) {
        $response = array('response' => 'invalid option: chunk.');
        echo json_encode(
            array(
                'batch_completed' => false,
                'bulk_cancelled' => false,
                'ran_out_of_credits' => false,
                'response' => $response,
            )
        );
        wp_die();
    } else if (empty($chunk)) {
        $response = array('response' => 'empty chunk.');
        echo json_encode(
            array(
                'batch_completed' => false,
                'bulk_cancelled' => false,
                'ran_out_of_credits' => false,
                'response' => $response,
            )
        );
        wp_die();
    }

    $response = alt_text_magic_change_alt($chunk, "bulk");
    echo json_encode($response);
    wp_die();
}
// Add action for chunk change alt text.
add_action('wp_ajax_alt_text_magic_chunk_change_alt_text', 'alt_text_magic_chunk_change_alt_text_handler');

/**
 * Handles notification dismissal.
 * 
 * Handles either image credit limit or invalid api key notification dismissal
 * by updating options in the option table.
 * 
 * @return null
 */
function alt_text_magic_dismiss_notification_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'dismiss_notification_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }
    $notification_type = sanitize_text_field($_POST['notification_type']);
    if ($notification_type == 'image_credit_limit') {
        update_option('alt_text_magic_image_limit_notification_dismissed', true);
    } else if ($notification_type == 'invalid_api_key') {
        update_option('alt_text_magic_invalid_api_key_notification_dismissed', true);
    } else {
        echo json_encode(
            array(
                'success' => false
            )
        );
        wp_die();
    }

    echo json_encode(
        array(
            'success' => true
        )
    );
    wp_die();
}

add_action('wp_ajax_alt_text_magic_dismiss_notification', 'alt_text_magic_dismiss_notification_handler');

/**
 * Changes the language for alt texts.
 * 
 * Adds the new language to the options table. This language will
 * be used when alt text api calls are made.
 * 
 * @return null
 */
function alt_text_magic_change_language_handler()
{
    if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'change_language_nonce'))) {
        echo json_encode(
            array('success' => false, 'message' => 'invalid nonce')
        );
        wp_die();
    }
    $language = sanitize_text_field($_POST['language']);

    update_option('alt_text_magic_language', $language);
    wp_cache_flush();

    // Echo response.
    echo json_encode(
        array(
            'success' => true
        )
    );
    wp_die();
}
// Add action for the change language handler.
add_action('wp_ajax_alt_text_magic_change_language', 'alt_text_magic_change_language_handler');


/**
 * Adds ajax to the client-side pages.
 * 
 * Adds ajax by adding the admin-ajax.php file.
 * 
 * @return null
 */
function alt_text_magic_add_ajax()
{
    ?>
    <script type="text/javascript">
        ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>
<?php
}
// Add action for the addition of ajax.
add_action('wp_head', 'alt_text_magic_add_ajax', 1);
