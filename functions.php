<?php namespace linkclick;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include_once '../../../wp-load.php';
include_once 'variables.php';

 function make_link_from_ticket($ticket){
     return plugins_url(basename(plugin_dir_path(__FILE__))."/view.php?ticket={$ticket}");
 }
function add_new_link($target){
    global $wpdb;
    global $db_links;
    $ticket = get_new_ticket($target);
    $wpdb->insert(
        $db_links,
        [
            'Ticket' => $ticket,
            'Target' => $target
        ]
    );
    return $ticket;
} 
function get_new_ticket($link = null){
    return str_replace('.','_',uniqid($link == null ? md5($link) : '',true)); 
}