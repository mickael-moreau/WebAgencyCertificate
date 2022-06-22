<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 *
 * This test will check if '.htaccess' redirect is activated
 *
 * @link https://moonkiosk.monwoo.com/missions/wa-config-par-monwoo wa-config by Monwoo
 * @since 0.0.1
 * @package
 * @filesource
 * @author service@monwoo.com
 **/

namespace WA\Config\ExtTest {

    if (!defined('WA_CONFIG_IS_WP')) {
        global $wp_version;
        // global $wpdsdfsfd;
        // var_dump($wp_version);
        // var_dump(isset($wpdsdfsfd));
        // var_dump(isset($wp_version));
        // define('WA_CONFIG_IS_WP', defined(WPINC)); => WPINC will NOT BE DEFINED on file require from wp files itself with this kind of test...
        define('WA_CONFIG_IS_WP', isset($wp_version));
        // var_dump(WA_CONFIG_IS_WP);
        // var_dump(array_keys($GLOBALS));
        // throw new \Exception("Debug include calls in progress");
        // exit();
    }

    /*
    * Use EXT_TEST_htaccessIsEnabled::check() to test if '.htaccess' is enabled
    *
    * @since 0.0.1
    * @author service@monwoo.com
    */
    class EXT_TEST_htaccessIsEnabled {
        // Inspired from
        // https://localcoder.org/php-scriptfunction-to-check-if-htaccess-is-allowed-on-server
        /**
         * List of possible errors encountered by the test check, reset on each check
         */
        static public $errors = [];

        /**
         * Run the checks about .htaccess capabilities
         * 
         * @param string $testsBaseURL The root test folder for _data folder lookup
         * @param bool $shouldEcho Should check function echo errors
         * @return bool True if check succed, false otherwise
         */
        static public function check(string $testsBaseURL = null, bool $shouldEcho = !WA_CONFIG_IS_WP) {
            // if (class_exists(AppInterface::class)
            // && ($inst = AppInterface::instance())) {
            global $_wa_fetch_instance;
            // var_dump(isset($_wa_fetch_instance));
            // var_dump(isset($_wa_fetch_instance) && ($wa = $_wa_fetch_instance()));
            // var_dump($_wa_fetch_instance);
            // var_dump($_wa_fetch_instance());
            // throw new \Exception('Debug calls to EXT_TEST_htaccessIsEnabled::check in progress');
            if (isset($_wa_fetch_instance) && ($wa = $_wa_fetch_instance())) {
                $wa->debug(
                    "Will EXT_TEST_htaccessIsEnabled::check with base '$testsBaseURL'",
                );
                $wa->debugVeryVerbose(
                    "At :",
                    $wa->debug_trace(),
                );
            }
            // if ($wa ?? false) {
            //     throw new \Exception('Debug calls to EXT_TEST_htaccessIsEnabled::check in progress');    
            // }
    
            self::$errors = [];
            if ( !WA_CONFIG_IS_WP && !isset($_SERVER['WA_CONFIG_HTACCESS_IS_ON']) ) {
                // This test only check env variable is working, on top of redirect
                // This test have meaning only if the .htaccess aside this __FILE__ is used
                self::$errors[] = "Fail to load environement variable from .htaccess";
            }

            if (!$testsBaseURL) {
                $testsBaseURL = (isset($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                . "://{$_SERVER['HTTP_HOST']}"
                . dirname(dirname("$_SERVER[REQUEST_URI]"));
            }
            $testsBaseURL = rtrim($testsBaseURL, "/");

            $targetUrl = "$testsBaseURL/_data/isHtaccessEnabled/index.html";

            $http = curl_init($targetUrl);
            // https://thisinterestsme.com/php-curl-ssl-certificate-error/
            // https://reqbin.com/req/php/c-ug1qqqwh/curl-ignore-certificate-checks
            curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
            //for debug only! (well we only test .htaccess, no private contentes in our urls call, can stay open)
            curl_setopt($http, CURLOPT_SSL_VERIFYHOST, false); // NEEDED FOR DEV since certificate is self signed on dev computers...
            curl_setopt($http, CURLOPT_SSL_VERIFYPEER, false);

            // BETTER, if you want to allow dev SSL certificate only :
            // https://stackoverflow.com/questions/15135834/php-curl-curlopt-ssl-verifypeer-ignored
            // curl_setopt($cHandler, CURLOPT_SSL_VERIFYPEER, true);  
            // curl_setopt($cHandler, CURLOPT_CAINFO, getcwd() . "/positiveSSL.ca-bundle");

            $result = curl_exec($http);
            $code = curl_getinfo($http, CURLINFO_HTTP_CODE);
            // var_dump(curl_getinfo($http)); exit;
            if (false === $result) {
                $result = 'false';
            }

            // if ($wa ?? false) {
            //     $wa->debugVerbose("Htaccess test results : ", $code, $result);
            // }

            if($code !== 302) {
                self::$errors[] = "[HTTP $code] Fail to redirect from $targetUrl with .htaccess.<br />"
                . curl_error($http) . "<br />Did Output: <br />$result";
            }

            $http = curl_init($targetUrl);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
            // for debug only! (well we only test .htaccess, no private contentes in our urls call, can stay open)
            curl_setopt($http, CURLOPT_SSL_VERIFYHOST, false); // NEEDED FOR DEV since certificate is self signed on dev computers...
            curl_setopt($http, CURLOPT_SSL_VERIFYPEER, false);
            // https://evertpot.com/curl-redirect-requestbody/
            curl_setopt($http, CURLOPT_FOLLOWLOCATION, true);

            $result = curl_exec($http);
            $code = curl_getinfo($http, CURLINFO_HTTP_CODE);

            if (false === $result) {
                $result = 'false';
            }

            $expected = 'You have been redirected';
            if(false !== strpos($result, $expected)) {
                self::$errors[] = "[HTTP $code] Fail to see expected output '$expected' in redirected html $targetUrl.<br />"
                . curl_error($http) . "<br />Did Output: <br />$result";
            }
            $haveErrors = !!count(self::$errors);
            if ($shouldEcho) {
                echo implode('<br />', self::$errors)
                . $haveErrors ? '' : '.htaccess checks OK';
            }

            return !$haveErrors;
        }
    }

    // TODO same define for WA Config ? from codeception will not be defined, ok to echo ?
    if (!WA_CONFIG_IS_WP) {
        EXT_TEST_htaccessIsEnabled::check();
    }

}