<?php 
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2013 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.customweb.ch/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.customweb.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

require_once 'Customweb/Barclaycard/Configuration.php';
require_once 'Customweb/Barclaycard/IAdapter.php';
require_once 'Customweb/Barclaycard/Util.php';
require_once 'Customweb/Http/Request.php';
require_once 'Customweb/Barclaycard/Update/PullRequestParameterBuilder.php';


class Customweb_Barclaycard_Update_PullRequest {
	
	/**
	 * @var Customweb_Barclaycard_Authorization_Transaction
	 */
	private $transaction = null;
	
	/**
	 * @var Customweb_DependencyInjection_IContainer
	 */
	private $container = null;
	
	/**
	 * @var Customweb_Barclaycard_Configuration
	 */
	private $configuration = null;
	
	public function __construct(Customweb_Barclaycard_Authorization_Transaction $transaction, Customweb_DependencyInjection_IContainer $container) {
		$this->transaction = $transaction;
		$this->container = $container;
		$this->configuration = new Customweb_Barclaycard_Configuration($this->container->getBean('Customweb_Payment_IConfigurationAdapter'));
	}
	
	/**
	 * This method sends a request to the remote server and pull
	 * the status of the given transaction.
	 * 
	 * @return array Map of response parameters
	 */
	public function pull() {
		$builder = new Customweb_Barclaycard_Update_PullRequestParameterBuilder($this->transaction, $this->container);
		$url = $this->configuration->getBaseEndPointUrl() . Customweb_Barclaycard_IAdapter::URL_QUERY_ORDER;
		$request = new Customweb_Http_Request($url);
		$response = $request->setMethod("POST")->setConnectionTimeout(15)->setBody($builder->buildParameters())->send();
		return Customweb_Barclaycard_Util::getXmlAttributes($response->getBody());
	}
}