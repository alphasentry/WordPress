<?php
/*
Plugin Name: AlphaSentry
Plugin URI: https://www.alphasentry.com
Description: Integrates AlphaSentry anti-spam and anti-fraud API with wordpress
Version: 1.0
Author: AlphaSentry.com
Email: service@alphasentry.com
Author URI: https://www.alphasentry.com
*/

// this is the 'driver' file that instantiates the objects and registers every hook

define('ALLOW_INCLUDE', true);

require_once('wordpress-alphasentry.php');

/**
 * Process AJAX Request. This function is hooked to the alphasentry_ajax wordpress
 *
 */
function alphasentry_process_ajax()
{
	$myAlphaSentry = new wpAlphaSentry('alphasentry_options');
	$myAlphaSentry->process_ajax();
	die();
}

$alphaSentry = new wpAlphaSentry('alphasentry_options');
?>
