<?php

/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 *
 * __WARNING__ : USING TESTS IN __PRODUCTION__ NEEDS 
 * __SERIOUS BACKUPS__ STRATEGIES
 *
 * __BE CARFULL WITH__ PRODUCTION __DATA__,
 * DO NOT MESS UP WITH REAL BUISINESS DATA FROM YOUR TESTS.
 * 
 * FOR THIS USE CASE, __ONLY CHANGE__ CONFIG 
 * __THAT CAN BE MANUALLY__ RESTORED FROM ADMIN PANEL 
 * to stay in the __"Safe zone"__.
 *
 * This end to end user test is an example of a __roll back test__.
 * See {@link https://codeception.com/docs/03-AcceptanceTests
 * Codeception}
 * documentation for more advanced usage.
 * 
 * It will also show you how to use the saved wa-config options
 * from the admin "WA Config" "Parameters" sub menu.
 *
 * @link https://moonkiosk.monwoo.com/missions/wa-config-par-monwoo wa-config by Monwoo
 * @since 0.0.1
 * @package
 * @filesource
 * @author service@monwoo.com
 **/

namespace WA\Config\E2E {
    use WA\Config\App;
    use Symfony\Component\CssSelector\CssSelectorConverter;
    use Codeception\Example;
    use Codeception\Util\Locator;
    use AcceptanceTester;
    use WA\Config\Core\AppInterface;

    /*
    // Try with different phar loads ways :
    $codecept = __DIR__ . "/../../tools/codecept.phar";
    $codecept = realpath($codecept);
    $codecept = "phar://$codecept/vendor/codeception/codeception/autoload.php";
    $codecept = "phar://" . __DIR__ . "/../../tools/codecept.phar/vendor/codeception/codeception/autoload.php";
    require_once($codecept);
    require_once("phar://" . __DIR__ . "/../../tools/codecept.phar/vendor/codeception/codeception/autoload.php");
    if (!file_exists($codecept)) {
        echo "Fail hacky VS Code Intelephense auto-complete for $codecept";
        exit;
    }

    // Hack in case your IDE do not load .phar archives (like VS Code Intelephense 2022-04)

    // IN MY CASE, solved by using composer in my dev environement
    // running this command inside plugin root folder :
    composer require codeception/codeception --dev
    // Then configuring json file from plugin root :
    // .vscode/settings.json
    {
        "intelephense.environment.includePaths": [
            "../WebAgencySources/e-commerce",
            "wa-config/vendor",
        ],
    }
    */

    // https://codeception.com/docs/03-AcceptanceTests
    // https://codeception.com/docs/07-AdvancedUsage

    $standaloneRelativeWp = __DIR__
        . "/../../../../WebAgencySources/e-commerce/wp-load.php";
    // var_dump($standaloneRelativeWp); exit;
    if (file_exists($standaloneRelativeWp)) {
        require_once($standaloneRelativeWp);
    } else {
        require_once(__DIR__ . "/../../../../../wp-load.php");
    }

    // TODO : load only ONCE before ALL TESTS
    // (same on all test end, close the door and/or have 
    //  time out server side on door open to close it
    //  if not closed by the end of tests script ?)
    // global $wa_plugin;
    // Why NEED to require wa-config since 
    // already loaded by wp-load.php ? (Global export missing ? 
    // bad to have globals...)
    // require __DIR__ . "/../../wa-config.php"; // TODO : heavy php to load ALL before ALL test, should try to load only once at first ?
    $wa_plugin = AppInterface::instance();

