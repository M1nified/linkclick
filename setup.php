<?php namespace linkclick; 
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

 function add_menu_settings(){
    add_submenu_page(
        'options-general.php',
        'LinkClick',
        'LinkClick',
        'edit_pages',
        'linkclick_settings',
        function(){
            include realpath(__DIR__.'/settings_page.php');
        }
    );
 }

 add_action('admin_menu','linkclick\add_menu_settings');

add_action( 'init', 'linkclick\shall_lock' );
function shall_lock() {
    // tutaj bedzie sprawdzanie czy zalogowany i czy powinien byc
    print_r("shalllock".is_user_logged_in());
    $is_user_logged_in = is_user_logged_in(); 
    return $is_user_logged_in;
}

add_filter('manage_post_posts_columns', 'linkclick\add_quick_edit_column');
function add_quick_edit_column($columns) {
    $columns['widget_set'] = 'LinkClick';
    return $columns;
}

/*add_action('manage_posts_custom_column', 'linkclick\add_column_content', 10, 2);
add_action('manage_pages_custom_column', 'linkclick\add_column_content', 10, 2);
function add_column_content($column_name, $id) {
    switch ($column_name) {
    case 'widget_set':
        echo 'ok';           
        break;
    }
}

add_action( 'quick_edit_custom_box', 'linkclick\add_quick_edit', 'page');
function add_quick_edit($column_name, $post_type){
    ?>
    <fieldset class="inline-edit-col-right inline-edit-book" style="clear:both;">
        <div class="inline-edit-col column-<?php echo $column_name; ?>">
        <label class="inline-edit-group">
        <?php 
            switch ( $column_name ) {
            case 'widget_set':
                ?>
                <span class="title">Author</span><input name="book_author" />
                <?php
                break;
            }
        ?>
        </label>
        </div>
    </fieldset>
    <?php
}
*/


// Add the column
function media_column( $cols ) {
    $cols["filename"] = "Secured";
    return $cols;
}

// Display filenames
function media_value( $column_name, $id ) {
    global $wpdb;
    $meta = wp_get_attachment_metadata($id);
    print_r($id);
    $info = $wpdb->get_row("SELECT *
        FROM www_wordpress.wp_posts p
        left join wp_linkclick_link ll on ll.PostId = p.ID
        WHERE p.ID = {$id}");
    //print_r($info);
    ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" style="display: block;">
        <?php
        /*<input type="hidden" name="mode" value="linkclick_update">
        <input type="hidden" name="post_id" value="<?php echo $id; ?>">
        <p><input type="checkbox" name="Secure" <?php echo $info->Secure == 1 ? 'checked' : ''; ?>></p>
        <p><input type="submit"></p>*/
        if($info->Secure == 1){
            ?>
            <p><a href="?post=<?php echo $id; ?>&lc_action=unsecure" class="button">Unsecure</a></p>
            <?php
        }else{?>
            <!--<a href="<?php echo $_SERVER['REQUEST_URI']; ?>">Secure</a>-->
            <p><a href="?post=<?php echo $id; ?>&lc_action=secure" class="button">Secure</a></p>
            <?php
        }?>
    </form>
    <?php
    //Used a few PHP functions cause 'file' stores local url to file not filename
}

// Register the column as sortable & sort by name
function media_column_sortable( $cols ) {
    $cols["filename"] = "name";

    return $cols;
}


// Hook actions to admin_init
function hook_new_media_columns() {
    add_filter( 'manage_media_columns', 'linkclick\media_column' );
    add_action( 'manage_media_custom_column', 'linkclick\media_value', 10, 2 );
    add_filter( 'manage_upload_sortable_columns', 'linkclick\media_column_sortable' );
}
add_action( 'admin_init', 'linkclick\hook_new_media_columns' );