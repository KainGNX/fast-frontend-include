<?php
/**
 * Base Context
 *
 * Base context class for views. Every context class should extend from this.
 * Wraps some of the environment variables that should not be used or repeated everywhere.
 *
 * @author  Jason Horvath <jason.horvath@greaterdevelopment.com>
 */

namespace FastFrontend\View;

class BaseContext {

    /**
     * @var string
     */
    protected $contextKey;

    /**
     * @var string
     */
    private $documentRoot;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $domainTrail;

    /**
     * Construct
     * 
     * @param string $contextKey
     * @return void
     */
    public function __construct(string $contextKey = '')
    {

        if(!empty($contextKey)) {
            $this->initContext($contextKey);
        }

        $this->setDocumentRoot();
        $this->setProtocol();
        $this->setDomain();
        $this->setDomainTrail();
    }

    /**
     * Init Context
     * 
     * @param string contextkey
     * @return void
     */
    protected function initContext(string $contextKey)
    {
        $this->contextKey = $contextKey;
    }

    /**
     * Set Document Root
     *
     * @return void
     */
    private function setDocumentRoot()
    {
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Set Protocol
     *
     * @return void
     */
    private function setProtocol()
    {
        $this->protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? 'https' : 'http';
    }

    /**
     * Set Domain
     *
     * @return void
     */
    private function setDomain()
    {
        $this->domain = $_SERVER['HTTP_HOST'];
    }

    /**
     * Set Domain Trail
     * Depending on environment, used to compensate pathing,
     * The domain trail could be just a slash, or the
     *
     * @return void
     */
    private function setDomainTrail() {
        $selfParts = array_filter(explode('/', $_SERVER['PHP_SELF']));
        if(strpos(end($selfParts), '.php')) {
            array_pop($selfParts);
        }
        $this->domainTrail =  ((count($selfParts) > 0) ? implode('/', $selfParts) . '/' : '');
    }

    /**
     * Full Root Path
     * Basically an absolute path
     *
     * @param string $relativePath
     * @return void
     */
    protected function fullRootPath(string $relativePath) {
        return $this->documentRoot . '/' . $this->domainTrail . $relativePath;
    }

    /**
     * Http Root
     *
     * @return string
     */
    public function httpRoot()
    {
        return $this->protocol . '://' . $this->domain . '/'. $this->domainTrail;
    }

    /**
     * Get Protocol
     *
     * @return string $protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}
