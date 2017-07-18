<?php namespace linkclick;
require_once '../../../wp-load.php';
require_once 'variables.php';
require_once 'functions.php';
global $wpdb;
global $db_links;
header('Content-type:text/json');
try{
    if(!isset($_GET['func']))
        throw new \Exception("Error Processing Request", 1);
    switch($_GET['func']){
        case 'makeLink':
            if(!isset($_POST['link'])) throw new \Exception("Error Processing Request", 3);
            $link = trim($_POST['link']);
            $select = "SELECT * FROM {$db_links} WHERE `Target` = '{$link}'";
            $currlink = $wpdb->get_results($select);
            // echo $select;
            // print_r($currlink);
            if(isset($currlink[0])){
                $result = [
                    'link' => make_link_from_ticket($currlink[0]->Ticket),
                    'ticket' => $currlink[0]->Ticket,
                    'isnew' => false
                ];
            }else{
                $ticket = add_new_link($link);
                $result = [
                    'link' => make_link_from_ticket($ticket),
                    'ticket' => $ticket,
                    'isnew' => true
                ];
                // throw new \Exception("Error Processing Request", 2);
            }
            break;
        default:
            throw new \Exception("Error Processing Request", 1);
    }
}catch(\Exception $e){
    $result = [
        'error' => true,
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage()
    ];
    // echo json_encode($result);
}finally{
    echo json_encode($result);
}