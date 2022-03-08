<?php
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

function faspayCC_CIMB_config() {
	$configarray = array(
    "FriendlyName" => array("Type" => "System", "Value"=>"Credit Card"),
        "faspay_merchant_id" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "50", ),
        "faspay_password" => array("FriendlyName" => "Password", "Type" => "password", "Size" => "50", ),
    	"faspayCC_returnURL" => array("FriendlyName" => "Return URL", "Type" => "text", "Size" => "50",),
	);
	return $configarray;
}

function faspayCC_CIMB_link($params) {
	# Gateway Specific Variables
	$faspay_merchant_id = $params['faspay_merchant_id'];
	$faspay_password = $params['faspay_password'];
	$faspay_returnURL = $params['faspayCC_returnURL'];
	
	# Invoice Variables
	$bill_no = $params['invoiceid'];
	$description = $params["description"];
	$amount = number_format(ceil($params['amount']), 2, '.', '');
	$currency = $params['currency']; 
	
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
	
    $signaturecc = sha1('##'.strtoupper($faspay_merchant_id).'##'.strtoupper($faspay_password).'##'.$bill_no.'##'.$amount.'##'.'0'.'##');
      $post = array(
                  "LANG"                => '',                              
                  "MERCHANTID"          => $faspay_merchant_id,                                     
                  "PAYMENT_METHOD"          => '1',
                  "MERCHANT_TRANID"         => $bill_no,       
                  "CURRENCYCODE"            => $currency,
                  "AMOUNT"              => $amount,
                  "CUSTNAME"                => $firstname.' '.$lastname,
                  "CUSTEMAIL"               => $email,                                           
                  "DESCRIPTION"         => 'Payment Invoice '.$bill_no,
                  "RESPONSE_TYPE"       => "1",
                  "RETURN_URL"          => $faspay_returnURL,
                  "SIGNATURE"               => $signaturecc,
                  "TRANSACTIONTYPE"        => "1",
                  "BILLING_ADDRESS"         => '',
                  "BILLING_ADDRESS_CITY"             => '',
                  "BILLING_ADDRESS_REGION"      => '',
                  "BILLING_ADDRESS_STATE"       => '',
                  "BILLING_ADDRESS_POSCODE"     => '',
                  "BILLING_ADDRESS_COUNTRY_CODE"        => '',
                  "RECEIVER_NAME_FOR_SHIPPING"      => '',
                  "SHIPPING_ADDRESS"            => '',
                  "SHIPPING_ADDRESS_CITY"       => '',
                  "SHIPPING_ADDRESS_REGION"     => '',
                  "SHIPPING_ADDRESS_STATE"      => '',
                  "SHIPPING_ADDRESS_POSCODE"        => '',
                  "SHIPPING_ADDRESS_COUNTRY_CODE"   => '',
                  "SHIPPINGCOST"            => '0.00',
                  "PHONE_NO"                => '',
                  "MREF1"               => '',
                  "MREF2"               => '',
                  "MREF3"               => '',
                  "MREF4"               => '',
                  "MREF5"               => '',
                  "MREF6"               => '',
                  "MREF7"               => '',
                  "MREF8"               => '',
                  "MREF9"               => '',
                  "MREF10"              => '',
                  "MPARAM1"                 => '',
                  "MPARAM2"                 => '',
                  "CUSTOMER_REF"            => '',
                  "PYMT_IND"            => '',
                  "PYMT_CRITERIA"       => '',
                  "FRISK1"              => '',
                  "FRISK2"              => '',
                  "DOMICILE_ADDRESS"            => '',
                  "DOMICILE_ADDRESS_CITY"       => '',
                  "DOMICILE_ADDRESS_REGION"     => '',
                  "DOMICILE_ADDRESS_STATE"      => '',
                  "DOMICILE_ADDRESS_POSCODE"        => '',
                  "DOMICILE_ADDRESS_COUNTRY_CODE"   => '',
                  "DOMICILE_PHONE_NO"           => '',
                  "handshake_url"           => '',
                  "handshake_param"         => '',
    );
    

    $string = '<form method="post" name="form" action="https://fpg.faspay.co.id/payment">';
    if ($post != null) {
          foreach ($post as $name=>$value) {
        $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
      }
    }
    
    $string .= '</form>';
    $string .= '<script> document.form.submit();</script>';
    
    echo $string;
	
}