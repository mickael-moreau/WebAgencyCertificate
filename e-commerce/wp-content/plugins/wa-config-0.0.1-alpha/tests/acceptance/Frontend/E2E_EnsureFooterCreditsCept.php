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
 * @link https://www.web-agency.app/e-commerce/produit/wa-config-codeception-testings/ wa-config by Monwoo
 * @since 0.0.1
 * @package
 * @filesource
 * @author service@monwoo.com
 **/
namespace WA\Config\E2E\Frontend {
    use AcceptanceTester;
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
     * @see \WA\Config\E2E\E2E_EnsureAdminConfigPanelCest More advanced example
     * @since 0.0.1
     * @author service@monwoo.com
     */   
    class E2E_EnsureFooterCreditsCept {
        // const FOOTER_CREDIT = '© Web-Agency.app 2022';
        // TODO : Lang sync translations test ? __(...) ?
        const FOOTER_CREDIT = 'Build by Monwoo and autre';
    }

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Test that footer credits are presents');
    $I->amOnPage('/');
    $I->see(E2E_EnsureFooterCreditsCept::FOOTER_CREDIT);

    // TODO : ensure plugin test folder
    //        AND private bckkup test upload folder
    //        are not listable, cf output of :
    // https://web-agency.local.dev/e-commerce/wp-content/plugins/wa-config/tests/
}