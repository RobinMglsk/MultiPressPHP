<?php

namespace RobinMglsk;

use Exceptions;

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
class MultiPressPHP {

	protected $url = null;
	protected $port = null;
	protected $protocol = "http://";

	private $user = null;
	private $password = null;
	private $headers = null;

	public $system_info = [];
	public $INT_BUFFER = [
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
		'invoice_address' => "",
		'invoice_company' => "",
		'remark' => "",
		'files' => false,
		'payment_type' => "",
		'payment_remark' => "",
		'product_type' => "Copie_school",
		'product_number' => 101,
		'delivery' => [
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
		],
		'reference' => "",
		'autofill' => [
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
		]
	];

	/**
	 * __construct
	 *
	 * Connect to MP API and test login
	 * @param string $user Username used to login to the MP API.
	 * @param string $password Password used to login to the MP API.
	 * @param string $url Address of the MP server.
	 * @param int $port Portnumber.
	 * @param bool $ssl Ssl encryption on or off.
	 * @return bool True if successful
	 */
	public function __construct($user,$password,$url,$port,$ssl=false)
	{
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
	 * @return bool True if successful
	 */
	public function fill_internet_buffer($job_details)
	{
   		foreach ($job_details as $key => $value) {
   			if(array_key_exists($key, $this->INT_BUFFER)){

   				//DATE
   				if( $key == "delivery_date" || $key == "artwork_date" ){
   					$value = self::convertToDate(strtotime($value));
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
	 * @return bool True if successful
	 */
	   public function internet_order_add()
	   {

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
			return $this->last_internet_id = $r['id'];
		}

   	}

   	/**
	 * internet_order_update
	 *
	 * Update order in internet buffer.
	 * @param int $id Id of order in webbuffer.
	 * @return bool True if successful
	 */
	   public function internet_order_update($id)
	   {

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
			return $this->last_internet_id = $r['id'];
		}

   	}

   	/**
	 * internet_order_remove
	 *
	 * Delete order line from the internet buffer in multipress.
	 * @param int $id Id of order in webbuffer.
	 * @return bool True if successful
	 */
	   public function internet_order_remove($id)
	   {


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
	 * @return array An array with orders
	 */
	public function internet_get_orders($relation_id)
	{

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
	 * Get planning
	 * 
 	 * @param int $start_time
	 * @param int $end_time
	 * @param int $department_id The id of the department default: 2 = digital
	 * @return array An array with planning items
	 * @license Connector (basic)
	 */
	public function planning_get_lines($start_time = null, $end_time = null, $department_id = 2)
	{

		$start_time = $start_time === null ? self::convertToDate( strtotime('today') ) : self::convertToDate($start_time);
		$end_time = $end_time === null ? self::convertToDate( strtotime('tommorow') ) : self::convertToDate($end_time);

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/planning/getLines?startdate=".$start_time."&stopdate=".$end_time."&department=".$department_id);
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
	 * Get planning item details
	 * 
 	 * @param int $id The id of the planning item
	 * @return array An array with planning item details
	 * @license Connector (basic)
	 */
	public function planning_get_details($id)
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/planning/getLines?id=".$id);
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
	 * Get employee list
	 * 
	 * @return array An array with employees
	 * @license Connector (basic)
	 */
	public function employee_list()
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/employees/getEmployeesList");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r['employees'];
		}
	}

	/**
	 * Get employee details
	 * 
 	 * @param int $id The id of the employee
	 * @return array An array with details for the employee
	 * @license Connector (basic)
	 */
	public function employee_details($id)
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/employees/getEmployeeInfo?employee_number=".$id);
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
	 * Get employee operations
	 * 
 	 * @param int $id The id of the employee
	 * @return array An array with operations linked to the employee
	 * @license Connector (basic)
	 */
	public function employee_operations($id)
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/employees/handleOperations?employee_number=".$id);
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r['operations'];
		}
	}

	/**
	 * Get employee worksheets
	 * 
 	 * @param int $id The id of the employee
 	 * @param int $date The date in seconds
	 * @return array An array with operations linked to the employee
	 * @license Connector (basic)
	 */
	public function employee_worksheets($id, $date = null)
	{

		$date = $date === null ? self::convertToDate( strtotime('today') ) : self::convertToDate($date);

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/employees/handleWorkSheets?employee_number=".$id."&date=".$date);
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
	 * Get job details
	 * 
 	 * @param int $id The id of the job
	 * @return array AN array with job details
	 * @license Connector (basic)
	 */
	public function job_details($id)
	{


		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/jobs/getPostCalculation?job_number=".$id);
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
	 * Get job order history
	 * 
 	 * @param int $id The id of the job
	 * @return array An array with order history
	 * @license Connector (basic)
	 */
	public function job_order_history($id)
	{
		$history = [];

		$details = $this->job_details($id);
		$historyRaw = explode("\r",$details['job_status_history']);

		foreach($historyRaw as $item){

			

			array_push($history, [
				'time' => strtotime(str_replace('/','-',substr($item,0,16))),
				'msg' => trim(explode("    ",substr($item,16))[0],' :'),
				'by' => trim(end(explode("    ",substr($item,16))),' :'),
			]);
			
		}

		return $history;
	}

	
	/**
	 * Get a list of relations
	 *
	 * @param int $relation_code Relation code: K = Client, L = Supplier, O = Old client, As defined in pulldownlist number 22.
	 * @return array An array with relations
	 */
	public function relation_list($relation_code = 'K')
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/relations/getRelationsList?relation_code=".$relation_code);
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r['relations'];
		}

	}

	/**
	 * get_relation_details
	 *
	 * Get a list of relations
	 * @param int $relation_number Relation number
	 * @param string $details - valid options: brief, full, contact, delivery, financial, pricelist, products, partnership
	 * @return array An array with relations
	 */
	public function relation_details($relation_number, $details = 'brief')
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/relations/getRelationInfo?relation_number=".$relation_number."&details=".$details);
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
	 * Get the autofill attributes
	 *
	 * @return array An array with autoFillAttributes
	 */
	public function get_auto_fill_attributes()
	{

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

	/**
	 * get_product_type
	 *
	 * Get product types
	 * @return array An array with product types
	 */
	public function get_product_types()
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/system/getProductTypes");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r['product_types'];
		}

	}

	/**
	 * Get paper
	 * 
	 * @return array An array list of all paper
	 * @license Connector (basic)
	 */
	public function get_paper($pdfLink = null)
	{
		$paperList = [];
		$autoFillAttributes = $this->get_auto_fill_attributes();

		if($pdfLink === null){
			foreach($autoFillAttributes as $type){
				foreach($type['paper'] as $paper){
					if(!array_key_exists($paper['id'], $paperList)){
						$paperList[$paper['id']] = [
							'name' => $paper['name'],
							'description' => $paper['description'],
							'roll' => $paper['roll'],
							'width' => $paper['width'],
							'length' => $paper['length'],
							'type' => $paper['type'],
							'weight' => $paper['weight']
						];
					}
				}
			}
		}else{

			$selectedType = null;
			foreach($autoFillAttributes as $type){
				if($type['id'] == $pdfLink) $selectedType =$type;
			}

			if(is_null($selectedType)) throw new Exception('PdfLink not found');

			foreach($selectedType['paper'] as $paper){
				if(!array_key_exists($paper['id'], $paperList)){
					$paperList[$paper['id']] = [
						'name' => $paper['name'],
						'description' => $paper['description'],
						'roll' => $paper['roll'],
						'width' => $paper['width'],
						'length' => $paper['length'],
						'type' => $paper['type'],
						'weight' => $paper['weight']
					];
				}
			}
		}

		return $paperList;

	}

	/**
	 * Get paper by id
	 * 
	 * @param int $id The id of the paper
	 * @return array An array attributes of the paper
	 * @license Connector (basic)
	 */
	public function get_paper_by_id($id)
	{
		$paperList = $this->get_paper();
		return array_key_exists($id, $paperList) ? $paperList[$id] : false;
	}

	/**
	 * get_operations_list
	 *
	 * Get operations
	 * @return array An array with operations types
	 */
	public function get_operations_list()
	{

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL,$this->protocol.$this->url.":".$this->port."/connector/employees/getOperationsList");
		curl_setopt($curl,CURLOPT_PORT,$this->port);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$this->headers);

		$r = json_decode(curl_exec($curl),true);

		if(isset($r['errornumber'])){
			throw new Exception("Error:" . $r['errornumber'] . " - " . @$r['errortext']);
		}else{
			return $r['operations'];
		}

	}

	/**
	 * Convert date to multipress date
	 * 
	 * @param int time in seconds
	 * @return string date
	 */
	public static function convertToDate($time)
	{
		return substr(date('c', $time), 0, 19);
	}

	/**
	 * Convert decimal time to seconds
	 * 
	 * @param int $time decimal time
	 * @return int Seconds
	 */
	public static function convertToTime($time)
	{
		return (60*$time)*60;
	}
}

?>
