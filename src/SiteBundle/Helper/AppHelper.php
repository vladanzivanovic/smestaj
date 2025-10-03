<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 6/27/2017
 * Time: 7:20 PM
 */

namespace SiteBundle\Helper;


use Symfony\Component\HttpFoundation\RequestStack;

class AppHelper
{
    private $request;
    private $environment;
    private $url;
    private $asseticUrl;

    /**
     * @param RequestStack $requestStack
     * @param string       $kernelEnvironment
     * @param string       $asseticUrl
     */
    public function __construct(
        RequestStack $requestStack,
        $kernelEnvironment,
        $asseticUrl
    ){
        $this->request = $requestStack->getCurrentRequest();
        $this->environment = $kernelEnvironment;
        $this->asseticUrl = $asseticUrl;
    }

    public function getHttpHostBaseUrl()
    {
        $this->url = $this->request->getSchemeAndHttpHost() . $this->request->getBaseUrl();

        if ($this->url . substr(-1, 1) != '/') {
            $this->url .= '/';
        }

        $this->setWebToUrl();

        if ($this->environment === 'dev')
            $this->url = str_replace('app_dev.php', '', $this->url);

        return $this->url;
    }

    private function setWebToUrl()
    {
        if(false === strpos($this->url, 'web/')) {
            $this->url .= !empty($this->asseticUrl) ? $this->asseticUrl .'/' : '';
        }
    }
}