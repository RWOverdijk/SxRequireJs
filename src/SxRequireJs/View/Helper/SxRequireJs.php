<?php

namespace SxRequireJs\View\Helper;

use Zend\View\Helper\AbstractHelper,
    Zend\View\Model\ViewModel,
    Zend\Config\Config;

/**
 * Helper for working with RequireJS
 *
 * @package    SxRequireJs
 */
class SxRequireJs extends AbstractHelper
{

    /**
     * @var array The paths / modules for the config
     */
    protected $modules = array();

    /**
     * @var array The applications to dispatch
     */
    protected $applications = array();

    /**
     * @var string Holds the base path
     */
    protected $basePath;

    /**
     * @var string The baseUrl of the javascript files
     */
    protected $baseUrl = 'js';

    /**
     * @var string Holds the source file of require js
     */
    protected $requireJsSrc;

    /**
     * @var Zend\Config\Config Holds the custom configurations
     */
    protected $configs;

    /**
     * @var boolean true when already rendered, false when not.
     */
    protected $rendered = false;

    public function __construct()
    {
        $this->configs = new Config(array(), true);
    }

    /**
     * This method simply returns self, to allow flexibility.
     * 
     * @return SxRequireJs fluent interface
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * This method renders the needed files
     * 
     * @return string the application
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get the rendered output
     * 
     * @return string The rendered output
     */
    public function render()
    {
        if ($this->rendered) {
            return '';
        }

        $this->rendered = true;

        $output = $this->getRequireJs();

        $output .= $this->inlineScriptTag(array(
            array(
                'description' => 'The application config',
                'script'      => $this->getConfig(),
            ), array(
                'description' => 'The main application (entry point)',
                'script'      => $this->getMain(),
            )
        ));
        
        return $output; 
    }

    /**
     * This method allows you to clean the helper.
     *  IMPORTANT: This means you lose all configuration and applications.
     * 
     * @return SxRequireJs fluent interface
     */
    public function clear()
    {
        $this->modules = array();
        $this->applications = array();
        $this->configs = array();
        $this->baseUrl  = 'js';
        $this->basePath = null;

        return $this;
    }

    /**
     * This method allows you to set the baseUrl for the requireJs config.
     * 
     * @param string $url the new base Url
     * 
     * @return SxRequireJs fluent interface
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * This method allows you to get the rendered config.
     * 
     * @return string the rendered config.
     */
    public function getConfig()
    {
        return $this->renderConfig();
    }

    /**
     * This method allows you to get the rendered main code.
     * 
     * @return string the rendered main code.
     */
    public function getMain()
    {
        return $this->renderMain();
    }

    /**
     * This method allows you to add paths to the config for your modules.
     * 
     * @param array $paths the paths to add
     * 
     * @return SxRequireJs fluent interface
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * This method allows you to add custom configuration
     * 
     * @param array $config the custom configuration to add
     * 
     * @return SxRequireJs fluent interface
     */
    public function addConfiguration(array $config)
    {
        $config = new Config($config);
        $this->configs->merge($config);

        return $this;
    }

