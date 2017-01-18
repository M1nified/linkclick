<?php namespace linkclick;
include_once '../../../wp-load.php';
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$user_id = get_current_user_id();
$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : get_home_url();
if(!isset($_POST['code_type'])){
    wp_redirect( $redirect_to );
    exit;
}
if(!isset($_POST['code'])){
    wp_redirect( $redirect_to );
    exit;
}
if($user_id == 0){
    wp_redirect( $redirect_to );
    exit;
}
switch ($_POST['code_type']) {
    case 'ss_has_serial':
        //
        if(eregi("^[[:alpha:]]{2,4}[0-9]{3}[a-z0-9-]*$",$_POST['code'])){
            update_metadata( 'user', $user_id, 'ss_has_serial', true );
        }
        break;
    
    default:
        # code...
        break;
}
wp_redirect( $redirect_to );
exit;