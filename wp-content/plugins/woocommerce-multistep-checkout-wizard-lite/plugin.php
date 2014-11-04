<?php
/**
 * Plugin Name: WooCommerce MultiStep Checkout Wizard Lite
 * Plugin URI: http://wordpress.org/plugins/woocommerce-multistep-checkout-wizard-lite/
 * Description:  With MultiStep Checkout Wizard Lite you can change default WooCommerce Checkout page to the modern and easy step-by-step checkout.
 * Version: 1.1
 * Author: Festi 
 * Author URI: http://festi.io/
 */

if (!class_exists('FestiPlugin')) {
    require_once dirname(__FILE__).'/common/FestiPlugin.php';
}

class WooCheckoutStepsLiteFestiPlugin extends FestiPlugin
{
    protected $_languageDomain = 'festi_checkout_steps';
    protected $_optionsPrefix = 'festi_checkout_steps';
    protected $_wizardThemes = array(
        'blue',
    );
    protected $_version = '1.1';
    
    protected $_defaultTheme = 'blue';
     
    protected function onInit()
    {        
        $this->addActionListener('plugins_loaded', 'onLanguagesInitAction');
       
        if ($this->_isWoocommercePluginNotActiveWhenFestiCartPluginActive()) {
            $this->addActionListener(
                'admin_notices',
                'onDisplayInfoAboutDisabledWoocommerceAction' 
            );
            
            return false;
        }
       
        parent::onInit();
    } // end onInit
    
    public function getThemeFolderPath($theme = '')
    {
        return $this->_pluginPath.'themes/'.$theme.'/';
    } // end getThemeFolderPath
    
    public function onInstall()
    {
        if (!$this->_isWoocommercePluginActive()) {
            $message = 'WooCommerce not active or not installed.';
            $this->displayError($message);
            exit();
        } 
                
        $plugin = $this->onBackendInit();
        
        $plugin->onInstall();
    } // end onInstall
    
    public function onUninstall()
    { 
        $plugin = $this->onBackendInit();
        
        $plugin->onUninstall();
    } // end onInstall
    
    public function onLanguagesInitAction()
    {
        load_plugin_textdomain(
            $this->_languageDomain,
            false,
            $this->_pluginLanguagesPath
        );
    } // end onLanguagesInitAction
    
    protected function onFrontendInit()
    {
        $filePath = 'common/WooCheckoutStepsLiteFrontendFestiPlugin.php';
        require_once $this->_pluginPath.$filePath;
        $frontend = new WooCheckoutStepsLiteFrontendFestiPlugin(__FILE__);
        return $frontend;
    } // end onFrontendInit
    
    protected function onBackendInit()
    {
        $filePath = 'common/WooCheckoutStepsLiteBackendFestiPlugin.php';
        require_once $this->_pluginPath.$filePath;
        $backend = new WooCheckoutStepsLiteBackendFestiPlugin(__FILE__);
        return $backend;
    } // end onFrontendInit
    
    public function onDisplayInfoAboutDisabledWoocommerceAction()
    {
        $message = 'Woocommerce Checkout Steps Wizard: ';
        $message .= 'WooCommerce not active or not installed.';
        $this->displayError($message);
    } //end onDisplayInfoAboutDisabledWoocommerceAction
    
    private function _isWoocommercePluginNotActiveWhenFestiCartPluginActive()
    {
        return $this->_isFestiCheckoutStepsPluginActive()
               && !$this->_isWoocommercePluginActive();
    } // end _isWoocommercePluginNotActiveWhenFestiCartPluginActive
    
    private function _isFestiCheckoutStepsPluginActive()
    {
        $path = 'woocommerce-multistep-checkout-wizard-lite/plugin.php';
        return $this->isPluginActive($path);
    } // end _isFestiCheckoutStepsPluginActive
    
    private function _isWoocommercePluginActive()
    {        
        return $this->isPluginActive('woocommerce/woocommerce.php');
    } // end _isWoocommercePluginActive
    
    public function isWoocommerceCheckoutFieldEditorPluginActive()
    {        
        return $this->isPluginActive(
            'woocommerce-checkout-field-editor/checkout-field-editor.php'
        );
    } // end isWoocommerceCheckoutFieldEditorPluginActive
    
    public function getCurrentTheme()
    {
        $options = $this->getOptions('current_theme');
        
        if (!$options) {
            return false;
        }
        
        return $options[0];
    } // end getCurrentTheme
    
    public function getWizardThemeSettings($theme = '', $attr = false)
    {
        $themes = array();
        
        foreach ($this->_wizardThemes as $value) {
            $path =  $this->getThemeFolderPath($value);
            if (!file_exists($path)) {
                echo "Theme folder is not found: ".$path;
                return false;
            }
            
            $themeSettingsPath = $path.'settings.php';
            if (!file_exists($themeSettingsPath)) {
                $message = "Theme configuration file ".$themeSettingsPath;
                $message .= " is not found.";
                echo $message;
                return false;
            }
                     
            $themes[$value] = include($themeSettingsPath);
        }

        if (!$theme) {
            return $themes;
        }
        
        if (!$attr) {
            return $themes[$theme]; 
        }
        
        if (!$this->_hasAttributInSettings($themes[$theme], $attr)) {
           return false; 
        }

        return $themes[$theme][$attr];
    } // end getWizardThemeSettings
    
    private function _hasAttributInSettings($settings, $attr)
    {
        return array_key_exists($attr, $settings);
    } // _hasAttributInSettings
}
$className = 'WooCheckoutStepsLiteFestiPlugin';
$GLOBALS[$className] = new WooCheckoutStepsLiteFestiPlugin(__FILE__); 