    /**
     * Get the base path from the viewHelper
     * 
     * @return string The basePath
     */
    protected function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->getView()->basePath();
        }

        return $this->basePath;
    }

    /**
     * This method allows you to add a path to the config for your modules.
     * 
     * @param string $modulename    the modulename to add
     * @param string $path          The path
     * 
     * @return SxRequireJs fluent interface
     */
    public function addPath($moduleName, $path = null)
    {
        if (null === $path) {
            $path = $moduleName . '/js';
        } else {
            $path = trim($path, '/');
        }

        $this->modules[$moduleName] = $this->getBasePath() . '/' . $path;

        return $this;
    }

    /**
     * Set the source file for requireJs. Requires the full path.
     *  IMPORTANT:  When setting this and deciding to not use require-jquery,
     *              please remember to add jquery to the paths if jquery
     *              is not available in the default path.
     * 
     * @param string $src the path to the source file.
     *
     * @return SxRequireJs fluent interface
     */
    public function setRequireJsSourceFile($src)
    {
        if (!is_string($src)) {
            throw new \Exception\InvalidArgumentException(
                    'Unexpected argument type received. Expected String got "' . gettype($src) . '"'
            );
        }

        $this->requireJsSrc = $src;

        return $this;
    }

    /**
     * This method allows you to get the requireJs script tag, to include require js on your page.
     * 
     * @return string the script tag
     */
    public function getRequireJs()
    {
        $src = (null !== $this->requireJsSrc) ?
                $this->requireJsSrc :
                $this->getBasePath() . '/' . $this->baseUrl . '/require-jquery.js';

        return '<script src="' . $src . '"></script>';
    }

    /**
     * This method allows you to add an application that will be dispatched on page load.
     * 
     * @param string    $applicationId  The module ID to add
     * @param integer   $priority       The priority of this application (lower prioty means load later)
     * 
     * @return SxRequireJs fluent interface
     */
    public function addApplication($applicationId, $priority = 1)
    {
        $this->applications[$applicationId] = array(
            'applicationId' => $applicationId,
            'priority'      => $priority,
        );

        return $this;
    }

    /**
     * This method renders the config.
     * 
     * @return string the rendered config
     */
    protected function renderConfig()
    {
        // If we don't have a config, or any modules, there's no point in returning a config.
        if (empty($this->configs) && empty($this->modules)) {
            return '';
        }

        $viewModel          = new ViewModel();
        $viewModel->baseUrl = $this->baseUrl;
        $viewModel->setTemplate('sxrequirejs/config.phtml');

        if (!empty($this->modules)) {
            $viewModel->paths = json_encode($this->modules);
        }

        if (!empty($this->configs)) {
            $viewModel->configuration = ',' . substr(json_encode($this->configs->toArray()), 1, -1);
        }

        return $this->getView()->render($viewModel);
    }

    /**
     * This method renders the main application code.
     * 
     * @return string the application code
     */
    protected function renderMain()
    {
        // If we don't have any applications, there's no point in returning a main.
        if (empty($this->applications)) {
            return '';
        }

        $this->prioritizeApplications();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('sxrequirejs/main.phtml');

        $arguments = array();
        $initializers = array();
        $dependencies = array();

        foreach ($this->applications as $app) {
            $dependencies[] = '"' . $app['applicationId'] . '"';
            $strippedId     = str_replace('/', '', $app['applicationId']);
            $arguments[]    = $strippedId;
            $initializers[] = $strippedId . '();';
        }

        $viewModel->dependencies = '[' . implode(', ', $dependencies) . '], ';
        $viewModel->arguments    = implode(', ', $arguments);
        $viewModel->initializers = implode(PHP_EOL, $initializers) . PHP_EOL;

        return $this->getView()->render($viewModel);
    }

    /**
     * This method takes a bunch of scripts, and puts them inside of a script tag.
     * 
     * @param array $scripts    The scripts to add inline
     * @param array $attributes The attributes for the script tag
     * 
     * @return string The script tag
     */
    protected function inlineScriptTag(array $scripts, array $attributes = array())
    {
        $scriptTag = '<script type="text/javascript"';

        if (!empty($attributes)) {
            foreach ($attributes as $attr => $val) {
                $scriptTag .= " {$attr}=\"$val\"";
            }
        }

        $scriptTag .= '>' . PHP_EOL;

        foreach ($scripts as $script) {
            $scriptTag .= PHP_EOL . '// ' . $script['description'] . PHP_EOL;
            $scriptTag .= $script['script'] . PHP_EOL;
        }

        $scriptTag .= '</script>';

        return $scriptTag;
    }

    /**
     * This method sorts the applications array by priority.
     * 
     * @return SxRequireJs fluent interface
     */
    protected function prioritizeApplications()
    {
        if (empty($this->applications)) {
            return $this;
        }

        usort($this->applications, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return 0;
            }
            
            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });

        return $this;
    }

}