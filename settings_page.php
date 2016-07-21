<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Tutaj znajduje sie tresc wyswietlana w ustawieniach modulu
 **/
?>

<?php 
include_once realpath(dirname(__FILE__).'/variables.php');
// echo realpath(dirname(__FILE__).'/functions.php');
// include_once realpath(dirname(__FILE__).'/functions.php');

global $wpdb;
global $db_links;

// ----- Saving -----
// print_r($_POST);
foreach ($_POST['Id'] as $key => $id) {
    $wpdb->update(
        $db_links,
        [
            'JustTrack' => (isset($_POST['JustTrack'][$key]) && $_POST['JustTrack'][$key] == true) ? 1 : 0
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
if($search_for == ''){
    $rows = $wpdb->get_results("SELECT * FROM {$db_links} ORDER BY `Target` LIMIT {$limit_from},{$limit_to}");
}else{
    $rows = $wpdb->get_results("SELECT * FROM {$db_links} WHERE `Target` LIKE '%{$search_for}%' OR `Name` LIKE '%{$search_for}%' ORDER BY `Target` LIMIT {$limit_from},{$limit_to}");
}
$limit_from_left = $limit_from-$limit_count-1;
$limit_from_left = $limit_from_left < 0 ? 0 : $limit_from_left;
$base_url = $_SERVER['REQUEST_URI'];
$base_url = preg_replace('/&?limit_count=\d*/','',$base_url);
$base_url = preg_replace('/&?limit_from=\d*/','',$base_url);
// print_r(parse_url($_SERVER['REQUEST_URI']));
?>
<h1>LinkClick</h1>
<form action="#" method="get" style="display:block; text-align: right;">
    <label>Search: <input type="text" name="search_for" value="<?php echo $search_for; ?>" style="width:20em;"></label>
    <label>Items per page: <input type="number" name="limit_count" value="<?php echo $limit_count; ?>" style="width:7em;"></label>
    <input type="hidden" name="limit_from" value="<?php echo $limit_from; ?>">
    <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
    <input type="submit" class="button">
</form>
<p style="text-align: right;">
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
<th>SubCategory</th>
<th>JustTrack</th>
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
    echo "<tr>";
    echo "<td>{$row->Id}</td>";
    echo "<td><input type=\"text\" value=\"".$row->Ticket."\" readonly disabled></td>";
    echo "<td><input type=\"text\" value=\"{$row->Target}\" readonly disabled></td>";
    echo "<td>{$row->Name}</td>";
    echo "<td></td>";
    echo "<td></td>";
    echo "<td style=\"text-align:center\"><input type=\"checkbox\" name=\"JustTrack[{$key}]\" ".($row->JustTrack == 1 ? 'checked' : '').">
    <input type=\"hidden\" name=\"Id[{$key}]\" value=\"{$row->Id}\">
    </td>";
    echo "</tr>";
}
?>
</tbody>
</table>
<p><input type="submit" class="button"></p>
</form>