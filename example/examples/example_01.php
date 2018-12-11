<?php

require_once('../../src/MultiPressPHP.php');
$secrets = (include '../secret.php');

if(isset($_GET['exec']) && $_GET['exec'] == 1){

    $response = '';

    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);

    $product_types  = $mp->get_product_types();
	$relation_details = $mp->relation_details($_GET['relation']);

	if(isset($_GET['paper'])){
		$paper_details = $mp->get_paper_by_id($_GET['paper']);
		$paper = $paper_details['description'];
	}else{
		$paper = 'condat silk wit 130 g/m²';
	}

    $mp->fill_internet_buffer([
		'type' => 2,
		'checklist' => 1,
		'standard_quotation' => 0,
		'run_01' => $_GET['quantity'],
		'run_02' => 0,
		'run_03' => 0,
		'text_01' => $_GET['description'], //Description
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
		'relation_number' => $_GET['relation'],
		'contact_name' => "Test piloot",
		'company' => $relation_details['company'],
		'phone' => $relation_details['phone'],
		'fax' => $relation_details['fax'],
		'email' => $relation_details['email'],
		'zipcode' => $relation_details['visit_zipcode'],
		'address_number' => $relation_details['visit_address_number'],
		'address' => $relation_details['visit_address'],
		'city' => $relation_details['visit_city'],
		'country' => "BELGIË",
		'country_code' => "BE",
		'extra_address_1' => "",
		'extra_address_2' => "",
		'extra_address_3' => "",
		'extra_address_4' => "",
		'extra_address_5' => "",
		'delivery_date' => MultiPressPHP::convertToDate(time()+86400),
		'artwork_date' => MultiPressPHP::convertToDate(time()),
		'vat' => $product_types[$_GET['product_type']]['vat'],
		'price' => $_GET['price'],
		'language_code' => "nl",
		'invoice_number' => null,
		'invoice_address' => "",
		'invoice_company' => "",
		'remark' => "",
		'files' => false,
		'payment_type' => "",
		'payment_remark' => "",
		'product_type' => $product_types[$_GET['product_type']]['product_type'],
		'product_number' => $product_types[$_GET['product_type']]['product_number'],
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
			'pagescover' => 0,
			'paperbody' => $paper,
			'papercover' => $paper,
			'colorbody' => "Recto: Cyaan; Magenta; Geel; Zwart\rVerso: Cyaan; Magenta; Geel; Zwart\r",
			'colorcover' => "Recto: Cyaan; Magenta; Geel; Zwart\rVerso: Cyaan; Magenta; Geel; Zwart\r",
			'bleed' => 3,
			'grain' => "",
			'production' => "Printen",
			'bindingtype' => "",
            'finishing' => ""
        ]	
    ]);

    $response = $mp->internet_order_add();


}else{

    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
    
    $product_types  = $mp->get_product_types();
    $relations  = $mp->relation_list();
	$auto_fill_attributes  = $mp->get_auto_fill_attributes();
	$paper_list = $mp->get_paper(2);

    usort($relations, function($a, $b){
        return strcmp(strtoupper($a['company']), strtoupper($b['company']));
    });


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MultiPressPHP - Examples</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.css" />
</head>
<body>
    <div class="container">
        <h1>Add order</h1><hr/>
        <?php if(!isset($response)): ?>

            <form action="" method="get">
                
                <label for="relation">Relation</label>
                <select class="u-full-width" id="relation" name="relation" required>
                    <option value=""></option>
                    <?php foreach($relations as $relation): ?>
                        <option value="<?= $relation['relation_number'] ?>"><?= $relation['company'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="reference">Reference</label>
                <input type="text" class="u-full-width" id="reference" name="reference">

                <label for="description">Description</label>
                <textarea class="u-full-width" id="description" name="description"><?= $response ?></textarea>

                <label for="product_type">Product type</label>
                <select class="u-full-width" id="product_type" name="product_type" required>
                    <option value=""></option>
                    <?php foreach($product_types as $key => $product_type): ?>
                        <option value="<?= $key ?>"><?= $product_type['product_type'] ?></option>
                    <?php endforeach; ?>
                </select>

				<label for="paper">Paper</label>
                <select class="u-full-width" id="paper" name="paper" required>
                    <option value=""></option>
                    <?php foreach($paper_list as $key => $paper): ?>
                        <option value="<?= $key ?>"><?= $paper['description'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="quantity">Quantity</label>
                <input type="number" class="u-full-width" id="quantity" name="quantity" min="0" max="9999" required>

                <label for="price">Price</label>
                <input type="number" class="u-full-width" id="price" name="price" min="0" max="9999" required>

                <input type="hidden" name="exec" value="1">
                <button class="btn">Submit</button>
            </form>

        <?php else: ?>
            <div class="row">
                <div class="six columns">
                    <label for="response">Internet buffer</label>
                    <pre><?= json_encode($mp->INT_BUFFER,JSON_UNESCAPED_UNICODE); ?></pre>
                </div>
                <div class="six columns">
                    <label for="response">Response</label>
                    <textarea class="u-full-width" id="response"><?= $response ?></textarea>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>