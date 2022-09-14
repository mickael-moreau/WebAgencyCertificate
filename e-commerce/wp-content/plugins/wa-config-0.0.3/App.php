<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com.
 * 
 * Build from researches and developpements done by Miguel Monwoo from 2011 to 2022.
 *
 * Wa-config is a Web Agency plugin ready
 * to run **parrallel programming**
 * with **advanced debug** and **end to end testing** tools.
 * 
 * {@see WA\Config\App} come with :
 * - **Skills and missions** concepts ready to use as taxonomy and custom post type
 * - **Internaionalisation** and **WooCommerce** integration
 * - A **securised REST API** to deploy custom static HTML front head
 * - A **commonJS deploy script** to easyliy deploy your static frontend 
 * - A **review system** for all team members using this plugin
 * - **Codeception** as end to end test tool
 * - **PhpDocumentor output** as an up to date HTML documentation
 * - **Pdf.js** for quick display of main documentation files
 * - results of **Miguel Monwoo R&D** for **parallel programmings** and **advanced integrations**
 * 
 * {@link https://moonkiosk.monwoo.com/en/missions/wa-config-monwoo_en Product owner}
 *
 * {@link https://codeception.com/docs/03-AcceptanceTests End to end test documentation}
 *
 * {@link https://github.com/mozilla/pdf.js PDF viewer lib}
 * 
 * @global *{@see \WA\Config\Core\AppInterface AppInterface}* **$_wa_fetch_instance**
 *    Function to get the first wa-config instance
 * 
 *    **@param** *int* **$idx** Optional, wa-config instance index, **default to 0**
 *    {@example
 *    ```php
 *    $app = $_wa_fetch_instance();
 *    ```}
 * 
 * @link    https://miguel.monwoo.com Miguel Monwoo R&D
 * @link    https://www.monwoo.com/don Author Donate link
 * @since   0.0.1
 * @package
 * @author  service@monwoo.com
 */
namespace {
    use WA\Config\Core\AppInterface;
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    if (!defined('WPINC')) {
        exit; 
    }
    if (!function_exists(_wa_e2e_tests_wp_die_handler::class)) {
        /**
         * Avoid WordPress killing execution for end to end test run
         *
         * @since 0.0.1
         * @access private
         *
         * @param string       $message Error message.
         * @param string       $title   Optional. Error title (unused). Default empty.
         * @param string|array $args    Optional. Arguments to control behavior. Default empty array.
         */
        function _wa_e2e_tests_wp_die_handler( $message, $title = '', $args = array() ) {
            AppInterface::exitAll();
            $inst = AppInterface::instance();
            $inst->debug(
                "Will _wa_e2e_tests_wp_die_handler $title", $message
            );
            $inst->debug(
                "At :",
                $inst->debug_trace()
            );
            $inst->debugVeryVerbose(" with :", $args);
        }
    }
}
namespace WA\Config\Core {
    use function WA\Config\Utils\strEndsWith;
    use function WA\Config\Utils\wa_filesystem;
    use function WA\Config\Utils\wa_redirect;
    use Exception;
    use QueryMonitor;
    use QM_Dispatchers;
    use RecursiveDirectoryIterator;
    use RecursiveIteratorIterator;
    use WA\Config\Admin\Notice;
    use WA\Config\Admin\EditableConfigPanels;
    use WA\Config\Admin\EditableSkillsTaxo; 
    use WA\Config\Admin\OptiLvl; 
    use WA\Config\Admin\EditableReview; 
    use WA\Config\Frontend\EditableFooter; 
    use WA\Config\Utils\DumpGzip;
    use WA\Config\Utils\DumpPlainTxt;
    use WA\Config\Utils\DumpZip;
    use WA\Config\Utils\InsertSqlStatement;
    use WA\Config\Utils;
    use WP;
    use WP_Error;
    use WP_Filesystem_Direct;
    use wpdb;
    use ZipArchive;
    if (!class_exists(WPFilters::class)) { 
        /**
         * This class register all wa-config WordPress filters like an ENUM
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class WPFilters {
            /**
             * Filters the HTML Editable footer rendered by wa-config package 
             *
             * **@param** *string*        **$htmlFooter**  The HTML that will be rendered 
             * for the footer
             * 
             * **@param** *{@see AppInterface}*   **$instance**    The {@see AppInterface} 
             * that is currently configuring the footer to render.
             * 
             * **@return** *string*       The HTML that will be rendered
             * 
             * @see EditableFooter::e_footer_render()
             * @since 0.0.1
             */
            const wa_e_footer_render = 'wa_e_footer_render';
            /**
             * Filters the reviews fixed_id to trash at the end of the base review
             *
             * **@param** *string[]*      **$idsToTrash**  The 'fixed_id' that will be 
             * trashed if present
             * 
             * **@param** *{@see AppInterface}*  **$instance**    The {@see AppInterface} 
             * plugin instance
             * 
             * **@return** *string[]*     The 'fixed_id' that will be trashed
             * 
             * @see EditableReview::e_review_data_add_base_review()
             * @since 0.0.1
             */
            const wa_base_review_ids_to_trash = 'wa_base_review_ids_to_trash';
            /**
             * Filters the skill terms to ensure for the base review
             * 
             * 
             * **@param** *array*   **$termsToEnsure**  The terms that need to be ensured.
             * Cf {@see EditableSkillsTaxo::e_skill_taxo_data_review() methode usage}
             * 
             * **@param** *string*  **$locale**     The locale used to ensure thoses fields (Wordpress)
             * 
             * **@param** *string*  **$localeSlug** The locale slug used to ensure thoses fields (Polylang)
             * 
             * **@return** *array*   The terms that need to be ensured.
             * 
             * {@see EditableSkillsTaxo::e_skill_taxo_data_review()}
             * {@see TestableSamples::test_sample_wa_base_review_skill_terms_to_ensure()}
             * 
             * 
             * Example working with native 'wp i18n make-pot' or loco translate plugin :
             * ```php
             * // Somewhere at the begining of your namespace :
             * use WA\Config\Utils;
             *
             * // Then, in your code :
             * 
             *  $app = $this;
             *  add_filter(WPFilters::wa_base_review_skill_terms_to_ensure, function (
             *      $termsToEnsure, $locale, $localeSlug
             *  ) use($app) {
             *      $termsToEnsure = array_merge($termsToEnsure, [
             *          // ensureIdentifier, must be unique, and will be reused in '_parentEnsureID' term arguments
             *          'health-care' => [
             *              // Term Title
             *              Utils\_x( 'Plaisir' , 'wa-skill term title', 'wa-config', $locale),
             *              [
             *                  // Term description
             *                  'description' => Utils\__("Expertise en bien-être", 'wa-config', $locale),
             *                  // Term slug. Must be UNIQUE over ALL languages.
             *                  // We comonly use suffix '_$localeSlug' for slugs having same names over different languages
             *                  'slug'        => Utils\_x('health-care' , 'wa-skill term slug', 'wa-config', $locale),
             *              ],
             *          ],
             *          'physiological-health-care' => [
             *              Utils\_x( 'Plaisir physiologique' , 'wa-skill term title', 'wa-config', $locale),
             *              [
             *                  'description' => Utils\__("Expertise en Plaisir de l'activité de l'organisme humain", 'wa-config', $locale),
             *                  'slug'        => Utils\_x('physiological-health-care' , 'wa-skill term slug', 'wa-config', $locale),
             *                  '_parentEnsureID' => 'health-care',
             *              ],
             *          ],
             *          'organisational-health-care' => [
             *              Utils\_x( 'Plaisir organisationnel' , 'wa-skill term title', 'wa-config', $locale),
             *              [
             *                  'description' => Utils\__("Expertise organisationnel pour se sentir bien ou améliorer un Plaisir relationnel ou physiologique", 'wa-config', $locale),
             *                  'slug'        => Utils\_x('organisational-health-care' , 'wa-skill term slug', 'wa-config', $locale),
             *                  '_parentEnsureID' => 'health-care',
             *              ],
             *          ],
             *      ]);
             *      $app->info("TestableSamples test_sample_wa_base_review_skill_terms_to_ensure : ", $termsToEnsure);
             *      return $termsToEnsure;
             *  }, 10, 3);
             * }
             * ```
             *
             * @since 0.0.1
             */
            const wa_base_review_skill_terms_to_ensure = 'wa_base_review_skill_terms_to_ensure';
            /**
             * Filters the IP used by protected functions of wa-config
             *
             * **@param** *string*    **$ip**  The computed IP
             * 
             * **@return** *string*   The filtered IP
             *
             * @since 0.0.1
             */
            const wa_get_ip = 'wa_get_ip';
        }
    }
    if (!class_exists(WPActions::class)) { 
        /**
         * This class register all wa-config WordPress actions like an ENUM
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class WPActions {
            /**
             * Fire after admin config parameters panel renderings.
             *
             * @since 0.0.1
             * @see EditableConfigPanels::e_config_param_render_panel()
             *
             * @param AppInterface $app the plugin instance.
             */
            const wa_ecp_render_after_parameters = 'wa_ecp_render_after_parameters';
            /**
             * Fire **before** base review is computed for the review panel.
             * 
             * This action can be used to add more 
             * e_review_data_check_insert to the base review.
             *
             * @since 0.0.1
             * @see EditableReview::e_review_data_add_base_review()
             * 
             * @param AppInterface $app the plugin instance.
             */
            const wa_do_base_review_preprocessing = 'wa_do_base_review_preprocessing';
            /**
             * Fire **after** base review is computed for the review panel.
             * 
             * This action can be used to add more 
             * e_review_data_check_insert to the base review.
             *
             * @since 0.0.1
             * @see EditableReview::e_review_data_add_base_review()
             *
             * @param AppInterface $app the plugin instance.
             */
            const wa_do_base_review_postprocessing = 'wa_do_base_review_postprocessing';
        }
    }
    if (!trait_exists(TestableEnd2End::class)) { 
        /**
         * This trait will load the required files to launch end to end tests
         * 
         * To launch codeception tests, we use the file 'tools/codecept.phar'
         * 
         * This file is NOT RECOMMENDED by the WordPress Plugin Review Team 2022.
         * 
         * Indeed, 'codecept.phar' contains non-human readable code.
         * Human reviews and security tools may fail to validate this file...
         * 
         * Feature update for an 'administrator' account :
         *  - ajax admin action downloading the codecept.phar needed to launch the end to end tests
         *  over the production data.
         *  - clickable ajax action link inside "WA Config" -> "Quality review" panel to solve 
         *  the missing "tools/codecept.phar" file with a warning about security risk.
         *  - add a common quality review about risky non-human readable code failing if "tools/codecept.phar" exist with a clickable ajax action to remove it .
         *
         * @since 0.0.2
         * @author service@monwoo.com
         */
        trait TestableEnd2End
        {
            protected function _000_test_e2e__bootstrap() {
                $this->codeceptSource = sanitize_url(constant('WA_Config_E2E_CODECEPTION_SRC'));
                $this->docAndTestsDatasetSource = sanitize_url(constant('WA_Config_DATASET_DOC_AND_TESTS_SRC'));
                if ($this->p_higherThanOneCallAchievedSentinel('_000_test_e2e__bootstrap')) {
                    return; 
                }
                add_action(
                    'wp_ajax_wa-testable-e2e', 
                    [$this, 'test_e2e_action']
                );
                add_action(
                    WPActions::wa_do_base_review_preprocessing,
                    [$this, 'test_e2e_action_base_review']
                );
            }
            /**
             * Review the default acceptance end to end test launcher
             * 
             * @param AppInterface $app the plugin instance.
             */
            public function test_e2e_action_base_review($app): void
            {
                $haveCodecept = $this->test_e2e_is_codecept_available();
                $haveDocOrTests = file_exists($this->pluginRoot . '_doc')
                || file_exists($this->pluginRoot . 'doc')
                || file_exists($this->pluginRoot . 'tests');
                $this->debugVerbose("Will test_e2e_action_base_review");
                $docAndTestsSanityActionUrl = add_query_arg([
                    'action' => 'wa-testable-e2e',
                    'testable-e2e-action' => 'clean-doc-and-tests' 
                ], admin_url( 'admin-ajax.php' ));
                $this->e_review_data_check_insert([
                    'category' => __('02 - Maintenance', 'monwoo-web-agency-config'/** 📜*/),
                    'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>',
                    'title' => __("03 - [security] WordPress review", 'monwoo-web-agency-config'/** 📜*/),
                    'title_icon' => '<span class="dashicons dashicons-dashboard"></span>',
                    'requirements' => __( "Codeception doit être supprimé dès que la phase de test est validée.<br />
                    'codecept.phar' contient du code non lisible humainement. <br />
                    Les revues humaines et outils de sécurités peuvent échouer dans la validation de ce fichier."
                    . ($haveCodecept ? $this->test_e2e_get_available_actions() : "") . "<br />"
                    . (
                        $haveDocOrTests
                        ? "<a
                        href='$docAndTestsSanityActionUrl'
                        >" 
                        . __("Cliquer ici pour supprimer les dossiers '_doc', 'doc' et 'tests' de votre serveur.", 'monwoo-web-agency-config'/** 📜*/ )
                        . "</a>"
                        : ""
                    )
                    ,
                    'monwoo-web-agency-config'/** 📜*/ ),
                    'value' => '',
                    'result'   => ! $haveCodecept,
                    'is_activated'   => true,
                    'fixed_id' => "{$this->iId}-testable-e2e-security-check",
                    'is_computed' => true,
                ]);
        }
            /**
             * Launch the ajax admin end to end test action received by post contents.
             * 
             * GET parameters :
             *  - **testable-e2e-action** : The end to end action to run.
             * Only 'deploy-codecept' or 'clean-codecept' for now
             * 
             */
            public function test_e2e_action(): void
            {
                $anonimizedIp = $this->get_user_ip();
                $this->debug("Will test_e2e_action");
                if (!current_user_can('administrator')) {
                    $this->err("test_e2e_action invalid access for $anonimizedIp, need to be administrator to do backups");
                    echo wp_json_encode([
                        "error" => "Invalid access for $anonimizedIp registred",
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }
                $action = filter_var( sanitize_key($_REQUEST['testable-e2e-action'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                $isJson = wp_is_json_request();
                $reviewUrl = add_query_arg([
                    'page' => $this->eReviewPageKey,
                ], admin_url( 'admin.php' ));
                if ($isJson) {
                    header("Content-Type: application/json");
                }
                $authenticatedActions = [
                    'deploy-codecept' => function($app, $instAction) use ($isJson, $reviewUrl) {
                        set_time_limit(30*60); 
                        $pharPath = $this->test_e2e_codecept_phar_path();
                        $pharDir = dirname($pharPath);
                        if (!file_exists($pharDir)) {
                            mkdir($pharDir, 0777, true);
                        }
                        $tmp_file = download_url( $this->codeceptSource );
                        if (is_wp_error($tmp_file)) {
                            $srcStatus = $tmp_file->get_error_code();
                            $app->err("Fail to deploy-codecept from {$this->codeceptSource} : '$srcStatus'", $tmp_file);
                            if (!$isJson) {
                                Notice::displayError(__(
                                    "[$srcStatus] Echec de la mise à jour de 'tools/codecept.phar' via : ", 'monwoo-web-agency-config'/** 📜*/
                                ) . $this->codeceptSource);
                                wa_redirect($reviewUrl, $this);
                                $status = http_response_code();
                            } else {
                                http_response_code($srcStatus);    
                            }
                            return [
                                "code" => $status,
                                "action" => $instAction,
                                "data" => [
                                    "source_url" => $this->codeceptSource,
                                    "load_fail" => $srcStatus,
                                ]
                            ];
                        }
                        copy( $tmp_file, $pharPath );
                        @unlink( $tmp_file );
                        $app->info("Succed to deploy-codecept to '$pharPath'"
                        . " from '{$this->codeceptSource}'");
                        $tmp_file = wp_tempnam( basename( $this->docAndTestsDatasetSource ) );
                        $response = wp_safe_remote_get(
                            $this->docAndTestsDatasetSource,
                            array(
                                'timeout'  => 60 * 5, 
                                'redirection' => 7,
                                'stream'   => true,
                                'filename' => $tmp_file,
                                'headers'     => array(
                                    'Accept' => 'application/octet-stream',
                                ),
                                'cookies'     => array(),                        
                            )
                        );
                        $response_code = wp_remote_retrieve_response_code( $response );
                        if ( !is_wp_error($response) && 200 !== $response_code ) {
                            $data = array(
                                'code' => $response_code,
                            );
                            $response = new WP_Error( "http_$response_code", trim( wp_remote_retrieve_response_message( $response ) ), $data );
                        }
                        if (is_wp_error($response)) {
                            $srcStatus = $response->get_error_code();
                            $app->err(
                                "Fail to deploy-codecept doc and tests from {$this->docAndTestsDatasetSource} : '$srcStatus'", [
                                "from" => $tmp_file,
                                "error" => $response,
                            ]);
                            if (!$isJson) {
                                Notice::displayError(__(
                                    "[$srcStatus] Echec de la mise à jour de la doc et des tests : ", 'monwoo-web-agency-config'/** 📜*/
                                ) . $this->docAndTestsDatasetSource);
                                wa_redirect($reviewUrl, $this);
                                $status = http_response_code();
                            } else {
                                http_response_code($srcStatus);    
                            }
                            return [
                                "code" => $status,
                                "action" => $instAction,
                                "data" => [
                                    "source_url" => $this->docAndTestsDatasetSource,
                                    "load_fail" => $srcStatus,
                                ]
                            ];
                        }
                        $fs = wa_filesystem();
                        if ($fs->move($tmp_file, "$tmp_file.zip")) {
                            $tmp_file = "$tmp_file.zip";
                        }
                        $zip = new ZipArchive;
                        if (true !== ($err = $zip->open($tmp_file))) {
                                $err = print_r($err, true);
                            $zipStatus = $zip->getStatusString();
                            return new WP_Error(
                                'wrong_zip_file',
                                "Err [$err - $zipStatus] : Fail to open zip file : ", 
                                [ 'deploy_action' => $instAction, 'zip-err-code' => $zipStatus, 'file' => $tmp_file, 'status' => 404 ]
                            );
                        }
                        $zip->extractTo($this->pluginRoot);
                        $zip->close();
                        if (!constant('WA_Config_SHOULD_SECURE_DOCUMENTATION')) {
                            $publicDocPath = $this->pluginRoot . "doc";
                            $docPath = $this->pluginRoot . "_doc";
                            $fs->move($docPath, $publicDocPath);
                            @unlink( "$publicDocPath/.htaccess" ); 
                        }
                        @unlink( $tmp_file );
                        $app->info("Succed to deploy-codecept doc and tests to '{$this->pluginRoot}'"
                        . " from '{$this->docAndTestsDatasetSource}'");
                        $status = 200;
                        if (!$isJson) {
                            Notice::displaySuccess(__(
                                "Succès de la mise à jour de 'tools/codecept.phar' via : ", 'monwoo-web-agency-config'/** 📜*/
                            ) . $this->codeceptSource);
                            wa_redirect($reviewUrl, $this);
                            $status = http_response_code();    
                        } else {
                            http_response_code($status);    
                        }
                        return [
                            "code" => 'ok',
                            "action" => $instAction,
                            "data" => [
                                "phar_path" => $pharPath,
                                "source_url" => $this->codeceptSource,
                                "redirect_to" => $reviewUrl,
                                "status" => $status,    
                            ]
                        ];
                    },
                    'clean-codecept' => function($app, $instAction) use ($isJson, $reviewUrl) {
                        $pharPath = $this->test_e2e_codecept_phar_path();
                        unlink($pharPath);
                        $app->info("Succed to clean '$pharPath'");
                        $status = 200;
                        if (!$isJson) {
                            wa_redirect($reviewUrl, $this);
                            $status = http_response_code();    
                        } else {
                            http_response_code($status);    
                        }
                        return [
                            "code" => 'ok',
                            "action" => $instAction,
                            "data" => [
                                "phar_path_deleted" => $pharPath,
                                "redirect_to" => $reviewUrl,
                                "status" => $status,    
                            ]
                        ];
                    },
                    'clean-doc-and-tests' => function($app, $instAction) use ($isJson, $reviewUrl) {
                        $testPath = $this->pluginRoot . "tests";
                        $publicDocPath = $this->pluginRoot . "doc";
                        $docPath = $this->pluginRoot . "_doc";
                        $fs = wa_filesystem();
                        $fs->rmdir($testPath, true);
                        $fs->rmdir($publicDocPath, true);
                        $fs->rmdir($docPath, true);
                        $app->info("Succed to remove 'tests', 'doc' and '_doc' folders");
                        $status = 200;
                        if (!$isJson) {
                            wa_redirect($reviewUrl, $this);
                            $status = http_response_code();    
                        } else {
                            http_response_code($status);    
                        }
                        return [
                            "code" => 'ok',
                            "action" => $instAction,
                            "data" => [
                                "test_path_deleted" => $testPath,
                                "doc_path_deleted" => $docPath,
                                "redirect_to" => $reviewUrl,
                                "status" => $status,    
                            ]
                        ];
                    },
                ];
                $response = "";
                if (array_key_exists($action, $authenticatedActions)) {
                    $response = $authenticatedActions[$action]($this, $action);
                } else {
                    $status = 404;
                    if (!$isJson) {
                        Notice::displayError(""
                        . __("'testable-e2e-action' NON VALIDE :",
                        'monwoo-web-agency-config'/** 📜*/) . "<br />\n{$action}<br />");
                        wa_redirect($reviewUrl, $this);
                        $status = http_response_code();    
                    } else {
                        http_response_code($status);    
                    }
                    $response = [
                        'wa_unknow_action',
                        "Unknown testable-e2e-action '$action'", 
                        [ 'testable-e2e-action' => $action, 'status' => $status ]
                    ];    
                }
                echo wp_json_encode($response);
                $this->exit();
            }
            /**
             * Return the testable end to end available actions
             * 
             * @return The rendered available action string
             */
            public function test_e2e_get_available_actions(): string {
                if (!current_user_can('administrator')) { 
                    return ""; 
                }
                $haveCodecept = $this->test_e2e_is_codecept_available();
                $testableEnd2EndActionUrl = add_query_arg([
                    'action' => 'wa-testable-e2e',
                    'testable-e2e-action' => $haveCodecept ?  'clean-codecept' : 'deploy-codecept',
                ], admin_url( 'admin-ajax.php' ));
                return "<p>[$this->iId] <strong><a
                href='$testableEnd2EndActionUrl'
                >" . 
                ( $haveCodecept
                    ? __("Cliquer ici pour supprimer 'tools/codecept.phar' de votre serveur.", 'monwoo-web-agency-config'/** 📜*/ )
                    : (
                        __("Cliquer ici pour Installer Codeception ('tools/codecept.phar', doc, tests) sur votre serveur via : ", 'monwoo-web-agency-config'/** 📜*/)
                        . $this->codeceptSource . " + " . $this->docAndTestsDatasetSource
                    )
                )
                . "</a></strong></p>";
            }
            protected $codeceptSource = null;
            protected $docAndTestsDatasetSource = null;
            protected function test_e2e_is_codecept_available() {
                $pharPath = $this->test_e2e_codecept_phar_path();
                return file_exists($pharPath);
            }
            protected function test_e2e_codecept_phar_path() {
                $pharName = 'codecept.phar';
                return $this->pluginRoot . "tools/$pharName";
            }
        }
    }
    if (!trait_exists(TestableSamples::class)) { 
        /**
         * This trait will run the provided examples from the domcumentation comments
         * 
         * Activate only for testing purpose, use your own adaptation for production.
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait TestableSamples
        {
            protected function _000_test_sample__bootstrap() {
                $this->test_sample_wa_base_review_skill_terms_to_ensure();
            }
            /**
             * Run the example from WPFilters::wa_base_review_skill_terms_to_ensure documentation
             * 
             * @see WPFilters::wa_base_review_skill_terms_to_ensure
             */
            public function test_sample_wa_base_review_skill_terms_to_ensure() : void {
                $app = $this;
                add_filter(WPFilters::wa_base_review_skill_terms_to_ensure, function (
                    $termsToEnsure, $locale, $localeSlug
                ) use($app) {
                    $termsToEnsure = array_merge($termsToEnsure, [
                        'health-care' => [
                            Utils\_x( 'Plaisir' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                [
                                'description' => Utils\__("Expertise en bien-être", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            ],
                        ],
                        'physiological-health-care' => [
                            Utils\_x( 'Plaisir physiologique' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise en Plaisir de l'activité de l'organisme humain", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('physiological-health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'health-care',
                            ],
                        ],
                        'organisational-health-care' => [
                            Utils\_x( 'Plaisir organisationnel' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise organisationnel pour se sentir bien ou améliorer un Plaisir relationnel ou physiologique", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('organisational-health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'health-care',
                            ],
                        ],
                    ]);
                    $app->info("[$locale] TestableSamples test_sample_wa_base_review_skill_terms_to_ensure : ", $termsToEnsure);
                    return $termsToEnsure;
                }, 10, 3);
            }
        }
    }
    if (!trait_exists(Identifiable::class)) { 
        /**
         * This trait will provide the loaded plugin information 
         * and instance identifiers
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait Identifiable
        {
            /**
             * Current instance prefix
             * @var string
             */
            public $iPrefix = "wa-i";
            /**
             * Current instance id
             * @var string
             */
            public $iId = null;
            /**
             * Current instance index (follow class constructor calls order)
             * @var int
             */
            public $iIndex = null;
            /**
             * Current instance index for current pluginRelativeFile
             * 
             * Follow class constructor calls order indexed by pluginRelativeFile value
             * 
             * @var int
             */
            public $iRelativeIndex = null;            
            /**
             * Plugin name only
             * @var string
             */
            public $pluginName = "";
            /**
             * Plugin relative path from WordPress root folder
             * @var string
             */
            public $pluginRelativeFile = "";
            /**
             * Current plugin version
             * @var string
             */
            public $pluginVersion = "";
            /**
             * Current site base url for this plugin instance
             * @var string
             */
            protected $siteBaseHref = "";
            /**
             * Full plugin file path of this plugin instance
             * @var string
             */
            protected $pluginFile = "";
            /**
             * Full plugin root folder path of this plugin instance
             * @var string
             */
            public $pluginRoot = "";
            /**
             * Test if current script is called from Comand Line Interface
             * @return bool True if command is launched from cli
             */
            function is_cli()
            {
                if( defined('STDIN') ) {
                    return true;
                }
                return empty($_SERVER['REMOTE_ADDR'])
                && !isset($_SERVER['HTTP_USER_AGENT'])
                && count($_SERVER['argv']) > 0;
            }
            /**
             * @see WPFilters::wa_get_ip
             */
            protected function get_user_ip($anonymize = true, $traceLog = false) {
                $ip = "#IP-NOT-FOUND-ERROR#";
                if ( $this->is_cli() ) {
                    $ip = sanitize_text_field($_SERVER['SERVER_ADDR']) 
                    ?? sanitize_text_field($_SERVER['REMOTE_ADDR']) ?? 
                    sanitize_text_field($_SERVER['argv'][0]);
                }
                if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                    $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
                } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                    $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
                } else {
                }
                if ($anonymize) {
                    $ip = preg_replace(
                        ['/\.\d*$/', '/[\da-f]*:[\da-f]*$/'],
                        ['.XXX', 'XXXX:XXXX'],
                        $ip
                    );
                }
                /**
                 * @see WPFilters::wa_get_ip
                 */
                return apply_filters( WPFilters::wa_get_ip, $ip );
            }
            /**
             * Will return the key id and set the 'fixed_id' attribut if not already fixed
             */
            protected function fetch_review_key_id( & $checkpoint) {
                if ($checkpoint['fixed_id']) {
                    return $checkpoint['fixed_id'];
                }
                $catSlug = sanitize_title($checkpoint['category']); 
                $titleSlug = sanitize_title($checkpoint['title']);
                $keyId = "$catSlug-$titleSlug-{$checkpoint['created_by']}-{$checkpoint['create_time']}";
                $checkpoint['fixed_id'] = $keyId;
                return $keyId;
            }
            protected function get_backup_folder() {
                $bckupFolder = wp_upload_dir()['basedir'] . "/plugins/{$this->pluginName}" ;
                if (!file_exists($bckupFolder)) {
                    mkdir($bckupFolder, 0777, true);
                }
                return $bckupFolder;
            }
        }
    }
    if (!trait_exists(Parallelizable::class)) { 
        /**
         * This trait will provide sentinels
         * to handle parallelizable loads 
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait Parallelizable
        {
            /**
             * Sentinel will return false if aim is not reached, true otherwise.
             * 
             * This sentinel will also call {@see AppInterface::methodeCalledFrom()}
             * to ajust method call counts.
             * 
             * @param string $methodeName A methode name to test and update 
             * @return boolean
             * - return true for first call
             * - return false for all next ones
             */
            public function p_higherThanOneCallAchievedSentinel($methodeName)
            {
                $isFirstCall = $this->isFirstMethodCall($methodeName);
                $this->methodeCalledFrom($methodeName);
                $sentinelAdvice = !$isFirstCall;
                if ($sentinelAdvice) {
                    $this->debugVerbose(
                        "higherThanOneCallAchievedSentinel reached for '$methodeName' called by {$this->iId}"
                    );
                }
                return $sentinelAdvice;
            }
        }
    }
    if (!trait_exists(Debugable::class)) { 
        /**
         * This trait will provide log output method
         * ready to debug parallel loads of same plugin
         * 
         * Ready to use as ($msg, ...$ctx) :
         * - info, warn, err
         * - debug, debugVerbose, debugVeryVerbose
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Parallelizable
         * @uses Identifiable
         */
        trait Debugable
        {
            use Parallelizable;
            use Identifiable;
            protected $shouldDebug = false;
            protected $shouldDebugVerbose = false;
            protected $shouldDebugVeryVerbose = false;
            /**
             * WARNING : Keep _000_debug__bootstrap at 000 and nothing others, to be able to debug
             * all next calls with our wa-config debug tools
             */
            protected function _000_debug__bootstrap() {
                if (!$this->shouldDebug) {
                    return;
                }
                if ($this->p_higherThanOneCallAchievedSentinel('_000_debug__bootstrap')) {
                    return; 
                }
                error_reporting( 
                    E_CORE_ERROR |
                    E_CORE_WARNING |
                    E_COMPILE_ERROR |
                    E_ERROR |
                    E_WARNING |
                    E_PARSE |
                    E_USER_ERROR |
                    E_USER_WARNING |
                    E_RECOVERABLE_ERROR |
                    E_STRICT
                );
                if (!defined('WC_ABSPATH')) {
                    ini_set("log_errors", 1);
                    $logPath = rtrim(WA_Config_LOG_FOLDER, "/") . "/debug.log";
                    ini_set("error_log", $logPath);
                }
                if ($this->shouldDebug
                && $this->shouldDebugVerbose) {
                    $previousHandler = set_error_handler([$this, "debug_exception_error_handler"]);
                    ini_set('display_startup_errors', 1);
                }
                $default_opts = array(
                    'http'=>array(
                        'notification' => [$this, 'debug_stream_notification_callback'] 
                    ),
                    'https'=>array(
                        'notification' => [$this, 'debug_stream_notification_callback'] 
                    )
                );
                $default = stream_context_set_default($default_opts);
                add_filter('pre_http_request', [$this, 'debug_trace_wp_http_requests'], 10, 3);
            }
            /**
             * Dev in progress, not ready yet
             */
            public function debug_stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
                $this->debug("Detect {$message_code} HTTP stream Call to : $message");
                if (STREAM_NOTIFY_REDIRECTED === $notification_code) {
                    $this->debug("Detect {$message_code} HTTP stream Call to : $message");
                }
                return;
                switch($notification_code) {
                    case STREAM_NOTIFY_RESOLVE:
                    case STREAM_NOTIFY_AUTH_REQUIRED:
                    case STREAM_NOTIFY_COMPLETED:
                    case STREAM_NOTIFY_FAILURE:
                    case STREAM_NOTIFY_AUTH_RESULT:
                        var_dump($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max);
                        break;
                    case STREAM_NOTIFY_REDIRECTED:
                        echo wp_kses_post("Redirection vers : ", $message);
                        break;
                    case STREAM_NOTIFY_CONNECT:
                        echo wp_kses_post("Connecté...");
                        break;
                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        echo wp_kses_post("Récupération de la taille du fichier : ", $bytes_max);
                        break;
                    case STREAM_NOTIFY_MIME_TYPE_IS:
                        echo wp_kses_post("Type mime trouvé : ", $message);
                        break;
                    case STREAM_NOTIFY_PROGRESS:
                        echo wp_kses_post("En cours de téléchargement, déjà ", $bytes_transferred, " octets transférés");
                        break;
                }
                echo wp_kses_post("\n");
            }
            /**
             * Error Handler used with native PHP set_error_handler()
             */
            public function debug_exception_error_handler($severity, $message, $file, $line) {
                $self = $this;
                $firstFile = "";
                $stackTrace = $this->debug_trace(true);
                array_shift($stackTrace);
                $internalStackTrace = array_filter($stackTrace, function($t)
                use ($self, & $firstFile) {
                    $f = $t['file'] ?? "Unknown file";
                    $l = $t['line'] ?? "--";
                    $isFromPlugin = false !== strpos($f, $this->pluginRoot);
                    if (!strlen($firstFile) && $isFromPlugin) {
                        $firstFile = "$f:$l";
                    }
                    return $isFromPlugin;
                });
                $this->debugVerbose("[$severity] $message at $file:$line from $firstFile"); 
                if ((E_WARNING === $severity || E_NOTICE === $severity)
                ) {
                    $file = ($stackTrace[0]['file'] ?? "Unknow file")
                    . ":" . ($stackTrace[0]['line'] ?? "--");
                    $this->warn(
                        "$message",
                        "$file",
                        ["{$stackTrace[1]['function']}" => $stackTrace[1]['args']],
                        $this->debug_trace()
                    );
                }
            }
            /**
             * Add debug log about WordPress PHP internal HTTP requests
             * 
             * @param false|array|WP_Error  $preempt     A preemptive return value of an HTTP request. Default false.
             * @param array                 $parsed_args HTTP request arguments.
             * @param string                $url         The request URL.
             * @return false|array|WP_Error The new preemptive return value
             */
            public function debug_trace_wp_http_requests($preempt, $parsed_args, $url) {
                $this->debug("Detect {$parsed_args['method']} HTTP Call to : $url");
                $e = new Exception("debug_trace_wp_http_requests trace callstack");
                $this->debugVeryVerbose("debug_trace_wp_http_requests Call stack", "\n" . $e->getTraceAsString());
                $this->debugVeryVerbose("debug_trace_wp_http_requests details $url", [
                    "preempt" => $preempt,
                    "http_args" => $parsed_args,
                    "full_trace" => $e->getTrace(),
                ]);
                return $preempt;
            }
            /**
             * Return the current debug trace
             * 
             * @param bool  $full  array with full informations will be returned if true
             * @return array|string The debug trace in full or simple mode
             */
            public function debug_trace($full = false) {
                $e = new Exception("debug_trace callstack");
                return $full ? $e->getTrace() : "\n" . $e->getTraceAsString();
            }
            /**
             * Return the current routes
             * 
             * @param bool  $full  array with full informations will be returned if true
             * @return array|string The debug trace in full or simple mode
             * @global $wp_rewrite Read rewrite rules from it
             */
            public function debug_routes($full = false) {
                global $wp_rewrite; 
                return "[REST]" . implode(
                    "\n[REST] ",
                    array_keys(rest_get_server()->get_routes() ?? [])
                ) . "\n[WP]" . implode(
                    "\n[WP] ",
                    array_keys($wp_rewrite->rules ?? [])
                );
            }
            /**
             * Log an info message and it's optionnal context.
             * 
             * @param string $msg
             * @param mixed $ctx
             */
            public function info(string $msg, ...$ctx): void
            {
                $this->log('info', $msg, ...$ctx);
            }
            /**
             * Log an error message and it's optionnal context.
             * 
             * @param string $msg
             * @param mixed $ctx
             */
            public function err(string $msg, ...$ctx): void
            {
                $this->log('error', $msg, ...$ctx);
            }
            /**
             * Log a warning message and it's optionnal context.
             * 
             * @param string $msg
             * @param mixed $ctx
             */
            public function warn(string $msg, ...$ctx): void
            {
                $this->log('warning', $msg, ...$ctx);
            }
            /**
             * Log a debug message and it's optionnal context.
             * 
             * @param string $msg
             * @param mixed $ctx
             */
            public function debug(string $msg, ...$ctx): void
            {
                if ($this->shouldDebug) {
                    $this->log('debug', $msg, ...$ctx);
                }
            }
            /**
             * Log a verbose debug message and it's optionnal context.
             * 
             * @param string $msg
             * @param mixed $ctx
             */
            public function debugVerbose(string $msg, ...$ctx): void
            {
                if ($this->shouldDebugVerbose) {
                    $this->debug($msg, ...$ctx);
                }
            }
            /**
             * Log a very verbose debug message and it's optionnal context.
             * 
             * @param string $msg
             * @param mixed $ctx
             */
            public function debugVeryVerbose(string $msg, ...$ctx): void
            {
                if ($this->shouldDebugVeryVerbose) {
                    $this->debug($msg, ...$ctx);
                }
            }
            /**
             * Assert a test is valid and log correponding message if not.
             * 
             * If shouldDebug is true, it will throw an \Exception()
             * with the corresponding message.
             * 
             * @param boolean $test
             * @param string $msg
             * @param mixed $ctx
             * @return bool Test result if did not throw
             * @throws \Exception Throw exception if shouldDebug is enabled
             */
            public function assert(bool $test, string $msg, ...$ctx): bool
            {
                if (!$this->assertLog($test, $msg, ...$ctx)) {
                    if ($this->shouldDebug) {
                        throw new \Exception($msg);
                    }
                }
                return $test;
            }
            /**
             * Assert a test is valid and log correponding message if not.
             * 
             * Only doing warning log on failure
             * 
             * @param mixed $test
             * @param string $msg
             * @param mixed $ctx
             * @return bool $ctx
             */
            public function assertLog($test, string $msg, ...$ctx): bool {
                if (!$test) {
                    $this->warn("[Assert FAIL] $msg", ...$ctx);
                }
                return !!$test;
            }
            protected $pIdSuffix = [
                "-",
                "^",
                "*",
                "!",
                "?",
                "&",
                "@",
            ];
            static protected $_pIdToSuffix = []; 
            /**
             * Log message and context by tags.
             * 
             * 
             * @param string|array<int, string> $tags
             * Tags can be :
             * - one tag string
             * - string list of tags with commas separator
             * - and array of string tags
             * 
             * @param string $msg Message to log 
             */
            public function log($tags, string $msg, ...$ctx): void
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
                $msg = "#$pId{$pSuffix}[{$this->iId}]$tagsPrompt $msg";
                if (defined('WC_ABSPATH') && function_exists( 'wp_hash' )) {
                    if (!function_exists('wc_get_logger')) {
                        /**
                         * Require missing wc_get_logger function
                         */
                        include_once WC_ABSPATH . 'includes/wc-core-functions.php';
                    }
                    $logger = wc_get_logger(); 
                    $logger->log($tags[0], $msg, $ctx);
                }
                { 
                    $logPath = rtrim(WA_Config_LOG_FOLDER, "/") . "/debug.log";
                    $timePrompt = date_i18n( 'Y-m-d @O H:i:s' );
                    $msg = "[$timePrompt] $msg"; 
                    if (count($ctx)) {
                        error_log($msg . " " . print_r($ctx, true) . "\n", 3, $logPath);
                    } else {
                        error_log($msg . "\n", 3, $logPath);
                    }
                }
                if (class_exists(QueryMonitor::class)) {
                    $tagMapToQM = [
                        'info' => 'qm/info',
                        'error' => 'qm/error',
                        'warning' => 'qm/warning',
                        'debug' => 'qm/debug',
                    ];
                    foreach (count($tags) ? $tags : [ 'qm/debug' ] as $tag) {
                        $QMAction = $tagMapToQM[$tag] ?? 'qm/debug';
                        do_action($QMAction, $msg, $ctx);
                    }
                }
            }
        }
    }
    if (!trait_exists(Editable::class)) { 
        /**
         * This trait will provide common end user editable options for WA Config.
         * 
         * All WA Config options are registred under the prefixed $eConfOpt attributes
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait Editable
        {
            /**
             * Should we enable the footer
             */
            protected $eConfOptEnableFooter = 'wa_enable_footer';
            /**
             * Footer template to use
             */
            protected $eConfOptFooterTemplate = 'wa_footer_template';
            /**
             * Footer credit to use if no footer_template provided
             */
            protected $eConfOptFooterCredit = 'wa_footer_credit';
            /**
             * Static head target folder for frontend renderigns
             * 
             * {@see EditableConfigPanels::_010_e_config__bootstrap()}
             */
            protected $eConfStaticHeadTarget = 'wa_static_wa_head_target';
            /**
             * Regular Expression used to narrow the rendered static front head to specific regex routes
             * 
             */
            protected $eConfStaticHeadSafeWpKeeper = 'wa_static_wa_head_safe_keeper';
            /**
             * Regular Expression used to keep some wordpress url safe from the rendered static front head
             * 
             * {@see EditableConfigPanels::_010_e_config__bootstrap()}
             * 
             */
            protected $eConfStaticHeadNarrowFilter = 'wa_static_wa_head_narrow_filter';
            /**
             * Prefix to prepend to woo commerce order numbers rendering for billings etc...
             */
            protected $eConfWooCommerceOrderPrefix = 'wa_woo_com_order_prefix';
            /**
             * Use plugin basic frontend style for frontend renderings
             */
            protected $eConfShouldRenderFrontendScripts = 'wa_should_render_frontend_scripts';
            /**
             * Comma separated list of optimisation levels to use.
             * {@see OptiLvl}
             */
            protected $eConfOptOptiLevels = 'wa_optimisable_levels';
            /**
             * Regular Expression used to block WordPress HTTP Requests
             * {@see Optimisable::opti_filter_wp_http_requests()}
             * 
             */
            protected $eConfOptOptiWpRequestsFilter = 'wa_optimisable_wp_http_request_filter';
            /**
             * Regular Expression used to whitelist WordPress HTTP Requests
             * {@see Optimisable::opti_filter_wp_http_requests()}
             * 
             */
            protected $eConfOptOptiWpRequestsSafeFilter = 'wa_optimisable_wp_http_request_safe_filter';
            public $E_DEFAULT_OPTIMISABLE_SAFE_FILTER = '$(^https://)((web-agency.local.dev/)|(codeception.com/)|(api.wordpress.org/(plugins)|(themes/info))|(downloads.wordpress.org/(plugin)|(theme)|(translation))|(translate.wordpress.com)|(sitekit.withgoogle.com)|((www|plugin-cdn|api).monsterinsights.com)|(www.google.com/blank.html)|(woocommerce.com/wp-json/))$';
            /**
             * Should we send and admin notice on each blocked HTTP Requests
             */
            protected $eConfOptOptiEnableBlockedHttpNotice = 'wa_optimisable_enable_blocked_http_notice';
            /**
             * Will enable a review report on each blocked HTTP Requests or frontend proxy 404 of the last 30 minutes
             */
            protected $eConfOptOptiEnableBlockedReviewReport = 'wa_optimisable_enable_blocked_review_report';
            /**
             * List of acceptance test users
             */
            protected $eConfOptATestsUsers = 'wa_acceptance_tests_users';
            public $E_DEFAULT_A_TESTS_USERS_LIST = "demo@monwoo.com,editor-wa@monwoo.com,client-wa@monwoo.com,demo-wrong@monwoo.com'demo-wrong@monwoo.com'";
            /**
             * Base url to test during the end to end test process
             */
            protected $eConfOptATestsBaseUrl = 'wa_acceptance_tests_base_url';
            /**
             * Default config is 'administrator' level to launch test
             * but you can change it if your tests are safe to use by
             * others
             */
            protected $eConfOptATestsRunForCabability = 'wa_acceptance_tests_r_capability';
            /**
             * Review category of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewCategory = 'wa_review_category';
            /**
             * Review category icon of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewCategoryIcon = 'wa_review_category_icon';
            /**
             * Review title of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewTitle = 'wa_review_title';
            /**
             * Review title icon of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewTitleIcon = 'wa_review_title_icon';
            /**
             * Review requirements of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewRequirements = 'wa_review_requirements';
            /**
             * Review value of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewValue = 'wa_review_value';
            /**
             * Review result of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewResult = 'wa_review_result';
            /**
             * Review access capability or role of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewAccessCapOrRole = 'wa_review_access_cap_or_role';
            /**
             * Review is activated status of the new checkpoint (review report) to add to review
             */
            protected $eConfOptReviewIsActivated = 'wa_review_is_activated';            
            /**
             * list of deleted reviews
             */
            protected $eConfOptReviewsDeleted = 'wa_reviews_deleted';
            /**
             * list of current reviews by category by title
             */
            protected $eConfOptReviewsByCategorieByTitle = 'wa_reviews_by_category_by_title';
        }
    }
    if (!trait_exists(EditableWaConfigOptions::class)) { 
        /**
         * This trait will handle our wa-config options.
         * 
         * @see EditableWaConfigOptions::$eConfigOptsKey
         *      Key used for WordPress get_option
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait EditableWaConfigOptions
        {
            /**
             * eAdminConfigOptsKey is our main store key
             * used for WordPress get_option and admin panel configs
             * 
             * @since 0.0.1
             * @property-read $eConfigOptsKey the wa-config option Key for get_option
             * @author service@monwoo.com
             */
            public $eConfigOptsKey = 'wa_e_config_opts';
            /**
             * eReviewSettingsFormKey is our admin panel 
             * form input option key used for the review panel in wa-config
             * 
             * @since 0.0.1
             * @property-read $eReviewSettingsFormKey the wa-config option Key for get_option
             * @author service@monwoo.com
             */
            public $eReviewSettingsFormKey = 'wa_e_review_settings_form_key';
            /**
             * eAdminConfigE2ETestsOptsKey is our end to end tests
             * store key for internal options and security
             * 
             * @since 0.0.1
             * @property-read $eConfigE2ETestsOptsKey the wa-config option Key for get_option
             * @author service@monwoo.com
             */
            public $eConfigE2ETestsOptsKey = 'wa_e_config_e2e_tests_opts';
            protected $eConfigPageKey = 'wa-e-config-param-page'; 
            protected $eConfigParamPageKey = 'wa-e-config-param-page';
            protected $eConfigDocPageKey = 'wa-e-config-doc-page';
            protected $eConfigParamSettingsKey = 'wa-e-config-param-section';
            protected $eConfigOptsGroupKey = 'wa_e_config_opts_group'; 
            protected $eConfigOpts = [];
            /**
             * Will get the saved option for $key and default to $default if none.
             * 
             * @param string $key     
             * @param string $default A methode name to test and update 
             *
             * @return mixed the value of saved option of $default if none available.
             * @see EditableWaConfigOptions::$eConfigOptsKey
             *      Key used for WordPress get_option
             */
            public function getWaConfigOption($key, $default)
            {
                $this->debugVeryVerbose("Will getWaConfigOption $key");
                $this->eConfigOpts = get_option($this->eConfigOptsKey, array_merge([
                    $key => $default,
                ], $this->eConfigOpts));
                if (!is_array($this->eConfigOpts)) {
                    $this->warn("Having wrong datatype saved for $key", $this->eConfigOpts);
                    $this->eConfigOpts = [];
                }
                if (!key_exists($key, $this->eConfigOpts)) {
                    $this->eConfigOpts[$key] = $default;
                    $this->assert(
                        update_option($this->eConfigOptsKey, $this->eConfigOpts),
                        "Fail to update option {$this->eConfigOptsKey}"
                    );
                }
                $value = $this->eConfigOpts[$key];
                $this->debugVeryVerbose("Did getWaConfigOption $key", $value);
                return $value;
            }
            /**
             * @ignore Comming Soon... or not, not needed for now
             * only update and delete seem enough (pre-update filter way)
             */
            public function setWaConfigOption($key, $value)
            {
                throw new \Exception("TODO in dev");
            }
        }
    }
    if (!trait_exists(Translatable::class)) { 
        /**
         * This trait will load the i18n plugin text domain international translations.
         * i18n translations files are located in "./languages" plugin subfolder.
         * 
         * It Polylang plugin is available, it will also ensure some configurations for it
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         * @uses Identifiable
         */
        trait Translatable
        {
            use Editable, Identifiable;
            /**
             * Plugin text domain, used to load plugin translations from ./languages folder
             * 
             * {@see https://wordpress.org/support/topic/using-constant-as-a-text-domain}
             * would like to suggest you to avoid using constant as a text domain 
             * within your plugin. It makes strings
             * untranslatable by WPML or Loco translate.
             * 
             * @var string
             */
            public $waConfigTextDomain = 'monwoo-web-agency-config'/** 📜*/;
            protected function _001_t__bootstrap()
            {
                add_action( 'plugins_loaded', [$this, 't_loadTextdomains'] );
                if ($this->p_higherThanOneCallAchievedSentinel('_001_t__bootstrap')) {
                    return; 
                }
                add_action('permalink_structure_changed', [$this, 't_on_permalink_structure_changed']);
            }
            /**
             * Will ensure rewrite rules are up to date. (call wisely, heavy computs ?)
             * 
             * @param bool $resetMode Will flush rules imediately if true, or
             * will flush after 'wp' action if false. Default false.
             */
            public function t_ensure_route_sync($resetMode = false) : void {
                $lowestActionTrigger = 'wp'; 
                if ( !$resetMode && !did_action($lowestActionTrigger) ) {
                    if ( !has_action($lowestActionTrigger, [$this, 't_ensure_route_sync']) ) {
                        add_action($lowestActionTrigger, [$this, 't_ensure_route_sync']);
                    }
                    $this->debugVerbose("Will t_ensure_route_sync on next 'wp' action");
                    return; 
                }
                flush_rewrite_rules(false);
                $this->debugVerbose("Did flush_rules for t_ensure_route_sync");
                $this->debugVeryVerbose("Current routes : ", $this->debug_routes());
            }
            /**
             * Filter done after the permalink structure is updated.
             *
             * @param string $old_permalink_structure The previous permalink structure.
             * @param string $permalink_structure     The new permalink structure.
             */
            public function t_on_permalink_structure_changed() : void {
                $this->debugVerbose("Will t_on_permalink_structure_changed");
                $this->t_ensure_route_sync();
            }
            /**
             * Will load the text domain translations with 
             * WordPress load_plugin_textdomain function
             * 
             * @see Translatable::$waConfigTextDomain
             */
            public function t_loadTextdomains(): void
            {
                $this->debugVerbose("Will t_loadTextdomains from plugin {$this->pluginName}");
                $langFolder = $this->pluginName . '/languages';
                $this->assertLog(
                    load_plugin_textdomain(
                        $this->waConfigTextDomain,
                        false,
                        $langFolder
                    ),
                    "Fail to load textdomain '{$this->waConfigTextDomain}' for ["
                    . get_locale() . "] at path $langFolder"
                );
            }
        }
    }
    if (!class_exists(AppInterface::class)) { 
        /**
         * This abstract class will hold the basic plugin features
         * 
         * It helps with parallel programmings and plugin lifecycle
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         */
        abstract class AppInterface
        {
            use Debugable, Parallelizable, Translatable;
            const PLUGIN_VERSION = "0.0.3";
            protected static $_compatibilityReports = [];
            /**
             * Will add compatibility report about current package parallel loads
             * 
             * Reports are shown for admin user only, under the
             * "Parameters" sub menu of "WA Config"
             * {@see EditableConfigPanels::e_config_param_render_panel()}
             * 
             * @param string $level Level of the compatibility report, 
             *                      like : information / warning / CRITICAL / etc...    
             * @param string $msg Content of the compatibility report
             */
            public static function addCompatibilityReport($level, $msg): void
            {
                self::$_compatibilityReports[] = ['level' => $level, 'msg' => $msg];
                usort(self::$_compatibilityReports, function ($cr1, $cr2) {
                    return strnatcasecmp($cr1['level'], $cr2['level']);
                });
            }
            /**
             * Will get all available compatibility reports
             * 
             * Reports are shown for admin user only, under the
             * "Parameters" sub menu of "WA Config"
             * {@see EditableConfigPanels::e_config_param_render_panel()}
             * 
             * @return array<int, array<string, string>> {
             *    The compatibility report logs
             *
             *    @type int $key Log index, 0 being the first written log.
             * 
             *    @type array $value {
             *        Compatibility report
             *
             *        @type string $key Compatibility report level.
             * 
             *        @type string $value Compatibility report message.
             *    }
             * }
             */
            public static function getCompatibilityReports()
            {
                return self::$_compatibilityReports;
            }
            protected static $shouldExitAll = false;
            /**
             * Setup all registred instances to exit
             * 
             * Usefull for end to end testings avoiding direct script exit breaking test runs
             */
            public static function exitAll() {
                self::$shouldExitAll = true;
            }
            protected static $_instances = [];
            protected static $_iByRelativeFile = [];
            protected static $_iByIId = [];
            /**
             * Add an App instance to this package
             * 
             * @param AppInterface $inst App instance, added from {@see __construct()}
             */
            protected static function addInstance(AppInterface $inst)
            {
                if (self::$shouldExitAll) {
                    return; 
                }
                $inst->iIndex = count(self::$_instances);
                $inst->iId = $inst->iPrefix . "-"
                    . $inst->iIndex;
                self::$_instances[] = $inst;
                $inst->debug("🌖🌖 new instance from '$inst->pluginRelativeFile' \n"); 
                if (!key_exists($inst->pluginRelativeFile, self::$_iByRelativeFile)) {
                    self::$_iByRelativeFile[$inst->pluginRelativeFile] = [];
                }
                $inst->iRelativeIndex = count(self::$_iByRelativeFile[$inst->pluginRelativeFile]);
                self::$_iByRelativeFile[$inst->pluginRelativeFile][] = $inst;
                self::$_iByIId[$inst->iId] = $inst;
            }
            /**
             * Get App instance by index
             * 
             * Instances are registred from the parent constructor,
             * called after children intitialisation of internal attributes
             * 
             * @param int $index App index to fetch, 0 being the first instance created
             * @return AppInterface
             */
            public static function instance(int $index = 0): AppInterface
            {
                return self::$_instances[$index];
            }
            /**
             * Get All registred App instance by index
             * 
             * @return AppInterface[]
             */
            public static function allInstances()
            {
                return self::$_instances;
            }
            protected static $_uIdxCount = 0;
            /**
             * Get a unique index counter shared by all App instance
             * 
             * May not be continous since act like a shared counter,
             * but will hold a progressive order number per call
             * 
             * @return int A unique progressive index
             */
            public static function uIdx(): int
            {
                return self::$_uIdxCount++;
            }
            /**
             * Get last registred App instance
             * 
             * @return AppInterface
             */
            public static function lastInstance(): AppInterface
            {
                return end(self::$_instances);
            }
            /**
             * Get App instance by relative plugin path and plugin instance index
             * 
             * @param string $path Relative plugin path
             * @param int $index App index to fetch, 0 being the first instance created
             * @return AppInterface
             */
            public static function instanceByRelativeFile($path, $index = 0): ?AppInterface
            {
                if (!key_exists($path, self::$_iByRelativeFile)) {
                    return null;
                }
                if ($index < 0) {
                    $index += count(self::$_iByRelativeFile[$path]);
                }
                return self::$_iByRelativeFile[$path][$index];
            }
            /**
             * Get App instance by registred App Identifiable Identifier
             * 
             * @see Identifiable::$iId
             * @param string $iId App instance Identifiable Identifier
             * @return AppInterface
             */
            public static function instanceByIId($iId): ?AppInterface
            {
                if (!$iId || !key_exists($iId, self::$_iByIId)) {
                    return null;
                }
                return self::$_iByIId[$iId];
            }
            protected static $_methodes = [];
            protected static $_statsCountKey = '__count__';
            /**
             * Get statistics about methods calls by iId
             * 
             * With total of {@see AppInterface::methodeCalledFrom()} under ```__count__``` key
             * 
             * @param string $methodeName Name of the method to fetch statistics from
             * @return array<string, int> {
             *    The method call counts statistics by iId
             * 
             *    @type string $key Identifiable Identifier or ```__count__``` key
             * 
             *    @type int $value Numbers of $methodeName call done by the target $key App instance
             * }
             */
            public static function getMethodStatistics(string $methodeName)
            {
                return key_exists($methodeName, self::$_methodes)
                    ? self::$_methodes[$methodeName]
                    : null;
            }
            protected function _000_e2e_test__bootstrap() {
                if (is_admin()) {
                    add_filter("pre_update_option_{$this->eConfigE2ETestsOptsKey}",
                    [$this, "e2e_test_pre_update_filter"], 10, 3);    
                }
            }
            /**
             * Filters the end to end test data options from our tests launchs
             *
             * Filter the eConfigE2ETests
             *
             * @param mixed  $value     The new, unserialized option value.
             * @param mixed  $old_value The old option value.
             * @param string $option    Option name.
             * @return mixed  The new, unserialized option value.
             * @since 0.0.2
             */
            public function e2e_test_pre_update_filter($value, $old_value, $option) {
                if (!$value) {
                    $this->debug("e2e_test_pre_update_filter on null, avoiding pre_update ...");
                    return $old_value;
                }
                $value = _wp_json_sanity_check($value, 42);
                return $value;
            }
            const ERR_AUTH_TEST_USER_FAIL_USERNAME = 1;
            const ERR_AUTH_TEST_USER_FAIL_USERNAME_UPDATE = 2;
            /**
             * Authenticate and get test user
             * 
             * WARNING : to ensure tests rollback and
             *           tests traking actions vs real user action
             *           we MUST call logoutTestUser on end
             * 
             * @param string $userLoginName Real user login name to use as test source
             * @param string $accessHash Access hash to ensure capability of requested action
             * @param string $emailTarget Real of fake login name to use as test duplicated name
             * @param string $shouldClone If true, will clone user instead of only tagging as test mode (Dev in progress, not available yet)
             * @return false|\WP_User The resulting test user or false if fail
             */
            public function e2e_test_authenticateTestUser(
                $userLoginName,
                $accessHash,
                $emailTarget = null,
                $shouldClone = false
            )
            {
                $anonimizedIp = $this->get_user_ip();
                if (!($aInfo = $this->e2e_tests_validate_access_hash($accessHash))) {
                    $this->err("Invalid authenticateTestUser access for '$accessHash' by $anonimizedIp");
                    echo wp_json_encode([
                        "error" => "[$anonimizedIp][$accessHash] "
                        . __("IP enregistrée suite à accès invalid", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                    http_response_code(401);
                    return false || $this->exit();
                }
                $dateStamp = time();
                $emailTarget = trim($emailTarget);
                if (!strlen($emailTarget ?? "")) $emailTarget = null; 
                $emailTarget = $emailTarget ?? "test-$dateStamp-$userLoginName";
                $this->debugVerbose("Will e2e_test_authenticateTestUser from '$userLoginName' to '$emailTarget'");
                $user = get_user_by('login', $userLoginName );
                if ( !$user || is_wp_error( $user ) ){
                    $this->err(
                        "[$anonimizedIp][$userLoginName] "
                        . __("N'est pas un utilisateur enregistré", 'monwoo-web-agency-config'/** 📜*/)
                    );    
                    echo wp_json_encode([
                        "error" => "[$anonimizedIp][$userLoginName] "
                        . __("N'est pas un utilisateur enregistré", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                    http_response_code(404);
                    return false || $this->exit();
                }
                if ($shouldClone) {
                    throw new \Error("Clone not available, dev in progress");
                }
                $realUserName = $user->user_login;
                $realUserEmail = $user->user_email;
                $testMeta = get_user_meta( $user->ID, 'wa-e2e-test' );
                $previousTestRealUserName = $testMeta[0]['real-username'] ?? false;
                $previousTestRealUserEmail = $testMeta[0]['real-user-email'] ?? false;
                if (count($testMeta)) {
                    $this->info("[$anonimizedIp] Test user already logged in...", $testMeta);
                    $emailTarget = $user->user_login;
                } else {
                    wp_cache_delete("alloptions", "options"); 
                    $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                    $testUsers = $E2ETestsOptions['test-users'] ?? [];
                    $user->user_login = $emailTarget;
                    $user->user_email = $emailTarget;
                    clean_user_cache($user); 
                    /** @var wpdb $wpdb*/
                    global $wpdb;
                    if (false === $wpdb->update(
                        $wpdb->users, 
                        [
                            'user_login' => $user->user_login,
                            'user_email' => $user->user_email,
                        ], 
                        ['ID' => $user->ID],      
                    )) {
                        $this->err(
                            "[$userLoginName][=> {$user->user_login}] "
                            . __("Echec de la mise à jour du nom utilisateur", 'monwoo-web-agency-config'/** 📜*/)
                        );
                        return false;
                    }
                    if (!update_user_meta( $user->ID, 'wa-e2e-test', [
                        "real-username" => $realUserName,
                        "real-user-email" => $realUserEmail,
                    ])) {
                        $this->err(
                            "[$userLoginName][=> {$user->user_login}] "
                            . __("Echec de la mise à jour des méta de test de l'utilisateur", 'monwoo-web-agency-config'/** 📜*/)
                        );
                        $user->user_login = $realUserName;
                        $user->user_email = $realUserEmail;
                        if (false === $wpdb->update(
                            $wpdb->users, 
                            [
                                'user_login' => $user->user_login,
                                'user_email' => $user->user_email,
                            ], 
                            ['ID' => $user->ID],      
                        )) {
                            $this->err(
                                "[$userLoginName][=> {$user->user_login}] "
                                . __("Echec du rollback de l'utilisateur", 'monwoo-web-agency-config'/** 📜*/)
                            );    
                        };
                        return false;
                    };
                    $testUsers[$emailTarget] = $user;
                    $E2ETestsOptions['test-users'] = $testUsers;
                    update_option($this->eConfigE2ETestsOptsKey, $E2ETestsOptions);
                }
                wp_clear_auth_cookie();
                global $current_user;
                $current_user = null;
                wp_set_current_user ( $user->user_login );
                wp_set_auth_cookie  ( $user->ID );
                $this->info("[$anonimizedIp] Succed to login test user from '$userLoginName' to '$user->user_login' with hash [$accessHash]");
                return $user;
            }
            /**
             * Logout a test user and bring back account to real user.
             * 
             * @param string $userLoginName The test user login name to logout
             * @return string sanitized wp_json_encode response
             */
            public function e2e_test_logoutTestUser(
                $userLoginName,
                $accessHash
            ): string
            {
                $this->debugVerbose("Will e2e_test_logoutTestUser");
                $anonimizedIp = $this->get_user_ip();
                if (!($aInfo = $this->e2e_tests_validate_access_hash($accessHash))) {
                    $this->err("Invalid e2e_test_logoutTestUser access for '$accessHash' by $anonimizedIp");
                    http_response_code(401);
                    return wp_json_encode([
                        "error" => "[$anonimizedIp][$accessHash] "
                        . __("IP enregistrée suite à accès invalid", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                }
                $user = get_user_by('login', $userLoginName );
                if (!$user) {
                    $this->err("Utilisateur '$userLoginName' non existant from '$accessHash' by $anonimizedIp");
                    http_response_code(404);
                    return wp_json_encode([
                        "error" => "[$anonimizedIp][$accessHash] $userLoginName "
                        . __("Utilisateur non existant ou déjà déconnecté", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                }
                clean_user_cache($user); 
                $testMeta = get_user_meta( $user->ID, 'wa-e2e-test' );
                $this->debugVeryVerbose("[$user->ID] Meta 'wa-e2e-test' : ", $testMeta);
                $realUserName = $testMeta[0]['real-username'] ?? $user->user_login;
                $realUserEmail = $testMeta[0]['real-user-email'] ?? $user->user_email;
                /** @var wpdb $wpdb*/
                global $wpdb;
                $user->user_login = $realUserName;
                $user->user_email = $realUserEmail;
                if (false === $wpdb->update(
                    $wpdb->users, 
                    [
                        'user_login' => $user->user_login,
                        'user_email' => $user->user_email,
                    ], 
                    ['ID' => $user->ID], 
                )) {
                    $this->err("Fail to restore test user from '$userLoginName' to '{$user->user_login}'");
                    http_response_code(404);
                    return wp_json_encode([
                        "error" => "[$anonimizedIp][$accessHash] $userLoginName "
                        . __("Erreur de restauration d'utilisateur", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                } else {
                    if (!delete_user_meta( $user->ID, 'wa-e2e-test' )) {
                        $this->err("Fail to clean user 'wa-e2e-test' meta from '$userLoginName' to '{$user->user_login}'");
                    }
                }
                clean_user_cache($user); 
                wp_clear_auth_cookie();
                $this->info("Succed to logout test user from '$userLoginName' to '{$user->user_login}'");
                http_response_code(200);
                return wp_json_encode([
                    "status" => "OK",
                    "end_date" => date("Y/m/d H:i:s O "),
                ]);
            }
            /**
             * Launch the end to end test action received by post contents.
             * 
             * Curl test
             * ```bash
             * curl -v -d "wa-action=wa-e2e-test-action" "https://web-agency.local.dev/e-commerce/wp-admin/admin-ajax.php?action=wa-e2e-test-action"
             * ```
             * 
             * To launch it for real, connect to wp-admin as administrator,
             * go in Wa-config Review sub panel and click on the test launch link.
             * 
             * **Be careful** with **test launch**, always ensure your backup strategies
             * since failling tests **might mess** up your website data or file or ... 
             * 
             * Indeed, all possible side effects depends of the tests you choose to write.
             * 
             * cf e2e tests for some usage examples on 'wa-e2e-test-action'
             * 
             * @see \WA\Config\E2E\E2E_EnsureAdminConfigPanelCest E2E_EnsureAdminConfigPanelCest
             */
            public function e2e_test_action(): void
            {
                $anonimizedIp = $this->get_user_ip();
                $action = '';
                if ( isset( $_REQUEST['wa-action'] ) ) {
                    $action = filter_var( sanitize_key($_REQUEST['wa-action']), FILTER_SANITIZE_SPECIAL_CHARS );
                } else {
                    $this->err("Missing action parameter for e2e_test_action by $anonimizedIp");
                    echo wp_json_encode([
                        "error" => "[$anonimizedIp] "
                        . __("Paramétre 'wa-action' manquant.", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                    http_response_code(404);
                    $this->exit(); return;
                }
                $this->debug("Will e2e_test_action '$action' by '$anonimizedIp'");
                if ('force-clean-and-restore-users' === $action) {
                    $this->e2e_test_clean_and_restore_test_users();
                    http_response_code(200);
                    $this->exit(); return;
                }
                if ('download-last-backup' === $action) {
                    $bckUpType = filter_var( sanitize_text_field($_REQUEST['wa-backup-type'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                    $this->e2e_test_download_last_backup($bckUpType);
                    $this->exit(); return;
                }
                if ('do-backup' === $action) {
                    $bckUpType = filter_var( sanitize_text_field($_REQUEST['wa-backup-type'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                    $compressionType = filter_var(
                        sanitize_text_field($_REQUEST['wa-compression-type']?? null), FILTER_SANITIZE_SPECIAL_CHARS
                    );
                    $this->e2e_test_do_backup($bckUpType, $compressionType);
                    $this->exit(); return;
                }
                $aHash = filter_var( sanitize_text_field($_POST['wa-access-hash'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS));
                if (!($aInfo = $this->e2e_tests_validate_access_hash($aHash))) {
                    $this->err("Invalid access for '$aHash' by $anonimizedIp");
                    echo wp_json_encode([
                        "error" => "[$anonimizedIp][$aHash] "
                        . __("IP enregistrée suite à accès invalid", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                switch ($action) {
                    case 'authenticate-user': {
                        $emailTarget = null;
                        $waData = filter_var( _wp_json_sanity_check($_POST['wa-data'] ?? null, 1), FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                        $this->debugVeryVerbose("wa-data", $waData);
                        $email = $waData[0];
                        $emailTarget = $waData[1] ?? null;
                        $test_user = $this->e2e_test_authenticateTestUser(
                            $email, $aHash, $emailTarget
                        );
                        if ($test_user) {
                            echo wp_json_encode([
                                "status" => "OK",
                                "test_user" => $test_user, 
                                "end_date" => date("Y/m/d H:i:s O "),
                            ]);
                            http_response_code(200);
                        }
                        $this->exit(); return;                            
                    } break;
                    case 'logout-user': {
                        $email = filter_var( sanitize_email($_POST['wa-data'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                        echo filter_var($this->e2e_test_logoutTestUser(
                            $email, $aHash
                        ), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        $this->exit(); return;
                    } break;
                    default: {
                        $this->warn("Unknow action '$action'");
                    } break;
                }
                echo wp_json_encode([
                    "status" => "OK",
                    "end_date" => date("Y/m/d H:i:s O "),
                ]);
                http_response_code(200);
                $this->exit(); return;
            }
            /**
             * Open an e2e test access and return the corresponding access hash
             * 
             * @param bool $doNotSendEmail Optional, if true, will
             * avoid real email expedition for all mailings, default true.
             * @return string Access hash used for test mode autentification
             */
            public function e2e_tests_access_hash_open($doNotSendEmail = true) {
                global $argv;
                $serverIP = $this->get_user_ip(false);
                $hSize = 6; 
                $h = bin2hex(random_bytes($hSize/2));
                $accessHash = base64_encode("e2e-tests-$serverIP-" . time() . "-$h");
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                $E2ETestsOptions['access-open'] = time();
                $E2ETestsOptions["tests-in-progress"] = array_merge(
                    $E2ETestsOptions["tests-in-progress"] ?? [], [
                        $accessHash => [
                            'access-hash' => $accessHash,
                            'started_at' => time(),
                            'started_by' => $serverIP,
                        ]
                    ]
                );
                $E2ETestsOptions['emails-sended'] = [];
                $E2ETestsOptions['do-not-send-email'] = $doNotSendEmail;
                update_option($this->eConfigE2ETestsOptsKey, $E2ETestsOptions);
                $this->debugVerbose("Openning e2e test hash '$accessHash' by $serverIP");
                return $E2ETestsOptions["tests-in-progress"][$accessHash];
            }
            /**
             * Soft kill wordpress, to allow end to end tests to keep going without COMMAND DID NOT FINISH PROPERLY error.
             *
             * @since 0.0.1
             * @access private
             *
    		 * @param callable $function Callback function name.
             */
            public function e2e_tests_filter_wp_die_callback( $function ) {
                return '_wa_e2e_tests_wp_die_handler';
            }
            public function exit() {
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                if ($E2ETestsOptions['access-open'] ?? false) {
                    $this->debugVerbose("Custom soft exit for test mode", $this->debug_trace());
                    AppInterface::exitAll();
                    $this->debugVerbose("Exit requested, removing all actions");
                    foreach ($GLOBALS['wp_filter'] as $name => $filter) {
                        try {
                            remove_all_actions($name);
                            remove_all_filters($name);
                        } catch (\Exception $e) {
                            $this->debugVerbose("Fail to remove action :", $e);
                        }
                    }
                    self::$e2e_tests_wp_die_callback = null;
                    $this->e2e_test_register_wp_die_callback();
                    if (class_exists(QM_Dispatchers::class)) {
                        $d = QM_Dispatchers::init();
                        $reflection = new \ReflectionClass($d);
                        $property = $reflection->getProperty('items');
                        $property->setAccessible(true);
                        $property->setValue($d, []); 
                    }
                    ini_set("display_errors", false); 
                    $previousHandler = set_error_handler([$this, "debug_exception_error_handler"]);
                    throw new \Exception("E2e tests : Soft exit requested", http_response_code());
                } else {
                    exit();
                }
            }
            /**
             * Email middleware to prevent email expedition if requested by configuration
             * 
             * We avoid email expedition if requested by the 'wa_e_config_e2e_tests_opts' option.
             * 
             * We break the 'to' field to avoid expedition and
             * allow regular logger to notify some emails activity
             * 
             * @param array $email Incoming email that will be send
             */
            public function e2e_tests_emails_middleware( $email ) {
                $this->debugVerbose("Sending email :" . $email["subject"]);
                $this->debugVeryVerbose("Sending email :", $email);
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                $E2ETestsOptions['emails-sended'] = $E2ETestsOptions['emails-sended'] ?? [];
                $E2ETestsOptions['emails-sended'][] = $email;
                update_option($this->eConfigE2ETestsOptsKey, $E2ETestsOptions);
                if ($E2ETestsOptions['do-not-send-email'] ?? false) {
                    $email['to'] = "#e2e#{$email['to']}#e2e#";
                    $this->debug("Avoid mail send from test adjusted OK for {$email['to']}");
                }
                return $email;
            }
            /**
             * Close an e2e test access and restore the regular website access
             * 
             * @param string $accessHash Access hash used for test mode autentification
             */
            public function e2e_tests_access_hash_close($accessHash) : void {
                $anonimizedIp = $this->get_user_ip();
                if (!($aInfo = $this->e2e_tests_validate_access_hash($accessHash))) {
                    $this->err("Invalid hash close access for '$accessHash' by $anonimizedIp");
                    echo wp_json_encode([
                        "error" => "[$anonimizedIp][$accessHash] "
                        . __("IP enregistrée suite à accès invalid", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                $E2ETestsOptions["tests-in-progress"][$accessHash] = array_merge(
                    $E2ETestsOptions["tests-in-progress"][$accessHash], [
                        'ended_at' => time(),
                    ]
                );
                $E2ETestsOptions["tests-in-progress"] = array_filter(
                    $E2ETestsOptions["tests-in-progress"],
                    function($testMeta) {
                        return time() - $testMeta['started_at'] < 60 * 60;
                    }
                );
                $E2ETestsOptions['access-open'] = false;
                $E2ETestsOptions['emails-sended'] = [];
                update_option($this->eConfigE2ETestsOptsKey, $E2ETestsOptions);
                $serverIP = $this->get_user_ip(false);
                $this->debugVerbose("Closing e2e test hash '$accessHash' by $serverIP");
            }
            /**
             * Validate an e2e test access
             * 
             * @param string $accessHash Access hash used for test mode autentification
             * @return false|array Access informations if $accessHash authorised,
             * false otherwise
             */
            public function e2e_tests_validate_access_hash($accessHash) {
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                $this->debugVerbose(
                    "e2e_tests_validate_access_hash '$accessHash'"
                    . (($E2ETestsOptions["tests-in-progress"][$accessHash] ?? [])['started_by'] ?? 'TEST HASH NOT FOUND')
                );
                if (!$accessHash || !strlen($accessHash)
                || !array_key_exists(
                    $accessHash,
                    $E2ETestsOptions["tests-in-progress"] ?? []
                )) { return false; }
                $accessInfos = $E2ETestsOptions["tests-in-progress"][$accessHash];
                $requestIP = $this->get_user_ip(false);
                if ((time() - $accessInfos['started_at']) < (60 * 60)
                && !array_key_exists('ended_at', $accessInfos)
                && $requestIP === $accessInfos['started_by']) {
                    return $accessInfos;
                };
                return false;
            }
            /**
             * Check pending tests users and restore them
             * 
             * Usefull if you have buggy tests launch that 
             * fails to restore users by themselves. You might need to launch it 2 times...
             * 
             * Curl test
             * ```bash
             * curl -H 'wa-e2e-test-mode: wa-config-e2e-tests' \
             * "https://web-agency.local.dev/e-commerce/wp-admin/admin-ajax.php?action=wa-e2e-test-action&wa-action=force-clean-and-restore-users"
             * ```
             */
            public function e2e_test_clean_and_restore_test_users() {
                $anonimizedIp = $this->get_user_ip();
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                $testUsers = $E2ETestsOptions['test-users'] ?? [];
                $testUsersCount = count($testUsers);
                $this->debug(
                    "[$anonimizedIp] Will e2e_test_clean_and_restore_test_users for $testUsersCount users."
                );
                $aInfo = $this->e2e_tests_access_hash_open();
                $aHash = $aInfo['access-hash'];
                if ($testUsersCount) {
                    foreach ($testUsers as $test_name => $user) {
                        $this->debug("[$anonimizedIp] Will e2e_test logout", $test_name);
                        $this->debugVeryVerbose(" for user :", $user);
                        $this->e2e_test_logoutTestUser( $test_name, $aHash );
                    }
                    unset($E2ETestsOptions['test-users']);
                    update_option($this->eConfigE2ETestsOptsKey, $E2ETestsOptions);
                }
                $this->e2e_tests_access_hash_close($aHash);
                echo wp_json_encode([
                    "did_update" => "E2ETestsOptions 'test-users' with clean_and_restore",
                    "caller" => "[$anonimizedIp][{$this->iId}]",
                    "update_count" => $testUsersCount,
                    "end_date" => date("Y/m/d H:i:s O "),
                ]);
            }
            /**
             * Download the targeted backup type
             * 
             */
            public function e2e_test_download_last_backup(string $bckUpType, $compressionType = null) {
                $anonimizedIp = $this->get_user_ip();
                if (!current_user_can($this->optAdminEditCabability)
                || !current_user_can('administrator')) { 
                    $this->err("e2e_test_download_last_backup invalid access for $anonimizedIp, need to be {$this->optAdminEditCabability} or administrator to do backups");
                    echo wp_json_encode([
                        "error" => "Invalid access for $anonimizedIp registred",
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }                   
                if ('sql' === $bckUpType) {
                    ob_start();
                    $siteSlug = sanitize_title(get_bloginfo( 'name' ));
                    $fileExtension = $compressionType ?? '.sql';
                    $filename = "$siteSlug-full-database-backup$fileExtension";
                    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
                    header( 'Content-Description: File Transfer' );
                    header( 'Content-Type: text/plain; charset=utf-8' );
                    header( "Content-Disposition: attachment; filename={$filename}" );
                    header( 'Expires: 0' );
                    header( 'Pragma: public' );
                    header("Content-Transfer-Encoding: binary");            
                    $bckupFolder = $this->get_backup_folder();
                    $lastBckupPath = "$bckupFolder/$filename";
                    $this->debug("Download src : $lastBckupPath");
                    $downloadReport = ob_get_clean();
                    flush();
                    $fOut = fopen( 'php://output', 'w' );
                    fwrite( $fOut, file_get_contents($lastBckupPath));
                    fclose( $fOut );
                    flush();
                    $this->debug("Succed to download $filename. $downloadReport");
                    http_response_code(200);
                    $this->exit(); return;
                }
                if ('simple-zip' === $bckUpType
                || 'full-zip' === $bckUpType) {
                    $siteSlug = sanitize_title(get_bloginfo( 'name' ));
                    $fileExtension = $compressionType ?? '.zip';  
                    $filename = "$siteSlug-simple-backup$fileExtension";
                    if ('full-zip' === $bckUpType) {
                        $filename = "$siteSlug-full-backup$fileExtension";
                    }
                    $bckupFolder = $this->get_backup_folder();
                    $lastBckupPath = "$bckupFolder/$filename";
                    $fileSize = filesize($lastBckupPath);
                    $this->debug("Download src : $lastBckupPath");
                    header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0"); 
                    header( 'Content-Description: File Transfer' );
                    header('Content-Type: application/zip');
                    header( "Content-Disposition: attachment; filename={$filename}" );
                    header( 'Expires: 0' );
                    header( 'Pragma: public' );
                    header('Content-Length: ' . $fileSize);
                    header("Content-Transfer-Encoding: binary");
                    header("Accept-Ranges: bytes"); 
                    set_time_limit(25*60); 
                    $downloadReport = ""; 
                    if (ob_get_level()) ob_end_clean();
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
                            while(!feof($file))
                            {
                                print fread($file, round($download_rate));
                                flush();
                            }
                            fclose($file);
                        } else {
                            $offset = 0;
                            $length = $fileSize; 
                            if(isset($_SERVER['HTTP_RANGE'])) 
                            { 
                                preg_match('/bytes=(\d+)-(\d+)?/', sanitize_text_field($_SERVER['HTTP_RANGE']), $matches); 
                                $offset = intval($matches[1]); 
                                $length = intval($matches[2]) - $offset;
                                $this->debug("Will offset range of lenght $length starting at $offset for $filename");
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
                            if (preg_match('/^(\d+)(.)$/', $maxAllowedMemory, $matches)) {
                                if (strtoupper($matches[2]) == 'G') {
                                    $maxAllowedMemory = $matches[1] * 1024 * 1024 * 1024; 
                                } else if (strtoupper($matches[2]) == 'M') {
                                    $maxAllowedMemory = $matches[1] * 1024 * 1024; 
                                } else if (strtoupper($matches[2]) == 'K') {
                                    $maxAllowedMemory = $matches[1] * 1024; 
                                } else {
                                    $maxAllowedMemory = $matches[1]; 
                                }
                            }
                            $memoryLimit = $maxAllowedMemory - memory_get_usage(true);
                            $this->debug("Memory limit before downloading $filename : "
                            . round($memoryLimit / 1024 / 1024) . " Mb left on "
                            . round($maxAllowedMemory  / 1024 / 1024)." Mb");
                            while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)
                            && $memoryLimit > ($chunksize * 2))
                            {
                              $buffer = fread($handle, $chunksize); 
                              print $buffer; 
                              flush();
                              $memoryLimit = $maxAllowedMemory - memory_get_usage(true);
                              $this->debug("Memory limit while downloading $filename : "
                              . round($memoryLimit / 1024 / 1024) . " Mb");
                            }
                            if(connection_status() !== CONNECTION_NORMAL) 
                            { 
                                $this->debug("Having aborted connection for $filename : " . connection_status());
                            } 
                            fclose($handle);
                        }
                    }
                    $this->debug("Succed to download $filename. $downloadReport");
                    http_response_code(200);
                    $this->exit(); return;
                }
                $this->err("[$anonimizedIp] Invalid backup type $bckUpType");
                echo wp_json_encode([
                    "error" => "[$anonimizedIp] "
                    . __("Type de backup invalid", 'monwoo-web-agency-config'/** 📜*/),
                ]);
                http_response_code(404);
                $this->exit(); return;            
            }
            /**
             * Download the targeted backup type
             * 
             */
            public function e2e_test_do_backup(
                string $bckUpType,
                $compressionType = null,
                $shouldDownload = true,
                $shouldServeResponse = true
            ) : void {
                $anonimizedIp = $this->get_user_ip();
                $wpRootPath = realpath(ABSPATH);
                if (!current_user_can($this->optAdminEditCabability)
                || !current_user_can('administrator')) { 
                    $this->err("e2e_test_do_backup invalid access for $anonimizedIp, need to be {$this->optAdminEditCabability} or administrator to do backups");
                    echo wp_json_encode([
                        "error" => "Invalid access for $anonimizedIp registred",
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }
                if ('sql' === $bckUpType) {
                    $siteSlug = sanitize_title(get_bloginfo( 'name' ));
                    $fileExtension = $compressionType ?? '.sql';
                    $filename = "$siteSlug-full-database-backup$fileExtension";
                    $bckupFolder = $this->get_backup_folder();
                    $lastBckupInfoPath = "$bckupFolder/plugins-and-themes.txt";
                    wp_delete_file($lastBckupInfoPath);
                    $lbip = fopen($lastBckupInfoPath, "w");
                    $wpPluginsPath = dirname(dirname(get_stylesheet_directory())) . "/plugins";
                    $files = glob("$wpPluginsPath/*");
                    foreach ($files as $f) {
                        fwrite($lbip, str_replace($wpRootPath, "", $f) . "\n");
                    }
                    $wpThemesPath = dirname(get_stylesheet_directory());
                    $files = glob("$wpThemesPath/*");
                    foreach ($files as $f) {
                        fwrite($lbip, str_replace($wpRootPath, "", $f) . "\n");
                    }
                    fclose($lbip);
                    $this->e2e_test_add_in_backup_history($lastBckupInfoPath);
                    $lastBckupPath = "$bckupFolder/$filename";
                    wp_delete_file($lastBckupPath);
                    $this->e2e_test_load_SQL_in_file($lastBckupPath);
                    $this->e2e_test_add_in_backup_history($lastBckupPath);
                    $this->debug("Succed to backup sql in $lastBckupPath.");
                    if ($shouldDownload) {
                        $this->e2e_test_download_last_backup($bckUpType, $compressionType);
                    } else {
                        if ($shouldServeResponse) {
                            echo wp_json_encode([
                                "status" => "OK",
                                "end_date" => date("Y/m/d H:i:s O "),
                            ]);
                            http_response_code(200);    
                        }
                    }
                    if ($shouldServeResponse) {
                        $this->exit();
                    }
                    return;
                }
                if ('simple-zip' === $bckUpType
                || 'full-zip' === $bckUpType) {
                    set_time_limit(25*60); 
                    $siteSlug = sanitize_title(get_bloginfo( 'name' ));
                    $fileExtension = '.zip'; 
                    $filename = "$siteSlug-simple-backup$fileExtension";
                    $rootPath = realpath(wp_upload_dir()['basedir']);
                    if ('full-zip' === $bckUpType) {
                        $filename = "$siteSlug-full-backup$fileExtension";
                        $rootPath = $wpRootPath;
                    }
                    $bckupFolder = realpath($this->get_backup_folder());
                    $lastBckupPath = "$bckupFolder/$filename";
                    wp_delete_file($lastBckupPath);
                    $historyFolder = "$bckupFolder/_history";
                    if (!file_exists($historyFolder)) {
                        mkdir($historyFolder, 0777, true);
                    } 
                    $historyFolder = realpath($historyFolder);
                    $this->e2e_test_do_backup('sql', '.zip', false, false);
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
                    $zip = new ZipArchive;
                    $zip->open($lastBckupPath, ZipArchive::CREATE); 
                    foreach ($files as $file) {
                        if ($file->isDir()) { continue; }
                        $filePath = $file->getRealPath();
                        $fileName = basename($filePath);
                        if ("$siteSlug-full-backup.zip" === $fileName
                        || "$siteSlug-simple-backup.zip" === $fileName
                        || false !== strpos($filePath, $historyFolder)) {
                            continue;
                        }
                        $relativePath = substr($filePath, strlen($rootPath) + 1);
                        $this->debugVerbose("Backup $relativePath from $filePath in $lastBckupPath.");
                        $zip->addFile($filePath, $relativePath);
                    }
                    $zip->close();
                    if ('simple-zip' === $bckUpType) {
                        $this->e2e_test_add_in_backup_history($lastBckupPath);
                    }
                    $this->debug("Succed to $bckUpType backup to : $lastBckupPath");
                    if ($shouldDownload) {
                        $downloadSimpleZipBckupUrl = add_query_arg([
                            'action' => 'wa-e2e-test-action',
                            'wa-action' => 'download-last-backup',
                            'wa-backup-type' => $bckUpType,
                        ], admin_url( 'admin-ajax.php' ));
                        $this->debug("Will redirect download to : $downloadSimpleZipBckupUrl");
                        if ( wp_redirect( $downloadSimpleZipBckupUrl ) ) {
                            http_response_code(302); $this->exit(); return;
                        }
                        echo wp_json_encode([
                            "error" => "Fail to redirect to $downloadSimpleZipBckupUrl",
                        ]);    
                        $this->err("FAIL Download redirect to : $downloadSimpleZipBckupUrl");
                        $this->exit(); return;
                    } else {
                        if ($shouldServeResponse) {
                            echo wp_json_encode([
                                "status" => "OK",
                                "end_date" => date("Y/m/d H:i:s O "),
                            ]);
                            http_response_code(200);
                        }
                    }
                    if ($shouldServeResponse) : $this->exit(); endif; return;
                }
                $this->err("[$anonimizedIp] Invalid backup type $bckUpType");
                echo wp_json_encode([
                    "error" => "[$anonimizedIp] "
                    . __("Type de backup invalid", 'monwoo-web-agency-config'/** 📜*/),
                ]);
                http_response_code(404);
                $this->exit(); return;            
            }
            static protected $bckupStartTime = null;
            protected function e2e_test_backup_start_time() {
                if (!self::$bckupStartTime) {
                    self::$bckupStartTime = date("Ymd-His_O"); 
                }
                return self::$bckupStartTime;
            }
            protected function e2e_test_add_in_backup_history($filePath, $historySubPath = "") {
                $fs = wa_filesystem();
                $bckupFolder = $this->get_backup_folder();
                $bckupHistoryFolder = "$bckupFolder/_history/"
                . $this->e2e_test_backup_start_time() ;
                if (!file_exists($bckupHistoryFolder)) {
                    mkdir($bckupHistoryFolder, 0777, true);
                }
                $historyFilePath = (strlen($historySubPath) ? "$historySubPath/" : "")
                . basename($filePath);
                $destination = "$bckupHistoryFolder/$historyFilePath";
                $fs->copy($filePath, $destination, true);
                unset($fs);
                $this->debug("Succed to add backup history from $filePath to $destination");
            }
            protected function e2e_test_load_SQL_in_file($filePath) {
                /** @var wpdb $wpdb*/
                global $wpdb;
                assert($wpdb, "Wp DB Not initialized error");
                $dbName = DB_NAME;
                $EOL = "</br>\n";
                $escape = function($value) {
                    if (is_null($value)) {
                        return "NULL";
                    }
                    return "'" . esc_sql($value) . "'"; 
                };  
                $wa_backup_sql = function () use ($dbName, $EOL, $wpdb, $filePath, $escape) {
                    $tablePrefix = ""; 
                    $exclude_tables = [];
                    $sql = "SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE' AND Tables_in_$dbName LIKE '$tablePrefix%'";
                    $tables = $wpdb->get_results($sql);
                    $tables_list = array();
                    foreach ($tables as $table_row) {
                        $table_row = (array)$table_row;
                        $table_name = array_shift($table_row); 
                        if (!in_array($table_name, $exclude_tables)) {
                            $tables_list[] = $table_name;
                        }
                    }
                    $dump_table = function ($dump_file, $table, $eol) use ($wpdb, $escape) {
                        $INSERT_THRESHOLD = 838860; 
                        $dump_file->write("DROP TABLE IF EXISTS `$table`;$eol");
                        $create_table = $wpdb->get_results('SHOW CREATE TABLE `' . $table . '`');
                        $create_table_sql = ((array)$create_table[0])['Create Table'] . ';';
                        $dump_file->write($create_table_sql . $eol . $eol);
                        $data = $wpdb->get_results("SELECT * FROM `$table`");
                        $insert = new InsertSqlStatement($table);
                        foreach ($data as $row) {            
                            $row_values = array();
                            foreach (((array)$row) as $value) {
                                $row_values[] = $escape($value);
                            }
                            $insert->add_row( $row_values );
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
                    if (preg_match('/\.sql\.gz$/', $filePath)
                    || preg_match('/\.tar\.gz$/', $filePath)) {
                        $dump_file = new DumpGzip($filePath); 
                    } elseif (preg_match('/\.zip$/', $filePath)) {
                        $dump_file = new DumpZip($filePath); 
                    } else {
                            $dump_file = new DumpPlainTxt($filePath);
                    }
                    $eol = "\r\n";
                    $dump_file->write("-- Generation time: " . date('r') . $eol);
                    $dump_file->write("-- Host: " . DB_HOST . $eol);
                    $dump_file->write("-- DB name: " . DB_NAME . $eol);
                    $dump_file->write("-- Backup tool author : wa-config, by Miguel Monwoo, service@monwoo.com" . $eol);
                    $dump_file->write("/*!40030 SET NAMES UTF8 */;$eol");
                    $dump_file->write("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;$eol");
                    $dump_file->write("/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;$eol");
                    $dump_file->write("/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;$eol");
                    $dump_file->write("/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;$eol");
                    $dump_file->write("/*!40103 SET TIME_ZONE='+00:00' */;$eol");
                    $dump_file->write("/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;$eol");
                    $dump_file->write("/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;$eol");
                    $dump_file->write("/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;$eol");
                    $dump_file->write("/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;$eol$eol");
                    foreach ($tables_list as $table) {
                        $dump_table($dump_file, $table, $eol);
                    }
                    $dump_file->write("$eol$eol");
                    $dump_file->write("/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;$eol");
                    $dump_file->write("/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;$eol");
                    $dump_file->write("/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;$eol");
                    $dump_file->write("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;$eol");
                    $dump_file->write("/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;$eol");
                    $dump_file->write("/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;$eol");
                    $dump_file->write("/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;$eol$eol");
                    $dump_file->end();
                    $this->debug("Did backup SQL to :$EOL{$dump_file->file_location}");
                };
                $wa_backup_sql();
            }
            protected function  _002_e2e_test__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_002_e2e_test__bootstrap')) {
                    return; 
                }
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                $maxTestDelay = 60 * 15; 
                if ($E2ETestsOptions['access-open'] ?? false) {
                    if ((time() - $E2ETestsOptions['access-open']) > $maxTestDelay) {
                        ob_start();
                        $this->e2e_test_clean_and_restore_test_users();
                        $restoreReport = ob_get_clean();
                        $this->err(
                            "MANUAL RESET of e2e access-open, that sound buggy since open for more than 15 minutes",
                            $restoreReport
                        );
                        update_option($this->eConfigE2ETestsOptsKey, []);
                        return;
                    }
                    $default_opts = array(
                        'http'=>array(
                            'header'=>"wa-e2e-test-mode: wa-config-e2e-tests\r\n" .
                                    "", 
                        )
                    );
                    if ($this->shouldDebug) {
                        $default_opts["ssl"] = array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        );
                        $default_opts["notification"] = [$this, "e2e_test_stream_notification_callback"];
                        add_filter('http_request_args', function ($args, $url) {
                            $args['headers']['wa-e2e-test-mode'] = 'wa-config-e2e-tests';
                            $args['sslverify'] = false;
                            return $args;
                        }, 100, 2);
                    }
                    $default = stream_context_set_default($default_opts);
                    $headersRaw = getallheaders();
                    $headers = [];
                    foreach($headersRaw as $h => $v) {
                        $headers[strtolower($h)] = $v;
                    }
                    if ('wa-config-e2e-tests' !== ($headers['wa-e2e-test-mode'] ?? false)) {
                            $this->debug(
                            "Website under test mode, serving maintenance page for external access",
                        );
                        echo __("<strong>Tests en cours, merci de revenir plus tard (15 minutes à 2 heures de délais). MAINTENANCE MODE, please come back later.</strong>", 'monwoo-web-agency-config'/** 📜*/);
                        $this->exit(); return;
                    }
                }
            }
            /**
             * Dev in progress, not ready yet
             */
            public function e2e_test_stream_notification_callback(
                $notification_code, $severity, $message,
                $message_code, $bytes_transferred, $bytes_max) {
                if (STREAM_NOTIFY_REDIRECTED === $notification_code)  {
                    $this->debug(
                        "e2e_test_stream_notification Redirection vers : ", $message
                    );
                } else {
                    $this->debug(
                        "e2e_test_stream_notification [$notification_code]"
                    );
                }
            }
            static protected $e2e_tests_wp_die_callback = null;
            protected function  e2e_test_register_wp_die_callback() {
                if (self::$e2e_tests_wp_die_callback) {
                    return; 
                }
                self::$e2e_tests_wp_die_callback = array($this, 'e2e_tests_filter_wp_die_callback');
                add_filter('wp_die_ajax_handler', self::$e2e_tests_wp_die_callback, 100);
                add_filter('wp_die_json_handler', self::$e2e_tests_wp_die_callback, 100);
                add_filter('wp_die_jsonp_handler', self::$e2e_tests_wp_die_callback, 100);
                add_filter('wp_die_xmlrpc_handler', self::$e2e_tests_wp_die_callback, 100);
                add_filter('wp_die_xml_handler', self::$e2e_tests_wp_die_callback, 100);
                add_filter('wp_die_handler', self::$e2e_tests_wp_die_callback, 100);
            }
            protected function  _002_e2e_test__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_002_e2e_test__load')) {
                    return; 
                }
                wp_cache_delete("alloptions", "options"); 
                $E2ETestsOptions = get_option($this->eConfigE2ETestsOptsKey, []);
                add_action(
                    'wp_ajax_nopriv_wa-e2e-test-action', 
                    [$this, 'e2e_test_action']
                );
                add_action(
                    'wp_ajax_wa-e2e-test-action', 
                    [$this, 'e2e_test_action']
                );
                if ($E2ETestsOptions['access-open'] ?? false || $this->shouldDebugVeryVerbose) {
                    $this->e2e_test_register_wp_die_callback();
                    add_filter('wp_mail',[$this, 'e2e_tests_emails_middleware'], 10,1);
                }
            }
            /**
             * Check if it's the fisrt time a method is called over all App instances
             * 
             * @param string $methodeName The method name to test
             * @return boolean true if it's the first time the method is called, false otherwise
             * @see Parallelizable::p_higherThanOneCallAchievedSentinel
             */
            public function isFirstMethodCall(string $methodeName)
            {
                $isFirstCall = key_exists($methodeName, self::$_methodes)
                    ? self::$_methodes[$methodeName][self::$_statsCountKey] === 0
                    : true;
                if ($isFirstCall) {
                    $this->debugVeryVerbose(
                        "First call by {$this->iId} for $methodeName"
                    );
                }
                return $isFirstCall;
            }
            /**
             * Register a method call by it's method name
             * 
             * @param string $methodeName The method name to test
             * @see Parallelizable::p_higherThanOneCallAchievedSentinel()
             */
            public function methodeCalledFrom(string $methodeName): void
            {
                $iId = $this->iId;
                if (!key_exists($methodeName, self::$_methodes)) {
                    self::$_methodes[$methodeName] = [];
                }
                $statistics = &self::$_methodes[$methodeName];
                if (!key_exists(self::$_statsCountKey, $statistics)) {
                    $statistics[self::$_statsCountKey] = 0;
                }
                $statistics[self::$_statsCountKey]++;
                if (!key_exists($iId, $statistics)) {
                    $statistics[$iId] = 0;
                }
                $statistics[$iId]++;
                $this->debugVeryVerbose("Methode statistics after $methodeName call", self::$_methodes);
            }
            /**
             * AppInterface constructor
             * 
             * Should be called in children constructor,
             * after child initialisation of instances properties
             * 
             * @param string $iPrefix The Identifiable prefix name of current App instance
             * @return void
             */
            public function __construct(string $iPrefix)
            {
                add_action('admin_notices', [
                    new Notice(),
                    Notice::class . "::displayNotices"
                ]);
                $this->iPrefix = $iPrefix;
                self::addInstance($this);
            }
            /**
             * Call all __bootstrap functions of the plugin.
             *
             * Triggered from main plugin file, juste after class constructor.
             * It will call all owned ```{.*}__bootstrap()``` functions
             */
            public function bootstrap(): void
            {
                global $wp;
                $this->debug("\n");
                $iid = $this->iId;
                if ($_SERVER && isset($_SERVER['SERVER_PORT'])) {
                    $protocole = "https";
                    $domain = sanitize_text_field($_SERVER['HTTP_HOST']);
                    $port = sanitize_text_field($_SERVER['SERVER_PORT']);
                    if (
                        $port != "80"
                        && $port != "443"
                    ) {
                        $domain .= ":" . $port;
                    }
                    $uri = sanitize_text_field($_SERVER['REQUEST_URI']);
                    $url = "$protocole://$domain$uri";
                    $this->debug("Plugin bootstraping\n{$this->pluginFile}\n\nFrom [$iid] :\n $url");
                } else {
                    global $argv;
                    $this->debug("Plugin bootstraping\n\nFrom [$iid] :\n {$argv[0]}");
                }
                $methods = get_class_methods($this);
                usort($methods, 'strnatcasecmp'); 
                foreach ($methods as $m) {
                    if (strEndsWith($m, "__bootstrap")) {
                        $this->debugVerbose("Bootstrap with: '$m'");
                        $this->$m();
                        if (self::$shouldExitAll) {
                            $this->debugVerbose("Should exit after last bootstrap : '$m'");
                            return; 
                        }
                    }
                }
                add_action('plugins_loaded', [$this, 'loadPlugin'], 11);
                $this->debugVerbose("Did bootstrap plugin");
            }
            /**
             * Call all __load functions of the plugin.
             *
             * Triggered by 'plugins_loaded' action.
             * It will call all owned ```{.*}__load()``` functions
             */
            public function loadPlugin(): void
            {
                $this->debug("Loading plugin from action 'plugins_loaded'");
                $methods = get_class_methods($this);
                usort($methods, 'strnatcasecmp'); 
                foreach ($methods as $m) {
                    if (strEndsWith($m, "__load")) {
                        $this->debugVerbose("Load with: '$m'");
                        $this->$m();
                        if (self::$shouldExitAll) {
                            $this->debugVerbose("Should exit after last load : '$m'");
                            return; 
                        }
                    }
                }
                $this->debugVerbose("Did load plugin");
            }
        }
    }
}
namespace WA\Config\Utils {
    use WA\Config\Core\AppInterface;
    use WA\Config\Core\Translatable;
    use Walker_Nav_Menu_Checklist;
    use WP_Filesystem_Direct;
    use ZipArchive;
    if (!function_exists(strEndsWith::class)) {
        /**
         * Check if string $haystack end with $needle
         * 
         * @param string $haystack The string to search in
         * @param string $needle The lookup
         * @return boolean true if $haystack end with $needle
         */
        function strEndsWith($haystack, $needle)
        {
            $length = strlen($needle);
            if (!$length) {
                return true;
            }
            return substr($haystack, -$length) === $needle;
        }
    }
    if (!function_exists(\WA\Config\Utils\__::class)) {
        /**
         * Get a translated string in the given translated domain
         * 
         * @param string $string The string to translate
         * @param string $textdomain The 'textdomain id' to load the translations from (ex : wa-config)
         * @param string $locale The locale to load the related local value (ex : fr_FR)
         */
        function __($text, $textdomain, $locale, $app = null){
            global $l10n;
            if(isset($l10n[$textdomain])) $backup = $l10n[$textdomain];
            $app = $app ?? AppInterface::instance();
            $langFolder = $app->pluginRoot . "languages/$textdomain-$locale.mo";
            unload_textdomain($textdomain); 
            $app->assertLog(
                load_textdomain(
                    $textdomain,
                    $langFolder
                ),
                "Fail to load textdomain $textdomain for $locale"
                    . " at $langFolder"
            );
            $translation = \__($text, $textdomain);
            if(isset($backup)) $l10n[$textdomain] = $backup;
            return $translation;
        }
    }
    if (!function_exists(\WA\Config\Utils\_x::class)) {
        /**
         * Get a translated string in the given translated domain
         * 
         * @param string $text The string to translate
         * @param string $context The translator context helper (txt advice for translator)
         * @param string $textdomain The 'textdomain id' to load the translations from (ex : wa-config)
         * @param string $locale The locale to load the related local value (ex : fr_FR)
         */
        function _x($text, $context, $textdomain, $locale, $app = null){
            global $l10n;
            if(isset($l10n[$textdomain])) $backup = $l10n[$textdomain];
            $app = $app ?? AppInterface::instance();
            $langFolder = $app->pluginRoot . "languages/$textdomain-$locale.mo";
            unload_textdomain($textdomain); 
            $app->assertLog(
                load_textdomain(
                    $textdomain,
                    $langFolder
                ),
                "Fail to load textdomain '$textdomain' for '$locale'"
                    . " at $langFolder"
            );
            $translation = \_x($text, $context, $textdomain);
            $app->debugVeryVerbose("Did _x [$locale] $translation");
            if(isset($backup)) $l10n[$textdomain] = $backup;
            return $translation;
        }
    }
    if (!function_exists(wa_filesystem::class)) {
        /**
         * Return our wa filesystem (Direct mode)
         * 
         * @return WP_Filesystem_Direct initilalized filesystem
         */
        function wa_filesystem(){
            require_once ( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
            $fs = new WP_Filesystem_Direct(null);
            return $fs;
        }
    }
    if (!function_exists(wa_redirect::class)) {
        /**
         * Redirect to the targeted url using an optional appHandler.
         * 
         * Follow this call by a return statement.
         * It will exit on regular usage and return in test mode launch.
         * 
         * @param string $redirectUrl The url to redirect to
         * @param AppInterface $appHandler The app used to handle exit and debugs
         */
        function wa_redirect($redirectUrl, $appHandler = null){
            if (!$appHandler) {
                $appHandler = AppInterface::instance();
            }
            if ( wp_redirect( $redirectUrl ) ) {
                echo  wp_kses_post("<a href='$redirectUrl'> [302] Redirecting to $redirectUrl...</a>");
                $appHandler->exit(); return;
            } else {
                echo wp_kses_post("<a class='fail-wp-redirect' href='$redirectUrl'> [302] Redirecting to $redirectUrl... please click this link.</a>");
                $appHandler->debug("wa_redirect Fail to redirect to : $redirectUrl");
            }
        }
    }
    if (!function_exists(wa_render::class)) {
        /**
         * Render a callable or a list of callable
         * 
         * @param callable|array<callable> $callables The callable(s) to render
         * @since 0.0.2
         */
        function wa_render($callables, $app = null) : void {
            if (!$app) {
                $app = AppInterface::instance();
            }
            $app->debugVeryVerbose("Will wa_render", $callables);
            if (!$callables) {
                $app->debug("Will avoid wa_render, empty callable");
                return; 
            }
            if (!is_array($callables)) {
                $callables = [ $callables ];
            }
            foreach ($callables as $idx => $callable) {
                if (is_callable($callable)) {
                    call_user_func($callable);
                } else {
                    $app->err("wa_render : Wrong callable [$idx]", $callable);                    
                }
            }
        }
    }
    if (!trait_exists(PdfToHTMLable::class)) {
        /**
         * This trait will load pdf.js script
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait PdfToHTMLable
        {
            protected function  _010_pdfAble_scripts__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_pdfAble_scripts__load')) {
                    return; 
                }
            }
            /**
             * wp_enqueue_script needed js for pdf.js as 'wa-config-pdf-to-html-js'
             */
            public function pdfAble_scripts_do_enqueue(): void
            {
                $this->debugVerbose("Will pdfAble_scripts_do_enqueue");
                $jsFile = "assets/pdfjs/build/pdf.js";
                add_filter(
                    'script_loader_tag',
                    [$this, 'pdfAble_scripts_tag'],
                    10,
                    3
                );
                wp_enqueue_script(
                    'wa-config-pdf-to-html-js',
                    plugins_url($jsFile, $this->pluginFile),
                    [],
                    $this->pluginVersion,
                    true
                );
            }
            /**
             * Add async script tags feature for 'wa-config-pdf-to-html-js'.
             *
             * @param string $tag HTML for the script tag.
             * @param string $handle Handle of script.
             * @param string $src Src of script.
             * @return string the feshly updated script tag
             */
            public function pdfAble_scripts_tag($tag, $handle, $source)
            {
                if ('wa-config-pdf-to-html-js' === $handle) {
                    ob_start();
                    ?>
                        <script
                        type="text/javascript"
                        src="<?php echo esc_url($source) ?>"
                        id="<?php echo esc_attr($handle) ?>"
                        async
                        ></script>
                    <?php
                    $tag = ob_get_clean();
                    $this->debugVerbose("pdfAble_scripts_tag", $tag);
                } else {
                    $this->debugVerbose("script_loader_tag $handle");
                }
                return $tag;
            }
        }
    }
    if (!class_exists(DumpInterface::class)) { 
        /**
         * This abstract class will define a dump interface
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        abstract class DumpInterface {
            abstract function open();
            abstract function write($string);
            abstract function end();
            function __construct($file) {
                $this->file_location = $file;
                $this->fh = $this->open();
                if (!$this->fh) {
                    throw new \Exception("Couldn't create DUMP file $file");
                }
            }
        }
    }
    if (!class_exists(DumpPlainTxt::class)) { 
        /**
         * This class will dump to a text file
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class DumpPlainTxt extends DumpInterface {
            function open() {
                return fopen($this->file_location, 'w');
            }
            function write($string) {
                return fwrite($this->fh, $string);
            }
            function end() {
                return fclose($this->fh);
            }
        }
    }
    if (!class_exists(DumpZip::class)) { 
        /**
         * This class will dump SQL to a zip file
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class DumpZip extends DumpPlainTxt {
            function __construct($file) {
                $this->file_location = str_replace('.zip', '.sql', $file);
                DumpPlainTxt::__construct($this->file_location);
            }
            function open() {
                return DumpPlainTxt::open();
            }
            function write($string) {
                DumpPlainTxt::write($string);
            }
            function end() {
                DumpPlainTxt::end();
                $sqlFile = $this->file_location;
                $this->file_location = str_replace('.sql', '.zip', $this->file_location);
                $zip = new ZipArchive;
                $zip->open($this->file_location, ZipArchive::CREATE | ZipArchive::OVERWRITE );
                $relativePath = basename($sqlFile);
                $relativeZip = basename($this->file_location);
                global $_wa_fetch_instance;
                $wa = $_wa_fetch_instance();
                $wa->debug("DumpZip [$relativeZip] : Adding '$relativePath' from '$sqlFile'");
                $zip->addFile($sqlFile, $relativePath);
                $zip->close();
            }
        }
    }
    if (!class_exists(DumpGzip::class)) { 
        /**
         * This class will dump to a .gz file (gzip compressed)
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class DumpGzip extends DumpInterface {
            function open() {
                return gzopen($this->file_location, 'wb9');
            }
            function write($string) {
                return gzwrite($this->fh, $string);
            }
            function end() {
                return gzclose($this->fh);
            }
        }
    }
    if (!class_exists(InsertSqlStatement::class)) { 
        /**
         * This class will help to format a SQL insert statement
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class InsertSqlStatement {
            private $rows = array();
            private $length = 0;
            private $table;
            function __construct($table) {
                $this->table = $table;
            }
            function reset() {
                $this->rows = array();
                $this->length = 0;
            }
            function add_row($row) {
                $row = '(' . implode(",", $row) . ')';
                $this->rows[] = $row;
                $this->length += strlen($row);
            }
            function get_sql() {
                if (empty($this->rows)) {
                    return false;
                }
                return 'INSERT INTO `' . $this->table . '` VALUES ' . 
                    implode(",\n", $this->rows) . '; ';
            }
            function get_length() {
                return $this->length;
            }
        }
    }
    if (!trait_exists(TranslatableProduct::class)) { 
        /**
         * This trait will add the polylang language feature to WooCommerce product post type
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait TranslatableProduct
        {
            use Translatable;
            protected function _010_t_product__bootstrap() {
                $waProductTypeVersion = ""; 
                if ($waProductTypeVersion !== AppInterface::PLUGIN_VERSION) {
                }
            }
            protected function _010_t_product__load()
            {
                $this->debugVerbose("Will _010_t_product__load");
                if ($this->p_higherThanOneCallAchievedSentinel('_010_t_product_post__load')) {
                    return; 
                }
                add_filter( 'woocommerce_order_number', [$this, 't_product_change_woocommerce_order_number'], 1, 2);
                if ( function_exists( 'pll_count_posts' ) ) {
                    add_filter( 'pll_get_post_types', [$this, 't_product_post_type_polylang_register'], 1, 2 );
                    add_filter( 'pll_get_taxonomies', [$this, 't_product_category_taxo_polylang_register'], 1, 2 );
                }
            }
            /**
             * Add prefix to woocommerce order numbers
             * 
             */
            public function  t_product_change_woocommerce_order_number( $order_id, $order ) {
                $prefix = $this->getWaConfigOption(
                    $this->eConfWooCommerceOrderPrefix,
                    ""
                );
                $suffix = ''; 
                return $prefix . $order_id . $suffix;
            }
            /**
             * Register woocommerce product post type with polylang plugin
             */
            public function t_product_post_type_polylang_register( $post_types, $is_settings ) {
                $this->debugVerbose("Will t_product_post_type_polylang_register");
                $missionCptKey = 'product';
                if ( $is_settings ) {
                    unset( $post_types[$missionCptKey] );
                } else {
                    $post_types[$missionCptKey] = $missionCptKey;
                }
                return $post_types;
            }
            /**
             * Register woocommerce product related taxonomy with polylang plugin
             */
            public function t_product_category_taxo_polylang_register( $taxonomies, $is_settings ) {
                $this->debugVerbose("Will t_product_category_taxo_polylang_register");
                $productTaxoList = ['product_tag', 'product_shipping_class'
                , 'product_type', 'product_visibility', 'product_cat'];
                foreach ($productTaxoList as $taxoKey) {
                    if ( $is_settings ) {
                        unset( $taxonomies[$taxoKey] );
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
    use ArrayObject;
    use Exception;
    use PhpParser\Node\Stmt\Foreach_;
    use ReflectionClass;
    use SplPriorityQueue;
    use WA\Config\Core\AppInterface;
    use WA\Config\Core\Editable;
    use WA\Config\Core\EditableWaConfigOptions;
    use WA\Config\Core\Identifiable;
    use WA\Config\Core\Translatable;
    use WA\Config\Core\Parallelizable;
    use WA\Config\Core\TestableEnd2End;
    use WA\Config\Core\WPActions;
    use WA\Config\Core\WPFilters;
    use WA\Config\Utils\PdfToHTMLable;
    use Walker_Nav_Menu_Checklist;
    use WP;
    use WP_Error;
    use WP_Filesystem_Direct;
    use WP_REST_Request;
    use WP_REST_Response;
    use ZipArchive;
    use WA\Config\Utils;
    use function WA\Config\Utils\wa_filesystem;
    use function WA\Config\Utils\wa_redirect;
    use function WA\Config\Utils\wa_render;
    if (!class_exists(Notice::class)) { 
        /**
         * This class will hold the admin notice that show on WordPress admin panels
         * 
         * It can be use from anywhere to add a 120 secondes cached notice message for :
         * - Error : {@see Notice::displayError()}
         * - Info : {@see Notice::displayInfo()}
         * - Warning : {@see Notice::displayWarning()}
         * - Success : {@see Notice::displaySuccess()}
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class Notice
        {
            const NOTICES_FIELD_ID = 'wa_config_admin_notices';
            /**
             * Echo the Notice report to inject in WA Config admin panels
             */
            public function displayNotices(): void
            {
                $notices = get_transient(self::NOTICES_FIELD_ID);
                if (!$notices) {
                    return;
                }
                foreach ($notices as $idx => $notice) {
                    $message     = isset($notice['message']) ? $notice['message'] : false;
                    $noticeLevel = !empty($notice['notice-level']) ? $notice['notice-level'] : 'notice-error';
                    if ($message) {
                        echo wp_kses_post("<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>");
                    }
                }
                delete_transient(self::NOTICES_FIELD_ID);
            }
            /**
             * Will save an Error Notice to display for 120 seconds.
             *
             * @param string $message Content of the notice.
             */
            public static function displayError($message): void
            {
                self::updateOption($message, 'notice-error');
            }
            /**
             * Will save a Warning Notice to display for 120 seconds.
             *
             * @param string $message Content of the notice.
             */
            public static function displayWarning($message)
            {
                self::updateOption($message, 'notice-warning');
            }
            /**
             * Will save an Info Notice to display for 120 seconds.
             *
             * @param string $message Content of the notice.
             */
            public static function displayInfo($message)
            {
                self::updateOption($message, 'notice-info');
            }
            /**
             * Will save an Success Notice to display for 120 seconds.
             *
             * @param string $message Content of the notice.
             */
            public static function displaySuccess($message)
            {
                self::updateOption($message, 'notice-success');
            }
            protected static function updateOption($message, $noticeLevel)
            {
                $notices = ($notices = get_transient(self::NOTICES_FIELD_ID)) ? $notices : [];
                $notices[] = [
                    'message' => $message,
                    'notice-level' => $noticeLevel
                ];
                set_transient(self::NOTICES_FIELD_ID, $notices, 120);
            }
        }
    }
    if (!trait_exists(EditableAdminScripts::class)) { 
        /**
         * This trait loads the wa-config administration sytesheet
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         */
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
                add_action(
                    'admin_enqueue_scripts', 
                    [$this, 'e_admin_scripts_do_enqueue']
                );
            }
            /**
             * Enqueue stylesheet and javascripts for wp-admin customisation by wa-config.
             * 
             * wp_enqueue_style the admin assets/styles-admin.css script from plugin directory.
             * wp_enqueue_script the admin assets/app-admin.js script from plugin directory.
             */
            public function e_admin_scripts_do_enqueue(): void
            {
                $this->debugVerbose("Will e_admin_scripts_do_enqueue for '" . get_current_screen()->id . "'");
                $cssFile = "assets/styles-admin.css";
                wp_enqueue_style(
                    "{$this->iPrefix}-css-admin",
                    plugins_url($cssFile, $this->pluginFile),
                    [],
                    $this->pluginVersion
                );
                $jsFile = "assets/app-admin.js";
                $jsUrl = plugins_url($jsFile, $this->pluginFile);
                wp_enqueue_script(
                    "{$this->iPrefix}-js-admin",
                    $jsUrl,
                    [ 'jquery', 'suggest' ],
                    $this->pluginVersion,
                    true
                );
            }
        }
    }
    if (!trait_exists(OrderablePluginLoads::class)) { 
        /**
         * This trait will ensure that master plugin is loaded first
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         */
        trait OrderablePluginLoads
        {
            protected function _001_o_plugin_loads__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_001_o_plugin_loads__bootstrap')) {
                    return; 
                }
                if (is_admin()) {
                    $this->debug("Will _001_o_plugin_loads__bootstrap");
                }
            }
            protected function _001_o_plugin_loads__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_001_o_plugin_loads__load')) {
                    return; 
                }
                add_action(
                    'wp_ajax_wa-plugin-loads-do-order-action', 
                    [$this, 'plugin_loads_admin_do_order_action']
                );
            }
            /**
             * Ensure master plugin is loaded first.
             */
            public function o_plugin_loads_master_first($masterPlugin): void
            {
                if ($plugins = get_option( 'active_plugins' ) ) {
                    $plugins = array_unique($plugins); 
                    $this->debugVeryVerbose("[$masterPlugin] Will o_plugin_loads_master_first from : ", $plugins);
                    if ( false !== ($key = array_search( $masterPlugin, $plugins ) )) {
                        array_splice( $plugins, $key, 1 );
                        array_unshift( $plugins, $masterPlugin );
                        update_option( 'active_plugins', $plugins );
                        wp_cache_delete("alloptions", "options"); 
                        $this->debugVerbose("Did o_plugin_loads_master_first for $masterPlugin");
                        $this->debugVerbose("Plugin activation order : ", $plugins);
                    }
                }
            }
            /**
             * Will run the associated order action 'wa_order_action'. (ajax admin request)
             *  
             * Available **wa_order_action** :
             * - [ **move-first** ] : move plugin to first load position
             *   - **wa_plugin_relative_file** : relative plugin file from plugin folder
             *   - **wa_plugin_iid** : parallel instance that ask to fulfil the action
             */
            public function plugin_loads_admin_do_order_action() : void {
                $anonimizedIp = $this->get_user_ip();
                if (!current_user_can('administrator')) { 
                    $this->err("plugin_loads_admin_do_order_action invalid access for $anonimizedIp, need to be administrator");
                    echo wp_json_encode([
                        "error" => "Invalid access for $anonimizedIp registred",
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }
                $this->debug("Will plugin_loads_admin_do_order_action");
                $action = filter_var(
                    sanitize_key($_REQUEST['wa_order_action'] ?? null),
                    FILTER_SANITIZE_SPECIAL_CHARS
                );
                $isJson = wp_is_json_request();
                if ($isJson) {
                    header("Content-Type: application/json");
                }
                $authenticatedActions = [
                    'move-first' => function($app, $action) use ($isJson) {
                        $masterPlugin = filter_var(
                            sanitize_text_field($_REQUEST['wa_plugin_relative_file'] ?? null),
                            FILTER_SANITIZE_SPECIAL_CHARS
                        );
                        $pluginIId = filter_var(
                            sanitize_text_field($_REQUEST['wa_plugin_iid']),
                            FILTER_SANITIZE_SPECIAL_CHARS
                        );
                        $this->o_plugin_loads_master_first($masterPlugin);
                        $app->info("Succed move-first plugin action for : '$masterPlugin'");
                        if ($isJson) {
                            return [
                                "code" => 'ok',
                                "wa_order_action" => $action,
                                "data" => [
                                    'plugin' => $masterPlugin,
                                ]
                            ];
                        } else {
                            $redirectUrl = admin_url( 'plugins.php' )
                            . "#wa-plugin-order-list-$pluginIId";
                            wa_redirect($redirectUrl, $this); return "";
                        }
                    },
                ];
                $resp = null;
                if (array_key_exists($action, $authenticatedActions)) {
                    $resp = $authenticatedActions[$action]($this, $action);
                } else {
                    $this->err("Unknow wa_order_action '$action'");
                    if (!$isJson) {
                        echo wp_kses_post("Unknow wa_order_action '$action'");
                    }
                    $resp = [
                        'code' => 'wa_error',
                        "error" => 'wa_unknow_order_action',
                        "data" => [
                            'wa_order_action' => $action,
                            'status' => 404
                        ]
                    ];
                }
                if ($isJson) {
                    echo $resp ? wp_json_encode($resp) : wp_kses_post("wa_order_action '$action' did fail");
                }
            }
        }
    }
    if (!trait_exists(ExtendablePluginDescription::class)) { 
        /**
         * This trait will allow to extend the plugin description inside the WordPress plugin list panel
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         * @uses Identifiable
         * @uses OrderablePluginLoads
         */
        trait ExtendablePluginDescription
        {
            use Editable;
            use Identifiable;
            use OrderablePluginLoads;
            protected function _020_ext_plugin_description__load()
            {
                add_filter(
                    'plugin_row_meta',
                    [$this, 'ext_plugin_description_meta'],
                    10,
                    2
                );
            }
            /**
             * Update the plugin description meta with extra informations
             *
             * @param array<int, string> $plugin_meta Existing plugin meta.
             * @param string $plugin_file The plugin file, to check against current instance pluginFile.
             * @return array<int, string> the feshly updated plugin meta
             */
            function ext_plugin_description_meta($plugin_meta, $plugin_file)
            {
                if ($plugin_file == plugin_basename($this->pluginFile)) {
                    $pluginWpPath = $this->pluginRelativeFile;
                    $moveFirstLink = add_query_arg([
                        'action' => 'wa-plugin-loads-do-order-action',
                        'wa_order_action' => 'move-first',
                        'wa_plugin_relative_file'  => $pluginWpPath,
                        'wa_plugin_iid' => $this->iId,
                    ], admin_url( 'admin-ajax.php' ));
                    $isFirstTitle = __(
                        "Première instance",
                        'monwoo-web-agency-config'/** 📜*/
                    );
                    $moveFirstTitle = __(
                        "Charger en premier",
                        'monwoo-web-agency-config'/** 📜*/
                    );
                    $actions = "";
                    $plugins = get_option( 'active_plugins' );
                    $this->debugVerbose("[$pluginWpPath] Will ext_plugin_description_meta with master plugin : {$plugins[0]}");
                    $is2ndOrMore = $this->iIndex > 0;
                    if ($is2ndOrMore) {
                        $actions .= " (<a href='$moveFirstLink'>$moveFirstTitle</a>)";
                    } else {
                        $actions .= " ($isFirstTitle)";
                    }
                    ob_start();
                    ?>
                        <span
                        class="wa-link-anchor"
                        id="wa-plugin-order-list-<?php echo esc_attr($this->iId) ?>">
                        </span>
                        <strong><?php echo esc_attr($this->pluginName); ?></strong>
                        <?php echo wp_kses_post($actions); ?>
                    <?php
                    $pOrderList = ob_get_clean();
                    $plugin_meta[] = $pOrderList;
                    $plugin_meta[] = $this->iId;
                }
                return $plugin_meta;
            }
        }
    }
    if (!trait_exists(EditableMissionPost::class)) { 
        /**
         * This trait will add the wa-mission post type
         * 
         * You could use it frontend side with the REST API or this kind of plugin :
         * 
         * {@link https://fr.wordpress.org/plugins/custom-post-type-widget-blocks/
         * Custom Post Type Widget Blocks by thingsym }
         * 
         * {@see https://developer.wordpress.org/rest-api/reference/posts
         * REST API Handbook for POST (equivalent of wa-mission)}
         * 
         * ```js
         * // JS ES6 usage example to fecth skill taxonomies :
         * // You can copy/past below code in your Chrome console to test it :
         * // (A Console opened by inspecting the same domain name to
         * // avoid fetch errors)
         * async function getMissions() {
         *     const WpV2ApiBaseUrl = "https://web-agency.local.dev/e-commerce/wp-json/wp/v2";
         *     let response = await fetch(`${WpV2ApiBaseUrl}/wa-mission`, {
         *         method: 'GET',
         *         mode: 'no-cors',
         *         withCredentials: false,
         *         headers: {
         *             'Content-type': 'application/json; charset=UTF-8'
         *         },
         *     });
         *     let missions = await response.json();
         *     return missions;
         * }
         * getMissions().then(data => console.log(data) && data);
         * ```
         * 
         * ```bash
         * # curl example to fecth skill taxonomies :
         * WP_V2API_BASEURL="https://web-agency.local.dev/e-commerce/wp-json/wp/v2"
         * curl "$WP_V2API_BASEURL/wa-mission"
         * ```
         *
         * 
         * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-content-types
         * @see https://developer.wordpress.org/rest-api/reference
         * @see https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         * @uses Translatable
         */
        trait EditableMissionPost
        {
            use Editable;
            use Translatable;
            protected function _010_e_mission_CPT__load()
            {
                add_filter( 'template_include', [$this, 'e_mission_CPT_load_template_includes'], 1 );
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_mission_CPT__load')) {
                    return; 
                }
                add_action( 'init', [$this, 'e_mission_CPT_register']);
                add_action( 'admin_menu', [$this, 'e_mission_CPT_admin_menu'], 2);
                $missionCptKey = "wa-mission";
                add_action("save_post_$missionCptKey", [$this, 'e_mission_CPT_end_date_save_metabox']);
                add_filter("manage_{$missionCptKey}_posts_columns",  [$this, 'e_mission_CPT_end_date_add_column']);
                add_filter("manage_{$missionCptKey}_posts_custom_column",  [$this, 'e_mission_CPT_end_date_render_column_row'], 10, 2);
                add_action( 'get_the_date', [$this, 'e_mission_CPT_get_the_date'], 10, 3 );
                add_filter( 'ocean_main_metaboxes_post_types', [$this, 'e_mission_CPT_oceanwp_metabox'], 20 );
                add_filter( 'ocean_post_layout_class', [$this, 'e_mission_CPT_layout_class'], 20 );
                if ( function_exists( 'pll_count_posts' ) ) {
                    add_filter( 'pll_get_post_types', [$this, 'e_mission_CPT_polylang_register'], 1, 2 );
                    add_filter( 'pll_the_language_link', [$this, 'e_mission_CPT_polylang_lang_link'], 10, 3 );
                }
                add_action( 'admin_head-nav-menus.php', [$this, 'e_mission_CPT_do_template_nav_menus'] );
                add_filter( 'wp_get_nav_menu_items', [$this, 'e_mission_CPT_do_template_nav_menus_filter'], 10, 3 );
            }
            /**
             * Alter your post layouts
             * 
             * @return string class : [ **full-width** ], full-screen, left-sidebar, right-sidebar or both-sidebars
             */
            public function  e_mission_CPT_layout_class($class ) {
                $this->debugVerbose("Will e_mission_CPT_layout_class");
                $pType = 'wa-mission';
                if ( is_singular( $pType )
                || is_post_type_archive( $pType )) {
                    $class = 'full-width';
                }
                return $class;
            }
            /**
             * Add the OceanWP Settings metabox in your CPT
             * 
             */
            public function  e_mission_CPT_oceanwp_metabox( $types ) {
                $this->debugVerbose("Will e_mission_CPT_oceanwp_metabox");
                $types[] = 'wa-mission';
                return $types;
            }
            /**
             * Filter templates to load custom templates if availables
             * 
             * @param string $template_path Pre-filtered template path
             */
            public function  e_mission_CPT_load_template_includes($template_path) {
                $this->debugVerbose("Will e_mission_CPT_load_template_includes");
                if ( get_post_type() == 'wa-mission' ) {
                    if ( is_single() ) {
                        if ( $theme_file = locate_template( array ( 'single-wa-mission.php' ) ) ) {
                            $template_path = $theme_file;
                        } else {
                            $theme_file = false;
                            $currentTheme = basename(get_parent_theme_file_path());
                            if ("oceanwp" === $currentTheme) {
                                $theme_file = realpath($this->pluginRoot
                                . 'templates/themes/oceanwp/singular-wa-mission.php');
                            }
                            $theme_file = $theme_file ?  $theme_file : realpath($this->pluginRoot
                            . 'templates/single-wa-mission.php');
                            if ($theme_file) {
                                $template_path = $theme_file;
                            }
                        }
                    } else { 
                        if ( $theme_file = locate_template( array ( 'index-wa-mission.php' ) ) ) {
                            $template_path = $theme_file;
                        } else {
                            $theme_file = false;
                            $currentTheme = basename(get_parent_theme_file_path());
                            if ("oceanwp" === $currentTheme) {
                                $theme_file = realpath($this->pluginRoot
                                . 'templates/themes/oceanwp/index-wa-mission.php');
                            }
                            $theme_file = $theme_file ?  $theme_file : realpath($this->pluginRoot
                            . 'templates/index-wa-mission.php');
                            if ($theme_file) {
                                $template_path = $theme_file;
                            }
                        }
                    }
                }
                return $template_path;
            }
            /**
             * Add nav menu
             */
            public function e_mission_CPT_do_template_nav_menus() {
                $this->debugVerbose("Will e_mission_CPT_do_template_nav_menus");
                add_meta_box( 'wa_mission_do_template_nav_menus', __( 'Missions', 'monwoo-web-agency-config'/** 📜*/), [$this, 'e_mission_CPT_do_template_nav_menu_metabox'], 'nav-menus', 'side', 'default' );
            }
            /**
             * Filter to render our nav menu
             */
            public function e_mission_CPT_do_template_nav_menus_filter( $items, $menu, $args ) {
                $this->debugVerbose("Will e_mission_CPT_do_template_nav_menus_filter");
                foreach( $items as &$item ){
                    if( $item->object != 'wa-mission' ) continue;
                    $item->url = get_post_type_archive_link( $item->type );
                    if( get_query_var( 'post_type' ) == $item->type ){
                      $item->classes []= 'current-menu-item';
                      $item->current = true;
                    }
                  }
                  return $items;
            }
            /**
             * Add nav menu admin metabox
             */
            public function e_mission_CPT_do_template_nav_menu_metabox() {
                $this->debugVerbose("Will e_mission_CPT_do_template_nav_menu_metabox");
                $missionCptKey = 'wa-mission';
                $post_types = get_post_types( array( 'show_in_nav_menus' => true, 'has_archive' => true ), 'object' );
                if( $post_types ){
                  foreach( $post_types as $post_type ){
                    $post_type->classes = array( $post_type->name );
                    $post_type->type = $post_type->name;
                    $post_type->object_id = $post_type->name;
                    $post_type->title = $post_type->labels->name;
                    $post_type->object = $missionCptKey; 
                  }
                  $walker = new Walker_Nav_Menu_Checklist( array() );?>
                  <div id="wa-mission-menu" class="posttypediv">
                    <div id="tabs-panel-wa-mission" class="tabs-panel tabs-panel-active">
                      <ul id="wa-mission-checklist" class="categorychecklist form-no-clear">
                        <?php
                            echo wp_kses_post(walk_nav_menu_tree(
                                array_map( 'wp_setup_nav_menu_item', $post_types ), 0, (object) array( 'walker' => $walker )
                            ));
                        ?>
                      </ul>
                    </div>
                  </div>
                  <p class="button-controls">
                    <span class="add-to-menu">
                      <input type="submit"<?php disabled( $nav_menu_selected_id ?? null, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-wa-mission-menu-item" id="submit-wa-mission-menu" />
                    </span>
                  </p><?php
              
                }
            }
            /**
             * Register polylang rewrite rule for wa-mission slugs
             * 
			 * @param string|null $url    The link, null if no translation was found.
			 * @param string      $slug   The language code.
			 * @param string      $locale The language locale
             */
            public function e_mission_CPT_polylang_lang_link( $url, $slug, $locale ) {
                $permalink = _x( 'missions', 'wa-mission post slug (url SEO)'
                , 'monwoo-web-agency-config'/** 📜*/);
                $translated = Utils\_x( 'missions', 'wa-mission post slug (url SEO)'
                , 'monwoo-web-agency-config'/** 📜*/, $locale);
                $this->debugVerbose("Will e_mission_CPT_polylang_lang_link from '$permalink' to [$locale] '$translated' for $url", );
                $newUrl = str_replace( "/$permalink/", "/$translated/", $url );
                if ($newUrl) return $newUrl;
                return $url; 
            }            
            /**
             * Register polylang rewrite rule for wa-mission slugs (Dev in progres...)
             */
            public function e_mission_CPT_polylang_rewrite_slugs( $post_type_translated_slugs ) {
                $this->debugVerbose("Will e_mission_CPT_polylang_rewrite_slugs");
                $pType = "wa-mission";
                $locales = [get_locale()];
                if (function_exists('pll_languages_list')) {
                    $locales = pll_languages_list();
                }
                $rules = [];
                foreach ($locales as $idx => $localeMetas) {
                    $localeSlug = $localeMetas['slug'];
                    $locale = $localeMetas['locale'];
                    $permalink = Utils\_x( 'missions',
                    'wa-mission post slug (url SEO)'
                    , 'monwoo-web-agency-config'/** 📜*/, $locale);
                    $rules[$localeSlug] = [
                        'has_archive' => true,
                        'rewrite'             => [
                            'slug'       => $permalink,
                            'with_front' => false,
                            'feeds'      => true,
                        ],
                        'slug' => $permalink,
                    ];
                }
                $post_type_translated_slugs[$pType] = $rules;
                $this->debugVeryVerbose("Did e_mission_CPT_polylang_rewrite_slugs for Polylangs : ", $post_type_translated_slugs);
                return $post_type_translated_slugs;
            }
            /**
             * Register polylang localized rewrite rule for wa-mission slugs Polylang 404 page fixes
             * 
             * Not Used yet, under dev...
             */
            protected function e_mission_register_localized_slug() {
                $this->debugVerbose("Will e_mission_register_localized_slug");
                $permalink = _x( 'missions', 'wa-mission post slug (url SEO)'
                , 'monwoo-web-agency-config'/** 📜*/);
                $this->warn("Will e_mission_register_localized_slug $permalink");
                register_post_type( 
                    'wa-mission', 
                    array (
                        'rewrite' => array (
                            'slug' => $permalink,
                            'with_front' => true,
                            'walk_dirs' => false ,                                               
                        ),
                        'slug' => $permalink,
                    )
                );
                $this->debugVerbose("Mission routes after localisation : "
                . $this->debug_routes());
            }
            /**
             * Register wa-mission post type with polylang plugin
             */
            public function e_mission_CPT_polylang_register( $post_types, $is_settings ) {
                $this->debugVerbose("Will e_mission_CPT_polylang_register $is_settings");
                $missionCptKey = 'wa-mission';
                if ( $is_settings ) {
                    unset( $post_types[$missionCptKey] );
                } else {
                    $post_types[$missionCptKey] = $missionCptKey;
                }
                return $post_types;
            }
            /**
             * Register wa-mission admin menu.
             */
            public function e_mission_CPT_admin_menu(): void
            {
                $this->debugVerbose("Will e_mission_CPT_admin_menu"); 
                $missionCptKey = 'wa-mission';
                $self = $this;
                if (!function_exists(\add_submenu_page::class)){
                    $this->warn("MISSING add_submenu_page function, 'e_mission_CPT_admin_menu' should be registred with 'admin_menu' hook.");
                }
                if (is_admin()
                && function_exists(\add_submenu_page::class) 
                ) {
                    $missionCpt = get_post_type_object($missionCptKey);
                    if (!$missionCpt) {
                        $this->err("Custom post type '$missionCptKey' is not defined.");
                    }
                    if (!($missionCpt->menu_icon ?? false)
                    || !strlen($missionCpt->menu_icon )) {
                        $this->err("Missing Custom post type 'menu_icon'.", $missionCpt);
                    }
                    add_action(
                        'add_meta_boxes_' . $missionCptKey,
                        [$this, 'e_mission_CPT_end_date_add_metabox'],
                    );
                    add_action('quick_edit_custom_box',  [$this, 'e_mission_CPT_end_date_quick_edit'], 10, 2);
                    add_action('admin_print_footer_scripts-edit.php', [$this, 'e_mission_CPT_end_date_quick_edit_js']);
                    \add_submenu_page(
                        $this->eConfigPageKey,
                        $missionCpt->labels->name,            
                        "<span class='dashicons {$missionCpt->menu_icon}'></span> "
                        . $missionCpt->labels->menu_name,       
                        $missionCpt->cap->edit_posts,         
                        'edit.php?post_type=' . $missionCptKey,       
                        '',  
                        $this->e_config_count_submenu()
                    );
                    /**
                     * Fix Parent Admin Menu Item
                     */
                    $my_cpt_parent_file = function ( $parent_file )
                    use ($self, $missionCptKey) {
                        global $current_screen;
                        /**
                         * Add upload.php as parent file/menu if
                         * it's Post Type list Screen or Edit screen of our post type.
                         */
                        if ( in_array( $current_screen->base, array( 'term', 'post-tags', 'edit-tags', 'post', 'edit' ) )
                        && $missionCptKey == $current_screen->post_type ) {
                            $parent_file = $self->eConfigPageKey; 
                        }
                        $self->debugVerbose("e_mission_CPT_admin_menu parent_file : $parent_file "
                        . "for $current_screen->base");
                        return $parent_file;
                    };
                    add_filter( 'parent_file',  $my_cpt_parent_file);                
                    /**
                     * Fix Sub Menu Item Highlights
                     */
                    $my_cpt_submenu_file = function ( $submenu_file ) use ($self, $missionCptKey){                
                        global $current_screen;
                        if ( in_array( $current_screen->base, array( 'post-tags', 'edit-tags',  'post', 'edit' ) )
                        && $missionCptKey == $current_screen->post_type ) {
                            $self->debugVeryVerbose("POST TYPE : ", $current_screen);
                            if (strlen($current_screen->taxonomy ?? "")) {
                                $submenu_file = "edit-tags.php?post_type=$missionCptKey"
                                . "&taxonomy={$current_screen->taxonomy}";
                            } else {
                                $submenu_file = "edit.php?post_type=$missionCptKey";
                            }
                            $self->debug("Sub menu file : ", $submenu_file);
                        }
                        $self->debugVerbose("e_mission_CPT_admin_menu submenu_file : $submenu_file for {$current_screen->base}");
                        return $submenu_file;
                    };
                    add_filter( 'submenu_file', $my_cpt_submenu_file );
                }
            }
            /**
             * Register wa-mission custom Post type.
             */
            public function e_mission_CPT_register(): void
            {
                $self = $this;
                $skillTaxoKey = 'wa-skill';
                $missionCptKey = 'wa-mission';
                $locale = get_locale();
                $permalink = _x( 'missions', 'wa-mission post slug (url SEO)', 'monwoo-web-agency-config'/** 📜*/);
                $this->debugVerbose("Will e_mission_CPT_register '$missionCptKey' [$locale] $permalink"); 
                $missionCpt = register_post_type(
                    $missionCptKey,
                    [
                        'label' => __('Missions', 'monwoo-web-agency-config'/** 📜*/),
                        'labels' => [
                            'name' => __('Missions', 'monwoo-web-agency-config'/** 📜*/),
                            'singular_name' => __('Mission', 'monwoo-web-agency-config'/** 📜*/),
                            'all_items' => __('Les missions', 'monwoo-web-agency-config'/** 📜*/),
                            'add_new_item' => __('Ajouter une mission', 'monwoo-web-agency-config'/** 📜*/),
                            'edit_item' => __('Éditer la mission', 'monwoo-web-agency-config'/** 📜*/),
                            'new_item' => __('Nouvelle mission', 'monwoo-web-agency-config'/** 📜*/),
                            'view_item' => __('Voir la mission', 'monwoo-web-agency-config'/** 📜*/),
                            'search_items' => __('Rechercher parmi les missions', 'monwoo-web-agency-config'/** 📜*/),
                            'not_found' => __('Pas de mission trouvée', 'monwoo-web-agency-config'/** 📜*/),
                            'not_found_in_trash'=> __('Pas de mission dans la corbeille', 'monwoo-web-agency-config'/** 📜*/),
                            'menu_name' => __('Missions', 'monwoo-web-agency-config'/** 📜*/), 
                        ],
                        'public' => true,
                        'delete_with_user' => false, 
                        'supports' => [
                            'title',
                            'editor',
                            'excerpt',
                            'author',
                            'thumbnail',
                            'comments',
                            'custom-fields',
                            'headway-seo',
                            'date',
                            'sticky',
                            'views',
                            'revisions',
                            'trackbacks',
                            'page-attributes',
                            'post-formats',
                        ],
                        'can_export'          => true,
                        'has_archive'         => true,
                        'exclude_from_search' => false,
                        'publicly_queryable'  => true,
                        'query_var' => true,
                        'show_admin_column' => true,
                        'show_in_rest'      => true,
                        'show_ui'           => true,
                        'show_in_admin_bar'   => true,                
                        'show_in_menu'      => false, 
                        'menu_icon'           => 'dashicons-clipboard',
                        'taxonomies'        => [
                            $skillTaxoKey,
                            'category', 
                        ],
                        'show_in_nav_menus'     	=> true,
                        'map_meta_cap'        => true, 
                        'hierarchical'        => false, 
                        'rewrite'             => [
                            'slug'       => $permalink,
                        ],
                        'slug' => $permalink,
                    ]
                );
                $missionCpt = get_post_type_object($missionCptKey);
                if (!($missionCpt->menu_icon ?? false)
                || !strlen($missionCpt->menu_icon )) {
                    $this->err("Register CPT : Missing 'menu_icon'.", $missionCpt);
                } else {
                    $this->debugVeryVerbose("Registered CPT : ", $missionCpt);
                }
                if ( function_exists( 'pll_count_posts' ) ) {
                    $this->t_ensure_route_sync(true); 
                }
                register_rest_field(
                    $missionCptKey,
                    'wa_end_date',
                    array(
                        'get_callback' => [ $this, 'e_mission_CPT_get_meta_from_rest' ],
                        'schema'       => null,
                    )
                );
            }
            /**
             * Get Site URL
             *
             * @param  string $object     Rest Object.
             * @param  string $field_name Rest Field.
             * @param  array  $request    Rest Request.
             * @return string             Post Meta.
             */
            public function e_mission_CPT_get_meta_from_rest( $object = '', $field_name = '', $request = array() ) {
                $this->debug("Will e_mission_CPT_get_meta_from_rest");
                $value = get_post_meta( $object['id'], $field_name, true );
                if ('wa-mission' === $object['type']
                && 'wa_end_date' === $field_name) {
                    $value = date_i18n("c", strtotime($value));
                }
                return $value;
            }
            /**
             * Overwsite post date to show mission date range instead.
             * 
             */
            public function e_mission_CPT_get_the_date( $the_date, $d, $post)
            {
                $this->debugVerbose("Will e_mission_CPT_get_the_date");
                $missionCptKey = "wa-mission";
                if ( is_int( $post) ) {
                    $post = get_post($post);
                }
                $post_id = $post->ID;
                if ($post->post_type === $missionCptKey) {
                    if (!strlen($d)) {
                        $d = get_option( 'date_format' );
                    }
                    $res = $val = get_post_meta($post_id,'wa_end_date',true);
                    if ($val) {
                        $time = wp_date( $d, strtotime($val) );
                        $res = "[ $the_date .. " . $time . " ]";
                    }
                    if (!$res) {
                        $res = $the_date;
                    }
                    $this->debug("Will e_mission_CPT_get_the_date formated with '$d'");
                    return $res;
                }
                return $the_date;
            }
            /**
             * Add wa-mission end-date meta box
             * 
             * @param $post wa-mission with end-date meta
             */
            public function e_mission_CPT_end_date_add_metabox($post): void
            {
                $post_type = get_post_type( $post );
                add_meta_box(
                    'end-date',
                    __('Définir la date de fin', 'monwoo-web-agency-config'/** 📜*/),
                    [$this, 'e_mission_CPT_end_date_render_metabox'],
                    $post_type,
                    'side', 'high'
                );
            }
            /**
             * Save the meta box for end-date meta.
             * 
             * @param $post_ID
             */
            public function e_mission_CPT_end_date_save_metabox($post_ID): void
            {
                $this->debug("Will e_mission_CPT_end_date_save_metabox for post $post_ID");
                if(isset($_POST['wa_mission_end_date'])){
                    $endDate = filter_var(sanitize_text_field($_POST['wa_mission_end_date'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                    $this->debug("Will e_mission_CPT_end_date_save_metabox with end date at $endDate");
                    update_post_meta($post_ID,'wa_end_date', $endDate);
                    wp_reset_postdata(); 
                }
            }
            /**
             * Render the meta box for end-date meta.
             * 
             * @param $post
             */
            public function e_mission_CPT_end_date_render_metabox($post): void
            {
                $this->debugVerbose("Will e_mission_CPT_end_date_render_metabox");
                $val = ($post->ID ?? false) 
                ? get_post_meta($post->ID,'wa_end_date',true)
                : false;
                echo '<label>' . __('Date de fin', 'monwoo-web-agency-config'/** 📜*/) . ' : </label>';
                echo '<input type="date" name="wa_mission_end_date"'
                . ($val ? " value='" . esc_attr($val) . "'" : "")
                . ' class="wa-mission-end-date" />';
            }
            /**
             * Add the end-date column from end-date meta of wa-mission posts.
             * 
             * @param $columns
             */
            public function e_mission_CPT_end_date_add_column($columns) {
                $columns['wa-end-date'] = __('Date de fin', 'monwoo-web-agency-config'/** 📜*/);
                return $columns;
            }
            /**
             * Render the column row data for end-date meta of wa-mission posts.
             * 
             * @param $columns
             */
            public function e_mission_CPT_end_date_render_column_row($column, $postId) {
                if ('wa-end-date' === $column) {
                    $this->debugVerbose("Will e_mission_CPT_end_date_render_column_row $column");
                    $endDate = get_post_meta($postId, 'wa_end_date', true);
                    $fmt = "Y-m-d"; 
                    echo wp_kses_data($endDate ? date($fmt, strtotime($endDate)) : ""); 
                }
            }
            /**
             * Allow quick edit for end-date meta of wa-mission posts.
             * 
             * @param $columns
             */
            public function e_mission_CPT_end_date_quick_edit($column, $postType) {
                if ('wa-end-date' === $column) {
                    $this->e_mission_CPT_end_date_render_metabox(null);
                }
            }
            /**
             * JS to allow quick edit value sync for end-date meta of wa-mission posts.
             * 
             */
            public function e_mission_CPT_end_date_quick_edit_js() {
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
        /**
         * This trait will add the wa-skill taxonomy
         * 
         * You could use it frontend side with the REST API or this kind of plugin :
         * 
         * {@link https://fr.wordpress.org/plugins/custom-post-type-widget-blocks/
         * Custom Post Type Widget Blocks by thingsym }
         * 
         * {@see https://developer.wordpress.org/rest-api/reference/categories/ 
         * REST API Handbook for Category (wa-skill equivalent)}
         * 
         * ```js
         * // JS ES6 usage example to fecth skill taxonomies :
         * // You can copy/past below code in your Chrome console to test it :
         * // (A Console you should open by inspecting the same domain name to
         * // avoid fetch errors)
         * async function getSkills() {
         *     const WpV2ApiBaseUrl = "https://web-agency.local.dev/e-commerce/wp-json/wp/v2";
         *     let response = await fetch(`${WpV2ApiBaseUrl}/wa-skill`, {
         *         method: 'GET',
         *         mode: 'no-cors',
         *         withCredentials: false,
         *         headers: {
         *             'Content-type': 'application/json; charset=UTF-8'
         *         },
         *     });
         *     let skills = await response.json();
         *     return skills;
         * }
         * getSkills().then(data => console.log(data) && data);
         * ```
         * 
         * ```bash
         * # curl example to fecth skill taxonomies :
         * WP_V2API_BASEURL="https://web-agency.local.dev/e-commerce/wp-json/wp/v2"
         * curl "$WP_V2API_BASEURL/wa-skill"
         * ```
         *
         * 
         * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-content-types/
         * @see https://developer.wordpress.org/rest-api/reference/
         * @see https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         */
        trait EditableSkillsTaxo
        {
            use Editable;
            protected function _011_e_skill_taxo__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_011_e_skill_taxo__bootsrap')) {
                    return; 
                }
                add_filter( 'the_category_list', [$this, 'e_skill_taxo_filter_the_category_list'], 10, 2);
            }
            protected function _011_e_skill_taxo__load()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_011_e_skill_taxo__load')) {
                    return; 
                }
                add_action( 'init', [$this, 'e_skill_taxo_register_taxonomy'], 8);
                add_action( 'admin_menu', [$this, 'e_skill_taxo_admin_menu'], 3);
                add_action(
                    WPActions::wa_do_base_review_preprocessing,
                    [$this, 'e_skill_taxo_data_review']
                );
                if ( function_exists( 'pll_count_posts' ) ) {
                    add_filter( 'pll_get_taxonomies', [$this, 'e_skill_taxo_filter_pll_taxonomies'], 1, 2 );
                    add_filter( 'pll_the_language_link', [$this, 'e_skill_taxo_polylang_lang_link'], 10, 3 );
                } else {
                    $this->debugVerbose("Polylang not detected. Avoiding 'pll_get_taxonomies' filter to 'e_skill_taxo_filter_pll_taxonomies'.");
                }
            }
            /**
             * Filters the categories to add skills to category before building the category list.
             *
             * @param WP_Term[] $categories An array of the post's categories.
             * @param int|bool  $post_id    ID of the post we're retrieving categories for.
             *                              When `false`, we assume the current post in the loop.
             * @return WP_Term[] $categories An array of the post's categories.
             */
            public function e_skill_taxo_filter_the_category_list( $categories, $post_id ) {
                $post_id = false === $post_id ? get_the_ID() : $post_id;
                $this->debugVerbose("Will e_skill_taxo_filter_the_category_list for $post_id.");
                $taxoKey = "wa-skill";
                $skills = wp_get_object_terms($post_id, $taxoKey); 
                if (function_exists('pll_get_term')) {
                    $locale = get_locale();
                    $skills = array_filter($skills, function($s) use ($locale) {
                        $termLocale = pll_get_term_language($s->term_id, 'locale');
                        $this->debugVerbose("Filter skill terme {$s->term_id} [$termLocale] for $locale.");
                        return $termLocale === $locale;
                    });
                }                    
                if ( is_wp_error( $skills ) ) {
                    /** @var WP_Error $err  */
                    $err = $skills;
                    $this->err("Fail to fetch terms $taxoKey for post ID : $post_id" . $err->get_error_message(), $err);
                    $skills = [];
                }
                $categories = array_merge($categories, $skills);
                $this->debugVeryVerbose("e_skill_taxo_filter_the_category_list TO : ", $categories);
                return $categories;
            }   
            /**
             * Register wa-skill taxonomy with polylang plugin
             * 
			 * Filters the list of taxonomies available for translation.
			 * The default are taxonomies which have the parameter ‘public’ set to true.
			 * The filter must be added soon in the WordPress loading process:
			 * in a function hooked to ‘plugins_loaded’ or directly in functions.php for themes.
			 *
			 * @param string[] $taxonomies  List of taxonomy names.
			 * @param bool     $is_settings True when displaying the list of custom taxonomies in Polylang settings.
			 * @return string[] Filtered list of taxonomy names.
             */
            public function e_skill_taxo_filter_pll_taxonomies( $taxonomies, $is_settings ) {
                $skillTaxoKey = 'wa-skill';
                if ( $is_settings ) {
                    unset( $taxonomies[$skillTaxoKey] );
                } else {
                    $taxonomies[$skillTaxoKey] = $skillTaxoKey;
                }
                return $taxonomies;
            }
            /**
             * Register polylang rewrite rule for wa-mission slugs
             * 
			 * @param string|null $url    The link, null if no translation was found.
			 * @param string      $slug   The language code.
			 * @param string      $locale The language locale
             */
            public function e_skill_taxo_polylang_lang_link( $url, $slug, $locale ) {
                $permalink = _x( 'expertises', 'wa-skill taxonomy slug (url SEO)',
                'monwoo-web-agency-config'/** 📜*/);
                $translated = Utils\_x( 'expertises', 'wa-skill taxonomy slug (url SEO)',
                'monwoo-web-agency-config'/** 📜*/, $locale);
                $this->debugVerbose("Will e_skill_taxo_polylang_lang_link from '$permalink' to [$locale] '$translated' for $url", );
                $newUrl = str_replace( "/$permalink/", "/$translated/", $url );
                if ($newUrl) return $newUrl;
                return $url; 
            }
            /**
             * Register wa-skill admin menu as sub page of Wa-config.
             */
            public function e_skill_taxo_admin_menu(): void
            {
                $this->debugVerbose("Will e_skill_taxo_admin_menu");
                $missionCptKey = 'wa-mission';
                $skillTaxoKey = 'wa-skill';
                $taxo = get_taxonomy($skillTaxoKey);
                $this->debugVerbose("Will e_skill_taxo_admin_menu.");
                if (!function_exists(\add_submenu_page::class)){
                    $this->warn("MISSING add_submenu_page function, 'e_skill_taxo_admin_menu' should be registred with 'admin_menu' hook.");
                }
                if (is_admin()
                && function_exists(\add_submenu_page::class) 
                ) {
                    \add_submenu_page(
                        $this->eConfigPageKey,
                        $taxo->labels->name,
                        "<span class='dashicons {$taxo->menu_icon}'></span> "
                        . $taxo->labels->name,
                        $taxo->cap->manage_terms,
                        "edit-tags.php?post_type=$missionCptKey&taxonomy=$skillTaxoKey",
                        "",
                        $this->e_config_count_submenu(),
                    );
                }
            }
            /**
             * Register the wa-skill taxonomy used for Mission post and User post
             */
            public function e_skill_taxo_register_taxonomy(): void
            {
                $this->debugVerbose("Will e_skill_taxo_register_taxonomy");
                $missionCptKey = 'wa-mission';
                $skillTaxoKey = 'wa-skill';
                $locale = get_locale();
                $permalink = _x( 'expertises', 'wa-skill taxonomy slug (url SEO)', 'monwoo-web-agency-config'/** 📜*/);
                $this->debugVerbose("Will e_skill_taxo_register_taxonomy '$skillTaxoKey' [$locale] $permalink");
                $labels = array(
                    'name'              => _x( 'Expertises', 'taxonomy general name (plural)', 'monwoo-web-agency-config'/** 📜*/),
                    'singular_name'     => _x( 'Expertise', 'taxonomy singular name', 'monwoo-web-agency-config'/** 📜*/),
                    'search_items'      => __( "Recherche d'expertises", 'monwoo-web-agency-config'/** 📜*/),
                    'all_items'         => __( 'Toutes les expertises', 'monwoo-web-agency-config'/** 📜*/),
                    'parent_item'       => __( 'Expertise parente', 'monwoo-web-agency-config'/** 📜*/),
                    'parent_item_colon' => __( 'Expertise parente:', 'monwoo-web-agency-config'/** 📜*/),
                    'edit_item'         => __( "Editer l'expertise", 'monwoo-web-agency-config'/** 📜*/),
                    'update_item'       => __( "Mettre à jour l'expertise", 'monwoo-web-agency-config'/** 📜*/),
                    'add_new_item'      => __( 'Ajouter une nouvelle expertise', 'monwoo-web-agency-config'/** 📜*/),
                    'new_item_name'     => __( 'Nom de la nouvelle expertise', 'monwoo-web-agency-config'/** 📜*/),
                    'menu_name'         => _x( 'Expertise', 'taxonomy menu name', 'monwoo-web-agency-config'/** 📜*/),
                );
                $args   = array(
                    'public'            => true,
                    'hierarchical'      => true, 
                    'labels'            => $labels,
                    'show_ui'           => true,
                    'show_in_rest'      => true, 
                    'show_admin_column' => true,
                    'can_export'          => true,
                    'has_archive'         => true,
                    'exclude_from_search' => false,
                    'publicly_queryable'  => true,
                    'query_var'         => true,
                    'rewrite'           => [ 'slug' => $permalink ], 
                    'show_in_nav_menus' => true,
                    'show_tagcloud' => true,
                    'menu_icon' => 'dashicons-welcome-learn-more',
                    'show_in_menu'      => false, 
                );
                $taxo = register_taxonomy($skillTaxoKey, [ $missionCptKey, 'user' ], $args );
            }
            /**
             * Review the default base terms used for wa-skill taxonomy
             * 
             * @param AppInterface $app the plugin instance.
             */
            public function e_skill_taxo_data_review($app): void
            {
                $this->debugVerbose("Will e_skill_taxo_data_review");
                $skillsSyncOK = true;
                $reviewReport = '';
                $taxoKey = 'wa-skill';
                $locales = [get_locale()];
                $slugByLocale = [
                    'en_US' => 'en',
                    'fr_FR' => 'fr',
                    'es_ES' => 'es',
                ];
                if (function_exists('pll_languages_list')) {
                    $slugByLocale = [];
                    $rawLocales = pll_languages_list(array('fields' => 'locale'));
                    $defaultLocale = pll_default_language('locale');
                    $locales = array_diff( $rawLocales, [ $defaultLocale ] );
                    array_unshift($locales, $defaultLocale);
                    $slugs = pll_languages_list(array('fields' => 'slug'));
                    foreach ($locales as $order => $locale) {
                        $idx = array_search($locale, $rawLocales);
                        $slugByLocale[$locale] = $slugs[$idx];
                    }
                }
                $termByEnsureIdByLocale = [];
                foreach ($locales as $locale) {
                        $ensureDataset = [
                        'frontend' => [
                            Utils\_x( 'Frontend' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise web frontend (UI, pages statiques, composant statiques)", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'frontend' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            ],
                        ],
                        'svelte' => [
                            Utils\_x( 'Svelte' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise Svelte", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'svelte' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'frontend',
                            ],
                        ],
                        'angular-js' => [
                            Utils\_x( 'Angular JS' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise Angular JS", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'angular-js' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'frontend',
                            ],
                        ],
                        'angular-2-etc' => [
                            Utils\_x( 'Angular 2+' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise Angular 2+", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'angular-2-etc' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'frontend',
                            ],
                        ],
                        'vue-js' => [
                            Utils\_x( 'Vue JS' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise Vue JS", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'vue-js' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'frontend',
                            ],
                        ],
                        'react-js' => [
                            Utils\_x( 'React JS' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise React JS", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'react-js' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'frontend',
                            ],
                        ],
                        'wordpress-frontend' => [
                            Utils\_x( 'WordPress Theme' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise en frontend WordPress", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'wordpress-frontend' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'frontend',
                            ],
                        ],
                        'backend' => [
                            Utils\_x( 'Backend' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise web backend (SEO, pages dynamiques, données dynamiques)", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'backend' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            ],
                        ],
                        'wordpress-plugin' => [
                            Utils\_x( 'WordPress Plugin' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise en backend WordPress", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x( 'wordpress-plugin' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'backend',
                            ],
                        ],
                        'symfony' => [
                            Utils\_x( 'Symfony' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise Symfony", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('symfony' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'backend',
                            ],
                        ],
                        'laravel' => [
                            Utils\_x( 'Laravel' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise Laravel", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('laravel' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'backend',
                            ],
                        ],
                        'r-et-d' => [
                            Utils\_x( 'Recherche et développement' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise de recherche et développement (R&D)", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('r-et-d' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            ],
                        ],
                        'p-o-c' => [
                            Utils\_x( 'POC' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Proof of concept (Preuve de conception)", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('p-o-c' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'r-et-d',
                            ],
                        ],
                        'etude-technique' => [
                            Utils\_x( 'Etude technique' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Présentation des résultats de veille technologique plus ou moins longues", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('etude-technique' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'r-et-d',
                            ],
                        ],
                        'publication-open-source' => [
                            Utils\_x( 'Publication Open Source' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Publication des résultats et outils de mise en oeuvre pour un domaine public ciblée", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('publication-open-source' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'r-et-d',
                            ],
                        ],
                        'health-care' => [
                            Utils\_x( 'Bien être' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise en bien-être", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            ],
                        ],
                        'physiological-health-care' => [
                            Utils\_x( 'Bien être physiologique' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise en bien être de l'activité de l'organisme humain", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('physiological-health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'health-care',
                            ],
                        ],
                        'relationship-health-care' => [
                            Utils\_x( 'Bien être relationnel' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise en bien être relationnel. Team building etc...", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('relationship-health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'health-care',
                            ],
                        ],
                        'organisational-health-care' => [
                            Utils\_x( 'Bien être organisationnel' , 'wa-skill term title', 'monwoo-web-agency-config'/** 📜*/, $locale),
                            [
                                'description' => Utils\__("Expertise organisationnel pour se sentir bien ou améliorer un bien être relationnel ou physiologique", 'monwoo-web-agency-config'/** 📜*/, $locale),
                                'slug'        => Utils\_x('organisational-health-care' , 'wa-skill term slug', 'monwoo-web-agency-config'/** 📜*/, $locale),
                                '_parentEnsureID' => 'health-care',
                            ],
                        ],
                    ];
                    /**
                     * @see WPFilters::wa_base_review_skill_terms_to_ensure
                     */
                    $ensureDataset = apply_filters(
                        WPFilters::wa_base_review_skill_terms_to_ensure,
                        $ensureDataset,
                        $locale,
                        $slugByLocale[$locale] ?? null,
                    );
                    foreach ($ensureDataset as $ensureId => $ensureSkill) {
                        $title = $ensureSkill[0];
                        $args = $ensureSkill[1];
                        if ($args['_parentEnsureID'] ?? false) {
                            $pID = $args['_parentEnsureID'];
                            $p = ($termByEnsureIdByLocale[$pID] ?? [])[$locale] ?? null;
                            if (!$p) {
                                $this->err("Fail to fetch _parentEnsureID '$pID' for child [$locale] '$ensureId' : ", $args);
                                $reviewReport .=
                                "<p>L'ajout du parent '$pID' pour [$locale] '$ensureId' a échoué.</p>";    
                            } else {
                                $args['parent'] = $p['term_id'];
                                unset($args['_parentEnsureID']);    
                            }
                        }
                        $skillsSyncOK = !! ($ensuredTerm = $this->e_skill_taxo_ensure_term(
                            $reviewReport, $title, $taxoKey, $args, $locale
                        )) && $skillsSyncOK;
                        if ($ensuredTerm) {
                            if (function_exists('pll_set_term_language')) {
                                $langSlug = $slugByLocale[$locale];
                                pll_set_term_language($ensuredTerm['term_id'], $langSlug);
                            }
                        } else {
                            $ensuredTerm = get_term_by('slug', $args['slug'], $taxoKey, ARRAY_A);
                            if (is_wp_error( $ensuredTerm )) {
                                /** @var WP_Error $err  */
                                $err = $ensuredTerm;
                                $this->err("Fail to get term by slug {$args['slug']} : " . $err->get_error_message(), $err);
                                $reviewReport .=
                                "<p>Slug '{$args['slug']}' non trouvé : " . $err->get_error_message() . " </p>";
                            } else {
                                $skillsSyncOK = true;
                            }
                        }
                        if ($ensuredTerm && $skillsSyncOK) {
                            if (!array_key_exists($ensureId, $termByEnsureIdByLocale)) {
                                $termByEnsureIdByLocale[$ensureId] = [];
                            }
                            $termByEnsureIdByLocale[$ensureId][$locale] = $ensuredTerm;    
                        }
                    }
                }
                $duplicateSlugSentinel = [];
                foreach($termByEnsureIdByLocale as $ensureID => $termByLocale) {
                    if (function_exists('pll_save_term_translations')) {
                        $translations = [];
                        foreach ($locales as $locale) {
                            $term = $termByLocale[$locale];
                            $termFetch = get_term($term['term_id'], $taxoKey, ARRAY_A);
                            if ( is_wp_error( $termFetch ) ) {
                                /** @var WP_Error $err  */
                                $err = $term;
                                $this->err("Fail to get term {$term['term_id']} : " . $err->get_error_message(), $err);
                                $reviewReport .=
                                "<p>L'ajout de l'expertise '{$term['term_id']}' a échoué : " . $err->get_error_message() . " </p>";
                                continue;
                            }
                            $term = $termFetch;
                            $tSlug = $term['slug'];
                            if ($duplicateSlugSentinel[$tSlug] ?? null) {
                                $app->warn("[$locale][$ensureID] Slug translation for wa-skill '{$tSlug}' is already registred. Ignoring '$locale' translations.");
                                $translations[$slugByLocale[$locale]] = null;
                                continue;
                            }
                            $duplicateSlugSentinel[$tSlug] = true;
                            $translations[$slugByLocale[$locale]] = $term['term_id'];
                            $app->debugVeryVerbose("Term data : ", $term);
                            $app->debug("[$locale][$ensureID] Slug translation as wa-skill '{$tSlug}' detected.");
                        }
                        pll_save_term_translations($translations);
                        $app->debugVeryVerbose("[$ensureID] pll translations saved : ", $translations);
                    }
                }
                $this->e_review_data_check_insert([
                    'category' => __('02 - Maintenance', 'monwoo-web-agency-config'/** 📜*/),
                    'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>',
                    'title' => __("02 - [wa-skill] Contrôle des données", 'monwoo-web-agency-config'/** 📜*/),
                    'title_icon' => '<span class="dashicons dashicons-dashboard"></span>',
                    'requirements' => __( '[wa-skill] Vérification de la présence des expertises de base.<br />',
                    'monwoo-web-agency-config'/** 📜*/ ) . $reviewReport,
                    'value' => strlen($reviewReport)
                    ? (
                        $skillsSyncOK
                        ? __( 'Les expertises sont définies avec quelques variations.', 'monwoo-web-agency-config'/** 📜*/)
                        : __( 'Supprimez les terms wa-skill basique puis rafraichir cette page.', 'monwoo-web-agency-config'/** 📜*/)
                    )
                    : '',
                    'result'   => $skillsSyncOK ,
                    'is_activated'   => true,
                    'fixed_id' => "{$this->iId}-data-review-taxo-terms-for-wa-skill",
                    'is_computed' => true,
                ]);
            }
            /**
             * Add a new term to the Skill taxonomy.
             *
             * @since 0.0.1
             *
             * @param string       $reviewReport     Output string to put the review report in.
             * @param string       $term     The term name to add.
             * @param string       $taxonomy The taxonomy to which to add the term.
             * @param array|string $args {
             *     Optional. Array or query string of arguments for inserting a term.
             *
             *     @type string $alias_of    Slug of the term to make this term an alias of.
             *                               Default empty string. Accepts a term slug.
             *     @type string $description The term description. Default empty string.
             *     @type int    $parent      The id of the parent term. Default 0.
             *     @type string $slug        The term slug to use. Default empty string.
             * }
             * @return array|WP_Error {
             *     An array of the new term data, false otherwise.
             *
             *     @type int    $term_id    The new term ID.
             *     @type WP_Term|array|false    $term_taxonomy_id   The new term taxonomy.
             * }
             */
            function e_skill_taxo_ensure_term( & $reviewReport, $term, $taxonomy, $args = array(), $locale = '') {
                $this->debug("[$locale] Will ensure existance of wa-skill term $term");
                $termInstance = get_term_by('slug', $args['slug'], $taxonomy, ARRAY_A);
                $this->debugVeryVerbose("Saved term : ", $termInstance, $term, $taxonomy);
                if ($termInstance) {
                    $haveDiffs = [];
                    $term = esc_attr($term);
                    $taxonomy = esc_attr($taxonomy);
                    $args['description'] = wp_unslash(esc_html($args['description']));
                    $args['description'] = str_replace("&#039;", "'", $args['description']);
                    if ($term !== $termInstance['name']) {
                        $haveDiffs[] = htmlentities("$term\n<>\n{$termInstance['name']}");
                    }
                    if ($taxonomy !== $termInstance['taxonomy']) {
                        $haveDiffs[] = htmlentities("$taxonomy\n<>\n{$termInstance['taxonomy']}");
                    }
                    if ($args['description'] !== $termInstance['description']) {
                        $haveDiffs[] = htmlentities("{$args['description']}\n<>\n{$termInstance['description']}");
                    }
                    if ($args['slug'] !== $termInstance['slug']) {
                        $haveDiffs[] = htmlentities("{$args['slug']}\n<>\n{$termInstance['slug']}");
                    }
                    if (count($haveDiffs)) {
                        $reviewReport .=
                        "<p> L'expertise '$term' est différente de la version du plugin : <pre style='overflow:scroll'>\n"
                        . implode(htmlentities("\n && \n"), $haveDiffs) . "\n</pre></p>";
                    }
                    $this->debug("[$locale] No need to add term $term for $taxonomy taxonomy since slug '{$args['slug']}' is already registred, return loaded one, differ : " . count($haveDiffs));
                    return false;
                } else {
                    $termInstance = wp_insert_term(
                        $term,
                        $taxonomy,
                        $args
                    );
                    if ( is_wp_error( $termInstance ) ) {
                        /** @var WP_Error $err  */
                        $err = $termInstance;
                            $this->err("Fail to add term $term for $taxonomy taxonomy : " . $err->get_error_message(), $err);
                            $reviewReport .=
                            "<p>L'ajout de l'expertise '$term' a échoué : " . $err->get_error_message() . " </p>";
                            return false;
                    }
                }
                if ( !count($termInstance)) {
                    $reviewReport .=
                    "<p> Echec de l'ajout de l'expertise '$term', réponse vide.</p>";
                    $this->err("Fail to add term $term for $taxonomy taxonomy", $termInstance);
                    return false;
                }
                return $termInstance;
            }
        }
    }
    if (!trait_exists(EditableConfigPanels::class)) { 
        /**
         * This trait load the wa-config admin panels
         * 
         * - **param** panel : the Parameter panel, sub-menu of WA Config
         * - **doc** panel : the Documentaion panel, sub-menu of WA Config
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Identifiable
         * @uses Translatable
         * @uses Editable
         * @uses EditableWaConfigOptions
         * @uses EditableAdminScripts
         * @uses Parallelizable
         * @uses PdfToHTMLable
         */
        trait EditableConfigPanels
        {
            use Identifiable,
                Translatable,
                Editable,
                EditableWaConfigOptions,
                EditableAdminScripts,
                Parallelizable,
                PdfToHTMLable;
            protected function _010_e_config__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_config__bootstrap')) {
                    return; 
                }
                $self = $this;
                $staticHeadTarget = $this->getWaConfigOption(
                    $this->eConfStaticHeadTarget,
                    ""
                );
                $staticHeadTargetSafeWpKeeper = $this->getWaConfigOption(
                    $this->eConfStaticHeadSafeWpKeeper,
                    ""
                );
                $eConfStaticHeadNarrowFilter = $this->getWaConfigOption(
                    $this->eConfStaticHeadNarrowFilter,
                    ""
                );
                $staticHeadTarget = trim($staticHeadTarget, '/');
                if (strlen($staticHeadTarget) && !is_admin()) {
                    $self = $this;
                    add_action('parse_request', function (WP $wp)
                    use ($self, $staticHeadTarget, $staticHeadTargetSafeWpKeeper, $eConfStaticHeadNarrowFilter) { 
                        $isSafeWp = false; 
                        if (strlen($eConfStaticHeadNarrowFilter)) {
                            $isSafeWp = true;
                            if (preg_match($eConfStaticHeadNarrowFilter, $wp->request)) {
                                $isSafeWp = false;
                            }
                        }
                        if (strlen($staticHeadTargetSafeWpKeeper)
                        && preg_match($staticHeadTargetSafeWpKeeper, $wp->request)) {
                            $isSafeWp = true;
                        }
                        if (0 !== strpos($wp->request, "wp-admin")
                        && 0 !== strpos($wp->request, "wp-json")
                        && 0 !== strpos($wp->request, "api-wa-config-nonce-rest")
                        && !$isSafeWp) {
                            $proxy = $this->pluginRoot . "head-proxy.php";
                            $GLOBALS["wa-proxy-url"] = $wp->request;
                            $GLOBALS["wa-front-head"] = $staticHeadTarget;
                            $this->debugVerbose("EditableConfigPannels proxify Frontend for : '{$wp->request}' at '$staticHeadTarget'");
                            include($proxy); 
                            $self->exit(); return; 
                        }
                        if ($isSafeWp) {
                            $this->debugVerbose("EditableConfigPannels did keep wp url safe for : '{$wp->request}'");
                        }
                    }, 200); 
                }
                if (!is_admin()) {
                    return; 
                } 
                add_action(WPActions::wa_ecp_render_after_parameters, function () use ($self) {
                    if (!current_user_can('administrator')) {
                        return; 
                    }
                    $waOptions = [
                        $this->eReviewDataStoreKey,
                        $this->eConfigOptsKey,
                        $this->eConfigE2ETestsOptsKey,
                    ];
                    $shouldClean = filter_var( sanitize_text_field($_GET['should-clean-all-options'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                    if ($shouldClean) {
                        $this->debug("_010_e_config__bootstrap will CLEAN all options then redirect");
                        foreach($waOptions as $optionKey) {
                            delete_option($optionKey);
                        }
                        $back_url = remove_query_arg([
                            'should-clean-all-options'
                        ]);
                        wp_redirect( $back_url ); 
                        $this->exit(); return;
                    } else {
                        $this->debug("_010_e_config__bootstrap will add CLEAN all option for 'wa_ecp_render_after_parameters' action");
                        $cleanLink = add_query_arg([
                            'should-clean-all-options' => 'yes'
                        ]);
                        $cleanLinkLabel = __('Supprimer toutes les options', 'monwoo-web-agency-config'/** 📜*/);
                        $confirmLinkLabel = __("Confirmer la remise aux paramêtre d'usine", 'monwoo-web-agency-config'/** 📜*/);
                        $confirmLinkLabel = str_replace("'", "&#039;", $confirmLinkLabel); 
                        ?>
                            <a
                            href='<?php echo esc_url($cleanLink) ?>'
                            onclick='return confirm("<?php echo wp_kses_post($confirmLinkLabel) ?>");'>
                                <?php echo wp_kses_post($cleanLinkLabel) ?>
                            </a>
                        <?php
                        
                    }
                }); 
            }
            protected function _010_e_config__load()
            {
                if (constant('WA_Config_SHOULD_SECURE_DOCUMENTATION') 
                && current_user_can($this->optAdminEditCabability)) {
                    add_action('parse_request', [$this, 'e_config_doc_parse_request'] );
                }
                if (!is_admin()) {
                    return; 
                }
                $self = $this;
                add_action('admin_menu', [$this, 'e_config_doc_admin_menu'], 5);
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_config__load')) {
                    return; 
                }
                add_action('admin_menu', [$this, 'e_config_param_and_root_admin_menu'], 1); 
                add_action('admin_init', [$this, 'e_config_param_and_root_admin_init']);
                add_action(
                    'wp_ajax_wa-list-capabilities-and-roles', 
                    [$this, 'e_config_list_capabilities_and_roles']
                );
            }
            protected $baseCabability = 'edit_posts'; 
            protected $optAdminEditCabability = 'administrator'; 
            /**
             * Initialise the WA Config Root admin menu and parameter panel
             */
            public function e_config_param_and_root_admin_menu(): void
            {
                $this->debugVerbose("Will e_config_param_and_root_admin_menu");
                add_menu_page(
                    null, 
                    __('WA Config', 'monwoo-web-agency-config'/** 📜*/), 
                    $this->baseCabability,
                    $this->eConfigPageKey,
                    '',
                    plugins_url('assets/LogoWAConfig-21x21.png', $this->pluginFile),
                    7
                ); 
                $this->e_config_add_section(
                    '<span class="dashicons dashicons-admin-generic"></span> '
                    . __('Paramètres', 'monwoo-web-agency-config'/** 📜*/),
                    [$this, 'e_config_param_render_panel'],
                    $this->eConfigParamPageKey,
                    $this->e_config_count_submenu(),
                    $this->baseCabability,
                );
            }
            /**
             * Initialise WA Config root and parameters Admin Settings
             */
            public function e_config_param_and_root_admin_init(): void
            {
                if (!is_admin()) {
                    $this->err("e_config_param_and_root_admin_init should be for admin call only");
                    return;
                }
                $self = $this;
                $this->debugVerbose("Will e_config_param_and_root_admin_init");
                register_setting(
                    $this->eConfigOptsGroupKey,
                    $this->eConfigOptsKey,
                    [ "sanitize_callback" => [$this, 'e_config_param_form_validator']]
                );
                add_settings_section(
                    $this->eConfigParamSettingsKey,
                    __('Paramètres', 'monwoo-web-agency-config'/** 📜*/),
                    '',
                    $this->eConfigParamPageKey,
                );
                $oLvls = implode(",", (
                    new ReflectionClass(OptiLvl::class)
                )->getConstants());
                $is_setting_page = function ($id) {
                    $pageId = filter_var( sanitize_text_field($_GET['page'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                    return $id === $pageId;
                };
                extract($this->e_config_form_field_templates());
                if (current_user_can($this->optAdminEditCabability)
                && $is_setting_page($this->eConfigParamPageKey)) {
                    $this->e_config_param_add_form_field(
                        $this->eConfOptEnableFooter,
                        __("Activer le bas de page", 'monwoo-web-agency-config'/** 📜*/),
                        true,
                        $checkboxTemplate
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptFooterCredit,
                        __("Copyright de bas de page", 'monwoo-web-agency-config'/** 📜*/),
                        $this->e_footer_get_localized_credit(),
                        $multilLangTemplate,
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptFooterTemplate,
                        __("Template de bas de page", 'monwoo-web-agency-config'/** 📜*/),
                        $this->e_footer_get_localized_template(),
                        $multilLangTextAreaTemplate,
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfStaticHeadTarget,
                        __("Redirect du Fronthead", 'monwoo-web-agency-config'/** 📜*/),
                        "",
                        [$this, 'api_fronthead_admin_sugestionbox_template'],
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfStaticHeadNarrowFilter,
                        __("Regex pour cibler les urls du Fronthead", 'monwoo-web-agency-config'/** 📜*/),
                        "",
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfStaticHeadSafeWpKeeper,
                        __("Regex pour exclure des url du Fronthead", 'monwoo-web-agency-config'/** 📜*/),
                        "",
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfWooCommerceOrderPrefix,
                        __("Prefix pour num de commande WooCommerce", 'monwoo-web-agency-config'/** 📜*/),
                        "",
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfShouldRenderFrontendScripts,
                        __("Scripts frontend", 'monwoo-web-agency-config'/** 📜*/),
                        true,
                        $checkboxTemplate
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptOptiLevels,
                        __("Axes d'optimisation", 'monwoo-web-agency-config'/** 📜*/)
                        . " ($oLvls)",
                        "",
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptOptiWpRequestsFilter,
                        __("RegEx pour bloquer les requêtes HTTP interne (Ex : /.*/)", 'monwoo-web-agency-config'/** 📜*/),
                        "",
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptOptiWpRequestsSafeFilter,
                        __('RegEx pour autoriser les requêtes HTTP interne (Ex : $wordpress.org$)', 'monwoo-web-agency-config'/** 📜*/),
                        $this->E_DEFAULT_OPTIMISABLE_SAFE_FILTER,
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptOptiEnableBlockedHttpNotice,
                        __("Notifier les requêttes HTTP bloquées", 'monwoo-web-agency-config'/** 📜*/),
                        false,
                        $checkboxTemplate,
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptOptiEnableBlockedReviewReport,
                        __("Activer le rapport des url bloquées", 'monwoo-web-agency-config'/** 📜*/),
                        false,
                        $checkboxTemplate,
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptATestsBaseUrl,
                        __("Url de base pour les tests d'acceptance", 'monwoo-web-agency-config'/** 📜*/),
                        site_url(),
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptATestsUsers,
                        __("Liste des utilisateurs de test", 'monwoo-web-agency-config'/** 📜*/),
                        $this->E_DEFAULT_A_TESTS_USERS_LIST,
                    );
                    $this->e_config_param_add_form_field(
                        $this->eConfOptATestsRunForCabability,
                        __("Capacité pour lancer les tests", 'monwoo-web-agency-config'/** 📜*/),
                        'administrator',
                        [$this, 'e_config_capability_suggestionbox_template'],
                    );
                }
            }
            /**
             * Validator used to validate options saved from parameter admin panel
             *
             * @param mixed $input wa-config package option to validate
             * @return mixed the new input after validator review
             */
            public function e_config_param_form_validator($input)
            {
                $input = _wp_json_sanity_check($input, 42);
                $newinput = $input;
                $this->debugVerbose("Will e_config_param_form_validator");
                $regExKey = $this->eConfOptOptiWpRequestsFilter;
                $newinput[$regExKey] = trim($input[$regExKey]);
                $regExKeySafe = $this->eConfOptOptiWpRequestsSafeFilter;
                $newinput[$regExKeySafe] = trim($input[$regExKeySafe]);
                $regExKeys = [
                    $this->eConfOptOptiWpRequestsFilter =>
                    __("'RegEx pour bloquer les requêtes HTTP interne' NON VALIDE :",
                    'monwoo-web-agency-config'/** 📜*/),
                    $this->eConfOptOptiWpRequestsSafeFilter => 
                    __("'RegEx pour autoriser les requêtes HTTP interne' NON VALIDE :",
                    'monwoo-web-agency-config'/** 📜*/),
                    $this->eConfStaticHeadNarrowFilter => 
                    __("'Regex pour cibler les urls du Fronthead' NON VALIDE :",
                    'monwoo-web-agency-config'/** 📜*/),
                    $this->eConfStaticHeadSafeWpKeeper => 
                    __("'Regex pour exclure des url du Fronthead' NON VALIDE :",
                    'monwoo-web-agency-config'/** 📜*/),
                ];
                $eConf = error_reporting(E_ALL); 
                foreach ($regExKeys as $regExKey => $errMsg) {
                    $newinput[$regExKey] = trim($input[$regExKey]);
                    ob_start();
                    $pMatch = preg_match($newinput[$regExKey], 'hello');
                    $noticeErr = ob_get_clean();
                    if (strlen($newinput[$regExKey])
                    && false === $pMatch) {
                        Notice::displayError(""
                        . $errMsg . "<br />\n{$newinput[$regExKey]}<br />\n" . $noticeErr);
                        $newinput[$regExKey] = '';
                    }    
                }
                error_reporting($eConf);
                $booleanAdaptor = function($fieldName) use ( & $newinput ) {
                    $newinput[$fieldName] = boolval( 
                        array_key_exists($fieldName, $newinput) ? $newinput[$fieldName] : false
                    );
                };
                $booleanAdaptor($this->eConfOptEnableFooter);
                $booleanAdaptor($this->eConfOptOptiEnableBlockedHttpNotice);
                $booleanAdaptor($this->eConfOptOptiEnableBlockedReviewReport);
                if (!$newinput[$this->eConfOptOptiEnableBlockedReviewReport]) {
                    delete_transient($this->BLOCKED_URL_REVIEW_REPORT);
                    delete_transient($this->ALLOWED_URL_REVIEW_REPORT);
                }
                $booleanAdaptor($this->eConfShouldRenderFrontendScripts);
                Notice::displaySuccess(__('Enregistrement OK.', 'monwoo-web-agency-config'/** 📜*/));
                wp_cache_delete("alloptions", "options"); 
                return $newinput;
            }
            protected function e_config_param_add_form_field($key, $title, $default = '', $template = null): void
            {
                $this->debugVeryVerbose("Will e_config_param_add_form_field");
                $fieldId = "{$this->eConfigOptsKey}_$key";
                $fieldName = "{$this->eConfigOptsKey}[$key]";
                $value = $this->getWaConfigOption($key, $default);
                $safeValue = $value;
                add_settings_field(
                    $fieldId,
                    $title,
                    function () use ( & $safeValue, & $fieldId, & $fieldName, & $template) {
                        if ($template) {
                            wa_render($template($safeValue, $fieldId, $fieldName));
                        } else {
                            ?>
                                <input id='<?php echo esc_attr($fieldId) ?>' type='text'
                                name='<?php echo esc_attr($fieldName) ?>'
                                value='<?php echo wp_kses_post($safeValue) ?>'
                                />
                            <?php
                            
                        }
                    },
                    $this->eConfigParamPageKey,
                    $this->eConfigParamSettingsKey,
                );
            }
            /**
             * Render the 'WA Config' 'Parameters' panel.
             * 
             * @see   WPActions::wa_ecp_render_after_parameters
             */
            public function e_config_param_render_panel(): void
            {
                $self = $this;
                if (!is_admin()) {
                    $this->err("wa-config admin param section is under admin pages only");
                    echo "<p> "
                        . __(
                            "Cette opération nécessite une page d'administration.",
                            'monwoo-web-agency-config'/** 📜*/
                        )
                        . "</p>";
                    return;
                }
                $pluginTitle = __("Web Agency Config ", 'monwoo-web-agency-config'/** 📜*/) . $this->iId;
                $pluginDescription = __(
                    "Ce plugin permet d'<strong>optimiser</strong> la <strong>qualité</strong> de votre site web ainsi que les <strong>actions</strong> à mener pour votre <strong>processus métier</strong>.",
                    'monwoo-web-agency-config'/** 📜*/
                );
                ?>
                    <h1><?php echo wp_kses_post($pluginTitle) ?></h1>
                    <section><?php echo wp_kses_post($pluginDescription) ?></section>
                <?php
                
                if (!current_user_can($this->optAdminEditCabability)) {
                    $this->err("wa-config admin param need '{$this->optAdminEditCabability}' capability");
                    echo "<p> " . __(
                        "Pour plus d'informations, nécessite une capacité ou un rôle :",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . " " . esc_attr($this->optAdminEditCabability) . " </p>";
                    return;
                }
                $title = __('Paramètres', 'monwoo-web-agency-config'/** 📜*/);
                $welcome = __(
                    'Ici, vous pouvez configurer tous les réglages généraux de wa-config ',
                    'monwoo-web-agency-config'/** 📜*/
                ) . " (" . AppInterface::PLUGIN_VERSION . ")";
                $formFields = function () use ($self) {
                    settings_fields($self->eConfigOptsGroupKey);
                }; 
                $sectionFormFields = function () use ($self) {
                    do_settings_sections($self->eConfigParamPageKey);
                };
                $submitBtn = function () {
                    submit_button();
                };
                $compatibilityReports = [];
                $compatibilityReportsData = AppInterface::getCompatibilityReports();
                foreach ($compatibilityReportsData as $report) {
                    $compatibilityReports[] = function() use ( & $report) { ?>
                        <p style="color:red">
                            <strong><?php echo wp_kses_post($report['level']) ?></strong>
                            <?php echo wp_kses_post($report['msg']) ?>
                        </p>
                    <?php };
                }
                $UIDoc = __('wa-config.admin.panel.param.doc', 'monwoo-web-agency-config'/** 📜*/);
                ?>
                    <div class="wrap">
                        <h1><?php echo wp_kses_post($title) ?></h1>
                        <p><?php echo wp_kses_post($welcome) ?></p>
                        <?php wa_render($compatibilityReports, $this) ?>
                        <div><?php echo wp_kses_post($UIDoc) ?></div>
                        <form method="post" action="options.php"> 
                            <?php $formFields() ?>
                            <?php $sectionFormFields() ?>
                            <?php $submitBtn() ?>
                        </form>
                    </div>
                <?php
                
                $app = $this;
                /**
                 * @see   WPActions::wa_ecp_render_after_parameters
                 */                
                do_action(WPActions::wa_ecp_render_after_parameters, $app);
            }
            /**
             * 
             * Allowing doc access for authenticated users
             * 
    		 * @param WP $wp Current WordPress environment instance (passed by reference).
             * 
             * @since 0.0.2
             * 
             */
            public function e_config_doc_parse_request(WP $wp): void
            {
                $siteUrl = rtrim(site_url(), '/') . '/';
                $docRootUrl = plugins_url('doc', $this->pluginFile);
                $docRootPath = str_replace($siteUrl, "", $docRootUrl);
                $docRelativePath = str_replace($docRootPath, "", $wp->request);
                $localDocRootPath = $this->pluginRoot . "_doc";
                $localDocPath = $localDocRootPath . $docRelativePath;
                if ( @ file_exists("$localDocPath/index.html")) {
                    $docRelativePath .= "/index.html";
                    $localDocPath = $localDocRootPath . $docRelativePath;
                }
                if (0 === strpos($wp->request, $docRootPath) && strlen($docRelativePath)) {
                    $this->debug("Will e_config_doc_parse_request");
                    if (!current_user_can($this->baseCabability)) {
                        $this->err("wa-config Doc Display can be done by {$this->baseCabability} only.");
                        return;
                    }
                    if (0 !== strpos(
                        realpath($localDocPath),
                        realpath($localDocRootPath)
                    )) {
                        $this->err("Doc path is OUTSIDE of doc folder or do not exist");
                        return;
                    }
                    $file_parts = pathinfo($localDocPath);
                    switch($file_parts['extension'] ?? NULL)
                    {
                        case "html": {
                            header("content-type: text/html;charset=UTF-8");
                        } break;
                        case "js": {
                            header("content-type: text/javascript");
                        } break;
                        case "css": {
                            header("content-type: text/css");
                        } break;
                        case "": 
                        case NULL: 
                        break;
                        default: {
                            header("content-type: ".mime_content_type($localDocPath));
                        } break;
                    }
                    wp_ob_end_flush_all(); 
                    readfile($localDocPath);
                    $this->exit(); return;
                }
            }
            /**
             * Render the 'WA Config' 'Documentation' panel.
             */
            public function e_config_doc_render_panel(): void
            {
                if( !is_admin() ){
                    $this->warn("e_config_doc_render_panel need to be called from admin areas.");
                    return;
                }
                echo wp_kses_post("<p>[$this->iId] Showing documentation from {$this->pluginName}</p>");
                $readMePdfUrl = plugins_url("ReadMe.pdf", $this->pluginFile);
                $readMePdfUrlEncoded = urlencode($readMePdfUrl);
                $readMeTitle = "<h1>ReadMe.pdf <a href='$readMePdfUrl' target='_blank'>"
                    . __("Télécharger", 'monwoo-web-agency-config'/** 📜*/)
                    . '<span class="dashicons dashicons-download"></span>'
                    . "</a></h1>";
                $waWithPdfJsAssets = AppInterface::instance();
                $viewerUrl = plugins_url("assets/pdfjs/web/viewer.html", $waWithPdfJsAssets->pluginFile)
                    . '?file=';
                $readMeDevPdfUrl = plugins_url("ReadMeDev.pdf", $this->pluginFile);
                $readMeDevPdfUrlEncoded = urlencode($readMeDevPdfUrl);
                $readMeDevTitle = "<h1>ReadMeDev.pdf <a href='$readMeDevPdfUrl' target='_blank'>"
                    . __("Télécharger", 'monwoo-web-agency-config'/** 📜*/)
                    . '<span class="dashicons dashicons-download"></span>'
                    . "</a></h1>";
                $extraDocFolder = $this->pluginRoot . 'doc-extra';
                $docFiles = list_files($extraDocFolder);
                if ($docFiles) {
                    usort($docFiles, 'strnatcasecmp');
                }
                $extraDocumentation = [ function () { ?>
                    <h1><?php _e(
                        "Documentation supplémentaire",
                        'monwoo-web-agency-config'/** 📜*/
                    ) ?> </h1>
                <?php } ];
                foreach ($docFiles as $docFile) {
                    $type = wp_check_filetype($docFile);
                    if ('pdf' !== $type['ext']) {
                        continue; 
                    }
                    $relativeDocPath = str_replace(
                        $this->pluginRoot,
                        "", 
                        $docFile
                    );
                    $docUrl = plugins_url($relativeDocPath, $this->pluginFile);
                    $docIframe = function () use ($viewerUrl, $docUrl) { ?>
                        <div>
                            <iframe
                            class="wa-pdf-read-me"
                            src="<?php echo esc_url("$viewerUrl$docUrl") ?>"
                            title="webviewer"
                            frameborder="0"
                            onload="wa_resizeDocIframe(this)"
                            width="100%">
                            </iframe>
                        </div>
                    <?php };
                    $extraDocumentation[] = function ()
                    use ($relativeDocPath, $docUrl, $docIframe) {?>
                        <h2><?php echo esc_html("$relativeDocPath") ?>
                            <a
                            href='<?php echo esc_url("$docUrl") ?>'
                            target='_blank'>
                                <?php _e("Télécharger", 'monwoo-web-agency-config'/** 📜*/) ?>
                                <span class="dashicons dashicons-download"></span>
                            </a>
                        </h2>
                        <?php wa_render($docIframe) ?>
                    <?php };
                }
                $docIndex = plugins_url("doc/", $this->pluginFile);
                ?>
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
                    <?php if(file_exists($this->pluginRoot . '_doc')
                    || file_exists($this->pluginRoot . 'doc')) { ?>
                        <div>
                            <iframe
                            class="wa-php-doc"
                            scrolling='no'
                            src="<?php echo esc_url("$docIndex") ?>"
                            title="Php documentation"
                            frameborder="0"
                            onload="wa_resizeDocIframe(this)"
                            width="100%">
                            </iframe>
                        </div>
                    <?php } ?>
                    <?php echo wp_kses_post("$readMeTitle") ?>
                    <div>
                        <iframe
                        class="wa-pdf-read-me"
                        src="<?php echo esc_url("$viewerUrl$readMePdfUrlEncoded") ?>"
                        title="webviewer"
                        frameborder="0"
                        onload="wa_resizeDocIframe(this)"
                        width="100%">
                        </iframe>
                    </div>
                    <?php echo wp_kses_post("$readMeDevTitle") ?>
                    <div>
                        <iframe
                        class="wa-pdf-read-me-dev"
                        src="<?php echo esc_url("$viewerUrl$readMeDevPdfUrlEncoded") ?>"
                        title="webviewer"
                        frameborder="0"
                        onload="wa_resizeDocIframe(this)"
                        width="100%">
                        </iframe>
                    </div>
                    <?php wa_render($extraDocumentation) ?>
                <?php
            }
            /**
             * Add the 'WA Config' 'Documentation' panel.
             */
            public function e_config_doc_admin_menu(): void
            {
                $this->debugVerbose("Will e_config_doc_admin_menu");
                $suffix = $this->iIndex ? "-{$this->iIndex}" : "";
                $titleSuffix = " " . $this->iPrefix
                . ($this->iRelativeIndex ? " {$this->iRelativeIndex}" : "");
                $this->e_config_add_section(
                    '<span class="dashicons dashicons-code-standards"></span> '
                    . (
                        $this->iIndex
                        ? __('Doc', 'monwoo-web-agency-config'/** 📜*/) . "$titleSuffix"
                        : __('Documentation', 'monwoo-web-agency-config'/** 📜*/)
                    ),
                    [$this, 'e_config_doc_render_panel'],
                    "{$this->eConfigDocPageKey}$suffix",
                    $this->e_config_count_submenu(),
                    $this->baseCabability,
                );
            }
            /**
             * Get available field templates usable with WP Admin Settings API
             * 
             * **Templates** : 
             *  - $checkboxTemplate
             *  - $textareaTemplate
             *  - $multilLangTemplate
             *  - $multilLangTextAreaTemplate
             *  - $hiddenTemplate
             * 
             * 
             * @return array<string, callable> {
             *   Available fields templates
             * 
             *   @type string **$templateVarName** Name of the template factory ready 
             *     to extract as PHP variable
             * 
             *   @type callable ((string, string, string) => string)  **$templateFactory** {
             *     Factory usable to render the corresponding form field
             *
             *     ($safeValue, $fieldId, $fieldName) => $templateView
             *   }
             * }
             */
            public function e_config_form_field_templates()
            {
                $checkboxTemplate = function ($safeValue, $fieldId, $fieldName) {
                    $checked = boolval($safeValue) ? 'checked' : '';
                    $value = $checked ? '1' : '0';
                    return function()
                    use ( & $fieldId, & $fieldName, & $value, & $checked ) { ?>
                        <div>
                            <input
                            type="checkbox"
                            class="wppd-ui-toggle wa-checkbox"
                            id="<?php echo esc_attr($fieldId) ?>"
                            name="<?php echo esc_attr($fieldName) ?>"
                            value="<?php echo wp_kses_post($value) ?>"
                            <?php echo esc_attr($checked) ?>
                            />
                        </div>
                    <?php };
                };
                $textareaTemplate = function ($safeValue, $fieldId, $fieldName) {
                    return function()
                    use ( & $fieldId, & $fieldName, & $safeValue ) { ?>
                        <textarea
                        rows="5"
                        class="wa-review-textarea-<?php echo esc_attr($fieldId) ?>"
                        id="<?php echo esc_attr($fieldId) ?>"
                        name="<?php echo esc_attr($fieldName) ?>"
                        ><?php echo htmlspecialchars(wp_kses_post($safeValue)) ?></textarea>
                    <?php };
                };
                $multilLangTemplate = function ($safeValue, $fieldId, $fieldName) {
                    $localTranslations = $safeValue;
                    $emptyDefaults = $this->e_footer_get_empty_string_by_locale();
                    if (!is_array($localTranslations)) {
                        $this->warn("Fixing wrong multilang value to empty", $localTranslations);
                        $localTranslations = $emptyDefaults;
                    }
                    $localTranslations = array_merge($emptyDefaults, $localTranslations);
                    $t = [];
                    foreach ($localTranslations as $locale => $translate) {
                        $t[] = function()
                        use ( & $fieldId, & $fieldName, $locale, $translate ) { ?>
                            <p>
                                <label><?php echo esc_attr($locale) ?> : </label>
                                <input class='<?php echo esc_attr("{$fieldId}_$locale") ?>'
                                type='text'
                                name='<?php echo esc_attr("{$fieldName}[$locale]") ?>'
                                value='<?php echo wp_kses_post($translate) ?>'
                                />
                            </p>
                        <?php };
                    }
                    return $t;
                };
                $multilLangTextAreaTemplate = function ($safeValue, $fieldId, $fieldName) {
                    $localTranslations = $safeValue;
                    $emptyDefaults = $this->e_footer_get_empty_string_by_locale();
                    if (!is_array($localTranslations)) {
                        $this->warn("Fixing wrong multilang value to empty", $localTranslations);
                        $localTranslations = $emptyDefaults;
                    }
                    $localTranslations = array_merge($emptyDefaults, $localTranslations);
                    $t = [];
                    foreach ($localTranslations as $locale => $translate) {
                        $t[] = function()
                        use ( & $fieldId, & $fieldName, $locale, $translate ) { ?>
                            <p>
                                <label><?php echo esc_attr($locale) ?> : </label>
                                <textarea
                                rows="5"
                                class="<?php echo esc_attr("{$fieldId}_$locale") ?>"
                                name="<?php echo esc_attr("{$fieldName}[$locale]") ?>"
                                ><?php echo htmlspecialchars(wp_kses_post($translate)) ?></textarea>
                            </p>
                        <?php };
                    };
                    return $t;
                };
                $hiddenTemplate = function ($safeValue, $fieldId, $fieldName) {
                    return function()
                    use ( & $fieldId, & $fieldName, & $safeValue ) { ?>
                        <input id='<?php echo esc_attr($fieldId) ?>' type='hidden'
                        name='<?php echo esc_attr($fieldName) ?>'
                        value='<?php echo wp_kses_post($safeValue) ?>'
                        />
                    <?php };
                };
                return [
                    'checkboxTemplate' => $checkboxTemplate,
                    'textareaTemplate' => $textareaTemplate,
                    'multilLangTemplate' => $multilLangTemplate,
                    'multilLangTextAreaTemplate' => $multilLangTextAreaTemplate,
                    'hiddenTemplate' => $hiddenTemplate,
                ]; 
            }
            protected $_capAndRolesCacheKey = 'wa_config_admin_capabilities_and_roles';
            protected $_capAndRolesCache = null;
            /**
             * WARNING, return an SplPriorityQueue => need to be cloned to
             * iterate more than one time since dequeue value on loop...
             */
            protected function e_config_capabilities_and_roles() {
                delete_transient($this->_capAndRolesCacheKey);
                if ($this->_capAndRolesCache
                    || $this->_capAndRolesCache = get_transient($this->_capAndRolesCacheKey)
                ) {
                    return clone $this->_capAndRolesCache;
                }
                $capAndRoles = new class() extends SplPriorityQueue {
                    public function compare($priority1, $priority2): int {
                        return strnatcasecmp(strval($priority2),strval($priority1));
                    }
                    public function __serialize() {
                        $clone = clone $this;
                        $data = [];
                        foreach ($clone as $item) {
                          $data[] = $item;
                        }
                        return $data; 
                    }
                    public function __unserialize($data) {
                        foreach ($data as $item) {
                            $this->insert($item['data'], $item['priority']);
                        }
                    }
                };
                $capAndRoles->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
                global $wp_roles;
                $checkDuplicates = [];
                $capAndRoles = array_reduce(
                    array_chunk($wp_roles->roles, 1, true), 
                    function (SplPriorityQueue $cAndR, $roleDataChunk)
                    use (&$checkDuplicates) {
                        $r = key($roleDataChunk);
                        $rData = current($roleDataChunk);
                        if (!array_key_exists($r, $checkDuplicates)) {
                            $cAndR->insert("--$r--", $r);
                            $checkDuplicates[$r] = null;
                        }
                        array_reduce(
                            array_keys($rData['capabilities']),
                            function (SplPriorityQueue $cAndR, $c)
                            use (&$checkDuplicates) {
                                if (!array_key_exists($c, $checkDuplicates)) {
                                    $cAndR->insert($c, $c);
                                    $checkDuplicates[$c] = null;
                                }
                                return $cAndR;
                            },
                            $cAndR
                        );
                        return $cAndR;
                    },
                    $capAndRoles
                );
                $this->_capAndRolesCache = $capAndRoles;
                set_transient( 
                    $this->_capAndRolesCacheKey,
                    $this->_capAndRolesCache,
                    15 * 60 
                );
                return clone $this->_capAndRolesCache;
            }
            protected $_capAndRolesSearchCacheKey = 'wa_config_admin_capabilities_and_roles_search';
            protected $_capAndRolesSearchCache = null;
            /**
             * Output a suggestion list of all available capabilities and roles
             * 
             * Used for ajax suggestion lists. Echo one sugestion per line.
             * 
             * GET parameters :
             *  - **q** : The query used to filter the suggestions (end user search input)
             *
             * @see https://mwop.net/blog/253-Taming-SplPriorityQueue.html
             */
            public function e_config_list_capabilities_and_roles() : void {
                if (!is_admin()) {
                    $this->err("wa-config admin param section is under admin pages only");
                    echo "<p> "
                        . __(
                            "Cette opération nécessite une page d'administration.",
                            'monwoo-web-agency-config'/** 📜*/
                        )
                        . "</p>";
                    return;
                }
                $query = filter_var( sanitize_text_field($_GET['q'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
                $query = wp_unslash( $query );
                if (!$this->_capAndRolesSearchCache) {
                    $this->_capAndRolesSearchCache = get_transient( 
                        $this->_capAndRolesSearchCacheKey
                    );
                }
                if (!$this->_capAndRolesSearchCache) {
                    $this->_capAndRolesSearchCache = [];
                }
                if (array_key_exists($query, $this->_capAndRolesSearchCache)) {
                    $this->debug("e_config_list_capabilities_and_roles loaded from cache [$query]");
                    echo wp_kses_post($this->_capAndRolesSearchCache[$query]);
                    $this->exit(); return;
                }
                $capAndRoles = $this->e_config_capabilities_and_roles();
                $isFirstMatch = true;
                $searchResult = '';
                while (
                    $capAndRoles->count()
                    && ['data'=>$d, 'priority'=>$p]
                    = $capAndRoles->extract()
                ) {
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
                set_transient( 
                    $this->_capAndRolesSearchCacheKey,
                    $this->_capAndRolesSearchCache,
                    24 * 60 * 60 
                );
                echo wp_kses_post($searchResult);
                $this->exit(); return;
            }
            protected function e_config_capability_suggestionbox_template(
                $safeValue, $fieldId, $fieldName, $placeholder = ""
            ) {
                return function()
                use ( & $fieldId, & $fieldName, & $placeholder, & $safeValue ) { ?>
                    <input
                    type='text'
                    placeholder="<?php echo esc_attr($placeholder) ?>"
                    class="wa-suggest-capabilities-and-roles"
                    id="<?php echo esc_attr($fieldId) ?>"
                    name="<?php echo esc_attr($fieldName) ?>"
                    value="<?php echo wp_kses_post($safeValue) ?>"
                    />
                <?php };
            }
            protected function e_config_capability_selectbox_template(
                $safeValue, $fieldId, $fieldName, $placeholder = ""
            ) {
                $options = [ function () { ?>
                    <option value = "" >
                        <?php _e("Non définit.", 'monwoo-web-agency-config'/** 📜*/) ?>
                    </option>
                <?php } ];
                $capAndRoles = $this->e_config_capabilities_and_roles();
                while (
                    $capAndRoles->count()
                    && ['data'=>$d, 'priority'=>$p]
                    = $capAndRoles->extract()
                ) {
                    $options[] = function () use ($p, $d) { ?>
                        <option value="<?php echo esc_attr($p) ?>">
                            <?php echo wp_kses_post($d) ?>
                        </option>
                    <?php };
                }
                return function ()
                use ( & $fieldId, & $fieldName, & $safeValue, & $options) { ?>
                    <select
                    class="wa-selectbox-capabilities-and-roles"
                    id="<?php echo esc_attr($fieldId) ?>"
                    name="<?php echo esc_attr($fieldName) ?>"
                    value="<?php echo esc_attr($safeValue) ?>"
                    >
                        <?php wa_render( $options ) ?>
                    </select>
                <?php };
            }
            /**
             * Helper to count sub menus of eAdminConfigPageKey or other page slug
             * 
             * @global array $submenu
             * 
             * @param string   $parent_slug The slug name for the parent menu 
             * (or the file name of a standard WordPress admin page).
             */
            protected function e_config_count_submenu($parent_slug = null) {
                global $submenu;
                $c = count($submenu[
                    $parent_slug ?? $this->eConfigPageKey
                ] ?? []);
                $this->debugVeryVerbose("Sub menu count for display order : $c");
                return $c;
            }
            protected function e_config_add_section(
                $title,
                $renderClbck,
                $newPageKey,
                $position = 1,
                $capability = null
            ): void {
                $this->debugVerbose("Will e_config_add_section $newPageKey");
                $capability = $capability ?? $this->baseCabability;
                $parentPageKey = $this->eConfigPageKey;
                \add_submenu_page(
                    $parentPageKey,
                    $title,
                    $title,
                    $capability,
                    $newPageKey,
                    $renderClbck,
                    $position
                );
                $this->debugVerbose("Did e_config_add_section $newPageKey");
            }
        }
    }
    if (!trait_exists(AdminParamActivable::class)) { 
        /**
         * This trait is under developpement
         * 
         * Will be used to generically enable/disable features from code and parameters wa-config panel
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        trait AdminParamActivable
        {
            protected function a_p_a_register_feature($featureClass) {
                throw new Exception("Dev in progress, forseen for next version");
            }
            protected function a_p_a_activate($featureId) {
                throw new Exception("Dev in progress, forseen for next version");
            }
            protected function a_p_a_desactivate($featureId) {
                throw new Exception("Dev in progress, forseen for next version");
            }
        }
    }
    if (!trait_exists(EditableReview::class)) { 
        /**
         * This trait load the wa-config review system and admin panel
         * 
         * - **review** : the Review panel, sub-menu of WA Config
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @use Editable
         */
        trait EditableReview
        {
            use Editable;
            use TestableEnd2End;
            /**
             * eReviewDataStoreKey is our 'review' option store key
             * used to store our reviews
             * 
             * TIPS : You can change this eReviewDataStoreKey in your app constructor
             * to use your own named store instead of 
             * sharing the main plugin review board.
             * 
             * @since 0.0.1
             * @property-read $eReviewDataStoreKey the Wa-config option Key for get_option
             * @author service@monwoo.com
             */
            public $eReviewDataStoreKey = 'wa_e_review_data_store';
            protected $eReviewDataStore = []; 
            protected $eReviewPageKey = 'wa-e-review-page';
            protected $eReviewSettingsFormSection = 'wa-e-review-settings-form-section';
            protected $eReviewSettingsFormGroup = 'wa_e_review_settings_form_group'; 
            protected $eReviewSettingsEditCabability = 'edit_posts';
            protected $eReviewSettingsForm = [];
            protected $eReviewSettingsFormDefaults = [];
            protected $_reviewsByKeySearchCacheKey = 'wa_config_admin_review_by_key_search';
            protected $_reviewsByKeySearchCache = null; 
            protected $_eReviewSettingsPreUpdateSelfSentinel = false;
            protected $_eReviewDataPreUpdateSelfSentinel = false;
            protected $eReviewDefaultCheckpoint = [
                'category' => null,
                'category_icon' => '',
                'title' => null,
                'title_icon' => '',
                'requirements' => null,
                'value' => null,
                'result' => false,
                'access_cap_or_role' => null,
                'is_activated' => true,
                'is_deleted' => false,
                'fixed_id' => null,
                'is_computed' => false,
                'create_time' => null,
                'created_by' => null,
                'import_time' => null,
                'imported_by' => null,
            ];
            protected $eReviewChecksByCategoryByTitle = [];
            protected $eReviewChecksByKeyId = []; 
            protected $eReviewIconsByCategory = [];
            protected $eReviewIdsToTrash = [];
            protected function _010_e_review__bootstrap()
            {
                $this->debug("Will _010_e_review__bootstrap");
                $this->eReviewSettingsFormDefaults = [ 
                    $this->eConfOptReviewCategory => "",
                    $this->eConfOptReviewCategoryIcon => "",
                    $this->eConfOptReviewTitle => "",
                    $this->eConfOptReviewTitleIcon => "",
                    $this->eConfOptReviewRequirements => "",
                    $this->eConfOptReviewResult => true,
                    $this->eConfOptReviewValue => "",
                    $this->eConfOptReviewIsActivated => true,
                    $this->eConfOptReviewAccessCapOrRole => "",
                ];
                $suffix = $this->iIndex ? "-{$this->iIndex}" : "";
                $this->eReviewPageKey = "{$this->eReviewPageKey}$suffix";
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_review__bootstrap')) {
                    return; 
                }
                add_action( 'activated_plugin', [$this, 'e_review_on_plugins_activated'], 10, 2);
                /**
                 * You need to duplicate this code if you want your own review system,
                 * cf Monwoo moon-scrap-lite
                 */
                add_filter("pre_update_option_{$this->eReviewDataStoreKey}",
                [$this, "e_review_data_pre_update_filter"], 10, 3);
                if (is_admin()) {
                    $filter = "pre_update_option_{$this->eReviewSettingsFormKey}";
                    $this->debugVerbose("Will filter '$filter' with 'e_review_settings_pre_update_filter'");
                    add_filter($filter,
                    [$this, "e_review_settings_pre_update_filter"], 10, 3);
                    $self = $this;
                    add_filter( "option_page_capability_{$this->eReviewPageKey}",
                    [$this, "e_review_settings_page_capability"]); 
                }
            }
            protected function _010_e_review__load()
            {
                $this->debug("Will _010_e_review__load");
                add_action('admin_menu', [$this, 'e_review_settings_do_admin_menu'], 4);
                $this->eReviewChecksByCategoryByTitle = $this->e_review_data_fetch(
                    $this->eConfOptReviewsByCategorieByTitle, []
                );
                $this->debugVeryVerbose("Admin init WA Review options", $this->eReviewDataStore);
                add_action('admin_init', [$this, 'e_review_settings_init_form'], 7);
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_review__load')) {
                    return; 
                }
                $pageId = filter_var( sanitize_text_field($_GET['page'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                $ajaxActionId = filter_var( sanitize_key($_GET['action'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                if ($pageId === $this->eReviewPageKey
                || $ajaxActionId === 'wa-review-action') {
                    add_action( 'init', [$this, 'e_review_data_add_base_review']);
                }
                add_action(
                    'wp_ajax_wa-list-review-data-by-key', 
                    [$this, 'e_review_list_data_by_key']
                );
                add_action(
                    'wp_ajax_wa-review-action', 
                    [$this, 'e_review_data_action']
                );
            }
			/**
			 * Fires after a plugin has been activated.
			 *
			 * If a plugin is silently activated (such as during an update),
			 * this callback does not fire.
			 *
			 * @param string $plugin       Path to the plugin file relative to the plugins directory.
			 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
			 *                             or just the current site. Multisite only. Default false.
			 */
            public function e_review_on_plugins_activated($plugin, $network_wide) : void {
                $this->debug("Did activate plugin : $plugin");
                if ($this->pluginRelativeFile === $plugin) {
                    $this->debug("Will e_review_on_plugins_activated for $plugin");
                    $this->e_review_data_add_base_review(); 
                }
            }
            /**
             * Render the 'WA Config' 'Review' panel for wp-admin.
             */
            public function e_review_render_admin_panel(): void
            {
                if (!is_user_logged_in()) {
                    $this->err("wa-config e_review_data_check_insert is under logged users only");
                    wp_loginout();
                    $this->exit(); return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                if ($this->shouldDebug || $this->shouldDebugVerbose || $this->shouldDebugVeryVerbose) {
                    echo "<h1 style='color:red;'>"
                    . __('ATTENTION : Mode débug activé.', 'monwoo-web-agency-config'/** 📜*/)
                    . "</h1>";
                }
                $this->e_review_settings_render_form();
                $checksByCategorieByTitle = $this->e_review_data_check_byCategoryByTitle();
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
                    $categoryIcon = $this->eReviewIconsByCategory[$category][0] ?? '';
                    $catIdx = sanitize_title($category);
                    echo wp_kses_post("<h1 class='wa-check-category-title'>
                    <span>
                    <span>$category</span> $categoryIcon</span>
                    <a
                    id='wa-check-category-title-$catIdx'
                    href='#wa-check-category-title-$catIdx'
                    data-wa-expand-target='#wa-check-list-$catIdx .wa-expand'
                    class='wa-expand-toggler'>
                        <span class='dashicons dashicons-fullscreen-alt'></span>
                    </a></h1>");
                    ?>
                    <table
                    id="<?php echo esc_attr("wa-check-list-$catIdx") ?>"
                    class="wa-check-list"
                    cellspacing="0px"
                    cellpadding="0px"
                    >
                        <!-- TODO : for print only... will messup with flex...  + Align for A4 thead>
                            <tr>
                                <th align="left">&nbsp;</th>
                                <th align="left"><?php esc_html_e( 'Exigence', 'monwoo-web-agency-config'/** 📜*/ ); ?></th>
                                <th align="left"><?php esc_html_e( 'Présent', 'monwoo-web-agency-config'/** 📜*/ ); ?></th>
                            </tr>
                        </thead-->
                        <tbody>
                            <tr>
                                <th class="wa-check-title">&nbsp;</th>
                                <th class="wa-check-required"><?php esc_html_e( 'Exigence', 'monwoo-web-agency-config'/** 📜*/ ); ?></th>
                                <th class="wa-check-present"><?php esc_html_e( 'Présent', 'monwoo-web-agency-config'/** 📜*/ ); ?></th>
                            </tr>
                            <?php
                            foreach ($reviewsByTitle as $title => $reviews) {
                                $titleIdx = sanitize_title($title);
                                foreach ( [$reviews[0]] as $idx => $review ) {
                                    $rowClass = "wa-check";
                                    if ( $review['result'] ) {
                                        $background = '#7cc038';
                                        $color      = 'black';
                                        $rowClass  .= ' wa-check-valid';
                                    } elseif ( isset( $review['fallback'] ) ) {
                                        $background = '#FCC612';
                                        $color      = 'black';
                                        $rowClass  .= ' wa-check-fallback';
                                    } else {
                                        $background = '#f43';
                                        $color      = 'white';
                                        $rowClass  .= ' wa-check-fail';
                                    }
                                    $isActif = $review['is_activated'] ?? false;
                                    $rowClass .= $isActif ? '' : ' wa-check-disabled wa-expand wa-expand-collapsed';
                                    $requirementIcon = $review['is_computed'] ? 'superhero' : 'buddicons-buddypress-logo';
                                    ?>
                                    <tr
                                    id='<?php echo esc_attr("wa-check-title-$catIdx-$titleIdx") ?>'
                                    class="<?php echo esc_attr($rowClass) ?>"
                                    >
                                        <td class="wa-check-title">
                                            <?php echo wp_kses_post( $title ); ?>
                                            <br />
                                            <?php
                                            if ( $review['result'] ) {
                                                echo wp_kses_post('<span class="dashicons dashicons-awards wa-color-review-ok"></span> ');
                                            }
                                            ?>
                                            <?php echo wp_kses_post( $review['title_icon'] ?? '' ); ?>
                                        </td>
                                        <td class="wa-check-required">
                                            <div
                                            class="wa-last-check wa-expand-toggler"
                                            data-wa-expand-target="<?php echo  esc_attr("#wa-check-title-$catIdx-$titleIdx .wa-expand") ?>"
                                            >
                                                <div>
                                                    <span class="dashicons dashicons-<?php echo  esc_attr($requirementIcon) ?>"></span>
                                                    <strong>
                                                        <?php echo wp_kses_post( $review['requirements'] === true 
                                                        ? esc_html__( 'Yes', 'monwoo-web-agency-config'/** 📜*/ ) 
                                                        : $review['requirements'] ); ?>
                                                    </strong>
                                                </div><br />
                                                <?php echo wp_kses_post("<notice><span class='dashicons dashicons-calendar-alt'></span> "
                                                . date("Y/m/d H:i:s O ", $review['create_time'])
                                                . ($review['is_computed'] ? "<br />" . $review['fixed_id'] : ''). "</notice>") ?>
                                                <?php
                                                    if ($review['created_by'] ?? false) {
                                                        echo wp_kses_post("<br/><notice>-- {$review['created_by']}</notice> ");
                                                    }
                                                    if ($review['access_cap_or_role'] ?? false) {
                                                        echo wp_kses_post("<br/>" . __( 'Pour :', 'monwoo-web-agency-config'/** 📜*/ )
                                                        . " <notice>{$review['access_cap_or_role']}</notice>");
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
                                                    
                                                    foreach ( array_slice($reviews, 0) as $idx => $pReview ) {
                                                        $pRowClass = "wa-check wa-previous-check";
                                                        if ( $pReview['result'] ) {
                                                            $pBackground = '#7cc038';
                                                            $pColor      = 'black';
                                                            $pRowClass  .= ' wa-check-valid';                    
                                                        } elseif ( isset( $pReview['fallback'] ) ) {
                                                            $pBackground = '#FCC612';
                                                            $pColor      = 'black';
                                                            $pRowClass  .= ' wa-check-fallback';
                                                        } else {
                                                            $pBackground = '#f43';
                                                            $pColor      = 'white';
                                                            $pRowClass  .= ' wa-check-fail';
                                                        }
                                                        $pReviewKey = $this->fetch_review_key_id($pReview);
                                                        $pIsActif = $pReview['is_activated'] ?? false;
                                                        $pRowClass .= $pIsActif ? '' : ' wa-check-disabled';
                                                        $pRequirementIcon = $pReview['is_computed'] ? 'superhero' : 'buddicons-buddypress-logo';
                                                        ?>
                                                        <tr
                                                        id='<?php echo esc_attr("wa-check-title-$catIdx-$titleIdx") ?>'
                                                        class="<?php echo esc_attr($pRowClass) ?>"
                                                        >
                                                            <td class="wa-check-required">
                                                                <div
                                                                class="wa-last-check"
                                                                >
                                                                    <div>
                                                                        <?php
                                                                        if ( $pReview['result'] ) {
                                                                            echo '<span class="dashicons dashicons-awards wa-color-review-ok"></span> ';
                                                                        }
                                                                        ?>
                                                                        <span class="dashicons dashicons-<?php echo esc_attr($pRequirementIcon) ?>"></span>
                                                                        <strong>
                                                                            <?php echo wp_kses_post( $pReview['requirements'] === true 
                                                                            ? esc_html__( 'Yes', 'monwoo-web-agency-config'/** 📜*/ ) 
                                                                            : $pReview['requirements'] ); ?>
                                                                        </strong>
                                                                    </div><br />
                                                                    <?php echo wp_kses_post("<notice><span class='dashicons dashicons-calendar-alt'></span> "
                                                                    . date("Y/m/d H:i:s O ", $pReview['create_time'])
                                                                    . ($pReview['is_computed'] ? "<br />" . $pReview['fixed_id'] : ''). "</notice>") ?>
                                                                    <?php
                                                                        if ($pReview['created_by'] ?? false) {
                                                                            echo wp_kses_post("<br /><notice>-- {$pReview['created_by']}</notice>");
                                                                        }
                                                                        if ($pReview['access_cap_or_role'] ?? false) {
                                                                            echo wp_kses_post("<br />" . __( 'Pour :', 'monwoo-web-agency-config'/** 📜*/ )
                                                                            . " <notice>{$pReview['access_cap_or_role']}</notice>");
                                                                        }
                                                                    ?>
                                                                </div>
                                                            </td>
                                                            <td
                                                            class="wa-check-present"
                                                            style="background-color:<?php echo esc_attr($pBackground) ?>; color:<?php echo esc_attr($pColor) ?>">
                                                                <?php
                                                                if ( $pReview['result'] ) {
                                                                    echo '<span class="dashicons dashicons-yes-alt"></span> ';
                                                                } else {
                                                                    echo '<span class="dashicons dashicons-marker"></span> ';
                                                                }
                                                                echo wp_kses_post( $pReview['value'] ?? "");
                                                                if ( $pReview['result'] && ! ($pReview['value'] ?? false) ) {
                                                                    echo esc_html__( 'Validé', 'monwoo-web-agency-config'/** 📜*/ );
                                                                }
                                                                if ( ! $pReview['result'] ) {
                                                                    if ( isset( $pReview['fallback'] ) ) {
                                                                        printf( '<div>%s. %s</div>', esc_html__( 'Compensé', 'monwoo-web-agency-config'/** 📜*/ ), esc_html( $pReview['fallback'] ) );
                                                                    }
                                                                    if ( isset( $pReview['failure'] ) ) {
                                                                        printf( '<div>%s</div>', wp_kses_post( $pReview['failure'] ) );
                                                                    } else {
                                                                        printf( '<div>%s.</div>', esc_html__( 'A faire', 'monwoo-web-agency-config'/** 📜*/ ) );
                                                                    }
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="wa-check-actions">
                                                                <?php if (current_user_can($this->optAdminEditCabability)
                                                                || !($pReview['is_computed'] ?? false)) { ?>
                                                                    <?php if ($this->e_review_data_check_isReadable($pReview)) { ?>
                                                                        <?php if ($this->e_review_data_check_isWriteable($pReview) && !($pReview['is_computed'] ?? false)) { ?>
                                                                            <a 
                                                                            href="#wa_config_review_add_checkpoint"
                                                                            class="wa-check-activate-toogle"
                                                                            data-wa-review-activate-src='<?php echo base64_encode(wp_json_encode($pReview)); ?>'
                                                                            data-wa-nonce='<?php echo wp_create_nonce("wa-check-nonce-$pReviewKey"); ?>'
                                                                            >
                                                                                <?php if (($pReview['is_activated'] ?? true)) { ?>
                                                                                    <span class="dashicons dashicons-hidden"></span>
                                                                                    <?php  _e( "Desactiver", 'monwoo-web-agency-config'/** 📜*/ )  ?>
                                                                                <?php } else { ?>
                                                                                    <span class="dashicons dashicons-visibility"></span>
                                                                                    <?php  _e( "Activer", 'monwoo-web-agency-config'/** 📜*/ )  ?>
                                                                                <?php } ?>
                                                                            </a>
                                                                        <?php } ?>
                                                                        <?php if ($this->e_review_data_check_isWriteable($pReview)) { ?>
                                                                            <a 
                                                                            href="#wa_config_review_delete_checkpoint"
                                                                            class="wa-check-delete-trigger"
                                                                            data-wa-review-delete-src='<?php echo base64_encode(wp_json_encode($pReview)); ?>'
                                                                            data-wa-nonce='<?php echo wp_create_nonce("wa-check-nonce-$pReviewKey"); ?>'
                                                                            >
                                                                                <span class="dashicons dashicons-trash"></span>
                                                                                <?php  _e( "Supprimer", 'monwoo-web-agency-config'/** 📜*/ )  ?>
                                                                            </a>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                                <a 
                                                                href="#wa_config_review_add_checkpoint"
                                                                class="wa-check-duplicate-trigger"
                                                                data-wa-review-duplicate-src='<?php echo base64_encode(wp_json_encode($pReview)); ?>'
                                                                >
                                                                    <span class="dashicons dashicons-admin-page"></span>
                                                                    <?php  _e( "Dupliquer", 'monwoo-web-agency-config'/** 📜*/ )  ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td
                                        class="wa-check-present"
                                        style="background-color:<?php echo esc_attr($background) ?>; color:<?php echo esc_attr($color) ?>">
                                            <?php
                                            if ( $review['result'] ) {
                                                echo '<span class="dashicons dashicons-yes-alt"></span> ';
                                            } else {
                                                echo '<span class="dashicons dashicons-marker"></span> ';
                                            }
                                            echo wp_kses_post( $review['value'] ?? "");
                                            if ( $review['result'] && ! ($review['value'] ?? false) ) echo esc_html__( 'Validé', 'monwoo-web-agency-config'/** 📜*/ );
                                            if ( ! $review['result'] ) {
                                                if ( isset( $review['fallback'] ) ) {
                                                    printf( '<div>%s. %s</div>', esc_html__( 'Compensé', 'monwoo-web-agency-config'/** 📜*/ ), esc_html( $review['fallback'] ) );
                                                }
                                                if ( isset( $review['failure'] ) ) {
                                                    printf( '<div>%s</div>', wp_kses_post( $review['failure'] ) );
                                                } else {
                                                    printf( '<div>%s.</div>', esc_html__( 'A faire', 'monwoo-web-agency-config'/** 📜*/ ) );
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
                echo wp_kses_post("<p>[$this->iId] <strong><a
                href='#wa-check-export-csv'
                class='wa-check-export-csv-trigger'
                data-wa-nonce='" . wp_create_nonce("wa-check-nonce-") . "'
                >" . __(
                    "Exporter les checkpoints en CSV",
                    'monwoo-web-agency-config'/** 📜*/
                ) . "</a></strong></p>");        
                echo wp_kses_post("<p>[$this->iId] <strong><span
                class='wa-check-import-csv-trigger'
                >" . __(
                    "Importer les checkpoints depuis un fichier CSV",
                    'monwoo-web-agency-config'/** 📜*/
                ) . "</span></strong></p>");
                echo '<form action="'
                . esc_url(add_query_arg([
                    'action' => 'wa-review-action',
                ], admin_url( 'admin-ajax.php' )))
                .'" method="post" enctype="multipart/form-data">';
                echo "<input type='hidden' name='wa-iid' value='" . esc_attr($this->iId) . "'>";
                echo '<input type="file" name="wa-import-csv-file">';
                echo '<input type="hidden" name="wa-action" value="import-csv">';
                echo '<input type="hidden" name="wa-nonce" value="'
                . wp_create_nonce("wa-check-nonce-")
                .'">';
                echo '<input type="submit" name="submit" value="submit">';
                echo '</form>';  
                if (current_user_can($this->optAdminEditCabability) ) {
                    echo "<p>[". esc_attr($this->iId) . "] <strong><a
                    href='#clean-all-need-javascript'
                    class='wa-check-export-csv-trigger'
                    data-wa-nonce='" . wp_create_nonce("wa-check-nonce-") . "'
                    data-wa-clean-after-download='true'
                    >" . __(
                        "Supprimer toutes les données de review (Nettoyage des revues en cours et historique de suppression)",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . "</a></strong></p>";        
                }                    
                ?>
                <script>
                    jQuery(function() {
                        // Will be loaded only when all page will have load
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
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_category')
                                .value = duplicateData.category;
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_category_icon')
                                .value = duplicateData.category_icon || '';
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_title')
                                .value = duplicateData.title;
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_title_icon')
                                .value = duplicateData.title_icon || '';
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_requirements')
                                .value = duplicateData.requirements;
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_result')
                                .checked = duplicateData.result;
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_value')
                                .value = duplicateData.value;
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_is_activated')
                                .checked = duplicateData.is_activated;
                                addCheckpointForm.querySelector('#wa_e_review_settings_form_key_wa_review_access_cap_or_role')
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
                                    'wa-iid': '<?php echo esc_attr($this->iId) ?>',
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
                                    'wa-iid': '<?php echo esc_attr($this->iId) ?>',
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
                                    'wa-iid': '<?php echo esc_attr($this->iId) ?>',
                                    'wa-nonce': nonce,
                                };
                                console.log(data);

                                var cleanAll = function () {
                                    var data = {
                                        'wa-action': 'clean-all',
                                        'wa-iid': '<?php echo esc_attr($this->iId) ?>',
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

                    })
                </script>

                <?php

                $this->opti_print_blocked_urls_report();
                echo "<br /><br />";
                $this->opti_print_allowed_urls_report();
                echo "<br /><br />";
                $minimumCapabilityToRun = $this->getWaConfigOption(
                    $this->eConfOptATestsRunForCabability,
                    'administrator'
                );
                if (!is_admin() || !current_user_can($minimumCapabilityToRun)) {
                    $this->debug("wa-config TEST RUN can be done by $minimumCapabilityToRun only.");
                    echo "<p> " . __(
                        "Pour plus d'informations, nécessite une capacité ou un rôle :",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . " " . esc_attr($minimumCapabilityToRun) ." </p>";
                    return;
                }
                $siteUrl = site_url();
                $current_url = add_query_arg([
                    'page' => $this->eReviewPageKey,
                ], admin_url( 'admin.php' ));
                echo "<h1> " . __(
                    "CRITIQUE : prevoir un rollback SQL",
                    'monwoo-web-agency-config'/** 📜*/
                ) . " </h1>";
                echo "<p> " . __(
                    "<strong>ATTENTION :</strong> Assurez vous de pouvoir modifier votre base de données en dehors de WordPress pour recharger cette dernière via le backup SQL suivant en cas d'echec de rollback des actions de tests (ex : accès phpmyadmin) : ",
                    'monwoo-web-agency-config'/** 📜*/
                ) . " </p>";
                if (current_user_can('administrator')) {
                    echo wp_kses_post("<p> " . (
                        "<strong> DB_NAME : '" . DB_NAME . "'</strong> <br />"
                        . "<strong> DB_USER : <span class='wa-show-pass-on-hover'>'"
                        . DB_USER . "'</span></strong> <br />"
                        . "<strong> DB_PASSWORD : <span class='wa-show-pass-on-hover'>'"
                        . DB_PASSWORD . "'</span></strong> <br />"
                        . "<strong> DB_HOST : <span class='wa-show-pass-on-hover'>'"
                        . DB_HOST . "'</span></strong> <br />"
                        . "<strong> DB_CHARSET : '" . DB_CHARSET . "'</strong> <br />"
                    ) . " </p>");
                }
                $bckupSQLUrl = add_query_arg([
                    'action' => 'wa-e2e-test-action',
                    'wa-action' => 'do-backup',
                    'wa-iid' => $this->iId,
                    'wa-backup-type' => 'sql',
                    'wa-compression-type' => '.zip'
                ], admin_url( 'admin-ajax.php' ));
                echo wp_kses_post("<p>[$this->iId] <strong><a
                href='$bckupSQLUrl'
                >" . __(
                    "Cliquer ici pour effectuer et télécharger le backup SQL avant de lancer les tests.",
                    'monwoo-web-agency-config'/** 📜*/
                ) . "</a></strong></p>");
                $bckupSimpleZipUrl = add_query_arg([
                    'action' => 'wa-e2e-test-action',
                    'wa-action' => 'do-backup',
                    'wa-iid' => $this->iId,
                    'wa-backup-type' => 'simple-zip',
                ], admin_url( 'admin-ajax.php' ));
                echo wp_kses_post("<p>[$this->iId] <strong><a
                href='$bckupSimpleZipUrl'
                >" . __(
                    "Cliquer ici pour effectuer et télécharger le backup Zip simple (SQL + fichiers d'Upload).",
                    'monwoo-web-agency-config'/** 📜*/
                ) . "</a></strong></p>");
                $bckupFullZipUrl = add_query_arg([
                    'action' => 'wa-e2e-test-action',
                    'wa-action' => 'do-backup',
                    'wa-iid' => $this->iId,
                    'wa-backup-type' => 'full-zip',
                ], admin_url( 'admin-ajax.php' ));
                echo wp_kses_post("<p>[$this->iId] <strong><a
                href='$bckupFullZipUrl'
                >" . __(
                    "Cliquer ici pour effectuer et télécharger le backup Zip complet (SQL + tous les fichiers WordPress depuis la racine du site web).",
                    'monwoo-web-agency-config'/** 📜*/
                ) . "</a></strong></p>");
                $aTestConfigSubPath = 'tests/acceptance.suite.yml';
                $aTestConfigFile = $this->pluginRoot . "$aTestConfigSubPath";
                if (file_exists($aTestConfigFile)) {                
                    $bckupATestUrl = add_query_arg([
                        'wa-bckup-a-tests' => true,
                    ], $current_url);
                    $loadATestBckupUrl = add_query_arg([
                        'wa-load-a-tests-bckup' => true,
                    ], $current_url);
                    $shouldBckupATest = filter_var( sanitize_text_field($_GET['wa-bckup-a-tests'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                    $shouldLoadATestBckup = filter_var( sanitize_text_field($_GET['wa-load-a-tests-bckup'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                    if ($shouldBckupATest && $shouldLoadATestBckup) {
                        $current_url = remove_query_arg([
                            'wa-bckup-a-tests', 'wa-load-a-tests-bckup'
                        ]);
                        Notice::displayError(""
                        . __("Ne peut backuper et charger en même temps, choisissez l'un ou l'autre s.v.p.",
                        'monwoo-web-agency-config'/** 📜*/));
                        wp_redirect( $current_url );
                        $this->exit(); return;
                    }
                    echo wp_kses_post("<p>[$this->iId] <strong><a
                    href='$bckupATestUrl'
                    >" . __(
                        "Backuper les fichiers de tests dans un dossier upload privé.",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . "</a></strong></p>");
                    echo wp_kses_post("<p>[$this->iId] <strong><a
                    href='$loadATestBckupUrl'
                    >" . __(
                        "Charger les fichers de tests depuis le dossier upload.",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . "</a></strong></p>");
                    require_once ( ABSPATH . '/wp-admin/includes/file.php' );
                    WP_Filesystem();
                    $bckupFolder = $this->get_backup_folder();
                    $bckupStructureSrc = $this->pluginRoot . "assets/backup-bootstrap";
                    if (!file_exists("$bckupFolder/test-wa-config")) {
                        copy_dir(
                            "$bckupStructureSrc",
                            "$bckupFolder"
                        );
                    }
                    $acceptanceSrcFolder = "{$this->pluginRoot}tests/acceptance";
                    $acceptanceUploadFolder = "$bckupFolder/acceptance";
                    if ($shouldBckupATest) {
                        if (file_exists($acceptanceUploadFolder)) {
                            rmdir($acceptanceUploadFolder);
                        }
                        mkdir($acceptanceUploadFolder, 0777, true);
                        copy_dir(
                            "$acceptanceSrcFolder",
                            "$acceptanceUploadFolder"
                        );
                        Notice::displaySuccess(""
                        . __("Backup des fichiers de test vers le backup upload privé OK",
                        'monwoo-web-agency-config'/** 📜*/));
                        wp_redirect( remove_query_arg([
                            'wa-bckup-a-tests'
                        ]) );
                        $this->exit(); return;
                    }
                    if ($shouldLoadATestBckup) {
                        if (file_exists($acceptanceUploadFolder)) {
                            copy_dir(
                                "$acceptanceUploadFolder",
                                "$acceptanceSrcFolder"
                            );
                            Notice::displaySuccess(""
                            . __("Chargement des fichiers de test depuis le backup upload OK",
                            'monwoo-web-agency-config'/** 📜*/));
                        } else {
                            Notice::displayError(""
                            . __("Aucun backup de tests dans l'upload privé du site",
                            'monwoo-web-agency-config'/** 📜*/));
                        }
                        wp_redirect( remove_query_arg([
                            'wa-load-a-tests-bckup'
                        ]) );
                        $this->exit(); return;
                    }
                    $acceptanceTestsFolder = $this->pluginRoot . 'tests/acceptance';
                    $aTests = list_files($acceptanceTestsFolder);
                    $pFile = basename($this->pluginRoot) . "/" . basename($this->pluginFile);
                    $pFileEncoded = urlencode($pFile);
                    foreach ($aTests as $testFile) {
                        $testFile = str_replace(
                            $this->pluginRoot,
                            basename($this->pluginRoot) . "/",
                            $testFile
                        );
                        $encodedFile = urlencode($testFile);
                        echo wp_kses_post("<p>[$this->iId] <a
                        href='$siteUrl/wp-admin/plugin-editor.php?file=$encodedFile&plugin=$pFileEncoded'
                        >Edit <strong>$testFile</strong> by clicking this link.</a></p>");
                    }
                    $reportPath = "{$this->pluginRoot}tests/_output/results.html";
                    $reportUrl = plugins_url('tests/_output/results.html', $this->pluginFile);
                    if (file_exists($reportPath)) {
                        echo wp_kses_post("<p>[$this->iId] <strong><a
                        href='$reportUrl'
                        target='_blank'
                        rel='noopener noreferrer'
                        >" . __(
                            "Cliquer ici pour visualiser le dernier rapport de test effectué",
                            'monwoo-web-agency-config'/** 📜*/
                        ) . "</a></strong></p>");    
                    }
                    $currentDirectory = getcwd();
                    chdir($this->pluginRoot);
                    echo wp_kses_post("<p>[$this->iId] With config file $aTestConfigSubPath</p>");
                    $aTestBaseUrl = $this->getWaConfigOption(
                        $this->eConfOptATestsBaseUrl,
                        site_url()
                    );
                    $updatedConfig = "";
                    $lineFilter = [
                        'matchPreviousLine' => '/- PhpBrowser:/',
                        'onMatch' => function ($line) use ($aTestBaseUrl) {
                            return "            url: $aTestBaseUrl\n";
                        },
                    ];
                    $handle = fopen($aTestConfigFile, "r");
                    if ($handle) {
                        $didMatchPreviousLine = false;
                        while (($line = fgets($handle)) !== false) {
                            $updatedConfig .= $didMatchPreviousLine
                                ? $lineFilter['onMatch']($line)
                                : $line;
                            $didMatchPreviousLine = preg_match(
                                $lineFilter['matchPreviousLine'],
                                $line
                            );
                        }
                        fclose($handle);
                    } else {
                        $this->err("wa-config fail to load acceptance config test file $aTestConfigFile");
                        echo wp_kses_post("<p> "
                            . __(
                                "Echec du chargement du fichier de configuration : " . $aTestConfigFile,
                                'monwoo-web-agency-config'/** 📜*/
                            )
                            . "</p>");
                    }
                    echo wp_kses_post("<pre>$updatedConfig</pre>");
                    file_put_contents($aTestConfigFile, $updatedConfig);
                }
                $runCodecept = filter_var( sanitize_text_field($_GET['run-codecept'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                $runLink = add_query_arg([
                    'run-codecept' => true,
                ], $current_url);
                echo wp_kses_post($this->test_e2e_get_available_actions()); 
                if (!$this->test_e2e_is_codecept_available()) {
                    return; 
                }
                if (!$runCodecept) {
                    echo wp_kses_post("<h1><a href='$runLink'>" . __(
                        "Cliquer ici pour lancer les tests",
                        'monwoo-web-agency-config'/** 📜*/
                    )
                        . "</a></h1>");
                    echo wp_kses_post("<p><strong> "
                        . __(
                            "ATTENTION : Ces tests sont lancés sur l'url de production : ",
                            'monwoo-web-agency-config'/** 📜*/
                        ) . "<br />$aTestBaseUrl"
                        . "</strong></p>");
                    echo wp_kses_post("<h2> "
                        . __(
                            "Prenons soin des données de productions. Utilisons une solution de backup ou de rollback dans la mise en oeuvre des tests.",
                            'monwoo-web-agency-config'/** 📜*/
                        )
                        . "</h2>");
                    return; 
                }
                if (!is_admin() || !current_user_can($minimumCapabilityToRun)) {
                    $this->err("wa-config TEST RUN can be done by $minimumCapabilityToRun only.");
                    echo wp_kses_post("<p> " . __(
                        "Cette opérations ADMIN nécessite une capacité :",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . " $minimumCapabilityToRun </p>");
                    return;
                }
                echo wp_kses_post("<p>[$this->iId] Running acceptance tests from {$this->pluginName}</p>");
                $pharName = 'codecept.phar';
                $pharPath = $this->pluginRoot . "tools/$pharName";
                try {
                    $p = new \Phar(
                        $pharPath,
                        \FilesystemIterator::CURRENT_AS_FILEINFO
                            | \FilesystemIterator::KEY_AS_FILENAME,
                        $pharName
                    );
                } catch (\UnexpectedValueException $e) {
                    $this->err("FAIL $pharName at $pharPath");
                    die("Could not open $pharName");
                } catch (\BadMethodCallException $e) {
                    echo 'technically, this cannot happen';
                }
                if (file_exists($this->pluginRoot . "vendor")) {
                    rename($this->pluginRoot . "vendor", $this->pluginRoot . "_vendor");
                }
                $autoloadFile = 'phar://codecept.phar/vendor/codeception/codeception/autoload.php';
                /**
                 * Require codeception autoload from tools/codecept.phar 
                 */
                require_once $autoloadFile;
                set_time_limit(30*60); 
                $Codecept = new \Codeception\Codecept(array(
                    'steps' => true,
                    'verbosity' => $this->shouldDebug ? 2 : 1,
                    'seed' => time(), 
                    'html' => 'results.html',
                    'colors' => false, 
                    'no-redirect' => true,
                    'silent' => false,
                    'interactive' => false,
                ));
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

                echo "<iframe src='". esc_attr($reportUrl) . "' 
                scrolling='no' marginwidth='0' marginheight='0' vspace='0' hspace='0'
                frameborder='0' onload='resizeCodeceptIframe(this)'></iframe>";
            }
            /**
             * Output a suggestion list from all available 'review' data key
             * 
             * Used for ajax suggestion lists. Echo one sugestion per line.
             * 
             * GET parameters :
             *  - **wa-iid** : The instance identifier requesting the suggest list
             *  - **key** : The 'review' data key source for the suggestion list
             *  - **q** : The query used to filter the suggestions (end user search input)
             *
             * @see https://mwop.net/blog/253-Taming-SplPriorityQueue.html
             */
            public function e_review_list_data_by_key() : void {
                $selfIid = filter_var( sanitize_text_field($_GET['wa-iid'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                $selfRequestTarget = self::instanceByIId($selfIid);
                $this->assertLog($selfRequestTarget, "Wrong 'wa-iid' hidden field", $selfIid);
                $self = $selfIid ? ($selfRequestTarget ?? $this) : $this;
                if (!is_admin()) {
                    $self->err("wa-config admin review section is under admin pages only");
                    echo "<p> "
                        . __(
                            "Cette opération nécessite une page d'administration.",
                            'monwoo-web-agency-config'/** 📜*/
                        )
                        . "</p>";
                    return;
                }
                $key = filter_var( sanitize_text_field($_GET['key'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                $query = filter_var( sanitize_text_field($_GET['q'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
                $query = wp_unslash( $query );
                if (!$self->_reviewsByKeySearchCache) {
                    $self->_reviewsByKeySearchCache = get_transient( 
                        $self->_reviewsByKeySearchCacheKey
                    );
                }
                if (!$self->_reviewsByKeySearchCache) {
                    $self->_reviewsByKeySearchCache = [];
                }
                if (!array_key_exists(
                    $key,
                    $self->_reviewsByKeySearchCache
                )) {
                    $self->_reviewsByKeySearchCache[$key] = [];
                }
                if (array_key_exists($query, $self->_reviewsByKeySearchCache[$key])) {
                    $self->debug("e_review_list_data_by_key loaded from cache [$key][$query]");
                    echo wp_kses_post($self->_reviewsByKeySearchCache[$key][$query]);
                    $self->exit(); return;
                }
                $datas = [];
                foreach ($self->eReviewChecksByCategoryByTitle as $category => $checksByTitle) {
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
                $self->_reviewsByKeySearchCache[$key][$query] = $searchResult;
                set_transient( 
                    $self->_reviewsByKeySearchCacheKey,
                    $self->_reviewsByKeySearchCache,
                    24 * 60 * 60 
                );
                echo wp_kses_post($searchResult);
                $self->exit(); return;
            }
            protected function e_review_settings_render_form() {
                $self = $this;
                $formFields = function () use ($self) {
                    settings_fields($self->eReviewSettingsFormGroup);
                };
                $sectionFormFields = function () use ($self) {
                    do_settings_sections($self->eReviewPageKey);
                };
                $submitBtn = function () {
                    submit_button(
                        __('Ajouter le checkpoint', 'monwoo-web-agency-config'/** 📜*/)
                        . ' ', 
                        'primary large wa-add-page-btn-icon',
                        'submit',
                        false
                    );
                };
                ?>
                    <form method="post" action="options.php" id="wa_config_review_add_checkpoint"> 
                        <?php $formFields() ?>
                        <?php $sectionFormFields() ?>
                        <p class="submit">
                            <?php $submitBtn() ?>
                            <!--span class="dashicons dashicons-welcome-add-page"></span-->
                        </p>
                    </form>
                <?php
            }
            /**
             * Validator used to validate saved 'review' options for wa-config
             *
             * @param mixed $input wa-config review option to validate
             * @return mixed the new input after validator review
             */
            public function e_review_settings_validate($input)
            {
                $input = _wp_json_sanity_check($input, 42);
                if (!$input) {
                    $this->debug("e_review_settings_validate on null, avoiding validate ...");
                    return $input;
                }
                $selfIid = $input['wa_instance_iid'] ?? false;
                $selfRequestTarget = self::instanceByIId($selfIid);
                $this->assertLog($selfRequestTarget, "Wrong 'wa_instance_iid' hidden field", $selfIid);
                $self = $selfIid ? ($selfRequestTarget ?? $this) : $this;
                $newinput = $input;
                $self->debugVerbose("Will e_review_settings_validate");
                $booleanAdaptor = function($fieldName) use ( & $newinput ) {
                    $newinput[$fieldName] = intval(
                        array_key_exists($fieldName, $newinput) ? $newinput[$fieldName] : false
                    );
                };
                $booleanAdaptor($self->eConfOptReviewIsActivated);
                $booleanAdaptor($self->eConfOptReviewResult);
                $self->debugVeryVerbose(
                    "Validated e_review_settings_validate input",
                    array_keys($input), array_keys($newinput),
                );
                return $newinput;
            }
            /**
             * Capability filter for review option page edits
             *
             * @param string $capability initial allowed capability
             * @return string the capability allowed to update review options
             */
            public function e_review_settings_page_capability($capability)
            {
                return $this->eReviewSettingsEditCabability;
            }
            /**
             * Filters the review form options from our eReviewSettingsForm before it get's updated.
             *
             * Used as a data provider for the eReviewChecksByCategoryByTitle
             * stored in our eReviewDataStore
             *
             * @param mixed  $value     The new, unserialized option value.
             * @param mixed  $old_value The old option value.
             * @param string $option    Option name.
             * @return mixed  The new, unserialized option value.
             */
            public function e_review_settings_pre_update_filter($value, $old_value, $option) {
                if (!$value) {
                    $this->debug("e_review_settings_pre_update_filter on null, avoiding pre_update ...");
                    return $old_value;
                }
                $value = _wp_json_sanity_check($value, 42);
                $selfIid = $value['wa_instance_iid'] ?? false;
                $selfRequestTarget = self::instanceByIId($selfIid);
                $this->assertLog($selfRequestTarget, "Wrong 'wa_instance_iid' hidden field", $selfIid);
                $self = $selfIid ? ($selfRequestTarget ?? $this) : $this;
                $self->debugVerbose("Will e_review_settings_pre_update_filter on $option");
                if (!is_admin()) { 
                    $self->err("wa-config e_review_settings_pre_update_filter need admin page.");
                    echo "<p> " . __(
                        "Cette opérations nécessite une page admin",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . "</p>";
                    return;
                }
                if ($self->_eReviewSettingsPreUpdateSelfSentinel) {
                    $self->warn(
                        'Sentinel still needed ? // TODO : refactor code to avoid _eReviewSettingsPreUpdateSelfSentinel ?'
                    );
                    return $value; 
                }
                $self->_eReviewSettingsPreUpdateSelfSentinel = true;
                $self->debugVeryVerbose("e_review_settings_pre_update_filter From", $old_value, $value);
                $checkpointValue = [
                    'category' => $value[$self->eConfOptReviewCategory] ?? null,
                    'category_icon' => $value[$self->eConfOptReviewCategoryIcon] ?? null,
                    'title' => $value[$self->eConfOptReviewTitle] ?? null,
                    'title_icon' => $value[$self->eConfOptReviewTitleIcon] ?? null,
                    'requirements' => $value[$self->eConfOptReviewRequirements] ?? null,
                    'value' => $value[$self->eConfOptReviewValue] ?? null,
                    'result' => $value[$self->eConfOptReviewResult] ?? null,
                    'access_cap_or_role' => $value[$self->eConfOptReviewAccessCapOrRole] ?? null,
                    'is_activated' => $value[$self->eConfOptReviewIsActivated] ?? null,
                ];
                $self->debugVeryVerbose("e_review_settings_pre_update_filter will add checkpoint", [
                    'checkpoint' => $checkpointValue,
                    'value' => $value,
                ]);
                if (!strlen($checkpointValue['category'])) {
                    $self->err("WRONG .checkpoint, missing 'category'");
                    $self->debug("WRONG value : ", $value, $self->debug_trace());
                    Notice::displayError(__("Echec de l'enregistrement de la revue.", 'monwoo-web-agency-config'/** 📜*/));
                    return $value; 
                }
                $self->e_review_data_check_insert($checkpointValue);
                $value[$self->eConfOptReviewCategory] = ""; 
                $value[$self->eConfOptReviewCategoryIcon] = ""; 
                $value[$self->eConfOptReviewTitle] = "";
                $value[$self->eConfOptReviewTitleIcon] = "";
                $value[$self->eConfOptReviewRequirements] = "";
                $value[$self->eConfOptReviewValue] = "";
                $value[$self->eConfOptReviewResult] = true;
                $value[$self->eConfOptReviewAccessCapOrRole] = "";
                $value[$self->eConfOptReviewIsActivated] = true;
                $value[$self->eConfOptReviewsByCategorieByTitle]
                = $self->eReviewChecksByCategoryByTitle;
                Notice::displaySuccess(__('Enregistrement de la revue OK.', 'monwoo-web-agency-config'/** 📜*/));
                delete_transient($self->_reviewsByKeySearchCacheKey);
                $self->_reviewsByKeySearchCacheKey = null;
                $self->_eReviewSettingsPreUpdateSelfSentinel = false;
                wp_cache_delete("alloptions", "options"); 
                return $value;
            }
            protected function e_review_settings_fetch_field($key, $default)
            {
                $this->debugVeryVerbose("Will e_review_settings_fetch_field $key");
                $this->eReviewSettingsForm = get_option($this->eReviewSettingsFormKey, array_merge([
                    $key => $default,
                ], $this->eReviewSettingsForm));
                if (!is_array($this->eReviewSettingsForm)){
                    $this->warn("Having wrong datatype saved for $key, fixing it to empty array", $this->eReviewSettingsForm);
                    $this->eReviewSettingsForm = [];
                }
                if (!key_exists($key, $this->eReviewSettingsForm)) {
                    $this->eReviewSettingsForm[$key] = $default;
                    update_option($this->eReviewSettingsFormKey, $this->eReviewSettingsForm)
                    ;
                }
                $value = $this->eReviewSettingsForm[$key]; 
                $this->debugVeryVerbose("Did e_review_settings_fetch_field $key", $value);
                return $value;
            }
            protected function e_review_settings_add_field($key, $title, $default = '', $template = null, ...$tArgs): void
            {
                $this->debugVeryVerbose("Will e_review_settings_add_field");
                $fieldId = "{$this->eReviewSettingsFormKey}_$key";
                $fieldName = "{$this->eReviewSettingsFormKey}[$key]";
                $value = $this->e_review_settings_fetch_field($key, $default);
                $safeValue = $value; 
                add_settings_field(
                    $fieldId,
                    $title,
                    function () use ($tArgs, $safeValue, $fieldId, $fieldName, $template) {
                        if ($template) {
                            wa_render($template($safeValue, $fieldId, $fieldName, "", ...$tArgs));
                        } else {
                            ?>
                                <input id='<?php echo esc_attr($fieldId) ?>' type='text'
                                name='<?php echo esc_attr($fieldName) ?>'
                                value='<?php echo wp_kses_post($safeValue) ?>'
                                />
                            <?php
                        }
                    },
                    $this->eReviewPageKey,
                    $this->eReviewSettingsFormSection,
                );
            }
            /**
             * Add the 'WA Config' 'Review' panel.
             */
            public function e_review_settings_do_admin_menu(): void
            {
                $titleSuffix = " " . $this->iPrefix
                . ($this->iRelativeIndex ? " {$this->iRelativeIndex}" : "");
                $this->e_config_add_section(
                    '<span class="dashicons dashicons-performance"></span> '
                    . (
                        $this->iIndex
                        ? __('R.Q.', 'monwoo-web-agency-config'/** 📜*/) . "$titleSuffix"
                        : __('Revue qualité', 'monwoo-web-agency-config'/** 📜*/)
                    ),
                    [$this, 'e_review_render_admin_panel'],
                    $this->eReviewPageKey,
                    $this->e_config_count_submenu(),
                    $this->baseCabability,
                );
            }
            /**
             * Initialise settings section, form fields and options
             */
            public function e_review_settings_init_form() : void {
                $pageId = filter_var( sanitize_text_field($_GET['page'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS);
                $this->debugVerbose("Will e_review_settings_init_form");
                extract($this->e_config_form_field_templates());
                register_setting(
                    $this->eReviewSettingsFormGroup,
                    $this->eReviewSettingsFormKey,
                    [$this, 'e_review_settings_validate']
                );
                add_option($this->eReviewSettingsFormKey, $this->eReviewSettingsForm); 
                add_option($this->eReviewDataStoreKey, $this->eReviewDataStore); 
                add_settings_section(
                    $this->eReviewSettingsFormSection,
                    __('Ajouter une revue', 'monwoo-web-agency-config'/** 📜*/),
                    '',
                    $this->eReviewPageKey,
                );
                if (current_user_can($this->eReviewSettingsEditCabability)) {
                    $fieldId = "{$this->eReviewSettingsFormKey}_wa_instance_iid";
                    $fieldName = "{$this->eReviewSettingsFormKey}[wa_instance_iid]";
                    $value = $this->iId;
                    $safeValue = esc_attr($value);
                    add_settings_field(
                        $fieldId,
                        '', 
                        function () use ($hiddenTemplate, $safeValue, $fieldId, $fieldName) {
                            wa_render( $hiddenTemplate($safeValue, $fieldId, $fieldName) );
                        },
                        $this->eReviewPageKey,
                        $this->eReviewSettingsFormSection,
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewCategory,
                        __("Categorie", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['category'],
                        [$this, 'e_review_settings_suggestionbox_template_by_check_data'],
                        'category'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewCategoryIcon,
                        __("Icône de categorie", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['category_icon'],
                        [$this, 'e_review_settings_suggestionbox_template_by_check_data'],
                        'category_icon'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewTitle,
                        __("Titre", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['title'],
                        [$this, 'e_review_settings_suggestionbox_template_by_check_data'],
                        'title'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewTitleIcon,
                        __("Icône de titre", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['title_icon'],
                        [$this, 'e_review_settings_suggestionbox_template_by_check_data'],
                        'title_icon'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewRequirements,
                        __("Exigences", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['requirements'],
                        $textareaTemplate,
                        'requirements'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewResult,
                        __("Résultat", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['result'],
                        $checkboxTemplate,
                        'result'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewValue,
                        __("Valeur (optionnel)", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['value'],
                        [$this, 'e_review_settings_suggestionbox_template_by_check_data'],
                        'value'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewIsActivated,
                        __("Activer la revue", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['is_activated'],
                        $checkboxTemplate,
                        'is_activated'
                    );
                    $this->e_review_settings_add_field(
                        $this->eConfOptReviewAccessCapOrRole,
                        __("Limiter l'accès", 'monwoo-web-agency-config'/** 📜*/),
                        $this->eReviewDefaultCheckpoint['access_cap_or_role'],
                        [$this, 'e_config_capability_selectbox_template'],
                        'access_cap_or_role'
                    );
                }
            }
            protected function e_review_settings_suggestionbox_template_by_check_data(
                $safeValue, $fieldId, $fieldName, $placeholder, $key
            ) {
                return function()
                use ( & $fieldId, & $fieldName, & $placeholder, & $safeValue, & $key ) { ?>
                    <input
                    type='text'
                    placeholder="<?php echo esc_attr($placeholder) ?>"
                    class="wa-suggest-list-review-data-by-<?php echo esc_attr($key) ?>"
                    id="<?php echo esc_attr($fieldId) ?>"
                    name="<?php echo esc_attr($fieldName) ?>"
                    value="<?php echo wp_kses_post($safeValue) ?>"
                    />
                <?php };
            }
            /**
             * Filters the review data options from our internal review Data pre-updates
             *
             * Filter the eReviewDataStore
             *
             * @param mixed  $value     The new, unserialized option value.
             * @param mixed  $old_value The old option value.
             * @param string $option    Option name.
             * @return mixed  The new, unserialized option value.
             */
            public function e_review_data_pre_update_filter($value, $old_value, $option) {
                if (!$value) {
                    $this->debug("e_review_data_pre_update_filter on null, avoiding pre_update ...");
                    return $old_value;
                }
                $value = _wp_json_sanity_check($value, 42);
                $self = $this;
                $self->debugVerbose("Will e_review_data_pre_update_filter on $option");
                if (!is_admin()) { 
                    $self->err("wa-config e_review_data_pre_update_filter need admin page.");
                    echo "<p> " . __(
                        "Cette opérations nécessite une page admin",
                        'monwoo-web-agency-config'/** 📜*/
                    ) . "</p>";
                    return;
                }
                if ($self->_eReviewDataPreUpdateSelfSentinel) {
                    $self->warn(
                        'Sentinel still needed ? // TODO : refactor code to avoid _eReviewDataPreUpdateSelfSentinel ?'
                    );
                    return $value; 
                }
                $self->_eReviewDataPreUpdateSelfSentinel = true;
                $self->debugVeryVerbose("e_review_data_pre_update_filter From", $old_value, $value);
                $this->eReviewChecksByCategoryByTitle = $this->e_review_data_fetch(
                    $this->eConfOptReviewsByCategorieByTitle, []
                );
                $this->eReviewDataStore = get_option($this->eReviewDataStoreKey, $this->eReviewDataStore);
                $eReviewChecksByCategoryByTitle = & $value[$this->eConfOptReviewsByCategorieByTitle] ?? [];
                foreach ($eReviewChecksByCategoryByTitle as $c => &$eReviewChecksByTitle) {
                    foreach ($eReviewChecksByTitle as $t => &$checkBulk) {
                        usort($checkBulk, function ($c1, $c2) {
                            $c1Key = intval(boolVal($c1['is_activated']))
                            . '-' . intval(!boolVal($c1['is_computed']))
                            . '-' . $c1['create_time'];
                            $c2Key = intval(boolVal($c2['is_activated']))
                            . '-' . intval(!boolVal($c2['is_computed']))
                            . '-' . $c2['create_time'];
                            return strnatcasecmp($c2Key, $c1Key);
                        });        
                    }
                    ksort(
                        $eReviewChecksByTitle,
                        SORT_NATURAL | SORT_FLAG_CASE
                    );
                }
                ksort(
                    $eReviewChecksByCategoryByTitle,
                    SORT_NATURAL | SORT_FLAG_CASE
                );
                /**
                 * @see WPFilters::wa_base_review_ids_to_trash
                 */
                $eReviewIdsToTrash = apply_filters(
                    WPFilters::wa_base_review_ids_to_trash,
                    $this->eReviewIdsToTrash,
                    $this
                );
                $deleteds = $this->eReviewDataStore[$this->eConfOptReviewsDeleted] ?? [];
                foreach ($eReviewIdsToTrash as $toTrash) {
                    $trashId = $toTrash['id'] ?? $toTrash;
                    $this->assert(
                        is_string($trashId),
                        "Missing ID in eReviewIdsToTrash for trash operation ?"
                    );
                    $trashSafeCategories = $toTrash['not_for_categories'] ?? null;
                    $trashSafeTitles = $toTrash['not_for_titles'] ?? null;
                    if (is_string($trashSafeCategories)) {
                        $trashSafeCategories = [$trashSafeCategories];
                    }
                    if (is_string($trashSafeTitles)) {
                        $trashSafeTitles = [$trashSafeTitles];
                    }
                    $this->assert(
                        !$trashSafeCategories || is_array($trashSafeCategories),
                        "Wrong type for trashSafeCategories", $trashSafeCategories
                    );
                    $this->assert(
                        !$trashSafeTitles || is_array($trashSafeTitles),
                        "Wrong type for trashSafeTitles", $trashSafeTitles
                    );
                    if ($trashSafeCategories && !count($trashSafeCategories)) {
                        $trashSafeCategories = null;
                    }
                    if ($trashSafeTitles && !count($trashSafeTitles)) {
                        $trashSafeTitles = null;
                    }
                    $categoryTrash = [];
                    foreach ($eReviewChecksByCategoryByTitle as $c => & $checksByTitle) {
                        $categoryIsSafe = $trashSafeCategories && in_array($c, $trashSafeCategories);
                        $titleTrash = [];
                        foreach ($checksByTitle as $t => & $checks) {
                            $titleIsSafe = $categoryIsSafe 
                            && $trashSafeTitles
                            && in_array($t, $trashSafeTitles);
                            $duplicatedIds = [];
                            $checks = array_filter(
                                $checks,
                                function (& $check)
                                use ($trashId, $titleIsSafe, & $duplicatedIds, & $deleteds) {
                                    $checkId = $check['fixed_id'];
                                    if (!$this->e_review_data_check_isReadable($check)) {
                                        $this->debug("e_review_data_add_base_review trash not accessible for : $checkId");
                                        return true;
                                    }
                                    if (!$titleIsSafe
                                    && $trashId === $checkId) {
                                        $check['is_deleted'] = true;
                                        if (!$check['is_computed']) {
                                            $deleteds[] = $check;                
                                        }
                                        return false;
                                    }
                                    if (array_key_exists($checkId, $duplicatedIds)) {
                                        $check['is_deleted'] = true;
                                        if (!$check['is_computed']) {
                                            $deleteds[] = $check; 
                                        }
                                        return false;
                                    }
                                    $duplicatedIds[$checkId] = true;
                                    return true;
                                }
                            );
                            if (!count($checks)) {
                                $titleTrash[] = $t;
                            }
                        }
                        foreach ($titleTrash as $t) {
                            unset($checksByTitle[$t]);
                        }
                        if (!count($checksByTitle)) {
                            $categoryTrash[] = $c;
                        }
                    }
                    foreach ($categoryTrash as $c) {
                        unset($eReviewChecksByCategoryByTitle[$c]);
                    }
                }
                $this->eReviewIdsToTrash = [];
                $this->debugVeryVerbose("Trashed wa-reviews", $deleteds);
                $this->eReviewDataStore[$this->eConfOptReviewsDeleted] = $deleteds;
                $value[$this->eConfOptReviewsByCategorieByTitle] = $eReviewChecksByCategoryByTitle;
                $self->_eReviewDataPreUpdateSelfSentinel = false;
                return $value;
            }
            protected function e_review_data_fetch($key, $default)
            {
                $this->debugVeryVerbose("Will e_review_data_fetch $key");
                $this->eReviewDataStore = get_option($this->eReviewDataStoreKey, array_merge([
                    $key => $default,
                ], $this->eReviewDataStore));
                if (!is_array($this->eReviewDataStore)){
                    $this->warn("Having wrong datatype saved for $key, fixing it to empty array", $this->eReviewDataStore);
                    $this->eReviewDataStore = [];
                }
                if (!key_exists($key, $this->eReviewDataStore)) {
                    $this->eReviewDataStore[$key] = $default;
                    update_option($this->eReviewDataStoreKey, $this->eReviewDataStore)
                    ;
                }
                $value = $this->eReviewDataStore[$key]; 
                $this->debugVeryVerbose("Did e_review_data_fetch $key", $value);
                return $value;
            }
            /**
             * Launch the review action received by HTML-POST contents.
             * 
             * REQUEST parameters :
             *  - **wa-iid** : The instance identifier requesting the action
             *  - **wa-data** : Associated data for the action
             *  - **wa-action** : The action to launch
             *     - **'checkpoint-activate-toggler'** : Toggle activation
             *     status of the 'is_activated' review data key
             *     - **'delete-checkpoint'** : Delete the targeted
             *     check review by 'wa-data'
             *     - **'clean-all'** : Clean up the whole review datastore.
             *     Delete all current and deleted review data.
             *     - **'export-csv'** : Export all available check review data
             *     - **'import-csv'** : Import all available check review data
             */
            public function e_review_data_action(): void
            {
                if (!is_user_logged_in()) {
                    $this->err("wa-config e_review_data_action is under logged users only");
                    wp_loginout();
                    $this->exit(); return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $anonimizedIp = $this->get_user_ip();
                $selfIid = filter_var( sanitize_text_field($_REQUEST['wa-iid'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                $selfRequestTarget = self::instanceByIId($selfIid);
                $this->assertLog($selfRequestTarget, "Wrong 'wa-iid' parameter", $selfIid);
                $self = $selfIid ? ($selfRequestTarget ?? $this) : $this;
                $action = filter_var( sanitize_key($_REQUEST['wa-action'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                $checkPOST = filter_var( sanitize_text_field($_REQUEST['wa-data'] ?? null), FILTER_SANITIZE_SPECIAL_CHARS );
                $checkJson = base64_decode($checkPOST);
                $check = _wp_json_sanity_check(json_decode($checkJson, true), 42);
                $checkKey = "";
                if ($check) {
                    $checkKey = $self->fetch_review_key_id($check);
                }
                $self->debug("Will e_review_data_action '$action' from '$checkKey' by '$anonimizedIp'");
                if (false === check_ajax_referer(
                    "wa-check-nonce-$checkKey",
                    'wa-nonce',
                    false
                ) || !is_admin()) {
                    $self->err("Invalid access for $anonimizedIp");
                    echo wp_json_encode([
                        "error" => "[$anonimizedIp] "
                        . __("IP enregistrée suite à accès invalid", 'monwoo-web-agency-config'/** 📜*/),
                    ]);
                    http_response_code(401);
                    $self->exit(); return;
                }
                $self->eReviewChecksByCategoryByTitle = $self->e_review_data_fetch(
                    $self->eConfOptReviewsByCategorieByTitle, []
                );
                switch ($action) {
                    case 'checkpoint-activate-toggler': {
                        $checksByTitle = & $self->eReviewChecksByCategoryByTitle[$check['category']];
                        $checks = & $checksByTitle[$check['title']];
                        $targets = array_filter($checks, function ($c) use (&$checkKey, $self) {
                            $cKey = $self->fetch_review_key_id($c);
                            return $checkKey === $cKey;
                        });
                        $tCount = count($targets);
                        if ($tCount !== 1) {                            
                            $self->err("Invalid checkpoint '$checkKey' for $anonimizedIp");
                            $self->debug("'$checkKey' not found or too much duplicata ($tCount) in ", $checks);
                            echo wp_json_encode([
                                "error" => __("Specific checkpoint not found", 'monwoo-web-agency-config'/** 📜*/),
                                "wa-data" => $check,
                                "count" => $tCount,
                            ]);
                            http_response_code(404);
                            $self->exit(); return;
                        }
                        $lookupIdx = array_keys($targets)[0];
                        $toggeled = & $checks[$lookupIdx];
                        if (!$self->e_review_data_check_isWriteable($toggeled)) {
                            $self->err("Invalid checkpoint access '{$checkKey}' for {$anonimizedIp}");
                            $self->debug("'{$checkKey}' not accessible in ", $checks);
                            echo wp_json_encode([
                                "error" => "[$checkKey] "
                                . __("Specific checkpoint not accessible", 'monwoo-web-agency-config'/** 📜*/),
                                "wa-data" => $check
                            ]);
                            http_response_code(404);
                            $self->exit(); return;
                        }
                        $toggeled['is_activated'] = !$toggeled['is_activated'];
                        $self->eReviewDataStore[$self->eConfOptReviewsByCategorieByTitle]
                        = $self->eReviewChecksByCategoryByTitle;
                        update_option($self->eReviewDataStoreKey, $self->eReviewDataStore);
                        delete_transient($self->_reviewsByKeySearchCacheKey);
                        $self->_reviewsByKeySearchCacheKey = null;
                        $self->debugVerbose("Did activate toggle from '$action' for '$checkKey'"); 
                    } break;
                    case 'delete-checkpoint': {
                        $cCat = $check['category'] ?? null; $cT = $check['title'] ?? null;
                        $self->debugVeryVerbose("Will delete checkpoint [$cCat][$cT] ", $check, array_keys($self->eReviewChecksByCategoryByTitle));
                        $checksByTitle = & $self->eReviewChecksByCategoryByTitle[$cCat];
                        $checks = & $checksByTitle[$cT] ?? [];
                        $targets = array_filter($checks, function ($c) use (&$checkKey, $self) {
                            $cKey = $self->fetch_review_key_id($c);
                            return $checkKey === $cKey;
                        });
                        $tCount = count($targets);
                        if ($tCount !== 1) {                            
                            $self->err("Invalid checkpoint '$checkKey' for $anonimizedIp");
                            $self->debug("'$checkKey' not found or too much duplicata ($tCount) in ", $checks);
                            echo wp_json_encode([
                                "error" => "Specific checkpoint not found",
                                "wa-data" => $check,
                                "count" => $tCount,
                            ]);
                            http_response_code(404);
                            $self->exit(); return;
                        }
                        $self->debug("Will '$action' for '$checkKey'");
                        $deleteds = $self->e_review_data_fetch($self->eConfOptReviewsDeleted, []);
                        $lookupIdx = array_keys($targets)[0];
                        if (!$self->e_review_data_check_isWriteable($checks[$lookupIdx])) {
                            $self->err("Invalid checkpoint access '{$checkKey}' for {$anonimizedIp}");
                            $self->debug("'{$checkKey}' not accessible in ", $checks);
                            echo wp_json_encode([
                                "error" => "[$checkKey] "
                                . __("Specific checkpoint not accessible", 'monwoo-web-agency-config'/** 📜*/),
                                "wa-data" => $check
                            ]);
                            http_response_code(404);
                            $self->exit(); return;
                        }
                        unset($checks[$lookupIdx]);
                        $self->eReviewDataStore[$self->eConfOptReviewsByCategorieByTitle]
                        = $self->eReviewChecksByCategoryByTitle;
                        $check['is_deleted'] = true;
                        $deleteds[] = $check;
                        $self->eReviewDataStore[$self->eConfOptReviewsDeleted] = $deleteds;
                        $self->debugVerbose("Review Options before delete", $self->eReviewDataStore);
                        update_option($self->eReviewDataStoreKey, $self->eReviewDataStore);
                        delete_transient($self->_reviewsByKeySearchCacheKey);
                        $self->_reviewsByKeySearchCacheKey = null;
                        $self->debugVerbose("Did delete checkpoint from '$action' for '$checkKey'");
                    } break;
                    case 'clean-all': {
                        if (current_user_can('administrator') ) {
                            $self->eReviewChecksByCategoryByTitle = [];
                            $self->eReviewDataStore[$self->eConfOptReviewsByCategorieByTitle]
                            = $self->eReviewChecksByCategoryByTitle;
                            delete_option($self->eReviewDataStoreKey);
                            delete_transient($self->_reviewsByKeySearchCacheKey);
                            $self->_reviewsByKeySearchCacheKey = null;
                            $self->debugVerbose("Did clean all review data from '$action'");
                        } else {
                            $self->err("Invalid access for $anonimizedIp, need to be administrator to clean all");
                            echo wp_json_encode([
                                "error" => "Invalid access for $anonimizedIp registred",
                            ]);
                            http_response_code(401);
                            $self->exit(); return;
                        }
                    } break;
                    case 'export-csv': {
                        ob_start();
                        $headerRow = array_keys($self->eReviewDefaultCheckpoint);
                        $dataRows = [];
                        if (current_user_can($self->optAdminEditCabability)) {
                            $checksByCategorieByTitle = $self->e_review_data_fetch($self->eConfOptReviewsByCategorieByTitle, []);
                        } else {
                            $checksByCategorieByTitle = $self->e_review_data_check_byCategoryByTitle();
                        }
                        foreach ( $checksByCategorieByTitle as $category => $checksByTitle ) {
                            foreach ( $checksByTitle as $title => $checks) {
                                foreach ($checks as $idx => $check) {
                                    $row = [];
                                    foreach ($headerRow as $hIndex) {
                                        $row[] = mb_convert_encoding($check[$hIndex], 'UTF-8');
                                    }
                                    $dataRows[] = $row;
                                }
                            }
                        }
                        if( current_user_can( 'administrator' ) ){
                            $deleteds = $self->e_review_data_fetch(
                                $self->eConfOptReviewsDeleted,
                                []
                            );
                            foreach ($deleteds as $d) {
                                $row = [];
                                foreach ($headerRow as $hIndex) {
                                    $row[] = mb_convert_encoding($d[$hIndex], 'UTF-8');
                                }
                                $dataRows[] = $row;
                            }
                        }
                        $siteSlug = sanitize_title(get_bloginfo( 'name' ));
                        $date = date("Ymd-His_O");
                        $filename = "$siteSlug-{$self->iId}-$date.csv";
                        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
                        header( 'Content-Description: File Transfer' );
                        header( 'Content-Type: text/csv; charset=utf-8' );
                        header( "Content-Disposition: attachment; filename={$filename}" );
                        header( 'Expires: 0' );
                        header( 'Pragma: public' );
                        $fh = fopen( 'php://output', 'w' );
                        fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) ); 
                        fputcsv( $fh, $headerRow );
                        foreach ( $dataRows as $dataRow ) {
                            fputcsv( $fh, $dataRow );
                        }
                        fclose( $fh );
                        ob_end_flush();
                        $self->debugVerbose("Did export reviews data as CSV from '$action'");
                        $self->exit(); return;
                    } break;
                    case 'import-csv': {
                        if (!current_user_can($self->eReviewSettingsEditCabability) ) {
                            $self->err("Invalid import access for {$anonimizedIp}");
                            $self->debug("'import-csv' not accessible");
                            echo wp_json_encode([
                                "error" => "[$checkKey] "
                                . __("'import-csv' not accessible, ip {$anonimizedIp} registred", 'monwoo-web-agency-config'/** 📜*/),
                            ]);
                            http_response_code(404);
                            $this->exit(); return;
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
                            $checkpointValue = array_map(function ($idx)
                            use ( & $headers, & $value ) {
                                $h = $headers[$idx];
                                return $value[$idx];
                            }, $hIdx);
                            $self->debugVerbose("e_review_data_action import-csv will add checkpoint");
                            $self->debugVeryVerbose("import-csv checkpoint :", [
                                'checkpoint' => $checkpointValue,
                                'from_value' => $value,
                            ]);
                            $user = wp_get_current_user();
                            $userName = $user->user_login;
                            if (current_user_can('administrator')
                            || current_user_can($self->optAdminEditCabability)
                            || $userName === ($checkpointValue['created_by'] ?? false)) {
                                $self->e_review_data_check_insert($checkpointValue, true);
                            } else {
                                $self->debugVerbose("Ignore checkpoint import since not accessible");
                            }
                        }
                        $self->debugVeryVerbose("WA Review options", $self->eReviewDataStore);
                        update_option($self->eReviewDataStoreKey, $self->eReviewDataStore);
                        delete_transient($self->_reviewsByKeySearchCacheKey);
                        $self->_reviewsByKeySearchCacheKey = null;
                        $redirectUrl = admin_url(
                            "admin.php?page={$self->eReviewPageKey}"
                        );
                        $self->debugVeryVerbose("After csv-import, will redirect to : $redirectUrl");
                        http_response_code(302); 
                        echo wp_kses_post("<a href='$redirectUrl'>Imports OK, retour à la revue en cours...</a>");
                        if ( wp_redirect( $redirectUrl ) ) { 
                            $this->exit(); return;
                        } else {
                            $self->debugVeryVerbose("csv-import Fail to redirect to : $redirectUrl");
                        }
                    } break;
                    default: {
                        $self->warn("Unknow action '$action'");
                    } break;
                }
                echo wp_json_encode([
                    "status" => "OK",
                    "end_date" => date("Y/m/d H:i:s O "),
                ]);
                http_response_code(200);
                $self->exit(); return;
            }
            protected function e_review_data_check_insert(array $toCheck, $importMode = false) {
                if (!is_user_logged_in()) {
                    $this->err("wa-config e_review_data_check_insert is under logged users only");
                    wp_loginout();
                    $this->exit(); return;
                }
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $toCheck = array_merge($this->eReviewDefaultCheckpoint, $toCheck);
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
                if (!array_key_exists($toCheck['category'],
                $this->eReviewChecksByCategoryByTitle)) {
                    $this->eReviewChecksByCategoryByTitle[$toCheck['category']] = [];
                }
                if (!array_key_exists($toCheck['title'],
                $this->eReviewChecksByCategoryByTitle[$toCheck['category']])) {
                    $this->eReviewChecksByCategoryByTitle[$toCheck['category']]
                    [$toCheck['title']] = [];
                }
                $checkBulk = & $this->eReviewChecksByCategoryByTitle
                [$toCheck['category']]
                [$toCheck['title']];
                $this->eReviewIdsToTrash = array_merge($this->eReviewIdsToTrash, [
                    [
                        'id' => $keyId,
                        'not_for_categories' => $toCheck['category'],
                        'not_for_titles' => $toCheck['title'],
                    ]
                ]);
                $checkBulk[] = $toCheck;
                if (strlen($toCheck['category_icon'] ?? '')) {
                    if (!array_key_exists($toCheck['category'],
                    $this->eReviewIconsByCategory)) {
                        $this->eReviewIconsByCategory[$toCheck['category']] = [];
                    }
                    array_unshift(
                        $this->eReviewIconsByCategory[$toCheck['category']],
                        $toCheck['category_icon']
                    );    
                }
                $this->eReviewChecksByKeyId[$keyId] = $toCheck;
                $this->eReviewDataStore[$this->eConfOptReviewsByCategorieByTitle]
                = $this->eReviewChecksByCategoryByTitle;
                update_option($this->eReviewDataStoreKey, $this->eReviewDataStore);
            }
            protected function e_review_data_check_isReadable( & $check) {
                $user = wp_get_current_user();
                $userName = $user->user_login;
                $canSentinel = $check['access_cap_or_role'] ?? false;
                return current_user_can('administrator')
                || $userName === $check['created_by']
                || $userName === $check['imported_by']
                || !$canSentinel
                || !strlen($canSentinel)
                || current_user_can($canSentinel);
            }
            protected function e_review_data_check_isWriteable( & $check) {
                $user = wp_get_current_user();
                $userName = $user->user_login;
                return current_user_can($this->optAdminEditCabability)
                || $userName === $check['created_by']
                || $userName === $check['imported_by'];
            }
            protected function e_review_data_check_byCategoryByTitle() {
                $checksByCategorieByTitle = [];
                foreach ($this->eReviewChecksByCategoryByTitle as $category => & $reviewsByTitle) {
                    foreach ($reviewsByTitle as $title => & $reviews) {
                        foreach ( $reviews as $idx => & $review ) {
                            if ($this->e_review_data_check_isReadable($review)) {
                                if (!array_key_exists($category, $checksByCategorieByTitle)) {
                                    $checksByCategorieByTitle[$category] = [];
                                }
                                $checksByTitle = & $checksByCategorieByTitle[$category];
                                if (!array_key_exists($title, $checksByTitle)) {
                                    $checksByTitle[$title] = [];
                                }
                                $checksBulk = & $checksByTitle[$title];
                                $checksBulk[] = $review;
                            } else {
                            }
                        }
                    }
                }
                return $checksByCategorieByTitle;
            }
            /**
             * Build and add the base review checkpoints, ensuring checked data
             * 
             * NEED to be called AFTER init hook (After Taxonomy register, etc...)
             * 
             * @see WPFilters::wa_base_review_ids_to_trash
             * @see WPActions::wa_do_base_review_preprocessing
             * @see WPActions::wa_do_base_review_postprocessing
             */
            public function e_review_data_add_base_review() : void {
                $this->debug("Will e_review_data_add_base_review");
                $this->eReviewChecksByCategoryByTitle = $this->e_review_data_fetch(
                    $this->eConfOptReviewsByCategorieByTitle, []
                );
                $app = $this;
                /**
                 * @see WPActions::wa_do_base_review_preprocessing
                 */
                do_action(WPActions::wa_do_base_review_preprocessing, $app);
                $this->e_review_data_check_insert([
                    'category' => __('01 - Critique', 'monwoo-web-agency-config'/** 📜*/),
                    'category_icon' => '<span class="dashicons dashicons-plugins-checked"></span>',
                    'title' => __('01 - Version de PHP', 'monwoo-web-agency-config'/** 📜*/),
                    'title_icon' => '<span class="dashicons dashicons-shield"></span>',
                    'requirements' => __( '7.4+ (7.4 or higher recommended)', 'monwoo-web-agency-config'/** 📜*/ ),
                    'value'    => "PHP " . PHP_VERSION,
                    'result'   => version_compare( PHP_VERSION, '7.4', '>' ),
                    'fixed_id' => "{$this->iId}-check-php-version",
                    'is_computed' => true,
                ]);
                $htaccessTest = $this->pluginRoot . "tests/external/EXT_TEST_htaccessIsEnabled.php";
                if (file_exists($htaccessTest)) {
                    $htaccessTestsFolder = 'tests';
                    $htaccessTestsBaseUrl = plugins_url($htaccessTestsFolder, $this->pluginFile);
                    require_once($htaccessTest);
                    $htaccessOK = \WA\Config\ExtTest\EXT_TEST_htaccessIsEnabled::check($htaccessTestsBaseUrl);
                    if (!$htaccessOK) {
                        foreach (\WA\Config\ExtTest\EXT_TEST_htaccessIsEnabled::$errors as $e) {
                            $this->err($e); 
                        }
                    }
                    $this->e_review_data_check_insert([
                        'category' => __('01 - Critique', 'monwoo-web-agency-config'/** 📜*/),
                        'title' => __('02 - Securisations .htaccess', 'monwoo-web-agency-config'/** 📜*/),
                        'title_icon' => '<span class="dashicons dashicons-shield"></span>',
                        'requirements' => __( 'Activation des redirections .htaccess', 'monwoo-web-agency-config'/** 📜*/ ),
                        'result'   => $htaccessOK,
                        'fixed_id' => "{$this->iId}-check-htaccess-ok",
                        'is_computed' => true,
                    ]);
                } else {
                    $this->eReviewIdsToTrash = array_merge($this->eReviewIdsToTrash, [
                        "{$this->iId}-check-htaccess-ok"
                    ]);
                }
                $report = "";
                $result = (function () use (& $report) {
                    $version = null;
                    $userAgent = strtolower(sanitize_text_field($_SERVER['HTTP_USER_AGENT']));
                    if (strrpos($userAgent, 'firefox') !== false) {
                        preg_match('/firefox\/([0-9]+\.*[0-9]*)/', $userAgent, $matches);
                        if (!empty($matches)) {
                            $version = explode('.', $matches[1])[0];
                            $report .= "Firefox $version";
                            return intval($version) >= 101; 
                        }
                    }
                    if (strrpos($userAgent, 'chrome') !== false) {
                        preg_match('/chrome\/([0-9]+\.*[0-9]*)/', $userAgent, $matches);
                        if (!empty($matches)) {
                            $version = explode('.', $matches[1])[0];
                            $report .= "Chrome $version";
                            return intval($version) >= 102; 
                        }
                    }
                    return false;
                }) ();
                $this->e_review_data_check_insert([
                    'category' => __('01 - Critique', 'monwoo-web-agency-config'/** 📜*/),
                    'title' => __('03 - Compatibilité', 'monwoo-web-agency-config'/** 📜*/),
                    'title_icon' => '<span class="dashicons dashicons-universal-access"></span>',
                    'requirements' => __( 'Navigateur compatible. Chrome > 102. Firefox > 101.',
                    'monwoo-web-agency-config'/** 📜*/ ),
                    'value'    => $report,
                    'result'   => $result,
                    'is_activated'   => true,
                    'fixed_id' => "{$this->iId}-check-chrome-version",
                    'is_computed' => true,
                ]);
                /**
                 * @see WPActions::wa_do_base_review_postprocessing
                 */
                do_action(WPActions::wa_do_base_review_postprocessing, $app);
                update_option($this->eReviewDataStoreKey, $this->eReviewDataStore);
                flush_rewrite_rules();
            }
        }
    }
    if (!trait_exists(ApiInstanciable::class)) { 
        /**
         * This trait will instanciate the 'wa-config' REST API and the related auth system
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         * @uses Identifiable
         */
        trait ApiInstanciable
        {
            use Editable;
            use Identifiable;
            protected function _020_api_inst__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_020_api_inst__bootstrap')) {
                    return; 
                }
                add_action( 'rest_api_init', [$this, 'api_inst_rest_init'] );
                if (is_admin()) {
                    add_action(
                        'wp_ajax_wa-delete-all-api-access', 
                        [$this, 'api_inst_admin_delete_all_api_access']
                    );
                    add_action(
                        WPActions::wa_ecp_render_after_parameters,
                        [$this, 'api_inst_print_access_report']
                    );
                }
                add_filter('query_vars', function ($aVars) {
                    $aVars[] = 'wa_api_pre_fetch_token';
                    return $aVars;
                });
                add_filter('rest_pre_dispatch', function($result, $server, $request) {
                    $this->debug("Having rest pre-dispatch : " . $request->get_route()); 
                    return $result;
                }, 10, 3);
                add_action('parse_request', [$this, 'api_inst_parse_request'] );
            }
            /**
             * 
             * Adding root secu request '/api-wa-config-nonce-rest' to website base url
             * 
             * REQUEST parameters {@see ApiInstanciable::api_inst_load_parameters()} :
             * 
    		 * @param WP $wp Current WordPress environment instance (passed by reference).
             */
            public function api_inst_parse_request(WP $wp) : void {
                $self = $this;
                $request = _wp_json_sanity_check(array_filter($_REQUEST, function ($c) {
                    return in_array($c, [
                        'wa_api_pre_fetch_token', 'wa_user_location', 'wa_access_id'
                    ]);
                }, ARRAY_FILTER_USE_KEY), 7);
                if (0 === strpos($wp->request, "api-wa-config-nonce-rest")) {
                    $this->debug("Will api_inst_parse_request");
                    $this->api_inst_load_parameters($request);
                    if (is_user_logged_in()) {
                        http_response_code(200); 
                        $quickCookie =  array_filter(_wp_json_sanity_check($_COOKIE, 7), function ($c) {
                            return in_array($c, [
                                SECURE_AUTH_COOKIE,
                                AUTH_COOKIE,
                                LOGGED_IN_COOKIE,
                                'PHPSESSID',
                            ]);
                        }, ARRAY_FILTER_USE_KEY);
                        $quickCookieStrings = [];
                        foreach ($quickCookie as $c => $v) {
                            $quickCookieStrings[] = "$c=$v";
                        }
                        $quickCookieStrings = implode('; ', $quickCookieStrings);
                        $restNonce = wp_create_nonce( 'wp_rest' );
                        $this->api_inst_allow_access_id(
                            "prefetch-{$this->apiClientPreFetchToken}",
                            $quickCookieStrings, $restNonce
                        );
                        $successMsg = "Your pre-fetch token '{$this->apiClientPreFetchToken}' have been authenticated, please boot your api with this pre-fetch token.";
                        if (wp_is_json_request()) {
                            header("Content-Type: application/json"); 
                            echo wp_json_encode([
                                'code' => 'wa_succed_nonce_auth',
                                'message' => $successMsg,
                                'data' => [
                                    "nonce" => $restNonce,
                                    "quick_COOKIE" => $quickCookieStrings,
                                    "wa_api_pre_fetch_token" => $this->apiClientPreFetchToken,
                                ],
                                "info" => [
                                    "COOKIE" => _wp_json_sanity_check($_COOKIE, 7),    
                                ],
                            ]);    
                        } else {
                            echo wp_kses_post($successMsg);
                        }
                    } else {
                        $redirectBack = add_query_arg([
                            'wa_api_pre_fetch_token' => $this->apiClientPreFetchToken,
                        ], home_url( "/api-wa-config-nonce-rest"));
                        $redirectUrl = wp_login_url($redirectBack);
                        if (wp_is_json_request()) {
                            header("Content-Type: application/json"); 
                            echo wp_json_encode([
                                "error" => "wa_api_login_required",
                                'wa_api_pre_fetch_token' => $this->apiClientPreFetchToken,
                                "message" => "Need to be logged in, please follow the redirect link location from your web browser",
                                "location" => $redirectUrl,
                            ]);
                        } else {
                            if (!wp_redirect($redirectUrl)) {
                                $this->err('Internal server error', $this->debug_trace());
                                header("Content-Type: application/json"); 
                                echo wp_json_encode([
                                    'error' => 'Internal server error'
                                ]);
                                $self->exit(); return; 
                            };    
                        }
                    }
                    $self->exit(); return; 
                }
            }
            /**
             * Delete all access tokens for wa-config rest api access
             * 
             * Usable as an Ajax call from wp-admin js scripts
             */
            function api_inst_admin_delete_all_api_access() : void {
                $anonimizedIp = $this->get_user_ip();
                if (!current_user_can('administrator')) { 
                    $this->err("api_inst_admin_delete_all_api_access invalid access for $anonimizedIp, need to be administrator");
                    echo wp_json_encode([
                        "error" => "Invalid access for $anonimizedIp registred",
                    ]);
                    http_response_code(401);
                    $this->exit(); return;
                }
                $this->debug("Will api_inst_admin_delete_all_api_access");
                delete_transient($this->API_ACCESS_IDS);
                $redirectUrl = add_query_arg([
                    'page' => $this->eConfigPageKey,
                ], admin_url( 'admin.php' ))
                . "#wa-admin-rest-api-access-details";
                wa_redirect($redirectUrl, $this); return;
            }
            /**
             * 
             * Init wa-config instanciable routings
             * 
             * Will add wa-config instanciable endpoints and logs.
             * 
             * @see ApiInstanciable::api_inst_run_action()
             * 
             */
            function api_inst_rest_init() : void {
                $self = $this;
                $this->debug("Will api_inst_rest_init");
                $customCORS = false;
                if ($customCORS) {
                    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
                    add_filter( 'rest_pre_serve_request', [$this, 'api_inst_rest_init_CORS']);
                }
                register_rest_route(
                    'wa-config/v1',
                    '/instanciable(?:/(?P<inst_action>.*))?', [
                        'methods' => ['POST', 'GET'],
                        'callback' => [ $this, 'api_inst_run_action' ],
                        'permission_callback' => function () {
                            return true;
                        },
                        'args' => array(
                            'inst_action' => array(
                                'validate_callback' => function($instAction, $request, $key) use ($self) {
                                    $self->debug("Allowing route wa-config instanciable/$instAction");
                                    return true;
                                }
                            ),
                        ),                  
                    ],
                );
                add_filter( 'rest_authentication_errors', [$this, 'api_inst_rest_check_auth_errors'], 101 );
            }
            /**
             * Not used yet, might be used if wp basic CORS not enough...
             */
            protected function api_inst_rest_init_CORS( $value ) {
                $this->debug("Will api_inst_rest_init_CORS");
                $origin_url = '*';
                header( 'Access-Control-Allow-Origin: ' . $origin_url );
                header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE' );
                header( 'Access-Control-Allow-Credentials: true' );
                if (
                    isset( $_SERVER['REQUEST_METHOD'] )
                    && $_SERVER['REQUEST_METHOD'] === 'OPTIONS'
                ) {
                    $this->debug("Serving response for CORS Preflight request");
                    header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );
                    header( 'Access-Control-Max-Age: 86400' );
                    header( 'Cache-Control: public, max-age=86400' );
                    header( 'Vary: origin' );
                    $this->exit(); return $value;
                }
                return $value;
            }
            /**
             * 
             * Verify errors and invalidate pre-fetch token if wordpress did send regular error event on it.
             * 
             * @param WP_Error|mixed $result Error from another authentication handler,
             *                               null if we should handle it, or another value if not.
             * @return WP_Error|mixed|bool WP_Error if the cookie is invalid, the $result, otherwise true.
             */
            function api_inst_rest_check_auth_errors($result) {
                if (is_wp_error($result)) {
                    $request = _wp_json_sanity_check(array_filter($_REQUEST, function ($c) {
                        return in_array($c, [
                            'wa_api_pre_fetch_token', 'wa_user_location', 'wa_access_id'
                        ]);
                    }, ARRAY_FILTER_USE_KEY), 7);
                    $this->api_inst_load_parameters($request);
                    $isWa = false;
                    if ( isset( $request['wa_api_pre_fetch_token'] ) ) {
                        $this->debug("Detect auth error for prefetched key '{$this->apiClientPreFetchToken}',"
                        . " will clean it up due to : [" . $result->get_error_code() . "] " . $result->get_error_message());
                        $this->api_inst_delete_access_id("prefetch-{$this->apiClientPreFetchToken}");
                        $isWa = true;
                    }
                    if ( isset( $request['wa_access_id'] ) ) {
                        $this->debug("Detect auth error for prefetched key '{$this->apiClientAccessId}',"
                        . " will clean it up due to : [" . $result->get_error_code() . "] " . $result->get_error_message());
                        $this->api_inst_delete_access_id($this->apiClientAccessId);
                        $isWa = true;
                    }
                    if ($isWa) {
                        return $this->api_inst_nonce_redirect("wa_auth_denied_since_wp_auth_denied");
                    }
                }
                return $result;
            }
            protected $apiClientPreFetchToken = null;
            protected $apiClientUserLocation = null;
            protected $apiClientAccessId = null;
            /**
             * Load ApiInstanciable sanitized parameters from the targeted $request
             * 
             * Load class members [
             *  - **apiClientPreFetchToken**
             *  - **apiClientUserLocation**
             *  - **apiClientAccessId**
             * 
             * ] From :
             *  - **wa_api_pre_fetch_token** : Token used for the pre-fetch
             * authentification system
             *  - **wa_user_location** : A text string written by the API
             * end user describing it's localisation
             *  - **wa_access_id** : Access id used to validate authenticated
             * REST API calls to wa-config
             *
             * @param array|WP_REST_Request $request Request to check. Rest request
             * or $_REQUEST object or similar
             */
            public function api_inst_load_parameters($request) : void {
                $f = function ($v) { return filter_var(
                    $v, FILTER_SANITIZE_SPECIAL_CHARS
                );};
                $this->apiClientPreFetchToken = $this->api_inst_ensure_access_id($f(
                    sanitize_text_field($request['wa_api_pre_fetch_token'] ?? null)
                ));
                $this->apiClientUserLocation = $f(
                    wp_kses_post($request['wa_user_location'] ?? null)
                );
                $this->apiClientAccessId = $this->api_inst_ensure_access_id($f(
                    sanitize_key($request['wa_access_id'] ?? null)
                ));
            }
            protected $apiAccessHashSize = 21;
            protected function api_inst_ensure_access_id($accessId = '') {
                $accessId = $accessId ?? ""; 
                $accessIdSafe = sanitize_title($accessId);
                if ($accessId !== $accessIdSafe) {
                    $this->err("'$accessIdSafe' should have been used, reseting access ID to new ID");
                }
                if (!strlen($accessId)) {
                    $accessId = bin2hex(
                        random_bytes($this->apiAccessHashSize/2)
                    );
                }
                return $accessId;
            }
            /**
             * Print the ApiInstanciable access report
             */
            public function api_inst_print_access_report() : void {
                if (!current_user_can('administrator')) {
                    $this->debugVerbose("api_inst_print_access_report : Only administrator can access this report...");
                    return; 
                }
                $content = "<h1 id='wa-admin-rest-api-access-details'> " . __(
                    "Détail des accès API en cours",
                    'monwoo-web-agency-config'/** 📜*/
                ) . " </h1>";
                $accessIds = get_transient(
                    $this->API_ACCESS_IDS
                );
                $accessIds = $accessIds
                ? _wp_json_sanity_check(json_decode($this->api_inst_encryptor($accessIds, 'd'), true), 42)
                : [];
                $content .= "<div class='wa-api-access-list'>";
                $aContend = "";
                foreach ($accessIds as $aId => $payload) {
                    $date =  date("Y/m/d", $payload['start_time']);
                    $time =  date("O H:i:s", $payload['start_time']);
                    $lastAccess =  date("Y/m/d O H:i:s", $payload['last_access_time']);
                    $uLoc = $payload['wa_user_location'] ?? null;
                    $localisation = $uLoc ? "<strong>$uLoc</strong><br />" : "";
                    $item = "
                    <div class='wa-api-access-item'>
                        <strong>$date</strong><br />
                        $localisation
                        <strong class='wa-time'>$time</strong><br />
                        <strong class='wa-time'>$lastAccess</strong><br />
                        [<span>{$payload['ip']}</span>]<br />
                        <span>$aId</span>
                    </div>
                    ";
                    $aContend = "$item $aContend";
                }
                $deleteLink = add_query_arg([
                    'action' => 'wa-delete-all-api-access',
                ], admin_url( 'admin-ajax.php' ));
                $content .= $aContend;
                $content .= "</div>";
                $content .= "<p>";
                if (get_transient($this->API_ACCESS_IDS)) {
                    $content .= "<a href='$deleteLink'>Clickez ici pour supprimer tous les accès.</a>"; 
                }
                $content .= "</p>";
                echo wp_kses_post($content);
            }
            protected function api_inst_encryptor($stringToHandle = "",$encryptDecrypt = 'e'){
                $output = null;
                $secret_key = NONCE_KEY; 
                $secret_iv = NONCE_SALT; 
                $key = hash('sha256',$secret_key);
                $iv = substr(hash('sha256',$secret_iv),0,16);
                if($encryptDecrypt == 'e'){
                   $output = base64_encode(openssl_encrypt($stringToHandle,"AES-256-CBC",$key,0,$iv));
                }else if($encryptDecrypt == 'd'){
                   $output = openssl_decrypt(base64_decode($stringToHandle),"AES-256-CBC",$key,0,$iv);
                }
                return $output;
            }
            protected $API_ACCESS_IDS = 'wa_api_a_ids';
            protected $ApiAccessDuration = 60 * 60; 
            protected function api_inst_validate_access_id($accessId = '') {
                $ip = $this->get_user_ip(true, true);
                $accessIds = get_transient(
                    $this->API_ACCESS_IDS
                );
                $accessIds = $accessIds
                ? _wp_json_sanity_check(json_decode($this->api_inst_encryptor($accessIds, 'd'), true), 42)
                : [];
                if (!is_array($accessIds)) {
                    $this->warn("$accessIds should be array type, fixing it");
                    $accessIds = [];
                }
                if ($accessIds[$accessId] ?? false) {
                    $accessPayload = $accessIds[$accessId];
                    $startTime = $accessPayload['start_time'];
                    $this->assertLog((time() - $startTime) < $this->ApiAccessDuration, "Access did expire");
                    $this->assertLog($ip === $accessPayload['ip'], "IP mismatch");
                    $this->assertLog($this->apiClientPreFetchToken === $accessPayload['wa_api_pre_fetch_token'], "wa_api_pre_fetch_token mismatch for $this->apiClientPreFetchToken vs {$accessPayload['wa_api_pre_fetch_token']}");
                    $shouldAccess = ((time() - $startTime) < $this->ApiAccessDuration
                    && $ip === $accessPayload['ip']
                    && $this->apiClientPreFetchToken === $accessPayload['wa_api_pre_fetch_token'])
                    ? $accessPayload : false;
                    if ($shouldAccess) {
                        $accessPayload['last_access_time'] = time();
                        $accessIds[$accessId] = $accessPayload;      
                        set_transient(
                            $this->API_ACCESS_IDS,
                            $this->api_inst_encryptor(wp_json_encode($accessIds), 'e'),
                            $this->ApiAccessDuration
                        );
                    }
                    return $shouldAccess;
                }
                return false;
            }
            protected function api_inst_allow_access_id($accessId = '', $quickCookie = '', $restNonce = null) {
                $ip = $this->get_user_ip(true, true);
                $accessIds = get_transient(
                    $this->API_ACCESS_IDS
                );
                $accessIds = $accessIds
                ? _wp_json_sanity_check(json_decode($this->api_inst_encryptor($accessIds, 'd'), true), 42)
                : [];
                if (!is_array($accessIds)) {
                    $this->warn("$accessIds should be array type, fixing it");
                    $accessIds = [];
                }
                $preRequest = $this->api_inst_ensure_access_id(
                    $this->apiClientPreFetchToken ?? ""
                );
                $this->apiClientPreFetchToken = $preRequest;
                $time = time();
                $accessIds[$accessId] = [
                    'ip' => $ip,
                    'start_time' => $time,
                    'last_access_time' => $time,
                    'wa_api_pre_fetch_token' => $preRequest,
                    'nonce' => $restNonce, 
                    'quick_COOKIE' => $quickCookie,
                    'wa_user_location' => $this->apiClientUserLocation,
                ];
                return set_transient(
                    $this->API_ACCESS_IDS,
                    $this->api_inst_encryptor(wp_json_encode($accessIds), 'e'),
                    $this->ApiAccessDuration
                );
            }
            protected function api_inst_delete_access_id($accessId) {
                $accessIds = get_transient(
                    $this->API_ACCESS_IDS
                );
                $accessIds = $accessIds
                ? _wp_json_sanity_check(json_decode($this->api_inst_encryptor($accessIds, 'd'), true), 42)
                : [];
                if (!is_array($accessIds)) {
                    $this->warn("$accessIds should be array type, fixing it");
                    $accessIds = [];
                }
                if (!$accessId || !strlen($accessId)
                || !array_key_exists($accessId, $accessIds)) {
                    return false;
                }
                unset($accessIds[$accessId]);
                return set_transient(
                    $this->API_ACCESS_IDS,
                    $this->api_inst_encryptor(wp_json_encode($accessIds), 'e'),
                    $this->ApiAccessDuration
                );
            }
            protected function api_inst_nonce_redirect($authError = 'authentication_needed') {
                $preRequest = $this->api_inst_ensure_access_id(
                    $this->apiClientPreFetchToken ?? ""
                );
                $getRestNonceUrl = add_query_arg([
                    'wa_api_pre_fetch_token' => $preRequest,
                ], home_url( "/api-wa-config-nonce-rest"));
                if (wp_is_json_request()) {
                    return new WP_Error(
                        $authError,
                        'Authentification redirect needed, please login', [
                            'wa_api_pre_fetch_token' => $this->apiClientPreFetchToken,
                            'location' => $getRestNonceUrl,
                            'status' => 302
                        ]
                    );
                }
                if ( wp_redirect( $getRestNonceUrl ) ) {
                    echo wp_kses_post("<a class='$authError' href='$getRestNonceUrl'> [302] Redirecting to $getRestNonceUrl...</a>");
                    $this->exit(); return;
                }
                return new WP_Error( 'internal_redirect_error', "Internal redirect error for '$authError'", array( 'status' => 404 ) );
            }
            protected function api_inst_need_authentification(WP_REST_Request $request) {
                if (!is_user_logged_in()) {
                    if ($preFetchPayload = $this->api_inst_validate_access_id(
                        "prefetch-{$this->apiClientPreFetchToken}"
                    )) {
                        $accessId = $this->api_inst_ensure_access_id(); 
                        $this->api_inst_allow_access_id($accessId);
                        return new WP_Error(
                            'wrong_auth_header_or_cookie',
                            "Pre-fetch OK. Missing X-WP-Nonce header or wordpress_* cookie", 
                            [
                                'nonce' => $preFetchPayload['nonce'],
                                'quick_COOKIE' => $preFetchPayload['quick_COOKIE'],
                                'wa_access_id' => $accessId,
                                'wa_api_pre_fetch_token' => $this->apiClientPreFetchToken,
                                'status' => 404 
                            ]
                        );        
                    } else if (!$this->api_inst_validate_access_id($this->apiClientAccessId)) {
                        $this->apiClientAccessId = null;
                        $this->apiClientPreFetchToken = $this->api_inst_ensure_access_id();
                        return $this->api_inst_nonce_redirect('wa_fail_prefetch_access');
                    }
                }
                if (!$this->api_inst_validate_access_id($this->apiClientAccessId)) {
                    $this->apiClientAccessId = null;
                    $this->apiClientPreFetchToken = $this->api_inst_ensure_access_id();
                    return $this->api_inst_nonce_redirect('fail_access_id_validation');
                }
                $this->api_inst_delete_access_id("prefetch-{$this->apiClientPreFetchToken}"); 
                return false; 
            }
            /**
             * Will run an api instanciable action call
             * 
             * Nothing special for now. May be for next versions...
             *
             * @param WP_REST_Request $request The rest request.
             * @return WP_REST_Response|WP_Error Result of the action
             */
            function api_inst_run_action(WP_REST_Request $request)
            {
                $self = $this;
                /**
                 * @var string $instAction Instanciable action to launch
                 */
                $instAction = $request['inst_action'];
                $this->debug("Will api_inst_run_action '$instAction'");
                $openActions = [
                ];
                if (array_key_exists($instAction, $openActions)) {
                    return $openActions[$instAction]($this);
                }
                if (!current_user_can($this->optAdminEditCabability)) {
                    return $this->api_inst_nonce_redirect();
                }
                $authenticatedActions = [
                ];
                if (array_key_exists($instAction, $authenticatedActions)) {
                    return $authenticatedActions[$instAction]($this, $instAction);
                }
                return new WP_Error(
                    'wa_unknow_action',
                    "Unknown action '$instAction'", 
                    [ 'inst_action' => $instAction, 'status' => 404 ]
                );
            }
        }
    }
    if (!trait_exists(ApiFrontHeadable::class)) { 
        /**
         * This trait will allow Frontend developers to synchronise front heads
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         * @uses Identifiable
         * @uses ApiInstanciable
         */
        trait ApiFrontHeadable
        {
            use Editable;
            use Identifiable;
            use ApiInstanciable;
            protected function _020_api_fronthead__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_020_api_fronthead__bootstrap')) {
                    return; 
                }
                add_action( 'rest_api_init', [$this, 'api_fronthead_rest_init'] );
                if (is_admin()) {   
                    add_action(
                        'wp_ajax_wa-suggest-frontheads', 
                        [$this, 'api_fronthead_admin_sugestion_list']
                    );
                }
            }
            /**
             * 
             * Init wa-config fronthead API routings
             * 
             * WARNING : NO BACKUPS are done of the front head folders
             * (since you have the static zip backup)
             * 
             * That means, in case of network fail or other possible error
             * the targeted head folder might be erased and empty
             * (with no server backup to recover the files)
             * 
             * Will add wa-config fronthead API endpoints :
             *  - 'wp-json/wa-config/v1/fronthead/< action >/< userLocation >'
             *    Could be called like :
             *    ```bash
             *    rm tmp.zip
             *    mkdir my-front-head
             *    cp readme.txt my-front-head
             *    zip -r tmp.zip my-front-head
             *    rm -rf my-front-head
             * 
             *    \curl -F "deploy_action=publish" -F "wa_head_target=my-front-head" \
             *    -F "wa_zip_subpath=my-front-head" -F "wa_user_location=my-dev-publish-location" \
             *    -F wa_api_pre_fetch_token="qlqjlmsdjlqmsfjqmlsdfjf" \
             *    -F "wa_zip_bundle=@tmp.zip;type=application/zip" \
             *    "https://web-agency.local.dev/e-commerce/wp-json/wa-config/v1/fronthead"
             * 
             *    # DO it again if you get a login redirect on your first try and did auth your prefetch token :
             *    \curl -F "deploy_action=publish" -F "wa_head_target=my-front-head" \
             *    -F "wa_zip_subpath=my-front-head" -F "wa_user_location=my-dev-publish-location" \
             *    -F wa_api_pre_fetch_token="qlqjlmsdjlqmsfjqmlsdfjf" \
             *    -F "wa_zip_bundle=@tmp.zip;type=application/zip" \
             *    "https://web-agency.local.dev/e-commerce/wp-json/wa-config/v1/fronthead"
             * 
             *    # Copy/past right value given by previous call 
             *    # (add -H 'Accept: application/json' to see json responses):
             *    alias wa-curl="\curl -H 'X-WP-Nonce: d7682d4b57' \
             *    -F 'wa_api_pre_fetch_token=qlqjlmsdjlqmsfjqmlsdfjf' \
             *    --cookie 'wordpress_logged_in_0817c4fe6cb20c659452290ed095d268=demo@monwoo.com|1655933394|HaXijJzKT99LigZx8IwWeW5r3iOvYK5VyAH77swJ3ha|7e0b28a2da2706c94ed3483429e09f30c2ab66f0c504bf13ce2710022da05198; PHPSESSID=hjt07ig3864k85td09gs86vfeh","wa_access_id":"9c35d5b3240104c32c48' \
             *    -F 'wa_access_id=9c35d5b3240104c32c48' -F 'wa_user_location=my-dev-publish-location' "
             * 
             *    wa-curl -F "deploy_action=publish" -F "wa_head_target=my-custom-head" \
             *    -F "wa_zip_subpath=my-front-head" \
             *    -F "wa_zip_bundle=@tmp.zip;type=application/zip" \
             *    "https://web-agency.local.dev/e-commerce/wp-json/wa-config/v1/fronthead"
             * 
             *    # If your server have error with upload type content, use b64 :
             *    b64=$(cat tmp.zip | base64)
             *    wa-curl -F "deploy_action=publish" -F "wa_head_target=my-custom-head" \
             *    -F "wa_zip_subpath=my-front-head" \
             *    -F "wa_zip_bundle_b64=$b64" -vvv --trace debug-trace.log \
             *    "https://web-agency.local.dev/e-commerce/wp-json/wa-config/v1/fronthead"
             *
             *    # If you want to use our publish node tool
             *    # Install dev dependencies
             *    npm install -D node 'node-fetch@^2.6.7' form-data dotenv \
             *        cryptr chrome-remote-interface chrome-launcher
             * 
             *    # Launch our deploy script
             *    ( export WA_BACKEND_HOST="https://< wordpress production target >"
             *    export WA_HEAD_TARGET="my-first-deploy"
             *    export WA_USER_LOCATION="my-laptop-from-france-in-2022"
             *    export WA_REST_API_SERVER="$WA_BACKEND_HOST/wp-json/wa-config/v1"
             *    node tools/wa-deploy.cjs)
             * 
             *    # For specific dev plateform with self signed certificate
             *    ( export WA_FRONTEND_HOST="https://web-agency.local.dev"
             *    export WA_HEAD_TARGET="my-first-deploy"
             *    export WA_USER_LOCATION="my-laptop-from-france-in-2022"
             *    export WA_SSL_ALLOW_SELFSIGNED=true
             *    export WA_BACKEND_HOST="$WA_FRONTEND_HOST/e-commerce"
             *    export WA_REST_API_SERVER="$WA_BACKEND_HOST/wp-json/wa-config/v1"
             *    node tools/wa-deploy.cjs)
             *    ```
             * 
             * {@see https://stackoverflow.com/questions/12667797/using-curl-to-upload-post-data-with-files Upload files with curl command line}
             */
            function api_fronthead_rest_init() : void {
                $self = $this;
                $this->debug("Will api_fronthead_rest_init");
                register_rest_route(
                    'wa-config/v1',
                    '/fronthead(?:/(?P<deploy_action>[^/]*))?(?:/(?P<wa_user_location>.*))?', [
                        'methods' => ['POST', 'GET'] ,
                        'callback' => [ $this, 'api_fronthead_deploy' ],
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'deploy_action' => [ 'validate_callback' => '__return_true' ],
                            'wa_user_location' => array(
                                'validate_callback' => function($userLocation, $request, $key) use ($self) {
                                    $self->debug("Allowing wa_user_location param for route wa-config fronthead/$userLocation");
                                    return true;
                                }
                            ),
                            'wa_head_target' => [ 'validate_callback' => '__return_true' ],
                            'wa_zip_subpath' => [ 'validate_callback' => '__return_true' ],
                            'wa_zip_bundle' => [ 'validate_callback' => '__return_true' ],
                            'wa_access_id' => [ 'validate_callback' => '__return_true' ],
                            'wa_api_pre_fetch_token' => [ 'validate_callback' => '__return_true' ],
                        ),                  
                    ],
                );
            }
            /**
             * Run deploy actions of zip archive to wa-config front heads folder
             * 
             * REQUEST parameters :
             *  - {@see ApiInstanciable::api_inst_load_parameters()}
             *  - **wa-data** : Associated data for the action
             *  - **deploy_action** : Deploy action to run
             *     - **'publish'** : Publish action of a ziped front-head
             *        - **wa_head_target** : Sever side front-head publish path
             *        - **wa_zip_subpath** : Zip sub path to use when deploying the zip to the server front-head
             *        - **wa_zip_bundle** : Zip file to deploy (Form multipart binary upload)
             *        - **wa_zip_bundle_b64** : Zip file to deploy (Base 64 encoded)
             *
             * @param WP_REST_Request $request The rest request doing the deploy.
             * @return WP_REST_Response|WP_Error Result of the deploy
             */
            function api_fronthead_deploy(WP_REST_Request $request)
            {
                $self = $this;
                $this->api_inst_load_parameters($request);
                $fHeadAction = $request['deploy_action'];
                $this->debug("Will api_fronthead_deploy for user : '{$this->apiClientUserLocation}'");
                $this->debugVeryVerbose("With request", $request);
                if ($resp = $this->api_inst_need_authentification($request)) {
                    return $resp;
                }
                if (!current_user_can($this->optAdminEditCabability)) {
                    return $this->api_inst_nonce_redirect("need_caps_{$this->optAdminEditCabability}");
                }
                $authenticatedActions = [
                    'publish' => function($app, $instAction) use ($request) {
                        set_time_limit(25*60); 
                        $fs = wa_filesystem();
                        $headTarget = trim($request['wa_head_target'] ?? '', '/');
                        $zipSubPath = $request['wa_zip_subpath'] ?? '';
                        $zipBundle = $request->get_file_params()["wa_zip_bundle"] ?? [];
                        if (!count($zipBundle)) {
                            $zipBundle = null;
                        }
                        $zipBundleB64 = $zipBundle ? null : ($request['wa_zip_bundle_b64'] ?? null); 
                        if (!$zipBundle && !$zipBundleB64) {
                            return new WP_Error(
                                'missing_wa_zip_bundle',
                                "Fail to access 'wa_zip_bundle' file param or 'wa_zip_bundle_b64' param.", 
                                [ 'deploy_action' => $instAction, 'status' => 404 ]
                            );            
                        }
                        $zipSource = $zipBundle['tmp_name'] ?? null;
                        $zipName = $zipBundle['name'] ?? null;
                        if (!$zipSource && $zipBundleB64) {
                            $zipSource = wp_tempnam(); 
                            $written = file_put_contents($zipSource, base64_decode($zipBundleB64));
                            $zipName = 'wa_zip_bundle_b64';
                        }
                        $app->debugVeryVerbose("With wa_zip_bundle : ", $zipBundle);
                        $avoidBackup = $request['avoid_backup']; 
                        $status = [];
                        $headsFolder = rtrim(realpath($app->pluginRoot . "heads"), '/');
                        $zipTargetPath = "$headsFolder/$headTarget";
                        $didCreateDir = false;
                        if (!$fs->exists($zipTargetPath)) {
                            $didCreateDir = $fs->mkdir($zipTargetPath); 
                        }
                        $zipTargetPath = realpath($zipTargetPath);
                        if (false === strpos($zipTargetPath, $headsFolder)) {
                            if ($didCreateDir) {
                                $fs->rmdir($zipTargetPath);
                            }
                            return new WP_Error(
                                'wrong_wa_head_target',
                                "Fail to access target head : '$headsFolder/$headTarget'", 
                                [ 'deploy_action' => $instAction, 'status' => 404 ]
                            );            
                        }
                        if (!$avoidBackup) {
                        }
                        $fs->rmdir($zipTargetPath, true);
                        $fs->mkdir($zipTargetPath);
                        $zip = new ZipArchive;
                        if (true !== ($err = $zip->open($zipSource))) {
                            $err = print_r($err, true);
                            $zipStatus = $zip->getStatusString();
                            if (!file_exists($zipSource)) {
                                $err = 404;
                                $zipStatus = "Internal Server Error. Missing file.";
                            }
                            return new WP_Error(
                                'wrong_zip_file',
                                "Err [$err - $zipStatus] : Fail to open zip file : '{$zipName}'", 
                                [ 'deploy_action' => $instAction, 'src' => $zipSource, 'status' => 404 ]
                            );
                        }
                        if ($zipSubPath) {
                            $zipSubPath = trim($zipSubPath, "/") . '/';
                            for($i = 0; $i < $zip->numFiles; $i++) {
                                $filename = $zip->getNameIndex($i);
                                $targetFilename = str_replace($zipSubPath, '', $filename);
                                if (!strlen($targetFilename)) {
                                    continue; 
                                }
                                $targetFilename = "$zipTargetPath/$targetFilename";
                                $dirname = dirname($targetFilename);
                                $zipSrcFile = "zip://".$zipSource."#".$filename;
                                if (!is_dir($dirname)) {
                                    mkdir($dirname, 0755, true); 
                                }
                                if (strrpos($targetFilename, '/', ) == (strlen($targetFilename) - 1)) {
                                    if (!is_dir($targetFilename)) {
                                        mkdir($targetFilename, 0755, true);
                                    }
                                } else {
                                    if (!copy($zipSrcFile, $targetFilename)) {
                                        $status[] = [
                                            "error" => "Fail to copy zip file",
                                            "src" => $zipSrcFile,
                                            "dst" => $targetFilename,
                                        ];
                                    };
                                }
                            }                          
                        } else {
                            $zip->extractTo($zipTargetPath);
                        }
                        $zip->close();
                        unlink($zipSource);
                        delete_transient($app->_frontheadsSearchCacheKey);
                        $status[] = ["end-status" => "OK"];
                        $app->info("Succed wa-api head publish to '$zipTargetPath'"
                        . " from '{$app->apiClientUserLocation}'");
                        return new WP_REST_Response([
                            "code" => 'ok',
                            "action" => $instAction,
                            "data" => [
                                "wa_head_target" => $headTarget,
                                "head_path" => $zipTargetPath,
                                "status" => $status,    
                            ]
                        ], 200);
                    },
                ];
                if (array_key_exists($fHeadAction, $authenticatedActions)) {
                    return $authenticatedActions[$fHeadAction]($this, $fHeadAction);
                }
                return new WP_Error(
                    'wa_unknow_action',
                    "Unknown deploy_action '$fHeadAction'", 
                    [ 'deploy_action' => $fHeadAction, 'status' => 404 ]
                );
            }
            protected $_frontheadsSearchCacheKey = 'wa_config_api_fronthead_search';
            protected $_frontheadsSearchCache = null;
            /**
             * Output a suggestion list of all available front-heads
             * 
             * Used for ajax suggestion lists. Echo one sugestion per line.
             * 
             * GET parameters :
             *  - **q** : The query used to filter the suggestions (end user search input)
             *
             */
            public function api_fronthead_admin_sugestion_list() : void {
                if (!is_admin()) {
                    $this->err("wa-config admin param section is under admin pages only");
                    echo "<p> "
                        . __(
                            "Cette opération nécessite une page d'administration.",
                            'monwoo-web-agency-config'/** 📜*/
                        )
                        . "</p>";
                    return;
                }
                $query = filter_var( sanitize_text_field($_GET['q'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
                $query = strtolower(wp_unslash( $query ));
                if (!$this->_frontheadsSearchCache) {
                    $this->_frontheadsSearchCache = get_transient( 
                        $this->_frontheadsSearchCacheKey
                    );
                }
                if (!$this->_frontheadsSearchCache) {
                    $this->_frontheadsSearchCache = [];
                }
                $allowCache = true;
                if ($allowCache
                && array_key_exists($query, $this->_frontheadsSearchCache)) {
                    $this->debug("api_fronthead_admin_sugestion_list loaded from cache [$query]");
                    echo wp_kses_post($this->_frontheadsSearchCache[$query]);
                    $this->exit(); return;
                }
                $heads = [];
                $headsDir = $this->pluginRoot . 'heads';
                $files = list_files($headsDir, 2); 
                foreach ($files as $f) {
                    $d = dirname($f);
                    $d = trim(str_replace($headsDir, '', $d), '/');
                    if (strlen($d)) {
                        $heads[$d] = $d;
                    }
                }
                $heads = array_values($heads);
                $isFirstMatch = true;
                $searchResult = '';
                $it = count($heads) - 1;
                while (
                    $it >= 0
                ) {
                    $current = $heads[$it];
                    --$it;
                    if (strpos(strtolower($current), $query) === false) {
                        continue;
                    }
                    if ($isFirstMatch) {
                        $isFirstMatch = false;
                    } else {
                        $searchResult .= "\n";
                    }
                    $searchResult .= $current;
                }
                $searchResult .= "\n";
                if ($isFirstMatch) {
                    $searchResult .= "<notice class='wa-no-search-result'>[$query] "
                    . __(" ### Aucune tête de trouvée.", 'monwoo-web-agency-config'/** 📜*/)
                    . "</notice>";
                }
                $this->_frontheadsSearchCache[$query] = $searchResult;
                set_transient( 
                    $this->_frontheadsSearchCacheKey,
                    $this->_frontheadsSearchCache,
                    24 * 60 * 60 
                );
                echo wp_kses_post($searchResult);
                $this->exit(); return;
            }
            protected function api_fronthead_admin_sugestionbox_template(
                $safeValue, $fieldId, $fieldName, $placeholder = ""
            ) {
                return function()
                use ( & $fieldId, & $fieldName, & $placeholder, & $safeValue ) { ?>
                    <input
                    type='text'
                    placeholder="<?php echo esc_attr($placeholder) ?>"
                    class="wa-suggest-list-api-frontheads"
                    id="<?php echo esc_attr($fieldId) ?>"
                    name="<?php echo esc_attr($fieldName) ?>"
                    value="<?php echo wp_kses_post($safeValue) ?>"
                    />
                <?php };
            }
        }
    }
    if (!class_exists(OptiLvl::class)) { 
        /**
         * This class register all optimisations levels like an ENUM
         *
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class OptiLvl {
            /**
             * Medium optimisation (Removing CRON)
             * 
             * CRON slow down frontend and admin calls, specially if you have lot of plugins
             * doing lot of heavy (or slow) short period jobs.
             * 
             * Indeed, default WP CRON strategy is to run those jobs at same time as client requests
             * and consume client waiting time loads on simple request that should not consume so much.
             * 
             * With this MEDIUM level activated review will test that wp-config.php define :
             * - **DISABLE_WP_CRON** : to true. Then you should handle CRON
             * with your website provider or call '/wp-cron.php?doing_wp_cron' purposely
             * to still run those background jobs (or run them from another
             * WP instance sharing the same database connection... cf LOCK and parallel DB operations)
             */
            const MEDIUM = 'medium';
            /**
             * Full optimisation (Removing Wordpress and Plugins audo-update)
             * 
             * Auto-update can slow down admin calls, and potentially client calls in case
             * of server overloads due to plugin updates in progress on top of client requests.
             * 
             * For maximum optimisation, we will disable those. You can still update plugins from
             * the regular wordpress update status panel.
             * 
             * Please, do the updates and reviews with regularity, since automatic mode have been disabled.
             * 
             * Moreover, **automatic** mode **or manual** mode
             * **dose not** launch the **reviews** and **end to end** tests.
             * 
             * You will gain more controle and quality for your website updates by doin thoses.
             * 
             * Updates may breaks some plugins or data. We will be pleased to provide some
             * of our expertise to solve thoses issues : {@see https://moonkiosk.monwoo.com}.
             * 
             * We honor paied contracts, and let open source request follow the open source community
             * 'delay' and 'will' to solve the request.
             * 
             * With this FULL level activated, common auto-update hooks will be disabled :
             * - plugins_auto_update_enabled is filtered to false
             * - themes_auto_update_enabled is filtered to false
             * 
             * Review will also test that wp-config.php define :
             * - **WP_AUTO_UPDATE_CORE** to false, removing server side Wordpress auto-update calls
             * - **AUTOMATIC_UPDATER_DISABLED** to true, removing server side Plugins auto-update calls
             * 
             */
            const MAX = 'full';
        }
    }
    if (!trait_exists(Optimisable::class)) { 
        /**
         * This trait will help with speed optimisations
         *
         * It will also enable WordPress http request filterings
         * to help you remove some internal calls.
         * 
         * It will optimize wp_http_requests (HTTP Request Filtering) on CRON jobs too
         * unless you define the WA_SHOULD_NOT_OPTIMIZE_CRON constant to true.
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         * @uses Identifiable
         */
        trait Optimisable
        {
            use Editable;
            use Identifiable;
            protected function _020_opti__bootstrap()
            {
                if ($this->p_higherThanOneCallAchievedSentinel('_020_opti__bootstrap')) {
                    return; 
                }
                add_filter('pre_http_request', [$this,
                'opti_filter_wp_http_requests'], 10, 3);
                if ( defined('DOING_CRON') && constant( 'DOING_CRON' )
                && defined('WA_SHOULD_NOT_OPTIMIZE_CRON') && constant('WA_SHOULD_NOT_OPTIMIZE_CRON'))
                {
                    return;
                }
                $optiLvls = explode(',', $this->getWaConfigOption(
                    $this->eConfOptOptiLevels,
                    ""
                ));
                $this->debugVeryVerbose('Requesting optimisation levels : ', ["levels" => $optiLvls]);
                if (false !== array_search(OptiLvl::MAX, $optiLvls)) {
                    $this->opti_setup_for_max_speed();
                    add_action(
                        WPActions::wa_do_base_review_preprocessing,
                        [$this, 'opti_max_speed_review']
                    );
                } else if (false !== array_search(OptiLvl::MEDIUM, $optiLvls)) {
                    $this->opti_setup_for_medium_speed();
                    add_action(
                        WPActions::wa_do_base_review_preprocessing,
                        [$this, 'opti_medium_speed_review']
                    );
                    if (false === array_search(OptiLvl::MAX, $optiLvls)) {
                        $this->eReviewIdsToTrash = array_merge($this->eReviewIdsToTrash, [
                            "{$this->iId}-data-review-opti-full"
                        ]);
                    }
                } else {
                    if (count($optiLvls) > 1) {
                        $this->warn('Unknown Optimisation levels : ', $optiLvls);
                    }
                    add_action(
                        WPActions::wa_do_base_review_preprocessing,
                        [$this, 'opti_disabled_review']
                    );
                }
                add_action(
                    WPActions::wa_do_base_review_preprocessing,
                    [$this, 'opti_common_review']
                );
            }
            /**
             * @var array<int, array> $BLOCKED_URL_REVIEW_REPORT List of blocked url report
             */
            protected $blockedUrlReviewReport = [];
            /**
             * @var array<int, array> $BLOCKED_URL_REVIEW_REPORT List of allowed url report
             */
            protected $allowedUrlReviewReport = [];
            protected $optiBlockedUrlBckupPeriode = (30*60*1000); 
            /**
             * @var string $BLOCKED_URL_REVIEW_REPORT Transient key for the list of blocked url report
             */
            public $BLOCKED_URL_REVIEW_REPORT = "wa_blocked_url_review_report";
            /**
             * @var string $BLOCKED_URL_REVIEW_REPORT Transient key for the list of allowed url report
             */
            public $ALLOWED_URL_REVIEW_REPORT = "wa_allowed_url_review_report";
            /**
             * Add a 30 minutes backlog review report about blocked url
             * 
             * It use transient cache, to keep stuff fast, check the verbose log if you wana to be sur to track all blocked links...
             * 
             * @param string    $sentinel   Name of the sentinel that did block this URL.
             * @param string    $url        The request URL.
             */
            public function opti_add_url_to_blocked_review_report($sentinel, $url) : void {
                $enableBlockedReviewReport = $this->getWaConfigOption(
                    $this->eConfOptOptiEnableBlockedReviewReport,
                    false
                );
                if (!$enableBlockedReviewReport) {
                    return; 
                }
                $this->blockedUrlReviewReport = (false === ($ar = get_transient(
                    $this->BLOCKED_URL_REVIEW_REPORT
                ))) ? [] : $ar;
                if (!$this->blockedUrlReviewReport) {
                    $this->warn("opti_add_url_to_blocked_review_report detect wrong saved report, reseting it to [] from ", $this->blockedUrlReviewReport);
                    $this->blockedUrlReviewReport = [];
                }
                if (!is_array($this->blockedUrlReviewReport)) {
                    $this->warn("opti_add_url_to_blocked_review_report 'blockedUrlReviewReport' have wrong type, should be 'array'", $this->blockedUrlReviewReport);
                    $this->blockedUrlReviewReport = [];
                }
                $this->debugVerbose("Will opti_add_url_to_blocked_review_report with '$url'");
                $report = & $this->blockedUrlReviewReport;
                $currentTime = time();
                if (!array_key_exists($url, $report)) {
                    $report[$url] = [
                        'sentinel' => $sentinel,
                        'access_log' => [],
                        'first_access' => $currentTime,
                    ];
                }
                $bckupPeriode = $this->optiBlockedUrlBckupPeriode; 
                $report[$url]['last_access'] = $currentTime;
                $report[$url]['access_log'][] = $currentTime;
                $report[$url]['access_log'] = array_filter(
                    $report[$url]['access_log'],
                    function ($time) use ($currentTime, $bckupPeriode) {
                        return ($currentTime - $time) < $bckupPeriode;
                    }
                );
                $report = array_filter(
                    $report, function ($log) use ($currentTime, $bckupPeriode) {
                        return ($currentTime - $log['last_access']) < $bckupPeriode;
                    }
                );
                set_transient(
                    $this->BLOCKED_URL_REVIEW_REPORT,
                    $this->blockedUrlReviewReport,
                    $this->optiBlockedUrlBckupPeriode / 1000
                );
            }
            /**
             * Add a 30 minutes backlog review report about allowed url
             * 
             * It use transient cache, to keep stuff fast, check the verbose log if you wana to be sur to track all followed sub links...
             * 
             * @param string    $sentinel   Name of the sentinel that allow this URL.
             * @param string    $url        The request URL.
             */
            public function opti_add_url_to_allowed_review_report($sentinel, $url) : void {
                $enableBlockedReviewReport = $this->getWaConfigOption(
                    $this->eConfOptOptiEnableBlockedReviewReport,
                    false
                );
                if (!$enableBlockedReviewReport) {
                    return; 
                }
                $this->allowedUrlReviewReport = (false === ($ar = get_transient(
                    $this->ALLOWED_URL_REVIEW_REPORT
                ))) ? [] : $ar;
                if (!is_array($this->allowedUrlReviewReport)) {
                    $this->warn("opti_add_url_to_allowed_review_report 'allowedUrlReviewReport' have wrong type, should be 'array'", $this->allowedUrlReviewReport);
                    $this->allowedUrlReviewReport = [];
                }
                $this->debugVerbose("Will opti_add_url_to_allowed_review_report with '$url'");
                $report = & $this->allowedUrlReviewReport;
                $currentTime = time();
                if (!array_key_exists($url, $report)) {
                    $report[$url] = [
                        'sentinel' => $sentinel,
                        'access_log' => [],
                        'first_access' => $currentTime,
                    ];
                }
                $bckupPeriode = $this->optiBlockedUrlBckupPeriode; 
                $report[$url]['last_access'] = $currentTime;
                $report[$url]['access_log'][] = $currentTime;
                $report[$url]['access_log'] = array_filter(
                    $report[$url]['access_log'],
                    function ($time) use ($currentTime, $bckupPeriode) {
                        return ($currentTime - $time) < $bckupPeriode;
                    }
                );
                $report = array_filter(
                    $report, function ($log) use ($currentTime, $bckupPeriode) {
                        return ($currentTime - $log['last_access']) < $bckupPeriode;
                    }
                );
                set_transient(
                    $this->ALLOWED_URL_REVIEW_REPORT,
                    $this->allowedUrlReviewReport,
                    $this->optiBlockedUrlBckupPeriode / 1000
                );
            }
            /**
             * Print the blocked url review report
             * 
             * It use transient cache, to keep stuff fast, check the verbose log if you wana to be sur to track all blocked links...
             * 
             * @param int  $reportOrder Report position in case of multiple display of same report in same page...
             */
            public function opti_print_blocked_urls_report($reportOrder = 0) : void {
                $enableBlockedReviewReport = $this->getWaConfigOption(
                    $this->eConfOptOptiEnableBlockedReviewReport,
                    false
                );
                if (!$enableBlockedReviewReport) {
                    return; 
                }
                echo "<h1> " . __(
                    "Détail des urls bloquées",
                    'monwoo-web-agency-config'/** 📜*/
                ) . " </h1>";
                $self = $this;
                $resp = "";
                $idx = 0;
                $dateFormat = "Y/m/d H:i:s O"; 
                $this->blockedUrlReviewReport = (false === ($ar = get_transient(
                    $this->BLOCKED_URL_REVIEW_REPORT
                ))) ? [] : $ar;
                if (!is_array($this->blockedUrlReviewReport)) {
                    $this->warn("opti_print_blocked_urls_report 'blockedUrlReviewReport' have wrong type, should be 'array'", $this->blockedUrlReviewReport);
                    $this->blockedUrlReviewReport = [];
                }
                $this->debug("Will opti_print_blocked_urls_report");
                $this->debugVeryVerbose("With : ", $this->blockedUrlReviewReport);
                $this->blockedUrlReviewReport = array_filter(
                    $this->blockedUrlReviewReport,
                    function ($log) use ($self) {
                        return (time() - $log['last_access']) < $self->optiBlockedUrlBckupPeriode;
                    }
                );
                set_transient(
                    $this->BLOCKED_URL_REVIEW_REPORT,
                    $this->blockedUrlReviewReport,
                    $this->optiBlockedUrlBckupPeriode / 1000
                );
                foreach ($this->blockedUrlReviewReport as $url => $log) {
                    $itemId = "wa-blocked-url-$reportOrder-$idx";
                    $timeFrame = "<span>". implode(
                        "</span> <span>",
                        array_map(function($t)
                        use ($dateFormat) {
                            return wp_date( $dateFormat, $t );
                        }, $log['access_log'])
                    ) . '</span>';
                    $lastAccess = wp_date( $dateFormat, $log['last_access']);
                    $sentinel = $log['sentinel'];
                    $countAccess = count($log['access_log']);
                    $resp .= "
                        <div class='$itemId'>
                            <p
                            data-wa-expand-target='.$itemId .wa-expand'
                            class='wa-expand-toggler'>
                                <span>[$lastAccess]</span>
                                <span>[$sentinel]</span> <br />
                                <span>[$countAccess]</span>
                                <strong>$url</strong>
                            </p>
                            <p class='wa-blocked-url-frames wa-expand wa-expand-collapsed'>
                                $timeFrame
                            </p>
                        </div>
                    ";
                    $idx ++;
                }
                echo wp_kses_post($resp);
            }
            /**
             * Print the allowed url review report
             * 
             * It use transient cache, to keep stuff fast, check the verbose log if you wana to be sur to track all allowed links...
             * 
             * @param int  $reportOrder Report position in case of multiple display of same report in same page...
             */
            public function opti_print_allowed_urls_report($reportOrder = 0) : void {
                $enableBlockedReviewReport = $this->getWaConfigOption(
                    $this->eConfOptOptiEnableBlockedReviewReport,
                    false
                );
                if (!$enableBlockedReviewReport) {
                    return; 
                }
                echo "<h1> " . __(
                    "Détail des urls autorisées",
                    'monwoo-web-agency-config'/** 📜*/
                ) . " </h1>";
                $self = $this;
                $resp = "";
                $idx = 0;
                $dateFormat = "Y/m/d H:i:s O"; 
                $this->allowedUrlReviewReport = (false === ($ar = get_transient(
                    $this->ALLOWED_URL_REVIEW_REPORT
                ))) ? [] : $ar;
                if (!is_array($this->allowedUrlReviewReport)) {
                    $this->warn("opti_print_blocked_urls_report 'allowedUrlReviewReport' have wrong type, should be 'array'", $this->allowedUrlReviewReport);
                    $this->allowedUrlReviewReport = [];
                }
                $this->debug("Will opti_print_blocked_urls_report");
                $this->debugVeryVerbose("With : ", $this->allowedUrlReviewReport);
                $this->allowedUrlReviewReport = array_filter(
                    $this->allowedUrlReviewReport,
                    function ($log) use ($self) {
                        return (time() - $log['last_access']) < $self->optiBlockedUrlBckupPeriode;
                    }
                );
                set_transient(
                    $this->ALLOWED_URL_REVIEW_REPORT,
                    $this->allowedUrlReviewReport,
                    $this->optiBlockedUrlBckupPeriode / 1000
                );
                foreach ($this->allowedUrlReviewReport as $url => $log) {
                    $itemId = "wa-blocked-url-$reportOrder-$idx";
                    $timeFrame = "<span>". implode(
                        "</span> <span>",
                        array_map(function($t)
                        use ($dateFormat) {
                            return wp_date( $dateFormat, $t );
                        }, $log['access_log'])
                    ) . '</span>';
                    $lastAccess = wp_date( $dateFormat, $log['last_access']);
                    $sentinel = $log['sentinel'];
                    $countAccess = count($log['access_log']);
                    $resp .= "
                        <div class='$itemId'>
                            <p
                            data-wa-expand-target='.$itemId .wa-expand'
                            class='wa-expand-toggler'>
                                <span>[$lastAccess]</span>
                                <span>[$sentinel]</span> <br />
                                <span>[$countAccess]</span>
                                <strong>$url</strong>
                            </p>
                            <p class='wa-blocked-url-frames wa-expand wa-expand-collapsed'>
                                $timeFrame
                            </p>
                        </div>
                    ";
                    $idx ++;
                }
                echo wp_kses_post($resp);
            }
            /**
             * Filter internal WP HTTP calls as requested by '' WA Config option
             * 
             * @param false|array|WP_Error  $preempt     A preemptive return value of an HTTP request. Default false.
             * @param array                 $parsed_args HTTP request arguments.
             * @param string                $url         The request URL.
             * @return false|array|WP_Error The new preemptive return value
             */
            public function opti_filter_wp_http_requests($preempt, $parsed_args, $url) {
                $regExFilter = $this->getWaConfigOption(
                    $this->eConfOptOptiWpRequestsFilter,
                    ""
                );
                if (is_string($regExFilter) && strlen($regExFilter)
                && preg_match($regExFilter, $url)) {
                    $this->debug("Will opti_filter_wp_http_requests with $regExFilter and BLOCK $url");
                    $safeFilter = $this->getWaConfigOption(
                        $this->eConfOptOptiWpRequestsSafeFilter,
                        $this->E_DEFAULT_OPTIMISABLE_SAFE_FILTER
                    );
                    if (is_string($safeFilter) && strlen($safeFilter)
                    && preg_match($safeFilter, $url)) {
                        $this->debugVerbose("opti_filter_wp_http_requests whitelisted by $safeFilter");
                        $this->opti_add_url_to_allowed_review_report($this->iId . '-filtered', $url);
                        return $preempt;
                    }
                    $enableNotice = $this->getWaConfigOption(
                        $this->eConfOptOptiEnableBlockedHttpNotice,
                        false
                    );
                    if ($enableNotice) {
                        Notice::displayInfo("$regExFilter ".
                        __(" : BLOQUE l'url : ", 'monwoo-web-agency-config'/** 📜*/)
                        ." $url");
                    }
                    $this->opti_add_url_to_blocked_review_report($this->iId . '-filtered', $url);
                    $this->debugVerbose("opti_filter_wp_http_requests blocked by $regExFilter");
                    return array(
                        'headers'       => array(
                            'X-Blocked-Http-by' => $this->iId
                        ),
                        'body'          => '',
                        'response'      => array(
                            'code'    => false,
                            'message' => false,
                        ),
                        'cookies'       => array(),
                        'http_response' => null,
                    );
                } else {
                    $this->opti_add_url_to_allowed_review_report($this->iId . '-filtered', $url);
                }
                return $preempt;
            }
            /**
             * Optimise server external sub call
             * 
             * @return bool True on setup success 
             */
            protected function opti_setup_for_max_speed(): bool
            {
                if (!$this->opti_setup_for_medium_speed()) {
                    return false; 
                }
                $this->debugVerbose("Will opti_setup_for_max_speed");
                add_filter( 'plugins_auto_update_enabled', '__return_false' );    
                add_filter( 'themes_auto_update_enabled', '__return_false' );
                $WPAutoUpdateOff = defined( 'AUTOMATIC_UPDATER_DISABLED')
                && constant('AUTOMATIC_UPDATER_DISABLED');
                $WPHostAutoUpdateOff = defined( 'WP_AUTO_UPDATE_CORE')
                && !constant('WP_AUTO_UPDATE_CORE');
                return $WPAutoUpdateOff && $WPHostAutoUpdateOff;
            }
            /**
             * Optimise server self call
             * 
             * @return bool True on setup success 
             */
            protected function opti_setup_for_medium_speed(): bool
            {
                $this->debugVerbose("Will opti_setup_for_medium_speed");
                return defined('DISABLE_WP_CRON');
            }
            /**
             * Add the MAX speed optimisation reviews
             * 
             * @param AppInterface $app the plugin instance.
             */
            public function opti_max_speed_review($app): void
            {
                $this->debugVerbose("Will opti_max_speed_review");
                $isOk = true;
                $reviewReport = '';
                $WPAutoUpdateOff = defined( 'AUTOMATIC_UPDATER_DISABLED')
                && constant('AUTOMATIC_UPDATER_DISABLED');
                if (!$WPAutoUpdateOff) {
                    $reviewReport .=
                    __("<p> AUTOMATIC_UPDATER_DISABLED doit être définit à 'true' dans wp-config.php.</p>", 'monwoo-web-agency-config'/** 📜*/);
                    $this->warn("Fail to ensure AUTOMATIC_UPDATER_DISABLED is true");
                }
                $isOk = $isOk && $WPAutoUpdateOff;
                $WPHostAutoUpdateOff = defined( 'WP_AUTO_UPDATE_CORE')
                && !constant('WP_AUTO_UPDATE_CORE');
                if (!$WPHostAutoUpdateOff) {
                    $reviewReport .=
                    __("<p> WP_AUTO_UPDATE_CORE doit être définit à 'false' dans wp-config.php.</p>", 'monwoo-web-agency-config'/** 📜*/);
                    $this->warn("Fail to ensure WP_AUTO_UPDATE_CORE is defined and falsy");
                }
                $isOk = $isOk && $WPHostAutoUpdateOff;
                $this->e_review_data_check_insert([
                    'category' => __('02 - Maintenance', 'monwoo-web-agency-config'/** 📜*/),
                    'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>',
                    'title' => __("03 - [Optimisations] Niveau maximal", 'monwoo-web-agency-config'/** 📜*/),
                    'title_icon' => '<span class="dashicons dashicons-chart-bar"></span>',
                    'requirements' => __( 'Vérification de la présence des optimisations maximales.<br />',
                    'monwoo-web-agency-config'/** 📜*/ ) . $reviewReport,
                    'value' => strlen($reviewReport)
                    ? (
                        $isOk
                        ? null
                        : __( 'Ajustez les configurations nécessaires puis rafraichir cette page.', 'monwoo-web-agency-config'/** 📜*/)
                    )
                    : '',
                    'result'   => $isOk ,
                    'is_activated'   => true,
                    'fixed_id' => "{$this->iId}-data-review-opti-full",
                    'is_computed' => true,
                ]);
                $this->opti_medium_speed_review($app);
            }
            /**
             * Add the MEDIUM speed optimisation reviews
             * 
             * @param AppInterface $app the plugin instance.
             */
            public function opti_medium_speed_review($app): void
            {
                $this->debugVerbose("Will opti_medium_speed_review");
                $isOk = true;
                $reviewReport = '';
                $cronDisabled = defined( 'DISABLE_WP_CRON')
                && constant('DISABLE_WP_CRON');
                if (!$cronDisabled) {
                    $reviewReport .=
                    __("<p> DISABLE_WP_CRON doit être définit à 'true' dans wp-config.php et vos tache cron géré par un autre service externe.</p>", 'monwoo-web-agency-config'/** 📜*/);
                    $this->warn("Fail to ensure DISABLE_WP_CRON is true");
                }
                $isOk = $isOk && $cronDisabled;
                $this->e_review_data_check_insert([
                    'category' => __('02 - Maintenance', 'monwoo-web-agency-config'/** 📜*/),
                    'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>',
                    'title' => __("03 - [Optimisations] Niveau moyen", 'monwoo-web-agency-config'/** 📜*/),
                    'title_icon' => '<span class="dashicons dashicons-chart-bar"></span>',
                    'requirements' => __( 'Vérification de la présence des optimisations moyennes.<br />',
                    'monwoo-web-agency-config'/** 📜*/ ) . $reviewReport,
                    'value' => strlen($reviewReport)
                    ? (
                        $isOk
                        ? null
                        : __( 'Ajustez les configurations nécessaires puis rafraichir cette page.', 'monwoo-web-agency-config'/** 📜*/)
                    )
                    : '',
                    'result'   => $isOk ,
                    'is_activated'   => true,
                    'fixed_id' => "{$this->iId}-data-review-opti-medium",
                    'is_computed' => true,
                ]);
            }
            /**
             * Add the no optimisation reviews (Disabled optimisations)
             * 
             * @param AppInterface $app the plugin instance.
             */
            public function opti_disabled_review($app): void
            {
                $this->debugVerbose("Will opti_disabled_review");
                $this->eReviewIdsToTrash = array_merge($this->eReviewIdsToTrash, [
                    "{$this->iId}-data-review-opti-medium",
                    "{$this->iId}-data-review-opti-full"
                ]);
            }
            /**
             * Add the common optimisation reviews
             * 
             * @param AppInterface $app the plugin instance.
             */
            public function opti_common_review($app): void
            {
                $this->debugVerbose("Will opti_common_review");
                $self = $this;
                $urlBuilder = function($pluginSlug) {
                    return admin_url(
                        "plugin-install.php?tab=plugin-information&plugin=$pluginSlug"
                    );
                };
                $plugins = get_option( 'active_plugins', []);
                $plugins = array_map(function($p) {
                    return dirname($p);
                }, $plugins);
                $pluginReviewer = function($pluginRequest, $pluginLinkTitle, $pluginSlug, $extraPlugins = []) 
                use ($self, $plugins, $urlBuilder) {
                    $url = $urlBuilder($pluginSlug);
                    $isPluginActivated = false;
                    $activationReport = ""; 
                    $localPluginBase = $pluginSlug;
                    if ( false !== ($loadOrder = array_search(
                        $localPluginBase,
                        $plugins,
                    )) ) {
                        $isPluginActivated = true;
                    }
                    $this->debugVeryVerbose("Plugin check : ", $localPluginBase, $plugins);
                    $extras = "";
                    foreach ($extraPlugins as $extraPluginSlug => $extraPluginData) {
                        if (is_string($extraPluginData)) {
                            $extraPluginData = [
                                'title' => $extraPluginData,
                                'type' => 'extra',
                            ];
                        }
                        if ('alternative' === $extraPluginData['type'] && !$isPluginActivated) {
                            if ( false !== ($loadOrder = array_search(
                                $extraPluginSlug,
                                $plugins,
                            )) ) {
                                $isPluginActivated = true;
                                $activationReport .= __('Alternative OK :', 'monwoo-web-agency-config'/** 📜*/)
                                . " $extraPluginSlug";
                            }
                        }
                        $extraUrl = $urlBuilder($extraPluginSlug);
                        $extras .= "<p><a
                        data-title='{$extraPluginData['title']}'
                        target='_blank'
                        href='$extraUrl'>
                          {$extraPluginData['title']}
                        </a></p>";
                    }
                    $this->e_review_data_check_insert([
                        'category' => __('02 - Maintenance', 'monwoo-web-agency-config'/** 📜*/),
                        'category_icon' => '<span class="dashicons dashicons-admin-tools"></span>',
                        'title' => __("01 - Plugin : ", 'monwoo-web-agency-config'/** 📜*/) . $localPluginBase,
                        'title_icon' => '<span class="dashicons dashicons-dashboard"></span>',
                        'requirements' => "$pluginRequest <a target='_blank' data-title='$pluginLinkTitle' href='$url'>$pluginLinkTitle</a> $extras",
                        'value'    => $isPluginActivated ? $activationReport : __('Validation humaine requise.', 'monwoo-web-agency-config'/** 📜*/),
                        'result'   => $isPluginActivated,
                        'is_activated'   => true,
                        'fixed_id' => "{$this->iId}-check-plugin-$localPluginBase",
                        'is_computed' => true,
                    ]);
                };
                $pluginReviewer(
                    __( 'Internationalisation continue de votre contenu web en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'Polylang', 'monwoo-web-agency-config'/** 📜*/ ),
                    'polylang',
                    [
                        'loco-translate' => __( 'Bonus : Loco Translate', 'monwoo-web-agency-config'/** 📜*/ ),
                        'automatic-translator-addon-for-loco-translate' => __( 'Bonus : Automatic Translate Addon For Loco Translate', 'monwoo-web-agency-config'/** 📜*/ ),
                        'translatepress-multilingual' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : TranslatePress - Multilingual', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                        'automatic-translate-addon-for-translatepress' => __( 'Bonus : Automatic Translate Addon For TranslatePress', 'monwoo-web-agency-config'/** 📜*/ ),
                        'gtranslate' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : Translate WordPress with GTranslate', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                    ]
                );
                $pluginReviewer(
                    __( 'Mise en cache et optimisations en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'LiteSpeed Cache', 'monwoo-web-agency-config'/** 📜*/ ),
                    'litespeed-cache',
                    [
                        'w3-total-cache' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : W3 Total Cache', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                        'wp-optimize' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : WP-Optimize', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                        'wp-super-cache' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : WP Super Cache', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                        'sg-cachepress' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : SiteGround Optimizer', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                        'use-memcached' => __( "Bonus : Utiliser 'Use Memcached' avec d'autres caches ne faisant pas de cache objet (Ex : SiteGround Optimizer).", 'monwoo-web-agency-config'/** 📜*/ ),
                    ]
                );
                $pluginReviewer(
                    __( 'Amélioration du SEO (Search Engine Optimisation) en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'All in One SEO', 'monwoo-web-agency-config'/** 📜*/ ),
                    'all-in-one-seo-pack',
                    [
                        'wordpress-seo' => [
                            'type' => 'alternative',
                            'title' => __( 'Alternative : Yoast SEO', 'monwoo-web-agency-config'/** 📜*/ ),
                        ],
                        'stockpack' => __( "Bonus : StockPack configuré sur Pixabay.", 'monwoo-web-agency-config'/** 📜*/ ),
                    ]
                );
                $pluginReviewer(
                    __( 'Suivi des utilisateurs en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'History Log by click5', 'monwoo-web-agency-config'/** 📜*/ ),
                    'history-log-by-click5',
                    [
                        'woocommerce' => __( "Bonus : Activer WooCommerce.", 'monwoo-web-agency-config'/** 📜*/ ),
                        'woocommerce-pdf-invoices-packing-slips' => __( "Bonus : Activer WooCommerce PDF Invoices & Packing Slips.", 'monwoo-web-agency-config'/** 📜*/ ),
                    ]
                );
                $pluginReviewer(
                    __( 'Suivi des emails en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'Check & Log Email', 'monwoo-web-agency-config'/** 📜*/ ),
                    'check-email'
                );
                $pluginReviewer(
                    __( 'Suivi des CRON en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'WP Crontrol', 'monwoo-web-agency-config'/** 📜*/ ),
                    'wp-crontrol'
                );
                $pluginReviewer(
                    __( 'Ajustements divers de la base de données en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'Better Search Replace', 'monwoo-web-agency-config'/** 📜*/ ),
                    'better-search-replace'
                );
                $this->eReviewIdsToTrash = array_merge($this->eReviewIdsToTrash, [
                    "{$this->iId}-check-plugin-pods",
                ]);    
                $pluginReviewer(
                    __( 'Ajustements des posts et taxonomies personalisées en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                    __( 'Display Post Types – Post Grid, post list and post sliders', 'monwoo-web-agency-config'/** 📜*/ ),
                    'display-post-types', [
                        'custom-post-type-widgets' =>  __( 'Bonus : Custom Post Type Widgets', 'monwoo-web-agency-config'/** 📜*/ ),
                    ]
                ); 
            if ($this->shouldDebug|| $this->shouldDebugVerbose
                || $this->shouldDebugVeryVerbose) {
                    $pluginReviewer(
                        __( 'ATTENTION : MODE DEBUG activé, non conseillé en production. Cela dis, optimisons les débugs en activant le plugin :', 'monwoo-web-agency-config'/** 📜*/ ),
                        __( 'Query Monitor', 'monwoo-web-agency-config'/** 📜*/ ),
                        'query-monitor'
                    );
                } else {
                    $this->eReviewIdsToTrash = array_merge($this->eReviewIdsToTrash, [
                        "{$this->iId}-check-plugin-query-monitor",
                    ]);    
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
    use WA\Config\Utils;
    if (!trait_exists(EditableScripts::class)) { 
        /**
         * This trait will load wa-config frontend javascript sources
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         */
        trait EditableScripts
        {
            use Editable;
            protected function  _010_e_scripts__load()
            {
                if (wp_is_json_request() || is_admin()) {
                    return; 
                }
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_scripts__load')) {
                    return; 
                }
                $shouldRender = $this->getWaConfigOption(
                    $this->eConfShouldRenderFrontendScripts,
                    true
                );
                if ($shouldRender) {
                    add_action(
                        'wp_enqueue_scripts',
                        [$this, 'e_scripts_do_enqueue']
                    );
                }
            }
            /**
             * wp_enqueue_script the frontend assets/app.js script from plugin directory.
             */
            public function e_scripts_do_enqueue(): void
            {
                $this->debugVerbose("Will e_scripts_do_enqueue");
                $jsFile = "assets/app.js";
                wp_enqueue_script(
                    "{$this->iPrefix}-js",
                    plugins_url($jsFile, $this->pluginFile),
                    [],
                    $this->pluginVersion,
                    true
                );
            }
        }
    }
    if (!trait_exists(EditableStyles::class)) { 
        /**
         * This trait will load wa-config frontend stylesheets sources
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses Editable
         */
        trait EditableStyles
        {
            use Editable;
            protected function _010_e_styles__load()
            {
                if (wp_is_json_request() || is_admin()) {
                    return; 
                }
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_styles__load')) {
                    return; 
                }
                $shouldRender = $this->getWaConfigOption(
                    $this->eConfShouldRenderFrontendScripts,
                    true
                );
                if ($shouldRender) {
                    add_action(
                        'wp_enqueue_scripts',
                        [$this, 'e_styles_do_enqueue']
                    );
                }
            }
            /**
             * wp_enqueue_style the frontend assets/styles.css script from plugin directory.
             */
            public function e_styles_do_enqueue(): void
            {
                $this->debugVerbose("Will e_styles_do_enqueue");
                $cssFile = "assets/styles.css";
                wp_enqueue_style(
                    "{$this->iPrefix}-css",
                    plugins_url($cssFile, $this->pluginFile),
                    [],
                    $this->pluginVersion
                );
            }
        }
    }
    if (!trait_exists(EditableFooter::class)) { 
        /**
         * This trait will render the frontend footer based on wa-config options
         *
         * @since 0.0.1
         * @author service@monwoo.com
         * @uses EditableWaConfigOptions
         * @uses Debugable
         */
        trait EditableFooter
        {
            use EditableWaConfigOptions, Debugable;
            protected function _010_e_footer__load()
            {
                if (wp_is_json_request() || is_admin()) {
                    return; 
                }
                if ($this->p_higherThanOneCallAchievedSentinel('_010_e_footer__load')) {
                    return; 
                }
                $currentTheme = basename(get_parent_theme_file_path());
                $enableFooter = boolVal($this->getWaConfigOption(
                    $this->eConfOptEnableFooter,
                    false
                ));
                if ($enableFooter) {
                    $this->debugVerbose("Will _010_e_footer__load for theme '$currentTheme'");
                    add_filter(
                        'storefront_credit_links_output',
                        [$this, 'e_footer_render']
                    );
                    if ('twentytwenty' === $currentTheme
                    || 'restaurant-food-delivery' === $currentTheme) {
                        add_action('wp_footer',
                        [$this, 'e_footer_do_wp_footer_twentytwenty'], 20);
                    }
                    if ('oceanwp' === $currentTheme) {
                        add_action('ocean_after_footer_bottom_inner',
                        [$this, 'e_footer_do_wp_footer_twentytwenty'], 20);
                    }
                    if ('twentytwentytwo' === $currentTheme) {
                        add_filter('render_block', [$this, 'e_footer_filter_render_block_twentytwentytwo'], null, 3);
                    }
                } else {
                    $this->debugVerbose("_010_e_footer__load not enabled, cf WA Admin configs params");
                }
            }
            /**
             * Customise the 'footer' 'template part' of 'twentytwentytwo' theme
             * 
             * Register with WordPress filter 'render_block' if current theme is 'twentytwentytwo'
             * 
             * @param string   $block_content The block content about to be appended.
             * @param array    $block         The full block, including name and attributes.
             * @param WP_Block $bInst         The block instance.
             * @return string The filtered blocks ajusted with the wa-config footer rendering
             */
            function e_footer_filter_render_block_twentytwentytwo(
                string $block_content, 
                array $block,
                \WP_Block $bInst
            ) : string {
                $blockName = $block['blockName'] ?? "__no-block-name__";
                $bInnerHTML = $block['innerHTML'];
                $this->debugVeryVerbose("Will e_footer_filter_render_block_twentytwentytwo for $blockName");
                if (
                    $blockName === 'core/paragraph' && 
                    !is_admin() &&
                    !wp_is_json_request()
                ) {
                    $this->debugVeryVerbose($blockName, $bInst->block_type,
                    $bInnerHTML, $bInst->context, $bInst->available_context);                    
                    if (strpos($bInnerHTML,
                    '<p class="has-text-align-right">') && strpos($bInnerHTML,
                    'wordpress.org" rel="nofollow">WordPress</a>')) {
                        return $this->e_footer_render(false);
                    }
                }
                return $block_content;
            }
            /**
             * Hide the template footer and inject WA Admin configured footer
             * 
             * Work for 'twentytwenty', 'twentytwentytow','oceanwp'
             * and 'restaurant-food-delivery' themes
             * 
             */
            function e_footer_do_wp_footer_twentytwenty() : void {
                $enableFooter = boolVal($this->getWaConfigOption(
                    $this->eConfOptEnableFooter,
                    false
                ));
                $currentTheme = basename(get_parent_theme_file_path());
                if ($enableFooter && (
                    'twentytwenty' === $currentTheme
                    || 'twentytwentytwo' === $currentTheme
                    || 'oceanwp' === $currentTheme
                    || 'restaurant-food-delivery' === $currentTheme
                )) {
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
                    echo wp_kses_post($this->e_footer_render());
                }
            }
            /**
             * Modify the twentytwenty footer template to fit our WA Config admin rules
             * @see {\WA\Config\Templates\}
             * TODO : not used refactor ? since language switch will faill with current code and css way is faster...
             */
            private function e_footer_locate_template_NOT_USED_YET($templatePath) { 
                $this->debugVerbose("Will try e_footer_locate_template for $templatePath");
                $originalPath = $templatePath;
                $templateParts = explode('/', $templatePath);
                $templateName = end($templateParts);
                $currentTheme = basename(get_parent_theme_file_path());
                if ('twentytwenty' === $currentTheme) {
                    $this->debugVeryVerbose("e_footer_locate_template overloads for $currentTheme");
                    $plugin_template = "{$this->pluginRoot}templates/themes/twentytwenty/$templatePath";
                    if (file_exists($plugin_template)) { 
                        $templatePath = $plugin_template;
                    }
                    else 
                    if ($theme_template = locate_template("$templatePath")) {
                        $templatePath = $theme_template;
                    }
                } else {
                    if ($theme_template = locate_template("$templatePath")) {
                        $templatePath = $theme_template;
                    } else {
                        $plugin_template = "{$this->pluginRoot}$templatePath";
                        if (file_exists($plugin_template)) {
                            $templatePath = $plugin_template;
                        }
                    }
                }
                if ($templatePath !== $originalPath) {
                    $this->debugVerbose("e_footer_locate_template update from $originalPath to $templatePath");
                }
                return $templatePath;
            }
            private function e_footer_modify_theme_include_file_NOT_USED_YET(
                string $path,
                string $file = ''
            ): string {
                $this->debugVerbose("Will try e_footer_modify_theme_include_file $file at $path");
                $currentTheme = basename(get_parent_theme_file_path());
                if ('twentytwenty' === $currentTheme) {
                    $this->debugVeryVerbose("e_footer_modify_theme_include_file tesing $file overloads");
                    $targetOverload = plugin_dir_path(__FILE__) . "templates/themes/twentytwenty/$file";
                    if (file_exists($targetOverload)) {
                        $this->debugVeryVerbose(
                            "Will e_footer_modify_theme_include_file at $file with $targetOverload"
                        );
                        return $targetOverload;
                    }
                }
                return $path;
            }
            protected function e_footer_get_languages() {
                $locales = [get_locale()];
                if (function_exists('pll_languages_list')) {
                    $locales = pll_languages_list(array('fields' => 'locale'));
                    $this->debugVeryVerbose("Will e_footer_get_languages for Polylangs : ", $locales);
                }
                return $locales;
            }
            protected function e_footer_get_empty_string_by_locale() {
                $locales = $this->e_footer_get_languages();
                $this->debugVerbose("Will e_footer_get_empty_string_by_locale");
                $localizedCredit = [];
                array_walk($locales, function($l)
                use (&$localizedCredit) {
                    $localizedCredit[$l] = "";
                });
                return $localizedCredit;
            }
            protected function e_footer_get_default_credit_by_locale() {
                $locales = $this->e_footer_get_languages();
                $this->debugVerbose("Will e_footer_get_default_credit_by_locale");
                $localizedCredit = [];
                array_walk($locales, function($l)
                use (&$localizedCredit) {
                    $localizedCredit[$l] = Utils\_x("autre", "Footer default credit", 'monwoo-web-agency-config'/** 📜*/, $l);
                    _x("autre", "Footer default credit", 'monwoo-web-agency-config'/** 📜*/);
                });
                return $localizedCredit;
            }
            protected function e_footer_get_localized_credit() {
                $this->debugVerbose("Will e_footer_get_localized_credit");
                $localizedCredit = $this->e_footer_get_default_credit_by_locale();
                $waFooterCreditByLocale = $this->getWaConfigOption(
                    $this->eConfOptFooterCredit,
                    $localizedCredit
                );
                if (!is_array($waFooterCreditByLocale)) {
                    $waFooterCreditByLocale = [];
                }
                $waFooterCreditByLocale = array_merge($localizedCredit, $waFooterCreditByLocale);
                $locale = get_locale();
                $waFooterCredit = $waFooterCreditByLocale[$locale] ?? null;
                if (!$waFooterCredit) {
                    $defaultLocale = 'fr_FR'; 
                    if (function_exists( 'pll_default_language' ) ) {
                        $defaultLocale = pll_default_language('locale');
                    }
                    $waFooterCredit = $waFooterCreditByLocale[$defaultLocale] ?? "";
                }
                return $waFooterCredit;
            }
            /**
             * get the web agency footer template for current or specific local
             * 
             * @param string $locale the locale to use to get the web agency footer, null to use default locale
             * @return string the web agency footer or empty string if not found
             * 
             * @since 0.0.1
             */
            public function e_footer_get_localized_template($locale = null) {
                $locales = $this->e_footer_get_languages();
                $this->debugVerbose("Will e_footer_get_localized_template");
                $localizedTemplates = [];
                array_walk($locales, function($l)
                use (&$localizedTemplates) {
                    $localizedTemplates[$l] = "";
                });
                $waFooterTemplateByLocale = $this->getWaConfigOption(
                    $this->eConfOptFooterTemplate,
                    $localizedTemplates
                );
                if (!$locale) {
                    $locale = get_locale();
                }
                $waFooterTemplate = $waFooterTemplateByLocale[$locale] ?? null;
                if (!$waFooterTemplate) {
                    $defaultLocale = 'fr_FR'; 
                    if (function_exists( 'pll_default_language' ) ) {
                        $defaultLocale = pll_default_language('locale');
                    }
                    $waFooterTemplate = $waFooterTemplateByLocale[$defaultLocale] ?? "";
                }
                return wp_kses_post($waFooterTemplate);
            }
            /**
             * Return the rendered Frontend footer acordingly to wa-config Admin options :
             * 
             * - Will not render if footer edit is not enabled from admin
             * - Will render the full footer template if provided
             * - Will render wa-config plugin footer template othewise
             * 
             * @return string|boolean The rendered html if did render, false otherwise,
             * false if no footer rendering activated from WA Admin config
             * 
             * @see WPFilters::wa_e_footer_render
             */
            public function e_footer_render()
            {
                if (!boolVal($this->getWaConfigOption(
                    $this->eConfOptEnableFooter,
                    false
                ))) {
                    $this->debugVerbose("e_footer_render not enabled");
                    return false;
                }
                $this->debugVerbose("Will e_footer_render");
                $htmlFooter = null; 
                $waFooterTemplate = $this->e_footer_get_localized_template();
                if (strlen($waFooterTemplate) > 0) {
                    $this->debugVeryVerbose("e_footer_render from eConfOptFooterTemplate");
                    $htmlFooter = $waFooterTemplate;
                } else {
                    $waFooterCredit = $this->e_footer_get_localized_credit();
                    $mailTarget = get_option( 'admin_email' );
                    $monwooCredit = __("Build by Monwoo and", 'monwoo-web-agency-config'/** 📜*/);
                    $htmlFooter = "
                    <footer id='wa-site-footer' class='header-footer-group'>
                        <div class='section-inner'>
                            <div class='footer-credits'>
                                <p class='powered-by-monwoo powered-by-web-agency-app'>
                                    <a href='$this->siteBaseHref/credits'>
                                        $monwooCredit $waFooterCredit
                                    </a>
                                    <br />
                                    <a href='mailto:$mailTarget'>
                                        $mailTarget
                                    </a>
                                </p>

                            </div>
                        </div>
                    </footer>
                    ";
                }
                $this->debugVeryVerbose("e_footer_render from eConfOptFooterCredit and custom wa-config template");
                /**
                 * @see WPFilters::wa_e_footer_render
                 */
                $htmlFooter = apply_filters( WPFilters::wa_e_footer_render, $htmlFooter, $this );
                return $htmlFooter;                
            }
        }
    }
}
namespace WA\Config {
    use WA\Config\Admin\ApiFrontHeadable;
    use WA\Config\Admin\ApiInstanciable;
    use WA\Config\Core\AppInterface;
    use WA\Config\Core\TestableSamples;
    use WA\Config\Admin\EditableConfigPanels;
    use WA\Config\Admin\EditableReview;
    use WA\Config\Admin\EditableMissionPost;
    use WA\Config\Admin\EditableSkillsTaxo;
    use WA\Config\Admin\ExtendablePluginDescription;
    use WA\Config\Admin\Optimisable;    
    use WA\Config\Frontend\EditableScripts;
    use WA\Config\Frontend\EditableStyles;
    use WA\Config\Frontend\EditableFooter;
    use WA\Config\Utils\TranslatableProduct;
    $current_WA_Version = "0.0.3";
    $pFolder = basename(plugin_dir_path(__FILE__));
    if ($pFolder === 'src') { 
        $pFolder = basename(plugin_dir_path(__DIR__));
    }
    $pluginSrcPath = "$pFolder/wa-config.php";
    if (class_exists(App::class)) { 
        $existing_WA_Version = AppInterface::PLUGIN_VERSION;
        $app = AppInterface::instanceByRelativeFile($pluginSrcPath, -1);
        $logMsg = "$pluginSrcPath : Will not load WA\\Config\\ since already loaded somewhere else
        at version $existing_WA_Version for requested version $current_WA_Version";
        $waConfigTextDomain = 'monwoo-web-agency-config'/** 📜*/;
        if ($current_WA_Version !== $existing_WA_Version) {
            AppInterface::addCompatibilityReport(
                __("Avertissement", $waConfigTextDomain),
                "$pluginSrcPath : $current_WA_Version. " . __(
                    "Version pre-chargé WA\\Config\\ non exacte : ",
                    $waConfigTextDomain
                ) . " $existing_WA_Version.",
            );
        } else {
        }
    } else {
        /**
         * This class is the main wa-config App instance class
         * 
         * **WA\Config\App** come with :
         * - **Skills and missions** concepts ready to use as taxonomy and custom post type
         * - **Internaionalisation** and **WooCommerce** integration
         * - A **securised REST API** to deploy custom static HTML front head
         * - A **commonJS deploy script** to easyliy deploy your static frontend 
         * - A **review system** for all team members using this plugin
         * - **Codeception** as end to end test tool
         * - **PhpDocumentor output** as an up to date HTML documentation
         * - **Pdf.js** for quick display of main documentation files
         * - results of **Miguel Monwoo R&D** for **parallel programmings** and **advanced integrations**
         *
         * If you want to use it as a **standalone library**, you will have to :
         *  - define ```WPINC```
         *  - define missing WordPress functions (or include ```./wp-load.php```)
         *  - define optional WooCommerce plugin functions
         *  - See {@see App::__construct()} for class instanciation usage :
         *    ```php
         *    $inst = new \WA\Config\App(...);
         *    ```
         *  - run the ```bootstrap``` method of the previously instanciated class :
         *    ```php
         *    $inst->bootstrap();
         *    ```
         *  - Cf : <a href="files/wa-config.html">wa-config.php</a>
         * 
         * Monwoo Web Agency Config will help with **Web Agency jobs** like :
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
         * 
         * {@link https://moonkiosk.monwoo.com/en/missions/wa-config-monwoo_en Product owner}
         *
         * {@link https://codeception.com/docs/03-AcceptanceTests End to end test documentation}
         *
         * {@link https://github.com/mozilla/pdf.js PDF viewer lib}
         *
         * {@link https://miguel.monwoo.com Miguel Monwoo R&D}
         * 
         * {@link https://www.monwoo.com/don Author Donate link}
         * 
         * @global *{@see \WA\Config\Core\AppInterface AppInterface}* **$_wa_fetch_instance**
         *    Function to get the first wa-config instance
         * 
         *    **@param** *int* **$idx** Optional, wa-config instance index, **default to 0**
         *    {@example
         *    ```php
         *    $app = $_wa_fetch_instance();
         *    ```}
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         */
        class App extends AppInterface
        {
            use EditableScripts;
            use EditableStyles;
            use EditableFooter;
            use EditableConfigPanels;
            use EditableReview;
            use EditableMissionPost;
            use EditableSkillsTaxo;
            use ExtendablePluginDescription;
            use Optimisable;
            use TranslatableProduct;
            use ApiFrontHeadable;
            use ApiInstanciable;
            /** use TestableSamples; **/
            /**
             * App constructor.
             *
             * @param string $siteBaseHref This web site base URL
             * @param string $pluginFile The file path of the loaded plugin
             * @param string $iPrefix The instance prefix to use for iId generations
             * @param bool|array<int, bool> $shouldDebug True if should debug or Array of 3 boolean for each debug verbosity level
             * @return void
             */
            public function __construct(
                string $siteBaseHref,
                string $pluginFile,
                string $iPrefix,
                $shouldDebug 
            ) {
                if (is_array($shouldDebug)) {
                    [$this->shouldDebug, $this->shouldDebugVerbose, $this->shouldDebugVeryVerbose]
                        = $shouldDebug;
                } else {
                    $this->shouldDebug = $shouldDebug;
                }
                $this->siteBaseHref = $siteBaseHref;
                $this->pluginFile = $pluginFile;
                $this->pluginRoot = plugin_dir_path($this->pluginFile);
                $this->pluginName = basename($this->pluginRoot); 
                $this->pluginRelativeFile = "{$this->pluginName}/" . basename($this->pluginFile);
                $this->pluginVersion = AppInterface::PLUGIN_VERSION;
                AppInterface::__construct($iPrefix);
                /**
                 * @since 0.0.1
                 * @global *{@see \WA\Config\Core\AppInterface AppInterface}* **$_wa_fetch_instance**
                 *    Function to get the first wa-config instance
                 * 
                 *    **@param** *int* **$idx** Optional, wa-config instance index, **default to 0**
                 *    {@example
                 *    ```php
                 *    $app = $_wa_fetch_instance();
                 *    ```}
                 * }
                 */
                global $_wa_fetch_instance; 
                if (!$_wa_fetch_instance) {
                    $_wa_fetch_instance = function($idx = 0) {
                        return AppInterface::instance($idx);
                    };
                    $firstInstance = $_wa_fetch_instance();
                    $firstInstance->debug("Did register '\$_wa_fetch_instance' global from "
                    . "{$firstInstance->pluginRelativeFile}");
                }
                $this->debugVeryVerbose("Construct WA\Config\App instance for {$this->pluginRelativeFile}");
            }
        }
    }
}
