<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo, service@monwoo.com
 * 
 * This file do the proxy rendering of selected front-head if enabled.
 * 
 * @link https://miguel.monwoo.com Miguel Monwoo R&D
 * @link https://www.monwoo.com/don Author Donate link
 * 
 * @since 0.0.1
 * @author service@monwoo.com
 */

// $proxyUrl = sanitize_url($GLOBALS["wa-proxy-url"]); => wrong with relative url path...
$proxyUrl = sanitize_text_field($GLOBALS["wa-proxy-url"]);
// $frontHead = trim(sanitize_file_name($GLOBALS["wa-front-head"]), "/"); // WRONG : add _ to extensions for folder : cv-regnoult-axel.web.app 
$frontHead = trim(sanitize_text_field($GLOBALS["wa-front-head"]), "/");
$frontHead = "heads/$frontHead";

$force404 = function($msg) use ($frontHead) {
    // https://wordpress.stackexchange.com/questions/91900/how-to-force-a-404-on-wordpress
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    // $c = "<!-- <notice> '$frontHead' : $msg </notice> -->";
    // echo wp_kses($c, ['post', '<!--', '<!---->', '-->']); // wp_kses_post($c);
    echo "<!-- <notice> '" . esc_attr($frontHead) . "' : "
    . wp_kses_post($msg) . " </notice> -->\n";
    include( get_query_template( '404' ) );
    $this->exit(); return;
};

if (0 !== strpos(
    realpath($this->pluginRoot . $frontHead),
    realpath($this->pluginRoot . 'heads')
)) {
    $this->err("head-proxy.php : Head folder not found inside "
    . "plugin ./heads, wrong path for : '$frontHead'");
    $this->opti_add_url_to_blocked_review_report($this->iId
    . '-head-proxy-404', $proxyUrl);

    $force404(__("Tête de rendue non trouvée.", 'monwoo-web-agency-config'/** 📜*/));
    return;
}

$proxyBaseUrl = explode('#', $proxyUrl)[0];
$proxyBasePath = explode('?', $proxyBaseUrl)[0];
$proxyBasePath = trim($proxyBasePath, "/");
$proxyBasePath = "$frontHead/$proxyBasePath";
$proxyBasePath = trim($proxyBasePath, "/");

$realPath = realpath( $this->pluginRoot . $proxyBasePath);

if (is_dir($realPath)) {
    $this->debugVerbose("head-proxy.php : Directory access attempt detected for $proxyUrl");
    $realPath = null; // We do not target dir, will check index.html inside this dir on next call if null
}
if (strlen($proxyBasePath)) {
    $proxyBasePath .= '/';
}
$indexPath = $this->pluginRoot . "{$proxyBasePath}index.html";
if (!$realPath) {
    $this->debugVerbose("head-proxy.php : Will try to lookup for $indexPath in $frontHead");
}

$realPath = $realPath ? $realPath
: realpath( $indexPath );

$proxyBase = rtrim($proxyBasePath, "/");
$htmlPath = $this->pluginRoot . "{$proxyBase}.html";
if (!$realPath) {
    $this->debugVerbose("head-proxy.php : Will try to lookup for $htmlPath in $frontHead");
}
$realPath = $realPath ? $realPath
: realpath( $htmlPath );

if (!$realPath) {
    $this->warn("head-proxy.php : Missing ressource for '$proxyUrl' in '$frontHead'");
    $this->opti_add_url_to_blocked_review_report($this->iId . '-head-proxy-404', $proxyUrl);
    $force404(__("Ressource frontend manquante.", 'monwoo-web-agency-config'/** 📜*/));
    return;
}

wp_ob_end_flush_all(); // Flush all, ensuring memory free up from there => NO MORE LAKING MEMORY thanks to this optim...
$file_parts = pathinfo($realPath);

switch($file_parts['extension'])
{
    case "html": {
        // https://stackoverflow.com/questions/4101394/when-to-use-the-javascript-mime-type-application-javascript-instead-of-text-java
        header("Content-Type: text/html;charset=UTF-8"); // application/javascript
    } break;
    case "js": {
        // https://stackoverflow.com/questions/4101394/when-to-use-the-javascript-mime-type-application-javascript-instead-of-text-java
        header("Content-Type: text/javascript"); // application/javascript
    } break;
    case "css": {
        header("Content-Type: text/css");
    } break;

    case "": // Handle file extension for files ending in '.'
    case NULL: // Handle no file extension
    break;
    default: {
        header("Content-Type: ".mime_content_type($realPath));
    } break;
}

readfile($realPath);
