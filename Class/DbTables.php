<?php namespace linkclick;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class DbTables
{
    const RAW_CATEGORY = '_linkclick-post_id';
    const RAW_LINK = '_linkclick-lock_id';
    const RAW_LOG = '_linkclick-category_id';
    const RAW_SETTINGS = '_linkclick-date';

    public static function getTableName($raw_name, $prefix = null)
    {
        if ($prefix === null) {
            global $wpdb;
            $prefix = $wpdb->prefix;
        }
        return $prefix.$raw_name;
    }
}
