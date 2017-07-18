<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

 function add_meta_boxes(){
     add_meta_box(
         'linkclick-metabox-1',
         esc_html__('LinkClick','linkclick'),
         __NAMESPACE__.'\add_meta_box_1',
         ['post','page','attachment'],
        //  ['post','page'],
         'side',
         'default'
     );
 }

 function add_meta_box_1($post,$box){
     wp_nonce_field( basename( __FILE__ ), 'linkclick_nonce_1' );
     echo '<p>'.esc_attr( get_post_meta( $post->ID, 'linkclick-metabox-1', true ) ).'</p>';
     print_dialog_1(false,$post->ID);
 }

 function metabox_save($post_id){
     // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'linkclick_nonce_1' ] ) && wp_verify_nonce( $_POST[ 'linkclick_nonce_1' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
 
    // Checks for input and sanitizes/saves if needed
    save_meta();
    // if( isset( $_POST[ 'meta-banner_stary' ] )) {
    // }
 }

 add_action('save_post',__NAMESPACE__.'\metabox_save');