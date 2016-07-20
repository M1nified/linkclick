<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $db_links;
$db_links = $wpdb->prefix.'linkclick_links';