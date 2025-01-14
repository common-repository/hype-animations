<?php
/*
Plugin Name: Hype Animations
Description: Insert your Hype animations
Author: <a href="http://www.eralion.com" target="_blank">ERALION.com</a>
Text Domain: hype-animations
Domain Path: /languages
Version: 1.5
*/
#---------------------------------------------------------------------------#
add_action( 'plugins_loaded', 'hypeanimations_init_lang' );
function hypeanimations_init_lang() {
	load_plugin_textdomain( 'hype-animations', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
include('includes/variables.php');
include('includes/init.php');
include('includes/functions.php');
include('includes/adminpanel.php');
include('includes/shortcode.php');
include('includes/iframe.php');
include('includes/tinymcetool.php');
?>
