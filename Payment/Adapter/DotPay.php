<?php
class Payment_Adapter_Dotpay extends Payment_AdapterAbstract
{
	
	private $config = array();
    
    public function __construct($config)
    {
        $this->config = $config;
    }
	
    public function init()
    {
        if(!$this->getParam('id_sprzedawcy')) {
            throw new Payment_Exception('Dotpay.pl nie jest skonfigurowane, zrób to teraz "Configuration -> Payments".');
        }
        
        if (!$this->getParam('pin')) {
        	throw new Payment_Exception('Dotpay.pl nie jest skonfigurowane - uzupełnij PIN, zrób to teraz "Configuration -> Payments".');
        }
    }
    
    public static function getConfig()
    {
        return array(
            'supports_one_time_payments'   =>  true,
            'supports_subscriptions'     =>  false,
            'description'     =>  'Clients will be redirected to Dotpay.pl to make payment.',
            'form'  => array(
                'id_sprzedawcy' => array('text', array(
                            'label' => 'Dotpay.pl Id Sprzedawcy', 
                            'description' => 'Dotpay.pl id sprzedawcy', 
                            'validators'=>array('notempty'),
                    ),
                 ),
                 'Pin' => array('text', array(
                 			'label' => 'PIN',
                 			'description' => 'Pin dla płatności',
                 			'validators' => array('notempty'),
                 	),
                 ),
            ),
        );
    }
    
    /**
     * Return payment gateway type
     * @return string
     */
	 
	 public function getHtml($api_admin, $invoice_id, $subscription) {
		 $invoice = $api_admin->invoice_get(array('id'=>$invoice_id));
		 
		 $params = array(
			'id' 					=>  $this->config['id_sprzedawcy'],
			'amount'					=>	$invoice['total'],
			'URL'				=>	$this->config['notify_url'],
			'description'			=>  $invoice['nr'],
			'URLC'				=>	$this->config['return_url'],
			'email'					=>  $invoice['buyer']['email'],
			'control'		=>	$invoice['nr'],
			'firstname'					=>	$invoice['buyer']['first_name'],
			'lastname'				=>	$invoice['buyer']['last_name'],
			'street'					=>	$invoice['buyer']['address'],
			'phone'				=>	$invoice['buyer']['phone'],
			'city'				=>	$invoice['buyer']['city'],
			'postcode'					=>	$invoice['buyer']['zip'],
			'country'					=>	$invoice['buyer']['country'],
		);
		
		  $html = '
            <form action="'.$this->getServiceUrl().'" method=POST>
                ';
				
				foreach($params as $key => $value) {
					$html .='<input type=hidden name="'.$key.'"  value="'.$value.'">
					';
				}
				
				$html .='
                
    
	
                <table>   
                  <tfoot>
                    <tr>
                        <td colspan=2>
                            <input type=SUBMIT value="'.__('Pay now').'" name=SUBMIT class="bb-button bb-button-submit bb-button-big">
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </form>
        ';
        
        return $html;
	 }
	 

    /**
     * Return payment gateway type
     * @return string
     */
    public function getServiceUrl()
    {
		return 'https://ssl.dotpay.pl';
    }

	public function singlePayment(Payment_Invoice $invoice) {
		$c = $invoice->getBuyer();
		$params = array(
			'id' 					=>  $this->getParam('id_sprzedawcy'),
			'amount'					=>	$invoice->getTotal(),
			'URL'				=>	$this->getParam('notify_url'),
			'email'				=>	$this->getParam('email'),
			'description'			=>  $invoice->getNumber(),
			'URLC'				=>	$this->getParam('return_url'),
			'control'		=>	$invoice->getNumber(),
			'firstname'					=>	$c->getFirstname(),
			'lastname'				=>	$c->getLastname(),
			'street'					=>	$c->getAddress(),
			'phone'				=>	$c->getPhone(),
			'city'				=>	$c->getCity(),
			'postcode'					=>	$c->getZip(),
			'country'					=>	$c->getCountry(),
		);
		
		return $params;
		//return $this->_generateForm($this->getServiceUrl(), $params);
	}

	public function recurrentPayment(Payment_Invoice $invoice) {
		// TODO Auto-generated method stub
		
	}

	public function getTransaction($data, Payment_Invoice $invoice) {
		$r = $data['post'];
		
		$tr = new Payment_Transaction();
		$tr->setAmount($r['mb_amount'])
		   ->setCurrency($r['currency']);
		
		if($_SERVER['REMOTE_ADDR']='217.17.41.5'&&$_SERVER['REMOTE_ADDR']='195.150.9.37') && !empty($_POST)){
		$id_sprzedawcy = $r['id'];
		$status_transakcji = $r['status'];
		$id_transakcji = $r['t_id'];
		$kwota_transakcji = $r['amount'];
		// sprawdzenie stanu transakcji
		if($status_transakcji=='OK'){
            $tr->setStatus($r['trade_status']);
            $tr->setType(Payment_Transaction::TXTYPE_PAYMENT);
			$tr->setStatus(Payment_Transaction::STATUS_COMPLETE);
            $tr->setIsValid(true);
		}
		else
		{
		if($_SERVER['REMOTE_ADDR']!='217.17.41.5'&&$_SERVER['REMOTE_ADDR']!='195.150.9.37') die("Incorrect sender IP");
		}
		}
		
		return $tr;
	}

	
	
}
?>