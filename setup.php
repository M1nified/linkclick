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

function shall_lock() {
    // tutaj bedzie sprawdzanie czy zalogowany i czy powinien byc
    print_r("shalllock".is_user_logged_in());
}

add_action( 'init', 'linkclick\shall_lock' );

