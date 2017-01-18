<?php namespace linkclick;
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $db_links;

function create_tables(){
    global $wpdb;
    global $db_links;
    global $lc_db_link;
    global $lc_db_log;
    global $lc_db_category;
    global $lc_db_settings;
    $tabquery = "
    
        CREATE TABLE {$lc_db_link} (
        `Id` int(11) NOT NULL AUTO_INCREMENT,
        `Ticket` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
        `Target` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
        `CategoryID` int(11) DEFAULT NULL,
        `SubCategoryId` int(11) DEFAULT NULL,
        `Name` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
        `Secure` int(11) DEFAULT '0',
        `PostId` bigint(20) DEFAULT NULL,
        PRIMARY KEY (`Id`),
        UNIQUE KEY `Id_UNIQUE` (`Id`),
        UNIQUE KEY `post_id_UNIQUE` (`PostId`)
        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

        CREATE TABLE {$lc_db_category} (
        `CategoryID` int(11) NOT NULL AUTO_INCREMENT,
        `MasterCategoryID` int(11) DEFAULT NULL,
        `Name` varchar(256) COLLATE utf8_bin DEFAULT NULL,
        PRIMARY KEY (`CategoryID`),
        UNIQUE KEY `CategoryID_UNIQUE` (`CategoryID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='			';

        CREATE TABLE {$lc_db_log} (
        `LogID` int(11) NOT NULL,
        `LinkID` int(11) DEFAULT NULL,
        `UserID` bigint(20) DEFAULT NULL,
        `Date` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`LogID`),
        UNIQUE KEY `LogID_UNIQUE` (`LogID`),
        KEY `link_idx` (`LinkID`),
        CONSTRAINT `link` FOREIGN KEY (`LinkID`) REFERENCES `{$lc_db_link}` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

        CREATE TABLE `{$lc_db_settings}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `option_name` varchar(250) COLLATE utf8_bin DEFAULT NULL,
        `option_value` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
        `option_reference` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id_UNIQUE` (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


    ";
    $wpdb->query($tabquery);
}
function install(){
    create_tables();
}
register_activation_hook(__DIR__.'/linkclick.php',__NAMESPACE__.'\install');