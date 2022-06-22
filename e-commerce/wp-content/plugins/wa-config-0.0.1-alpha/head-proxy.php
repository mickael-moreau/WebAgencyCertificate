<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo, service@monwoo.com
 * 
 * @since 0.0.1
 * @author service@monwoo.com
 */

$proxyUrl = $GLOBALS["wa-proxy-url"];
$frontHead = trim($GLOBALS["wa-front-head"], "/");
$frontHead = "heads/$frontHead";

if (false === strpos(
    realpath($this->pluginRoot . $frontHead),
    realpath($this->pluginRoot . 'heads')
)) {
    // echo "<notice>404 : head config issue. '$proxyUrl' Not found.</notice>";
    $this->err("head-proxy.php : Head folder not found inside plugin ./heads, wrong path for : '$frontHead'");
    $this->opti_add_url_to_blocked_review_report($this->iId . '-head-proxy-404', $proxyUrl);
    // https://wordpress.stackexchange.com/questions/91900/how-to-force-a-404-on-wordpress
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    // add_filter( 'the_content', function ($c) use ($proxyUrl, $frontHead) { // No content in some 404...
    // add_action( 'get_header', function ($file, $args) use ($proxyUrl, $frontHead) {
    //     $msg = __("Tête de rendue non trouvée.", /*📜*/ 'wa-config'/*📜*/);
    //     $c = "<!-- <notice> '$frontHead' : $msg </notice> -->";
    //     // return $c;
    //     echo $c;
    // }, 1 ); // TODO : why no effect ?
    $msg = __("Tête de rendue non trouvée.", /*📜*/ 'wa-config'/*📜*/);
    $c = "<!-- <notice> '$frontHead' : $msg </notice> -->";
    echo $c;
    // exit;
    include( get_query_template( '404' ) );
    // get_template_part( 404 ); 
    $this->exit(); return;
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
// var_dump( $this->pluginRoot . $proxyBasePath ); exit;
$realPath = $realPath ? $realPath
: realpath( $indexPath );

$proxyBase = rtrim($proxyBasePath, "/");
$htmlPath = $this->pluginRoot . "{$proxyBase}.html";
if (!$realPath) {
    $this->debugVerbose("head-proxy.php : Will try to lookup for $htmlPath in $frontHead");
}
$realPath = $realPath ? $realPath
: realpath( $htmlPath );

$homeUrl = home_url();

if (!$realPath) {
    // TODO : filter or 404 error page ?
    // echo "404 : Missing ressource for '$proxyUrl'";
    $this->warn("head-proxy.php : Missing ressource for '$proxyUrl' in '$frontHead'");
    $this->opti_add_url_to_blocked_review_report($this->iId . '-head-proxy-404', $proxyUrl);
    // http_response_code(404);
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    $msg = __("Ressource frontend manquante.", /*📜*/ 'wa-config'/*📜*/);
    $c = "<!-- <notice> '$frontHead' : $msg </notice> -->";
    echo $c;
    include( get_query_template( '404' ) );
    // get_template_part( 404 ); 
    $this->exit(); return;
}

wp_ob_end_flush_all(); // Flush all, ensuring memory free up from there => NO MORE LAKING MEMORY with this optim only...
$file_parts = pathinfo($realPath);
// exit();
// var_dump($file_parts['extension']); exit;
switch($file_parts['extension'])
{
    case "html":
        // https://stackoverflow.com/questions/4101394/when-to-use-the-javascript-mime-type-application-javascript-instead-of-text-java
        header("Content-Type: text/html;charset=UTF-8"); // application/javascript
    break;
    case "js":
        // https://stackoverflow.com/questions/4101394/when-to-use-the-javascript-mime-type-application-javascript-instead-of-text-java
        header("Content-Type: text/javascript"); // application/javascript
    break;
    case "css":
        header("Content-Type: text/css");
    break;

    case "": // Handle file extension for files ending in '.'
    case NULL: // Handle no file extension
    break;
    default: {
        header("Content-Type: ".mime_content_type($realPath));
    } break;
}

readfile($realPath);
