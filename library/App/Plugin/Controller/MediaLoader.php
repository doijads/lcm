<?php

class App_Plugin_Controller_MediaLoader extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $this->loadJavaScript($request);
        $this->loadCSS($request);

        $this->loadMediaFromIni($request);
    }

    public function loadJavaScript(Zend_Controller_Request_Abstract $request) {
        $view = $this->getView();

        $moduleJS = $this->getModuleFileName($request, 'js');
        if ($this->checkFile($moduleJS)) {
            $view->headScript()->appendFile($moduleJS);
        }

        $controllerJS = $this->getControllerFileName($request, 'js');
        if ($this->checkFile($controllerJS)) {
            $view->headScript()->appendFile($controllerJS);
        }

        $actionJS = $this->getActionFileName($request, 'js');
        if ($this->checkFile($actionJS)) {
            $view->headScript()->appendFile($actionJS);
        }
    }

    public function loadCSS(Zend_Controller_Request_Abstract $request) {
        $view = $this->getView();
        
        $moduleCSS = $this->getModuleFileName($request, 'css');
        if ($this->checkFile($moduleCSS)) {
            $view->headLink()->appendStylesheet($moduleCSS);
        }

        $controllerCSS = $this->getControllerFileName($request, 'css');
        if ($this->checkFile($controllerCSS)) {
            $view->headLink()->appendStylesheet($controllerCSS);
        }

        $actionCSS = $this->getActionFileName($request, 'css');
        if ($this->checkFile($actionCSS)) {
            $view->headLink()->appendStylesheet($actionCSS);
        }
    }

    /**
     * @return Zend_View
     */
    public function getView() {
        return Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
    }

    public function getActionFileName(Zend_Controller_Request_Abstract $request, $ext) {
        $action = $request->getActionName();
        $controller = $request->getControllerName();
        $module = $request->getModuleName();

        return '/' . $ext . '/modules/' . $module . '/' . $controller . '/' . $action . '.' . $ext;
    }

    public function getControllerFileName(Zend_Controller_Request_Abstract $request, $ext) {
        $controller = $request->getControllerName();
        $module = $request->getModuleName();

        return '/' . $ext . '/modules/' . $module . '/' . $controller . '.' . $ext;
    }

    public function getModuleFileName(Zend_Controller_Request_Abstract $request, $ext) {
        $module = $request->getModuleName();

        return '/' . $ext . '/modules/' . $module . '.' . $ext;
    }

    public function checkFile($fileName) {
        return is_file(PUBLIC_PATH . $fileName);
    }

    public function loadMediaFromIni(Zend_Controller_Request_Abstract $request) {
        //$filename = APPLICATION_PATH .'/modules/' . $request->getModuleName() . '/configs/media.ini';
        $filename = APPLICATION_PATH . '/configs/media.ini';

        if (!is_file($filename)) {
            return;
        }

        $view = $this->getView();

        $media = new Zend_Config_Ini($filename);

        $mediaArray = $media->toArray();



        if (isset($mediaArray['javaScripts']) && isset($mediaArray['javaScripts'][$request->getControllerName()])) {

            foreach ($mediaArray['javaScripts'][$request->getControllerName()] as $key => $value) {



                if (is_numeric($key)) {
                    $view->headScript()->appendFile($value);
                } else if (is_string($key) && is_array($value) && $key == $request->getActionName()) {

                    foreach ($value as $jsPath) {
                        $view->headScript()->appendFile($jsPath);
                    }
                }
            }
        }

        if (isset($mediaArray['css']) && isset($mediaArray['css'][$request->getControllerName()])) {

            foreach ($mediaArray['css'][$request->getControllerName()] as $key => $value) {

                if (is_numeric($key)) {
                    $view->headLink()->appendStylesheet($value);
                } else if (is_string($key) && is_array($value) && $key == $request->getActionName()) {
                    foreach ($value as $cssPath) {
                        $view->headLink()->appendStylesheet($cssPath);
                    }
                }
            }
        }
    }

}