<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $db_links;
global $lc_db_link;
global $lc_db_log;
global $lc_db_category;
$lc_db_link = $wpdb->prefix.'linkclick_link';
$db_links = $lc_db_link;
$lc_db_log = $wpdb->prefix.'linkclick_log';
$lc_db_category = $wpdb->prefix.'linkclick_category';