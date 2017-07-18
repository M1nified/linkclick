<?php namespace linkclick;

/**
 * Plugin Name: LinkClick
 * Description:
 * Version: 3.0.0
 * Author: M1nified
 */
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

 include_once(realpath(__DIR__.'/variables.php'));

 include_once(realpath(__DIR__.'/Class/DB.php'));
 include_once(realpath(__DIR__.'/Class/DialogPerms.php'));
 include_once(realpath(__DIR__.'/Class/PostMetaFields.php'));

 include_once(realpath(__DIR__.'/elements/meta-box-1.php'));

 include_once(realpath(__DIR__.'/install.php'));

 include_once(realpath(__DIR__.'/setup.php'));
