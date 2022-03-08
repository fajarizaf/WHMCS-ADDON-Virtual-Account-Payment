<?php
include("../../init.php");
/*
 * This is WHMCS module using FasPay Payment Gateway
 * License : http://www.gnu.org/licenses/gpl.html
 *
 * WHMCS - The Complete Client Management, Billing & Support Solution
 * Copyright (c) WHMCS Ltd. All Rights Reserved,
 * Email: info@whmcs.com
 * Website: http://www.whmcs.com
 *
 * Masterweb Corporation
 * Website: http://masterweb.com
 */

function faspayBniVA_config() {
	$configarray = array(
    "FriendlyName" => array("Type" => "System", "Value"=>"BNI VA"),
        "faspay_merchant_id" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "50", ),
        "faspay_merchant_name" => array("FriendlyName" => "Merchant Name", "Type" => "text", "Size" => "50", ),
        "faspay_channel" => array("FriendlyName" => "Channel Code", "Type" => "text", "Size" => "50", "Value"=>"801"),
        "faspay_username" => array("FriendlyName" => "User ID", "Type" => "text", "Size" => "50", ),
        "faspay_password" => array("FriendlyName" => "Password", "Type" => "password", "Size" => "50", ),
	);
	return $configarray;
}

function faspayBniVA_link($params) {
	# Gateway Specific Variables
	$faspay_merchant_id = $params['faspay_merchant_id'];
	$faspay_merchant_name = $params['faspay_merchant_name'];
	$faspay_username = $params['faspay_username'];
	$faspay_password = $params['faspay_password'];
	$faspay_log = $params['faspay_log'];
	$channel_bank = $params['faspay_channel'];
	$faspay_post_act = '300011/383xx00010100000';

	# Invoice Variables
	$bill_no = $params['invoiceid'];
	$description = $params["description"];
    $amount = number_format(ceil($params['amount']), 2, '', '');# Format: ##.##
    $bill_date = date('Y-m-d H:i:s');
	$currency = $params['currency']; # Currency Code
    
    $signature = sha1(md5($faspay_username.$faspay_password.$bill_no));
    
	# Client Variables
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$postcode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$phone = $params['clientdetails']['phonenumber'];
    
	# System Variables
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$currency = $params['currency'];
    
    $VANumber = getVABNINumber($bill_no);

    if($VANumber == 'Virtual Account Number :' OR $VANumber == '') {
        $faspay_post_trans =
        '<faspay>'.
            '<request>'.'Post Data Transaksi'.'</request>'.
            '<merchant_id>'.$faspay_merchant_id.'</merchant_id>'.
            '<merchant>'.$faspay_merchant_name.'</merchant>'.
            '<bill_no>'.$bill_no.'</bill_no>'.
            '<bill_date>'.$bill_date.'</bill_date>'.
            '<cust_name>'.$firstname.' '.$lastname.'</cust_name>'.
            '<payment_channel>'.$channel_bank.'</payment_channel>'.
            '<bill_total>'.$amount.'</bill_total>'.
            '<pay_type>'.'1'.'</pay_type>'.
            '<terminal>'.'10'.'</terminal>'.
            '<bill_desc>'.'Pembayaran Invoice '.$bill_no.' - Channel Payment : '.$channel_bank.'</bill_desc>'.
            '<bill_expired>'.date('Y-m-d H:i:s', strtotime('+24 hours')).'</bill_expired>'.
            '<item>'.
                '<product>Pembayaran Invoice '.$bill_no.' - Channel Payment : '.$channel_bank.'</product>'.
                '<amount>'.$amount.'</amount>'.
                '<qty>1</qty>'.
                '<payment_plan>1</payment_plan>'.
                '<tenor>00</tenor>'.
                '<merchant_id />'.
            '</item>'.
            '<signature>'.$signature.'</signature>'.
        '</faspay>';        
        $data_faspay = faspay_postxmlBniVA($faspay_post_act,$faspay_post_trans);
		
        try{
            addVABNINumber($data_faspay->bill_no,$data_faspay->trx_id);
            
            $string = '<form method="get" name="form" action="https://web.faspay.co.id/pws/100003/2830000010100000/'.$signature.'">';
            $string .= '<input type="hidden" name="trx_id" value="'.$data_faspay->trx_id.'">';
            $string .= '<input type="hidden" name="merchant_id" value="'.$data_faspay->merchant_id.'">';
            $string .= '<input type="hidden" name="bill_no" value="'.$data_faspay->bill_no.'">';
            $string .= '</form>';
            $string .= '<script> document.form.submit();</script>';   
            echo $string;
            
        }catch(Exception $e){
            $e->getMessage();
        }
    } else {
        $VANumber = preg_replace('/[^0-9]/', '', $VANumber);
        $string = '<form method="get" name="form" action="https://web.faspay.co.id/pws/100003/2830000010100000/'.$signature.'">';
        $string .= '<input type="hidden" name="trx_id" value="'.$VANumber.'">';
        $string .= '<input type="hidden" name="merchant_id" value="'.$faspay_merchant_id.'">';
        $string .= '<input type="hidden" name="bill_no" value="'.$bill_no.'">';
        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';   
        echo $string;
    }
    //}
}

function faspay_postxmlBniVA($faspay_post_act,$faspay_post_trans) {
    $URLdev   = 'https://web.faspay.co.id/pws/'.$faspay_post_act;
    
    $c = curl_init ($URLdev);
    curl_setopt ($c, CURLOPT_POST, true);
    curl_setopt ($c, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt ($c, CURLOPT_POSTFIELDS, $faspay_post_trans);
    curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($c, CURLOPT_SSL_VERIFYPEER, false);
    
    $post = simplexml_load_string(curl_exec ($c));
    return $post;
}

function addVABNINumber($invoiceid,$va_number) {
    
    $command = 'updateInvoice';
    $values = array(
        'invoiceid' => $invoiceid,
        'notes' => 'Virtual Account Number : '.$va_number
    );
    $adminuser = 'fajar';

    // Call the localAPI function
    localAPI($command, $values, $adminuser);
}

function getVABNINumber($invoiceid) {
    
    $command = 'getinvoice';
    $values = array(
        'invoiceid' => $invoiceid
    );
    $adminuser = 'fajar';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);
    return $results['notes'];
}


?>