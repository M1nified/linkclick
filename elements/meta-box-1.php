<?php namespace linkclick;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function add_meta_box_1()
{
    \add_meta_box(
       'linkclick-metabox-1',
       esc_html__('LinkClick', 'linkclick'),
       __NAMESPACE__.'\add_meta_box_1_body',
       ['post','page','attachment'],
      //  ['post','page'],
       'side',
       'default'
    );
}

function add_meta_box_1_body($post, $box)
{
    wp_nonce_field( basename( __FILE__ ), 'linkclick_nonce_1' );
    echo '<p>'.esc_attr( get_post_meta( $post->ID, 'linkclick-metabox-1', true ) ).'</p>';
    $dialog = new DialogPerms();
    $dialog->postId = $post->ID;
    $dialog->printForm = false;
    $dialog->printOut();
}

function save_metabox_1($post_id)
{
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'linkclick_nonce_1' ] ) && wp_verify_nonce( $_POST[ 'linkclick_nonce_1' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ($is_autosave || $is_revision || !$is_valid_nonce) {
        return;
    }
 
    (new \linkclick\DialogPerms)->saveFromPost();
}
