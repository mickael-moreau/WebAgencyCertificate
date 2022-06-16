<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo, service@monwoo.com
 * 
 * @since 0.0.1
 * @author service@monwoo.com
 */

$proxyUrl = $GLOBALS["wa-proxy-url"];
$frontHead = $GLOBALS["wa-front-head"];
$proxyBaseUrl = explode('#', $proxyUrl)[0];
$proxyBasePath = explode('?', $proxyBaseUrl)[0];
$proxyBasePath = trim($proxyBasePath, "/");
$proxyBasePath = "$frontHead/$proxyBasePath";

$realPath = realpath( $this->pluginRoot . $proxyBasePath);
if (is_dir($realPath)) {
    $realPath = null; // We do not target dir, will check index.html inside this dir on next call if null
}
if (strlen($proxyBasePath)) {
    $proxyBasePath .= '/';
}
// var_dump( $this->pluginRoot . $proxyBasePath ); exit;
$realPath = $realPath ? $realPath
: realpath( $this->pluginRoot . "{$proxyBasePath}index.html");

if (!$realPath) {
    echo "404 : Missing ressource for $proxyUrl";
    http_response_code(404);
    wp_die(); return;
}

wp_ob_end_flush_all(); // Flush all, ensuring memory free up from there => NO MORE LAKING MEMORY with this optim only...
$file_parts = pathinfo($realPath);

// var_dump($file_parts['extension']); exit;
switch($file_parts['extension'])
{
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
