<?php
/**
 * MultiPress RESTAPI
 *
 * PHP class for Multipress API
 *
 * @author Robin Migalski <robin@migalski.be>
 * @copyright 2016 Egberghs Printing Service NV
 * @link http://www.egberghs.be
 * @version 1.0.0
 */

/**
 * Multipress RESTAPI
 *
 * PHP class for Multipress API
 *
 * @author Robin Migalski <robin@migalski.be>
 * @copyright 2016 Egberghs Printing Service NV
 * @link http://www.egberghs.be
 * @version 1.0.0
 */
class MultiPress {

	protected $url = null;
	protected $port = null;
	protected $protocol = "http://";

	private $user = null;
	private $password = null;
	private $headers = null;

	public $system_info = array();
	public $INT_BUFFER = array(
		'type' => 2,
		'checklist' => 1,
		'standard_quotation' => 0,
		'run_01' => 1,
		'run_02' => 0,
		'run_03' => 0,
		'text_01' => "", //Description
		'text_02' => "",
		'text_03' => "",
		'text_04' => "",
		'text_05' => "",
		'text_06' => "",
		'text_07' => "",
		'text_08' => "",
		'text_09' => "",
		'text_10' => "",
		'text_11' => "",
		'text_12' => "",
		'text_13' => "",
		'web_job_nr' => 0,
		'holding' => "",
		'relation_number' => 0,
		'contact_name' => "",
		'company' => "",
		'phone' => "",
		'fax' => "",
		'email' => "",
		'zipcode' => "",
		'address_number' => "",
		'address' => "",
		'city' => "",
		'country' => "BELGIË",
		'country_code' => "BE",
		'extra_address_1' => "",
		'extra_address_2' => "",
		'extra_address_3' => "",
		'extra_address_4' => "",
		'extra_address_5' => "",
		'delivery_date' => "2016-01-08T00:00:00Z",
		'artwork_date' => "2016-01-08T00:00:00Z",
		'vat' => "6%",
		'price' => "0",
		'language_code' => "nl",
		'invoice_number' => null,
		'invoice_address' => "Standaard Boekhandel nv",
		'invoice_company' => "",
		'remark' => "",
		'files' => false,
		'payment_type' => "",
		'payment_remark' => "",
		'product_type' => "Copie_school",
		'product_number' => 101,
		'delivery' => array(
			"company" => "",
			"branch" => "",
			"address" => "",
			"address_number" => "",
			"zipcode" => "",
			"city" => "",
			"country" => "",
			"contact_name" => "",
			"phone" => "",
			"remark" => "",
			"email" => ""
		),
		'reference' => "",
		'autofill' => array(
		  	'pdflink' =>  2,
			'modelheight' => 297,
			'modelwidth' => 210,
			'pagesbody' => 8, //Pages total not body!
			'pagescover' => 4,
			'paperbody' => "condat silk wit 130 g/m²",
			'papercover' => "condat silk wit 300 g/m²",
			'colorbody' => "Recto: Cyaan; Magenta; Geel; Zwart\rVerso: Cyaan; Magenta; Geel; Zwart\r",
			'colorcover' => "Recto: Cyaan; Magenta; Geel; Zwart\rVerso: Cyaan; Magenta; Geel; Zwart\r",
			'bleed' => 3,
			'grain' => "",
			'production' => "Printen",
			'bindingtype' => "",
			'finishing' => ""
		)
	);

	/**
	 * __construct
	 *
	 * Connect to MP API and test login
	 * @param string $user Username used to login to the MP API.
	 * @param string $password Password used to login to the MP API.
	 * @param string $url Address of the MP server.
	 * @param int $port Portnumber.
	 * @param bool $ssl Ssl encryption on or off.
	 * @return True if successful
	 */
	public function __construct($user,$password,$url,$port,$ssl=false) {
       	$this->url = $url;
       	$this->port = $port;

       	if($ssl){
       		$this->protocol = "https://";
       	}else{
       		$this->protocol = "http://";
       	}

       	//Headers
       	$this->headers = array(
		  "authorization: Basic ".base64_encode($user.":".$password),
		  "cache-control: no-cache"
		);

       	//Test connaction
       	$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/system/getInfo");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,"GET");
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['program'])){
			$this->system_info = $r;
			return true;
		}else{
			throw new Exception('Can not connect to Multipress server.');
		}
   	}

   	/**
	 * fill_internet_buffer
	 *
	 * Add data to internet buffer.
	 * @param array $job_details Details of job like defined in $INT_BUFFER.
	 * @return True if successful
	 */
   	public function fill_internet_buffer($job_details){
   		foreach ($job_details as $key => $value) {
   			if(isset($this->INT_BUFFER[$key])){

   				//DATE
   				if( $key == "delivery_date" || $key == "artwork_date" ){
   					$value = date("Y-m-d\TH:i:s\Z",strtotime($value));
   				}

   				//AUTOFILL
   				if( $key == "autofill" ){

   					if(is_array($value)){

   						foreach ($value as $k1 => $v1) {

   							if(isset($this->INT_BUFFER[$key][$k1])){

   								$this->INT_BUFFER[$key][$k1] = $v1;

   							}else{

								throw new Exception($k1.' is not a valid autofill-option');

   							}

   						}

   						break;

   					}else{

   						throw new Exception($key.' has to be an array');

   					}

   				}

	   			$this->INT_BUFFER[$key] = $value;

   			}else{

				throw new Exception($key.' is not a valid job-option');

   			}

   		}

   		return true;
   	}


   	/**
	 * internet_order_add
	 *
	 * Write internet buffer to Multipress as an order.
	 * @return True if successful
	 */
   	public function internet_order_add(){

   		$fields = array(
		  'data' => json_encode($this->INT_BUFFER,JSON_UNESCAPED_UNICODE),
		  'id' => urlencode(0)
		);

   		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/internet/handleBufferLine");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,"PUT");
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);
		curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($fields));

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			$this->last_internet_id = $r['id'];
			return true;
		}

   	}

   	/**
	 * internet_order_update
	 *
	 * Update order in internet buffer.
	 * @param int $id Id of order in webbuffer.
	 * @return True if successful
	 */
   	public function internet_order_update($id){

   		$fields = array(
		  'data' => json_encode($this->INT_BUFFER,JSON_UNESCAPED_UNICODE),
		  'id' => urlencode($id)
		);

   		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/internet/handleBufferLine");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,"POST");
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);
		curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($fields));

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			$this->last_internet_id = $r['id'];
			return true;
		}

   	}

   	/**
	 * internet_order_remove
	 *
	 * Delete order line from the internet buffer in multipress.
	 * @param int $id Id of order in webbuffer.
	 * @return True if successful
	 */
   	public function internet_order_remove($id){


   		$fields = array(
		  'id' => urlencode($id)
		);

   		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/internet/handleBufferLine?id=".urlencode($id));
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,"DELETE");
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);
		curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($fields));

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			$this->last_internet_id = $r['id'];
			return true;
		}

   	}

   	/**
	 * internet_get_orders
	 *
	 * Get order from relation
	 * @param int $relation_id Relation id.
	 * @return Array with orders
	 */
   	public function internet_get_orders($relation_id){

   		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/internet/getBufferLines?relation_number=".urlencode($relation_id));
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r;
		}

   	}

   	/**
	 * get_autoFillAttributes
	 *
	 * Get order from relation
	 * @param int $relation_id Relation id.
	 * @return Array with autoFillAttributes
	 */
   	public function get_autoFillAttributes(){

   		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/internet/autoFillAttributes");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r;
		}

   	}
}

?>
