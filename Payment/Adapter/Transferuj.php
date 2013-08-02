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
            'supports_subscriptions'     =>  true,
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
		 $buyer = $invoice['buyer'];
		 
		 $params = array(
			'id' 					=>  $this->config['id_sprzedawcy'],
			'kwota'					=>	$invoice['total'],
			'opis'					=>  $invoice['nr'],
			/*'crc'					=>  'BoxBilling',*/
			'wyn_url'				=>	$this->config['notify_url'],
			'wyn_email'				=>	$buyer['email'],
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

	public function recurrentPayment(Payment_Invoice $invoice) {
		// TODO Auto-generated method stub
		
	}
	
	public function isIpnValid($data, Payment_Invoice $invoice)
    {
		return true;	
	}
	
public function getTransaction($data, Payment_Invoice $invoice) 
	{
    
		$r = $_POST;
		
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
		
		$tx = new Payment_Transaction();
        $tx->setId($r['tr_id']);
        $tx->setAmount($r['tr_amount']);
        $tx->setCurrency("PLN");
        $tx->setStatus(Payment_Transaction::STATUS_COMPLETE);
        $tx->setType(Payment_Transaction::TXTYPE_PAYMENT);
        return $tx;
			
		}
		else
		{
		// transakcja wykonana niepoprawnie
		
		}
		}
		
		
	}
	
}