<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function get_sitelic_path($instance){
    return isset($instance['sitelic_path'])?$instance['sitelic_path']:'/';;
}
function get_title($instance){
    return isset($instance['title'])?$instance['title']:'';;
}