    /* 
    // Framework load example, you IDE should load it since it's 
    // already loaded by codecept tool
    $pluginRoot = __DIR__ . "/../../";
    $codeceptPharName = 'codecept.phar';
    $codeceptPharPath = "{$pluginRoot}tools/{$codeceptPharName}";
    try {
        $p = new \Phar($codeceptPharPath, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME, $codeceptPharName);
    } catch (\UnexpectedValueException $e) {
        $this->err("FAIL {$codeceptPharName} at {$codeceptPharPath}");
        echo "Could not open {$codeceptPharName}";
        exit;
    } catch (\BadMethodCallException $e) {
        echo 'technically, this cannot happen';
        exit;
    }
    $codeceptionFramework  = "phar://$codeceptPharName/vendor/codeception/codeception/autoload.php";
    if (file_exists($codeceptionFramework)) {
        require_once($codeceptionFramework);
    } else {
        echo "Fail to load $codeceptionFramework";
        exit;
    }
    */


    // Ensuring script execution up to it's end even if some wordpress will exit() for security reasons
    // $shouldExit = false;
    // register_shutdown_function(function() use (&$shouldExit) {
    //     if (! $shouldExit) {
    //         return;
    //     }
    //     echo 'Something went wrong.';
    // });

    // function exit() 
    // {
    //     echo "EXIT Application requested ========== =========\n"; 
    // }


    /**
     * This e2e will ensure wa-config options updates is working
     * 
     * __WARNING__ :
     * - __ENSURE PRODUCTION BACKUP BEFORE TESTS LAUNCH 
     * WITH ROLLBACK FEATURE__
     * - PRODUCTION TESTS NEED TO __FOCUS ON SIMPLE EDIT__
     * TO AVOID UNDESIRABLE SIDE EFFECTS
     * - PRODUCTION TESTS NEED TO __AVOID HARD BUGGY PATH__
     * TO PREVENT FAIL OF ROLLBACK SYSTEM
     * - e2e test launchs are __only available to ADMIN users__
     * so like a __carefull administrator__, allways have a way
     * to __ensure__ that your __production data__
     * stay coherent __ANY TIME__.
     * - IF you really want __to do hard tests__, change the test
     * config to fit a __SWITCH TO TEST DATABASE__ before testing.
     * {@see https://codeception.com/docs/modules/Db#sql-data-dump SQL DATA DUMP}
     * {@see https://generalchicken.guru/wp-codeception-tutorial/config-files/ Classic WordPress DB Duplication tutoriel }
     * {@see https://codeception.com/docs/07-AdvancedUsage}
     *
     * @see https://codeception.com/docs/03-AcceptanceTests
     * @see https://runebook.dev/fr/docs/codeception/07-advancedusage#example-annotation
     * @see https://runebook.dev/fr/docs/codeception/07-advancedusage#example-annotation#dataprovider-annotations
     * @see https://codeception.com/docs/07-AdvancedUsage
     * @see https://codeception.com/docs/03-AcceptanceTests
     * @see https://infinum.com/handbook/wordpress/automated-testing-in-wordpress/codeception
     * @see https://wpbrowser.wptestkit.dev/modules/wpwebdriver
     * @see https://github.com/lucatume/wp-browser
     * @see https://github.com/10up/wp-codeception
     * @see https://stackoverflow.com/questions/40235954/testing-file-uploads-with-codeception
     * @see \WA\Config\E2E\EnsureFooterCredits\DATA Simplier example
     * @since 0.0.1
     * @author service@monwoo.com
     */
    class E2E_EnsureAdminConfigPanelCest
    {
        // use \Codeception\Test\Feature\Stub;

        protected $rollback = [];
        protected $lateRollback = [];
        
        protected $eACOptATestsUsers = 'acceptance_tests_users';
        protected $testUsers = [];
        protected $accessHash = null;
        /** @var App $wa_plugin  */
        protected $wa_plugin = null;

