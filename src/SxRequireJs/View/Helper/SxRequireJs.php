<?php
namespace SxRequireJs\View\Helper;

use Zend\View\Helper\AbstractHelper,
    Zend\View\Model\ViewModel;

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
    protected $modules      = array();

    /**
     * @var array The applications to dispatch
     */
    protected $applications = array();

    /**
     * @var string The baseUrl of the javascript files
     */
    protected $baseUrl      = 'js';

    /**
     * @var array Holds the custom configurations
     */
    protected $configs      = array();

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
        return $this->getRequireJs() . $this->inlineScriptTag(array(
            array(
                'description'   => 'The application config',
                'script'        => $this->getConfig(),
            ), array(
                'description'   => 'The main application (entry point)',
                'script'        => $this->getMain(),
            ),
        ));
    }

    /**
     * This method allows you to clean the helper.
     *  IMPORTANT: This means you lose all configuration and applications.
     * 
     * @return SxRequireJs fluent interface
     */
    public function clear()
    {
        $this->modules      = array();
        $this->applications = array();
        $this->configs      = array();
        $this->baseUrl      = 'js';

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
        foreach ($config as $key => $value) {
            if (isset($this->configs[$key]) && is_array($this->configs[$key])) {
                if (is_array($value)) {
                    $this->configs[$key] = array_merge($this->configs[$key], $value);
                } else {
                    $this->configs[$key][] = $value;
                }
            } else {
                $this->configs[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * This method allows you to add a path to the config for your modules.
     * 
     * @param string $modulename    the modulename to add
     * @param string $path          The path
     * 
     * @return SxRequireJs fluent interface
     */
    public function addModule($moduleName, $path = null)
    {
        return $this->addPath($moduleName, $path);
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

        $this->modules[$moduleName] = $path;

        return $this;
    }

    /**
     * This method allows you to get the requireJs script tag, to include require js on your page.
     * 
     * @return string the script tag
     */
    public function getRequireJs()
    {
        return '<script src="'.$this->baseUrl.'/require-jquery.js"></script>';
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
        $this->applications[$applicationId] = array (
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
        $viewModel          = new ViewModel();
        $viewModel->baseUrl = $this->baseUrl;
        $viewModel->setTemplate('sxrequirejs/config.phtml');

        if (!empty($this->modules)) {
            $viewModel->paths = json_encode($this->modules);
        }

        if (!empty($this->configs)) {
            $viewModel->configuration = ',' . substr(json_encode($this->configs), 1, -1);
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

        $dependencies   = '';
        $arguments      = array();
        $initializers   = array();

        $dependencies   .= '[';

        foreach ($this->applications as $app) {
            $dependencies       .= '"'.$app['applicationId'].'",';
            $strippedId         = str_replace('/', '', $app['applicationId']);
            $arguments[]        = $strippedId;
            $initializers[]     = $strippedId . '();';
        }

        $dependencies = substr($dependencies, 0, -1) . '], ';

        $viewModel->dependencies    = $dependencies;
        $viewModel->arguments       = implode(', ', $arguments);
        $viewModel->initializers    = implode(PHP_EOL, $initializers) . PHP_EOL;

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

        $sorter = array();
        $ret    = array();

        reset($this->applications);

        foreach ($this->applications as $k => $v) {
            $sorter[$k] = $v['priority'];
        }

        asort($sorter);

        foreach ($sorter as $k => $v) {
            $ret[$k]=$this->applications[$k];
        }

        $this->applications = $ret;

        return $this;
    }
}