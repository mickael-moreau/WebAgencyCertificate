<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.13  |
    |              on 2022-06-14 05:34:08              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
/*
🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo, service@monwoo.com
*/

namespace {
    use WA\Config\Core\AppInterface;
    if (!defined('WPINC')) {
        exit;
    }
    if (!function_exists(_wa_e2e_tests_wp_die_handler::class)) {
        function _wa_e2e_tests_wp_die_handler($message, $title = '', $args = array())
        {
            $inst = AppInterface::instance();
            $inst->debug("Will _wa_e2e_tests_wp_die_handler '{$message}' {$title}");
            $inst->debugVeryVerbose("At :", $inst->debug_trace());
            $inst->debugVeryVerbose(" with :", $args);
        }
    }
}
namespace WA\Config\Core {
    use function WA\Config\Utils\strEndsWith;
    use Exception;
    use RecursiveDirectoryIterator;
    use RecursiveIteratorIterator;
    use WA\Config\Admin\Notice;
    use WA\Config\Admin\EditableConfigPanels;
    use WA\Config\Admin\OptiLvl;
    use WA\Config\Frontend\EditableFooter;
    use WA\Config\Utils\DumpGzip;
    use WA\Config\Utils\DumpPlainTxt;
    use WA\Config\Utils\InsertSqlStatement;
    use WP;
    use WP_Filesystem_Direct;
    use wpdb;
    use ZipArchive;
    if (!class_exists(WPFilters::class)) {
        class WPFilters
        {
            const wa_config_e_footer_render = 'wa_config_e_footer_render';
            const wa_config_reviews_ids_to_trash = 'wa_config_reviews_ids_to_trash';
        }
    }
    if (!class_exists(WPActions::class)) {
        class WPActions
        {
            const wa_ac_render_after_parameters = 'wa_ac_render_after_parameters';
            const wa_do_base_review_preprocessing = 'wa_do_base_review_preprocessing';
            const wa_do_base_review_postprocessing = 'wa_do_base_review_postprocessing';
        }
    }
    if (!trait_exists(Identifiable::class)) {
        trait Identifiable
        {
            public $iPrefix = "wa-i";
            public $iId = null;
            public $iIndex = null;
            public $pluginName = "";
            public $pluginRelativePath = "";
            public $pluginVersion = "";
            protected $siteBaseHref = "";
            protected $pluginFile = "";
            protected $pluginRoot = "";
            function is_cli()
            {
                if (defined('STDIN')) {
                    return true;
                }
                return empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0;
            }
            protected function get_user_ip($anonymize = true)
            {
                $ip = "#IP-NOT-FOUND-ERROR#";
                if ($this->is_cli()) {
                    $ip = $_SERVER['SERVER_ADDR'] ?? $_SERVER['REMOTE_ADDR'] ?? $_SERVER['argv'][0];
                }
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                }
                if ($anonymize) {
                    $ip = preg_replace(['/\\.\\d*$/', '/[\\da-f]*:[\\da-f]*$/'], ['.XXX', 'XXXX:XXXX'], $ip);
                }
                return apply_filters('wa_get_ip', $ip);
            }
            protected function fetch_review_key_id(&$checkpoint)
            {
                if ($checkpoint['fixed_id']) {
                    return $checkpoint['fixed_id'];
                }
                $catSlug = sanitize_title($checkpoint['category']);
                $titleSlug = sanitize_title($checkpoint['title']);
                $keyId = "{$catSlug}-{$titleSlug}-{$checkpoint['created_by']}-{$checkpoint['create_time']}";
                $checkpoint['fixed_id'] = $keyId;
                return $keyId;
            }
            protected function get_backup_folder()
            {
                $bckupFolder = wp_upload_dir()['basedir'] . "/plugins/{$this->pluginName}";
                if (!file_exists($bckupFolder)) {
                    mkdir($bckupFolder, 0777, true);
                }
                return $bckupFolder;
            }
        }
    }
    if (!trait_exists(Parallelizable::class)) {
        trait Parallelizable
        {
            public function p_higherThanOneCallAchievedSentinel($methodeName)
            {
                $isFirstCall = $this->isFirstMethodCall($methodeName);
                $this->methodeCalledFrom($methodeName);
                $sentinelAdvice = !$isFirstCall;
                if ($sentinelAdvice) {
                    $this->debugVerbose("Sentinel higherThanOneCallAchieved reached for {$methodeName} called by {$this->iId}");
                }
                return $sentinelAdvice;
            }
        }
    }
    if (!trait_exists(Debugable::class)) {
        trait Debugable
        {
            use Identifiable;
            protected $shouldDebug = false;
            protected $shouldDebugVerbose = false;
            protected $shouldDebugVeryVerbose = false;
            protected $throwOnAllError = false;
            protected function _000_debug__bootstrap()
            {
                if (!$this->shouldDebug) {
                    return;
                }
                error_reporting(E_ALL);
                if (!defined('WC_ABSPATH')) {
                    ini_set("log_errors", 1);
                    $logPath = ABSPATH . "wp-content/debug.log";
                    ini_set("error_log", $logPath);
                }
                if ($this->throwOnAllError) {
                    set_error_handler([$this, "debug_exception_error_handler"]);
                }
                $default_opts = array('http' => array('notification' => [$this, 'debug_stream_notification_callback']), 'https' => array('notification' => [$this, 'debug_stream_notification_callback']));
                $default = stream_context_set_default($default_opts);
                add_filter('pre_http_request', [$this, 'debug_trace_wp_http_requests'], 10, 3);
            }
            public function debug_stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
            {
                if (STREAM_NOTIFY_REDIRECTED === $notification_code) {
                    $this->debug("Detect {$message_code} HTTP stream Call to : {$message}");
                }
                return;
                switch ($notification_code) {
                    case STREAM_NOTIFY_RESOLVE:
                    case STREAM_NOTIFY_AUTH_REQUIRED:
                    case STREAM_NOTIFY_COMPLETED:
                    case STREAM_NOTIFY_FAILURE:
                    case STREAM_NOTIFY_AUTH_RESULT:
                        var_dump($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max);
                        break;
                    case STREAM_NOTIFY_REDIRECTED:
                        echo "Redirection vers : ", $message;
                        break;
                    case STREAM_NOTIFY_CONNECT:
                        echo "Connecté...";
                        break;
                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        echo "Récupération de la taille du fichier : ", $bytes_max;
                        break;
                    case STREAM_NOTIFY_MIME_TYPE_IS:
                        echo "Type mime trouvé : ", $message;
                        break;
                    case STREAM_NOTIFY_PROGRESS:
                        echo "En cours de téléchargement, déjà ", $bytes_transferred, " octets transférés";
                        break;
                }
                echo "\n";
            }
            public function debug_exception_error_handler($severity, $message, $file, $line)
            {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }
            public function debug_trace_wp_http_requests($preempt, $parsed_args, $url)
            {
                $this->debug("Detect {$parsed_args['method']} HTTP Call to : {$url}");
                $e = new Exception("debug_trace_wp_http_requests trace callstack");
                $this->debugVeryVerbose("debug_trace_wp_http_requests Call stack", "\n" . $e->getTraceAsString());
                $this->debugVeryVerbose("debug_trace_wp_http_requests details {$url}", ["preempt" => $preempt, "http_args" => $parsed_args, "full_trace" => $e->getTrace()]);
                return $preempt;
            }
            public function debug_trace($full = false)
            {
                $e = new Exception("debug_trace callstack");
                return $full ? $e->getTrace() : "\n" . $e->getTraceAsString();
            }
            public function info(string $msg, ...$ctx) : void
            {
                $this->log('info', $msg, ...$ctx);
            }
            public function err(string $msg, ...$ctx) : void
            {
                $this->log('error', $msg, ...$ctx);
            }
            public function warn(string $msg, ...$ctx) : void
            {
                $this->log('warning', $msg, ...$ctx);
            }
            public function debug(string $msg, ...$ctx) : void
            {
                if ($this->shouldDebug) {
                    $this->log('debug', $msg, ...$ctx);
                }
            }
            public function debugVerbose(string $msg, ...$ctx) : void
            {
                if ($this->shouldDebugVerbose) {
                    $this->debug($msg, ...$ctx);
                }
            }
            public function debugVeryVerbose(string $msg, ...$ctx) : void
            {
                if ($this->shouldDebugVeryVerbose) {
                    $this->debug($msg, ...$ctx);
                }
            }
            public function assert(bool $test, string $msg, ...$ctx) : bool
            {
                if (!$this->assertLog($test, $msg, ...$ctx)) {
                    if ($this->shouldDebug) {
                        throw new \Exception($msg);
                    }
                }
                return $test;
            }
            public function assertLog(bool $test, string $msg, ...$ctx) : bool
            {
                if (!$test) {
                    $this->warn("[Assert FAIL] {$msg}", ...$ctx);
                }
                return $test;
            }
            protected $pIdSuffix = ["-", "^", "*", "!", "?", "&", "@"];
            protected static $_pIdToSuffix = [];
            public function log($tags, string $msg, ...$ctx) : void
            {
                if (is_string($tags)) {
                    $tags = explode(',', $tags);
                }
                $this->assert(count($tags), "Debug log need at least one level tag defined...");
                $pId = getmypid();
                $tagsPrompt = "[" . implode("][", $tags) . "]";
                $pSuffix = self::$_pIdToSuffix[$pId] ?? "";
                if (strlen($pSuffix) <= 0) {
                    $randL = function () {
                        return $this->pIdSuffix[array_rand($this->pIdSuffix)];
                    };
                    $pSuffix = $randL() . $randL();
                    self::$_pIdToSuffix[$pId] = $pSuffix;
                }
                $msg = "#{$pId}{$pSuffix}[{$this->iId}]{$tagsPrompt} {$msg}";
                if (defined('WC_ABSPATH')) {
                    if (!function_exists('wc_get_logger')) {
                        include_once WC_ABSPATH . 'includes/wc-core-functions.php';
                    }
                    $logger = wc_get_logger();
                    $logger->log($tags[0], $msg, $ctx);
                }
                $logPath = ABSPATH . "wp-content/debug.log";
                $timePrompt = date_i18n('m-d-Y @ H:i:s');
                $msg = "[{$timePrompt}] {$msg}";
                if (count($ctx)) {
                    error_log($msg . " " . print_r($ctx, true) . "\n", 3, $logPath);
                } else {
                    error_log($msg . "\n", 3, $logPath);
                }
            }
        }
    }
    if (!trait_exists(Editable::class)) {
        trait Editable
        {
            protected $eConfOptEnableFooter = 'wa_enable_footer';
            protected $eConfOptFooterTemplate = 'wa_footer_template';
            protected $eConfOptFooterCredit = 'wa_footer_credit';
            protected $eConfOptOptiLevels = 'wa_optimisable_levels';
            protected $eConfOptOptiWpRequestsFilter = 'wa_optimisable_wp_http_request_filter';
            protected $eConfOptOptiWpRequestsSafeFilter = 'wa_optimisable_wp_http_request_safe_filter';
            protected $E_DEFAULT_OPTIMISABLE_SAFE_FILTER = '$(^https://)((web-agency.local.dev/)|(api.wordpress.org/(plugins)|(themes/info))|(downloads.wordpress.org/(plugin)|(theme)|(translation))|(translate.wordpress.com)|(woocommerce.com/wp-json/))$';
            protected $eConfOptOptiEnableBlockedHttpNotice = 'wa_optimisable_enable_blocked_http_notice';
            protected $eConfOptATestsUsers = 'wa_acceptance_tests_users';
            protected $E_DEFAULT_A_TESTS_USERS_LIST = "demo@monwoo.com,editor-wa@monwoo.com,client-wa@monwoo.com,demo-wrong@monwoo.com'demo-wrong@monwoo.com'";
            protected $eConfOptATestsBaseUrl = 'wa_acceptance_tests_base_url';
            protected $eConfOptATestsRunForCabability = 'wa_acceptance_tests_r_capability';
            protected $eConfOptReviewCategory = 'wa_review_category';
            protected $eConfOptReviewCategoryIcon = 'wa_review_category_icon';
            protected $eConfOptReviewTitle = 'wa_review_title';
            protected $eConfOptReviewTitleIcon = 'wa_review_title_icon';
            protected $eConfOptReviewRequirements = 'wa_review_requirements';
            protected $eConfOptReviewValue = 'wa_review_value';
            protected $eConfOptReviewResult = 'wa_review_result';
            protected $eConfOptReviewAccessCapOrRole = 'wa_review_access_cap_or_role';
            protected $eConfOptReviewIsActivated = 'wa_review_is_activated';
            protected $eConfOptReviewsDeleted = 'wa_reviews_deleted';
            protected $eConfOptReviewsByCategorieByTitle = 'wa_reviews_by_category_by_title';
            protected $eConfOptReviewsInternalPreUpdateAction = 'wa_reviews_internal_pre_update_action';
        }
    }
    if (!trait_exists(EditableWaConfigOptions::class)) {
        trait EditableWaConfigOptions
        {
            public $eAdminConfigOptsKey = 'wa_config_e_admin_config_opts';
            public $eAdminConfigReviewOptsKey = 'wa_config_e_admin_config_review_opts';
            public $eAdminConfigE2ETestsOptsKey = 'wa_config_e_admin_config_e2e_tests_opts';
            public $oPluginLoadsMasterPathOptKey = 'wa_config_master_load_plugin_path_opt';
            protected $eAdminConfigPageKey = 'wa-config-e-admin-config-param-page';
            protected $eAdminConfigParamPageKey = 'wa-config-e-admin-config-param-page';
            protected $eAdminConfigReviewPageKey = 'wa-config-e-admin-config-review-page';
            protected $eAdminConfigDocPageKey = 'wa-config-e-admin-config-doc-page';
            protected $eAdminConfigParamSettingsKey = 'wa-config-e-admin-config-param-section';
            protected $eAdminConfigReviewSettingsKey = 'wa-config-e-admin-config-review-section';
            protected $eAdminConfigOptsGroupKey = 'wa_config_e_admin_config_opts_group';
            protected $eAdminConfigOptsReviewGroupKey = 'wa_config_e_admin_config_opts_review_group';
            protected $eAdminConfigOpts = [];
            public function getWaConfigOption($key, $default)
            {
                $this->debugVeryVerbose("Will getWaConfigOption {$key}");
                $this->eAdminConfigOpts = get_option($this->eAdminConfigOptsKey, array_merge([$key => $default], $this->eAdminConfigOpts));
                if (!is_array($this->eAdminConfigOpts)) {
                    $this->warn("Having wrong datatype saved for {$key}", $this->eAdminConfigOpts);
                    $this->eAdminConfigOpts = [$key => $default];
                }
                if (!key_exists($key, $this->eAdminConfigOpts)) {
                    $this->eAdminConfigOpts[$key] = $default;
                    $this->assert(update_option($this->eAdminConfigOptsKey, $this->eAdminConfigOpts), "Fail to update option {$this->eAdminConfigOptsKey}");
                }
                $value = $this->eAdminConfigOpts[$key];
                $this->debugVeryVerbose("Did getWaConfigOption {$key}", $value);
                return $value;
            }
            public function setWaConfigOption($key, $value)
            {
                throw new \Exception("TODO in dev");
            }
        }
    }
    if (!trait_exists(Translatable::class)) {
        trait Translatable
        {
            use Editable, Identifiable;
            public $waConfigTextDomain = 'wa-config';
            protected function _000_t_scripts__bootstrap()
            {
                $this->t_loadTextdomains();
            }
            public function t_loadTextdomains() : void
            {
                $this->debugVerbose("Will t_loadTextdomains from plugin {$this->pluginName}");
                $langFolder = $this->pluginName . '/languages';
                $this->assertLog(load_plugin_textdomain('wa-config', false, $langFolder), "Fail to load textdomain /*📜*/'wa-config'/*📜*/ for " . get_locale() . " at path {$langFolder}");
            }
        }
    }
    if (!class_exists(AppInterface::class)) {
        abstract class AppInterface
        {
            use Debugable, Parallelizable, Translatable;
            const PLUGIN_VERSION = "0.0.1-alpha";
            protected static $_compatibilityReports = [];
            public static function addCompatibilityReport($level, $msg) : void
            {
                self::$_compatibilityReports[] = ['level' => $level, 'msg' => $msg];
                usort(self::$_compatibilityReports, function ($cr1, $cr2) {
                    return strnatcasecmp($cr1['level'], $cr2['level']);
                });
            }
            public static function getCompatibilityReports()
            {
                return self::$_compatibilityReports;
            }
            protected static $_instances = [];
            protected static $_iByRelativePath = [];
            protected static $_iByIId = [];
            protected static function addInstance(AppInterface $inst)
            {
                $inst->iIndex = count(self::$_instances);
                $inst->iId = $inst->iPrefix . "-" . $inst->iIndex;
                self::$_instances[] = $inst;
                if (!key_exists($inst->pluginRelativePath, self::$_iByRelativePath)) {
                    self::$_iByRelativePath[$inst->pluginRelativePath] = [];
                }
                self::$_iByRelativePath[$inst->pluginRelativePath][] = $inst;
                if (!key_exists($inst->iId, self::$_iByIId)) {
                    self::$_iByIId[$inst->iId] = [];
                }
                self::$_iByIId[$inst->iId][] = $inst;
            }
            public static function instance(int $index = 0) : AppInterface
            {
                return self::$_instances[$index];
            }
            protected static $_uIdxCount = 0;
            public static function uIdx() : int
            {
                return self::$_uIdxCount++;
            }
            public static function lastInstance() : AppInterface
            {
                return end(self::$_instances);
            }
            public static function instanceByRelativePath($path, $index = 0) : ?AppInterface
            {
                if (!key_exists($path, self::$_iByRelativePath)) {
                    return null;
                }
                if ($index < 0) {
                    $index += count(self::$_iByRelativePath[$path]);
                }
                return self::$_iByRelativePath[$path][$index];
            }
            public static function instanceByIId($iId) : ?AppInterface
            {
                if (!key_exists($iId, self::$_iByIId)) {
                    return null;
                }
                return self::$_iByIId[$iId];
            }
            protected static $_methodes = [];
            protected static $_statsCountKey = '__count__';
            public static function getMethodStatistics(string $methodeName)
            {
                return key_exists($methodeName, self::$_methodes) ? self::$_methodes[$methodeName] : null;
            }
            const ERR_AUTH_TEST_USER_FAIL_USERNAME = 1;
            const ERR_AUTH_TEST_USER_FAIL_USERNAME_UPDATE = 2;
            public function e2e_test_authenticateTestUser($userLoginName, $accessHash, $emailTarget = null, $shouldClone = false)
            {
                $anonimizedIp = $this->get_user_ip();
                if (!($aInfo = $this->e2e_tests_validate_access_hash($accessHash))) {
                    $this->err("Invalid authenticateTestUser access for '{$accessHash}' by {$anonimizedIp}");
                    echo json_encode(["error" => "[{$anonimizedIp}][{$accessHash}] " . __("IP enregistrée suite à accès invalid", 'wa-config')]);
                    http_response_code(401);
                    return false || wp_die();
                }
                $dateStamp = time();
                $emailTarget = trim($emailTarget);
                if (!strlen($emailTarget ?? "")) {
                    $emailTarget = null;
                }
                $emailTarget = $emailTarget ?? "test-{$dateStamp}-{$userLoginName}";
                $this->debugVerbose("Will e2e_test_authenticateTestUser from '{$userLoginName}' to '{$emailTarget}'");
                $user = get_user_by('login', $userLoginName);
                if (!$user || is_wp_error($user)) {
                    $this->err("[{$anonimizedIp}][{$userLoginName}] " . __("N'est pas un utilisateur enregistré", 'wa-config'));
                    echo json_encode(["error" => "[{$anonimizedIp}][{$userLoginName}] " . __("N'est pas un utilisateur enregistré", 'wa-config')]);
                    http_response_code(404);
                    return false || wp_die();
                }
                if ($shouldClone) {
                    throw new \Error("Clone not available, dev in progress");
                }
                $realUserName = $user->user_login;
                $realUserEmail = $user->user_email;
                $testMeta = get_user_meta($user->ID, 'wa-e2e-test');
                $previousTestRealUserName = $testMeta[0]['real-username'] ?? false;
                $previousTestRealUserEmail = $testMeta[0]['real-user-email'] ?? false;
                if (count($testMeta)) {
                    $this->info("[{$anonimizedIp}] Test user already logged in...", $testMeta);
                    $emailTarget = $user->user_login;
                } else {
                    wp_cache_delete("alloptions", "options");
                    $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                    $testUsers = $E2ETestsOptions['test-users'] ?? [];
                    $user->user_login = $emailTarget;
                    $user->user_email = $emailTarget;
                    global $wpdb;
                    if (false === $wpdb->update($wpdb->users, ['user_login' => $user->user_login, 'user_email' => $user->user_email], ['ID' => $user->ID])) {
                        $this->err("[{$userLoginName}][=> {$user->user_login}] " . __("Echec de la mise à jour du nom utilisateur", 'wa-config'));
                        return false;
                    }
                    if (!update_user_meta($user->ID, 'wa-e2e-test', ["real-username" => $realUserName, "real-user-email" => $realUserEmail])) {
                        $this->err("[{$userLoginName}][=> {$user->user_login}] " . __("Echec de la mise à jour des méta de test de l'utilisateur", 'wa-config'));
                        $user->user_login = $realUserName;
                        $user->user_email = $realUserEmail;
                        if (false === $wpdb->update($wpdb->users, ['user_login' => $user->user_login, 'user_email' => $user->user_email], ['ID' => $user->ID])) {
                            $this->err("[{$userLoginName}][=> {$user->user_login}] " . __("Echec du rollback de l'utilisateur", 'wa-config'));
                        }
                        return false;
                    }
                    $testUsers[$emailTarget] = $user;
                    $E2ETestsOptions['test-users'] = $testUsers;
                    update_option($this->eAdminConfigE2ETestsOptsKey, $E2ETestsOptions);
                }
                wp_clear_auth_cookie();
                global $current_user;
                $current_user = null;
                wp_set_current_user($user->user_login);
                wp_set_auth_cookie($user->ID);
                $this->info("[{$anonimizedIp}] Succed to login test user from '{$userLoginName}' to '{$user->user_login}' with hash [{$accessHash}]");
                return $user;
            }
            public function e2e_test_logoutTestUser($userLoginName, $accessHash) : string
            {
                $this->debugVerbose("Will e2e_test_logoutTestUser");
                $anonimizedIp = $this->get_user_ip();
                if (!($aInfo = $this->e2e_tests_validate_access_hash($accessHash))) {
                    $this->err("Invalid e2e_test_logoutTestUser access for '{$accessHash}' by {$anonimizedIp}");
                    http_response_code(401);
                    return json_encode(["error" => "[{$anonimizedIp}][{$accessHash}] " . __("IP enregistrée suite à accès invalid", 'wa-config')]);
                }
                $user = get_user_by('login', $userLoginName);
                if (!$user) {
                    $this->err("Utilisateur '{$userLoginName}' non existant from '{$accessHash}' by {$anonimizedIp}");
                    http_response_code(404);
                    return json_encode(["error" => "[{$anonimizedIp}][{$accessHash}] {$userLoginName} " . __("Utilisateur non existant ou déjà déconnecté", 'wa-config')]);
                }
                $testMeta = get_user_meta($user->ID, 'wa-e2e-test');
                $this->debugVeryVerbose("[{$user->ID}] Meta 'wa-e2e-test' : ", $testMeta);
                $realUserName = $testMeta[0]['real-username'] ?? $user->user_login;
                $realUserEmail = $testMeta[0]['real-user-email'] ?? $user->user_email;
                global $wpdb;
                $user->user_login = $realUserName;
                $user->user_email = $realUserEmail;
                if (false === $wpdb->update($wpdb->users, ['user_login' => $user->user_login, 'user_email' => $user->user_email], ['ID' => $user->ID])) {
                    $this->err("Fail to restore test user from '{$userLoginName}' to '{$user->user_login}'");
                    http_response_code(404);
                    return json_encode(["error" => "[{$anonimizedIp}][{$accessHash}] {$userLoginName} " . __("Erreur de restauration d'utilisateur", 'wa-config')]);
                } else {
                    if (!delete_user_meta($user->ID, 'wa-e2e-test')) {
                        $this->err("Fail to clean user 'wa-e2e-test' meta from '{$userLoginName}' to '{$user->user_login}'");
                    }
                }
                wp_clear_auth_cookie();
                $this->info("Succed to logout test user from '{$userLoginName}' to '{$user->user_login}'");
                http_response_code(200);
                return json_encode(["status" => "OK", "end_date" => date("Y/m/d H:i:s O ")]);
            }
            public function e2e_test_action() : void
            {
                $anonimizedIp = $this->get_user_ip();
                $action = '';
                if (isset($_REQUEST['wa-action'])) {
                    $action = filter_var($_REQUEST['wa-action'], FILTER_SANITIZE_SPECIAL_CHARS);
                } else {
                    $this->err("Missing action parameter for e2e_test_action by {$anonimizedIp}");
                    echo json_encode(["error" => "[{$anonimizedIp}] " . __("Paramétre 'wa-action' manquant.", 'wa-config')]);
                    http_response_code(404);
                    wp_die();
                    return;
                }
                $this->debug("Will e2e_test_action '{$action}' by '{$anonimizedIp}'");
                if ('force-clean-and-restore-users' === $action) {
                    echo $this->e2e_test_clean_and_restore_test_users();
                    http_response_code(200);
                    wp_die();
                    return;
                }
                if ('download-last-backup' === $action) {
                    $bckUpType = filter_var($_REQUEST['wa-backup-type'], FILTER_SANITIZE_SPECIAL_CHARS);
                    echo $this->e2e_test_download_last_backup($bckUpType);
                    wp_die();
                    return;
                }
                if ('do-backup' === $action) {
                    $bckUpType = filter_var($_REQUEST['wa-backup-type'], FILTER_SANITIZE_SPECIAL_CHARS);
                    $compressionType = filter_var($_REQUEST['wa-compression-type'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
                    echo $this->e2e_test_do_backup($bckUpType, $compressionType);
                    wp_die();
                    return;
                }
                $aHash = filter_input(INPUT_POST, 'wa-access-hash', FILTER_SANITIZE_SPECIAL_CHARS);
                if (!($aInfo = $this->e2e_tests_validate_access_hash($aHash))) {
                    $this->err("Invalid access for '{$aHash}' by {$anonimizedIp}");
                    echo json_encode(["error" => "[{$anonimizedIp}][{$aHash}] " . __("IP enregistrée suite à accès invalid", 'wa-config')]);
                    http_response_code(401);
                    wp_die();
                    return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $dataPOST = filter_input(INPUT_POST, 'wa-data', FILTER_SANITIZE_SPECIAL_CHARS);
                $dataJson = base64_decode($dataPOST);
                $data = json_decode($dataJson, true);
                switch ($action) {
                    case 'authenticate-user':
                        $emailTarget = null;
                        @([$email, $emailTarget] = filter_input(INPUT_POST, 'wa-data', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY));
                        $test_user = $this->e2e_test_authenticateTestUser($email, $aHash, $emailTarget);
                        if ($test_user) {
                            echo json_encode(["status" => "OK", "test_user" => $test_user, "end_date" => date("Y/m/d H:i:s O ")]);
                            http_response_code(200);
                        }
                        wp_die();
                        return;
                        break;
                    case 'logout-user':
                        $email = filter_input(INPUT_POST, 'wa-data', FILTER_SANITIZE_SPECIAL_CHARS);
                        echo $this->e2e_test_logoutTestUser($email, $aHash);
                        wp_die();
                        return;
                        break;
                    default:
                        $this->warn("Unknow action '{$action}'");
                        break;
                }
                echo json_encode(["status" => "OK", "end_date" => date("Y/m/d H:i:s O ")]);
                http_response_code(200);
                wp_die();
                return;
            }
            public function e2e_tests_access_hash_open($doNotSendEmail = true)
            {
                global $argv;
                $serverIP = $this->get_user_ip(false);
                $hSize = 6;
                $h = bin2hex(random_bytes($hSize / 2));
                $accessHash = base64_encode("e2e-tests-{$serverIP}-" . time() . "-{$h}");
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                $E2ETestsOptions['access-open'] = time();
                $E2ETestsOptions["tests-in-progress"] = array_merge($E2ETestsOptions["tests-in-progress"] ?? [], [$accessHash => ['access-hash' => $accessHash, 'started_at' => time(), 'started_by' => $serverIP]]);
                $E2ETestsOptions['emails-sended'] = [];
                $E2ETestsOptions['do-not-send-email'] = $doNotSendEmail;
                update_option($this->eAdminConfigE2ETestsOptsKey, $E2ETestsOptions);
                $this->debugVerbose("Openning e2e test hash '{$accessHash}' by {$serverIP}");
                return $E2ETestsOptions["tests-in-progress"][$accessHash];
            }
            public function e2e_tests_filter_wp_die_callback($message, $title = '', $args = array())
            {
                return '_wa_e2e_tests_wp_die_handler';
            }
            protected function exit()
            {
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                if ($E2ETestsOptions['access-open'] ?? false) {
                    $this->debugVerbose("Custom soft exit for test mode", $this->debug_trace());
                } else {
                    exit;
                }
            }
            public function e2e_tests_emails_middleware($email)
            {
                $this->debugVerbose("Sending email :" . $email["subject"]);
                $this->debugVeryVerbose("Sending email :", $email);
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                $E2ETestsOptions['emails-sended'] = $E2ETestsOptions['emails-sended'] ?? [];
                $E2ETestsOptions['emails-sended'][] = $email;
                update_option($this->eAdminConfigE2ETestsOptsKey, $E2ETestsOptions);
                if ($E2ETestsOptions['do-not-send-email'] ?? false) {
                    $email['to'] = "#e2e#{$email['to']}#e2e#";
                    $this->debug("Avoid mail send from test adjusted OK for {$email['to']}");
                }
                return $email;
            }
            public function e2e_tests_access_hash_close($accessHash)
            {
                $anonimizedIp = $this->get_user_ip();
                if (!($aInfo = $this->e2e_tests_validate_access_hash($accessHash))) {
                    $this->err("Invalid hash close access for '{$accessHash}' by {$anonimizedIp}");
                    echo json_encode(["error" => "[{$anonimizedIp}][{$accessHash}] " . __("IP enregistrée suite à accès invalid", 'wa-config')]);
                    http_response_code(401);
                    wp_die();
                    return;
                }
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                $E2ETestsOptions["tests-in-progress"][$accessHash] = array_merge($E2ETestsOptions["tests-in-progress"][$accessHash], ['ended_at' => time()]);
                $E2ETestsOptions["tests-in-progress"] = array_filter($E2ETestsOptions["tests-in-progress"], function ($testMeta) {
                    return time() - $testMeta['started_at'] < 1000 * 60 * 60;
                });
                $E2ETestsOptions['access-open'] = false;
                $E2ETestsOptions['emails-sended'] = [];
                update_option($this->eAdminConfigE2ETestsOptsKey, $E2ETestsOptions);
                $serverIP = $this->get_user_ip(false);
                $this->debugVerbose("Closing e2e test hash '{$accessHash}' by {$serverIP}");
            }
            public function e2e_tests_validate_access_hash($accessHash)
            {
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                $this->debugVerbose("e2e_tests_validate_access_hash '{$accessHash}'" . $E2ETestsOptions["tests-in-progress"][$accessHash]['started_by'] ?? 'TEST HASH NOT FOUND');
                if (!$accessHash || !strlen($accessHash) || !array_key_exists($accessHash, $E2ETestsOptions["tests-in-progress"] ?? [])) {
                    return false;
                }
                $accessInfos = $E2ETestsOptions["tests-in-progress"][$accessHash];
                $requestIP = $this->get_user_ip(false);
                if (time() - $accessInfos['started_at'] < 1000 * 60 * 60 && !array_key_exists('ended_at', $accessInfos) && $requestIP === $accessInfos['started_by']) {
                    return $accessInfos;
                }
                return false;
            }
            public function e2e_test_clean_and_restore_test_users()
            {
                $anonimizedIp = $this->get_user_ip();
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                $testUsers = $E2ETestsOptions['test-users'] ?? [];
                $testUsersCount = count($testUsers);
                $this->debug("[{$anonimizedIp}] Will e2e_test_clean_and_restore_test_users for {$testUsersCount} users.");
                if ($testUsersCount) {
                    $aInfo = $this->e2e_tests_access_hash_open();
                    $aHash = $aInfo['access-hash'];
                    foreach ($testUsers as $test_name => $user) {
                        $this->debug("[{$anonimizedIp}] Will e2e_test logout", $test_name);
                        $this->debugVeryVerbose(" for user :", $user);
                        $this->e2e_test_logoutTestUser($test_name, $aHash);
                    }
                    unset($E2ETestsOptions['test-users']);
                    update_option($this->eAdminConfigE2ETestsOptsKey, $E2ETestsOptions);
                    $this->e2e_tests_access_hash_close($aHash);
                }
                return json_encode(["did_update" => "E2ETestsOptions 'test-users' with clean_and_restore", "caller" => "[{$anonimizedIp}][{$this->iId}]", "update_count" => $testUsersCount, "end_date" => date("Y/m/d H:i:s O ")]);
            }
            public function e2e_test_download_last_backup(string $bckUpType, $compressionType = null)
            {
                $anonimizedIp = $this->get_user_ip();
                if (!current_user_can($this->optAdminEditCabability) || !current_user_can('administrator')) {
                    $this->err("e2e_test_download_last_backup invalid access for {$anonimizedIp}, need to be {$this->optAdminEditCabability} or administrator to do backups");
                    echo json_encode(["error" => "Invalid access for {$anonimizedIp} registred"]);
                    http_response_code(401);
                    wp_die();
                    return;
                }
                if ('sql' === $bckUpType) {
                    ob_start();
                    $siteSlug = sanitize_title(get_bloginfo('name'));
                    $fileExtension = $compressionType ?? '.sql';
                    $filename = "{$siteSlug}-full-database-backup{$fileExtension}";
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Content-Description: File Transfer');
                    header('Content-Type: text/plain; charset=utf-8');
                    header("Content-Disposition: attachment; filename={$filename}");
                    header('Expires: 0');
                    header('Pragma: public');
                    header("Content-Transfer-Encoding: binary");
                    $bckupFolder = $this->get_backup_folder();
                    $lastBckupPath = "{$bckupFolder}/{$filename}";
                    $this->debug("Download src : {$lastBckupPath}");
                    $downloadReport = ob_get_clean();
                    flush();
                    $fOut = fopen('php://output', 'w');
                    fwrite($fOut, file_get_contents($lastBckupPath));
                    fclose($fOut);
                    flush();
                    $this->debug("Succed to download {$filename}. {$downloadReport}");
                    http_response_code(200);
                    wp_die();
                    return;
                }
                if ('simple-zip' === $bckUpType || 'full-zip' === $bckUpType) {
                    $siteSlug = sanitize_title(get_bloginfo('name'));
                    $fileExtension = '.zip';
                    $filename = "{$siteSlug}-simple-backup{$fileExtension}";
                    if ('full-zip' === $bckUpType) {
                        $filename = "{$siteSlug}-full-backup{$fileExtension}";
                    }
                    $bckupFolder = $this->get_backup_folder();
                    $lastBckupPath = "{$bckupFolder}/{$filename}";
                    $fileSize = filesize($lastBckupPath);
                    $this->debug("Download src : {$lastBckupPath}");
                    header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/zip');
                    header("Content-Disposition: attachment; filename={$filename}");
                    header('Expires: 0');
                    header('Pragma: public');
                    header('Content-Length: ' . $fileSize);
                    header("Content-Transfer-Encoding: binary");
                    header("Accept-Ranges: bytes");
                    set_time_limit(25 * 60);
                    $downloadReport = "";
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    ob_clean();
                    flush();
                    $chunkSplit = false;
                    if (!$chunkSplit) {
                        wp_ob_end_flush_all();
                        readfile($lastBckupPath);
                    } else {
                        $simpleChunck = false;
                        if ($simpleChunck) {
                            $download_rate = 8 * (1024 * 1024);
                            $file = fopen($lastBckupPath, "r");
                            while (!feof($file)) {
                                print fread($file, round($download_rate));
                                flush();
                            }
                            fclose($file);
                        } else {
                            $offset = 0;
                            $length = $fileSize;
                            if (isset($_SERVER['HTTP_RANGE'])) {
                                preg_match('/bytes=(\\d+)-(\\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                                $offset = intval($matches[1]);
                                $length = intval($matches[2]) - $offset;
                                $this->debug("Will offset range of lenght {$length} starting at {$offset} for {$filename}");
                                $fhandle = fopen($lastBckupPath, 'r');
                                fseek($fhandle, $offset);
                                $data = fread($fhandle, $length);
                                fclose($fhandle);
                                header('HTTP/1.1 206 Partial Content');
                                header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $fileSize);
                            }
                            $chunksize = 8 * (1024 * 1024);
                            $handle = fopen($lastBckupPath, 'rb');
                            wp_ob_end_flush_all();
                            $buffer = '';
                            $maxAllowedMemory = ini_get('memory_limit');
                            if (preg_match('/^(\\d+)(.)$/', $maxAllowedMemory, $matches)) {
                                if (strtoupper($matches[2]) == 'G') {
                                    $maxAllowedMemory = $matches[1] * 1024 * 1024 * 1024;
                                } else {
                                    if (strtoupper($matches[2]) == 'M') {
                                        $maxAllowedMemory = $matches[1] * 1024 * 1024;
                                    } else {
                                        if (strtoupper($matches[2]) == 'K') {
                                            $maxAllowedMemory = $matches[1] * 1024;
                                        } else {
                                            $maxAllowedMemory = $matches[1];
                                        }
                                    }
                                }
                            }
                            $memoryLimit = $maxAllowedMemory - memory_get_usage(true);
                            $this->debug("Memory limit before downloading {$filename} : " . round($memoryLimit / 1024 / 1024) . " Mb left on " . round($maxAllowedMemory / 1024 / 1024) . " Mb");
                            while (!feof($handle) && connection_status() === CONNECTION_NORMAL && $memoryLimit > $chunksize * 2) {
                                $buffer = fread($handle, $chunksize);
                                print $buffer;
                                flush();
                                $memoryLimit = $maxAllowedMemory - memory_get_usage(true);
                                $this->debug("Memory limit while downloading {$filename} : " . round($memoryLimit / 1024 / 1024) . " Mb");
                            }
                            if (connection_status() !== CONNECTION_NORMAL) {
                                $this->debug("Having aborted connection for {$filename} : " . connection_status());
                            }
                            fclose($handle);
                        }
                    }
                    $this->debug("Succed to download {$filename}. {$downloadReport}");
                    http_response_code(200);
                    wp_die();
                    return;
                }
                $this->err("[{$anonimizedIp}] Invalid backup type {$bckUpType}");
                echo json_encode(["error" => "[{$anonimizedIp}] " . __("Type de backup invalid", 'wa-config')]);
                http_response_code(404);
                wp_die();
                return;
            }
            public function e2e_test_do_backup(string $bckUpType, $compressionType = null, $shouldDownload = true, $shouldServeResponse = true)
            {
                $anonimizedIp = $this->get_user_ip();
                $wpRootPath = realpath(ABSPATH);
                if (!current_user_can($this->optAdminEditCabability) || !current_user_can('administrator')) {
                    $this->err("e2e_test_do_backup invalid access for {$anonimizedIp}, need to be {$this->optAdminEditCabability} or administrator to do backups");
                    echo json_encode(["error" => "Invalid access for {$anonimizedIp} registred"]);
                    http_response_code(401);
                    wp_die();
                    return;
                }
                if ('sql' === $bckUpType) {
                    $siteSlug = sanitize_title(get_bloginfo('name'));
                    $fileExtension = $compressionType ?? '.sql';
                    $filename = "{$siteSlug}-full-database-backup{$fileExtension}";
                    $bckupFolder = $this->get_backup_folder();
                    $lastBckupInfoPath = "{$bckupFolder}/plugins-and-themes.txt";
                    wp_delete_file($lastBckupInfoPath);
                    $lbip = fopen($lastBckupInfoPath, "w");
                    $files = glob("{$wpRootPath}/wp-content/plugins/*");
                    foreach ($files as $f) {
                        fwrite($lbip, str_replace($wpRootPath, "", $f) . "\n");
                    }
                    $files = glob("{$wpRootPath}/wp-content/themes/*");
                    foreach ($files as $f) {
                        fwrite($lbip, str_replace($wpRootPath, "", $f) . "\n");
                    }
                    fclose($lbip);
                    $this->e2e_test_add_in_backup_history($lastBckupInfoPath);
                    $lastBckupPath = "{$bckupFolder}/{$filename}";
                    wp_delete_file($lastBckupPath);
                    $this->e2e_test_load_SQL_in_file($lastBckupPath);
                    $this->e2e_test_add_in_backup_history($lastBckupPath);
                    $this->debug("Succed to backup sql in {$lastBckupPath}.");
                    if ($shouldDownload) {
                        $this->e2e_test_download_last_backup($bckUpType, $compressionType);
                    } else {
                        if ($shouldServeResponse) {
                            echo json_encode(["status" => "OK", "end_date" => date("Y/m/d H:i:s O ")]);
                            http_response_code(200);
                        }
                    }
                    if ($shouldServeResponse) {
                        wp_die();
                    }
                    return;
                }
                if ('simple-zip' === $bckUpType || 'full-zip' === $bckUpType) {
                    set_time_limit(25 * 60);
                    $siteSlug = sanitize_title(get_bloginfo('name'));
                    $fileExtension = '.zip';
                    $filename = "{$siteSlug}-simple-backup{$fileExtension}";
                    $rootPath = realpath(wp_upload_dir()['basedir']);
                    if ('full-zip' === $bckUpType) {
                        $filename = "{$siteSlug}-full-backup{$fileExtension}";
                        $rootPath = $wpRootPath;
                    }
                    $bckupFolder = realpath($this->get_backup_folder());
                    $lastBckupPath = "{$bckupFolder}/{$filename}";
                    wp_delete_file($lastBckupPath);
                    $historyFolder = "{$bckupFolder}/_history";
                    if (!file_exists($historyFolder)) {
                        mkdir($historyFolder, 0777, true);
                    }
                    $historyFolder = realpath($historyFolder);
                    $this->e2e_test_do_backup('sql', '.tar.gz', false, false);
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
                    $zip = new ZipArchive();
                    $zip->open($lastBckupPath, ZipArchive::CREATE);
                    foreach ($files as $file) {
                        if ($file->isDir()) {
                            continue;
                        }
                        $filePath = $file->getRealPath();
                        $fileName = basename($filePath);
                        if ("{$siteSlug}-full-backup.zip" === $fileName || "{$siteSlug}-simple-backup.zip" === $fileName || false !== strpos($filePath, $historyFolder)) {
                            continue;
                        }
                        $relativePath = substr($filePath, strlen($rootPath) + 1);
                        $this->debugVeryVerbose("Backup {$relativePath} from {$filePath} in {$lastBckupPath}.");
                        $zip->addFile($filePath, $relativePath);
                    }
                    $zip->close();
                    if ('simple-zip' === $bckUpType) {
                        $this->e2e_test_add_in_backup_history($lastBckupPath);
                    }
                    $this->debug("Succed to {$bckUpType} backup to : {$lastBckupPath}");
                    if ($shouldDownload) {
                        $downloadSimpleZipBckupUrl = add_query_arg(['action' => 'wa-e2e-test-action', 'wa-action' => 'download-last-backup', 'wa-backup-type' => $bckUpType], admin_url('admin-ajax.php'));
                        $this->debug("Will redirect download to : {$downloadSimpleZipBckupUrl}");
                        if (wp_redirect($downloadSimpleZipBckupUrl)) {
                            http_response_code(301);
                            $this->exit();
                            return;
                        }
                        echo json_encode(["error" => "Fail to redirect to {$downloadSimpleZipBckupUrl}"]);
                        $this->err("FAIL Download redirect to : {$downloadSimpleZipBckupUrl}");
                        wp_die();
                        return;
                    } else {
                        if ($shouldServeResponse) {
                            echo json_encode(["status" => "OK", "end_date" => date("Y/m/d H:i:s O ")]);
                            http_response_code(200);
                        }
                    }
                    if ($shouldServeResponse) {
                        wp_die();
                    }
                    return;
                }
                $this->err("[{$anonimizedIp}] Invalid backup type {$bckUpType}");
                echo json_encode(["error" => "[{$anonimizedIp}] " . __("Type de backup invalid", 'wa-config')]);
                http_response_code(404);
                wp_die();
                return;
            }
            protected static $bckupStartTime = null;
            protected function e2e_test_backup_start_time()
            {
                if (!self::$bckupStartTime) {
                    self::$bckupStartTime = date("Ymd-His_O");
                }
                return self::$bckupStartTime;
            }
            protected function e2e_test_add_in_backup_history($filePath, $historySubPath = "")
            {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
                $fs = new WP_Filesystem_Direct(null);
                $bckupFolder = $this->get_backup_folder();
                $bckupHistoryFolder = "{$bckupFolder}/_history/" . $this->e2e_test_backup_start_time();
                if (!file_exists($bckupHistoryFolder)) {
                    mkdir($bckupHistoryFolder, 0777, true);
                }
                $historyFilePath = (strlen($historySubPath) ? "{$historySubPath}/" : "") . basename($filePath);
                $destination = "{$bckupHistoryFolder}/{$historyFilePath}";
                $fs->copy($filePath, $destination, true);
                unset($fs);
                $this->debug("Succed to add backup history from {$filePath} to {$destination}");
            }
            protected function e2e_test_load_SQL_in_file($filePath)
            {
                global $wpdb;
                assert($wpdb, "Wp DB Not initialized error");
                $dbName = DB_NAME;
                $EOL = "</br>\n";
                $escape = function ($value) {
                    if (is_null($value)) {
                        return "NULL";
                    }
                    return "'" . esc_sql($value) . "'";
                };
                $wa_backup_sql = function () use($dbName, $EOL, $wpdb, $filePath, $escape) {
                    $tablePrefix = "";
                    $exclude_tables = [];
                    $sql = "SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE' AND Tables_in_{$dbName} LIKE '{$tablePrefix}%'";
                    $tables = $wpdb->get_results($sql);
                    $tables_list = array();
                    foreach ($tables as $table_row) {
                        $table_row = (array) $table_row;
                        $table_name = array_shift($table_row);
                        if (!in_array($table_name, $exclude_tables)) {
                            $tables_list[] = $table_name;
                        }
                    }
                    $dump_table = function ($dump_file, $table, $eol) use($wpdb, $escape) {
                        $INSERT_THRESHOLD = 838860;
                        $dump_file->write("DROP TABLE IF EXISTS `{$table}`;{$eol}");
                        $create_table = $wpdb->get_results('SHOW CREATE TABLE `' . $table . '`');
                        $create_table_sql = ((array) $create_table[0])['Create Table'] . ';';
                        $dump_file->write($create_table_sql . $eol . $eol);
                        $data = $wpdb->get_results("SELECT * FROM `{$table}`");
                        $insert = new InsertSqlStatement($table);
                        foreach ($data as $row) {
                            $row_values = array();
                            foreach ((array) $row as $value) {
                                $row_values[] = $escape($value);
                            }
                            $insert->add_row($row_values);
                            if ($insert->get_length() > $INSERT_THRESHOLD) {
                                $dump_file->write($insert->get_sql() . $eol);
                                $insert->reset();
                            }
                        }
                        $sql = $insert->get_sql();
                        if ($sql) {
                            $dump_file->write($insert->get_sql() . $eol);
                        }
                        $dump_file->write($eol . $eol);
                    };
                    if (preg_match('/\\.sql\\.gz$/', $filePath) || preg_match('/\\.tar\\.gz$/', $filePath)) {
                        $dump_file = new DumpGzip($filePath);
                    } else {
                        $dump_file = new DumpPlainTxt($filePath);
                    }
                    $eol = "\r\n";
                    $dump_file->write("-- Generation time: " . date('r') . $eol);
                    $dump_file->write("-- Host: " . DB_HOST . $eol);
                    $dump_file->write("-- DB name: " . DB_NAME . $eol);
                    $dump_file->write("-- Backup tool author : wa-config, by Miguel Monwoo, service@monwoo.com" . $eol);
                    $dump_file->write("/*!40030 SET NAMES UTF8 */;{$eol}");
                    $dump_file->write("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;{$eol}");
                    $dump_file->write("/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;{$eol}");
                    $dump_file->write("/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;{$eol}");
                    $dump_file->write("/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;{$eol}");
                    $dump_file->write("/*!40103 SET TIME_ZONE='+00:00' */;{$eol}");
                    $dump_file->write("/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;{$eol}");
                    $dump_file->write("/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;{$eol}");
                    $dump_file->write("/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;{$eol}");
                    $dump_file->write("/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;{$eol}{$eol}");
                    foreach ($tables_list as $table) {
                        $dump_table($dump_file, $table, $eol);
                    }
                    $dump_file->write("{$eol}{$eol}");
                    $dump_file->write("/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;{$eol}");
                    $dump_file->write("/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;{$eol}");
                    $dump_file->write("/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;{$eol}");
                    $dump_file->write("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;{$eol}");
                    $dump_file->write("/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;{$eol}");
                    $dump_file->write("/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;{$eol}");
                    $dump_file->write("/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;{$eol}{$eol}");
                    $dump_file->end();
                    $this->debug("Did backup SQL to :{$EOL}{$dump_file->file_location}");
                };
                $wa_backup_sql();
            }
            protected function _000_e2e_test__bootstrap()
            {
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                $maxTestDelay = 1000 * 60 * 15;
                if ($E2ETestsOptions['access-open'] ?? false) {
                    if (time() - $E2ETestsOptions['access-open'] > $maxTestDelay) {
                        $this->err("MANUAL RESET of e2e access-open, that sound buggy since open for more than 15 minutes");
                        $this->e2e_test_clean_and_restore_test_users();
                        update_option($this->eAdminConfigE2ETestsOptsKey, []);
                        return;
                    }
                    $default_opts = array('http' => array('header' => "wa-e2e-test-mode: wa-config-e2e-tests\r\n" . ""));
                    if ($this->shouldDebug) {
                        $default_opts["ssl"] = array("verify_peer" => false, "verify_peer_name" => false);
                        $default_opts["notification"] = [$this, "e2e_test_stream_notification_callback"];
                        add_filter('http_request_args', function ($args, $url) {
                            $args['headers']['wa-e2e-test-mode'] = 'wa-config-e2e-tests';
                            $args['sslverify'] = false;
                            return $args;
                        }, 100, 2);
                    }
                    $default = stream_context_set_default($default_opts);
                    if ('wa-config-e2e-tests' !== ($_SERVER['HTTP_WA_E2E_TEST_MODE'] ?? false)) {
                        $this->debug("Website under test mode, serving maintenance page for external access");
                        $this->debugVeryVerbose("Request headers :", array_filter($_SERVER, function ($v, $k) {
                            return substr($k, 0, 5) === 'HTTP_';
                        }, ARRAY_FILTER_USE_BOTH));
                        echo "<strong>Tests en cours, merci de revenir plus tard (15 minutes à 2 heures de délais). MAINTENANCE MODE, please come back later.</strong>";
                        wp_die();
                        return;
                    }
                }
            }
            public function e2e_test_stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
            {
                if (STREAM_NOTIFY_REDIRECTED === $notification_code) {
                    $this->debug("e2e_test_stream_notification Redirection vers : ", $message);
                } else {
                    $this->debug("e2e_test_stream_notification [{$notification_code}]");
                }
            }
            protected function _000_e2e_test__load()
            {
                wp_cache_delete("alloptions", "options");
                $E2ETestsOptions = get_option($this->eAdminConfigE2ETestsOptsKey, []);
                add_action('wp_ajax_nopriv_wa-e2e-test-action', [$this, 'e2e_test_action']);
                add_action('wp_ajax_wa-e2e-test-action', [$this, 'e2e_test_action']);
                if ($E2ETestsOptions['access-open'] ?? false) {
                    $dHandler = array($this, 'e2e_tests_filter_wp_die_callback');
                    add_filter('wp_die_ajax_handler', $dHandler, 100, 3);
                    add_filter('wp_die_json_handler', $dHandler, 100, 3);
                    add_filter('wp_die_jsonp_handler', $dHandler, 100, 3);
                    add_filter('wp_die_xmlrpc_handler', $dHandler, 100, 3);
                    add_filter('wp_die_xml_handler', $dHandler, 100, 3);
                    add_filter('wp_die_handler', $dHandler, 100, 3);
                    add_filter('wp_mail', [$this, 'e2e_tests_emails_middleware'], 10, 1);
                }
            }
            public function isFirstMethodCall(string $methodeName)
            {
                $isFirstCall = key_exists($methodeName, self::$_methodes) ? self::$_methodes[$methodeName][self::$_statsCountKey] === 0 : true;
                if ($isFirstCall) {
                    $this->debugVeryVerbose("First call by {$this->iId} for {$methodeName}");
                }
                return $isFirstCall;
            }
            public function methodeCalledFrom(string $methodeName) : void
            {
                $iId = $this->iId;
                if (!key_exists($methodeName, self::$_methodes)) {
                    self::$_methodes[$methodeName] = [];
                }
                $statistics =& self::$_methodes[$methodeName];
                if (!key_exists(self::$_statsCountKey, $statistics)) {
                    $statistics[self::$_statsCountKey] = 0;
                }
                $statistics[self::$_statsCountKey]++;
                if (!key_exists($iId, $statistics)) {
                    $statistics[$iId] = 0;
                }
                $statistics[$iId]++;
                $this->debugVeryVerbose("Methode statistics after {$methodeName} call", self::$_methodes);
            }
            public function __construct(string $iPrefix)
            {
                add_action('admin_notices', [new Notice(), Notice::class . "::displayNotices"]);
                $this->iPrefix = $iPrefix;
                $lastInstance = self::instanceByRelativePath($this->pluginRelativePath, -1);
                if (!$lastInstance) {
                }
                self::addInstance($this);
            }
            public function bootstrap() : void
            {
                global $wp;
                $this->debug("Bootstraping plugin\n");
                if ($_SERVER && isset($_SERVER['SERVER_PORT'])) {
                    $protocole = ($_SERVER["HTTPS"] == "on" ? "https" : "http") ?? "http";
                    $domain = $_SERVER['HTTP_HOST'];
                    if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
                        $domain .= ":" . $_SERVER["SERVER_PORT"];
                    }
                    $uri = $_SERVER['REQUEST_URI'];
                    $url = "{$protocole}://{$domain}{$uri}";
                    $this->debug("\n\nFrom :\n {$url}");
                } else {
                    global $argv;
                    $this->debug("\n\nFrom :\n {$argv[0]}");
                }
                $methods = get_class_methods($this);
                foreach ($methods as $m) {
                    if (strEndsWith($m, "__bootstrap")) {
                        $this->{$m}();
                        $this->debugVeryVerbose("Will bootstrap with: {$m}");
                    }
                }
                add_action('plugins_loaded', [$this, 'loadPlugin'], 11);
                $this->debugVerbose("Did bootstrap plugin");
            }
            public function loadPlugin() : void
            {
                $this->debug("Loading plugin from action 'plugins_loaded'");
                $methods = get_class_methods($this);
                foreach ($methods as $m) {
                    if (strEndsWith($m, "__load")) {
                        $this->debugVeryVerbose("Will Init with: {$m}");
                        $this->{$m}();
                    }
                }
                $this->debugVerbose("Did init plugin");
            }
        }
    }
}
namespace WA\Config\Utils {
    use Walker_Nav_Menu_Checklist;
    if (!function_exists(strEndsWith::class)) {
        function strEndsWith($haystack, $needle)
        {
            $length = strlen($needle);
            if (!$length) {
                return true;
            }
            return substr($haystack, -$length) === $needle;
        }
    }
    if (!trait_exists(PdfToHTMLable::class)) {
        trait PdfToHTMLable
        {
            protected function _010_pdfAble_scripts__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_pdfAble_scripts__load')) {
                    return;
                }
            }
            public function pdfAble_scripts_do_enqueue() : void
            {
                $this->debugVerbose("Will pdfAble_scripts_do_enqueue");
                $jsFile = "assets/pdfjs/build/pdf.js";
                add_filter('script_loader_tag', [$this, 'apdfAble_scripts_tag'], 10, 3);
                wp_enqueue_script('wa-config-pdf-to-html-js', plugins_url($jsFile, $this->pluginFile), [], $this->pluginVersion, true);
            }
            public function apdfAble_scripts_tag($tag, $handle, $source)
            {
                if ('wa-config-pdf-to-html-js' === $handle) {
                    ob_start();
                    echo <<<TEMPLATE
    <script
    type="text/javascript"
    src="{$source}"
    id="{$handle}"
    async
    ></script>
TEMPLATE;
                    $tag = ob_get_clean();
                    $this->debugVerbose("apdfAble_scripts_tag", $tag);
                } else {
                    $this->debugVerbose("script_loader_tag {$handle}");
                }
                return $tag;
            }
        }
    }
    if (!class_exists(DumpInterface::class)) {
        abstract class DumpInterface
        {
            abstract function open();
            abstract function write($string);
            abstract function end();
            function __construct($file)
            {
                $this->file_location = $file;
                $this->fh = $this->open();
                if (!$this->fh) {
                    throw new \Exception("Couldn't create DUMP file {$file}");
                }
            }
        }
    }
    if (!class_exists(DumpPlainTxt::class)) {
        class DumpPlainTxt extends DumpInterface
        {
            function open()
            {
                return fopen($this->file_location, 'w');
            }
            function write($string)
            {
                return fwrite($this->fh, $string);
            }
            function end()
            {
                return fclose($this->fh);
            }
        }
    }
    if (!class_exists(DumpGzip::class)) {
        class DumpGzip extends DumpInterface
        {
            function open()
            {
                return gzopen($this->file_location, 'wb9');
            }
            function write($string)
            {
                return gzwrite($this->fh, $string);
            }
            function end()
            {
                return gzclose($this->fh);
            }
        }
    }
    if (!class_exists(InsertSqlStatement::class)) {
        class InsertSqlStatement
        {
            private $rows = array();
            private $length = 0;
            private $table;
            function __construct($table)
            {
                $this->table = $table;
            }
            function reset()
            {
                $this->rows = array();
                $this->length = 0;
            }
            function add_row($row)
            {
                $row = '(' . implode(",", $row) . ')';
                $this->rows[] = $row;
                $this->length += strlen($row);
            }
            function get_sql()
            {
                if (empty($this->rows)) {
                    return false;
                }
                return 'INSERT INTO `' . $this->table . '` VALUES ' . implode(",\n", $this->rows) . '; ';
            }
            function get_length()
            {
                return $this->length;
            }
        }
    }
    if (!trait_exists(TranslatableProduct::class)) {
        trait TranslatableProduct
        {
            protected function _010_t_product__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_t_product_post__load')) {
                    return;
                }
                if (function_exists('pll_count_posts')) {
                    add_filter('pll_get_post_types', [$this, 't_product_post_type_polylang_register'], 10, 2);
                    add_filter('pll_get_taxonomies', [$this, 't_product_category_taxo_polylang_register'], 10, 2);
                }
            }
            public function t_product_post_type_polylang_register($post_types, $is_settings)
            {
                $missionCptKey = 'product';
                if ($is_settings) {
                    unset($post_types[$missionCptKey]);
                } else {
                    $post_types[$missionCptKey] = $missionCptKey;
                }
                return $post_types;
            }
            public function t_product_category_taxo_polylang_register($taxonomies, $is_settings)
            {
                $productTaxoList = ['product_tag', 'product_shipping_class', 'product_type', 'product_visibility', 'product_cat'];
                foreach ($productTaxoList as $taxoKey) {
                    if ($is_settings) {
                        unset($taxonomies[$taxoKey]);
                    } else {
                        $taxonomies[$taxoKey] = $taxoKey;
                    }
                }
                return $taxonomies;
            }
        }
    }
}
namespace WA\Config\Admin {
    use PhpParser\Node\Stmt\Foreach_;
    use ReflectionClass;
    use SplPriorityQueue;
    use WA\Config\Core\AppInterface;
    use WA\Config\Core\Editable;
    use WA\Config\Core\EditableWaConfigOptions;
    use WA\Config\Core\Identifiable;
    use WA\Config\Core\Translatable;
    use WA\Config\Core\Parallelizable;
    use WA\Config\Core\WPActions;
    use WA\Config\Core\WPFilters;
    use WA\Config\Utils\PdfToHTMLable;
    use Walker_Nav_Menu_Checklist;
    use WP_Error;
    use WP_Filesystem_Direct;
    if (!class_exists(Notice::class)) {
        class Notice
        {
            const NOTICES_FIELD_ID = 'wa_config_admin_notices';
            public function displayNotices() : void
            {
                $notices = get_transient(self::NOTICES_FIELD_ID);
                if (!$notices) {
                    return;
                }
                foreach ($notices as $idx => $notice) {
                    $message = isset($notice['message']) ? $notice['message'] : false;
                    $noticeLevel = !empty($notice['notice-level']) ? $notice['notice-level'] : 'notice-error';
                    if ($message) {
                        echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
                    }
                }
                delete_transient(self::NOTICES_FIELD_ID);
            }
            public static function displayError($message) : void
            {
                self::updateOption($message, 'notice-error');
            }
            public static function displayWarning($message)
            {
                self::updateOption($message, 'notice-warning');
            }
            public static function displayInfo($message)
            {
                self::updateOption($message, 'notice-info');
            }
            public static function displaySuccess($message)
            {
                self::updateOption($message, 'notice-success');
            }
            protected static function updateOption($message, $noticeLevel)
            {
                $notices = get_transient(self::NOTICES_FIELD_ID) ?? [];
                $notices[] = ['message' => $message, 'notice-level' => $noticeLevel];
                set_transient(self::NOTICES_FIELD_ID, $notices, 120);
            }
        }
    }
    if (!trait_exists(EditableAdminScripts::class)) {
        trait EditableAdminScripts
        {
            use Editable;
            protected function _010_e_admin_scripts__load()
            {
                if (!is_admin()) {
                    $this->debugVerbose("Will avoid _010_e_admin_scripts__load");
                    return;
                }
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_admin_scripts__load')) {
                    return;
                }
                add_action('admin_enqueue_scripts', [$this, 'e_admin_scripts_do_enqueue']);
            }
            public function e_admin_scripts_do_enqueue() : void
            {
                $this->debugVerbose("Will e_admin_scripts_do_enqueue");
                $cssFile = "assets/styles-admin.css";
                wp_enqueue_style('wa-config-css-admin', plugins_url($cssFile, $this->pluginFile), [], $this->pluginVersion);
                $jsFile = "assets/app.js";
                $jsUrl = plugins_url($jsFile, $this->pluginFile);
                wp_enqueue_script('wa-admin-js', $jsUrl, ['jquery', 'suggest'], $this->pluginVersion, true);
                $this->debugVerbose("Will e_admin_scripts_do_enqueue for ", get_current_screen()->id);
                if (false !== strpos(get_current_screen()->id, $this->eAdminConfigReviewPageKey)) {
                    wp_enqueue_style('thickbox');
                    wp_enqueue_script('plugin-install');
                }
            }
        }
    }
    if (!trait_exists(EditableMissionPost::class)) {
        trait OrderablePluginLoads
        {
            protected function _000_o_plugin_loads__bootstrap()
            {
                add_action('activated_plugin', [$this, 'o_plugin_loads_master_first']);
            }
            protected function _000_o_plugin_loads__load()
            {
            }
            public function o_plugin_loads_master_first() : void
            {
                $masterPlugin = get_option($this->oPluginLoadsMasterPathOptKey, '');
                if (strlen($masterPlugin) && ($plugins = get_option('active_plugins'))) {
                    if (false !== ($key = array_search($masterPlugin, $plugins))) {
                        array_splice($plugins, $key, 1);
                        array_unshift($plugins, $masterPlugin);
                        update_option('active_plugins', $plugins);
                    }
                }
            }
        }
    }
    if (!trait_exists(EditableMissionPost::class)) {
        trait EditableMissionPost
        {
            use Editable;
            protected function _010_e_mission_post__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_mission_post__load')) {
                    return;
                }
                add_action('init', [$this, 'e_mission_post_type_register']);
                add_action('get_the_date', [$this, 'e_mission_post_type_get_the_date'], 10, 3);
                if (function_exists('pll_count_posts')) {
                    add_filter('pll_get_post_types', [$this, 'e_mission_post_type_polylang_register'], 10, 2);
                }
                add_action('admin_head-nav-menus.php', 'e_mission_post_type_do_template_nav_menus');
                add_filter('wp_get_nav_menu_items', [$this, 'e_mission_post_type_do_template_nav_menus_filter'], 10, 3);
            }
            public function e_mission_post_type_do_template_nav_menus()
            {
                add_meta_box('wa_mission_do_template_nav_menus', __('Missions'), [$this, 'e_mission_post_type_do_template_nav_menu_metabox'], 'nav-menus', 'side', 'default');
            }
            public function e_mission_post_type_do_template_nav_menus_filter($items, $menu, $args)
            {
                foreach ($items as &$item) {
                    if ($item->object != 'cpt_archive') {
                        continue;
                    }
                    $item->url = get_post_type_archive_link($item->type);
                    if (get_query_var('post_type') == $item->type) {
                        $item->classes[] = 'current-menu-item';
                        $item->current = true;
                    }
                }
                return $items;
            }
            public function e_mission_post_type_do_template_nav_menu_metabox()
            {
                $missionCptKey = 'wa-mission';
                $post_types = get_post_types(array('show_in_nav_menus' => true, 'has_archive' => true), 'object');
                if ($post_types) {
                    foreach ($post_types as $post_type) {
                        $post_type->classes = array($post_type->name);
                        $post_type->type = $post_type->name;
                        $post_type->object_id = $post_type->name;
                        $post_type->title = $post_type->labels->name;
                        $post_type->object = $missionCptKey;
                    }
                    $walker = new Walker_Nav_Menu_Checklist(array());
                    ?>
                  <div id="wa-mission-menu" class="posttypediv">
                    <div id="tabs-panel-wa-mission" class="tabs-panel tabs-panel-active">
                      <ul id="wa-mission-checklist" class="categorychecklist form-no-clear"><?php 
                    echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $post_types), 0, (object) array('walker' => $walker));
                    ?>
                      </ul>
                    </div>
                  </div>
                  <p class="button-controls">
                    <span class="add-to-menu">
                      <input type="submit"<?php 
                    disabled($nav_menu_selected_id ?? null, 0);
                    ?> class="button-secondary submit-add-to-menu" value="<?php 
                    esc_attr_e('Add to Menu');
                    ?>" name="add-wa-mission-menu-item" id="submit-wa-mission-menu" />
                    </span>
                  </p><?php 
                }
            }
            public function e_mission_post_type_polylang_register($post_types, $is_settings)
            {
                $missionCptKey = 'wa-mission';
                if ($is_settings) {
                    unset($post_types[$missionCptKey]);
                } else {
                    $post_types[$missionCptKey] = $missionCptKey;
                }
                return $post_types;
            }
            public function e_mission_post_type_register() : void
            {
                $self = $this;
                $skillTaxoKey = 'wa-skill';
                $this->debugVerbose("Will e_mission_post_type_register");
                $missionCptKey = 'wa-mission';
                $permalink = _x('missions', 'wa-mission post slug (url SEO)', 'wa-config');
                $missionCpt = register_post_type($missionCptKey, ['label' => __('Missions', 'wa-config'), 'labels' => ['name' => __('Missions', 'wa-config'), 'singular_name' => __('Mission', 'wa-config'), 'all_items' => __('Les missions', 'wa-config'), 'add_new_item' => __('Ajouter une mission', 'wa-config'), 'edit_item' => __('Éditer la mission', 'wa-config'), 'new_item' => __('Nouvelle mission', 'wa-config'), 'view_item' => __('Voir la mission', 'wa-config'), 'search_items' => __('Rechercher parmi les missions', 'wa-config'), 'not_found' => __('Pas de mission trouvée', 'wa-config'), 'not_found_in_trash' => __('Pas de mission dans la corbeille', 'wa-config'), 'menu_name' => __('Missions', 'wa-config')], 'public' => true, 'delete_with_user' => false, 'supports' => ['title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'custom-fields'], 'can_export' => true, 'has_archive' => true, 'exclude_from_search' => false, 'publicly_queryable' => true, 'query_var' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'show_ui' => true, 'show_in_admin_bar' => true, 'show_in_menu' => false, 'menu_icon' => 'dashicons-clipboard', 'taxonomies' => [$skillTaxoKey], 'show_in_nav_menus' => true, 'map_meta_cap' => true, 'hierarchical' => false, 'rewrite' => ['slug' => $permalink, 'with_front' => false, 'feeds' => true], 'slug' => $permalink]);
                if (!function_exists(\add_submenu_page::class)) {
                    $this->warn("MISSING add_submenu_page, strange stuff to debug...");
                }
                if (is_admin() && function_exists(\add_submenu_page::class)) {
                    add_action('add_meta_boxes_' . $missionCptKey, [$this, 'e_mission_post_end_date_add_metabox']);
                    add_action("save_post_{$missionCptKey}", [$this, 'e_mission_post_end_date_save_metabox']);
                    add_filter("manage_{$missionCptKey}_posts_columns", [$this, 'e_mission_post_end_date_add_column']);
                    add_filter("manage_{$missionCptKey}_posts_custom_column", [$this, 'e_mission_post_end_date_render_column_row'], 10, 2);
                    add_action('quick_edit_custom_box', [$this, 'e_mission_post_end_date_quick_edit'], 10, 2);
                    add_action('admin_print_footer_scripts-edit.php', [$this, 'e_mission_post_end_date_quick_edit_js']);
                    \add_submenu_page($this->eAdminConfigPageKey, $missionCpt->labels->name, "<span class='dashicons {$missionCpt->menu_icon}'></span> " . $missionCpt->labels->menu_name, $missionCpt->cap->edit_posts, 'edit.php?post_type=' . $missionCptKey);
                    $my_cpt_parent_file = function ($parent_file) use($self, $missionCptKey, $skillTaxoKey) {
                        global $current_screen;
                        $self->debugVeryVerbose("Screen for parent_file : ", $current_screen);
                        if (in_array($current_screen->base, array('term', 'post-tags', 'edit-tags', 'post', 'edit')) && $missionCptKey == $current_screen->post_type) {
                            $parent_file = $self->eAdminConfigPageKey;
                        }
                        return $parent_file;
                    };
                    add_filter('parent_file', $my_cpt_parent_file);
                    $my_cpt_submenu_file = function ($submenu_file) use($self, $missionCptKey) {
                        global $current_screen;
                        if (in_array($current_screen->base, array('post-tags', 'edit-tags', 'post', 'edit')) && $missionCptKey == $current_screen->post_type) {
                            $self->debugVeryVerbose("POST TYPE : ", $current_screen);
                            if (strlen($current_screen->taxonomy ?? "")) {
                                $submenu_file = "edit-tags.php?post_type={$missionCptKey}" . "&taxonomy={$current_screen->taxonomy}";
                            } else {
                                $submenu_file = "edit.php?post_type={$missionCptKey}";
                            }
                            $self->debug("Sub menu file : ", $submenu_file);
                        }
                        return $submenu_file;
                    };
                    add_filter('submenu_file', $my_cpt_submenu_file);
                }
            }
            public function e_mission_post_type_get_the_date($the_date, $d, $post)
            {
                $missionCptKey = "wa-mission";
                if (is_int($post)) {
                    $post = get_post($post);
                }
                $post_id = $post->ID;
                if ($post->post_type === $missionCptKey) {
                    $val = get_post_meta($post_id, 'wa_end_date', true);
                    $this->debug("Will e_mission_post_type_get_the_date end date with ", $val);
                    return $the_date . ($val ? " - " . date($d, strtotime($val)) : "");
                }
                return $the_date;
            }
            public function e_mission_post_end_date_add_metabox($post) : void
            {
                $post_type = get_post_type($post);
                add_meta_box('end-date', __('Définir la date de fin', 'wa-config'), [$this, 'e_mission_post_end_date_render_metabox'], $post_type, 'side', 'high');
            }
            public function e_mission_post_end_date_save_metabox($post_ID) : void
            {
                if (isset($_POST['wa_mission_end_date'])) {
                    $endDate = esc_html($_POST['wa_mission_end_date']);
                    $this->debug("Will e_mission_post_end_date_save_metabox with end date at {$endDate}");
                    update_post_meta($post_ID, 'wa_end_date', $endDate);
                }
            }
            public function e_mission_post_end_date_render_metabox($post) : void
            {
                $val = $post->ID ?? false ? get_post_meta($post->ID, 'wa_end_date', true) : false;
                echo '<label>' . __('Date de fin', 'wa-config') . ' : </label>';
                echo '<input type="date" name="wa_mission_end_date"' . ($val ? " value='{$val}'" : "") . ' class="wa-mission-end-date" />';
            }
            public function e_mission_post_end_date_add_column($columns)
            {
                $columns['wa-end-date'] = __('Date de fin', 'wa-config');
                return $columns;
            }
            public function e_mission_post_end_date_render_column_row($column, $postId)
            {
                if ('wa-end-date' === $column) {
                    $endDate = get_post_meta($postId, 'wa_end_date', true);
                    $fmt = "Y-m-d";
                    echo $endDate ? date($fmt, strtotime($endDate)) : "";
                }
            }
            public function e_mission_post_end_date_quick_edit($column, $postType)
            {
                if ('wa-end-date' === $column) {
                    $this->e_mission_post_end_date_render_metabox(null);
                }
            }
            public function e_mission_post_end_date_quick_edit_js()
            {
                $missionCptKey = "wa-mission";
                $current_screen = get_current_screen();
                if ($current_screen->post_type === $missionCptKey) {
                    wp_enqueue_script('jquery');
                    ?>
                    <!-- add JS script -->
                    <script type="text/javascript">
                        jQuery(function($) {
                            // we create a copy of the WP inline edit post function
                            var $inline_editor = inlineEditPost.edit;
            
                            // Note: Hooking inlineEditPost.edit must be done in a JS script, loaded after wp-admin/js/inline-edit-post.js
                            // then we overwrite the inlineEditPost.edit function with our own code
                            inlineEditPost.edit = function(id) {
                                // call the original WP edit function 
                                $inline_editor.apply(this, arguments);
                                // ### start: add our custom functionality below  ###            
                                // get the post ID
                                var $post_id = 0;
                                if (typeof(id) == 'object') {
                                    $post_id = parseInt(this.getId(id));
                                }
                                // if we have our post
                                if ($post_id != 0) {
                                    // tips: use the inspecttion tool to help you see the HTML structure on the edit page.
            
                                    // explanation: 
                                    // On the posts management page, all posts will render inside the <tbody> along with "the-list" id.
                                    // Then each post will render on each <tr> along with "post-176" which 176 is my post ID. Your will be difference.
                                    // When the quick edit menu is clicked on the "post-176", the <tr> will be set as hide(display:none)
                                    // and the new <tr> along with "edit-176" id will be appended after <tr> which is hidden.
                                    // What we will do, we will use the jQuery to find the website value from the hidden <tr>. 
                                    // Get that value and assign to the website input field on the quick edit box.
                                    // 
                                    // The concept is the same when you create the inline editor by jQuery manually.
            
                                    // define the edit row
                                    var $edit_row = $('#edit-' + $post_id);
                                    var $post_row = $('#post-' + $post_id);
            
                                    // get the data
                                    var $endDate = $('.column-wa-end-date', $post_row).text();
                                    // console.log("WA END DATE", $endDate);
            
                                    // populate the data
                                    $(':input[name="wa_mission_end_date"]', $edit_row).val($endDate);
                                }
            
                                // ### end: add our custom functionality below  ###
                            }
            
                        });
                    </script>
                    <?php 
                }
            }
        }
    }
    if (!trait_exists(EditableSkillsTaxo::class)) {
        trait EditableSkillsTaxo
        {
            use Editable;
            protected function _011_e_skill_taxo__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_011_e_skill_taxo__load')) {
                    return;
                }
                add_action('init', [$this, 'e_skill_taxo_register_taxonomy']);
                add_action(WPActions::wa_do_base_review_preprocessing, [$this, 'e_skill_taxo_data_review']);
                if (function_exists('pll_count_posts')) {
                    add_filter('pll_get_taxonomies', [$this, 'e_skill_taxo_polylang_register'], 10, 2);
                }
            }
            public function e_skill_taxo_polylang_register($taxonomies, $is_settings)
            {
                $skillTaxoKey = 'wa-skill';
                if ($is_settings) {
                    unset($taxonomies[$skillTaxoKey]);
                } else {
                    $taxonomies[$skillTaxoKey] = $skillTaxoKey;
                }
                return $taxonomies;
            }
            public function e_skill_taxo_register_taxonomy() : void
            {
                $this->debugVerbose("Will e_skill_taxo_register_taxonomy");
                $labels = array('name' => _x('Expertises', 'taxonomy general name (plural)', 'wa-config'), 'singular_name' => _x('Expertise', 'taxonomy singular name', 'wa-config'), 'search_items' => __("Recherche d'expertises", 'wa-config'), 'all_items' => __('Toutes les expertises', 'wa-config'), 'parent_item' => __('Expertise parente', 'wa-config'), 'parent_item_colon' => __('Expertise parente:', 'wa-config'), 'edit_item' => __("Editer l'expertise", 'wa-config'), 'update_item' => __("Mettre à jour l'expertise", 'wa-config'), 'add_new_item' => __('Ajouter une nouvelle expertise', 'wa-config'), 'new_item_name' => __('Nom de la nouvelle expertise', 'wa-config'), 'menu_name' => _x('Expertise', 'taxonomy menu name', 'wa-config'));
                $args = array('public' => true, 'hierarchical' => true, 'labels' => $labels, 'show_ui' => true, 'show_in_rest' => true, 'show_admin_column' => true, 'can_export' => true, 'has_archive' => true, 'exclude_from_search' => false, 'publicly_queryable' => true, 'query_var' => true, 'rewrite' => ['slug' => _x('expertises', 'wa-skill taxonomy slug (url SEO)', 'wa-config')], 'show_in_nav_menus' => true, 'show_tagcloud' => true, 'menu_icon' => 'dashicons-welcome-learn-more', 'show_in_menu' => true);
                $missionCptKey = 'wa-mission';
                $skillTaxoKey = 'wa-skill';
                $taxo = register_taxonomy($skillTaxoKey, [$missionCptKey, 'user'], $args);
                if (is_admin() && function_exists(\add_submenu_page::class)) {
                    \add_submenu_page($this->eAdminConfigPageKey, $taxo->labels->name, "<span class='dashicons {$taxo->menu_icon}'></span> " . $taxo->labels->name, $taxo->cap->manage_terms, "edit-tags.php?post_type={$missionCptKey}&taxonomy={$skillTaxoKey}");
                }
            }
            public function e_skill_taxo_data_review($app) : void
            {
                $this->debugVerbose("Will e_skill_taxo_data_review");
                $skillsSyncOK = true;
                $reviewReport = '';
                $skillsSyncOK = $skillsSyncOK && ($frontendTerm = $this->e_skill_taxo_ensure_term($reviewReport, _x('Frontend', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise web frontend (UI, pages statiques, composant statiques)"), 'slug' => 'frontend')));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Svelte', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise Svelte)"), 'slug' => 'svelte', 'parent' => $frontendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Angular JS', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise Angular JS)"), 'slug' => 'angular-js', 'parent' => $frontendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Angular 2+', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise Angular 2+)"), 'slug' => 'angular-2-etc', 'parent' => $frontendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Vue JS', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise Vue JS)"), 'slug' => 'vue-js', 'parent' => $frontendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('React JS', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise React JS)"), 'slug' => 'react-js', 'parent' => $frontendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('WordPress Theme', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise en frontend WordPress)"), 'slug' => 'wordpress-frontend', 'parent' => $frontendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($backendTerm = $this->e_skill_taxo_ensure_term($reviewReport, _x('Backend', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise web backend (SEO, pages dynamiques, données dynamiques)"), 'slug' => 'backend')));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('WordPress Plugin', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise en backend WordPress)"), 'slug' => 'wordpress-plugin', 'parent' => $backendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Symfony', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise WordPress)"), 'slug' => 'symfony', 'parent' => $backendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Laravel', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise Laravel)"), 'slug' => 'laravel', 'parent' => $backendTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($rEtDTerm = $this->e_skill_taxo_ensure_term($reviewReport, _x('Recherche et développement', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise de recherche et développement (R&D)"), 'slug' => __('r-et-d'))));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('POC', 'wa-skill term'), 'wa-skill', array('description' => __("Proof of concept (Preuve de conception tangible)"), 'slug' => __('poc'), 'parent' => $rEtDTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Etude technique', 'wa-skill term'), 'wa-skill', array('description' => __("Présentation des résultats de veille technologique plus ou moins longues"), 'slug' => __('etude-technique'), 'parent' => $rEtDTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Publication Open Source', 'wa-skill term'), 'wa-skill', array('description' => __("Publication des résultats et outils de mise en oeuvre pour un domaine public ciblée."), 'slug' => __('publication-open-source'), 'parent' => $rEtDTerm['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($healthCare = $this->e_skill_taxo_ensure_term($reviewReport, _x('Bien être', 'wa-skill term', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise en bien-être"), 'slug' => __('health-care'))));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Bien être physiologique', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise en bien être de l'activité de l'organisme humain."), 'slug' => __('physiological-health-care'), 'parent' => $healthCare['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Bien être des relations humaines', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise en bien être relationnel. Team building etc..."), 'slug' => __('relationship-health-care'), 'parent' => $healthCare['term_id'])));
                $skillsSyncOK = $skillsSyncOK && ($term = $this->e_skill_taxo_ensure_term($reviewReport, _x('Bien être organisationnel', 'wa-skill term'), 'wa-skill', array('description' => __("Expertise organisationnel pour se sentir bien ou améliorer un bien être relationnel ."), 'slug' => __('organisational-health-care'), 'parent' => $healthCare['term_id'])));
                $this->e_admin_config_add_check_list_to_review(['category' => __('02 - Maintenance', 'wa-config'), 'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>', 'title' => __("02 - [wa-skill] Contrôle des données", 'wa-config'), 'title_icon' => '<span class="dashicons dashicons-dashboard"></span>', 'requirements' => __('[wa-skill] Vérification de la présence des expertises de base.<br />', 'wa-config') . $reviewReport, 'value' => strlen($reviewReport) ? $skillsSyncOK ? __('Les expertises sont définies même si certaines différent.') : __('Supprimez les terms wa-skill basique puis rafraichir cette page.') : '', 'result' => $skillsSyncOK, 'is_activated' => true, 'fixed_id' => "{$this->iId}-data-review-taxo-terms-for-wa-skill", 'is_computed' => true]);
            }
            function e_skill_taxo_ensure_term(&$reviewReport, $term, $taxonomy, $args = array())
            {
                $this->debug("Will ensure existance of wa-skill term {$term}");
                $termInstance = get_term_by('slug', $args['slug'], $taxonomy, ARRAY_A);
                $this->debugVeryVerbose("Saved term : ", $termInstance, $term, $taxonomy);
                if ($termInstance) {
                    $haveDiffs = [];
                    $term = esc_attr($term);
                    $taxonomy = esc_attr($taxonomy);
                    $args['description'] = wp_unslash(esc_html($args['description']));
                    $args['description'] = str_replace("&#039;", "'", $args['description']);
                    if ($term !== $termInstance['name']) {
                        $haveDiffs[] = htmlentities("{$term}\n<>\n{$termInstance['name']}");
                    }
                    if ($taxonomy !== $termInstance['taxonomy']) {
                        $haveDiffs[] = htmlentities("{$taxonomy}\n<>\n{$termInstance['taxonomy']}");
                    }
                    if ($args['description'] !== $termInstance['description']) {
                        $haveDiffs[] = htmlentities("{$args['description']}\n<>\n{$termInstance['description']}");
                    }
                    if ($args['slug'] !== $termInstance['slug']) {
                        $haveDiffs[] = htmlentities("{$args['slug']}\n<>\n{$termInstance['slug']}");
                    }
                    if (count($haveDiffs)) {
                        $reviewReport .= __("<p> L'expertise '{$term}' est différente de la version du plugin : <pre style='overflow:scroll'>\n" . implode(htmlentities("\n && \n"), $haveDiffs) . "\n</pre></p>");
                    }
                    $this->debug("No need to add term {$term} for {$taxonomy} taxonomy since already registred, return loaded one, differ : " . count($haveDiffs));
                    return $termInstance;
                } else {
                    $termInstance = wp_insert_term($term, $taxonomy, $args);
                    if (is_wp_error($termInstance)) {
                        $err = $termInstance;
                        $this->err("Fail to add term {$term} for {$taxonomy} taxonomy : " . $err->get_error_message());
                        $reviewReport .= __("<p>L'ajout de l'expertise '{$term}' a échoué : " . $err->get_error_message() . " </p>");
                        return false;
                    }
                }
                if (is_a($termInstance, WP_Error::class) || !count($termInstance)) {
                    $reviewReport .= __("<p> Echec de l'ajout de l'expertise '{$term}', réponse vide.</p>");
                    $this->err("Fail to add term {$term} for {$taxonomy} taxonomy", $termInstance);
                    return false;
                }
                return $termInstance;
            }
        }
    }
    if (!trait_exists(EditableConfigPanels::class)) {
        trait EditableConfigPanels
        {
            use Identifiable, Translatable, Editable, EditableWaConfigOptions, EditableAdminScripts, Parallelizable, PdfToHTMLable;
            protected function _010_e_admin_config__bootstrap()
            {
                $this->eAdminConfigReviewOptsDefaults = [$this->eConfOptReviewCategory => "", $this->eConfOptReviewCategoryIcon => "", $this->eConfOptReviewTitle => "", $this->eConfOptReviewTitleIcon => "", $this->eConfOptReviewRequirements => "", $this->eConfOptReviewResult => true, $this->eConfOptReviewValue => "", $this->eConfOptReviewIsActivated => true, $this->eConfOptReviewAccessCapOrRole => ""];
                add_action('activated_plugin', [$this, 'e_admin_config_on_plugins_activated'], 10, 2);
            }
            public function e_admin_config_on_plugins_activated($plugin, $network_wide)
            {
                $relativePath = basename(dirname($this->pluginFile)) . '/' . basename($this->pluginFile);
                if ($relativePath === $plugin) {
                    $this->debug("Will e_admin_config_on_plugins_activated for {$plugin}");
                    $this->e_admin_config_add_base_review();
                }
            }
            protected function _010_e_admin_config__load()
            {
                add_action('admin_menu', [$this, 'e_admin_config_do_admin_menu_review'], 20);
                add_action('admin_menu', [$this, 'e_admin_config_do_admin_menu_doc'], 20);
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_admin_config__load')) {
                    return;
                }
                add_action('admin_menu', [$this, 'e_admin_config_do_admin_menu']);
                add_action('admin_init', [$this, 'e_admin_config_do_admin_init']);
                add_action('wp_ajax_wa-list-capabilities-and-roles', [$this, 'e_admin_config_list_capabilities_and_roles']);
                add_action('wp_ajax_wa-list-review-data-by-key', [$this, 'e_admin_config_list_review_data_by_key']);
                add_action('wp_ajax_wa-review-action', [$this, 'e_admin_config_review_action']);
            }
            protected $baseCabability = 'edit_posts';
            protected $optAdminEditCabability = 'administrator';
            protected $optAdminReviewEditCabability = 'edit_posts';
            public function e_admin_config_do_admin_menu() : void
            {
                $this->debugVerbose("Will e_admin_config_do_admin_menu");
                add_menu_page(null, __('WA Config', 'wa-config'), $this->baseCabability, $this->eAdminConfigPageKey, '', plugins_url('assets/LogoWAConfig-21x21.png', $this->pluginFile), 7);
                $this->e_admin_config_add_section('<span class="dashicons dashicons-admin-generic"></span> ' . __('Paramètres', 'wa-config'), [$this, 'e_admin_config_render_param_section'], $this->eAdminConfigParamPageKey, 10, $this->baseCabability);
            }
            public function e_admin_config_do_admin_menu_review() : void
            {
                $suffix = $this->iIndex ? "-{$this->iIndex}" : "";
                $titleSuffix = $this->iIndex ? " {$this->iIndex}" : "";
                $this->e_admin_config_add_section('<span class="dashicons dashicons-performance"></span> ' . __('Revue qualité', 'wa-config') . "{$titleSuffix}", [$this, 'e_admin_config_render_review_section'], "{$this->eAdminConfigReviewPageKey}{$suffix}", 20 + $this->iIndex, $this->baseCabability);
            }
            public function e_admin_config_do_admin_menu_doc() : void
            {
                $suffix = $this->iIndex ? "-{$this->iIndex}" : "";
                $titleSuffix = $this->iIndex ? " {$this->iIndex}" : "";
                $this->e_admin_config_add_section('<span class="dashicons dashicons-code-standards"></span> ' . __('Documentation', 'wa-config') . "{$titleSuffix}", [$this, 'e_admin_config_render_doc_section'], "{$this->eAdminConfigDocPageKey}{$suffix}", 30 + $this->iIndex, $this->baseCabability);
            }
            public function e_admin_config_do_admin_init() : void
            {
                if (!is_admin()) {
                    $this->err("e_admin_config_do_admin_init should be for admin call only");
                    return;
                }
                $self = $this;
                $this->debugVerbose("Will e_admin_config_do_admin_init");
                $this->eAdminConfigReviewOpts = $this->eAdminConfigReviewOptsDefaults;
                $this->eAdminConfigReviewOpts = get_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                $this->debugVeryVerbose("Admin init WA Review options", $this->eAdminConfigReviewOpts);
                $this->eACChecksByCategorieByTitle = $this->getReviewOption($this->eConfOptReviewsByCategorieByTitle, []);
                $pageId = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);
                $ajaxActionId = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
                if ($pageId === $this->eAdminConfigReviewPageKey || $ajaxActionId === 'wa-review-action') {
                    $this->e_admin_config_add_base_review();
                }
                register_setting($this->eAdminConfigOptsGroupKey, $this->eAdminConfigOptsKey, [$this, 'e_admin_config_opts_validate']);
                add_settings_section($this->eAdminConfigParamSettingsKey, __('Paramètres', 'wa-config'), '', $this->eAdminConfigParamPageKey);
                register_setting($this->eAdminConfigOptsReviewGroupKey, $this->eAdminConfigReviewOptsKey, [$this, 'e_admin_config_opts_review_validate']);
                add_settings_section($this->eAdminConfigReviewSettingsKey, __('Ajouter une revue', 'wa-config'), '', $this->eAdminConfigReviewPageKey);
                add_filter("pre_update_option_{$this->eAdminConfigReviewOptsKey}", [$this, "e_admin_config_pre_update_review_filter"], 10, 3);
                add_filter("option_page_capability_{$this->eAdminConfigOptsReviewGroupKey}", [$this, "e_admin_config_review_option_page_capability"]);
                $checkboxTemplate = function ($safeValue, $fieldId, $fieldName) {
                    $checked = boolval($safeValue) ? 'checked' : '';
                    $value = $checked ? '1' : '0';
                    return <<<TEMPLATE
<div>
    <input
    type="checkbox"
    class="wppd-ui-toggle wa-checkbox"
    id="{$fieldId}"
    name="{$fieldName}"
    value="{$value}"
    {$checked}
    ></input>
</div>
TEMPLATE;
                };
                $textareaTemplate = function ($safeValue, $fieldId, $fieldName) {
                    return <<<TEMPLATE
    <textarea
    rows="5"
    class="wa-review-textarea-{$fieldId}"
    id="{$fieldId}"
    name="{$fieldName}"
    >{$safeValue}</textarea>
TEMPLATE;
                };
                $multilLangTemplate = function ($safeValue, $fieldId, $fieldName) {
                    $locals = [get_locale()];
                    if (function_exists('pll_languages_list')) {
                        $pllLangs = pll_languages_list(array('fields' => array()));
                        $locals = array_map(function ($l) {
                            return $l[''];
                        }, $pllLangs);
                    }
                    $t = "";
                    foreach ($locals as $l) {
                        $t .= <<<TEMPLATE
<input id='{$fieldId}-{$l}' type='text'
name='{$fieldName}-{$l}'
value='{$safeValue}'
/>
TEMPLATE;
                    }
                    return $t;
                };
                $oLvls = implode(",", (new ReflectionClass(OptiLvl::class))->getConstants());
                if (current_user_can($this->optAdminEditCabability)) {
                    $this->e_admin_menu_add_param_field($this->eConfOptEnableFooter, __("Activer le bas de page", 'wa-config'), true, $checkboxTemplate);
                    $this->e_admin_menu_add_param_field($this->eConfOptFooterCredit, __("Copyright de bas de page", 'wa-config'), __("autre", 'wa-config'));
                    $this->e_admin_menu_add_param_field($this->eConfOptFooterTemplate, __("Template de bas de page", 'wa-config'), "");
                    $this->e_admin_menu_add_param_field($this->eConfOptOptiLevels, __("Axes d'optimisation", 'wa-config') . " ({$oLvls})", "");
                    $this->e_admin_menu_add_param_field($this->eConfOptOptiWpRequestsFilter, __("RegEx pour bloquer les requêtes HTTP interne (Ex : /.*/)", 'wa-config'), "");
                    $this->e_admin_menu_add_param_field($this->eConfOptOptiWpRequestsSafeFilter, __('RegEx pour autoriser les requêtes HTTP interne (Ex : $wordpress.org$)', 'wa-config'), $this->E_DEFAULT_OPTIMISABLE_SAFE_FILTER);
                    $this->e_admin_menu_add_param_field($this->eConfOptOptiEnableBlockedHttpNotice, __("Notifier les requêttes HTTP bloquées", 'wa-config'), false, $checkboxTemplate);
                    $this->e_admin_menu_add_param_field($this->eConfOptATestsBaseUrl, __("Url de base pour les tests d'acceptance", 'wa-config'), site_url());
                    $this->e_admin_menu_add_param_field($this->eConfOptATestsUsers, __("Liste des utilisateurs de test", 'wa-config'), $this->E_DEFAULT_A_TESTS_USERS_LIST);
                    $this->e_admin_menu_add_param_field($this->eConfOptATestsRunForCabability, __("Capacité pour lancer les tests", 'wa-config'), 'administrator', [$this, 'e_admin_config_capability_suggestionbox_template']);
                }
                add_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                if (current_user_can($this->optAdminReviewEditCabability)) {
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewCategory, __("Categorie", 'wa-config'), $this->eACDefaultCheckpoint['category'], [$this, 'e_admin_config_review_data_by_key_suggestionbox_template'], 'category');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewCategoryIcon, __("Icône de categorie", 'wa-config'), $this->eACDefaultCheckpoint['category_icon'], [$this, 'e_admin_config_review_data_by_key_suggestionbox_template'], 'category_icon');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewTitle, __("Titre", 'wa-config'), $this->eACDefaultCheckpoint['title'], [$this, 'e_admin_config_review_data_by_key_suggestionbox_template'], 'title');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewTitleIcon, __("Icône de titre", 'wa-config'), $this->eACDefaultCheckpoint['title_icon'], [$this, 'e_admin_config_review_data_by_key_suggestionbox_template'], 'title_icon');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewRequirements, __("Exigences", 'wa-config'), $this->eACDefaultCheckpoint['requirements'], $textareaTemplate, 'requirements');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewResult, __("Résultat", 'wa-config'), $this->eACDefaultCheckpoint['result'], $checkboxTemplate, 'result');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewValue, __("Valeur (optionnel)", 'wa-config'), $this->eACDefaultCheckpoint['value'], [$this, 'e_admin_config_review_data_by_key_suggestionbox_template'], 'value');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewIsActivated, __("Activer la revue", 'wa-config'), $this->eACDefaultCheckpoint['is_activated'], $checkboxTemplate, 'is_activated');
                    $this->e_admin_menu_add_review_field($this->eConfOptReviewAccessCapOrRole, __("Limiter l'accès", 'wa-config'), $this->eACDefaultCheckpoint['access_cap_or_role'], [$this, 'e_admin_config_capability_selectbox_template'], 'access_cap_or_role');
                }
            }
            protected $_capAndRolesCacheKey = 'wa_config_admin_capabilities_and_roles';
            protected $_capAndRolesCache = null;
            protected function e_admin_config_get_capabilities_and_roles()
            {
                delete_transient($this->_capAndRolesCacheKey);
                if ($this->_capAndRolesCache || ($this->_capAndRolesCache = get_transient($this->_capAndRolesCacheKey))) {
                    return clone $this->_capAndRolesCache;
                }
                $capAndRoles = new class extends SplPriorityQueue
                {
                    public function compare($priority1, $priority2) : int
                    {
                        return strnatcasecmp(strval($priority2), strval($priority1));
                    }
                    public function __serialize()
                    {
                        $clone = clone $this;
                        $data = [];
                        foreach ($clone as $item) {
                            $data[] = $item;
                        }
                        return $data;
                    }
                    public function __unserialize($data)
                    {
                        foreach ($data as $item) {
                            $this->insert($item['data'], $item['priority']);
                        }
                    }
                };
                $capAndRoles->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
                global $wp_roles;
                $checkDuplicates = [];
                $capAndRoles = array_reduce(array_chunk($wp_roles->roles, 1, true), function (SplPriorityQueue $cAndR, $roleDataChunk) use(&$checkDuplicates) {
                    $r = key($roleDataChunk);
                    $rData = current($roleDataChunk);
                    if (!array_key_exists($r, $checkDuplicates)) {
                        $cAndR->insert("--{$r}--", $r);
                        $checkDuplicates[$r] = null;
                    }
                    array_reduce(array_keys($rData['capabilities']), function (SplPriorityQueue $cAndR, $c) use(&$checkDuplicates) {
                        if (!array_key_exists($c, $checkDuplicates)) {
                            $cAndR->insert($c, $c);
                            $checkDuplicates[$c] = null;
                        }
                        return $cAndR;
                    }, $cAndR);
                    return $cAndR;
                }, $capAndRoles);
                $this->_capAndRolesCache = $capAndRoles;
                set_transient($this->_capAndRolesCacheKey, $this->_capAndRolesCache, 15 * 60);
                return clone $this->_capAndRolesCache;
            }
            protected $_capAndRolesSearchCacheKey = 'wa_config_admin_capabilities_and_roles_search';
            protected $_capAndRolesSearchCache = null;
            public function e_admin_config_list_capabilities_and_roles()
            {
                if (!is_admin()) {
                    $this->err("wa-config admin param section is under admin pages only");
                    echo "<p> " . __("Cette opération nécessite une page d'administration.", 'wa-config') . "</p>";
                    return;
                }
                $query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);
                $query = wp_unslash($query);
                if (!$this->_capAndRolesSearchCache) {
                    $this->_capAndRolesSearchCache = get_transient($this->_capAndRolesSearchCacheKey);
                }
                if (!$this->_capAndRolesSearchCache) {
                    $this->_capAndRolesSearchCache = [];
                }
                if (array_key_exists($query, $this->_capAndRolesSearchCache)) {
                    $this->debug("e_admin_config_list_capabilities_and_roles loaded from cache [{$query}]");
                    echo $this->_capAndRolesSearchCache[$query];
                    wp_die();
                    return;
                }
                $capAndRoles = $this->e_admin_config_get_capabilities_and_roles();
                $isFirstMatch = true;
                $searchResult = '';
                while ($capAndRoles->count() && (['data' => $d, 'priority' => $p] = $capAndRoles->extract())) {
                    if (strpos(strtolower($p), strtolower($query)) === false) {
                        continue;
                    }
                    if ($isFirstMatch) {
                        $isFirstMatch = false;
                    } else {
                        $searchResult .= "\n";
                    }
                    $searchResult .= $p;
                }
                $searchResult .= "\n";
                $this->_capAndRolesSearchCache[$query] = $searchResult;
                set_transient($this->_capAndRolesSearchCacheKey, $this->_capAndRolesSearchCache, 24 * 60 * 60);
                echo $searchResult;
                wp_die();
                return;
            }
            protected function e_admin_config_capability_suggestionbox_template($safeValue, $fieldId, $fieldName, $placeholder = "")
            {
                return <<<TEMPLATE
    <input
    type="text"
    placeholder="{$placeholder}"
    class="wa-suggest-capabilities-and-roles"
    id="{$fieldId}"
    name="{$fieldName}"
    value="{$safeValue}"
    />
TEMPLATE;
            }
            protected function e_admin_config_capability_selectbox_template($safeValue, $fieldId, $fieldName, $placeholder = "")
            {
                $options = '<option value = "" >' . __("Non définit.", 'wa-config') . '</option>';
                $capAndRoles = $this->e_admin_config_get_capabilities_and_roles();
                while ($capAndRoles->count() && (['data' => $d, 'priority' => $p] = $capAndRoles->extract())) {
                    $options .= <<<TEMPLATE
    <option value="{$p}">{$d}</option>
TEMPLATE;
                }
                return <<<TEMPLATE
    <select
    class="wa-selectbox-capabilities-and-roles"
    id="{$fieldId}"
    name="{$fieldName}"
    value="{$safeValue}"
    >
        {$options}
    </select>
TEMPLATE;
            }
            public function e_admin_config_opts_validate($input)
            {
                $newinput = $input;
                $this->debugVerbose("Will e_admin_config_opts_validate");
                $regExKey = $this->eConfOptOptiWpRequestsFilter;
                $newinput[$regExKey] = trim($input[$regExKey]);
                $regExKeySafe = $this->eConfOptOptiWpRequestsSafeFilter;
                $newinput[$regExKeySafe] = trim($input[$regExKeySafe]);
                $eConf = error_reporting(E_ALL);
                ob_start();
                $pMatch = preg_match($newinput[$regExKey], 'hello');
                $noticeErr = ob_get_clean();
                ob_start();
                $pMatchSafe = preg_match($newinput[$regExKeySafe], 'hello');
                $noticeErrSafe = ob_get_clean();
                error_reporting($eConf);
                if (strlen($newinput[$regExKey]) && false === $pMatch) {
                    Notice::displayError("" . __("'RegEx pour bloquer les requêtes HTTP interne' NON VALIDE :", 'wa-config') . "<br />\n{$newinput[$regExKey]}<br />\n" . $noticeErr);
                    $newinput[$regExKey] = '';
                }
                $newinput[$regExKeySafe] = trim($input[$regExKeySafe]);
                if (strlen($newinput[$regExKeySafe]) && false === $pMatchSafe) {
                    Notice::displayError("" . __("'RegEx pour autoriser les requêtes HTTP interne' NON VALIDE :", 'wa-config') . "<br />\n{$newinput[$regExKeySafe]}<br />\n" . $noticeErrSafe);
                    $newinput[$regExKeySafe] = '';
                }
                Notice::displaySuccess(__('Enregistrement OK.', 'wa-config'));
                return $newinput;
            }
            protected function e_admin_config_add_section($title, $renderClbck, $newPageKey, $position = 1, $capability = null) : void
            {
                $this->debugVerbose("Will e_admin_config_add_section {$newPageKey}");
                $capability = $capability ?? $this->baseCabability;
                $parentPageKey = $this->eAdminConfigPageKey;
                \add_submenu_page($parentPageKey, $title, $title, $capability, $newPageKey, $renderClbck, $position);
                $this->debugVerbose("Did e_admin_config_add_section {$newPageKey}");
            }
            protected function e_admin_menu_add_param_field($key, $title, $default = '', $template = null) : void
            {
                $this->debugVeryVerbose("Will e_admin_menu_add_param_field");
                $fieldId = "{$this->eAdminConfigOptsKey}_{$key}";
                $fieldName = "{$this->eAdminConfigOptsKey}[{$key}]";
                $value = $this->getWaConfigOption($key, $default);
                $safeValue = esc_attr($value);
                add_settings_field($fieldId, $title, function () use($safeValue, $fieldId, $fieldName, $template) {
                    if ($template) {
                        echo $template($safeValue, $fieldId, $fieldName);
                    } else {
                        echo <<<TEMPLATE
    <input id='{$fieldId}' type='text'
    name='{$fieldName}'
    value='{$safeValue}'
    />
TEMPLATE;
                    }
                }, $this->eAdminConfigParamPageKey, $this->eAdminConfigParamSettingsKey);
            }
            public function e_admin_config_render_param_section() : void
            {
                $self = $this;
                if (!is_admin()) {
                    $this->err("wa-config admin param section is under admin pages only");
                    echo "<p> " . __("Cette opération nécessite une page d'administration.", 'wa-config') . "</p>";
                    return;
                }
                $pluginTitle = __("Web-agency.app ") . $this->iId;
                $pluginDescription = __("Ce plugin permet d'<strong>optimiser</strong> la <strong>qualité</strong> de votre site web ainsi que les <strong>actions</strong> à mener pour votre <strong>processus métier</strong>.", 'wa-config');
                echo <<<TEMPLATE
    <h1>{$pluginTitle}</h1>
    <section>{$pluginDescription}</section>
TEMPLATE;
                if (!current_user_can($this->optAdminEditCabability)) {
                    $this->err("wa-config admin param need '{$this->optAdminEditCabability}' capability");
                    echo "<p> " . __("Pour plus d'informations, nécessite une capacité ou un rôle :", 'wa-config') . " {$this->optAdminEditCabability} </p>";
                    return;
                }
                $title = __('Paramètres', 'wa-config');
                $welcome = __('Ici, vous pouvez configurer tous les réglages généraux de wa-config ', 'wa-config') . " (" . AppInterface::PLUGIN_VERSION . ")";
                $formFields = function () use($self) {
                    ob_start();
                    settings_fields($self->eAdminConfigOptsGroupKey);
                    return ob_get_clean();
                };
                $sectionFormFields = function () use($self) {
                    ob_start();
                    do_settings_sections($self->eAdminConfigParamPageKey);
                    return ob_get_clean();
                };
                $submitBtn = function () {
                    ob_start();
                    submit_button();
                    return ob_get_clean();
                };
                $compatibilityReports = "";
                $compatibilityReportsData = AppInterface::getCompatibilityReports();
                foreach ($compatibilityReportsData as $report) {
                    $compatibilityReports .= "\n                        <p>\n                            <strong>{$report['level']}</strong> {$report['msg']}\n                        </p>\n                    ";
                }
                $UIDoc = __('wa-config.admin.panel.param.doc', 'wa-config');
                echo <<<TEMPLATE
    <div class="wrap">
        <h1>{$title}</h1>
        <p>{$welcome}</p>
        {$compatibilityReports}
        <div>{$UIDoc}</div>
        <form method="post" action="options.php"> 
            {$formFields()}
            {$sectionFormFields()}
            {$submitBtn()}
        </form>
    </div>
TEMPLATE;
                $app = $this;
                do_action(WPActions::wa_ac_render_after_parameters, $app);
            }
            protected $_reviewsByKeySearchCacheKey = 'wa_config_admin_review_by_key_search';
            protected $_reviewsByKeySearchCache = null;
            public function e_admin_config_list_review_data_by_key()
            {
                if (!is_admin()) {
                    $this->err("wa-config admin review section is under admin pages only");
                    echo "<p> " . __("Cette opération nécessite une page d'administration.", 'wa-config') . "</p>";
                    return;
                }
                $key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_SPECIAL_CHARS);
                $query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);
                $query = wp_unslash($query);
                if (!$this->_reviewsByKeySearchCache) {
                    $this->_reviewsByKeySearchCache = get_transient($this->_reviewsByKeySearchCacheKey);
                }
                if (!$this->_reviewsByKeySearchCache) {
                    $this->_reviewsByKeySearchCache = [];
                }
                if (!array_key_exists($key, $this->_reviewsByKeySearchCache)) {
                    $this->_reviewsByKeySearchCache[$key] = [];
                }
                if (array_key_exists($query, $this->_reviewsByKeySearchCache[$key])) {
                    $this->debug("e_admin_config_list_review_data_by_key loaded from cache [{$key}][{$query}]");
                    echo $this->_reviewsByKeySearchCache[$key][$query];
                    wp_die();
                    return;
                }
                $datas = [];
                foreach ($this->eACChecksByCategorieByTitle as $category => $checksByTitle) {
                    foreach ($checksByTitle as $title => $checks) {
                        foreach ($checks as $idx => $check) {
                            $datas[$check[$key]] = $check[$key];
                        }
                    }
                }
                usort($datas, 'strnatcasecmp');
                $isFirstMatch = true;
                $searchResult = '';
                foreach ($datas as $d) {
                    if (strpos(strtolower($d), strtolower($query)) === false) {
                        continue;
                    }
                    if ($isFirstMatch) {
                        $isFirstMatch = false;
                    } else {
                        $searchResult .= "\n";
                    }
                    $searchResult .= htmlentities($d);
                }
                $searchResult .= "\n";
                $this->_reviewsByKeySearchCache[$key][$query] = $searchResult;
                set_transient($this->_reviewsByKeySearchCacheKey, $this->_reviewsByKeySearchCache, 24 * 60 * 60);
                echo $searchResult;
                wp_die();
                return;
            }
            public function e_admin_config_opts_review_validate($input)
            {
                $newinput = $input;
                $this->debugVerbose("Will e_admin_config_opts_review_validate");
                $newinput[$this->eConfOptReviewResult] = boolval($newinput[$this->eConfOptReviewResult] ?? false);
                $newinput[$this->eConfOptReviewIsActivated] = boolval($newinput[$this->eConfOptReviewIsActivated] ?? false);
                return $newinput;
            }
            public function e_admin_config_review_option_page_capability($capability)
            {
                return $this->baseCabability;
            }
            protected $_eACPreUpdateReviewSelfSentinel = false;
            public function e_admin_config_pre_update_review_filter($value, $old_value, $option)
            {
                if (!is_admin()) {
                    $this->err("wa-config e_admin_config_pre_update_review_filter need admin page.");
                    echo "<p> " . __("Cette opérations nécessite une page admin", 'wa-config') . "</p>";
                    return;
                }
                if ($this->_eACPreUpdateReviewSelfSentinel) {
                    $this->warn('Sentinel still needed ? // TODO : refactor code to avoid _eACPreUpdateReviewSelfSentinel ?');
                    return $value;
                }
                $this->_eACPreUpdateReviewSelfSentinel = true;
                $this->debugVerbose("Will e_admin_config_pre_update_review_filter on {$option}");
                $this->debugVeryVerbose("e_admin_config_pre_update_review_filter From", $old_value, $value);
                if (!key_exists($this->eConfOptReviewsInternalPreUpdateAction, $value)) {
                    $checkpointValue = ['category' => $value[$this->eConfOptReviewCategory], 'category_icon' => $value[$this->eConfOptReviewCategoryIcon], 'title' => $value[$this->eConfOptReviewTitle], 'title_icon' => $value[$this->eConfOptReviewTitleIcon], 'requirements' => $value[$this->eConfOptReviewRequirements], 'value' => $value[$this->eConfOptReviewValue], 'result' => $value[$this->eConfOptReviewResult], 'access_cap_or_role' => $value[$this->eConfOptReviewAccessCapOrRole], 'is_activated' => $value[$this->eConfOptReviewIsActivated]];
                    $this->debugVeryVerbose("e_admin_config_pre_update_review_filter will add checkpoint", ['checkpoint' => $checkpointValue, 'value' => $value]);
                    if (!strlen($checkpointValue['category'])) {
                        $this->err("WRONG e_admin_config_pre_update_review_filter, missing eConfOptReviewsInternalPreUpdateAction param ? ");
                        $this->debug("WRONG value : ", $value, $this->debug_trace());
                        Notice::displayError(__("Echec de l'enregistrement de la revue.", 'wa-config'));
                        return $value;
                    }
                    $this->e_admin_config_add_check_list_to_review($checkpointValue);
                    $value[$this->eConfOptReviewCategory] = "";
                    $value[$this->eConfOptReviewCategoryIcon] = "";
                    $value[$this->eConfOptReviewTitle] = "";
                    $value[$this->eConfOptReviewTitleIcon] = "";
                    $value[$this->eConfOptReviewRequirements] = "";
                    $value[$this->eConfOptReviewValue] = "";
                    $value[$this->eConfOptReviewResult] = true;
                    $value[$this->eConfOptReviewAccessCapOrRole] = "";
                    $value[$this->eConfOptReviewIsActivated] = true;
                    $value[$this->eConfOptReviewsByCategorieByTitle] = $this->eACChecksByCategorieByTitle;
                    Notice::displaySuccess(__('Enregistrement de la revue OK.', 'wa-config'));
                } else {
                    $action = $value[$this->eConfOptReviewsInternalPreUpdateAction];
                    unset($value[$this->eConfOptReviewsInternalPreUpdateAction]);
                    $this->debugVerbose("Will e_admin_config_pre_update_review_filter for {$action}");
                }
                delete_transient($this->_reviewsByKeySearchCacheKey);
                $this->_reviewsByKeySearchCacheKey = null;
                $this->_eACPreUpdateReviewSelfSentinel = false;
                return $value;
            }
            protected $eAdminConfigReviewOptsDefaults = [];
            protected $eAdminConfigReviewOpts = [];
            protected function getReviewOption($key, $default)
            {
                $this->debugVeryVerbose("Will getReviewOption {$key}");
                $this->eAdminConfigReviewOpts = get_option($this->eAdminConfigReviewOptsKey, array_merge([$key => $default], $this->eAdminConfigReviewOpts));
                $this->assert(is_array($this->eAdminConfigReviewOpts), "Having wrong datatype saved for {$key}", $this->eAdminConfigReviewOpts);
                if (!key_exists($key, $this->eAdminConfigReviewOpts)) {
                    $this->eAdminConfigReviewOpts[$key] = $default;
                    $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = "getReviewOption";
                    update_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                }
                if ('wa_reviews_deleted' === $key) {
                    $this->debugVerbose("Missing key error ? for {$key}", ['trace' => $this->debug_trace(), 'reviewOpts' => $this->eAdminConfigReviewOpts]);
                }
                $value = $this->eAdminConfigReviewOpts[$key];
                $this->debugVeryVerbose("Did getReviewOption {$key}", $value);
                return $value;
            }
            protected $eACDefaultCheckpoint = ['category' => null, 'category_icon' => '', 'title' => null, 'title_icon' => '', 'requirements' => null, 'value' => null, 'result' => false, 'access_cap_or_role' => null, 'is_activated' => true, 'is_deleted' => false, 'fixed_id' => null, 'is_computed' => false, 'create_time' => null, 'created_by' => null, 'import_time' => null, 'imported_by' => null];
            protected $eACChecksByCategorieByTitle = [];
            protected $eACChecksByKeyId = [];
            protected $iconsByCategory = [];
            protected function e_admin_config_add_check_list_to_review(array $toCheck, $importMode = false)
            {
                if (!is_user_logged_in()) {
                    $this->err("wa-config e_admin_config_add_check_list_to_review is under logged users only");
                    wp_loginout();
                    wp_die();
                    return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $toCheck = array_merge($this->eACDefaultCheckpoint, $toCheck);
                if ($importMode) {
                    $toCheck['import_time'] = time();
                    $toCheck['imported_by'] = $userName;
                } else {
                    $toCheck['create_time'] = time();
                    $toCheck['created_by'] = $userName;
                }
                $keyId = $this->fetch_review_key_id($toCheck);
                if ("" === $toCheck['category']) {
                    $this->warn("Empty category should not happen ?", $this->debug_trace());
                }
                if (!array_key_exists($toCheck['category'], $this->eACChecksByCategorieByTitle)) {
                    $this->eACChecksByCategorieByTitle[$toCheck['category']] = [];
                }
                if (!array_key_exists($toCheck['title'], $this->eACChecksByCategorieByTitle[$toCheck['category']])) {
                    $this->eACChecksByCategorieByTitle[$toCheck['category']][$toCheck['title']] = [];
                }
                $checkBulk =& $this->eACChecksByCategorieByTitle[$toCheck['category']][$toCheck['title']];
                if ($targets = array_filter($checkBulk, function ($c) use(&$keyId) {
                    $cKey = $this->fetch_review_key_id($c);
                    return $keyId === $cKey;
                })) {
                    $tCount = count($targets);
                    $tIdx = array_keys($targets)[0];
                    if (!$this->e_AC_isAccessibleCheck($checkBulk[$tIdx])) {
                        $this->err("Checkpoint '{$keyId}' is not accessible");
                        Notice::displayError("[{$keyId}] " . __("Is not accessible", 'wa-config'));
                    } else {
                        $this->debug("Will replace '{$keyId}' from e_admin_config_add_check_list_to_review ({$tCount})");
                        $checkBulk[$tIdx] = $toCheck;
                    }
                } else {
                    $checkBulk[] = $toCheck;
                }
                usort($checkBulk, function ($c1, $c2) {
                    $c1Key = intval(boolVal($c1['is_activated'])) . '-' . intval(!boolVal($c1['is_computed'])) . '-' . $c1['create_time'];
                    $c2Key = intval(boolVal($c2['is_activated'])) . '-' . intval(!boolVal($c2['is_computed'])) . '-' . $c2['create_time'];
                    return strnatcasecmp($c2Key, $c1Key);
                });
                ksort($this->eACChecksByCategorieByTitle[$toCheck['category']], SORT_NATURAL | SORT_FLAG_CASE);
                ksort($this->eACChecksByCategorieByTitle, SORT_NATURAL | SORT_FLAG_CASE);
                if (strlen($toCheck['category_icon'] ?? '')) {
                    if (!array_key_exists($toCheck['category'], $this->iconsByCategory)) {
                        $this->iconsByCategory[$toCheck['category']] = [];
                    }
                    array_unshift($this->iconsByCategory[$toCheck['category']], $toCheck['category_icon']);
                }
                $this->eACChecksByKeyId[$keyId] = $toCheck;
                $this->eAdminConfigReviewOpts[$this->eConfOptReviewsByCategorieByTitle] = $this->eACChecksByCategorieByTitle;
                $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = "add_check_list_to_review";
                update_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
            }
            protected function e_AC_isAccessibleCheck(&$check)
            {
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $canSentinel = $check['access_cap_or_role'] ?? false;
                return current_user_can('administrator') || $userName === $check['created_by'] || $userName === $check['imported_by'] || !$canSentinel || !strlen($canSentinel) || current_user_can($canSentinel);
            }
            protected function e_AC_isEditableCheck(&$check)
            {
                $user = wp_get_current_user();
                $userName = $user->user_login;
                return current_user_can($this->optAdminEditCabability) || $userName === $check['created_by'] || $userName === $check['imported_by'];
            }
            protected function e_AC_accessibleChecksByCategoryByTitle()
            {
                $checksByCategorieByTitle = [];
                foreach ($this->eACChecksByCategorieByTitle as $category => $reviewsByTitle) {
                    foreach ($reviewsByTitle as $title => $reviews) {
                        foreach ($reviews as $idx => $review) {
                            if ($this->e_AC_isAccessibleCheck($review)) {
                                if (!array_key_exists($category, $checksByCategorieByTitle)) {
                                    $checksByCategorieByTitle[$category] = [];
                                }
                                $checksByTitle =& $checksByCategorieByTitle[$category];
                                if (!array_key_exists($title, $checksByTitle)) {
                                    $checksByTitle[$title] = [];
                                }
                                $checksBulk =& $checksByTitle[$title];
                                $checksBulk[] = $review;
                            } else {
                            }
                        }
                    }
                }
                return $checksByCategorieByTitle;
            }
            protected function e_admin_menu_add_review_field($key, $title, $default = '', $template = null, ...$tArgs) : void
            {
                $this->debugVeryVerbose("Will e_admin_menu_add_review_field");
                $fieldId = "{$this->eAdminConfigReviewOptsKey}_{$key}";
                $fieldName = "{$this->eAdminConfigReviewOptsKey}[{$key}]";
                $value = $this->getReviewOption($key, $default);
                $safeValue = esc_attr($value);
                add_settings_field($fieldId, $title, function () use($tArgs, $safeValue, $fieldId, $fieldName, $template) {
                    if ($template) {
                        echo $template($safeValue, $fieldId, $fieldName, "", ...$tArgs);
                    } else {
                        echo <<<TEMPLATE
    <input id='{$fieldId}' type='text'
    name='{$fieldName}'
    value='{$safeValue}'
    />
TEMPLATE;
                    }
                }, $this->eAdminConfigReviewPageKey, $this->eAdminConfigReviewSettingsKey);
            }
            protected function e_admin_config_review_data_by_key_suggestionbox_template($safeValue, $fieldId, $fieldName, $placeholder, $key)
            {
                return <<<TEMPLATE
    <input
    type="text"
    placeholder="{$placeholder}"
    class="wa-suggest-list-review-data-by-{$key}"
    id="{$fieldId}"
    name="{$fieldName}"
    value="{$safeValue}"
    />
TEMPLATE;
            }
            public function e_admin_config_review_action() : void
            {
                if (!is_user_logged_in()) {
                    $this->err("wa-config e_admin_config_review_action is under logged users only");
                    wp_loginout();
                    wp_die();
                    return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $anonimizedIp = $this->get_user_ip();
                $action = filter_input(INPUT_POST, 'wa-action', FILTER_SANITIZE_SPECIAL_CHARS);
                $checkPOST = filter_input(INPUT_POST, 'wa-data', FILTER_SANITIZE_SPECIAL_CHARS);
                $checkJson = base64_decode($checkPOST);
                $check = json_decode($checkJson, true);
                $checkKey = "";
                if ($check) {
                    $checkKey = $this->fetch_review_key_id($check);
                }
                $this->debug("Will e_admin_config_review_action '{$action}' from '{$checkKey}' by '{$anonimizedIp}'");
                if (false === check_ajax_referer("wa-check-nonce-{$checkKey}", 'wa-nonce', false) || !is_admin()) {
                    $this->err("Invalid access for {$anonimizedIp}");
                    echo json_encode(["error" => "[{$anonimizedIp}] " . __("IP enregistrée suite à accès invalid", 'wa-config')]);
                    http_response_code(401);
                    wp_die();
                    return;
                }
                switch ($action) {
                    case 'checkpoint-activate-toggler':
                        $checksByTitle =& $this->eACChecksByCategorieByTitle[$check['category']];
                        $checks =& $checksByTitle[$check['title']];
                        $targets = array_filter($checks, function ($c) use(&$checkKey) {
                            $cKey = $this->fetch_review_key_id($c);
                            return $checkKey === $cKey;
                        });
                        $tCount = count($targets);
                        if ($tCount !== 1) {
                            $this->err("Invalid checkpoint '{$checkKey}' for {$anonimizedIp}");
                            $this->debug("'{$checkKey}' not found or too much duplicata ({$tCount}) in ", $checks);
                            echo json_encode(["error" => __("Specific checkpoint not found", 'wa-config'), "wa-data" => $check, "count" => $tCount]);
                            http_response_code(404);
                            wp_die();
                            return;
                        }
                        $lookupIdx = array_keys($targets)[0];
                        $toggeled =& $checks[$lookupIdx];
                        if (!$this->e_AC_isEditableCheck($toggeled)) {
                            $this->err("Invalid checkpoint access '{$checkKey}' for {$anonimizedIp}");
                            $this->debug("'{$checkKey}' not accessible in ", $checks);
                            echo json_encode(["error" => "[{$checkKey}] " . __("Specific checkpoint not accessible", 'wa-config'), "wa-data" => $check]);
                            http_response_code(404);
                            wp_die();
                            return;
                        }
                        $toggeled['is_activated'] = !$toggeled['is_activated'];
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsByCategorieByTitle] = $this->eACChecksByCategorieByTitle;
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = $action;
                        update_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                        delete_transient($this->_reviewsByKeySearchCacheKey);
                        $this->_reviewsByKeySearchCacheKey = null;
                        $this->debugVerbose("Did activate toggle from '{$action}' for '{$checkKey}'");
                        break;
                    case 'delete-checkpoint':
                        $checksByTitle =& $this->eACChecksByCategorieByTitle[$check['category']];
                        $checks =& $checksByTitle[$check['title']];
                        $targets = array_filter($checks, function ($c) use(&$checkKey) {
                            $cKey = $this->fetch_review_key_id($c);
                            return $checkKey === $cKey;
                        });
                        $tCount = count($targets);
                        if ($tCount !== 1) {
                            $this->err("Invalid checkpoint '{$checkKey}' for {$anonimizedIp}");
                            $this->debug("'{$checkKey}' not found or too much duplicata ({$tCount}) in ", $checks);
                            echo json_encode(["error" => "Specific checkpoint not found", "wa-data" => $check, "count" => $tCount]);
                            http_response_code(404);
                            wp_die();
                            return;
                        }
                        $this->debug("Will '{$action}' for '{$checkKey}'");
                        $deleteds = $this->getReviewOption($this->eConfOptReviewsDeleted, []);
                        $lookupIdx = array_keys($targets)[0];
                        if (!$this->e_AC_isEditableCheck($checks[$lookupIdx])) {
                            $this->err("Invalid checkpoint access '{$checkKey}' for {$anonimizedIp}");
                            $this->debug("'{$checkKey}' not accessible in ", $checks);
                            echo json_encode(["error" => "[{$checkKey}] " . __("Specific checkpoint not accessible", 'wa-config'), "wa-data" => $check]);
                            http_response_code(404);
                            wp_die();
                            return;
                        }
                        unset($checks[$lookupIdx]);
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsByCategorieByTitle] = $this->eACChecksByCategorieByTitle;
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = $action;
                        $check['is_deleted'] = true;
                        $deleteds[] = $check;
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsDeleted] = $deleteds;
                        $this->debugVerbose("Review Options before delete", $this->eAdminConfigReviewOpts);
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = $action;
                        update_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                        delete_transient($this->_reviewsByKeySearchCacheKey);
                        $this->_reviewsByKeySearchCacheKey = null;
                        $this->debugVerbose("Did delete checkpoint from '{$action}' for '{$checkKey}'");
                        break;
                    case 'clean-all':
                        if (current_user_can('administrator')) {
                            $this->eACChecksByCategorieByTitle = [];
                            $this->eAdminConfigReviewOpts[$this->eConfOptReviewsByCategorieByTitle] = $this->eACChecksByCategorieByTitle;
                            delete_option($this->eAdminConfigReviewOptsKey);
                            delete_transient($this->_reviewsByKeySearchCacheKey);
                            $this->_reviewsByKeySearchCacheKey = null;
                            $this->debugVerbose("Did clean all review data from '{$action}'");
                        } else {
                            $this->err("Invalid access for {$anonimizedIp}, need to be administrator to clean all");
                            echo json_encode(["error" => "Invalid access for {$anonimizedIp} registred"]);
                            http_response_code(401);
                            wp_die();
                            return;
                        }
                        break;
                    case 'export-csv':
                        ob_start();
                        $headerRow = array_keys($this->eACDefaultCheckpoint);
                        $dataRows = [];
                        if (current_user_can($this->optAdminEditCabability)) {
                            $checksByCategorieByTitle = $this->getReviewOption($this->eConfOptReviewsByCategorieByTitle, []);
                        } else {
                            $checksByCategorieByTitle = $this->e_AC_accessibleChecksByCategoryByTitle();
                        }
                        foreach ($checksByCategorieByTitle as $category => $checksByTitle) {
                            foreach ($checksByTitle as $title => $checks) {
                                foreach ($checks as $idx => $check) {
                                    $row = [];
                                    foreach ($headerRow as $hIndex) {
                                        $row[] = mb_convert_encoding($check[$hIndex], 'UTF-8');
                                    }
                                    $dataRows[] = $row;
                                }
                            }
                        }
                        if (current_user_can('administrator')) {
                            $deleteds = $this->getReviewOption($this->eConfOptReviewsDeleted, []);
                            foreach ($deleteds as $d) {
                                $row = [];
                                foreach ($headerRow as $hIndex) {
                                    $row[] = mb_convert_encoding($d[$hIndex], 'UTF-8');
                                }
                                $dataRows[] = $row;
                            }
                        }
                        $siteSlug = sanitize_title(get_bloginfo('name'));
                        $date = date("Ymd-His_O");
                        $filename = "{$siteSlug}-{$this->iId}-{$date}.csv";
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Content-Description: File Transfer');
                        header('Content-Type: text/csv; charset=utf-8');
                        header("Content-Disposition: attachment; filename={$filename}");
                        header('Expires: 0');
                        header('Pragma: public');
                        $fh = fopen('php://output', 'w');
                        fprintf($fh, chr(0xef) . chr(0xbb) . chr(0xbf));
                        fputcsv($fh, $headerRow);
                        foreach ($dataRows as $dataRow) {
                            fputcsv($fh, $dataRow);
                        }
                        fclose($fh);
                        ob_end_flush();
                        $this->debugVerbose("Did export reviews data as CSV from '{$action}'");
                        wp_die();
                        return;
                        break;
                    case 'import-csv':
                        if (!current_user_can($this->optAdminReviewEditCabability)) {
                            $this->err("Invalid import access for {$anonimizedIp}");
                            $this->debug("'import-csv' not accessible");
                            echo json_encode(["error" => "[{$checkKey}] " . __("'import-csv' not accessible, ip {$anonimizedIp} registred", 'wa-config')]);
                            http_response_code(404);
                            wp_die();
                            return;
                        }
                        $csv_file = $_FILES['wa-import-csv-file'];
                        $csv_to_array = array_map('str_getcsv', file($csv_file['tmp_name']));
                        $headers = [];
                        $hIdx = [];
                        foreach ($csv_to_array as $key => $value) {
                            if ($key == 0) {
                                $headers = $value;
                                foreach ($headers as $idx => $h) {
                                    $hIdx[$h] = $idx;
                                }
                                continue;
                            }
                            $checkpointValue = array_map(function ($idx) use(&$headers, &$value) {
                                $h = $headers[$idx];
                                return $value[$idx];
                            }, $hIdx);
                            $this->debugVerbose("e_admin_config_review_action import-csv will add checkpoint");
                            $this->debugVeryVerbose("import-csv checkpoint :", ['checkpoint' => $checkpointValue, 'from_value' => $value]);
                            $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = "{$action}-add-checkpoint";
                            $user = wp_get_current_user();
                            $userName = $user->user_login;
                            if (current_user_can('administrator') || current_user_can($this->optAdminEditCabability) || $userName === ($checkpointValue['created_by'] ?? false)) {
                                $this->e_admin_config_add_check_list_to_review($checkpointValue, true);
                            } else {
                                $this->debugVerbose("Ignore checkpoint import since not accessible");
                            }
                        }
                        $this->debugVeryVerbose("WA Review options", $this->eAdminConfigReviewOpts);
                        $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = $action;
                        update_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                        delete_transient($this->_reviewsByKeySearchCacheKey);
                        $this->_reviewsByKeySearchCacheKey = null;
                        $redirectUrl = admin_url("admin.php?page={$this->eAdminConfigReviewPageKey}");
                        $this->debugVeryVerbose("After csv-import, will redirect to : {$redirectUrl}");
                        http_response_code(302);
                        echo "<a href='{$redirectUrl}'>Imports OK, retour à la revue en cours...</a>";
                        if (wp_redirect($redirectUrl)) {
                            wp_die();
                            return;
                        } else {
                            $this->debugVeryVerbose("csv-import Fail to redirect to : {$redirectUrl}");
                        }
                        break;
                    default:
                        $this->warn("Unknow action '{$action}'");
                        break;
                }
                echo json_encode(["status" => "OK", "end_date" => date("Y/m/d H:i:s O ")]);
                http_response_code(200);
                wp_die();
                return;
            }
            protected function e_admin_config_render_review_options($echo = false)
            {
                $self = $this;
                $formFields = function () use($self) {
                    ob_start();
                    settings_fields($self->eAdminConfigOptsReviewGroupKey);
                    return ob_get_clean();
                };
                $sectionFormFields = function () use($self) {
                    ob_start();
                    do_settings_sections($self->eAdminConfigReviewPageKey);
                    return ob_get_clean();
                };
                $submitBtn = function () {
                    ob_start();
                    submit_button(__('Ajouter le checkpoint', 'wa-config') . ' ', 'primary large wa-add-page-btn-icon', 'submit', false);
                    return ob_get_clean();
                };
                $rendering = <<<TEMPLATE
    <form method="post" action="options.php" id="wa_config_review_add_checkpoint"> 
        {$formFields()}
        {$sectionFormFields()}
        <p class="submit">
            {$submitBtn()}
            <!--span class="dashicons dashicons-welcome-add-page"></span-->
        </p>
    </form>
TEMPLATE;
                if ($echo) {
                    echo $rendering;
                }
                return $rendering;
            }
            protected $reviewIdsToTrash = [];
            public function e_admin_config_add_base_review()
            {
                $this->eACChecksByCategorieByTitle = $this->getReviewOption($this->eConfOptReviewsByCategorieByTitle, []);
                unregister_post_type('wa-config');
                register_taxonomy('wa-skill', array());
                $app = $this;
                do_action(WPActions::wa_do_base_review_preprocessing, $app);
                $this->e_admin_config_add_check_list_to_review(['category' => __('01 - Critique', 'wa-config'), 'category_icon' => '<span class="dashicons dashicons-plugins-checked"></span>', 'title' => __('01 - Version de PHP', 'wa-config'), 'title_icon' => '<span class="dashicons dashicons-shield"></span>', 'requirements' => __('7.4+ (7.4 or higher recommended)', 'wa-config'), 'value' => "PHP " . PHP_VERSION, 'result' => version_compare(PHP_VERSION, '7.4', '>'), 'fixed_id' => "{$this->iId}-check-php-version", 'is_computed' => true]);
                $aTestBaseUrl = $this->getWaConfigOption($this->eConfOptATestsBaseUrl, site_url());
                require_once $this->pluginRoot . "tests/external/EXT_TEST_htaccessIsEnabled.php";
                $htaccessOK = \WA\Config\ExtTest\EXT_TEST_htaccessIsEnabled::check($aTestBaseUrl);
                $this->e_admin_config_add_check_list_to_review(['category' => __('01 - Critique', 'wa-config'), 'title' => __('02 - Securisations .htaccess', 'wa-config'), 'title_icon' => '<span class="dashicons dashicons-shield"></span>', 'requirements' => __('Activation des redirections .htaccess', 'wa-config'), 'result' => !$htaccessOK, 'fixed_id' => "{$this->iId}-check-htaccess-ok", 'is_computed' => true]);
                $report = "";
                $result = (function () use(&$report) {
                    $version = null;
                    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
                    if (strrpos($userAgent, 'firefox') !== false) {
                        preg_match('/firefox\\/([0-9]+\\.*[0-9]*)/', $userAgent, $matches);
                        if (!empty($matches)) {
                            $version = explode('.', $matches[1])[0];
                            $report .= "Firefox {$version}";
                            return intval($version) >= 101;
                        }
                    }
                    if (strrpos($userAgent, 'chrome') !== false) {
                        preg_match('/chrome\\/([0-9]+\\.*[0-9]*)/', $userAgent, $matches);
                        if (!empty($matches)) {
                            $version = explode('.', $matches[1])[0];
                            $report .= "Chrome {$version}";
                            return intval($version) >= 102;
                        }
                    }
                    return false;
                })();
                $this->e_admin_config_add_check_list_to_review(['category' => __('01 - Critique', 'wa-config'), 'title' => __('03 - Compatibilité', 'wa-config'), 'title_icon' => '<span class="dashicons dashicons-universal-access"></span>', 'requirements' => __('Navigateur compatible. Chrome > 102. Firefox > 101.', 'wa-config'), 'value' => $report, 'result' => $result, 'is_activated' => true, 'fixed_id' => "{$this->iId}-check-chrome-version", 'is_computed' => true]);
                do_action(WPActions::wa_do_base_review_postprocessing, $app);
                $reviewIdsToTrash = apply_filters(WPFilters::wa_config_reviews_ids_to_trash, $this->reviewIdsToTrash, $this);
                $deleteds = $this->eAdminConfigReviewOpts[$this->eConfOptReviewsDeleted];
                foreach ($this->eACChecksByCategorieByTitle as $c => &$checksByTitle) {
                    foreach ($checksByTitle as $t => &$checks) {
                        $checks = array_filter($checks, function (&$check) use(&$reviewIdsToTrash, &$deleteds) {
                            if (in_array($check['fixed_id'], $reviewIdsToTrash)) {
                                $check['is_deleted'] = true;
                                $deleteds[] = $check;
                                return false;
                            }
                            return true;
                        });
                    }
                }
                $this->reviewIdsToTrash = [];
                $this->debugVeryVerbose("Trashed wa-reviews", $deleteds);
                $this->eAdminConfigReviewOpts[$this->eConfOptReviewsDeleted] = $deleteds;
                $this->eAdminConfigReviewOpts[$this->eConfOptReviewsByCategorieByTitle] = $this->eACChecksByCategorieByTitle;
                $this->eAdminConfigReviewOpts[$this->eConfOptReviewsInternalPreUpdateAction] = "add_base_review";
                update_option($this->eAdminConfigReviewOptsKey, $this->eAdminConfigReviewOpts);
                flush_rewrite_rules();
            }
            public function e_admin_config_render_review_section() : void
            {
                if (!is_user_logged_in()) {
                    $this->err("wa-config e_admin_config_add_check_list_to_review is under logged users only");
                    wp_loginout();
                    wp_die();
                    return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $this->e_admin_config_render_review_options(true);
                $checksByCategorieByTitle = $this->e_AC_accessibleChecksByCategoryByTitle();
                ?>
                <a
                id='wa-expand-all'
                href='#wa-expand-all'
                data-wa-expand-target='.wa-expand'
                class='wa-expand-toggler'>
                    <span class='dashicons dashicons-fullscreen-alt'></span>
                </a>
                <?php 
                foreach ($checksByCategorieByTitle as $category => $reviewsByTitle) {
                    $categoryIcon = $this->iconsByCategory[$category][0] ?? '';
                    $catIdx = sanitize_title($category);
                    echo "<h1 class='wa-check-category-title'>\n                    <span>\n                    <span>{$category}</span> {$categoryIcon}</span>\n                    <a\n                    id='wa-check-category-title-{$catIdx}'\n                    href='#wa-check-category-title-{$catIdx}'\n                    data-wa-expand-target='#wa-check-list-{$catIdx} .wa-expand'\n                    class='wa-expand-toggler'>\n                        <span class='dashicons dashicons-fullscreen-alt'></span>\n                    </a></h1>";
                    ?>
                    <table
                    id="<?php 
                    echo "wa-check-list-{$catIdx}";
                    ?>"
                    class="wa-check-list"
                    cellspacing="0px"
                    cellpadding="0px"
                    >
                        <!-- TODO : for print only... will messup with flex...  + Align for A4 thead>
                            <tr>
                                <th align="left">&nbsp;</th>
                                <th align="left"><?php 
                    esc_html_e('Exigence', 'wa-config');
                    ?></th>
                                <th align="left"><?php 
                    esc_html_e('Présent', 'wa-config');
                    ?></th>
                            </tr>
                        </thead-->
                        <tbody>
                            <tr>
                                <th class="wa-check-title">&nbsp;</th>
                                <th class="wa-check-required"><?php 
                    esc_html_e('Exigence', 'wa-config');
                    ?></th>
                                <th class="wa-check-present"><?php 
                    esc_html_e('Présent', 'wa-config');
                    ?></th>
                            </tr>
                            <?php 
                    foreach ($reviewsByTitle as $title => $reviews) {
                        $titleIdx = sanitize_title($title);
                        foreach ([$reviews[0]] as $idx => $review) {
                            $rowClass = "wa-check";
                            if ($review['result']) {
                                $background = '#7cc038';
                                $color = 'black';
                                $rowClass .= ' wa-check-valid';
                            } elseif (isset($review['fallback'])) {
                                $background = '#FCC612';
                                $color = 'black';
                                $rowClass .= ' wa-check-fallback';
                            } else {
                                $background = '#f43';
                                $color = 'white';
                                $rowClass .= ' wa-check-fail';
                            }
                            $isActif = $review['is_activated'] ?? false;
                            $rowClass .= $isActif ? '' : ' wa-check-disabled wa-expand wa-expand-collapsed';
                            $requirementIcon = $review['is_computed'] ? 'superhero' : 'buddicons-buddypress-logo';
                            ?>
                                    <tr
                                    id='<?php 
                            echo "wa-check-title-{$catIdx}-{$titleIdx}";
                            ?>'
                                    class="<?php 
                            echo $rowClass;
                            ?>"
                                    >
                                        <td class="wa-check-title">
                                            <?php 
                            echo wp_kses_post($title);
                            ?>
                                            <br />
                                            <?php 
                            if ($review['result']) {
                                echo '<span class="dashicons dashicons-awards wa-color-review-ok"></span> ';
                            }
                            ?>
                                            <?php 
                            echo wp_kses_post($review['title_icon'] ?? '');
                            ?>
                                        </td>
                                        <td class="wa-check-required">
                                            <div
                                            class="wa-last-check wa-expand-toggler"
                                            data-wa-expand-target="<?php 
                            echo "#wa-check-title-{$catIdx}-{$titleIdx} .wa-expand";
                            ?>"
                                            >
                                                <div>
                                                    <span class="dashicons dashicons-<?php 
                            echo $requirementIcon;
                            ?>"></span>
                                                    <strong>
                                                        <?php 
                            echo wp_kses_post($review['requirements'] === true ? esc_html__('Yes', 'wa-config') : $review['requirements']);
                            ?>
                                                    </strong>
                                                </div><br />
                                                <?php 
                            echo "<notice><span class='dashicons dashicons-calendar-alt'></span> " . date("Y/m/d H:i:s O ", $review['create_time']) . ($review['is_computed'] ? "<br />" . $review['fixed_id'] : '') . "</notice>";
                            ?>
                                                <?php 
                            if ($review['created_by'] ?? false) {
                                echo "<br/><notice>-- {$review['created_by']}</notice> ";
                            }
                            if ($review['access_cap_or_role'] ?? false) {
                                echo "<br/>" . __('Pour :', 'wa-config') . " <notice>{$review['access_cap_or_role']}</notice>";
                            }
                            ?>
                                            </div>
                                            <table
                                            class="wa-check-list wa-previous-check-list wa-expand wa-expand-collapsed"
                                            cellspacing="0px"
                                            cellpadding="0px"
                                            style="background-color: white; width:100%;"
                                            >
                                                <tbody>
                                                    <?php 
                            foreach (array_slice($reviews, 0) as $idx => $pReview) {
                                $pRowClass = "wa-check wa-previous-check";
                                if ($pReview['result']) {
                                    $pBackground = '#7cc038';
                                    $pColor = 'black';
                                    $pRowClass .= ' wa-check-valid';
                                } elseif (isset($pReview['fallback'])) {
                                    $pBackground = '#FCC612';
                                    $pColor = 'black';
                                    $pRowClass .= ' wa-check-fallback';
                                } else {
                                    $pBackground = '#f43';
                                    $pColor = 'white';
                                    $pRowClass .= ' wa-check-fail';
                                }
                                $pReviewKey = $this->fetch_review_key_id($pReview);
                                $pIsActif = $pReview['is_activated'] ?? false;
                                $pRowClass .= $pIsActif ? '' : ' wa-check-disabled';
                                $pRequirementIcon = $pReview['is_computed'] ? 'superhero' : 'buddicons-buddypress-logo';
                                ?>
                                                        <tr
                                                        id='<?php 
                                echo "wa-check-title-{$catIdx}-{$titleIdx}";
                                ?>'
                                                        class="<?php 
                                echo $pRowClass;
                                ?>"
                                                        >
                                                            <td class="wa-check-required">
                                                                <div
                                                                class="wa-last-check"
                                                                >
                                                                    <div>
                                                                        <?php 
                                if ($pReview['result']) {
                                    echo '<span class="dashicons dashicons-awards wa-color-review-ok"></span> ';
                                }
                                ?>
                                                                        <span class="dashicons dashicons-<?php 
                                echo $pRequirementIcon;
                                ?>"></span>
                                                                        <strong>
                                                                            <?php 
                                echo wp_kses_post($pReview['requirements'] === true ? esc_html__('Yes', 'wa-config') : $pReview['requirements']);
                                ?>
                                                                        </strong>
                                                                    </div><br />
                                                                    <?php 
                                echo "<notice><span class='dashicons dashicons-calendar-alt'></span> " . date("Y/m/d H:i:s O ", $pReview['create_time']) . ($pReview['is_computed'] ? "<br />" . $pReview['fixed_id'] : '') . "</notice>";
                                ?>
                                                                    <?php 
                                if ($pReview['created_by'] ?? false) {
                                    echo "<br /><notice>-- {$pReview['created_by']}</notice>";
                                }
                                if ($pReview['access_cap_or_role'] ?? false) {
                                    echo "<br />" . __('Pour :', 'wa-config') . " <notice>{$pReview['access_cap_or_role']}</notice>";
                                }
                                ?>
                                                                </div>
                                                            </td>
                                                            <td
                                                            class="wa-check-present"
                                                            style="background-color:<?php 
                                echo $pBackground;
                                ?>; color:<?php 
                                echo $pColor;
                                ?>">
                                                                <?php 
                                if ($pReview['result']) {
                                    echo '<span class="dashicons dashicons-yes-alt"></span> ';
                                } else {
                                    echo '<span class="dashicons dashicons-marker"></span> ';
                                }
                                echo wp_kses_post($pReview['value'] ?? "");
                                if ($pReview['result'] && !($pReview['value'] ?? false)) {
                                    echo esc_html__('Validé', 'wa-config');
                                }
                                if (!$pReview['result']) {
                                    if (isset($pReview['fallback'])) {
                                        printf('<div>%s. %s</div>', esc_html__('Compensé', 'wa-config'), esc_html($pReview['fallback']));
                                    }
                                    if (isset($pReview['failure'])) {
                                        printf('<div>%s</div>', wp_kses_post($pReview['failure']));
                                    } else {
                                        printf('<div>%s.</div>', esc_html__('A faire', 'wa-config'));
                                    }
                                }
                                ?>
                                                            </td>
                                                            <td class="wa-check-actions">
                                                                <?php 
                                if (current_user_can($this->optAdminEditCabability) || !($pReview['is_computed'] ?? false)) {
                                    ?>
                                                                    <?php 
                                    if ($this->e_AC_isAccessibleCheck($pReview)) {
                                        ?>
                                                                        <?php 
                                        if ($this->e_AC_isEditableCheck($pReview) && !($pReview['is_computed'] ?? false)) {
                                            ?>
                                                                            <a 
                                                                            href="#wa_config_review_add_checkpoint"
                                                                            class="wa-check-activate-toogle"
                                                                            data-wa-review-activate-src='<?php 
                                            echo base64_encode(json_encode($pReview));
                                            ?>'
                                                                            data-wa-nonce='<?php 
                                            echo wp_create_nonce("wa-check-nonce-{$pReviewKey}");
                                            ?>'
                                                                            >
                                                                                <?php 
                                            if ($pReview['is_activated'] ?? true) {
                                                ?>
                                                                                    <span class="dashicons dashicons-hidden"></span>
                                                                                    <?php 
                                                _e("Desactiver", 'wa-config');
                                                ?>
                                                                                <?php 
                                            } else {
                                                ?>
                                                                                    <span class="dashicons dashicons-visibility"></span>
                                                                                    <?php 
                                                _e("Activer", 'wa-config');
                                                ?>
                                                                                <?php 
                                            }
                                            ?>
                                                                            </a>
                                                                        <?php 
                                        }
                                        ?>
                                                                        <?php 
                                        if ($this->e_AC_isEditableCheck($pReview)) {
                                            ?>
                                                                            <a 
                                                                            href="#wa_config_review_delete_checkpoint"
                                                                            class="wa-check-delete-trigger"
                                                                            data-wa-review-delete-src='<?php 
                                            echo base64_encode(json_encode($pReview));
                                            ?>'
                                                                            data-wa-nonce='<?php 
                                            echo wp_create_nonce("wa-check-nonce-{$pReviewKey}");
                                            ?>'
                                                                            >
                                                                                <span class="dashicons dashicons-trash"></span>
                                                                                <?php 
                                            _e("Supprimer", 'wa-config');
                                            ?>
                                                                            </a>
                                                                        <?php 
                                        }
                                        ?>
                                                                    <?php 
                                    }
                                    ?>
                                                                <?php 
                                }
                                ?>
                                                                <a 
                                                                href="#wa_config_review_add_checkpoint"
                                                                class="wa-check-duplicate-trigger"
                                                                data-wa-review-duplicate-src='<?php 
                                echo base64_encode(json_encode($pReview));
                                ?>'
                                                                >
                                                                    <span class="dashicons dashicons-admin-page"></span>
                                                                    <?php 
                                _e("Dupliquer", 'wa-config');
                                ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php 
                            }
                            ?>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td
                                        class="wa-check-present"
                                        style="background-color:<?php 
                            echo $background;
                            ?>; color:<?php 
                            echo $color;
                            ?>">
                                            <?php 
                            if ($review['result']) {
                                echo '<span class="dashicons dashicons-yes-alt"></span> ';
                            } else {
                                echo '<span class="dashicons dashicons-marker"></span> ';
                            }
                            echo wp_kses_post($review['value'] ?? "");
                            if ($review['result'] && !($review['value'] ?? false)) {
                                echo esc_html__('Validé', 'wa-config');
                            }
                            if (!$review['result']) {
                                if (isset($review['fallback'])) {
                                    printf('<div>%s. %s</div>', esc_html__('Compensé', 'wa-config'), esc_html($review['fallback']));
                                }
                                if (isset($review['failure'])) {
                                    printf('<div>%s</div>', wp_kses_post($review['failure']));
                                } else {
                                    printf('<div>%s.</div>', esc_html__('A faire', 'wa-config'));
                                }
                            }
                            ?>
                                        </td>
                                    </tr>
                                <?php 
                        }
                    }
                    ?>
                        </tbody>
                    </table>
                    <?php 
                }
                echo "<p>[{$this->iId}] <strong><a\n                href='#wa-check-export-csv'\n                class='wa-check-export-csv-trigger'\n                data-wa-nonce='" . wp_create_nonce("wa-check-nonce-") . "'\n                >" . __("Exporter les checkpoints en CSV", 'wa-config') . "</a></strong></p>";
                echo "<p>[{$this->iId}] <strong><span\n                class='wa-check-import-csv-trigger'\n                >" . __("Importer les checkpoints depuis un fichier CSV", 'wa-config') . "</span></strong></p>";
                echo '<form action="' . add_query_arg(['action' => 'wa-review-action'], admin_url('admin-ajax.php')) . '" method="post" enctype="multipart/form-data">';
                echo '<input type="file" name="wa-import-csv-file">';
                echo '<input type="hidden" name="wa-action" value="import-csv">';
                echo '<input type="hidden" name="wa-nonce" value="' . wp_create_nonce("wa-check-nonce-") . '">';
                echo '<input type="submit" name="submit" value="submit">';
                echo '</form>';
                if (current_user_can($this->optAdminEditCabability)) {
                    echo "<p>[{$this->iId}] <strong><a\n                    href='#clean-all-need-javascript'\n                    class='wa-check-export-csv-trigger'\n                    data-wa-nonce='" . wp_create_nonce("wa-check-nonce-") . "'\n                    data-wa-clean-after-download='true'\n                    >" . __("Supprimer toutes les données de review (Nettoyage des revues en cours et historique de suppression)", 'wa-config') . "</a></strong></p>";
                }
                ?>
                <script>
                    // TODO : debounce and UI lock loader ?

                    var expTriggers = document.querySelectorAll('.wa-expand-toggler');

                    // <span class="dashicons dashicons-hidden"></span>
                    // visibility hidden post status edit trash

                    expTriggers.forEach(function(trigger) {
                        var tTargetsSelector = trigger.getAttribute(
                            'data-wa-expand-target'
                        );
                        var tTargets = document.querySelectorAll(
                            tTargetsSelector
                        );
                        if (tTargets.length) {
                            trigger.onclick = function(e) {
                                // e.preventDefault(); // Do not follow link, prevent page refresh => but also prevent link click if link in title...
                                // console.log(e);
                                var didExpand = !!Number(trigger.getAttribute(
                                    'data-wa-did-expand'
                                ));
                                var icon = trigger.querySelector(
                                    '.dashicons.dashicons-fullscreen-exit-alt,'
                                    + '.dashicons.dashicons-fullscreen-alt'
                                );

                                if (icon && icon.classList) {
                                    if (didExpand) {
                                        icon.classList.remove('dashicons-fullscreen-exit-alt');
                                        icon.classList.add('dashicons-fullscreen-alt');
                                    } else {
                                        icon.classList.add('dashicons-fullscreen-exit-alt');
                                        icon.classList.remove('dashicons-fullscreen-alt');
                                    }
                                }

                                // e.target.classList.toggle('transition'); => will swith open to close by TARGET
                                tTargets.forEach(function(tTarget) {
                                    if (didExpand) {
                                        tTarget.classList.add('wa-expand-collapsed');
                                        // tTarget.classList.remove('wa-expand'); // wa-expand should always stay since lookup used by expand toggler
                                    } else {
                                        tTarget.classList.remove('wa-expand-collapsed');
                                        // tTarget.classList.add('wa-expand');
                                    }
                                });
                                trigger.setAttribute('data-wa-did-expand', Number(!didExpand));
                            }
                        }
                    });

                    var duplicateTriggers = document.querySelectorAll('.wa-check-duplicate-trigger');

                    duplicateTriggers.forEach(function(trigger) {
                        trigger.onclick = function(e) {
                            var duplicateData = JSON.parse(atob(trigger.getAttribute(
                                'data-wa-review-duplicate-src'
                            )));
                            var addCheckpointForm = document.querySelector('#wa_config_review_add_checkpoint');
                            // https://stackoverflow.com/questions/5700471/set-value-of-input-using-javascript-function
                            // // Try... for YUI
                            // Dom.get("gadget_url").set("value","");
                            // // with normal Javascript
                            // document.getElementById('gadget_url').value = '';
                            // // with JQuery
                            // $("#gadget_url").val("");

                            // Use configured wa-config dynamic labelings ?
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_category')
                            .value = duplicateData.category;
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_category_icon')
                            .value = duplicateData.category_icon || '';
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_title')
                            .value = duplicateData.title;
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_title_icon')
                            .value = duplicateData.title_icon || '';
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_requirements')
                            .value = duplicateData.requirements;
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_result')
                            .checked = duplicateData.result;
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_value')
                            .value = duplicateData.value;
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_is_activated')
                            .checked = duplicateData.is_activated;
                            addCheckpointForm.querySelector('#wa_config_e_admin_config_review_opts_wa_review_access_cap_or_role')
                            .value = duplicateData.access_cap_or_role || "";
                        }
                    });

                    var activateTogglers = document.querySelectorAll(
                        '.wa-check-activate-toogle'
                    );

                    activateTogglers.forEach(function(toggler) {
                        toggler.onclick = function(event) {
                            event.preventDefault();
                            // var checkData = JSON.parse(atob(toggler.getAttribute(
                            //     'data-wa-review-activate-src'
                            // )));
                            // var encodedData = btoa(JSON.stringify(checkData));
                            var encodedData = toggler.getAttribute(
                                'data-wa-review-activate-src'
                            );
                            // Will not be used to update check point, 
                            // checkData is just used as a search lookup
                            //   checkData['is_activated'] = !checkData['is_activated'];
                            var nonce = toggler.getAttribute(
                                'data-wa-nonce'
                            );
                            // var data = jQuery({ // "action=wa-review-action&" + jQuery({
                            //     'wa-action': 'update-checkpoint',
                            //     'wa-nonce': nonce,
                            //     'wa-data': JSON.stringify(checkData),
                            // }).serialize();
                            var data = { // "action=wa-review-action&" + jQuery({
                                'wa-action': 'checkpoint-activate-toggler',
                                'wa-nonce': nonce,
                                // Wrappers shitting the json, so need another wrapper clean to POST
                                // 'wa-data': JSON.stringify(checkData),
                                // https://stackoverflow.com/questions/246801/how-can-you-encode-a-string-to-base64-in-javascript
                                'wa-data': encodedData,
                            };

                            console.log(data);
                            jQuery.ajax(
                            {
                                type: "POST",
                                url: window.ajaxurl + '?action=wa-review-action',
                                data: data,
                                success: function(msg) {
                                    console.log(msg);
                                    // https://developer.mozilla.org/fr/docs/Web/API/Location/reload
                                    document.location.reload();
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.log(errorThrown);
                                }
                            });
                        };
                    });

                    var deleteTriggers = document.querySelectorAll(
                        '.wa-check-delete-trigger'
                    );

                    deleteTriggers.forEach(function(trigger) {
                        trigger.onclick = function(event) {
                            event.preventDefault();
                            var encodedData = trigger.getAttribute(
                                'data-wa-review-delete-src'
                            );
                            var nonce = trigger.getAttribute(
                                'data-wa-nonce'
                            );
                            var data = {
                                'wa-action': 'delete-checkpoint',
                                'wa-nonce': nonce,
                                'wa-data': encodedData,
                            };

                            console.log(data);
                            jQuery.ajax(
                            {
                                type: "POST",
                                url: window.ajaxurl + '?action=wa-review-action',
                                data: data,
                                success: function(msg) {
                                    console.log(msg);
                                    document.location.reload();
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.log(errorThrown);
                                }
                            });
                        };
                    });

                    // var cleanAllTriggers = document.querySelectorAll('.wa-check-clean-all');
                    // cleanAllTriggers.forEach(function(trigger) {
                    //     trigger.onclick = function(event) {
                    //         event.preventDefault();
                    //         var nonce = trigger.getAttribute(
                    //             'data-wa-nonce'
                    //         );
                    //         var data = {
                    //             'wa-action': 'clean-all',
                    //             'wa-nonce': nonce,
                    //         };
                    //         console.log(data);
                    //         jQuery.ajax(
                    //         {
                    //             type: "POST",
                    //             url: window.ajaxurl + '?action=wa-review-action',
                    //             data: data,
                    //             success: function(msg) {
                    //                 console.log(msg);
                    //                 // https://developer.mozilla.org/fr/docs/Web/API/Location/reload
                    //                 document.location.reload();
                    //             },
                    //             error: function(jqXHR, textStatus, errorThrown) {
                    //                 console.error(errorThrown);
                    //             }
                    //         });
                    //     };
                    // });

                    // convert a Unicode string to a string in which
                    // each 16-bit unit occupies only one byte
                    // function toBinary(string) {
                    //     const codeUnits = new Uint16Array(string.length);
                    //     for (let i = 0; i < codeUnits.length; i++) {
                    //         codeUnits[i] = string.charCodeAt(i);
                    //     }
                    //     return btoa(String.fromCharCode(...new Uint8Array(codeUnits.buffer)));
                    // }
                    var exportCSVTriggers = document.querySelectorAll('.wa-check-export-csv-trigger');
                    exportCSVTriggers.forEach(function(trigger) {
                        trigger.onclick = function(event) {
                            event.preventDefault();
                            var nonce = trigger.getAttribute(
                                'data-wa-nonce'
                            );
                            var shouldCleanAfterDownload = trigger.getAttribute(
                                'data-wa-clean-after-download'
                            );
                            var data = {
                                'wa-action': 'export-csv',
                                'wa-nonce': nonce,
                            };
                            console.log(data);

                            var cleanAll = function () {
                                var data = {
                                    'wa-action': 'clean-all',
                                    'wa-nonce': nonce,
                                };
                                console.log(data);
                                jQuery.ajax(
                                {
                                    type: "POST",
                                    url: window.ajaxurl + '?action=wa-review-action',
                                    data: data,
                                    success: function(msg) {
                                        console.log(msg);
                                        // https://developer.mozilla.org/fr/docs/Web/API/Location/reload
                                        document.location.reload();
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error(errorThrown);
                                    }
                                });
                            };

                            jQuery.ajax(
                            {
                                type: "POST",
                                url: window.ajaxurl + '?action=wa-review-action',
                                data: data,
                                dataType: "text",
                                // dataType: "html",
                                success: function(msg, textStatus, request) {
                                    // console.log(msg);
                                    // https://stackoverflow.com/questions/26584349/downloading-files-using-ajax-in-wordpress
                                    // https://codepen.io/chriddyp/pen/aVammp
                                    // https://diegolamonica.info/generate-csv-data-uri-from-a-table-via-javascript/
                                    // 'data:text/csv;charset=utf-8;base64,' + btoa(dataURL);
                                    // Open is good, but may blocked by pop up blockers...
                                    // window.open('data:text/csv;charset=utf-8;base64,' + btoa(msg));

                                    // https://stackoverflow.com/questions/32545632/how-can-i-download-a-file-using-window-fetch
                                    // // var blob = request.blob();
                                    // var blob = msg;
                                    // // Uncaught TypeError: Failed to execute 'createObjectURL' on 'URL': Overload resolution failed. :
                                    // var url = window.URL.createObjectURL(blob);

                                    // var rawData = toBinary(msg);
                                    // https://stackoverflow.com/questions/12270764/get-raw-text-from-ajax-request
                                    // var rawData = request.responseText;
                                    var rawData = msg;

                                    // var url = 'data:text/csv;charset=utf-8;base64,'
                                    // + btoa(rawData); // TIPS : btoa HAVE ISSUE with special utf8 chars, use encodeURIComponent instead
                                    // https://stackoverflow.com/questions/42462764/javascript-export-csv-encoding-utf-8-issue
                                    var url = 'data:text/csv;charset=utf-8,'
                                    + encodeURIComponent(rawData);
                                    
                                    var a = document.createElement('a');
                                    a.href = url;
                                    contentDisposition = request.getResponseHeader('Content-Disposition');
                                    a.download = contentDisposition.split('filename=')[1];
                                    document.body.appendChild(a); // we need to append the element to the dom -> otherwise it will not work in firefox
                                    a.click();
                                    a.remove();  //afterwards we remove the element again
                                    if (shouldCleanAfterDownload) {
                                        cleanAll();
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.error(errorThrown);
                                },
                                // progress: function() {
                                //     if (shouldCleanAfterDownload) {
                                //         cleanAll();
                                //     }
                                // }
                            });
                        };
                    });
                </script>

                <?php 
                $minimumCapabilityToRun = $this->getWaConfigOption($this->eConfOptATestsRunForCabability, 'administrator');
                if (!is_admin() || !current_user_can($minimumCapabilityToRun)) {
                    $this->debug("wa-config TEST RUN can be done by {$minimumCapabilityToRun} only.");
                    echo "<p> " . __("Pour plus d'informations, nécessite une capacité ou un rôle :", 'wa-config') . " {$minimumCapabilityToRun} </p>";
                    return;
                }
                $siteUrl = site_url();
                global $wp;
                $current_url = add_query_arg($wp->query_vars, home_url($wp->request));
                echo "<h1> " . __("CRITIQUE : prevoir un roolback SQL", 'wa-config') . " </h1>";
                echo "<p> " . __("<strong>ATTENTION :</strong> Assurez vous de pouvoir modifier votre base de données en dehors de WordPress pour recharger cette dernière via le backup SQL suivant en cas d'echec de roolback des actions de tests (ex : accès phpmyadmin) : ", 'wa-config') . " </p>";
                if (current_user_can('administrator')) {
                    echo "<p> " . ("<strong> DB_NAME : '" . DB_NAME . "'</strong> <br />" . "<strong> DB_USER : '" . DB_USER . "'</strong> <br />" . "<strong> DB_PASSWORD : <span class='wa-show-pass-on-hover'>'" . DB_PASSWORD . "'</span></strong> <br />" . "<strong> DB_HOST : '" . DB_HOST . "'</strong> <br />" . "<strong> DB_CHARSET : '" . DB_CHARSET . "'</strong> <br />") . " </p>";
                }
                $bckupSQLUrl = add_query_arg(['action' => 'wa-e2e-test-action', 'wa-action' => 'do-backup', 'wa-backup-type' => 'sql', 'wa-compression-type' => '.sql.gz'], admin_url('admin-ajax.php'));
                echo "<p>[{$this->iId}] <strong><a\n                href='{$bckupSQLUrl}'\n                >" . __("Cliquer ici pour effectuer et télécharger le backup SQL avant de lancer les tests.", 'wa-config') . "</a></strong></p>";
                $bckupSimpleZipUrl = add_query_arg(['action' => 'wa-e2e-test-action', 'wa-action' => 'do-backup', 'wa-backup-type' => 'simple-zip'], admin_url('admin-ajax.php'));
                echo "<p>[{$this->iId}] <strong><a\n                href='{$bckupSimpleZipUrl}'\n                >" . __("Cliquer ici pour effectuer et télécharger le backup Zip simple (SQL + fichiers d'Upload).", 'wa-config') . "</a></strong></p>";
                $bckupFullZipUrl = add_query_arg(['action' => 'wa-e2e-test-action', 'wa-action' => 'do-backup', 'wa-backup-type' => 'full-zip'], admin_url('admin-ajax.php'));
                echo "<p>[{$this->iId}] <strong><a\n                href='{$bckupFullZipUrl}'\n                >" . __("Cliquer ici pour effectuer et télécharger le backup Zip complet (SQL + tous les fichiers WordPress depuis la racine du site web).", 'wa-config') . "</a></strong></p>";
                $bckupATestUrl = add_query_arg(['wa-bckup-a-tests' => true], $current_url);
                $loadATestBckupUrl = add_query_arg(['wa-load-a-tests-bckup' => true], $current_url);
                $shouldBckupATest = filter_input(INPUT_GET, 'wa-bckup-a-tests', FILTER_SANITIZE_SPECIAL_CHARS);
                $shouldLoadATestBckup = filter_input(INPUT_GET, 'wa-load-a-tests-bckup', FILTER_SANITIZE_SPECIAL_CHARS);
                if ($shouldBckupATest && $shouldLoadATestBckup) {
                    $current_url = remove_query_arg(['wa-bckup-a-tests', 'wa-load-a-tests-bckup']);
                    Notice::displayError("" . __("Ne peut backuper et charger en même temps, choisissez l'un ou l'autre s.v.p.", 'wa-config'));
                    wp_redirect($current_url);
                    wp_die();
                    return;
                }
                echo "<p>[{$this->iId}] <strong><a\n                href='{$bckupATestUrl}'\n                >" . __("Backuper les fichiers de tests dans un dossier upload privé.", 'wa-config') . "</a></strong></p>";
                echo "<p>[{$this->iId}] <strong><a\n                href='{$loadATestBckupUrl}'\n                >" . __("Charger les fichers de tests depuis le dossier upload.", 'wa-config') . "</a></strong></p>";
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
                $bckupFolder = $this->get_backup_folder();
                $bckupStructureSrc = $this->pluginRoot . "assets/backup-bootstrap";
                if (!file_exists("{$bckupFolder}/test-wa-config")) {
                    copy_dir("{$bckupStructureSrc}", "{$bckupFolder}");
                }
                $acceptanceSrcFolder = "{$this->pluginRoot}tests/acceptance";
                $acceptanceUploadFolder = "{$bckupFolder}/acceptance";
                if ($shouldBckupATest) {
                    if (file_exists($acceptanceUploadFolder)) {
                        rmdir($acceptanceUploadFolder);
                    }
                    mkdir($acceptanceUploadFolder, 0777, true);
                    copy_dir("{$acceptanceSrcFolder}", "{$acceptanceUploadFolder}");
                    Notice::displaySuccess("" . __("Backup des fichiers de test vers le backup upload privé OK", 'wa-config'));
                    wp_redirect(remove_query_arg(['wa-bckup-a-tests']));
                    wp_die();
                    return;
                }
                if ($shouldLoadATestBckup) {
                    if (file_exists($acceptanceUploadFolder)) {
                        copy_dir("{$acceptanceUploadFolder}", "{$acceptanceSrcFolder}");
                        Notice::displaySuccess("" . __("Chargement des fichiers de test depuis le backup upload OK", 'wa-config'));
                    } else {
                        Notice::displayError("" . __("Aucun backup de tests dans l'upload privé du site", 'wa-config'));
                    }
                    wp_redirect(remove_query_arg(['wa-load-a-tests-bckup']));
                    wp_die();
                    return;
                }
                $acceptanceTestsFolder = $this->pluginRoot . 'tests/acceptance';
                $aTests = list_files($acceptanceTestsFolder);
                $pFile = basename($this->pluginRoot) . "/" . basename($this->pluginFile);
                $pFileEncoded = urlencode($pFile);
                foreach ($aTests as $testFile) {
                    $testFile = str_replace($this->pluginRoot, basename($this->pluginRoot) . "/", $testFile);
                    $encodedFile = urlencode($testFile);
                    echo "<p>[{$this->iId}] <a\n                    href='{$siteUrl}/wp-admin/plugin-editor.php?file={$encodedFile}&plugin={$pFileEncoded}'\n                    >Edit <strong>{$testFile}</strong> by clicking this link.</a></p>";
                }
                $reportPath = "{$this->pluginRoot}tests/_output/results.html";
                $reportUrl = plugins_url('tests/_output/results.html', $this->pluginFile);
                if (file_exists($reportPath)) {
                    echo "<p>[{$this->iId}] <strong><a\n                    href='{$reportUrl}'\n                    target='_blank'\n                    rel='noopener noreferrer'\n                    >" . __("Cliquer ici pour visualiser le dernier rapport de test effectué", 'wa-config') . "</a></strong></p>";
                }
                $currentDirectory = getcwd();
                chdir($this->pluginRoot);
                $aTestConfigSubPath = 'tests/acceptance.suite.yml';
                echo "<p>[{$this->iId}] With config file {$aTestConfigSubPath}</p>";
                $aTestConfigFile = $this->pluginRoot . "{$aTestConfigSubPath}";
                $aTestBaseUrl = $this->getWaConfigOption($this->eConfOptATestsBaseUrl, site_url());
                $updatedConfig = "";
                $lineFilter = ['matchPreviousLine' => '/- PhpBrowser:/', 'onMatch' => function ($line) use($aTestBaseUrl) {
                    return "            url: {$aTestBaseUrl}\n";
                }];
                $handle = fopen($aTestConfigFile, "r");
                if ($handle) {
                    $didMatchPreviousLine = false;
                    while (($line = fgets($handle)) !== false) {
                        $updatedConfig .= $didMatchPreviousLine ? $lineFilter['onMatch']($line) : $line;
                        $didMatchPreviousLine = preg_match($lineFilter['matchPreviousLine'], $line);
                    }
                    fclose($handle);
                } else {
                    $this->err("wa-config fail to load acceptance config test file {$aTestConfigFile}");
                    echo "<p> " . __("Echec du chargement du fichier de configuration : " . $aTestConfigFile, 'wa-config') . "</p>";
                    return;
                }
                echo "<pre>{$updatedConfig}</pre>";
                file_put_contents($aTestConfigFile, $updatedConfig);
                $runCodecept = filter_input(INPUT_GET, 'run-codecept', FILTER_SANITIZE_SPECIAL_CHARS);
                $runLink = add_query_arg(['run-codecept' => true], $current_url);
                if (!$runCodecept) {
                    echo "<h1><a href='{$runLink}'>" . __("Cliquer ici pour lancer les tests", 'wa-config') . "</a></h1>";
                    echo "<p><strong> " . __("ATTENTION : Ces tests sont lancés sur l'url de production : ", 'wa-config') . "<br />{$aTestBaseUrl}" . "</strong></p>";
                    echo "<h2> " . __("Prenons soin des données de productions. Utilisons une solution de backup ou de rollback dans la mise en oeuvre des tests.", 'wa-config') . "</h2>";
                    return;
                }
                if (!is_admin() || !current_user_can($minimumCapabilityToRun)) {
                    $this->err("wa-config TEST RUN can be done by {$minimumCapabilityToRun} only.");
                    echo "<p> " . __("Cette opérations ADMIN nécessite une capacité :", 'wa-config') . " {$minimumCapabilityToRun} </p>";
                    return;
                }
                echo "<p>[{$this->iId}] Running acceptance tests from {$this->pluginName}</p>";
                $pharName = 'codecept.phar';
                $pharPath = $this->pluginRoot . "tools/{$pharName}";
                try {
                    $p = new \Phar($pharPath, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME, $pharName);
                } catch (\UnexpectedValueException $e) {
                    $this->err("FAIL {$pharName} at {$pharPath}");
                    die("Could not open {$pharName}");
                } catch (\BadMethodCallException $e) {
                    echo 'technically, this cannot happen';
                }
                if (file_exists($this->pluginRoot . "vendor")) {
                    rename($this->pluginRoot . "vendor", $this->pluginRoot . "_vendor");
                }
                $autoloadFile = 'phar://codecept.phar/vendor/codeception/codeception/autoload.php';
                require_once $autoloadFile;
                set_time_limit(15 * 60);
                $Codecept = new \Codeception\Codecept(array('steps' => true, 'verbosity' => 1, 'seed' => time(), 'html' => 'results.html', 'colors' => false, 'no-redirect' => true, 'silent' => true, 'interactive' => false));
                $Codecept->run('acceptance');
                $Codecept->printResult();
                if (file_exists($this->pluginRoot . "_vendor")) {
                    rename($this->pluginRoot . "_vendor", $this->pluginRoot . "vendor");
                }
                chdir($currentDirectory);
                ?>
                <script>
                    function resizeCodeceptIframe(oFrame) {
                        oFrame.style.height = oFrame.contentWindow.document.documentElement.scrollHeight + 'px';
                        oFrame.contentWindow.document.onclick = function() {
                            oFrame.style.height = oFrame.contentWindow.document.documentElement.scrollHeight + 'px';
                        };
                    }
                </script>
                <style>
                    iframe {
                        width: 100%;
                    }
                </style>
<?php 
                echo "<iframe src='{$reportUrl}' \n                scrolling='no' marginwidth='0' marginheight='0' vspace='0' hspace='0'\n                frameborder='0' onload='resizeCodeceptIframe(this)'></iframe>";
            }
            public function e_admin_config_render_doc_section() : void
            {
                if (!is_admin()) {
                    $this->warn("e_admin_config_render_doc_section need to be called from admin areas.");
                    return;
                }
                echo "<p>[{$this->iId}] Showing documentation from {$this->pluginName}</p>";
                $readMePdfUrl = plugins_url("ReadMe.pdf", $this->pluginFile);
                $readMePdfUrlEncoded = urlencode($readMePdfUrl);
                $readMeTitle = "<h1>ReadMe.pdf <a href='{$readMePdfUrl}' target='_blank'>" . __("Télécharger", 'wa-config') . '<span class="dashicons dashicons-download"></span>' . "</a></h1>";
                $viewerUrl = plugins_url("assets/pdfjs/web/viewer.html", $this->pluginFile) . '?file=';
                $readMeDevPdfUrl = plugins_url("ReadMeDev.pdf", $this->pluginFile);
                $readMeDevPdfUrlEncoded = urlencode($readMeDevPdfUrl);
                $readMeDevTitle = "<h1>ReadMeDev.pdf <a href='{$readMeDevPdfUrl}' target='_blank'>" . __("Télécharger", 'wa-config') . '<span class="dashicons dashicons-download"></span>' . "</a></h1>";
                $extraDocFolder = $this->pluginRoot . 'doc-extra';
                $docFiles = list_files($extraDocFolder);
                if ($docFiles) {
                    usort($docFiles, 'strnatcasecmp');
                }
                $extraDocumentation = "<h1>" . __("Documentation supplémentaire", 'wa-config') . "</h1>";
                foreach ($docFiles as $docFile) {
                    $type = wp_check_filetype($docFile);
                    if ('pdf' !== $type['ext']) {
                        continue;
                    }
                    $relativeDocPath = str_replace($this->pluginRoot, "", $docFile);
                    $docUrl = plugins_url($relativeDocPath, $this->pluginFile);
                    $docIframe = <<<TEMPLATE
    <div>
        <iframe
        class="wa-pdf-read-me"
        src="{$viewerUrl}{$docUrl}"
        title="webviewer"
        frameborder="0"
        onload="wa_resizeDocIframe(this)"
        width="100%">
        </iframe>
    </div>
TEMPLATE;
                    $extraDocumentation .= "<h2>{$relativeDocPath}" . "<a href='{$docUrl}' target='_blank'>" . __("Télécharger", 'wa-config') . '<span class="dashicons dashicons-download"></span>' . "</a></h2>{$docIframe}";
                }
                $docIndex = plugins_url("doc/index.html", $this->pluginFile);
                echo <<<TEMPLATE
<script>
    function wa_resizeDocIframe(oFrame) {
        var resizeator = function(frame) {
            var fMainClass = oFrame.getAttribute("class").split(' ')[0];
            if ("wa-php-doc" === fMainClass) {
                oFrame.style.height = oFrame.contentWindow.document.documentElement.scrollHeight + 'px';
            } else {
                // Fail back for specific iframe like pdf etc...
                // since previous height check sound fixed to 150px, with page scroll still used inside...
                oFrame.style.height = oFrame.contentWindow.outerHeight + 'px'; // document.documentElement.scrollHeight + 'px';
            }
        }
        resizeator(oFrame);
        // oFrame.contentWindow.document.onload = function() {
        //    resizeator(oFrame); // Already called by above, right ?
        // };
        oFrame.contentWindow.document.onclick = function() {
            resizeator(oFrame);
        };
    }
</script>
<div>
    <iframe
    class="wa-php-doc"
    scrolling='no'
    src="{$docIndex}"
    title="Php documentation"
    frameborder="0"
    onload="wa_resizeDocIframe(this)"
    width="100%">
    </iframe>
</div>
{$readMeTitle}
<div>
    <iframe
    class="wa-pdf-read-me"
    src="{$viewerUrl}{$readMePdfUrlEncoded}"
    title="webviewer"
    frameborder="0"
    onload="wa_resizeDocIframe(this)"
    width="100%">
    </iframe>
</div>
{$readMeDevTitle}
<div>
    <iframe
    class="wa-pdf-read-me-dev"
    src="{$viewerUrl}{$readMeDevPdfUrlEncoded}"
    title="webviewer"
    frameborder="0"
    onload="wa_resizeDocIframe(this)"
    width="100%">
    </iframe>
</div>
{$extraDocumentation}
TEMPLATE;
            }
        }
    }
    if (!trait_exists(ExtendablePluginDescription::class)) {
        trait ExtendablePluginDescription
        {
            use Editable;
            use Identifiable;
            protected function _020_ext_plugin_description__load()
            {
                add_filter('plugin_row_meta', [$this, 'ext_plugin_description_meta'], 10, 2);
            }
            function ext_plugin_description_meta($plugin_meta, $plugin_file)
            {
                if ($plugin_file == plugin_basename($this->pluginFile)) {
                    $plugin_meta[] = $this->pluginName;
                    $plugin_meta[] = $this->iId;
                }
                return $plugin_meta;
            }
        }
    }
    if (!class_exists(OptiLvl::class)) {
        class OptiLvl
        {
            const MEDIUM = 'medium';
            const MAX = 'full';
        }
    }
    if (!trait_exists(Optimisable::class)) {
        trait Optimisable
        {
            use Editable;
            use Identifiable;
            protected function _020_opti__bootstrap()
            {
                if (defined('DOING_CRON')) {
                    return;
                }
                $optiLvls = explode(',', $this->getWaConfigOption($this->eConfOptOptiLevels, ""));
                $this->debugVeryVerbose('Requesting optimisation levels : ', ["levels" => $optiLvls]);
                if (false !== array_search(OptiLvl::MAX, $optiLvls)) {
                    $this->opti_setup_for_max_speed();
                    add_action(WPActions::wa_do_base_review_preprocessing, [$this, 'opti_max_speed_review']);
                } else {
                    if (false !== array_search(OptiLvl::MEDIUM, $optiLvls)) {
                        $this->opti_setup_for_medium_speed();
                        add_action(WPActions::wa_do_base_review_preprocessing, [$this, 'opti_medium_speed_review']);
                        if (false === array_search(OptiLvl::MAX, $optiLvls)) {
                            $this->reviewIdsToTrash = array_merge($this->reviewIdsToTrash, ["{$this->iId}-data-review-opti-full"]);
                        }
                    } else {
                        if (count($optiLvls) > 1) {
                            $this->warn('Unknown Optimisation levels : ', $optiLvls);
                        }
                        add_action(WPActions::wa_do_base_review_preprocessing, [$this, 'opti_disabled_review']);
                    }
                }
                add_action(WPActions::wa_do_base_review_preprocessing, [$this, 'opti_common_review']);
                add_filter('pre_http_request', [$this, 'opti_filter_wp_http_requests'], 10, 3);
            }
            public function opti_filter_wp_http_requests($preempt, $parsed_args, $url)
            {
                $regExFilter = $this->getWaConfigOption($this->eConfOptOptiWpRequestsFilter, "");
                if (is_string($regExFilter) && strlen($regExFilter) && preg_match($regExFilter, $url)) {
                    $this->debug("Will opti_filter_wp_http_requests with {$regExFilter} and BLOCK {$url}");
                    $safeFilter = $this->getWaConfigOption($this->eConfOptOptiWpRequestsSafeFilter, $this->E_DEFAULT_OPTIMISABLE_SAFE_FILTER);
                    if (is_string($safeFilter) && strlen($safeFilter) && preg_match($safeFilter, $url)) {
                        $this->debugVerbose("opti_filter_wp_http_requests whitelisted by {$safeFilter}");
                        return $preempt;
                    }
                    $enableNotice = $this->getWaConfigOption($this->eConfOptOptiEnableBlockedHttpNotice, false);
                    if ($enableNotice) {
                        Notice::displayInfo("{$regExFilter} " . __(" : BLOQUE l'url : ", 'wa-config') . " {$url}");
                    }
                    $this->debugVerbose("opti_filter_wp_http_requests blocked by {$regExFilter}");
                    return array('headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array(), 'http_response' => null);
                }
                return $preempt;
            }
            protected function opti_setup_for_max_speed() : bool
            {
                if (!$this->opti_setup_for_medium_speed()) {
                    return false;
                }
                $this->debugVerbose("Will opti_setup_for_max_speed");
                add_filter('plugins_auto_update_enabled', '__return_false');
                add_filter('themes_auto_update_enabled', '__return_false');
                $WPAutoUpdateOff = defined('AUTOMATIC_UPDATER_DISABLED') && constant('AUTOMATIC_UPDATER_DISABLED');
                $WPHostAutoUpdateOff = defined('WP_AUTO_UPDATE_CORE') && !constant('WP_AUTO_UPDATE_CORE');
                return $WPAutoUpdateOff && $WPHostAutoUpdateOff;
            }
            protected function opti_setup_for_medium_speed() : bool
            {
                $this->debugVerbose("Will opti_setup_for_medium_speed");
                return defined('DISABLE_WP_CRON');
            }
            public function opti_max_speed_review($app) : void
            {
                $this->debugVerbose("Will opti_max_speed_review");
                $skillsSyncOK = true;
                $reviewReport = '';
                $WPAutoUpdateOff = defined('AUTOMATIC_UPDATER_DISABLED') && constant('AUTOMATIC_UPDATER_DISABLED');
                if (!$WPAutoUpdateOff) {
                    $reviewReport .= __("<p> AUTOMATIC_UPDATER_DISABLED doit être définit à 'true' dans wp-config.php.</p>");
                    $this->warn("Fail to ensure AUTOMATIC_UPDATER_DISABLED is true");
                }
                $skillsSyncOK = $skillsSyncOK && $WPAutoUpdateOff;
                $WPHostAutoUpdateOff = defined('WP_AUTO_UPDATE_CORE') && !constant('WP_AUTO_UPDATE_CORE');
                if (!$WPHostAutoUpdateOff) {
                    $reviewReport .= __("<p> WP_AUTO_UPDATE_CORE doit être définit à 'false' dans wp-config.php.</p>");
                    $this->warn("Fail to ensure WP_AUTO_UPDATE_CORE is defined and falsy");
                }
                $skillsSyncOK = $skillsSyncOK && $WPHostAutoUpdateOff;
                $this->e_admin_config_add_check_list_to_review(['category' => __('02 - Maintenance', 'wa-config'), 'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>', 'title' => __("03 - [Optimisations] Niveau maximal", 'wa-config'), 'title_icon' => '<span class="dashicons dashicons-chart-bar"></span>', 'requirements' => __('Vérification de la présence des optimisations maximales.<br />', 'wa-config') . $reviewReport, 'value' => strlen($reviewReport) ? $skillsSyncOK ? null : __('Ajustez les configurations nécessaires puis rafraichir cette page.') : '', 'result' => $skillsSyncOK, 'is_activated' => true, 'fixed_id' => "{$this->iId}-data-review-opti-full", 'is_computed' => true]);
                $this->opti_medium_speed_review($app);
            }
            public function opti_medium_speed_review($app) : void
            {
                $this->debugVerbose("Will opti_medium_speed_review");
                $skillsSyncOK = true;
                $reviewReport = '';
                $cronDisabled = defined('DISABLE_WP_CRON') && constant('DISABLE_WP_CRON');
                if (!$cronDisabled) {
                    $reviewReport .= __("<p> DISABLE_WP_CRON doit être définit à 'true' dans wp-config.php et vos tache cron géré par un autre service externe.</p>");
                    $this->warn("Fail to ensure DISABLE_WP_CRON is true");
                }
                $skillsSyncOK = $skillsSyncOK && $cronDisabled;
                $this->e_admin_config_add_check_list_to_review(['category' => __('02 - Maintenance', 'wa-config'), 'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>', 'title' => __("03 - [Optimisations] Niveau moyen", 'wa-config'), 'title_icon' => '<span class="dashicons dashicons-chart-bar"></span>', 'requirements' => __('Vérification de la présence des optimisations moyennes.<br />', 'wa-config') . $reviewReport, 'value' => strlen($reviewReport) ? $skillsSyncOK ? null : __('Ajustez les configurations nécessaires puis rafraichir cette page.') : '', 'result' => $skillsSyncOK, 'is_activated' => true, 'fixed_id' => "{$this->iId}-data-review-opti-medium", 'is_computed' => true]);
            }
            public function opti_disabled_review($app) : void
            {
                $this->debugVerbose("Will opti_disabled_review");
                $this->reviewIdsToTrash = array_merge($this->reviewIdsToTrash, ["{$this->iId}-data-review-opti-medium", "{$this->iId}-data-review-opti-full"]);
            }
            public function opti_common_review($app) : void
            {
                $this->debugVerbose("Will opti_common_review");
                $self = $this;
                $urlBuilder = function ($pluginSlug) {
                    return admin_url("plugin-install.php?tab=plugin-information&plugin={$pluginSlug}&TB_iframe=true&width=640&height=500");
                };
                $plugins = get_option('active_plugins', []);
                $plugins = array_map(function ($p) {
                    return dirname($p);
                }, $plugins);
                $pluginReviewer = function ($pluginRequest, $pluginLinkTitle, $pluginSlug, $extraPlugins = []) use($self, $plugins, $urlBuilder) {
                    $url = $urlBuilder($pluginSlug);
                    $isPluginActivated = false;
                    $activationReport = "";
                    $localPluginBase = $pluginSlug;
                    if (false !== ($loadOrder = array_search($localPluginBase, $plugins))) {
                        $isPluginActivated = true;
                    }
                    $this->debugVeryVerbose("Plugin check : ", $localPluginBase, $plugins);
                    $extras = "";
                    foreach ($extraPlugins as $extraPluginSlug => $extraPluginData) {
                        if (is_string($extraPluginData)) {
                            $extraPluginData = ['title' => $extraPluginData, 'type' => 'extra'];
                        }
                        if ('alternative' === $extraPluginData['type'] && !$isPluginActivated) {
                            if (false !== ($loadOrder = array_search($extraPluginSlug, $plugins))) {
                                $isPluginActivated = true;
                                $activationReport .= __('Alternative OK :', 'wa-config') . " {$extraPluginSlug}";
                            }
                        }
                        $extraUrl = $urlBuilder($extraPluginSlug);
                        $extras .= "<p><a\n                        class='thickbox open-plugin-details-modal'\n                        data-title='{$extraPluginData['title']}'\n                        href='{$extraUrl}'>\n                          {$extraPluginData['title']}\n                        </a></p>";
                    }
                    $this->e_admin_config_add_check_list_to_review(['category' => __('02 - Maintenance', 'wa-config'), 'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>', 'title' => __("01 - Plugin : ", 'wa-config') . $localPluginBase, 'title_icon' => '<span class="dashicons dashicons-dashboard"></span>', 'requirements' => "{$pluginRequest} <a class='thickbox open-plugin-details-modal' data-title='{$pluginLinkTitle}' href='{$url}'>{$pluginLinkTitle}</a> {$extras}", 'value' => $isPluginActivated ? $activationReport : __('Validation humaine requise.', 'wa-config'), 'result' => $isPluginActivated, 'is_activated' => true, 'fixed_id' => "{$this->iId}-check-plugin-{$localPluginBase}", 'is_computed' => true]);
                };
                $pluginReviewer(__('Internationalisation continue de votre contenu web en activant le plugin :', 'wa-config'), __('Polylang', 'wa-config'), 'polylang', ['loco-translate' => __('Bonus : Loco Translate', 'wa-config'), 'automatic-translator-addon-for-loco-translate' => __('Bonus : Automatic Translate Addon For Loco Translate', 'wa-config'), 'translatepress-multilingual' => ['type' => 'alternative', 'title' => __('Alternative : TranslatePress - Multilingual', 'wa-config')], 'automatic-translate-addon-for-translatepress' => __('Bonus : Automatic Translate Addon For TranslatePress', 'wa-config'), 'gtranslate' => ['type' => 'alternative', 'title' => __('Alternative : Translate WordPress with GTranslate', 'wa-config')]]);
                $pluginReviewer(__('Suivi des actions des utilisateurs réel en activant le plugin :', 'wa-config'), __('History Log by click5', 'wa-config'), 'history-log-by-click5');
                $pluginReviewer(__('Suivi des emails en activant le plugin :', 'wa-config'), __('Check & Log Email', 'wa-config'), 'check-email');
                $pluginReviewer(__('Suivi des CRON en activant le plugin :', 'wa-config'), __('WP Crontrol', 'wa-config'), 'wp-crontrol');
                $pluginReviewer(__('Ajustements divers de la base de données en activant le plugin :', 'wa-config'), __('Better Search Replace', 'wa-config'), 'better-search-replace');
                $pluginReviewer(__('Ajustements des post et taxonomies personalisées en activant le plugin :', 'wa-config'), __('Pods – Custom Content Types and Fields', 'wa-config'), 'pods', ['custom-post-type-widgets' => __('Bonus : Custom Post Type Widgets', 'wa-config')]);
                if ($this->shouldDebug || $this->shouldDebugVerbose || $this->shouldDebugVeryVerbose) {
                    $pluginReviewer(__('ATTENTION : MODE DEBUG activé, non conseillé en production. Cela dis, optimisons les débugs en activant le plugin :', 'wa-config'), __('Query Monitor', 'wa-config'), 'query-monitor');
                } else {
                    $this->reviewIdsToTrash = array_merge($this->reviewIdsToTrash, ["{$this->iId}-check-plugin-query-monitor"]);
                }
            }
        }
    }
}
namespace WA\Config\Frontend {
    use WA\Config\Core\Debugable;
    use WA\Config\Core\Editable;
    use WA\Config\Core\EditableWaConfigOptions;
    use WA\Config\Core\WPFilters;
    if (!trait_exists(EditableScripts::class)) {
        trait EditableScripts
        {
            use Editable;
            protected function _010_e_scripts__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_scripts__load')) {
                    return;
                }
                add_action('wp_enqueue_scripts', [$this, 'e_scripts_do_enqueue']);
            }
            public function e_scripts_do_enqueue() : void
            {
                $this->debugVerbose("Will e_scripts_do_enqueue");
                $jsFile = "assets/app.js";
                wp_enqueue_script('wa-config-js', plugins_url($jsFile, $this->pluginFile), [], $this->pluginVersion, true);
            }
        }
    }
    if (!trait_exists(EditableStyles::class)) {
        trait EditableStyles
        {
            use Editable;
            protected function _010_e_styles__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_styles__load')) {
                    return;
                }
                add_action('wp_enqueue_scripts', [$this, 'e_styles_do_enqueue']);
            }
            public function e_styles_do_enqueue() : void
            {
                $this->debugVerbose("Will e_styles_do_enqueue");
                $cssFile = "assets/styles.css";
                wp_enqueue_style('wa-config-css', plugins_url($cssFile, $this->pluginFile), [], $this->pluginVersion);
            }
        }
    }
    if (!trait_exists(EditableFooter::class)) {
        trait EditableFooter
        {
            use EditableWaConfigOptions, Debugable;
            protected function _010_e_footer__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_footer__load')) {
                    return;
                }
                $currentTheme = basename(get_parent_theme_file_path());
                $enableFooter = boolVal($this->getWaConfigOption($this->eConfOptEnableFooter, true));
                if ($enableFooter) {
                    $this->debugVerbose("Will _010_e_footer__load for theme '{$currentTheme}'");
                    add_filter('storefront_credit_links_output', [$this, 'e_footer_render']);
                    if ('twentytwenty' === $currentTheme || 'restaurant-food-delivery' === $currentTheme) {
                        add_action('wp_footer', [$this, 'e_footer_do_wp_footer_twentytwenty'], 20);
                    }
                    if ('oceanwp' === $currentTheme) {
                        add_action('ocean_after_footer_bottom_inner', [$this, 'e_footer_do_wp_footer_twentytwenty'], 20);
                    }
                    if ('twentytwentytwo' === $currentTheme) {
                        add_filter('render_block', [$this, 'e_footer_filter_render_block_twentytwentytwo'], null, 3);
                    }
                } else {
                    $this->debugVerbose("_010_e_footer__load not enabled, cf WA Admin configs params");
                }
            }
            function e_footer_filter_render_block_twentytwentytwo(string $block_content, array $block, \WP_Block $bInst) : string
            {
                $blockName = $block['blockName'] ?? "__no-block-name__";
                $bInnerHTML = $block['innerHTML'];
                $this->debugVeryVerbose("Will e_footer_filter_render_block_twentytwentytwo for {$blockName}");
                if ($blockName === 'core/paragraph' && !is_admin() && !wp_is_json_request()) {
                    $this->debugVeryVerbose($blockName, $bInst->block_type, $bInnerHTML, $bInst->context, $bInst->available_context);
                    if (strpos($bInnerHTML, '<p class="has-text-align-right">') && strpos($bInnerHTML, 'wordpress.org" rel="nofollow">WordPress</a>')) {
                        return $this->e_footer_render(false);
                    }
                }
                return $block_content;
            }
            function e_footer_do_wp_footer_twentytwenty()
            {
                $enableFooter = boolVal($this->getWaConfigOption($this->eConfOptEnableFooter, true));
                $currentTheme = basename(get_parent_theme_file_path());
                if ($enableFooter && ('twentytwentytwo' === $currentTheme || 'oceanwp' === $currentTheme || 'restaurant-food-delivery' === $currentTheme)) {
                    ?>
                    <style>
                        /* twentytwenty */
                        .powered-by-wordpress {
                            display: none !important;
                        }
                        /* oceanwp */
                        #footer-bottom-inner #copyright {
                            display: none !important;
                        }
                        /* restaurant-food-delivery */
                        footer .mb-0.py-3.text-center.text-md-left {
                            display: none !important;
                        }
                    </style>
                    <?php 
                    echo $this->e_footer_render();
                }
            }
            private function e_footer_locate_template_NOT_USED_YET($templatePath)
            {
                $this->debugVerbose("Will try e_footer_locate_template for {$templatePath}");
                $originalPath = $templatePath;
                $templateParts = explode('/', $templatePath);
                $templateName = end($templateParts);
                $currentTheme = basename(get_parent_theme_file_path());
                if ('twentytwenty' === $currentTheme) {
                    $this->debugVeryVerbose("e_footer_locate_template overloads for {$currentTheme}");
                    $plugin_template = "{$this->pluginRoot}templates/themes/twentytwenty/{$templatePath}";
                    if (file_exists($plugin_template)) {
                        $templatePath = $plugin_template;
                    } else {
                        if ($theme_template = locate_template("{$templatePath}")) {
                            $templatePath = $theme_template;
                        }
                    }
                } else {
                    if ($theme_template = locate_template("{$templatePath}")) {
                        $templatePath = $theme_template;
                    } else {
                        $plugin_template = "{$this->pluginRoot}{$templatePath}";
                        if (file_exists($plugin_template)) {
                            $templatePath = $plugin_template;
                        }
                    }
                }
                if ($templatePath !== $originalPath) {
                    $this->debugVerbose("e_footer_locate_template update from {$originalPath} to {$templatePath}");
                }
                return $templatePath;
            }
            private function e_footer_modify_theme_include_file_NOT_USED_YET(string $path, string $file = '') : string
            {
                $this->debugVerbose("Will try e_footer_modify_theme_include_file {$file} at {$path}");
                $currentTheme = basename(get_parent_theme_file_path());
                if ('twentytwenty' === $currentTheme) {
                    $this->debugVeryVerbose("e_footer_modify_theme_include_file tesing {$file} overloads");
                    $targetOverload = plugin_dir_path(__FILE__) . "templates/themes/twentytwenty/{$file}";
                    if (file_exists($targetOverload)) {
                        $this->debugVeryVerbose("Will e_footer_modify_theme_include_file at {$file} with {$targetOverload}");
                        return $targetOverload;
                    }
                }
                return $path;
            }
            public function e_footer_render()
            {
                if (!boolVal($this->getWaConfigOption($this->eConfOptEnableFooter, true))) {
                    $this->debugVerbose("e_footer_render not enabled");
                    return false;
                }
                $this->debugVerbose("Will e_footer_render");
                $htmlFooter = null;
                $waFooterTemplate = $this->getWaConfigOption($this->eConfOptFooterTemplate, "");
                if (strlen($waFooterTemplate) > 0) {
                    $this->debugVeryVerbose("e_footer_render from eConfOptFooterTemplate");
                    $htmlFooter = $waFooterTemplate;
                } else {
                    $waFooterCredit = $this->getWaConfigOption($this->eConfOptFooterCredit, __("autre", 'wa-config'));
                    $monwooCredit = __("Build by Monwoo and", 'wa-config');
                    $htmlFooter = <<<TEMPLATE
<style>
  #wa-site-footer {
    text-align: right;
    padding: 7px;
  }
</style>
<footer id="wa-site-footer" class="header-footer-group">
    <div class="section-inner">
        <div class="footer-credits">
            <p class="powered-by-monwoo powered-by-web-agency-app">
                <a href="{$this->siteBaseHref}/credits">
                    {$monwooCredit} {$waFooterCredit}
                </a>
                <br />
                <a href="mailto:service@monwoo.com">
                    service@monwoo.com
                </a>
            </p>

        </div>
    </div>
</footer>
TEMPLATE;
                }
                $this->debugVeryVerbose("e_footer_render from eConfOptFooterCredit and custom wa-config template");
                $htmlFooter = apply_filters(WPFilters::wa_config_e_footer_render, $htmlFooter, $this);
                return $htmlFooter;
            }
        }
    }
}
namespace WA\Config {
    use WA\Config\Core\AppInterface;
    use WA\Config\Admin\EditableConfigPanels;
    use WA\Config\Admin\EditableMissionPost;
    use WA\Config\Admin\EditableSkillsTaxo;
    use WA\Config\Admin\ExtendablePluginDescription;
    use WA\Config\Admin\Optimisable;
    use WA\Config\Frontend\EditableScripts;
    use WA\Config\Frontend\EditableStyles;
    use WA\Config\Frontend\EditableFooter;
    use WA\Config\Utils\TranslatableProduct;
    $current_WA_Version = "0.0.1-alpha";
    $pFolder = basename(plugin_dir_path(__FILE__));
    if ($pFolder === 'src') {
        $pFolder = basename(plugin_dir_path(__DIR__));
    }
    $pluginSrcPath = "wp-content/plugins/" . $pFolder;
    if (class_exists(App::class)) {
        $existing_WA_Version = AppInterface::PLUGIN_VERSION;
        $app = AppInterface::instanceByRelativePath($pluginSrcPath, -1);
        $logMsg = "{$pluginSrcPath} : Will not load WA\\Config\\ since already loaded somewhere else\n        at version {$existing_WA_Version} for requested version {$current_WA_Version}";
        $waConfigTextDomain = 'wa-config';
        if ($current_WA_Version !== AppInterface::PLUGIN_VERSION) {
            AppInterface::addCompatibilityReport(__("Avertissement", $waConfigTextDomain), "{$pluginSrcPath} : {$current_WA_Version}. " . __("Version pre-chargé WA\\Config\\ non exacte : ", $waConfigTextDomain) . " {$existing_WA_Version}.");
        } else {
        }
    } else {
        class App extends AppInterface
        {
            use EditableScripts;
            use EditableStyles;
            use EditableFooter;
            use EditableConfigPanels;
            use EditableMissionPost;
            use EditableSkillsTaxo;
            use ExtendablePluginDescription;
            use Optimisable;
            use TranslatableProduct;
            public function __construct(string $siteBaseHref, string $pluginFile, string $iPrefix, $shouldDebug)
            {
                if (is_array($shouldDebug)) {
                    [$this->shouldDebug, $this->shouldDebugVerbose, $this->shouldDebugVeryVerbose] = $shouldDebug;
                } else {
                    $this->shouldDebug = $shouldDebug;
                }
                $this->siteBaseHref = $siteBaseHref;
                $this->pluginFile = $pluginFile;
                $this->pluginRoot = plugin_dir_path($this->pluginFile);
                $this->pluginName = basename($this->pluginRoot);
                $this->pluginRelativePath = "wp-content/plugins/{$this->pluginName}";
                $this->pluginVersion = AppInterface::PLUGIN_VERSION;
                AppInterface::__construct($iPrefix);
            }
        }
    }
}