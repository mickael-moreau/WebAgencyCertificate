<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 * 
 * __WARNING__ : USING TESTS IN __PRODUCTION__ NEEDS __SERIOUS BACKUPS__ STRATEGIES
 *
 * __BE CARFULL WITH__ PRODUCTION __DATA__,
 * DO NOT MESS UP WITH REAL BUISINESS DATA FROM YOUR TESTS.
 *
 * This end to end user test is an example of a simple safe test.
 * See {@link https://codeception.com/docs/03-AcceptanceTests
 * Codeception}
 * documentation for more advanced usage.
 * 
 * It will show you how to simply test the open Frontend.
 * 
 * You can also launch it with command line :
 * ```bash
 * alias e2e-footer="php 'tools/codecept.phar' run"
 * export WA_STANDALONE_WP_LOADER_FOR_E2E_CLI="../../WebAgencySources/e-commerce/wp-load.php"
 * e2e-footer 'acceptance' 'Frontend/E2E_EnsureFooterCreditsCept'
 * ```
 *
 * @link https://moonkiosk.monwoo.com/en/missions/wa-config-monwoo_en Monwoo Web Agency Config
 * @since 0.0.1
 * @package
 * @filesource
 * @author service@monwoo.com
 **/
namespace WA\Config\E2E\Frontend {
    /**
     * Simple end to end data test 

     * __WARNING__ : USING TESTS IN __PRODUCTION__ NEEDS __SERIOUS BACKUPS__ STRATEGIES
     *
     * __BE CARFULL WITH__ PRODUCTION __DATA__,
     * DO NOT MESS UP WITH REAL BUISINESS DATA FROM YOUR TESTS.
     *
     * This end to end user test is an example of a simple safe test.
     * See {@link https://codeception.com/docs/03-AcceptanceTests
     * Codeception}
     * documentation for more advanced usage.
     * 
     * You can also launch it with command line :
     * ```bash
     * alias e2e-footer="php 'tools/codecept.phar' run"
     * export WA_STANDALONE_WP_LOADER_FOR_E2E_CLI="../../WebAgencySources/e-commerce/wp-load.php"
     * e2e-footer 'acceptance' 'Frontend/E2E_EnsureFooterCreditsCept'
     * ```
     *
     * @see \WA\Config\E2E\E2E_EnsureAdminConfigPanelCest More advanced example
     * @since 0.0.1
     * @author service@monwoo.com
     */   
    class E2E_EnsureFooterCreditsCept {
        const FOOTER_CREDIT = [
            'fr_FR' => 'Construit par Monwoo et autre',
            'en_US' => 'Build by Monwoo and other',
            'es_ES' => 'Construido por Monwoo y otro',
        ];
        const FOOTER_TEST_POOL = [
            'fr_FR' => [
                '/', '/missions'
            ],
            'en_US' => [
                '/en', '/en/missions'
            ],
            'es_ES' => [
                '/es', '/es/misiones'
            ],
        ];
    }
}

namespace WA\Config\E2E\Frontend {
    use WA\Config\E2E\Frontend\E2E_EnsureFooterCreditsCept as E2eCept;
    use AcceptanceTester;
    use Codeception\Codecept;
    use Codeception\Util\HttpCode;
    // use Codeception\Extension\Logger;

    $I = new AcceptanceTester($scenario);

    $I->comment("Running Codeception version : " . Codecept::VERSION);
    // Logger::log((string)$var);

    function test_footer($page, $I, $locale, $expected) {
        $I->wantTo("Test footer credits for [$locale]");
        $I->amOnPage($page);

        $waFooterTemplate = $I->grabWaFooterTemplate($locale);
        if (strlen($waFooterTemplate)) {
            // $I->see($waFooterTemplate); // NOP, for TEXTUAL search without html tags only
            $I->seeResponseContains($waFooterTemplate);
            // $I->assertContains($waFooterTemplate, $pageContent);
        } else {
            $I->see($expected);
        }
    }
    foreach (E2eCept::FOOTER_CREDIT as $locale => $expected) {
        $testPool = E2eCept::FOOTER_TEST_POOL[$locale];
        foreach ($testPool as $testPage) {
           test_footer($testPage, $I, $locale, $expected);
        }

    }

    // BONUS : simple secu test to ensurre our tests stay safe from public access (not directly accessible)

    // $matches = [];
    // preg_match( // WILL NO WORK ON SYMLINK, thoses are not under wp dir...
    //     "/wp-content\/plugins\/([^\/]+)\/(.*)/",
    //     "/Users/miguel/goinfre/WA-wp-plugins/wa-config/wa-config.php",
    //     $matches
    // );
    // codecept_debug($matches);
    // $pluginFolder = $matches[1] ?? null;
    // $pluginRelativeTestFile = $matches[2] ?? null;

    // wa-config/tests/acceptance/Frontend/E2E_EnsureFooterCreditsCept.php
    // $pluginFolder = "wa-config";
    $pluginFolder = "wa-config-0.0.2";

    $pluginRelativeTestFile = "tests/acceptance/Frontend/E2E_EnsureFooterCreditsCept.php";
    $I->wantTo("Test test Access for $pluginFolder");

    $pluginBaseUrl = "/wp-content/plugins/$pluginFolder";
    $I->amOnPage("$pluginBaseUrl/tests/");
    // https://codeception.com/docs/reference/HttpCode
    // $I->seeResponseCodeIs(HttpCode::FORBIDDEN); // 404 under production server, // TODO : same for dev...
    $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    $I->amOnPage("$pluginBaseUrl/$pluginRelativeTestFile");
    // $I->seeResponseCodeIs(HttpCode::FORBIDDEN); // 404 under production server, // TODO : same for dev...
    $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

    // TODO BONUS : private bckkup test upload folder should not be accessible too
}