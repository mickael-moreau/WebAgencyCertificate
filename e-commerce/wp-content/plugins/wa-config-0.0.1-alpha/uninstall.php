<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 *
 * The wa-config plugin un-install entry point
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link              https://www.web-agency.app/e-commerce/produit/wa-config-codeception-testings/
 * @since             0.0.1
 * @package
 * @filesource
 * @author service@monwoo.com
 **/

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// $plugin->uninstall();