        /**
         * Codeception trigger done "before" the "all test launch" callback
         * 
         * This code will show how to load wa-config options
         * saved from WA Config admin :
         * - to get the acceptance_tests_users listed in
         * "WA Config" "Parameters" Admin
         * - to get the acceptance test user
         * string list ready for authentification 
         * 
         * {@see https://codeception.com/docs/07-AdvancedUsage}
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @see https://codeception.com/docs/03-AcceptanceTests
         * @param AcceptanceTester $I Codeception Acceptance test browser (based on PhpBrowser for this wa-config package) 
         */
        public function _before(AcceptanceTester $I): void
        // public function beforeAllTests(AcceptanceTester $I): void
        {
            $I->haveHttpHeader('wa-e2e-test-mode', 'wa-config-e2e-tests');

            $I->expectTo("Load access HASH before test");
            // global $wa_plugin;
            $wa_plugin = AppInterface::instance();

            $this->wa_plugin = $wa_plugin;
            $accessInfos = $this->wa_plugin->e2e_tests_access_hash_open(true);
            // var_dump($accessInfos);
            $this->accessHash = $accessInfos['access-hash'];
 
            // https://codeception.com/docs/03-AcceptanceTests
            // Each failed assertion will be shown in the test results, but it won’t stop the test.
            // $I->canSeeInCurrentUrl('/user/miles');
            // VS $I->see('Form is filled incorrectly');
            // $password = $I->grabTextFrom("descendant::input/descendant::*[@id = 'password']");
            // $api_key = $I->grabValueFrom('input[name=api]');
            // $I->retry(4, 400); // need step decorator   - \Codeception\Step\Retry

            // $I->performOn('.confirm', function(\Codeception\Module\WebDriver $I) {

            // https://codeception.com/docs/05-UnitTests
            // https://codeception.com/docs/modules/Asserts
            // => need to enable Asserts module in
            //  wa-config/tests/acceptance.suite.yml
            // $I->assertNotNull($wa_plugin);
            $I->assertNotNull($this->wa_plugin);
            $I->assertNotNull($this->accessHash);

            // To force server to have this IP OK for access validity
            $ip = $accessInfos['started_by'];
            // https://codeception.com/docs/modules/PhpBrowser
            $I->haveServerParameter('HTTP_CLIENT_IP', $ip);

            $openHash = base64_decode($this->accessHash);
            $I->comment("Access HASH : {$openHash}");

            $key = 'wa_acceptance_tests_users'; // TODO : better use $inst->eConfOptATestsUsers or hard defined values ?
            $default = '';
            $eAdminConfigOptsKey = 'wa_config_e_admin_config_opts';
            $eAdminConfigOpts = get_option($eAdminConfigOptsKey, [
                $key => $default,
            ]);
            $testUsersList = $default;
            if (key_exists($key, $eAdminConfigOpts)) {
                $testUsersList = $eAdminConfigOpts[$key];
            }
            $I->comment("Having test user list : {$testUsersList}");

            $usersArray = explode(",", $testUsersList);
            $this->testUsers = array_map(function ($userData) {
                $user_parts = explode("'", $userData);
                // var_dump($user_parts);

                return new class($user_parts)
                {
                    function __construct($user_parts)
                    {
                        $this->email = $user_parts[0];
                        $this->testLogin = $user_parts[1] ?? null; // $user_parts[0];
                        // $this->pass = base64_decode($user_parts[1]);
                    }
                };
            }, $usersArray);

        }

        /**
         * Codeception trigger done "after" the "all test launch" callback
         * 
         * This code will show how to __rollback__ what need to be rollback
         * after a configuration edit test change.
         * 
         * @param AcceptanceTester $I Codeception Acceptance test browser (based on PhpBrowser for this wa-config package) 
         */
        public function _after(AcceptanceTester $I, $scenario): void
        // public function afterAllTests(AcceptanceTester $I): void
        {
            // https://github.com/Codeception/Codeception/issues/4977
            $I->comment("After tests, will rollback");
            $successfulRollback = true;
            $faillingRollback = []; // TODO : will keep trying them ? or report expert to review stuff ?

            foreach ($this->rollback as $idx => $callback) {
                try { // Run maximum rollback, even if some might fails
                    $callback();
                } catch (\Exception $e) {
                    codecept_debug($e);
                    $I->comment("Fail to rollback index $idx : " . $e->getMessage());
                    $successfulRollback = false;
                    $faillingRollback[] = $callback;
                }
            }
            foreach ($this->lateRollback as $idx => $callback) {
                try { // Run maximum rollback, even if some might fails
                    $callback();
                } catch (\Exception $e) {
                    codecept_debug($e);
                    $I->comment("Fail to rollback late index $idx : " . $e->getMessage());
                    $successfulRollback = false;
                    $faillingRollback[] = $callback;
                }
            }
            $this->rollback = $faillingRollback;
            $this->lateRollback = [];
            // $I->assertNotNull($this->accessHash);

            $this->wa_plugin->e2e_tests_access_hash_close(
                $this->accessHash
            );
            $this->accessHash = null;

            if (!$successfulRollback) {
                $I->fail("Need a full successful rollback to ensure data integrity");
            }
        }

