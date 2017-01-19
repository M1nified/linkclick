<?php namespace linkclick;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// include_once realpath('../../../wp-load.php');
include_once 'variables.php';

 function make_link_from_ticket($ticket){
     return plugins_url(basename(plugin_dir_path(__FILE__))."/view.php?ticket={$ticket}");
 }
function add_new_link($target){
    global $wpdb;
    global $db_links;
    $ticket = get_new_ticket($target);
    $wpdb->insert(
        $db_links,
        [
            'Ticket' => $ticket,
            'Target' => $target
        ]
    );
    return $ticket;
} 
function get_new_ticket($link = null){
    return str_replace('.','_',uniqid($link == null ? md5($link) : '',true)); 
}

function get_categories_tree(){
    global $wpdb;
    global $lc_db_category;
    global $categories;
    global $tree;
    $categories = $wpdb->get_results("SELECT * FROM {$lc_db_category}");
    $tree = [];
    function add_subcategories($masterid,$indentation_level){
        global $categories;
        global $tree;
        global $masterid2;
        $masterid2 = $masterid;
        $kids = array_filter($categories, function($cat){
            global $masterid2;
            return $cat->MasterCategoryID == $masterid2;
        });
        foreach ($kids as $key => $cat) {
            $cat->DisplayName = str_pad("",$indentation_level,"-")." ".$cat->Name;
            $tree[] = $cat;
            add_subcategories($cat->CategoryID,$indentation_level+1);
        }
    }
    // foreach ($categories as $key => $category) {
    //     $categories[$key]->DisplayName = $category->Name;
    // }
    add_subcategories(null,0);
    // print_r($categories);
    // print_r($tree);
    return $tree;
}

function get_new_file_name($filepathname){
    $finfo = pathinfo($filepathname);
    $noext = $finfo['dirname'].DIRECTORY_SEPARATOR.$finfo['filename'];
    $files = glob("{$noext}.*");
    if(sizeof($files) == 0){
        return $filepathname;
    }
    $files = glob("{$noext}-*");
    $last = end($files);
    if(preg_match('/-(\d)\.[^.\s]+/m',$last,$matches)){
        print_r($matches);
        $index = intval($matches[1]) + 1;
        return "{$noext}-{$index}.{$finfo['extension']}";
    }
    return "{$noext}-1.{$finfo['extension']}";
}

function secure_media($post_id){
    // global $wpdb;
    // $wpdb->get_row()
    $file = get_attached_file($post_id);
    print_r($file);
    return secure_file($file);
}
function secure_file($filepath){
    return true;
}
function unsecure_media($post_id){
    $file = get_attached_file($post_id);
    print_r($file);
    return unsecure_file($file);
}
function unsecure_file($filepath){
    return true;
}

