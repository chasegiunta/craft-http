<?php
/**
 * http plugin for Craft CMS 3.x
 *
 * simply return the http status of an asset/url
 *
 * @link      chasegiunta.com
 * @copyright Copyright (c) 2018 Chase Giunta
 */

namespace chasegiunta\http\variables;

use chasegiunta\http\Http;
use yii\web\BadRequestHttpException;
use yii\base\InvalidConfigException;

use Craft;

/**
 * http Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.http }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Chase Giunta
 * @package   Http
 * @since     1.0.0
 */
class HttpVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Check if URL returns '200 OK'
     *
     * @param string $url to check against
     * @param boolean $output whether to output raw http status
     * @return bool|string
     */
    public function status(string $url = null, bool $output = false)
    {
        if ($url === null) {
            throw new InvalidConfigException('URL has not been set on http.status()');
        }
        
        $host = '';
        try {

            if (substr($url, 0, 4) !== 'http' && substr($url, 0, 1 === '/')) {
                // $url seems to be a local URL
                $host = Craft::$app->request->getHostInfo();
            }

            try {
                $headers = get_headers($host.$url);
            } catch (\Exception $e) {
                // URL offline, not accessible, etc.
                if ($output === true) {
                    throw new BadRequestHttpException('Problem getting headers from '.$host.$url);
                } else {
                    return false;
                }
            }

            if ( strstr($headers[0], '200 OK') ) {

                return $output === true ? $headers[0] : true;

            } else {
                
                if ($output === true) {

                    return $headers[0];
                } else if (preg_match('/^HTTP\/\d\.\d\s+(301|302)/',$headers[0])) {
                    // Test for 301 or 302
                    $url = $this->handleRedirect($url, $headers);

                    return $this->status($url, $output);
                }

            }
            
        } catch (\Exception $e) {
            exit('Something went wrong: ' . $e->getMessage());
        }
    }

    public function ok(string $url = null) {
        return $this->status($url);
    }

    /**
     * Handle 301/302 Redirects
     * 
     * Use cases:
     * http://unpkg.com/vue -> http://unpkg.com/vue (301 redirect)
     * https://unpkg.com/vue -> https://unpkg.com/vue@2.5.13/dist/vue.js (302 redirect)
     *
     * @param string $url
     * @param array $headers
     * @return string URL which should result in a 200 lookup 
     */
    public function handleRedirect(string $url, array $headers)
    {

        foreach($headers as $value)
        {
            if(substr(strtolower($value), 0, 9) === "location:")
            {
                $redirectUrl = trim(substr($value, 9, strlen($value)));
                break;
            }
        }

        // Make sure $redirectUrl is set
        if (!isset($redirectUrl)) {
            return $output === true ? $headers[0].' (failed to follow redirect)' : true;
        }

        // Handle simple HTTP -> HTTPS redirects
        if ( substr($redirectUrl, 0, 5) === 'https' && substr($url, 0, 5) === 'http:' ) {
            $url = str_replace('http:', 'https:', $url);

            return $url;
        }

        // If the $redirectUrl begins a '/', it's relative to the domain of $url
        if (substr($redirectUrl, 0, 1) === "/") {
            $parsedUrl = parse_url($url);
            $redirectUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$redirectUrl;

            return $redirectUrl;
        }
        
    }
}