        /**
         * Testing admin panel WA Config parameters save and updates
         * 
         * We use Key-value data in Doctrine-style for @ exemple
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @param AcceptanceTester $I Codeception Acceptance test browser
         * @param Example $example Codeception Example extracted from the anotation above the test line
         * 
         * Codeceptions Annotations
         * @example(footer_credit="A. test")
         */
        public function testingWAConfig_paramsSaveFromAdmin(AcceptanceTester $I, Example $example): void
        {
            // https://codeception.com/07-24-2013/testing-wordpress-plugins.html
            // https://codeception.com/08-01-2013/testing-wordpress-plugins-2.html
            // https://medium.com/typist-tech/wordpress-plugin-acceptance-test-with-codeception-on-vvv-2042d265c7cf
            // $I = new AcceptanceTester($scenario);

            $userLogin = $this->authenticateUser($I, 0);
            $I->expect("To be able to connect with '$userLogin'");

            // 🌖🌖 Going to wa-config pannel : 🌖🌖
            $I->amOnPage('/wp-admin');
            $I->click('a[href="admin.php?page=wa-config-e-admin-config-param-page"]');
            $I->see('Copyright de bas de page');
            $footerCreditId = '#wa_config_e_admin_config_opts_wa_footer_credit';
            $I->seeElement($footerCreditId);
            $footerEnableId = '#wa_config_e_admin_config_opts_wa_enable_footer';
            $I->seeElement($footerEnableId);

            // 🌖🌖 testing options updates : 🌖🌖
            $initialFCredit = $I->grabValueFrom($footerCreditId);
            $I->comment("Grab '$initialFCredit' from '$footerCreditId'");
            $initialEnableFooter = $I->grabValueFrom($footerEnableId);
            $I->comment("Grab '$initialEnableFooter[0]' from '$footerEnableId'");
            $testValue = $example['footer_credit'];
            // $I->fillField($footerEnableId, true); // [LogicException] Checkboxes should be instances of ChoiceFormField.  
            $I->checkOption($footerEnableId);
            $I->fillField($footerCreditId, $testValue);
            $I->expect('To be able to update footer copyright');
            $I->click('#submit');
            $this->rollback[] = function () use (
                $I,
                $initialFCredit,
                $footerCreditId,
                $initialEnableFooter,
                $footerEnableId
            ) {
                $I->amOnPage('/wp-admin/admin.php?page=wa-config-e-admin-config-param-page');
                if ($initialEnableFooter) {
                    $I->checkOption($footerEnableId);
                } else {
                    $I->uncheckOption($footerEnableId);
                }
                $I->fillField($footerCreditId, $initialFCredit);
                $I->click('#submit');
                $I->seeInField($footerCreditId, $initialFCredit);
            };
            // $I->see('Thank you, Miles', "//table/tr[2]");
            // $I->dontSee('Form is filled incorrectly');
            // $I->seeElement('.notice');
            // $I->dontSeeElement('.error');
            // $I->seeInCurrentUrl('/user/miles');
            // $I->seeCheckboxIsChecked('#agree');
            // $I->seeLink('Login');
            $I->seeInField($footerCreditId, $testValue);

            $I->amOnPage('/');
            $credit = 'Build by Monwoo and ' . $testValue;
            $I->see($credit);

            $I->wantTo('Test that credit link page is OK');
            $I->click($credit);
            $I->see('Crédits');
        }

