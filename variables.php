<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $db_links;
global $db_log;
global $db_category;
$db_links = $wpdb->prefix.'linkclick_link';
$db_log = $wpdb->prefix.'linkclick_log';
$db_category = $wpdb->prefix.'linkclick_category';