<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Layout.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Layout.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
require_once 'Zend/Tool/Project/Provider/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Tool
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Layout extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{
<<<<<<< HEAD
       /**
        * @var string Layout path
        */
       protected $_layoutPath = 'APPLICATION_PATH "/layouts/scripts/"';
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

    public static function createResource(Zend_Tool_Project_Profile $profile, $layoutName = 'layout')
    {
        $applicationDirectory = $profile->search('applicationDirectory');
        $layoutDirectory = $applicationDirectory->search('layoutsDirectory');

        if ($layoutDirectory == false) {
            $layoutDirectory = $applicationDirectory->createResource('layoutsDirectory');
        }

        $layoutScriptsDirectory = $layoutDirectory->search('layoutScriptsDirectory');

        if ($layoutScriptsDirectory == false) {
            $layoutScriptsDirectory = $layoutDirectory->createResource('layoutScriptsDirectory');
        }

        $layoutScriptFile = $layoutScriptsDirectory->search('layoutScriptFile', array('layoutName' => 'layout'));

        if ($layoutScriptFile == false) {
            $layoutScriptFile = $layoutScriptsDirectory->createResource('layoutScriptFile', array('layoutName' => 'layout'));
        }

        return $layoutScriptFile;
    }

    public function enable()
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $applicationConfigResource = $profile->search('ApplicationConfigFile');

        if (!$applicationConfigResource) {
            throw new Zend_Tool_Project_Exception('A project with an application config file is required to use this provider.');
        }

        $zc = $applicationConfigResource->getAsZendConfig();

<<<<<<< HEAD
        if (isset($zc->resources) && isset($zc->resources->layout)) {
=======
        if (isset($zc->resources) && isset($zf->resources->layout)) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            $this->_registry->getResponse()->appendContent('A layout resource already exists in this project\'s application configuration file.');
            return;
        }

<<<<<<< HEAD
        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would add "resources.layout.layoutPath" key to the application config file.');
        } else {
            $applicationConfigResource->addStringItem('resources.layout.layoutPath', $this->_layoutPath, 'production', false);
            $applicationConfigResource->create();

            $this->_registry->getResponse()->appendContent('A layout entry has been added to the application config file.');
            
            $layoutScriptFile = self::createResource($profile);
            if (!$layoutScriptFile->exists()) {
                $layoutScriptFile->create();
                $this->_registry->getResponse()->appendContent(
                    'A default layout has been created at '
                    . $layoutScriptFile->getPath()
                    );

            }

            $this->_storeProfile();
        }
    }

    public function disable()
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $applicationConfigResource = $this->_getApplicationConfigResource($profile);
        $zc = $applicationConfigResource->getAsZendConfig();

        if (isset($zc->resources) && !isset($zc->resources->layout)) {
            $this->_registry->getResponse()->appendContent('No layout configuration exists in application config file.');
            return;
        }

        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would remove "resources.layout.layoutPath" key from the application config file.');
        } else {

            // Remove the resources.layout.layoutPath directive from application config
            $applicationConfigResource->removeStringItem('resources.layout.layoutPath', $this->_layoutPath, 'production', false);
            $applicationConfigResource->create();

            // Tell the user about the good work we've done
            $this->_registry->getResponse()->appendContent('Layout entry has been removed from the application config file.');

            $this->_storeProfile();
        }
     }

    protected function _getApplicationConfigResource(Zend_Tool_Project_Profile $profile)
    {
        $applicationConfigResource = $profile->search('ApplicationConfigFile');
        if (!$applicationConfigResource) {
            throw new Zend_Tool_Project_Exception('A project with an application config file is required to use this provider.');
        }

        return $applicationConfigResource;
    }
=======
        $layoutPath = 'APPLICATION_PATH "/layouts/scripts/"';

        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would add "resources.layout.layoutPath" key to the application config file.');
        } else {
            $applicationConfigResource->addStringItem('resources.layout.layoutPath', $layoutPath, 'production', false);
            $applicationConfigResource->create();

            $layoutScriptFile = self::createResource($profile);

            $layoutScriptFile->create();

            $this->_registry->getResponse()->appendContent(
                'Layouts have been enabled, and a default layout created at '
                . $layoutScriptFile->getPath()
                );

            $this->_registry->getResponse()->appendContent('A layout entry has been added to the application config file.');
        }



    }

    public function disable()
    {
        // @todo
    }



>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
}