        /**
         * Testing email trakings
         * 
         * We use Key-value data in Doctrine-style for @ exemple
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @param AcceptanceTester $I Codeception Acceptance test browser
         * @param Example $example Codeception Example extracted from the anotation above the test line
         * 
         * Codeceptions Annotations
         * @example(post_comment="[E2E] Testing credits comment success")
         * @example(post_comment="[E2E] Testing 2nd credits comment success")
         */
        public function testingWAConfig_emailTrackingForComments(AcceptanceTester $I, Example $example): void
        {
            $eAdminConfigE2ETestsOptsKey = 'wa_config_e_admin_config_e2e_tests_opts';
            $E2ETestsOptions = get_option($eAdminConfigE2ETestsOptsKey, []);
            // https://codeception.com/docs/02-GettingStarted#Interactive-Pause
            // https://codeception.com/docs/02-GettingStarted#Debugging
            // $I->pause(); // for cmd line with --debug option, missing display output under php 8 ?

            // $I->comment("e2e opts : " . print_r($E2ETestsOptions['emails-sended'], true));
            // codecept_debug($E2ETestsOptions['emails-sended']);
            // $I->pause();

            $editorUser = $this->authenticateUser($I, 1);
            $I->expect("To be able to connect with '$editorUser'");

            // 🌖🌖 Going to credit page 🌖🌖
            $I->amOnPage('/credits');
            $I->see('Crédits');

            // 🌖🌖 posting a comment and ensuring email notifications OK 🌖🌖
            $I->assertEquals(0, count($E2ETestsOptions['emails-sended']??[]), "No emails should have been sent yet.");

            $I->fillField("#commentform #comment", $example["post_comment"]);
            $I->click('#commentform #submit');

            wp_cache_delete("alloptions", "options"); // avoid wrong value do to cached stuff
            $E2ETestsOptions = get_option($eAdminConfigE2ETestsOptsKey, []);

            // codecept_debug($E2ETestsOptions['emails-sended']); $I->pause();

            $I->assertEquals(1, count($E2ETestsOptions['emails-sended']??[]), "1 email should have been sent.");

            $this->rollback[] = function () use (
                $I, $editorUser
            ) {
                $successfulRollback = true;
                $adminUser = $this->authenticateUser($I, 0);
                // TODO : only if Email Logs activated ?
                try {
                    $I->amOnPage('/wp-admin/admin.php?page=check-email-logs');
                    // Ensuring it's our stuff to remove
                    $I->click('.view-content a');
                    $I->see($editorUser);
                    $I->amOnPage('/wp-admin/admin.php?page=check-email-logs');
                    $I->click(Locator::elementAt('#the-list td .delete a', 1));
                    $I->see('1 email log deleted'); // TODO : lang switch tests ? FR/EN/ES    
                } catch (\Exception $e) {
                    codecept_debug($e);
                    try { $I->fail(
                        "to rollback e2e sent email : "
                        . $e->getMessage()
                    );} catch (\Exception $e) {}
                    $successfulRollback = false;
                }

                try {
                    $I->amOnPage('/wp-admin/edit-comments.php');
                    // $cmtRowLoc = Locator::elementAt('#the-comment-list tr', 1);
                    $cmtRowLoc = '#the-comment-list tr:first-child';
                    $I->see($editorUser, $cmtRowLoc);

                    // $deleteSubPath = $cmtRowLoc . '/'
                    // . (new CssSelectorConverter())->toXPath('a.delete');
                    // $deleteSubPath = Locator::elementAt('#the-comment-list tr a.delete', 1);
                    $deleteSubPath = "$cmtRowLoc a.delete";

                    $I->expect("To delete comment");
                    $I->click($deleteSubPath);
                    $I->seeElement('.undo.untrash');
                    // throw new \Exception('testing $successfulRollback is failling last');
                } catch (\Exception $e) {
                    codecept_debug($e);
                    try { $I->fail(
                        "to rollback e2e credit page comment : "
                        . $e->getMessage()
                    );} catch (\Exception $e) {}
                    $successfulRollback = false;
                }

                // $I->assertTrue($successfulRollback,
                if (!$successfulRollback) {
                    $I->fail("to ensure a full successful rollback for data integrity");
                }
            };
        }

