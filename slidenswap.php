<?php
/*
 * Plugin Name: SlideNSwap
 * Version: 1.0
 * Plugin URI: http://github.com/AZdv/slidenswap
 * Description: WP Plugin for the SlideNSwap jQuery plugin
 * Author: Asaf Zamir
 * Author URI: http://github.com/AZdv/slidenswap
 * Requires at least: 3.0
 * Tested up to: 4.0
 *
 * Text Domain: slidenswap
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Asaf Zamir
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-slidenswap.php' );

/**
 * Returns the main instance of slidenswap to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object slidenswap
 */
function slidenswap () {
	$instance = slidenswap::instance( __FILE__, '1.0.0' );
	return $instance;
}

slidenswap();
