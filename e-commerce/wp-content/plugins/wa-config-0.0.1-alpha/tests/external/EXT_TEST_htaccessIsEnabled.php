<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 *
 * This test will check if '.htaccess' redirect is activated
 *
 * @link https://www.web-agency.app/e-commerce/produit/wa-config-codeception-testings/ wa-config by Monwoo
 * @since 0.0.1
 * @package
 * @filesource
 * @author service@monwoo.com
 **/

namespace WA\Config\ExtTest {

    if (!defined('WA_CONFIG_IS_WP')) {
        define('WA_CONFIG_IS_WP', defined(WPINC));
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
         * @param string $testsURL The root test folder for _data folder lookup
         * @param bool $shouldEcho Should check function echo errors
         * @return bool True if check succed, false otherwise
         */
        static public function check(string $testsURL = null, bool $shouldEcho = !WA_CONFIG_IS_WP) {
            self::$errors = [];
            if ( !isset($_SERVER['WA_CONFIG_HTACCESS_IS_ON']) ) {
                self::$errors[] = "Fail to load environement variable from .htaccess";
            }

            if (!$testsURL) {
                $testsURL = (isset($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                . "://{$_SERVER['HTTP_HOST']}"
                . dirname(dirname("$_SERVER[REQUEST_URI]"));
            }

            $targetUrl = "$testsURL/_data/isHtaccessEnabled/index.html";

            $http = curl_init($targetUrl);
            // https://thisinterestsme.com/php-curl-ssl-certificate-error/
            // 
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

            if($code !== 302) {
                self::$errors[] = "[HTTP $code] Fail to redirect from $targetUrl with .htaccess.<br />"
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