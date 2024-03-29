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

require_once 'Customweb/Payment/Authorization/ITransaction.php';

/**
 * This interface defines the interaction with the capturing service. The capturing service 
 * may change the transaction object. Hence it must be stored after the invokation.
 * 
 * The state of the transaction is change during the processing accordingly to the input parameters and the 
 * result of the request.
 * 
 * A transaction may be captured partially and leave it open for further partial captures. 
 * 
 * In case this interface is implemented by a store, the store must ensure, that the transaction 
 * is stored during the execution of the methods.
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_Payment_BackendOperation_Adapter_ICaptureAdapter {
	
	/**
	 * The invocation of this method capture the given transaction (whole amount). 
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction The transaction object on which a capture should be executed.
	 * @throws Exception In case the capturing fails, this method may throw an exception.
	 * @return void
	 */
	public function capture(Customweb_Payment_Authorization_ITransaction $transaction);
	
	/**
	 * A partial capture enables to capture only certain items form the whole order. The captured amount corresponds with 
	 * the sum of all items.
	 * 
	 * Each item of the list must correspond to a item in the order context from the 
	 * transaction. The match is done by the SKU of the item. Hence the SKU should 
	 * not be changed.
	 * 
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $items
	 * @param boolean $close
	 * @throws Exception In case the capturing fails, this method may throw an exception.
	 * @return void
	 */
	public function partialCapture(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close);
	
}