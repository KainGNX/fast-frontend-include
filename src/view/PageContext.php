<?php
/**
 * Page Context
 *
 * Page context class for views. Focused on global and page specific output.
 *
 * @author  Jason Horvath <jason.horvath@greaterdevelopment.com>
 */

namespace FastFrontend\View;

use FastFrontend\View\BaseContext;

class PageContext extends BaseContext {

    /**
     * Patterns for determing which files are remote
     * 
     * @const array REMOTE_URL_STARTS
     */
    const REMOTE_URL_PATTERNS = [
        'https',
        'http',
        '//'
    ];

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
    protected $jsRelative = 'js';

    /**
     * @var string
     */
    protected $cssRelative = 'css';

    /**
     * @var bool
     */
    protected $cacheBust = false;

    /**
     * Construct
     * 
     * @param string $contextKey
     * @param array $globalIncludes
     * @return void
     */
    public function __construct(
        string $contextKey = '',
        array $globalIncludes = [])
    {
        
        parent::__construct($contextKey);

        $this->globalIncludes = [
            'js' => $globalIncludes['js'] ?? [],
            'css' => $globalIncludes['css'] ?? []
        ];
        
    }

    /**
     * Init Context
     * 
     * @param string $contextKey
     */
    protected function initContext(string $contextKey)
    {
        parent::initContext($contextKey);
        $this->setJsRelative($this->jsRelative . '/' . $contextKey);
        $this->setCssRelative($this->cssRelative . '/' . $contextKey);
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
                    $fileUrl = $this->assembleFileUrl($relativeFilePath);
                    $tagOutput .= sprintf($tagTemplate, $fileUrl);
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
            $includeUrl = (!$this->isRemoteFilename($relFilename)) ? $this->assembleFileUrl($relFilename) : $relFilename;
            $appIncludesOutput .= sprintf(self::INCLUDE_TAGS[$type], $includeUrl);
        }
        return $appIncludesOutput;
    }

    /**
     * Is Remote Filename
     * 
     * @param string $filename
     * @return bool
     */
    protected function isRemoteFilename(string $filename)
    {
        foreach(self::REMOTE_URL_PATTERNS as $pattern) {
            if(strpos($filename, $pattern) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Assemble File Url
     * 
     * @param string $filename
     * @return string
     */
    protected function assembleFileUrl(string $filename)
    {
        return $this->httpRoot() . $filename .= ($this->cacheBust) ? '?' . time() : '' ; 
    }

    /**
     * Set Cache Bust
     * 
     * @param bool $cacheFlag
     * @return void
     */
    public function setCacheBust(bool $cacheFlag)
    {
        $this->cacheBust = $cacheFlag;
    }

}
