<?php namespace linkclick;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('add_meta_boxes', __NAMESPACE__.'\add_meta_box_1');
add_action('save_post', __NAMESPACE__.'\save_metabox_1');