        /**
         * Testing admin panel WA Config panels availability
         * 
         * The @ exemple annotation must stay on one line to work,
         * use DataProvider otherwise
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @param AcceptanceTester $I Codeception Acceptance test browser
         * @param Example $example Codeception Example extracted from the anotation above the test line
         * 
         * Codeception annotations :
         * @before authenticateUser
         * @dataProvider panelsProvider
         */
        public function testingWAConfig_panelsAvailability(AcceptanceTester $I, Example $example): void
        {
            $I->amOnPage($example['url']);
            $I->see($example['title'], 'li.current'); // Testing currently selected menu from wp admin nav bar
        }

        // TODO : ensure CRONS Are launch externally ?
        //        => check min cron missed time delay
        //       (some are each minutes, but external
        //        CRON run each 5 minutes, if min dela
        //        < 5 minutes = CRON DID run extenaly OK
        //        WARNING on last CRON delay until last
        //        missing pendings CRONS launch)

        ///////////////////////
        //////// UTILS ////////
        ///////////////////////
        /**
         * Authenticate with the first test user
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @param AcceptanceTester $I Codeception Acceptance test browser
         * @param int $idx User index in e2e user test list
         */
        protected function authenticateUser($I, $idx = 0) {
            // 🌖🌖 Login to admin pannel : 🌖🌖

            // $I->amOnPage('/wp-admin');
            // $I->fillField('#user_login', $this->testUsers[0]->email);
            // $I->fillField('#user_pass', $this->testUsers[0]->pass);
            // $I->click('#wp-submit');

            // // Will authenticate for WEBCEPTION Tool
            // // but not for php browser...
            // $user = AppInterface::e2e_test_authenticateTestUser(
            //     $this->testUsers[$idx]->email
            // );

            // https://stackoverflow.com/questions/56700802/how-to-do-post-request-in-codeception-functionaltest-with-json-body
            // https://codeception.com/docs/modules/Yii2#sendAjaxPostRequest
            // $I->haveHttpHeader('X-Requested-With', 'Codeception');

            // $I->haveHttpHeader("Content-Type", "application/x-www-form-urlencoded");
            $postData = [
                'action' => 'wa-e2e-test-action',
                'wa-access-hash' => $this->accessHash,
                'wa-action' => 'authenticate-user',
                'wa-data' => [
                    $this->testUsers[$idx]->email,
                    $this->testUsers[$idx]->testLogin,
                ],
            ];
            codecept_debug($postData);
            $resp = $I->sendAjaxPostRequest(
                // admin_url( 'admin-ajax.php?action=wa-e2e-test-action' ), // Will load externally with full url ?
                '/wp-admin/admin-ajax.php',
                $postData
            );
            // $I->deleteHeader("Content-Type");

            // Will send debug output to wa-config/tests/_output/debug/sendAjaxPostRequest_wa-e2e-test-action_authenticate-user.html
            $I->makeHtmlSnapshot('sendAjaxPostRequest_wa-e2e-test-action_authenticate-user');
            // var_dump($resp); // EMPTY, no resp or strinfigy to empty str ?

            // $I->seeResponseContainsJson(array("test_user", "exact value to match"));
            $testUserLogin = $I->grabDataFromResponseByJsonPath('$..test_user.data.user_login');
            $userLogin = $testUserLogin[0] ?? null; // null; // $resp;

            $I->assertNotNull($userLogin, "Test user should be authenticated");

            $I->comment("Succed test auth to : '$userLogin'");
            // $I->assertIsEmpty("Debug break here");

            // https://codeception.com/docs/modules/PhpBrowser
            // https://guzzlephp.org
            // $I->executeInGuzzle(function (\GuzzleHttp\Client $client) {
            //     $client->post(admin_url('admin-ajax.php'), [
            //         'query' => ['action' => 'wa-e2e-test-action'],

            //     ]);
            // });

            // https://codeception.com/docs/modules/REST.html#algolia:p:nth-of-type(40)
            // $I->seeHttpHeader("Content-Type"); //=> TODO : load rest tools inside acceptance ?
            // https://github.com/Codeception/Codeception/issues/4258
            // In a PHPUnit test that extends Symfony's WebTestCase, you can do:
            // $resp->headers->contains(
            //     'Content-Type',
            //     'text/javascript; charset=UTF-8'
            // );

            $this->lateRollback[] = function () use (
                $I, $userLogin
            ) {
                $this->logoutUser($I, $userLogin);
            };

            // if ($I) {
            //     $I->amOnPage('/wp-admin');
            //     $I->makeHtmlSnapshot('wp-admin_wa-e2e-test-action_after-authenticate-user');
            //     $I->expect('To be logged as admin');
            //     // $I->see('Dashboard'); // Wrong on lang
            //     $I->seeElement('#menu-dashboard .wp-menu-name');    
            // }

            return $userLogin;
        }
        /**
         * Logout the test user
         * 
         * DO NOT call yourself without knowing what you do.
         * Used for test data rollback
         * 
         * @since 0.0.1
         * @author service@monwoo.com
         * @param AcceptanceTester $I Codeception Acceptance test browser
         * @param \WP_User $userLogin The test user to logout
         */
        protected function logoutUser($I, $userLogin) {
            // // Will logout for WEBCEPTION Tool
            // // but not for php browser...
            // AppInterface::e2e_test_authenticateTestUser(
            //     $this->testUsers[0]->email
            // );

            // $I->haveHttpHeader("Content-Type", "application/x-www-form-urlencoded");
            $postData = [
                'action' => 'wa-e2e-test-action',
                'wa-access-hash' => $this->accessHash,
                'wa-action' => 'logout-user',
                'wa-data' => $userLogin,
            ];
            codecept_debug($postData);
            $resp = $I->sendAjaxPostRequest(
                '/wp-admin/admin-ajax.php',
                $postData
            );

            $I->makeHtmlSnapshot('sendAjaxPostRequest_wa-e2e-test-action_logout-user');
        }

