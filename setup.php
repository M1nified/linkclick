<?php namespace linkclick;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include_once 'functions.php';

function add_menu_settings()
{
    add_submenu_page(
      'options-general.php',
      'LinkClick',
      'LinkClick',
      'edit_pages',
      'linkclick_settings',
        function () {
            include realpath(__DIR__.'/settings_page.php');
        }
    );
}

add_action('admin_menu', __NAMESPACE__.'\add_menu_settings');

add_action( 'init', __NAMESPACE__.'\shall_lock' );
function shall_lock()
{
    if (preg_match('/\/wp-content\/uploads\//m', $_SERVER['REQUEST_URI']) === 1) {
        error_reporting(0);
        $post_id = get_the_ID();
        if ($post_id === false) {
            // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] way: 1\n", 3, __DIR__.'\..\..\debug.dev.log');
            $is_access = is_access_url($_SERVER['REQUEST_URI'], true);
        } else {
            // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] way: 2\n", 3, __DIR__.'\..\..\debug.dev.log');
            $is_access = is_access($post_id, true);
        }
        // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] IS_ACCESS: ".var_export($is_access, true)."\n", 3, __DIR__.'\..\..\debug.dev.log');
        if ($is_access === true) {
            // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] ".print_r(['GRANTED'], true)."\n", 3,  __DIR__.'\..\..\debug.dev.log');
            $realpathname = realpath("{$_SERVER['DOCUMENT_ROOT']}{$_SERVER['REQUEST_URI']}");
            if (!$realpathname) {
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
            $download_tmp_size_min = get_option( '_linkclick_download_tmp_size_min', 0 );
            $download_tmp_status = get_option( '_linkclick_download_tmp_status', 0 );
            $basename = basename($realpathname);
            if ($download_tmp_status == 0 || filesize($realpathname) < $download_tmp_size_min) {
                set_time_limit(0);
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                header('Content-Type: '.$filetype['type']);
                header("Content-Transfer-Encoding: Binary");
                header("Content-Disposition: attachment; filename=\"" . $basename . "\"");
                header("Content-Length: ".filesize($realpathname));
                @ob_clean();
                @flush();
                // @readfile($realpathname);
                @readfile_chunked($realpathname);
            } else {
                try {
                    $download_tmp_dir = get_option( '_linkclick_download_tmp_dir', '' );
                    $download_tmp_url = get_option( '_linkclick_download_tmp_url', '' );
                    $tmp_name_hash = md5($basename);
                    $tmp_filepath;
                    $link_url;
                    $active_links = glob($download_tmp_dir.DIRECTORY_SEPARATOR."{$tmp_name_hash}*");
                    $should_make_new = true;
                    if (sizeof($active_links) > 0) {
                        $time = time();
                        foreach ($active_links as $link_path) {
                            if (preg_match('/\.info$/i', $link_path)) {
                                continue;
                            }
                            $link_stat = lstat($link_path.'.info');
                            if ($time - $link_stat['mtime'] < 7200) {
                                $should_make_new = false;
                                $tmp_filepath = $link_path;
                                $link_basename = basename($link_path);
                                $link_url = $download_tmp_url.'/'.$link_basename;
                                break;
                            }
                        }
                    }
                    if ($should_make_new) {
                        $tmp_name_id = uniqid();
                        $tmp_name_ext = wp_check_filetype( $basename )['ext'];
                        $tmp_name = "{$tmp_name_hash}.{$tmp_name_id}.{$tmp_name_ext}";
                        $tmp_filepath = $download_tmp_dir.DIRECTORY_SEPARATOR.$tmp_name;
                        $link_url = $download_tmp_url.'/'.$tmp_name;
                        if (!(link($realpathname, $tmp_filepath) && realpath($tmp_filepath) !== false)) {
                            throw new \Exception('Failed to link file.', 1);
                        }
                        if (!touch($tmp_filepath.'.info')) {
                            throw new \Exception('Failed to link file.', 2);
                        }
                    }
                    header("Location: {$link_url}", true, 302);
                    exit();
                } catch (\Exception $e) {
                    die("Download failed during preparation. Code: {$e->getCode()}");
                }
            }
            exit();
        } else {
            // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] ".print_r(['DENIED'], true)."\n", 3,  __DIR__.'\..\..\debug.dev.log');
            $url = get_permission_denied_permalink($_SERVER['REQUEST_URI'], null, $is_access);
            // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] ".print_r([$url], true)."\n", 3,  __DIR__.'\..\..\debug.dev.log');
            wp_redirect( $url, 302 );
            // auth_redirect();
            exit();
        }
    }
}

function action_loop_start($wp_query)
{
    // print_r($wp_query->posts);
    $posts = $wp_query->posts;
    $is_singular = is_singular( );
    foreach ($posts as $key => $post) {
        $is_access = is_access($post->ID, $is_singular);
        if ($is_access === true) {
            continue;
        }
        do_action( 'linkclick_permission_denied', $post, $is_access );
    }
}
add_action( 'loop_start', __NAMESPACE__.'\action_loop_start', 10, 1 );

// add_filter('manage_post_posts_columns', __NAMESPACE__.'\add_quick_edit_column');
// function add_quick_edit_column($columns) {
//     $columns['widget_set'] = 'LinkClick';
//     return $columns;
// }

/*add_action('manage_posts_custom_column', __NAMESPACE__.'\add_column_content', 10, 2);
add_action('manage_pages_custom_column', __NAMESPACE__.'\add_column_content', 10, 2);
function add_column_content($column_name, $id) {
    switch ($column_name) {
    case 'widget_set':
        echo 'ok';           
        break;
    }
}

add_action( 'quick_edit_custom_box', __NAMESPACE__.'\add_quick_edit', 'page');
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
function media_column($cols)
{
    $cols["lc_security"] = "LinkClick";
    return $cols;
}

// Display filenames
function media_value($column_name, $id)
{
    if ($column_name != 'lc_security') {
        return;
    }
    global $wpdb;
    global $lc_db_link;
    global $meta_lock_id;
    global $meta_category_id;
    global $meta_date;
    // $meta = wp_get_attachment_metadata($id);
    // print_r($id);
    $info = $wpdb->get_row("SELECT
        p.*,
        ll.*,
        pm.meta_value as lc_lock_id,
        pmcat.meta_value as lc_category_id,
        pmdat.meta_value as lc_date
        FROM {$wpdb->posts} p
        left join {$lc_db_link} ll on ll.PostId = p.ID
        left join {$wpdb->postmeta} pm on pm.post_id = p.ID and pm.meta_key = '{$meta_lock_id}'
        left join {$wpdb->postmeta} pmcat on pmcat.post_id = p.ID and pmcat.meta_key = '{$meta_category_id}'
        left join {$wpdb->postmeta} pmdat on pmdat.post_id = p.ID and pmdat.meta_key = '{$meta_date}'
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
    <p><button class="button button-primary linkclick-btn-secure" type="button" data-post-id="<?php echo $id; ?>" data-linkclick-category-id="<?php echo $info->lc_category_id; ?>" data-linkclick-lock-id="<?php echo $info->lc_lock_id; ?>" data-linkclick-date="<?php echo $info->lc_date; ?>" >Security</button></p>
    <?php
    /* <span class="badge"><?php echo str_replace("~","&nbsp;",str_pad($info->lc_category_id ? $info->lc_category_id : 0,3,"~",STR_PAD_LEFT)); ?></span> */
    //Used a few PHP functions cause 'file' stores local url to file not filename
}

