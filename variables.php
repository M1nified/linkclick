<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $db_links;
global $lc_db_link;
global $lc_db_log;
global $lc_db_category;
global $lc_db_settings;
$lc_db_link = $wpdb->prefix.'linkclick_link';
$db_links = $lc_db_link;
$lc_db_log = $wpdb->prefix.'linkclick_log';
$lc_db_category = $wpdb->prefix.'linkclick_category';
$lc_db_settings = $wpdb->prefix.'linkclick_settings';

// meta names
global $meta_lock_id;
global $meta_category_id;
$meta_lock_id = '_linkclick-lock_id';
$meta_category_id = '_linkclick-category_id';
$meta_date = '_linkclick-date';

//
$code_validation_url = plugins_url(basename(plugin_dir_path(__FILE__)).'/code_validation.php');

// bots
global $bots_allowed_agents;
global $bots_allowed_domains;
$bots_allowed_agents = [
    'Google',
    'msnbot',
    'bingbot'
];
$bots_allowed_domains = [
    'googlebot.com',
    'google.com',
    'search.msn.com'
    // 'k125'
];