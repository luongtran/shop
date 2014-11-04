<?php
$settings = array(
    'enablePlugin' => array(
        'caption' => __('Enable Plugin', $this->_languageDomain),
        'type' => 'input_checkbox',
        'lable' => __('available in Lite version', $this->_languageDomain),
        'fieldsetKey' => 'general',
        'default' => 1
    ),
    'disableSteps' => array(
        'caption' => __('Disable Steps', $this->_languageDomain),
        'type'    => 'multi_select',
        'fieldsetKey' => 'general',
        'values' => array(
            'login' => __('Login', $this->_languageDomain),
            'shipping' => __('Shipping', $this->_languageDomain),
            'reviewOrder' => __('Review Order', $this->_languageDomain),
            'payment' => __('Payment', $this->_languageDomain)
        ),
        'default' => array()
    ),
    'sortSteps' => array(
        'caption' => __('Sort Steps', $this->_languageDomain),
        'type'    => 'sortable_select',
        'fieldsetKey' => 'general',
        'values' => array(
            1 => array(
                'step' => 'billing',
                'caption' => __('Billing', $this->_languageDomain),
            ),
            2 => array(
                'step' => 'shipping',
                'caption' => __('Shipping', $this->_languageDomain),
            ),
            3 => array(
                'step' => 'reviewOrder',
                'caption' => __('Review Order', $this->_languageDomain),
            ),
            4 => array(
                'step' => 'payment',
                'caption' => __('Payment', $this->_languageDomain)
            ),
            5 => array(
                'step' => 'custom',
                'caption' => __('Custom', $this->_languageDomain),
            ),
        ),
        'default' => array(
            'billing' => 1,
            'shipping' => 2,
            'reviewOrder' => 3,
            'payment' => 4,
        )
    ),
    'termsAndConditionsDivider' => array(
        'caption' => __(
            'Terms and Conditions',
            $this->_languageDomain
        ),
        'type'    => 'divider',
        'fieldsetKey' => 'general',
    ),
    'termsAndConditionsLocation' => array(
        'caption' => __('Location', $this->_languageDomain),
        'type'    => 'input_select',
        'fieldsetKey' => 'general',
        'values' => array(
            'billing' => __('Billing', $this->_languageDomain),
            'shipping' => __('Shipping', $this->_languageDomain),
            'view-order' => __('Review Order', $this->_languageDomain),
            'payment' => __('Payment', $this->_languageDomain)
        ),
        'default' => 'payment'
    ),
    'termsAndConditionsPosition' => array(
        'caption' => __('Position', $this->_languageDomain),
        'type'    => 'input_select',
        'fieldsetKey' => 'general',
        'values' => array(
            'left' => __('Left', $this->_languageDomain),
            'right' => __('Right', $this->_languageDomain),
        ),
        'default' => 'right'
    ),
    'enableCustomStep' => array(
        'caption' => __('Enable Custom Step', $this->_languageDomain),
        'hint' => __(
            'In step will be displayed fields which created with the plugin'.
            ' WooCommerce Checkout Field Editor, see Additional Fields',
            $this->_languageDomain
        ),
        'type'    => 'input_checkbox',
        'fieldsetKey' => 'general',
        'conditionForDisplay' => 'isWoocommerceCheckoutFieldEditorPluginActive',
        'event' => 'visible'
    ),
    /*
    'customStepPosition' => array(
        'caption' => __('Custom Step Position', $this->_languageDomain),
        'type'    => 'input_select',
        'fieldsetKey' => 'general',
        'conditionForDisplay' => 'isWoocommerceCheckoutFieldEditorPluginActive',
        'values' => array(
            'first' => __('First', $this->_languageDomain),
            'login' => __('After Login', $this->_languageDomain),
            'billing' => __('After Billing', $this->_languageDomain),
            'shipping' => __('After Shipping', $this->_languageDomain),
            'reviewOrder' => __('After Review Order', $this->_languageDomain),
            'payment' => __('After Payment', $this->_languageDomain)
        ),
        'eventClasses' => 'enableCustomStep',
        'default' => 'first'
    ),
     */
    'tabsTitlesDivider' => array(
        'caption' => __(
            'Tabs Titles',
            $this->_languageDomain
        ),
        'type'    => 'divider',
        'fieldsetKey' => 'general',
    ),
    'customStepName' => array(
        'caption' => __('Custom Step', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'conditionForDisplay' => 'isWoocommerceCheckoutFieldEditorPluginActive',
        'eventClasses' => 'enableCustomStep',
        'default' => __('Custom', $this->_languageDomain)
    ),
    'loginStepName' => array(
        'caption' => __('Login Step', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('LogIn User', $this->_languageDomain)
    ),
    'bilingStepName' => array(
        'caption' => __('Billing Step', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('Billing Details', $this->_languageDomain)
    ),
    'shippingStepName' => array(
        'caption' => __('Shipping Step', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('Ship Details', $this->_languageDomain)
    ),
    'reviewOrderStepName' => array(
        'caption' => __('Review Order Step', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('View order', $this->_languageDomain)
    ),
    'paymentStepName' => array(
        'caption' => __('Payment Step', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('Payment', $this->_languageDomain)
    ),
    'actionsButtonsTitlesDivider' => array(
        'caption' => __(
            'Actions Buttons Titles',
            $this->_languageDomain
        ),
        'type'    => 'divider',
        'fieldsetKey' => 'general',
    ),
    'nextButtonName' => array(
        'caption' => __('Next', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('Next', $this->_languageDomain)
    ),
    'previousButtonName' => array(
        'caption' => __('Previous', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('Previous', $this->_languageDomain)
    ),
    'finishButtonName' => array(
        'caption' => __('Finish', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __('Finish', $this->_languageDomain)
    ),
    'noAccountButtonName' => array(
        'caption' => __('No Account', $this->_languageDomain),
        'type'    => 'input_text',
        'fieldsetKey' => 'general',
        'default' => __("I don't have an account", $this->_languageDomain)
    ),
    
);

return $settings;