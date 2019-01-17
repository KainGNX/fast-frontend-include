<?php
/**
 * Context
 *
 * Base context class for views. Every context class should extend from this.
 * Wraps some of the environment variables that should not be used or repeated everywhere.
 *
 * @author  Jason Horvath <jason.horvath@greaterdevelopment.com>
 */
namespace FastFrontend\View;


class Context {

    /**
     * Tag templates for output generation, referenced by key
     *
     * @const array INCLUDE_TAGS
     */
    const INCLUDE_TAGS = [
        'js' => '<script src="%s" type="text/javascript"></script>'."\n",
        'css' => '<link href="%s" type="text/css" rel="stylesheet"/>'."\n"
    ];

    /**
     * @var array
     */
    protected $globalIncludes;

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
     * @var string
     */
    protected $jsRelative = 'js';

    /**
     * @var string
     */
    protected $cssRelative = 'css';

    /**
     * Construct
     * 
     * @param string $contextKey
     * @param array $globalIncludes
     */
    public function __construct(
        string $contextKey = '',
        array $globalIncludes = [])
    {

        if(!empty($contextKey)) {
            $this->initContext($contextKey);
        }
        
        $this->globalIncludes = [
            'js' => $globalIncludes['js'] ?? [],
            'css' => $globalIncludes['css'] ?? []
        ];
        
        $this->setDocumentRoot();
        $this->setProtocol();
        $this->setDomain();
        $this->setDomainTrail();
    }

    /**
     * Init Context
     * 
     * @param string $contextKey
     */
    protected function initContext(string $contextKey)
    {
        $this->contextKey = $contextKey;
        $this->setJsRelative($this->jsRelative . '/' . $contextKey);
        $this->setCssRelative($this->cssRelative . '/' . $contextKey);
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
        $selfParts = explode('/', $_SERVER['PHP_SELF']);
        if(strpos(end($selfParts), '.php')) {
            array_pop($selfParts);
        }
        $this->domainTrail = '/' . implode('/', $selfParts);
    }

    /**
     * Set Js Relative
     * Javascript relative path from document root
     *
     * @param string $relativePath
     * @return void
     */
    protected function setJsRelative(string $relativePath)
    {
        $this->jsRelative = $relativePath;
    }

    /**
     * Set CSS Relative
     * CSS Relative path from document root
     *
     * @param string $relativePath
     * @return void
     */
    protected function setCssRelative(string $relativePath)
    {
        $this->cssRelative = $relativePath;
    }

    /**
     * Assett Path Exists
     * Does the asset path exists
     *
     * @param string $assetRelativePath
     * @return bool
     */
    protected function assetPathExists(string $assetRelativePath)
    {
        $assetPath = $this->fullRootPath($assetRelativePath);
        return stream_resolve_include_path($assetPath) === $assetPath;
    }

    /**
     * Get Asset List
     * File list of assets in given relative path from the document root
     *
     * @param string $assetRelativePath
     * @return array $assetList
     */
    protected function getAssetList(string $assetRelativePath)
    {
        $assetList = [];
        $assetPath = $this->fullRootPath($assetRelativePath);
        if($this->assetPathExists($assetRelativePath)) {
            $assetList = array_slice(scandir($assetPath), 2);
        }
        return $assetList;
    }

    /**
     * Full Root Path
     * Basically and absolute path
     *
     * @param string $relativePath
     * @return void
     */
    protected function fullRootPath(string $relativePath) {
        return $this->documentRoot . '/' . $relativePath;
    }

    /**
     * Get Asset Tags
     *
     * @param string $tagType
     * @param string $assetRelative
     * @return string $tagOutput
     */
    protected function getAssetTags(string $tagType, string $assetRelative)
    {
        $tagOutput = '';
        $assetList = $this->getAssetList($assetRelative);
        if(!empty($assetList)) {
            $tagTemplate = self::INCLUDE_TAGS[$tagType];
            foreach($assetList as $filename) {
                $relativeFilePath = $assetRelative . '/' . $filename;
                if(!in_array($relativeFilePath, $this->globalIncludes[$tagType])) {
                    $httpFilePath = $this->httpFilePath($assetRelative, $filename);
                    $tagOutput .= sprintf($tagTemplate, $httpFilePath);
                }
            }
        }
        return $tagOutput;
    }

    /**
     * Js
     * Generate JS tag output
     *
     * @return string
     */
    public function js()
    {
        return $this->appIncludes('js') . $this->getAssetTags('js', $this->jsRelative);
    }

    /**
     *Css
     * Generate CSS tag output
     *
     * @return string
     */
    public function css()
    {
        return $this->appIncludes('css') . $this->getAssetTags('css', $this->cssRelative);
    }

    /**
     * App Includes
     * Generate App includes tag output based on type
     *
     * @return string $appIncludesOutput
     */
    protected function appIncludes(string $type)
    {
        $appIncludesOutput = '';
        foreach($this->globalIncludes[$type] as $relFilename) {
            $appIncludesOutput .= sprintf(self::INCLUDE_TAGS[$type], $this->httpRoot() . $relFilename);
        }
        return $appIncludesOutput;
    }
    
    /**
     * Http Root
     *
     * @return string
     */
    public function httpRoot() {
        return $this->protocol . '://' . $this->domain . $this->domainTrail;
    }

    /**
     * Http File Path
     *
     * @return string
     *
     */
    public function httpFilePath($relativePath, $filename)
    {
        return $this->httpRoot() . $relativePath . '/' . $filename;
    }

    /**
     * Get Protocol
     *
     * @return string $protocol
     */
    public function getProtocol() {
        return $this->protocol;
    }

}
