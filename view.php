<?php namespace linkclick;
require_once '../../../wp-load.php';
require_once 'variables.php';
require_once 'functions.php';
global $wpdb;
global $db_links;

try{
    if(!isset($_GET['ticket'])) throw new Exception("Error Processing Request", 1);
    $ticket = trim($_GET['ticket']);
    $rows = $wpdb->get_results("SELECT * FROM ${db_links} WHERE `Ticket` = '{$ticket}'");
    if(!isset($rows[0])) throw new Exception("Error Processing Request", 2);
    $lc = $rows[0];
    $url = parse_url($lc->Target);
    $wp_url = parse_url(get_bloginfo('url'));
    print_r($url);
    print_r($wp_url);
    $is_user_logged_in = is_user_logged_in();
    if($lc->JustTrack == false && !$is_user_logged_in){// if login required and not logged in
        auth_redirect();
    }
    if($is_user_logged_in){ // user is logged in
        $user = wp_get_current_user();
        print_r($user);
        $user_id = $user['data']['ID'];
    }
    if($wp_url['scheme'] == $url['scheme'] && $wp_url['host'] == $url['host']){//is local path
        $path = $url['path'];
        $rp =  realpath($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$path);
        var_dump($rp);
        if(is_file($rp)){//is file -> download
            echo 'd';
            header("Content-Disposition: attachment; filename=\"".basename($path)."\"");
            header("Content-Length: " . filesize($file));
            header("Content-Type: application/octet-stream;");
            readfile($file);
        }elseif(is_dir($rp)){// display website
            echo 'w';
            $file = realpath($rp.DIRECTORY_SEPARATOR.'index.php');
            require $file;
        }
    }else{
        header("Location:{$lc->Target}");
    }
}catch(Exception $e){

}