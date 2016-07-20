<?php namespace linkclick;
require_once ABSPATH.'/wp-load.php';
require_once 'variables.php';
global $wpdb;
global $db_links;
header('Content-type:text/json');
switch($_GET['func']){
    case 'makeLink':
        $currlink = $wpdb->get_results("SELECT * FROM {$db_links} WHERE `Target` = '${$_POST['link']}'");
        if(isset($currlink[0])){
            $result = [
                'link' => $currlink[0]->link
            ]
        }
        break;
}