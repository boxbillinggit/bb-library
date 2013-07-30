<?php
class Payment_Adapter_Transferuj extends Payment_AdapterAbstract
{
	
	private $config = array();
    
    public function __construct($config)
    {
        $this->config = $config;
    }
	
    public function init()
    {
        if(!$this->getParam('id_sprzedawcy')) {
            throw new Payment_Exception('Transferuj.pl nie jest skonfigurowane, zrób to teraz "Configuration -> Payments".');
        }
        
        if (!$this->getParam('jezyk')) {
        	throw new Payment_Exception('Transferuj.pl nie jest skonfigurowane, zrób to teraz "Configuration -> Payments".');
        }
    }
    
    public static function getConfig()
    {
        return array(
            'supports_one_time_payments'   =>  true,
            'supports_subscriptions'     =>  false,
            'description'     =>  'Clients will be redirected to Transferuj.pl to make payment.',
            'form'  => array(
                'id_sprzedawcy' => array('text', array(
                            'label' => 'Transferuj.pl Id Sprzedawcy', 
                            'description' => 'Transferuj.pl id sprzedawcy', 
                            'validators'=>array('notempty'),
                    ),
                 ),
                 'jezyk' => array('text', array(
                 			'label' => 'Jezyk',
                 			'description' => 'Język płatności',
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
			'kwota'					=>	$invoice['total'],
			'opis'					=>  '',
			'crc'					=>  'BoxBilling',
			'wyn_url'				=>	$this->config['notify_url'],
			'wyn_email'				=>	$this->config['email'],
			'opis_sprzed'			=>  $invoice['nr'],
			'pow_url'				=>	$this->config['return_url'],
			'pow_url_blad'			=>  $this->config['return_url'],
			'email'					=>  $invoice['buyer']['email'],
			'transaction_id'		=>	$invoice['nr'],
			'pow_url'				=>	$this->config['return_url'],
			'cancel_url'			=>	$this->config['cancel_url'],
			'imie'					=>	$invoice['buyer']['first_name'],
			'nazwisko'				=>	$invoice['buyer']['last_name'],
			'adres'					=>	$invoice['buyer']['address'],
			'telefon'				=>	$invoice['buyer']['phone'],
			'miasto'				=>	$invoice['buyer']['city'],
			'kod'					=>	$invoice['buyer']['zip'],
			'kraj'					=>	$invoice['buyer']['country'],
			'jezyk'					=>	$this->config['jezyk'],
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
		return 'https://secure.transferuj.pl';
    }

	public function singlePayment(Payment_Invoice $invoice) {
		$c = $invoice->getBuyer();
		$params = array(
			'id' 					=>  $this->getParam('id_sprzedawcy'),
			'kwota'					=>	$invoice->getTotal(),
			'opis'					=>  '',
			'crc'					=>  'BoxBilling',
			'wyn_url'				=>	$this->getParam('notify_url'),
			'wyn_email'				=>	$this->getParam('email'),
			'opis_sprzed'			=>  $invoice->getNumber(),
			'pow_url'				=>	$this->getParam('return_url'),
			'pow_url_blad'			=>  $this->getParam('return_url'),
			'email'					=>  $c->getEmail(),
			'transaction_id'		=>	$invoice->getNumber(),
			'pow_url'				=>	$this->getParam('return_url'),
			'cancel_url'			=>	$this->getParam('cancel_url'),
			'imie'					=>	$c->getFirstname(),
			'nazwisko'				=>	$c->getLastname(),
			'adres'					=>	$c->getAddress(),
			'telefon'				=>	$c->getPhone(),
			'miasto'				=>	$c->getCity(),
			'kod'					=>	$c->getZip(),
			'kraj'					=>	$c->getCountry(),
			'jezyk'					=>	$this->getParam('jezyk'),
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
		
		if($_SERVER['REMOTE_ADDR']=='195.149.229.109' && !empty($_POST)){
		$id_sprzedawcy = $r['id'];
		$status_transakcji = $r['tr_status'];
		$id_transakcji = $r['tr_id'];
		$kwota_transakcji = $r['tr_amount'];
		$kwota_zaplacona = $r['tr_paid'];
		$blad = $r['tr_error'];
		$data_transakcji = $r['tr_date'];
		$opis_transakcji = $r['tr_desc'];
		$ciag_pomocniczy = $r['tr_crc'];
		$email_klienta = $r['tr_email'];
		$suma_kontrolna = $r['md5sum'];
		// sprawdzenie stanu transakcji
		if($status_transakcji=='TRUE' && $blad=='none'){
            $tr->setStatus($r['trade_status']);
            $tr->setType(Payment_Transaction::TXTYPE_PAYMENT);
			$tr->setStatus(Payment_Transaction::STATUS_COMPLETE);
            $tr->setIsValid(true);
		}
		else
		{
		// transakcja wykonana niepoprawnie
		}
		}
		
		return $tr;
	}

	
	
}