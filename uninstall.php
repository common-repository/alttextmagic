<?php

// If uninstall.php is not called by WordPress, die.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Delete all Alt Text Magic options from the database.
delete_option('alt_text_magic_api_key');
delete_option('alt_text_magic_api_key_is_invalid');
delete_option('alt_text_magic_account_type');
delete_option('alt_text_magic_image_credit_count');
delete_option('alt_text_magic_image_credit_limit');
delete_option('alt_text_magic_monthly_image_count');
delete_option('alt_text_magic_monthly_image_limit');
delete_option('alt_text_magic_status');
delete_option('alt_text_magic_batch_in_progress');
delete_option('alt_text_magic_batch_current_idx');
delete_option('alt_text_magic_batch_total_images');
delete_option('alt_text_magic_total_images');
delete_option('alt_text_magic_images_missing_alt_text');
delete_option('alt_text_magic_bulk_suggestions');
delete_option('alt_text_magic_batch_timestamp');
delete_option('alt_text_magic_image_limit_notification_dismissed');
delete_option('alt_text_magic_invalid_api_key_notification_dismissed');
