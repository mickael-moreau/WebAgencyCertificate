<?php

/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 *
 * Welcome to our wa-config plugin.
 *
 * This WordPress plugin will :
 * - help you **ensure, modify and test** the **site copyright** workflow.
 * - help you **ensure, modify and test almost anything** directly from Wordpress admin.
 * - use the **codeception** framework to launch editable tests from Wordpress admin.
 * - **speed up end to end testings** by allowing parallel load of duplicated plugin folders.
 * 
 * Solutions included :
 * - **Missions** posts and **Skills** taxonomy.
 * - **Ligh review** managable by automatic script and human checkups.
 * - **Codeception** tool as end to end test tool.
 * - **PhpDocumentor output** as an easy up to date documentation.
 * - **Pdf.js** for quick display of main documentations files.
 * - Results of **Miguel Monwoo R&D** for **parallel programmings** and **advanced integrations**.
 *
 * {@link https://www.web-agency.app/e-commerce/produit/wa-config-codeception-testings Product owner}
 *
 * {@link https://codeception.com/docs/03-AcceptanceTests End to end test documentation}
 *
 * {@link https://github.com/mozilla/pdf.js PDF viewer lib}
 *
 * {@link https://miguel.monwoo.com Miguel Monwoo R&D}
 * 
 * {@link https://wordpress.org/download/releases/ WordPress Releases}
 * 
 * {@link https://wordpress.org/wordpress-5.9.2.zip WordPress 5.9.2 Zip download}
 * 
 * {@link https://wordpress.org/support/wordpress-version/version-5-9-2 WordPress 5.9.2 details}
 * 
 * @since 0.0.1
 * @package waConfig
 * @filesource
 * @author service@monwoo.com
 *
 * @wordpress-plugin
 * Plugin Name:       wa-config by Monwoo
 * Plugin URI:        https://www.web-agency.app/e-commerce/produit/wa-config-codeception-testings/
 * Description:       <strong>End to end user tests</strong> with <strong>Codeception</strong>. Speed up documentations and tests in <strong>parallel</strong>. Enjoy an <strong>editable footer</strong> copyright done by wa-config (by service@monwoo.com)
 * Version:           0.0.1-alpha
 * Author:            Miguel Monwoo for Web-Agency.app
 * Author URI:        https://miguel.monwoo.com
 * License:           Apache-2.0
 * License URI:       https://directory.fsf.org/wiki/License:Apache-2.0
 * Text Domain:       wa-config
 * Domain Path:       /languages
 * Requires at least: 5.9.2
 * Requires PHP:      7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$WA_Config_SHOULD_DEBUG = false;
if (file_exists(__DIR__ . "/src/App.php")) {
    // $WA_Config_SHOULD_DEBUG = true;
    $WA_Config_SHOULD_DEBUG = [true, true, false];
    // $WA_Config_SHOULD_DEBUG = [true, true, true];
    // $WA_Config_SHOULD_DEBUG = false;
    include_once(__DIR__ . "/src/App.php");
} else {
    include_once(__DIR__ . "/App.php");
}

if (defined('WA_Config_SHOULD_DEBUG')) {
    $WA_Config_SHOULD_DEBUG = constant('WA_Config_SHOULD_DEBUG');
    // $WA_Config_SHOULD_DEBUG = false;
}
// $WA_Config_SHOULD_DEBUG = [true, true, false];

if ($WA_Config_SHOULD_DEBUG) {
    // TODO : not working to set those here ?
    // TODO : issue on ES lang ? => eveny if setup in config, will fail under WP in ES ?
    //        If so, need doc on setup in wp-config.php...
    // if (!defined('WP_DEBUG')) {
    //     define( 'WP_DEBUG', true );
    // }
    // if (!defined('WP_DEBUG_LOG')) {
    //     // wp-content/debug.log
    //     define( 'WP_DEBUG_LOG', true );
    // }
}

if (!defined('WA_Config_BASE_CLASS')) {
    define("WA_Config_BASE_CLASS", 'WA\Config\App');
}

if (!defined('WA_Config_INSTANCE_PREFIX')) {
    define("WA_Config_INSTANCE_PREFIX", 'wa-config');
}

$wa_baseClass = WA_Config_BASE_CLASS;
$wa_plugin = new $wa_baseClass(
    site_url(), // WP_SITEURL ?? 
    __FILE__,
    WA_Config_INSTANCE_PREFIX,
    $WA_Config_SHOULD_DEBUG,
);

$wa_plugin->bootstrap();
