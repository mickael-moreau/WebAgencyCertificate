<?php

/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 *
 * Welcome to our wa-config plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       WA-Config Monwoo
 * Plugin URI:        https://moonkiosk.monwoo.com/en/missions/wa-config-monwoo_en
 * Description:       <strong>End to end user tests</strong> with <strong>Codeception</strong>. Speed up documentations and tests in <strong>parallel</strong>. Enjoy an <strong>editable footer</strong> copyright done by wa-config (by service@monwoo.com)
 * Version:           0.0.1-alpha
 * Author:            Miguel Monwoo
 * Author URI:        https://miguel.monwoo.com
 * Donate link:       https://www.monwoo.com/don
 * License:           Apache-2.0
 * License URI:       https://directory.fsf.org/wiki/License:Apache-2.0
 * Text Domain:       wa-config
 * Domain Path:       /languages
 * Requires at least: 5.9.2
 * Requires PHP:      7.4
 * 
 * Wa-config is a Web Agency production tool.
 * 
 * Build from researches and developpements done by Miguel Monwoo from 2011 to 2022.
 *
 * It's a Web Agency (WA) plugin ready
 * to run **parrallel programming**
 * with **advanced debugs** and **end to end testing** tools.
 * 
 * It come with :
 * - **Skills and missions** concepts ready to use as taxonomy and custom post type
 * - **Internaionalisation** and **WooCommerce** integration
 * - A **securised REST API** to deploy custom static front head
 * - A **commonJS deploy script** to easyliy deploy your static frontend 
 * - A **review system** for all team members using this plugin
 * - **Codeception** as end to end test tool
 * - **PhpDocumentor output** as an easy up to date documentation
 * - **Pdf.js** for quick display of main documentation files
 * - results of **Miguel Monwoo R&D** for **parallel programmings** and **advanced integrations**
 * 
 * WA-Config Monwoo will help with **Web Agency jobs** like :
 *  - Posting past or current **missions managable by skills**.
 *  - **Internationalising** content and WooCommerce products (need Polylang plugin).
 *  - Billings with **order prefix** for WooCommerce.
 *  - Ensuring human and automatic **plugable reviews**.
 *  - Deploying custom **static frontend** like Angular/Svelte/Vue.js/JS/HTML/etc....
 *  - Launching custom authenticated **End to End user tests**
 *    under production server with existing user accounts (Codeception).
 *  - **Backuping** and **optimizing** the website 
 *    (mandatory to ensure safe tests launch under production data).
 *  - Extending this plugin to **improve those base features**.
 *  - Runing same **instance** of this plugin **in parallele**.
 *
 * @link    https://miguel.monwoo.com Miguel Monwoo R&D
 * @link    https://www.monwoo.com/don Author Donate link
 * @since   0.0.1
 * @package
 * @author  service@monwoo.com
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$WA_Config_SHOULD_DEBUG = false;
if (file_exists(__DIR__ . "/src/App.src.php")) {
    $WA_Config_SHOULD_DEBUG = [true, false, false];
    // $WA_Config_SHOULD_DEBUG = [true, true, false];
    // $WA_Config_SHOULD_DEBUG = [true, true, true];
    // $WA_Config_SHOULD_DEBUG = false;
    include_once(__DIR__ . "/src/App.src.php");
} else {
    include_once(__DIR__ . "/App.php");
}

if (defined('WA_Config_SHOULD_DEBUG')) {
    $WA_Config_SHOULD_DEBUG = constant('WA_Config_SHOULD_DEBUG');
    // $WA_Config_SHOULD_DEBUG = false;
}
// $WA_Config_SHOULD_DEBUG = [true, true, false];
// $WA_Config_SHOULD_DEBUG = [true, true, true];
// $WA_Config_SHOULD_DEBUG = true;

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
    site_url(),
    __FILE__,
    WA_Config_INSTANCE_PREFIX,
    $WA_Config_SHOULD_DEBUG,
);

$wa_plugin->bootstrap();

// Quick test parallele load of same plugin, un-comment below :
// Be CARFUL : not same as plugin duplication 
// (source folders are same one with parallel test below)
// BUT it's a start to see parallel load bugs etc...
/*
$wa_plugin = new $wa_baseClass(
    site_url(),
    __FILE__,
    'wa-config-bis',
    $WA_Config_SHOULD_DEBUG,
);

$wa_plugin->bootstrap();
*/