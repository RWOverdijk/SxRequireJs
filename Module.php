<?php
namespace SxRequireJs;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap($e)
    {
        $viewManager    = $e->getApplication()->getServiceManager()->get('ViewManager');
        $helperManager  = $viewManager->getHelperManager();
        $helperManager->get('requirejs')->setViewManager($viewManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
