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
        print("Added:");
        print_r($insert_array);
    }


    /* 
     * DATA COLLECTING
     */

    $categories = get_categories_tree();

?>

<?php // View ?>

<section>
    <h2> Register uploaded files</h2>
    <form action="<?php echo $this_page_url; ?>" method="post">
    <p><input type="hidden" name="action" value="register_uploaded_from_dir"></p>
    <p><input type="text" name="location" placeholder="Directory relative to wp_root" required></p>
    <p><input type="submit"></p>
    </form>
</section>

<section>
<h3>Add Category</h3>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <input type="hidden" name="action" value="add_category">
    <input type="text" name="Name" placeholder="Category name..." required>
    <select name="MasterCategoryID">
        <option value="">No master category</option><?php
        foreach ($categories as $key => $category) {
            echo "<option value=\"{$category->CategoryID}\">{$category->DisplayName}</option>";
        }
    ?></select>
    <input type="submit">
</form>
</section>

