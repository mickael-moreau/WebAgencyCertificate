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
 * You can also launch it with command line :
 * ```bash
 * alias e2e="php 'tools/codecept.phar' run"
 * 
 * e2e --no-redirect  'acceptance' --html
 * 
 * e2e --no-redirect  'acceptance' 'E2E_EnsureAdminConfigPanelCest' --html
 * 
 * e2e --no-redirect  'acceptance' \
 * 'E2E_EnsureAdminConfigPanelCest::testingWAConfig_emailTrackingForComments' \
 * --html --debug
 * ```
 *
 * In case of test failure failling user login rollback, use this 
 * curl commande 1 or 2 times (first time may get an invalid access error) :
 * ```bash
 * WA_BACKEND=https://web-agency.local.dev/e-commerce
 * curl -H 'wa-e2e-test-mode: wa-config-e2e-tests' \
 * "$WA_BACKEND/wp-admin/admin-ajax.php?action=wa-e2e-test-action&wa-action=force-clean-and-restore-users"
 * # for apache servers (secu on header syntax ?)
 * curl -H 'wa-e2e-test-mode: wa-config-e2e-tests' \
 * "$WA_BACKEND/wp-admin/admin-ajax.php?action=wa-e2e-test-action&wa-action=force-clean-and-restore-users"
 * ```
 * 
 * If still not able to login with the targeted test user real login name after test, update manually :
 * Open your Database access (**phpmyadmin**) and look for **'wp_users' table**
 * Then look for the real login name test user from other data than the login name
 * that may change during test authentification, and fix back the :
 * - **'user_login'** field with right login data.
 * - **'user_email'** field with right login data.
 *
 * Common errors about login is the missing real login name target.
 * ex : demo@monwoo.com and editor-wa@monwoo.com are our default value for tests users list.
 * So if no accessible account with demo@monwoo.com and editor-wa@monwoo.com exist under your database,
 * then login will fail...
 * 
 * You may also experiment some Guzzle clash on WebLaunch side, since codecept.phar
 * will use a different version of Guzzle than the one it have been build with if present.
 * If new Guzzle version is not comptaible with the codecept.phar version you have,
 * you may have trouble launching the tests.
 * 
 * For exemple, with codecept version at the time of this documentation, 
 * we need to desactivate 
 * 'Google Listings and Ads' plugins that is defining
 * it's own version of Guzzle to succed test launch ...
 * 
 * You may also get an error like :
 * 'I fail "to rollback e2e sent email : Failed asserting that on page ...'
 * if 'Check & Log Email' plugin is not configured to log incoming emails.
 * cf : /wp-admin/admin.php?page=check-email-settings&tab=logging
 * 
 * Purging cache example :
 * {@see https://docs.litespeedtech.com/lscache/basics}.
 * If the home page http://example.com/index.php needs to be purged,
 * the trailing / is required. The index page will not be purged without it.
 * ```bash
 * curl -i -X PURGE https://web-agency.local.dev/e-commerce/
 * ```
 * 
 * If you see a 404 in your detailed page result, it's because the test did return a 404.
 * If you try to access a missing ressource from the test _output folder, you will see
 * a 404 message like this :
 * ```[404] for Request: %{THE_REQUEST} Referrer: %{HTTP_REFERER} Host: %{HTTP_HOST}```
 * 
 * @link https://moonkiosk.monwoo.com/en/missions/wa-config-monwoo_en WA-Config Monwoo
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
    use Codeception\Codecept;
    use Codeception\Util\HttpCode;
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

    // TODO : load only ONCE before ALL TESTS ?
    // (same on all test end, close the door and/or have 
    //  time out server side on door open to close it
    //  if not closed by the end of tests script ?)

    $wa_plugin = AppInterface::instance();

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
     * 
     * You can also launch it with command line :
     * ```bash
     * alias e2e="php 'tools/codecept.phar' run"
     * 
     * e2e --no-redirect  'acceptance' --html
     * 
     * e2e --no-redirect  'acceptance' 'E2E_EnsureAdminConfigPanelCest' --html
     * 
     * e2e --no-redirect  'acceptance' \
     * 'E2E_EnsureAdminConfigPanelCest::testingWAConfig_emailTrackingForComments' \
     * --html --debug
     * ```
     *
     * In case of test failure failling user login rollback, use this 
     * curl commande 1 or 2 times (first time may get an invalid access error) :
     * ```bash
     * WA_BACKEND=https://web-agency.local.dev/e-commerce
     * # curl -H 'wa-e2e-test-mode: wa-config-e2e-tests' \ _ will not pass some secured Apache servers
     * curl -H 'wa-e2e-test-mode: wa-config-e2e-tests' \
     * "$WA_BACKEND/wp-admin/admin-ajax.php?action=wa-e2e-test-action&wa-action=force-clean-and-restore-users"
     * ```
     * 
     * If still not able to login with the targeted test user real login name after test, update manually :
     * Open your Database access (**phpmyadmin**) and look for **'wp_users' table**
     * Then look for the real login name test user from other data than the login name
     * that may change during test authentification, and fix back the :
     * - **'user_login'** field with right login data.
     * - **'user_email'** field with right login data.
     *
     * Common errors about login is the missing real login name target.
     * ex : demo@monwoo.com and editor-wa@monwoo.com are our default value for tests users list.
     * So if no accessible account with demo@monwoo.com and editor-wa@monwoo.com exist under your database,
     * then login will fail...
     * 
     * You may also experiment some Guzzle clash on WebLaunch side, since codecept.phar
     * will use a different version of Guzzle than the one it have been build with if present.
     * If new Guzzle version is not comptaible with the codecept.phar version you have,
     * you may have trouble launching the tests.
     * 
     * For exemple, with codecept version at the time of this documentation, we need to desactivate 
     * 'Google Listings and Ads' plugins that is defining it's own version of Guzzle...
     * 
     * You may also get an error like :
     * 'I fail "to rollback e2e sent email : Failed asserting that on page ...'
     * if 'Check & Log Email' plugin is not configured to log incoming emails.
     * cf : /wp-admin/admin.php?page=check-email-settings&tab=logging
     * 
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
     * @see \WA\Config\E2E\Frontend\E2E_EnsureFooterCreditsCept E2E_EnsureFooterCreditsCept, Simplier example
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
            $I->comment("Running Codeception version : " . Codecept::VERSION);

            // TODO : strange : why need to be called before wait to not be null ?
            $wa_plugin = AppInterface::instance();

            // For productions servers with limited calls per seconds for DDOS protections :
            // Take some pause :
            $time = time();
            // https://codeception.com/docs/03-AcceptanceTests#wait
            // $I->wait(2); // Buggy with PhpBrowser, use with WebDriver only ? $wa_plugin become null...
            sleep(2);
            $waitting = time() - $time;
            $I->comment("☕️ 🥐🥯🥖🧀 Did take a $waitting seconds pause");

            $I->haveHttpHeader('wa-e2e-test-mode', 'wa-config-e2e-tests');

            $I->expectTo("Load access HASH before test");

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
            $I->comment("[$ip] Access HASH : {$openHash}");

            $key = 'wa_acceptance_tests_users'; // TODO : better use $inst->eConfOptATestsUsers or hard defined values ?
            $default = '';
            $eConfigOptsKey = 'wa_e_config_opts';
            $eConfigOpts = get_option($eConfigOptsKey, [
                $key => $default,
            ]);
            global $_wa_fetch_instance;
            $app = $_wa_fetch_instance();

            $testUsersList = $app->E_DEFAULT_A_TESTS_USERS_LIST;
            if (key_exists($key, $eConfigOpts)
            && strlen($eConfigOpts[$key])) {
                $testUsersList = $eConfigOpts[$key];
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
                    $this->debug($e);
                    $I->comment("Fail to rollback index $idx : " . $e->getMessage());
                    $successfulRollback = false;
                    $faillingRollback[] = $callback;
                }
            }
            foreach ($this->lateRollback as $idx => $callback) {
                try { // Run maximum rollback, even if some might fails
                    $callback();
                } catch (\Exception $e) {
                    $this->debug($e);
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
            $I->click('a[href="admin.php?page=wa-e-config-param-page"]');
            $I->see('Copyright de bas de page');
            // TODO : Language aware TESTINGS for 3 languages each time ?
            //        WELL, language test sound more like FRONTEND easy tests.
            //        Already done in :
            //        tests/acceptance/Frontend/E2E_EnsureFooterCreditsCept.php
            $footerCreditId = '.wa_e_config_opts_wa_footer_credit_fr_FR';
            $I->seeElement($footerCreditId);
            $footerEnableId = '#wa_e_config_opts_wa_enable_footer';
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
                $I->amOnPage('/wp-admin/admin.php?page=wa-e-config-param-page');
                if ($initialEnableFooter) {
                    $I->checkOption($footerEnableId);
                } else {
                    $I->uncheckOption($footerEnableId);
                }
                $I->fillField($footerCreditId, $initialFCredit);
                $I->click('#submit');
                $I->seeInField($footerCreditId, $initialFCredit);
            };
            // $I->see('Thank you', "//table/tr[2]");
            // $I->dontSee('Form is filled incorrectly');
            // $I->seeElement('.notice');
            // $I->dontSeeElement('.error');
            // $I->seeInCurrentUrl('/user/admin');
            // $I->seeCheckboxIsChecked('#agree');
            // $I->seeLink('Login');
            $I->seeInField($footerCreditId, $testValue);

            $I->amOnPage('/');
            $credit = 'Construit par Monwoo et ' . $testValue;
            $I->see($credit);

            $I->wantTo('Test that credit link page is OK');
            $I->click($credit);
            // $I->see('Crédits');
            $I->seeResponseCodeIs(HttpCode::OK);
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
            $eConfigE2ETestsOptsKey = 'wa_e_config_e2e_tests_opts';
            $E2ETestsOptions = get_option($eConfigE2ETestsOptsKey, []);
            // https://codeception.com/docs/02-GettingStarted#Interactive-Pause
            // https://codeception.com/docs/02-GettingStarted#Debugging
            // $I->pause(); // for cmd line with --debug option, missing display output under php 8 ?

            // $I->comment("e2e opts : " . print_r($E2ETestsOptions['emails-sended'], true));
            // $this->debug($E2ETestsOptions['emails-sended']);
            // $I->pause();

            $editorUser = $this->authenticateUser($I, 1);
            $I->expect("To be able to connect with '$editorUser'");

            // 🌖🌖 Going to credit page 🌖🌖
            $I->amOnPage('/credits');
            $I->seeResponseCodeIs(HttpCode::OK);

            // 🌖🌖 posting a comment and ensuring email notifications OK 🌖🌖
            $I->assertEquals(0, count($E2ETestsOptions['emails-sended']??[]), "No emails should have been sent yet.");

            $commentSelector = '#commentform #comment';
            $submitSelector = '#comment-submit';

            // TODO : handle $submitSelector for multi-theme no fails if selectors changes on theme switch ?
            // https://stackoverflow.com/questions/26183792/use-codeception-assertion-in-conditional-if-statement
            // https://codeception.com/docs/modules/WebDriver#performOn
            // $I->performOn('.model', ActionSequence::build()
            //     ->see('Warning')
            //     ->see('Are you sure you want to delete this?')
            //     ->click('Yes')
            // );

            $I->fillField($commentSelector, $example["post_comment"]);
            $I->click($submitSelector);

            wp_cache_delete("alloptions", "options"); // avoid wrong value do to cached stuff
            $E2ETestsOptions = get_option($eConfigE2ETestsOptsKey, []);

            // $this->debug($E2ETestsOptions['emails-sended']); $I->pause();

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
                    $this->debug($e);
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
                    $this->debug($e);
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

            // https://stackoverflow.com/questions/56700802/how-to-do-post-request-in-codeception-functionaltest-with-json-body
            // https://codeception.com/docs/modules/Yii2#sendAjaxPostRequest
            // $I->haveHttpHeader('X-Requested-With', 'Codeception');
            // $I->haveHttpHeader("Content-Type", "application/x-www-form-urlencoded");
            $wantedLogin = $this->testUsers[$idx]->email;
            $wantedTestLogin = $this->testUsers[$idx]->testLogin;
            $postData = [
                'action' => 'wa-e2e-test-action',
                'wa-access-hash' => $this->accessHash,
                'wa-action' => 'authenticate-user',
                'wa-data' => [
                    $wantedLogin,
                    $wantedTestLogin,
                ],
            ];
            $this->debug("postData : ", $postData);
            $I->comment("Will try to connect as : '$wantedLogin' for login test target : '$wantedTestLogin'");
            $resp = $I->sendAjaxPostRequest(
                // admin_url( 'admin-ajax.php?action=wa-e2e-test-action' ),
                '/wp-admin/admin-ajax.php',
                $postData
            );
            // $I->deleteHeader("Content-Type");
            $this->debug("response : ", $resp);

            // Will send debug output to wa-config/tests/_output/debug/sendAjaxPostRequest_wa-e2e-test-action_authenticate-user.html
            $I->makeHtmlSnapshot('sendAjaxPostRequest_wa-e2e-test-action_authenticate-user');

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
            // $I->seeHttpHeader("Content-Type");
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
            //     // Below comment is WRONG on language switch,
            //     // split per language or use WP : __('Dashboard', 'wa-config')
            //     // IF you test exact same strings from our Plugin php code
            //     // $I->see('Dashboard');
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
            if (!$userLogin) {
                $I->comment("No userLogin provided for logoutUser, avoiding Logout");
                return;
            }
            $I->makeHtmlSnapshot('htmlResult_before_logout-user');
            // $I->haveHttpHeader("Content-Type", "application/x-www-form-urlencoded");
            $postData = [
                'action' => 'wa-e2e-test-action',
                'wa-access-hash' => $this->accessHash,
                'wa-action' => 'logout-user',
                'wa-data' => $userLogin,
            ];
            $this->debug("postData :", $postData);
            $resp = $I->sendAjaxPostRequest(
                '/wp-admin/admin-ajax.php',
                $postData
            );
            $this->debug("response : ", $resp);

            // TODO : sometime, in case of failure (403 or 404 page ?), last snapshoot is saved, 
            //       so the sucessfull result of logout is send... for previous error
            //       => should send the same as ? :
            //       $I->makeHtmlSnapshot('htmlResult_before_logout-user'); (Or not, sound like
            //       snapshoot of last success result... 
            //       check wp-content/debug.log instead... => wp_die with page not found error...)
            $I->makeHtmlSnapshot('sendAjaxPostRequest_wa-e2e-test-action_logout-user');
        }

        protected function debug(...$datas) {
            if (!\Codeception\Util\Debug::isEnabled()) {
                return; // Keep spped, nothing to do if not in debug state
            }

            // https://stackoverflow.com/questions/2110732/how-to-get-name-of-calling-function-method-in-php
            // $caller = debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $position = $backtrace[0];
            $caller = $backtrace[1];
            $source = "[{$caller['function']}] at {$position['file']}:{$position['line']}";
            codecept_debug($source);
            // codecept_debug(array_keys(get_object_vars($caller)));
            // codecept_debug(array_keys($caller));

            $len = count($datas);
            $strBulk = "";
            $idx = 0;
            while($idx < $len && is_string($datas[0])) {
                $strBulk .= array_shift($datas) . " ";
                $idx++;
            }
            // $prettyPrint = "[$source] $strBulk" . print_r($datas, true);
            // codecept_debug([$source => $datas]);
            // codecept_debug($prettyPrint);
            // \Codeception\Util\Debug::debug($datas);
            codecept_debug($strBulk);
            $len = count($datas);
            $idx = 0;
            while($idx < $len) {
                codecept_debug($datas[$idx]);
                $idx++;
            }
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
                    'url' => "/wp-admin/admin.php?page=wa-e-config-param-page"
                ], [
                    'title' => __('Documentation', /*📜*/ 'wa-config'/*📜*/),
                    'url' => "/wp-admin/admin.php?page=wa-e-config-doc-page"
                ], [
                    'title' => __('Revue qualité', /*📜*/ 'wa-config'/*📜*/),
                    'url' => "/wp-admin/admin.php?page=wa-e-review-page"
                ],
            ];
        }
    }
}
