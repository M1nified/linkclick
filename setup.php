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
