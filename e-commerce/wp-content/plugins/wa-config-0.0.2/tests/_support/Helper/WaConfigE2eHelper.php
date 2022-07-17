<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com
 * 
 * Custom Web Agency helper
 * 
 * @package waConfig
 * @since 0.0.2
 *
 */
namespace Helper;

use Codeception\Exception\ElementNotFound;
use Codeception\Module\PhpBrowser;
use WA\Config\Core\AppInterface;
use WA\Config\App;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

// Load custom WordPress
$standaloneRelativeWp = __DIR__
. "/../../../../../WebAgencySources/e-commerce/wp-load.php";
// var_dump($standaloneRelativeWp); exit;
if (file_exists($standaloneRelativeWp)) {
    require_once($standaloneRelativeWp);
} else {
    require_once(__DIR__ . "/../../../../../../wp-load.php");
}

class WaConfigE2eHelper extends \Codeception\Module
{
    /**
     * grab html content
     * 
     * @return string|array<string> return the html content if match one element
     * or an array of html if multiple matches
     */
    public function grabHtmlFrom(string $cssOrXPath)
    {
        // https://stackoverflow.com/questions/15133541/get-raw-html-code-of-element-with-symfony-domcrawler
        // https://github.com/symfony/symfony/issues/18609
        // https://symfony.com/doc/current/components/dom_crawler.html#component-dom-crawler-dumping
        /** @var PhpBrowser $browser */
        $browser = $this->getModule('PhpBrowser'); // ->_getResponseContent();

        // => match is from other module than PhpBrowser ?
        // $nodes = $browser->match($cssOrXPathOrRegex);

        // https://codeception.com/docs/modules/PhpBrowser#_findElements
        $nodes = $browser->_findElements($cssOrXPath);

        if (!count($nodes)) {
            throw new ElementNotFound($cssOrXPath, 'Element that matches CSS or XPath or Regex');
        }

        $htmls = [];
        foreach ($nodes as $idx => $node) {
            $html = "";
            foreach($node->childNodes as $child) {
                $html .= $node->ownerDocument->saveHTML($child);
            }
            $htmls[] = $html;
        }

        if (!count($htmls)) {
            throw new ElementNotFound(
                $cssOrXPath,
                "Element [$idx] targeted by '$cssOrXPath' do not have child contents."
            );
        }

        return count($htmls) > 1 ? $htmls : $htmls[0];
    }

    /**
     * See if reponse have string matchs with requested text
     * 
     */
    public function seeResponseContains(string $text)
    {
        // https://codeception.com/docs/modules/PhpBrowser.html#algolia:p:nth-of-type(1)
        /** @var PhpBrowser $browser */
        $browser = $this->getModule('PhpBrowser');
        $this->assertStringContainsString(
            $text, $browser->_getResponseContent(), "response contains"
        );
        // $I->assertContains($waFooterTemplate, $pageContent);
    }

    /**
     * grab html content
     * 
     * @return string|array<string> return the html content if match one element
     * or an array of html if multiple matches
     */
    public function fillContentWithHtml(string $cssOrXPath, string $html) : void
    {
        // https://symfony.com/doc/current/components/dom_crawler.html#component-dom-crawler-dumping
        /** @var PhpBrowser $browser */
        $browser = $this->getModule('PhpBrowser'); // ->_getResponseContent();

        // https://codeception.com/docs/modules/PhpBrowser#_findElements
        $nodes = $browser->_findElements($cssOrXPath);

        if (!count($nodes)) {
            throw new ElementNotFound($cssOrXPath, 'Element that matches CSS or XPath or Regex');
        }

        foreach ($nodes as $idx => $node) {
            $node->nodeValue = htmlspecialchars($html);
        }
    }

    /**
     * grab wordpress option
     * 
     * @param string $optKey the wordpress option key
     * @param mixed $default the default value in case option is missing.
     * @return mixed the wordpress option value
     */
    public function grabWordPressOption(string $optKey, $default = null)
    {
        $val = get_option($optKey, $default);
        return $val;
    }

    /**
     * grab the web agency footer template for current or specific local
     * 
     * @param string $locale the locale to use to get the web agency footer, null to use default locale
     * @return string the web agency footer or empty string if not found
     */
    public function grabWaFooterTemplate(string $locale = null) : string
    {
        /** @var App $app the first wa-config app instance */
        $app = AppInterface::instance();
        return $app->e_footer_get_localized_template($locale);
    }

    // TIPS : inspiration if need to fill forms in iframes : https://stackoverflow.com/questions/29168107/how-to-fill-a-rich-text-editor-field-for-a-codeception-acceptance-test
}
