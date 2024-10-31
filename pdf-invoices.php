<?php
/**
* Plugin Name: PDF Invoices For Gravity Forms
* Plugin URI: https://pluginscafe.com
* Description: Automatically generate PDF invoices and attach them to every form submission in Gravity Forms.
* Author: KaisarAhmmed
* Author URI: https://themecafe.net
* Version: 1.0.0
* Text Domain: pdf-invoices-for-gravity-forms
* Domain Path: /languages/
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PCAFE_GFPI_VERSION_FREE' ) ) {
	define( 'PCAFE_GFPI_VERSION_FREE', '1.0.0' );
}

if( ! class_exists('GFForms') || ! pcafe_gfpi_meets_requirements() ) {
	add_action( 'admin_notices','pcafe_gfpi_notice_for_missing_requirements' );
	return;
}

function pcafe_gfpi_localization_setup() {
	load_plugin_textdomain('pdf-invoices-for-gravity-forms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'pcafe_gfpi_localization_setup');

function PCAFE_GFPI_Bootstrap() {
	if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
		return;
	}

	require_once 'class-pcafe-gfpi-pdf-invoices-free.php';
	require_once 'includes/class-pcafe-gfpi-create-invoice.php';
	require_once 'includes/class-pcafe-gfpi-helpers.php';

	GFAddOn::register( 'PCAFE_GFPI_Pdf_Invoices_Free' );
}
add_action( 'gform_loaded', 'PCAFE_GFPI_Bootstrap', 5 );

function pcafe_gfpi_meets_requirements() {
	global $wp_version;

	return (
		version_compare( PHP_VERSION, '7.3', '>=' ) &&
		version_compare( $wp_version, '5.5', '>=' )
	);
}

function pcafe_gfpi_notice_for_missing_requirements() {
	printf(
		'<div class="notice notice-error"><p>%1$s</p></div>',
		esc_html__( 'The "PDF Invoices For Gravity Forms" requires Gravity Forms to be installed and activated.', 'pdf-invoices-for-gravity-forms' )
	);
}