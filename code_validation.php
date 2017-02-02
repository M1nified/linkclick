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

do_action( 'linkclick_code_validation', $_POST['code_type'], $_POST['code']);

wp_redirect( $redirect_to );
exit;