function get_post_id_for_url($url){
    // echo $url;
    global $wpdb;
    $post = $wpdb->get_row("SELECT 
            ID FROM {$wpdb->posts}
            WHERE guid 
            LIKE '%{$url}%'");
    if($post === NULL){
        return false;
    }else{
        return $post->ID;
    }
}
function is_access_url($url){
    $post_id = get_post_id_for_url($url);
    return $post_id == false ? true : is_access($post_id);
}
function is_access($post_id){
    global $meta_lock_id;
    $lock_id = get_metadata( 'post', $post_id, $meta_lock_id, true);
    if(!isset($lock_id) || $lock_id == "" || $lock_id === false){
        // not set
        return true;
    }
    // echo $lock_id;
    // Securities
    switch ($lock_id) {
        case 1:
            return true;
            break;
        case 2:
            $user_id = get_current_user_id();
            if($user_id == 0){
                return 2;
            }
            $user_has_license = get_metadata( 'user', $user_id, 'ss_has_serial', true );
            if($user_has_license == true){
                return true;
            }
            return 2;
            break;
        case 3:
            return is_user_logged_in() == 1 ? true : 3;
            break;
        default:

            break;
    }
    // OLD --
    global $wpdb;
    global $lc_db_link;
    $record = $wpdb->get_row("SELECT lcl.Secure FROM {$wpdb->posts} p
    LEFT JOIN {$lc_db_link} lcl ON p.ID = lcl.PostId
    WHERE p.ID = {$post_id}");
    // echo 1;
    // print_r($record);
    if($record === NULL || $record->Secure != true){
        // echo 2;
        return true;
    }else{
        // echo 3;
        return is_user_logged_in() == 1 ? true : false;
    }
}

function log_download_of_path($filepathname){
    // echo 'log_download_of_path';
    global $wpdb;
    global $lc_db_link;
    global $lc_db_log;
    $record = $wpdb->get_row("SELECT * FROM {$wpdb->posts} p
    LEFT JOIN {$lc_db_link} lcl ON p.ID = lcl.PostId
    WHERE p.guid LIKE '%{$filepathname}'");
    // print_r($record);
    $LinkId = $record->Id;
    log_download_of($LinkId);
}
function log_download_of($LinkId){
    // echo 'log_download_of';
    // print_r($LinkId);
    if($LinkId == NULL){
        return;
    }
    global $wpdb;
    global $lc_db_log;
    $current_user = wp_get_current_user();
    $cuid = $current_user->ID;
    $wpdb->insert(
        $lc_db_log,
        [
            'LinkId' => $LinkId,
            'UserId' => $cuid
        ]
    );
}
function print_dialog_1($print_form = true, $post_id = null){
    global $wpdb;
    global $lc_db_category;
    global $lc_db_settings;
    global $meta_lock_id;
    global $meta_category_id;
    $categories = get_categories_tree();
    $locks = $wpdb->get_results("SELECT
        s1.option_value as lock_id, s2.option_value as lock_name
        FROM {$lc_db_settings} s1
        LEFT JOIN {$lc_db_settings} s2 ON s1.option_reference = s2.option_reference AND s2.option_name like 'lock_name'
        WHERE s1.option_name like 'lock_id'
    ");
    if(isset($post_id) && $post_id != null){
        $meta_data = get_post_meta( $post_id);
    }
    ?>
    <div class="" id="linkclick-dialog-1">
    <?php echo $print_form === true ? "<form method=\"post\">" : ""; ?>
        <input type="hidden" name="linkclick-action" value="save">
        <input type="hidden" name="linkclick-post-id" value="<?php echo $post_id != null ? $post_id : ""; ?>" id="linkclick-dialog-1-post-id">
        <p>Category: <select name="linkclick-category-id">
            <option value=""></option>
            <?php
            if(isset($meta_data) && isset($meta_data[$meta_category_id][0])){
                foreach ($categories as $category) {
                    if($meta_data[$meta_category_id][0] == $category->CategoryID){
                        echo "<option value=\"{$category->CategoryID}\" data-parent-id=\"{$category->MasterCategoryID}\" selected>{$category->DisplayName}</option>";
                    }else{
                        echo "<option value=\"{$category->CategoryID}\" data-parent-id=\"{$category->MasterCategoryID}\">{$category->DisplayName}</option>";
                    }
                }
            }else{
                foreach ($categories as $category) {
                    echo "<option value=\"{$category->CategoryID}\" data-parent-id=\"{$category->MasterCategoryID}\">{$category->DisplayName}</option>";
                }
            }
            ?>
        </select></p>
        <p>Lock type: <select name="linkclick-lock-id">
            <option value=""></option>
            <?php
            if(isset($meta_data) && isset($meta_data[$meta_lock_id][0])){
                foreach ($locks as $lock) {
                    if($meta_data[$meta_lock_id][0] == $lock->lock_id){
                        echo "<option value=\"{$lock->lock_id}\" selected>{$lock->lock_name}</option>";
                    }else{
                        echo "<option value=\"{$lock->lock_id}\">{$lock->lock_name}</option>";
                    }
                }
            }else{
                foreach ($locks as $lock) {
                    echo "<option value=\"{$lock->lock_id}\">{$lock->lock_name}</option>";
                }
            }
            ?>

        </select></p>
        <?php echo $print_form === true ? "<p style=\"text-align: right;\"><input type=\"submit\" class=\"button button-primary\"></p>" : ""; ?>
    <?php echo $print_form === true ? "</form>" : ""; ?>
    </div>
<?php
}
function save_meta(){
    // echo basename($_SERVER["SCRIPT_FILENAME"]);
    if(basename($_SERVER["SCRIPT_FILENAME"]) != 'upload.php' && basename($_SERVER["SCRIPT_FILENAME"]) != 'edit.php' && basename($_SERVER["SCRIPT_FILENAME"]) != 'post.php'){
        return;
    }
    global $wpdb;
    global $lc_db_link; 
    if(isset($_POST['linkclick-post-id']) && isset($_POST['linkclick-action'])){
        $post_id = $_POST['linkclick-post-id'];
        $lock_id = $_POST['linkclick-lock-id'];
        $category_id = $_POST['linkclick-category-id'];
        if($_POST['linkclick-action'] === 'save' ) {
            global $meta_lock_id;
            global $meta_category_id;
            update_post_meta( $post_id, $meta_lock_id, $lock_id );
            update_post_meta( $post_id, $meta_category_id, $category_id );
        }
    }
}

function register_uploaded_file($spl_file_info){
    // print_r($spl_file_info);
    $relative_path = str_replace(realpath(get_home_path()),'',$spl_file_info->getPathName());
    $relative_path_slashed = str_replace('\\','/',$relative_path);
    // print_r($relative_path_slashed);
    // echo '<br>';
    global $wpdb;
    $post_id = $wpdb->get_row(
        "SELECT
            `post_id`
        FROM {$wpdb->postmeta}
        WHERE   `meta_value` LIKE '%{$relative_path_slashed}%'
                AND 
                `meta_key` LIKE '_wp_attachment_metadata'
    ");
    if($post_id !== NULL){
        return [false, 'already_registered', $post_id->post_id];
    }
    $filetype = wp_check_filetype( basename( $spl_file_info->getPathName() ), null );
    $post_id = wp_insert_attachment(
        [
            'guid'              => get_home_url( 0, $relative_path_slashed ),
            'post_mime_type'    => $filetype['type'],
            'post_title'        => preg_replace( '/\.[^.]+$/', '', $spl_file_info->getFileName() ),
            'post_content'      => '',
            'post_status'       => 'inherit'
        ],
        $spl_file_info->getFileName(),
        // $relative_path,
        0 
    );
    // echo $post_id;
    $upload_dir = wp_upload_dir();
    // print_r($upload_dir);
    $relative_to_wpuploads_path = str_replace(str_replace('\\','/',$upload_dir['basedir']),'',str_replace('\\','/',$spl_file_info->getPathName()));
    // echo $relative_to_wpuploads_path;
    if($attachment_data = wp_generate_attachment_metadata( $post_id, $spl_file_info->getFileName() )){
        // print_r($attachment_data);
        $update_result = wp_update_attachment_metadata( $post_id, $attachment_data );
        // print_r($update_result);
    }else{
        $meta_dir = array(
            'post_id' => $post_id,
            'meta_key' => '_wp_attachment_metadata',
            'meta_value' => $relative_path_slashed
        );
        $attach_id = $wpdb->insert(
            $wpdb->postmeta,
            $meta_dir
        );
    }
    // echo '<br>';
    return [true, 'registered', $post_id];
}
function register_uploaded_from_dir($location_from_root){
    $abs_dir = realpath(get_home_path().$location_from_root);
    // print_r($abs_dir);
    $DirectoryI = new \RecursiveDirectoryIterator($abs_dir,\FilesystemIterator::SKIP_DOTS);
    $IteratorI = new \RecursiveIteratorIterator($DirectoryI);
    $results = [];
    foreach ($IteratorI as $file) {
        $result = register_uploaded_file($file);
        array_push($result, $file);
        array_push($results, $result);
    }
    return $results;
}