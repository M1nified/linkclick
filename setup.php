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
        $is_access = is_access_url($_SERVER['REQUEST_URI']);
        if($is_access === true){
            $realpathname = realpath("{$_SERVER['DOCUMENT_ROOT']}{$_SERVER['REQUEST_URI']}");
            if(!$realpathname){
                // echo "404";
                // header('HTTP/1.0 404 Not Found');
                global $wp_query;
                $wp_query->set_404();
                status_header( 404 );
                // get_template_part( 404 );
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
    if(is_singular( )){
        foreach ($posts as $key => $post) {
            $is_access = is_access($post->ID);
            if($is_access === true){
                continue;
            }
            switch ($is_access) {
                case 3:
                    $login_url = wp_login_url( get_permalink() );
                    $post->post_content = "<p class=\"text-center\">"._x("Treść dostępna po zalogowaniu", 'default')."</p><p class=\"text-right\"><a class=\"btn btn-primary\" href=\"{$login_url}\">"._x("Logowanie",'default')."</a></p>";
                    break;
                
                case 2:
                    if(is_user_logged_in() != 1){
                        $login_url = wp_login_url( get_permalink() );
                        $post->post_content = "<p class=\"text-center\">"._x("Treść dostępna po zalogowaniu", 'default')."</p><p class=\"text-right\"><a class=\"btn btn-primary\" href=\"{$login_url}\">"._x("Logowanie",'default')."</a></p>";
                    }elseif(get_metadata( 'user', get_current_user_id(), 'ss_has_serial', true ) != true){
                        global $code_validation_url;
                        $post->post_content = "<p>Treść dostępna po podaniu klucza licencji</p><form method=\"post\" action=\"{$code_validation_url}\"><input type=\"hidden\" name=\"redirect_to\" value=\"".get_permalink()."\"><input type=\"hidden\" name=\"code_type\" value=\"ss_has_serial\"><p><label>Numer seryjny: <input name=\"code\" type=\"text\" placeholder=\"\" required></label></p><p><input class=\"button button-primary\" type=\"submit\"></p></form>";
                    }
                
                default:
                    # code...
                    break;
            }

        }
    }else{
        foreach ($posts as $key => $post) {
            $is_access = is_access($post->ID);
            if($is_access !== true){
                $login_url = wp_login_url( $_SERVER['REQUEST_URI'] );
                $post->post_content = $post->post_excerpt;//."<p><a href=\"{$login_url}\" class=\"btn btn-primary\" role=\"button\">"._x( 'Log in', 'default' )."</a></p>";
                // $post->post_excerpt = "hidden";
            }
        }
    }
} 
add_action( 'loop_start', __NAMESPACE__.'\action_loop_start', 10, 1 ); 

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
    $cols["lc_security"] = "LinkClick";
    return $cols;
}

// Display filenames
function media_value( $column_name, $id ) {
    if($column_name != 'lc_security'){
        return;
    }
    global $wpdb;
    global $lc_db_link;
    global $meta_lock_id;
    global $meta_category_id;
    // $meta = wp_get_attachment_metadata($id);
    // print_r($id);
    $info = $wpdb->get_row("SELECT
        p.*,
        ll.*,
        pm.meta_value as lc_lock_id,
        pmcat.meta_value as lc_category_id
        FROM {$wpdb->posts} p
        left join {$lc_db_link} ll on ll.PostId = p.ID
        left join {$wpdb->postmeta} pm on pm.post_id = p.ID and pm.meta_key = '{$meta_lock_id}'
        left join {$wpdb->postmeta} pmcat on pmcat.post_id = p.ID and pmcat.meta_key = '{$meta_category_id}'
        WHERE p.ID = {$id}");
    //print_r($info);
    /*<input type="hidden" name="mode" value="linkclick_update">
    <input type="hidden" name="post_id" value="<?php echo $id; ?>">
    <p><input type="checkbox" name="Secure" <?php echo $info->Secure == 1 ? 'checked' : ''; ?>></p>
    <p><input type="submit"></p>*/
    /*if($info->Secure == 1){
        ?>
        <p><a href="?post=<?php echo $id; ?>&lc_action=unsecure" class="button">Unsecure</a></p>
        <?php
    }else{?>
        <p><a href="?post=<?php echo $id; ?>&lc_action=secure" class="button">Secure</a></p>
        <?php
    }*/
    ?>
    <p><button class="button button-primary linkclick-btn-secure" type="button" data-post-id="<?php echo $id; ?>" data-linkclick-category-id="<?php echo $info->lc_category_id; ?>" data-linkclick-lock-id="<?php echo $info->lc_lock_id; ?>" >Security</button></p>
    <?php
    /* <span class="badge"><?php echo str_replace("~","&nbsp;",str_pad($info->lc_category_id ? $info->lc_category_id : 0,3,"~",STR_PAD_LEFT)); ?></span> */
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
    save_meta();
}
add_action( 'admin_init', 'linkclick\hook_new_media_columns' );

add_action('template_redirect','linkclick\template_redirect');
function template_redirect() {
    
}


function add_admin_scripts( $hook ) {
    if(in_array($hook,[
        'edit.php',
        'upload.php'
    ])){
        wp_enqueue_script('linkclick_secure',plugins_url(basename(plugin_dir_path(__FILE__)).'/js/secure.js'),['jquery','jquery-ui-dialog']);
        wp_enqueue_style( 'style-jquery-ui-dialog', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
    }
}

function add_dialog_1(){
    add_action( 'in_admin_footer', __NAMESPACE__.'\print_dialog_1_form');
} 
add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\add_admin_scripts', 10, 1 );
add_action( 'edit.php', __NAMESPACE__.'\add_dialog_1');
add_action( 'load-edit.php', __NAMESPACE__.'\add_dialog_1');
add_action( 'upload.php', __NAMESPACE__.'\add_dialog_1');
add_action( 'load-upload.php', __NAMESPACE__.'\add_dialog_1');
// add_action( 'post.php', __NAMESPACE__.'\add_dialog_1');
// add_action( 'load-post.php', __NAMESPACE__.'\add_dialog_1');

function setup_boxes(){
    add_action('add_meta_boxes',__NAMESPACE__.'\add_meta_boxes');
}
add_action('load-post.php',__NAMESPACE__.'\setup_boxes');
add_action('load-post-new.php',__NAMESPACE__.'\setup_boxes');


add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\load_bootstrap' );
function load_bootstrap($hook) {
    wp_enqueue_style( 'bootstrap_css', plugins_url('lib/bootstrap/css/bootstrap.min.css', __FILE__) );
}