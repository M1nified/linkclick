<?php namespace linkclick;
include_once '../../../wp-load.php';
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$user_id = get_current_user_id();
$redirect_to = isset($_POST['redirect_to']) ? urldecode( $_POST['redirect_to'] ) : get_home_url();
// error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] ".print_r([$user_id, $redirect_to, $_POST], true)."\n", 3,  __DIR__.'\..\..\debug.dev.log');
if(!isset($_POST['code_type'])){
    wp_redirect( $redirect_to );
    exit;
}
if(!isset($_POST['code'])){
    wp_redirect( $redirect_to );
    exit;
}
if($user_id === 0){
    wp_redirect( $redirect_to );
    exit;
}
// error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] ".print_r(['NEXT',$user_id, $redirect_to, $_POST], true)."\n", 3,  __DIR__.'\..\..\debug.dev.log');

do_action( 'linkclick_code_validation', $_POST['code_type'], $_POST['code']);

wp_redirect( $redirect_to );
exit;