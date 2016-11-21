<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $db_links;

function create_tables(){
    global $wpdb;
    global $db_links;
    $tabquery = "CREATE TABLE `{$db_links}` (
    `Id` INT NOT NULL AUTO_INCREMENT,
    `Ticket` NVARCHAR(256) NULL DEFAULT NULL,
    `Target` NVARCHAR(1000) NULL DEFAULT NULL,
    `CategoryId` INT NULL DEFAULT NULL,
    `SubCategoryId` INT NULL DEFAULT NULL,
    `Name` NVARCHAR(256) NULL DEFAULT NULL,
    `JustTrack` int(11) DEFAULT '0',
    PRIMARY KEY (`Id`),
    UNIQUE INDEX `Id_UNIQUE` (`Id` ASC));";
    $wpdb->query($tabquery);
}
function install(){
    create_tables();
}
register_activation_hook(__DIR__.'/linkclick.php','linkclick\install');