        // /* *
        //  * @param AcceptanceTester $I Codeception Acceptance test browser
        //  * @param string $text The test user to logout
        //  */
        // public function seeResponseContains($I, $text)
        // {
        //     // https://codeception.com/docs/modules/PhpBrowser
        //     $I->assertStringContainsString(
        //         $text,
        //         $I->getModule('PhpBrowser')->_getResponseContent(),
        //         "response contains"
        //     );
        // }

        ////////////////////////////////
        //////// DATA PROVIDERS ////////
        ////////////////////////////////

        /** * @return tableau */
        protected function panelsProvider() // alternatively, if you want the function to be public, be sure to prefix it with `_`
        {
            return [
                [
                    'title' => __('Paramètres', /*📜*/ 'wa-config'/*📜*/),
                    'url' => "/wp-admin/admin.php?page=wa-config-e-admin-config-param-page"
                ], [
                    'title' => __('Documentation', /*📜*/ 'wa-config'/*📜*/),
                    'url' => "/wp-admin/admin.php?page=wa-config-e-admin-config-doc-page"
                ], [
                    'title' => __('Revue qualité', /*📜*/ 'wa-config'/*📜*/),
                    'url' => "/wp-admin/admin.php?page=wa-config-e-admin-config-review-page"
                ],
            ];
        }
    }
}