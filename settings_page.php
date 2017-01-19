<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Tutaj znajduje sie tresc wyswietlana w ustawieniach modulu
 **/
?>

<?php 
include_once realpath(dirname(__FILE__).'/variables.php');
// include_once realpath(dirname(__FILE__).'/functions.php');
// echo realpath(dirname(__FILE__).'/functions.php');
include_once realpath(dirname(__FILE__).'/functions.php');

global $wpdb;
global $db_links;
global $lc_db_link;
global $lc_db_log;
global $lc_db_category;

$this_page_url = home_url(add_query_arg( NULL, NULL ));

?>

<?php

    if(isset($_POST['action']) && $_POST['action'] == 'register_uploaded_from_dir' && isset($_POST['location'])){
        $results = register_uploaded_from_dir($_POST['location']);
        $count = sizeof($results);
        // print_r($results);
        echo "<div class=\"alert alert-info\"><p>Processed <b>{$count}</b> files.</p><textarea style=\"width:100%;min-height:250px;\">";
        foreach($results as $result) {
            vprintf("%b\t%s\t%s\t%s\n",$result);
        }
        echo "</textarea></div>";
    }

?>

<?php // View ?>

<h1>LinkClick</h1>
<section>
    <h2> Register uploaded files</h2>
    <form action="<?php echo $this_page_url; ?>" method="post">
    <p><input type="hidden" name="action" value="register_uploaded_from_dir"></p>
    <p><input type="text" name="location" placeholder="Directory relative to wp_root" required></p>
    <p><input type="submit"></p>
    </form>
</section>

<?php


// ==================================================================

echo "<h1 style=\"color:red;\">TA STRONA NIE DZIALA</h1>";

// v 1.0.1
// --------------------------------------------------

// ----- Add --------
if(isset($_POST['mode']) && $_POST['mode'] === 'add_link'){
    print_r($_POST);
    $insert_array = array(
        'Ticket' => get_new_ticket(),
        'Target' => isset($_POST['Url']) && $_POST['Url'] ? $_POST['Url'] : null,
        'CategoryID' => isset($_POST['CategoryID']) && $_POST['CategoryID'] ? strval($_POST['CategoryID']) : null,
        'Name' => isset($_POST['Name']) && $_POST['Name'] ? $_POST['Name'] : null,
        'Secure' => isset($_POST['Secure']) ? $_POST['Secure'] : null,
        'PostId' => isset($_POST['PostId']) && $_POST['PostId'] ? $_POST['PostId'] : null
    );
    if(!$wpdb->insert(
        $db_links,
        $insert_array
    )){
        print("Added:");
        print_r($insert_array);
    }
}elseif(isset($_POST['mode']) && $_POST['mode'] === 'add_category'){
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
}elseif(isset($_POST['mode']) && $_POST['mode'] === 'update_media'){
    try{
        $update_dir = realpath("{$_SERVER['DOCUMENT_ROOT']}/media-upload/");
        if(!$update_dir){
            throw new \Exception("[ERROR] media-update directory not present");
        }
        $Directory = new \RecursiveDirectoryIterator($update_dir);
        $Iterator = new \RecursiveIteratorIterator($Directory);
        // $Regex = new \RegexIterator($Iterator, '/.*/i', \RecursiveRegexIterator::GET_MATCH);
        // print_r($Iterator);
        foreach ($Iterator as $key => $value) {
            $filename = $value->getFilename();
            if(in_array($filename,['.','..'])){
                continue;
            }
            $filepathname = $value->getPathname();
            // print_r($filepathname);
            $filetype = wp_check_filetype( basename( $filepathname ), null );
            // print_r($filetype);
            $wp_upload_dir = wp_upload_dir();
            // print_r($wp_upload_dir);
            $parent_post_id = 0;

            $uploadedfilename = basename( $filename );
            $uploadedfilepath = get_new_file_name($wp_upload_dir['path'] . '/' . $uploadedfilename);
            $uploadedfilename = basename( $uploadedfilepath );
            if(!copy($filepathname,$uploadedfilepath)){
                throw new \Exception("[ERROR] Failed to copy file.");
            }
            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . $uploadedfilename, 
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', $uploadedfilename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            // print_r($attach_id);
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $parent_post_id, $attach_id );
            unlink($filepathname);
        }
    }catch(\Exception $e){
        echo $e->getMessage(), "\n";
    }
}


