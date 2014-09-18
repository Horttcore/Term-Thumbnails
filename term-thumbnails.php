<?php
/*
Plugin Name: Term Thumbnails
Plugin URI: http://horttcore.de
Description: Post Thumbnails for Terms
Version: 1.0.2
Author: Ralf Hortt
Author URI: http://horttcore.de
License: GPL2
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}



// Include template tags class
include( 'inc/template-tags.php' );



// Include admin class
if ( is_admin() )
	include( 'classes/class.term-thumbnails-admin.php' );
