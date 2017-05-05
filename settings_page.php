<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Tutaj znajduje sie tresc wyswietlana w ustawieniach modulu
 **/
?>

<?php 
include_once realpath(dirname(__FILE__).'/variables.php');
include_once realpath(dirname(__FILE__).'/functions.php');

global $wpdb;
global $db_links;
global $lc_db_link;
global $lc_db_log;
global $lc_db_category;

$this_page_url = home_url(add_query_arg( NULL, NULL ));

?>

<h1>LinkClick</h1>

<?php

    if(isset($_POST['action']) && $_POST['action'] == 'register_uploaded_from_dir' && isset($_POST['location'])){
        $results = register_uploaded_from_dir($_POST['location']);
        $count = sizeof($results);
        // print_r($results);
        echo "<div class=\"alert alert-info\">Processed <b>{$count}</b> files.</div><div class=\"alert alert-info\"><textarea style=\"width:100%;min-height:250px;white-space:pre;\" readonly>";
        foreach($results as $result) {
            vprintf("%b\t%s\t%s\t%s\n",$result);
        }
        echo "</textarea></div>";
    }elseif(isset($_POST['action']) && $_POST['action'] === 'add_category'){
        $insert_array = array(
            'MasterCategoryID' => isset($_POST['MasterCategoryID']) && $_POST['MasterCategoryID'] ? $_POST['MasterCategoryID'] : null,
            'Name' => isset($_POST['Name']) && $_POST['Name'] ? $_POST['Name'] : null
        );
        if(!$wpdb->insert(
            $lc_db_category,
            $insert_array
        )){
            echo '<pre>';
            print("Added:");
            print_r($insert_array);
            echo '</pre>';
        }
    }elseif(isset($_POST['action']) && $_POST['action'] === 'set_download_settings'){
        if(array_key_exists('tmp_on', $_POST) && $_POST['tmp_on'] === 'on')
        {
            update_option( '_linkclick_download_tmp_status', 1);
            update_option( '_linkclick_download_tmp_size_min', $_POST['tmp_size_min'] );
            update_option( '_linkclick_download_tmp_dir', $_POST['tmp_dir'] );
            update_option( '_linkclick_download_tmp_url', $_POST['tmp_url'] );
        }
        else
        {
            delete_option( '_linkclick_download_tmp_status' );
            // delete_option( '_linkclick_download_tmp_size_min' );
        }
    }elseif(isset($_POST['action']) && $_POST['action'] === 'set_code_settings'){
        if(array_key_exists('form_page_id', $_POST))
        {
            update_option( '_linkclick_code_form_page_id', $_POST['form_page_id']);
        }
        else
        {
            delete_option( '_linkclick_code_form_page_id' );
        }
    }


    /* 
     * DATA COLLECTING
     */

    $categories = get_categories_tree();
    $download_tmp_status = get_option( '_linkclick_download_tmp_status', 0 );
    $download_tmp_size_min = get_option( '_linkclick_download_tmp_size_min', 0 );
    $download_tmp_dir = get_option( '_linkclick_download_tmp_dir', '' );
    $download_tmp_url = get_option( '_linkclick_download_tmp_url', '' );
    $code_form_page_id = get_option( '_linkclick_code_form_page_id', null);

?>

<?php // View ?>

<section>
    <h2> Register uploaded files</h2>
    <form action="<?php echo $this_page_url; ?>" method="post">
    <p><input type="hidden" name="action" value="register_uploaded_from_dir"></p>
    <p><input type="text" name="location" placeholder="Directory relative to wp_root" title="Directory relative to wp_root" required></p>
    <p><input type="submit" class="button"></p>
    </form>
</section>

<section>
    <h2>Add Category</h2>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="hidden" name="action" value="add_category">
        <input type="text" name="Name" placeholder="New category name..." required>
        <select name="MasterCategoryID">
            <option value="">No master category</option><?php
            foreach ($categories as $key => $category) {
                echo "<option value=\"{$category->CategoryID}\">{$category->DisplayName}</option>";
            }
        ?></select>
        <input type="submit" class="button">
    </form>
</section>

<section>
    <h2>Download settings</h2>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="hidden" name="action" value="set_download_settings">
        <p><label><input type="checkbox" name="tmp_on" <?php if($download_tmp_status == 1) echo 'checked'; ?>> Create temporary files for downloads over </label>
        <label><input type="number" name="tmp_size_min" value="<?php echo $download_tmp_size_min; ?>"> bytes.</label></p>
        <p><label>Tmp files dir: <input type="text" name="tmp_dir" value="<?php echo $download_tmp_dir; ?>" placeholder="/root/server/httpd/tmp"></label></p>
        <p><label>Tmp files url: <input type="text" name="tmp_url" value="<?php echo $download_tmp_url; ?>" placeholder="http://example.com/tmp"></label></p>
        <input type="submit" class="button">
    </form>
</section>

<section>
    <h2>Code settings</h2>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="hidden" name="action" value="set_code_settings">
        <p><label>Code form page id: <input type="text" name="form_page_id" value="<?php echo $code_form_page_id; ?>" placeholder="12345"></label></p>
        <p>Shortcode: <code>[linkclick-form-code]</code></p>
        <input type="submit" class="button">
    </form>
</section>
