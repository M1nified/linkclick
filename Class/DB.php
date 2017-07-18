<?php namespace linkclick;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class DB
{
    const TABLE_RAW_CATEGORY = 'linkclick_category';
    const TABLE_RAW_LINK = 'linkclick_link';
    const TABLE_RAW_LOG = 'linkclick_log';
    const TABLE_RAW_SETTINGS = 'linkclick_settings';

    private $TABLE_CATEGORY;
    private $TABLE_LINK;
    private $TABLE_LOG;
    private $TABLE_SETTINGS;

    private $db;

    function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;

        $this->TABLE_CATEGORY = $this->db->prefix.self::TABLE_RAW_CATEGORY;
        $this->TABLE_LINK = $this->db->prefix.self::TABLE_RAW_LINK;
        $this->TABLE_LOG = $this->db->prefix.self::TABLE_RAW_LOG;
        $this->TABLE_SETTINGS = $this->db->prefix.self::TABLE_RAW_SETTINGS;
    }

    public function getLocks()
    {
        $query = "SELECT s1.option_value as lock_id, s2.option_value as lock_name FROM {$this->TABLE_SETTINGS} s1 LEFT JOIN {$this->TABLE_SETTINGS} s2 ON s1.option_reference = s2.option_reference AND s2.option_name like 'lock_name' WHERE s1.option_name like 'lock_id';";
        echo $query;
        $locks = $this->db->get_results($query);
        return $locks;
    }
}
