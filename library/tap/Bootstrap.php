<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{


    public function _initDbRegistry()
    {
        $this->bootstrap('multidb');
        $multidb = $this->getPluginResource('multidb');
        Zend_Registry::set('live', $multidb->getDb('live'));
        Zend_Registry::set('read', $multidb->getDb('read'));
        Zend_Registry::set('test', $multidb->getDb('test'));
    }

    
    protected function _initAutoloaders()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);

        $defaultLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => '',
                    'basePath' => APPLICATION_PATH
                ));
    }
    
    protected function _initLayoutHelper()
    {

        
    }
    
    protected function _initRoutes()
    {
        
        $frontController = Zend_Controller_Front::getInstance();
        
        $router = $frontController->getRouter();
        
        
        $router->addRoute(
            'page',
            new Zend_Controller_Router_Route(
                'page/:slug',
                array( 
                    'module' => 'default',
                    'controller' => 'page',
                    'action' => 'index'
                )
            )
        );        
        
    }
    
    
}