// ----- Saving -----
// print_r($_POST);
if (isset($_POST['Id']))
foreach ($_POST['Id'] as $key => $id) {
    $wpdb->update(
        $db_links,
        [
            'Secure' => (isset($_POST['Secure'][$key]) && $_POST['Secure'][$key] == true) ? 1 : 0
        ],
        [
            'Id' => $id
        ]
    );
}


// ----- View -----

$search_for = (isset($_GET['search_for'])) ? $_GET['search_for'] : '';
$limit_from = (isset($_GET['limit_from']) && is_numeric($_GET['limit_from'])) ? $_GET['limit_from'] : 0;
$limit_count = (isset($_GET['limit_count']) && is_numeric($_GET['limit_count'])) ? $_GET['limit_count'] : 50;
$limit_to = $limit_from + $limit_count;
$select_query = "SELECT l.*,posts.*,
    CASE Target IS NULL WHEN TRUE THEN CONCAT('(',posts.ID,') ',posts.post_title) ELSE l.Target END AS TargetValue,
    cat.MasterCategoryID, cat.Name AS CategoryName
    FROM {$db_links} AS l
    LEFT JOIN
        {$wpdb->posts} AS posts ON posts.ID = l.PostId
    LEFT JOIN
        {$lc_db_category} AS cat ON cat.CategoryID = l.CategoryID
