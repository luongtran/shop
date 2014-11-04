<?php

class WooCheckoutStepsLiteFrontendFestiPlugin
      extends WooCheckoutStepsLiteFestiPlugin
{
    protected $_currenteTheme = array();
    
    protected function onInit()
    {
        $this->onInitVariables();
        
        if (!$this->_hasEnablePluginOptionInSettings()) {
            return false;
        }

        $this->addActionListener(
            'woocommerce_before_checkout_form',
            'appendWizardBlockToCheckoutPage'
        );

        $this->addActionListener('wp_enqueue_scripts', 'onInitJsAction');
        
        $this->addActionListener('wp_print_styles', 'onInitCssAction');
        
        $this->addActionListener(
            'woocommerce_checkout_update_order_review',
            'removeRepeatingPaymentBlockAction'
        );
        
        $this->addActionListener(
            'wp_ajax_valid_post_code',
            'checkUserPostcodeAction'
        );
        
        $this->addActionListener(
            'wp_ajax_nopriv_valid_post_code',
            'checkUserPostcodeAction'
        );

        $this->addActionListener(
            'wp_ajax_login_user_wizard_step',
            'authorizeUserAction'
        );
        
        $this->addActionListener(
            'wp_ajax_nopriv_login_user_wizard_step',
            'authorizeUserAction'
        );
    } // end onInit
    
    private function _hasEnablePluginOptionInSettings()
    {
         return array_key_exists('enablePlugin', $this->_themeSettings);
    } // end _hasEnablePluginOptionInSettings 

    public function onInitVariables()
    {
        $this->_currenteTheme = $this->getCurrentTheme();
        $this->_themeSettings = $this->getOptions(
            'settings_'.$this->_currenteTheme
        );
    } //end onInitVariables
    
    public function removeRepeatingPaymentBlockAction()
    {
        if ($this->_isDisabledPaymentStep()) {
            return false;
        }
        
        echo $this->fetch("remove_repeating_payment_block.phtml");
    } // end removeRepeatingPaymentBlockAction
    
    private function _isDisabledPaymentStep()
    {
        $theme = $this->_currenteTheme;
        
        $settings = $this->_themeSettings;
        
        return $this->_hasOptionInSettings($settings, 'disableSteps')
               && in_array('payment', $settings['disableSteps']);
    } //end _isDisabledPaymentStep
    
    private function _hasOptionInSettings($settings, $option)
    {
        return array_key_exists($option, $settings);
    } // end _hasOptionInSettings
        
    public function checkUserPostcodeAction()
    {
       $country = $_POST['country'];
       
       $postCode = $_POST['postCode'];
        
       echo WC_Validation::is_postcode($postCode, $country);
       
       exit();
    } // end checkUserPostcodeAction
     
    public function authorizeUserAction()
    {
        $userData = array(
            'user_login'    => $_POST['username'],
            'user_password' => $_POST['password'],
            'remember'      => $_POST['rememberme']
        );
        
        $result = wp_signon($userData, false);
        
        if ($this->_isUnsuccessfulAuthentication($result)) {
            $message = $result->get_error_message();
            echo $this->fetchLoginErrorMessage($message);
        } else {
            echo 'successfully';
        }
        exit();
    } // end authorizeUserAction
    
    private function _isUnsuccessfulAuthentication($result)
    {
        return is_wp_error($result);
    } // end _isUnsuccessfulAuthentication
    
    public function appendWizardBlockToCheckoutPage()
    {
        $theme = $this->_currenteTheme;
        
        $settings = $this->_themeSettings;

        $steps = array(
            'login' => array(
                'name' => __('Login', $this->_languageDomain),
                'class' => 'festi-wizard-step-login'
            ),
            'billing' => array(
                'name' => __('Billing Details', $this->_languageDomain),
                'class' => 'festi-wizard-step-billing'
            ),
            'shipping' => array(
                'name' => __('Ship Details', $this->_languageDomain),
                'class' => 'festi-wizard-step-shipping'
            ),
            'reviewOrder' => array(
                'name' => __('View Order', $this->_languageDomain),
                'class' => 'festi-wizard-step-view-order',
            ),
            'payment' => array(
                'name' => __('Payment', $this->_languageDomain),
                'class' => 'festi-wizard-step-payment',
            ),
        );

        $permissibleSteps = array();

        foreach ($steps as $key => $step) {
            if (!$this->_isDesabledStep($settings, $key)) {
                $newKey = $this->getElementOrdinalNumberInArray(
                    $key,
                    $settings['sortSteps']
                );
                $permissibleSteps[$newKey] = $step;
            }
        }

        ksort($permissibleSteps);

        $vars = array(
            'steps' => $permissibleSteps,
            'count' => count($permissibleSteps)
        );
        
        echo $this->fetch('wizard_content.phtml', $vars);
    } // end appendWizardBlockToCheckoutPage
    
    public function getElementOrdinalNumberInArray($element, $array)
    {
        $n = -1;
        
        if ($element == 'login') {
            return $n;
        }
        
        foreach ($array as $key => $value) {
            $n++;
            if ($key == $element) {
                return $n;
            }
        }
    } //end getElementOrdinalNumberInArray

    private function _isDesabledStep($settings, $step)
    {
        if ($this->_isLoginStep($step) && $this->isAuthorizedUser()) {
            return true;
        }
        
        return false;
    } // end _isDesabledStep
    
    private function _isLoginStep($step)
    {
        return $step == 'login';
    } //end _isLoginStep

    public function fetchLoginErrorMessage($message = '')
    {
        $vars = array(
           'message' => $message
        );
        
        return $this->fetch('login_error_message.phtml', $vars);
    } // end fetchErrorMessage
    
    public function getPluginCssUrl($path) 
    {
        return $this->_pluginUrl.$path;
    } // end getPluginCssUrl
    
    public function getPluginJsUrl($fileName)
    {
        return $this->_pluginJsUrl.'frontend/'.$fileName;
    } // end getPluginJsUrl
    
    public function getPluginTemplatePath($fileName)
    {
        return $this->_pluginTemplatePath.'frontend/'.$fileName;
    } // end getPluginTemplatePath
    
    public function onInitJsAction()
    {
        if (!is_checkout()) {
            return false;
        }
        
        $this->onEnqueueJsFileAction('jquery');
        $this->onEnqueueJsFileAction(
            'festi-jquery-steps',
            'jquery.steps.min.js',
            'jquery',
            $this->_version
        );
        
        $this->onEnqueueJsFileAction(
            'festi-checkout-steps-collect_wizard',
            'collect_wizard.js',
            'festi-checkout-steps-general',
            $this->_version
        );
        
        $this->onEnqueueJsFileAction(
            'festi-checkout-steps-general',
            'general.js',
            'wc-checkout',
            $this->_version
        );
        
        $this->onEnqueueJsFileAction(
            'festi-jquery-steps',
            'jquery.steps.min.js',
            'jquery',
            $this->_version
        );
 
        $settings = $this->getOptions('settings_'.$this->_currenteTheme);
        
        $vars = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'isAuthorizedUser' => $this->isAuthorizedUser(),
            'nextButton' => $settings['nextButtonName'],
            'previousButton' => $settings['previousButtonName'],
            'finishButton' => $settings['finishButtonName'],
            'noAccountButton' => $settings['noAccountButtonName'],
            'titleTemplate' => $this->getTabsTitleTemplate(),
            'termsLocation' => $settings['termsAndConditionsLocation']
        );

        wp_localize_script(
            'festi-checkout-steps-general',
            'fesiCheckoutSteps',
            $vars
        );
    } // end onInitJsAction
    
    public function getTabsTitleTemplate()
    {
        $path =  $this->getThemeFolderPath($this->_currenteTheme);

        if (!file_exists($path)) {
            echo "Theme folder is not found: ".$path;
            return false;
        }
        
        $templatePath = $path.'tab_title_template.phtml';
        
        if (!file_exists($templatePath)) {
            $message = "Theme tabs title template file ".$templatePath;
            $message .= " is not found.";
            echo $message;
            return false;
        }
        
        $content =  $this->fetchThemeContent($templatePath);
        
        return $content;
    } //end getTabsTitleTemplate
    
    public function getAdditionalFieldsFromCheckoutFieldEditor()
    {
        $fields = get_option('wc_fields_additional');
        
        $fields = array_keys($fields);
        
        return $fields;
    } //end getAdditionalFieldsFromCheckoutFieldEditor
    
    public function onInitCssAction()
    {
        if (!is_checkout()) {
            return false;
        }

        $this->onEnqueueCssFileAction(
            'festi-checkout-steps-styles',
            'static/styles/frontend/style.css',
            array(),
            $this->_version
        );
        
        $path = 'themes/'.$this->_currenteTheme.'/style.css';
        
        $this->onEnqueueCssFileAction(
            'festi-jquery-steps',
            $path,
            array(),
            $this->_version
        );
    } // end onInitCssAction
    
    public function appendCssToHeaderForWizardCustomize()
    {
        $theme = $this->_currenteTheme;
        
        $themePath = $this->getThemeFolderPath($theme);
        $themePath .= 'customize.phtml';
        
        if (!file_exists($themePath)) {
            return false;
        }
        
        $vars = array(
            'settings' => $this->_themeSettings
        );

        echo $this->fetchThemeContent($themePath, $vars);
    } // end appendCssToHeaderForWizardCustomize
    
    public function getCurrentThemeStyle($theme)
    {
        return $this->getWizardThemeSettings($theme, 'file');
    } //end getCurrentThemeStyle
    
    public function isAuthorizedUser()
    {
        return get_current_user_id();
    } // end isAuthorizedUser
    
    public function fetchThemeContent($path, $vars = array()) 
    {
        if ($vars) {
            extract($vars);
        }

        ob_start();

        include $path;

        $content = ob_get_clean();    
        
        return $content;                
    } // end fetchThemeContent
}