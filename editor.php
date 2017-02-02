<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// add new buttons
add_filter( 'mce_buttons', __NAMESPACE__.'\register_buttons' );

function register_buttons( $buttons ) {
   array_push( $buttons, 'separator', 'linkclick_button' );
   return $buttons;
}
 
// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
add_filter( 'mce_external_plugins', __NAMESPACE__.'\register_tinymce_javascript' );

function register_tinymce_javascript( $plugin_array ) {
   $plugin_array['linkclick_button'] = plugins_url( '/js/tinymce.js',__FILE__ );
   return $plugin_array;
}