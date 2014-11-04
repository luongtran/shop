<?php

class WooCheckoutStepsLiteBackendFestiPlugin
      extends WooCheckoutStepsLiteFestiPlugin
{
    protected $_menuOptions = array(
        'settings' => "Settings",
    );
    protected $_defaultMenuOption = 'settings';
    
    protected function onInit()
    {
        $this->addActionListener('admin_menu', 'onAdminMenuAction');
    } // end onInit
    
    public function onInstall($refresh = false, $settings = false)
    {      
        if (!$this->_fileSystem) {
            $this->_fileSystem = $this->getFileSystemInstance();
        }
        
        if ($this->_hasPermissionToCreateCacheFolder()) {
            $this->_fileSystem->mkdir($this->_pluginCachePath, 0777);
        }

        if (!$refresh) {
            $currentTheme = $this->getCurrentTheme();
            $currentTheme = false;
            if (!$currentTheme) {
                
                $currentTheme = $this->_defaultTheme; 
                $value = array($currentTheme);
                $this->updateOptions('current_theme', $value);
            }
        }
                     
        if (!$refresh) {
            $this->installThemeOptions($currentTheme);
        } 
    } // end onInstal
    
    public function installThemeOptions($theme)
    {
        $themeSettings = $this->getOptions('settings_'.$theme);
            
        if ($themeSettings) {
            return true;
        }
        
        $this->_doInitDefaultThemeOptions($theme);
    } //end installThemeOptions
    
    private function _doInitDefaultThemeOptions($theme)
    {
        $generalOptions = $this->getGeneralSettings();
        
        $options = $this->getWizardThemeSettings($theme, 'options');
        
        if (!$options) {
            $options = array();
        }
        
        $options = array_merge($generalOptions, $options);
        
        foreach ($options as $ident => &$item) {
            if ($this->_hasDefaultValueInItem($item)) {
                $values[$ident] = $item['default'];
            }
        }
        unset($item);
        
        $this->updateOptions('settings_'.$theme, $values);
    } // end _doInitDefaultOptions
    
    private function _hasDefaultValueInItem($item)
    {
        return isset($item['default']);
    } //end _hasDefaultValueInItem
    
    public function onUninstall()
    {
        $temes = $this->_wizardThemes;
        
        foreach ($temes as $theme) {
            $optionName = $this->_optionsPrefix.'settings_'.$theme;
            delete_option($optionName);
        }
                 
        $optionName = $this->_optionsPrefix.'current_theme';
        delete_option($optionName);  
    } // end onUninstall
    
    private function _hasPermissionToCreateCacheFolder()
    {
        return ($this->_fileSystem->is_writable($this->_pluginPath)
               && !file_exists($this->_pluginCachePath));
    } // end _hasPermissionToCreateFolder
    
    public function _onFileSystemInstanceAction()
    {
        $this->_fileSystem = $this->getFileSystemInstance();
    } // end _onFileSystemInstanceAction
    
    public function getPluginTemplatePath($fileName)
    {
        return $this->_pluginTemplatePath.'backend/'.$fileName;
    } // end getPluginTemplatePath
    
    public function getPluginCssUrl($fileName) 
    {
        return $this->_pluginCssUrl.'backend/'.$fileName;
    } // end getPluginCssUrl
    
    public function getPluginJsUrl($fileName)
    {
        return $this->_pluginJsUrl.'backend/'.$fileName;
    } // end getPluginJsUrl
    
    public function onAdminMenuAction() 
    {
        $page = add_menu_page(
            __(
                'Woocommerce Multistep Checkout Wizard Lite',
                $this->_languageDomain
            ), 
            __('Checkout Wizard', $this->_languageDomain), 
            'manage_options', 
            'festi-checkout-steps-wizard', 
            array(&$this, 'onDisplayOptionPage'), 
            $this->getPluginImagesUrl('icon_16x16.png')
        );
        
        $this->addActionListener(
            'admin_print_scripts-'.$page, 
            'onInitJsAction'
        );
        
        $this->addActionListener(
            'admin_print_styles-'.$page, 
            'onInitCssAction'
        );
        
        $this->addActionListener(
            'admin_head-'.$page,
            '_onFileSystemInstanceAction'
        );
    } // end onAdminMenuAction
    
    public function onInitJsAction()
    {
        $this->onEnqueueJsFileAction('jquery');
        $this->onEnqueueJsFileAction(
            'festi-checkout-steps-wizard-colorpicker',
            'colorpicker.js',
            'jquery'
        );
        
        $this->onEnqueueJsFileAction(
            'jquery-ui-sortable',
            false,
            'jquery'
        );
        
        $this->onEnqueueJsFileAction(
            'festi-checkout-steps-wizard-tooltip',
            'tooltip.js',
            'festi-checkout-steps-wizard-colorpicker'
        );
        
        $this->onEnqueueJsFileAction(
            'festi-checkout-steps-wizard-general',
            'general.js',
            'jquery'
        );       
    } // end onInitJsAction
    
    public function onInitCssAction()
    {
        $this->onEnqueueCssFileAction(
            'festi-checkout-steps-wizard-styles',
            'style.css'
        );
        
        $this->onEnqueueCssFileAction(
            'festi-checkout-steps-wizard-menu',
            'menu.css'
        );
        
        $this->onEnqueueCssFileAction(
            'festi-checkout-steps-wizard-tooltip',
            'tooltip.css'
        );
        
        $this->onEnqueueCssFileAction(
            'festi-checkout-steps-wizard-colorpicker',
            'colorpicker.css'
        );
        
        $this->onEnqueueCssFileAction(
            'festi-checkout-steps-wizard-sortable',
            'sortable.css'
        );
    } // end onInitCssAction
    
    public function onDisplayOptionPage()
    {
        if (!$this->_menuOptions) {         
            $menu = $this->fetch('menu.phtml');
            echo $menu;
        }

        $methodName = 'fetchOptionPage';
        
        if ($this->hasOptionPageInRequest()) {
            $postfix = $_GET['tab'];
        } else {
            $postfix = $this->_defaultMenuOption;
        }
        $methodName.= ucfirst($postfix);
        
        $method = array(&$this, $methodName);
        
        if (!is_callable($method)) {
            throw new Exception("Undefined method name: ".$methodName);
        }
        
        call_user_func_array($method, array());
    } // end onDisplayOptionPage
    
    protected function hasOptionPageInRequest()
    {
        return array_key_exists('tab', $_GET)
               && array_key_exists($_GET['tab'], $this->_menuOptions);
    } // end hasOptionPageInRequest
    
    public function getOptionsFieldSet($theme)
    {
        $fildset = array(
            'general' => array(),
        );
        
        $settings = $this->loadSettings($theme);
        
        if (!$settings) {
            return false;
        }
        
        foreach ($settings as $ident => &$item) {
            if (array_key_exists('fieldsetKey', $item)) {
               $key = $item['fieldsetKey'];
               $fildset[$key]['filds'][$ident] = $settings[$ident];
            }
        }
        unset($item);
        
        return $fildset;
    } // end getOptionsFieldSet
    
    public function getGeneralSettings()
    {
        $path = $this->_pluginPath.'includes/general_settings_list.php';
        
        if (!file_exists($path)) {
            return false;
        }
        
        $generalSettings = include($path);
        
        return $generalSettings;
    } //end getGeneralSettings
    
    public function getSettings($optionsList)
    {
        $generalSettings = $this->getGeneralSettings();
        
        $path = $this->_pluginPath.'includes/themes_settings_list.php';
        
        if (!file_exists($path)) {
            return false;
        }
        

        $settings = include($path);

        $diff = array_diff_key($settings, $optionsList);
        $options = array_diff_key($settings, $diff);
        
        $options = $this->arrayReplace($optionsList, $options);
        
        $options = array_merge($generalSettings, $options);

        return $options;
    } // end loadSettings
    
    public function arrayReplace($arrayOne, $arrayTwo)
    {
        foreach ($arrayOne as $key => $value) {

            if (!array_key_exists($key, $arrayTwo)) {
                continue;
            }
            
            if (is_array($value)) {          
                $arrayTwo[$key] = $this->arrayReplace($arrayTwo[$key], $arrayOne[$key]);   
            }
            
            $arrayOne[$key] = $arrayTwo[$key];
        } 

        return $arrayOne;
    } // end arrayReplace
    
    public function loadSettings($theme)
    {
        $optionsList = $this->getWizardThemeSettings($theme, 'options');
        
        if (!$optionsList) {
            return false;
        }
        
        $settings = $this->getSettings($optionsList);
        
        if (!$settings) {
            return false;
        }

        $values = $this->getOptions('settings_'.$theme);
        if ($values) {
            foreach ($settings as $ident => &$item) {
                if (array_key_exists($ident, $values)) {
                    $item['value'] = $values[$ident];
                }
            }
            unset($item);
        }
        
        return $settings;
    } // end loadSettings
    
    public function fetchOptionPageSettings()
    {
        if ($this->_isRefreshPlugin()) {
            $this->onRefreshPlugin();
        }
        
        if ($this->_isRefreshCompleted()) {
            $message = __(
                'Success refresh plugin',
                $this->_languageDomain
            );
            
            $this->displayUpdate($message);   
        }
        $this->_displayPluginErrors();
        
        echo $this->fetch('banner.phtml');
        
        //$this->displayOptionsHeader();
        
        $this->updateTheme();
        
        $currentTheme = $this->getCurrentTheme();
        
        $this->updateThemeOptions($currentTheme);

        $vars = array(
            'themes' => $this->getWizardThemeSettings(),
            'currentTheme' => $currentTheme,
        );
        
        $themsBlock =  $this->fetch('list_of_themes.phtml', $vars);

        $fieldsets = $this->getOptionsFieldSet($currentTheme);
        
        if (!$fieldsets) {
            return false;
        }
        $options = $this->getOptions('settings_'.$currentTheme);
        
        $vars = array(
            'fieldset' => $fieldsets,
            'currentValues' => $options,
            'currentThemeName' => $this->getWizardThemeSettings(
                $currentTheme,
                'name'
            )
        );
        
        $settingsBlock =  $this->fetch('settings_page.phtml', $vars);
        
        $vars = array(
            'themsBlock' => $themsBlock,
            'settingsBlock' => $settingsBlock,
        );
        
        echo $this->fetch('general_container.phtml', $vars);
    } // end fetchOptionPageSettings
    
    public function getSelectorClassForDisplayEvent($class)
    {
        $selector = $class.'-visible';
        
        $currentTheme = $this->getCurrentTheme();
        
        $options = $this->getOptions('settings_'.$currentTheme);
                
        if (!isset($options[$class]) || $options[$class] == 'disable') {
            $selector.=  ' festi-checkout-steps-wizard-hidden ';
        }
        
        return $selector;
    } // end getSelectorClassForDisplayEvent
    
    public function displayOptionsHeader()
    { 
        $vars = array(
            'content' => __(
                'Woocommerce Multistep Checkout Wizard Lite',
                $this->_languageDomain
            )
        );
        
        echo $this->fetch('options_header.phtml', $vars);
    } // end displayOptionsHeader
    
    private function _isRefreshCompleted()
    {
        return array_key_exists('refresh_completed', $_GET);
    } // end _isRefreshCompleted
    
    private function _displayPluginErrors()
    {        
        $caheFolderErorr = $this->_detectTheCacheFolderAccessErrors();

        if ($caheFolderErorr) {
            echo $this->fetch('refresh.phtml');
        }
    } // end _displayPluginErrors
    
    private function _detectTheCacheFolderAccessErrors()
    {
        if (!$this->_fileSystem->is_writable($this->_pluginCachePath)) {

            $message = __(
                "Caching does not work! ",
                $this->_languageDomain
            );
            
            $message .= __(
                "You don't have permission to access: ",
                $this->_languageDomain
            );
            
            $path = $this->_pluginCachePath;
            
            if (!$this->_fileSystem->exists($path)) {
                $path = $this->_pluginPath;
            }
            
            $message .= $path;
            //$message .= $this->fetch('manual_url.phtml');
            
            $this->displayError($message);
            
            return true;
        }
        
        return false;
    } // end _detectTheCacheFolderAccessErrors
    
    private function _isRefreshPlugin()
    {
        return array_key_exists('refresh_plugin', $_GET);
    } // end _isRefreshPlugin
    
    public function onRefreshPlugin()
    {
        $this->onInstall(true);
    } // end onRefreshPlugin
    
    public function updateThemeOptions($theme)
    {
        if ($this->isUpdateOptions('save')) {
            try {
                $this->_doUpdateOptions($_POST, $theme);
                           
                $this->displayOptionPageUpdateMessage(
                    'Success update settings'
                );               
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->displayError($message);
            }
        }
    } //end updateThemeOptions
    
    public function updateTheme()
    {
        if ($this->isUpdateOptions('changeTheme')) {
           try {
                $this->updateCurrentTheme();
                $this->installThemeOptions($_POST['themeSelector']);
                           
                $this->displayOptionPageUpdateMessage(
                    'Success update current theme'
                );               
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->displayError($message);
            }
        }
    } //end updateTheme
    
    public function updateCurrentTheme()
    {
        $value = array($_POST['themeSelector']);
        $options = $this->updateOptions('current_theme', $value);
    } //end updateCurrentTheme
    
    public function displayOptionPageUpdateMessage($text)
    {
        $message = __(
            $text,
            $this->_languageDomain
        );
            
        $this->displayUpdate($message);   
    } // end displayOptionPageUpdateMessage
    
    private function _doUpdateOptions($newSettings = array(), $theme)
    {
        $this->updateOptions('settings_'.$theme, $newSettings);
    } // end _doUpdateOptions
    
    public function isUpdateOptions($action)
    {
        return array_key_exists('__action', $_POST)
               && $_POST['__action'] == $action;
    } // end isUpdateOptions
}