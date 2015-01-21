<?php 

require_once 'IAdapter.php';


abstract class BarclaycardCw_Adapter_AbstractAdapter implements BarclaycardCw_Adapter_IAdapter {
	
	/**
	 * @var Customweb_Payment_Authorization_IAdapter
	 */
	private $interfaceAdapter;
	
	
	public function setInterfaceAdapter(Customweb_Payment_Authorization_IAdapter $interface) {
		$this->interfaceAdapter = $interface;
	}
	
	public function getInterfaceAdapter() {
		return $this->interfaceAdapter;
	}
	
}