";
if($search_for == ''){
    $rows = $wpdb->get_results("{$select_query}
    ORDER BY `TargetValue` LIMIT {$limit_from},{$limit_to}");
}else{
    $rows = $wpdb->get_results("{$select_query}
    WHERE
        CASE Target IS NULL WHEN TRUE THEN CONCAT('(',posts.ID,') ',posts.post_title) ELSE l.Target END LIKE '%{$search_for}%'
        OR l.Name LIKE '%{$search_for}%'
        OR l.Ticket LIKE '%{$search_for}%'
    ORDER BY `TargetValue`
    LIMIT {$limit_from},{$limit_to}");
}
$limit_from_left = $limit_from-$limit_count-1;
$limit_from_left = $limit_from_left < 0 ? 0 : $limit_from_left;
$base_url = $_SERVER['REQUEST_URI'];
$base_url = preg_replace('/&?limit_count=\d*/','',$base_url);
$base_url = preg_replace('/&?limit_from=\d*/','',$base_url);
// print_r(parse_url($_SERVER['REQUEST_URI']));

// DATA COLLECTING
$categories = get_categories_tree();

?>
<h1>LinkClick</h1>
<h2>Update Media</h2>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" style="display: block;">
    <input type="hidden" name="mode" value="update_media">
    <input type="submit">
</form>
<h2>Add</h2>
<section>
<h3>Add Link</h3>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" style="display: block;">
    <input type="hidden" name="mode" value="add_link">
    <label style="display:block;">Page: <input type="checkbox" name="is_post"></label>
    <label style="display:block;">Name: <input type="text" name="Name"></label>
    <label style="display:block;">Url: <input type="text" name="Url"></label>
    <label style="display:block;">Post ID: <input type="text" name="PostId" class="lc-add-PostId"></label>
    <p>
    <label>Select post:
        <select class="lc-add-list_posts" size="5" style="height:auto;">
            <option value=""></option>
            <?php
                $posts = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->posts}");
                foreach ($posts as $key => $row){
                    print("<option value=\"{$row->ID}\">{$row->post_title}</option>");
                }
            ?>
        </select>
    </label>
    <label><input class="lc-add-filter_posts" type="text" placeholder="Filter posts.."></label>
    </p>
    <p>
    <select name="CategoryID">
        <option value="">No category</option>
        <?php
        foreach ($categories as $key => $category) {
            echo "<option value=\"{$category->CategoryID}\">{$category->DisplayName}</option>";
        }
        ?>
    </select>
    </p>
    <p>
    <label> Secure: <input type="checkbox" name="Secure"></label>
    </p>
    <p><input type="submit" class="button"></p>
    <script>
        document.querySelector(".lc-add-list_posts").addEventListener('change',function(){
            // console.log(this.selectedOptions[0].value)
            document.querySelector(".lc-add-PostId").value = this.selectedOptions[0].value;
        });
        document.querySelector(".lc-add-filter_posts").addEventListener('keyup',function(){
            let searchFor = this.value;
            let searchForRegEx = new RegExp(searchFor,"ig");
            console.log(searchForRegEx)
            document.querySelectorAll('.lc-add-list_posts>option').forEach(function(option){
                // console.log(option.innerHTML)
                if(searchFor == ''){
                    option.style.display = '';
                }else if(!searchForRegEx.test(option.innerHTML)){
                    option.style.display = 'none';
                }else{
                    option.style.display = '';
                }
            });
        });
    </script>
</form>
</section>
<section>
<h3>Add Category</h3>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <input type="hidden" name="mode" value="add_category">
    <input type="text" name="Name" placeholder="Category name..." required>
    <select name="MasterCategoryID">
        <option value="">No master category</option>
        <?php
        foreach ($categories as $key => $category) {
            echo "<option value=\"{$category->CategoryID}\">{$category->DisplayName}</option>";
        }
        ?>
    </select>
    <input type="submit">
</form>
</section>
<section>
<h2>Explore</h2>
<form action="#" method="get" style="display:block; text-align: right;">
    <label>Search: <input type="text" name="search_for" value="<?php echo $search_for; ?>" style="width:20em;"></label>
    <label>Items per page: <input type="number" name="limit_count" value="<?php echo $limit_count; ?>" style="width:7em;"></label>
    <input type="hidden" name="limit_from" value="<?php echo $limit_from; ?>">
    <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
    <input type="submit" class="button">
</form>
<p style="text-align: right;">
<a href="<?php echo $base_url; ?>" class="button">|&lt;</a>
<a href="<?php echo $base_url.'&limit_from='.$limit_from_left.'&limit_count='.$limit_count; ?>" class="button">&lt;&lt;</a>
<a href="<?php echo $base_url.'&limit_from='.($limit_to+1).'&limit_count='.$limit_count; ?>" class="button">>></a>
</p>
<p>&nbsp;</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<input type="hidden" name="mode" value="edit">
<table>
<thead>
<tr>
<th>Id</th>
<th>Ticket</th>
<th>Target</th>
<th>Name</th>
<th>Category</th>
<!--<th>SubCategory</th>-->
<th>Secure</th>
</tr>
</thead>
<tbody>
<?php
foreach ($rows as $key => $row) {
    // try{
    //     // $link = make_link_from_ticket($row->Ticket);
    // }catch(Exception $e){
    //     print_r($e);
    // }
    // $target_value = $row->Target ? $row->Target : "({$row->ID}) {$row->post_title}";
    echo "<tr>";
    echo "<td>{$row->Id}</td>";
    echo "<td><input type=\"text\" value=\"".$row->Ticket."\" readonly></td>";
    echo "<td><input type=\"text\" value=\"{$row->TargetValue}\" readonly></td>";
    echo "<td>{$row->Name}</td>";
    echo "<td>{$row->CategoryName}</td>";
    // echo "<td></td>";
    echo "<td style=\"text-align:center\"><input type=\"checkbox\" name=\"Secure[{$key}]\" ".($row->Secure == 1 ? 'checked' : '').">
    <input type=\"hidden\" name=\"Id[{$key}]\" value=\"{$row->Id}\">
    </td>";
    echo "</tr>";
}
?>
</tbody>
</table>
<p><input type="submit" class="button"></p>
</section>
</form>