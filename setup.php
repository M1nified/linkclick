<?php namespace linkclick; 
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
 include_once 'functions.php';

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
    if (preg_match('/\/wp-content\/uploads\//m',$_SERVER['REQUEST_URI']) === 1) {
        if(is_access_url($_SERVER['REQUEST_URI']) === true){
            $realpathname = realpath("{$_SERVER['DOCUMENT_ROOT']}{$_SERVER['REQUEST_URI']}");
            if(!$realpathname){
                header('HTTP/1.0 404 Not Found');
                exit();
            }
            $filetype = wp_check_filetype($realpathname);
            log_download_of_path($_SERVER['REQUEST_URI']);
            header('Content-Type: '.$filetype['type']);
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: filename=\"" . basename($realpathname) . "\"");
            ob_clean();
            flush(); 
            readfile($realpathname);
            exit(); 
        }else{
            auth_redirect();
            exit();
        }   
    }
}

function action_loop_start( $wp_query ) {
    // print_r($wp_query->posts);
    $posts = $wp_query->posts;
    foreach ($posts as $key => $post) {
        if(is_access($post->ID) === false){
            $login_url = wp_login_url( $_SERVER['REQUEST_URI'] );
            $post->post_content = "<a href=\"{$login_url}\">{$login_url}</a>";
        }
    }
} 
add_action( 'loop_start', 'linkclick\action_loop_start', 10, 1 ); 

// add_filter('manage_post_posts_columns', 'linkclick\add_quick_edit_column');
// function add_quick_edit_column($columns) {
//     $columns['widget_set'] = 'LinkClick';
//     return $columns;
// }

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
    global $lc_db_link;
    // $meta = wp_get_attachment_metadata($id);
    // print_r($id);
    $info = $wpdb->get_row("SELECT *
        FROM {$wpdb->posts} p
        left join {$lc_db_link} ll on ll.PostId = p.ID
        WHERE p.ID = {$id}");
    //print_r($info);
    /*<input type="hidden" name="mode" value="linkclick_update">
    <input type="hidden" name="post_id" value="<?php echo $id; ?>">
    <p><input type="checkbox" name="Secure" <?php echo $info->Secure == 1 ? 'checked' : ''; ?>></p>
    <p><input type="submit"></p>*/
    if($info->Secure == 1){
        ?>
        <p><a href="?post=<?php echo $id; ?>&lc_action=unsecure" class="button">Unsecure</a></p>
        <?php
    }else{?>
        <p><a href="?post=<?php echo $id; ?>&lc_action=secure" class="button">Secure</a></p>
        <?php
    }
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

    add_filter( 'manage_posts_columns', 'linkclick\media_column' );
    add_action( 'manage_posts_custom_column', 'linkclick\media_value', 10, 2 );

    add_filter( 'manage_pages_columns', 'linkclick\media_column' );
    add_action( 'manage_pages_custom_column', 'linkclick\media_value', 10, 2 );
    save_media_page();
}
add_action( 'admin_init', 'linkclick\hook_new_media_columns' );

function save_media_page(){
    echo basename($_SERVER["SCRIPT_FILENAME"]);
    if(basename($_SERVER["SCRIPT_FILENAME"]) != 'upload.php' && basename($_SERVER["SCRIPT_FILENAME"]) != 'edit.php'){
        return;
    }
    global $wpdb;
    global $lc_db_link; 
    if(isset($_GET['post']) && isset($_GET['lc_action'])){
        if($_GET['lc_action'] === 'secure' || $_GET['lc_action'] === 'unsecure' ) {
            if($_GET['lc_action'] === 'secure'){
                secure_media($_GET['post']);
            }
            if($_GET['lc_action'] === 'unsecure'){
                unsecure_media($_GET['post']);
            }
            $update_result = $wpdb->update(
                $lc_db_link,
                [
                    'Secure' => $_GET['lc_action'] == 'secure' ? 1 : 0
                ],
                [
                    'PostId' => $_GET['post'],
                ]
            );
            if($update_result === 0){
                $update_result = $wpdb->insert(
                    $lc_db_link,
                    [
                        'PostId' => $_GET['post'],
                        'Secure' => $_GET['lc_action'] == 'secure' ? 1 : 0
                    ]
                );  
            }
            echo $wpdb->insert_id;
        }
    }
}

add_action('template_redirect','linkclick\template_redirect');
function template_redirect() {
    
}