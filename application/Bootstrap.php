<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDoctype()
    {
    }

    /**
    * Loads app-wide constants from ini file
    */
   protected function _initDefineConstants()
   {
       $constantFile = APPLICATION_PATH . '/configs/constants.ini';
       $iniParser = new Zend_Config_Ini($constantFile);

       foreach ($iniParser->toArray() as $constName => $constantVal) {
           define($constName, $constantVal);
       }
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
       
}