// Register the column as sortable & sort by name
function media_column_sortable($cols)
{
    $cols["filename"] = "name";

    return $cols;
}


// Hook actions to admin_init
function hook_new_media_columns()
{
    add_filter( 'manage_media_columns', __NAMESPACE__.'\media_column' );
    add_action( 'manage_media_custom_column', __NAMESPACE__.'\media_value', 10, 2 );
    add_filter( 'manage_upload_sortable_columns', __NAMESPACE__.'\media_column_sortable' );

    add_filter( 'manage_posts_columns', __NAMESPACE__.'\media_column' );
    add_action( 'manage_posts_custom_column', __NAMESPACE__.'\media_value', 10, 2 );

    add_filter( 'manage_pages_columns', __NAMESPACE__.'\media_column' );
    add_action( 'manage_pages_custom_column', __NAMESPACE__.'\media_value', 10, 2 );
    save_meta();
}
add_action( 'admin_init', __NAMESPACE__.'\hook_new_media_columns' );

add_action('template_redirect', __NAMESPACE__.'\template_redirect');
function template_redirect()
{
}


function add_admin_scripts($hook)
{
    if (in_array($hook, [
        'edit.php',
        'upload.php'
    ])) {
        wp_enqueue_script('linkclick_secure', plugins_url(basename(plugin_dir_path(__FILE__)).'/js/secure.js'), ['jquery','jquery-ui-dialog']);
        wp_enqueue_style( 'style-jquery-ui-dialog', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
    }
}

function add_dialog_1()
{
    add_action( 'in_admin_footer', __NAMESPACE__.'\print_dialog_1_form');
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\add_admin_scripts', 10, 1 );
add_action( 'edit.php', __NAMESPACE__.'\add_dialog_1');
add_action( 'load-edit.php', __NAMESPACE__.'\add_dialog_1');
add_action( 'upload.php', __NAMESPACE__.'\add_dialog_1');
add_action( 'load-upload.php', __NAMESPACE__.'\add_dialog_1');
// add_action( 'post.php', __NAMESPACE__.'\add_dialog_1');
// add_action( 'load-post.php', __NAMESPACE__.'\add_dialog_1');

function setup_boxes()
{
    add_action('add_meta_boxes', __NAMESPACE__.'\add_meta_boxes');
}
add_action('load-post.php', __NAMESPACE__.'\setup_boxes');
add_action('load-post-new.php', __NAMESPACE__.'\setup_boxes');


add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\load_bootstrap' );
function load_bootstrap($hook)
{
    wp_enqueue_style( 'bootstrap_css', plugins_url('lib/bootstrap/css/bootstrap.css', __FILE__) );
}

add_filter( 'post_link', __NAMESPACE__.'\append_query_string', 10, 3 );
function append_query_string($url, $post, $leavename = false)
{
    // error_log("[".date('Y-m-d H:i:s')."][".__FUNCTION__."] ".print_r(func_get_args(), true)."\n", 3,  __DIR__.'\..\..\debug.dev.log');
    if (in_array($post->post_type, ['post','page','attachment'])) {
        $is_access = is_access($post->ID);
        $url = get_permission_denied_permalink($url, $post, $is_access);
    }
    return $url;
}

add_shortcode( 'linkclick-form-code', __NAMESPACE__.'\get_form_code' );