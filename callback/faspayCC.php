<?php

include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = getpaymethod($_POST['MERCHANT_TRANID']);

$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) die("Module Not Activated"); 
$systemURL = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);

    if (isset($_POST)) {
        
		//$biayaService 			= 2500+(2.75*$amountInv/100);
        $trx_id                 = $_POST['TRANSACTIONID'];
        $merchant               = $_POST['MERCHANTID'];
        $bill_no                = $_POST['MERCHANT_TRANID'];
        $bill_reff              = $_POST['DESCRIPTION'];
        $bill_amount            = $_POST['AMOUNT'];
        $payment_date           = $_POST['TRANDATE'];
        $bank_actid             = $_POST['ACQUIRER_ID'];
        $status                 = $_POST['TXN_STATUS']; 
        $invoiceid 				= checkCbInvoiceID($bill_no,$GATEWAY["name"]);

		if($_POST["TXN_STATUS"]=="F" || $_POST["TXN_STATUS"]=="RC"){
			// F = gagal
            $fee = 0;
            logTransaction($GATEWAY["name"], $_POST, __LINE__.": Payment Failed"); # Save to Gateway Log: name, data array, status
            header('Refresh: 10; URL='.$systemURL.'');
            exit(__LINE__.': Payment Failed');
		}
		elseif($_POST["TXN_STATUS"]=="N" ){
			// N = Pending
            $fee = 0;
            logTransaction($GATEWAY["name"], $_POST, __LINE__.": Payment Pending"); # Save to Gateway Log: name, data array, status
            header('Refresh: 10; URL='.$systemURL.'');
            exit(__LINE__.': Payment Pending');
		}		
		elseif($_POST['TXN_STATUS']  == "A"){
            $fee = 0;
            logTransaction($GATEWAY["name"], $_POST, __LINE__.": Payment Proccess"); # Save to Gateway Log: name, data array, status
            header('Refresh: 10; URL='.$systemURL.'');
            exit(__LINE__.': Payment Proccess');
		}
		elseif($_POST['TXN_STATUS']  == "S" || $_POST['TXN_STATUS']  == "C"){
            $fee = 0;
            $invoiceid = checkCbInvoiceID($bill_no,$GATEWAY["name"]);
            //checkCbTransID($trx_id);
            addInvoicePayment($invoiceid,$trx_id,$bill_amount,$fee,$gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
            logTransaction($GATEWAY["name"], $_POST, __LINE__.": Payment Success"); # Save to Gateway Log: name, data array, status
            header("Location: {$systemURL}/viewinvoice.php?id={$invoiceid}");
		}
		elseif($_POST["TXN_STATUS"] == "V"){
            $fee = 0;
            logTransaction($GATEWAY["name"], $_GET, __LINE__.": Unknown"); # Save to Gateway Log: name, data array, status
            header('Refresh: 10; URL='.$systemURL.'');
            exit(__LINE__.': Unknown Payment');
		}			
	}
        //case "A": /*Paid - Authorized*/
        //case "C": /*Paid - Captured*/
        //case "S": /*Paid - Sales*/
        //    $fee = 0;
        //    $invoiceid = checkCbInvoiceID($bill_no,$GATEWAY["name"]);
        //    checkCbTransID($trx_id);
        //    addInvoicePayment($invoiceid,$trx_id,$bill_amount,$fee,$gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
        //    logTransaction($GATEWAY["name"], $_POST, __LINE__.": Paid"); # Save to Gateway Log: name, data array, status
        //    header("Location: {$systemURL}/viewinvoice.php?id={$invoiceid}");
        //    exit(__LINE__.': Paid');
        //    break;
        //
        //case "F": /*Declined*/
        //    $fee = 0;
        //    logTransaction($GATEWAY["name"], $_POST, __LINE__.": Declined"); # Save to Gateway Log: name, data array, status
        //    header('HTTP/1.1 400 Declined');
        //    exit(__LINE__.': Declined');
        //    break;
        //
        //default: /*Unknown*/
        //    $fee = 0;
        //    logTransaction($GATEWAY["name"], $_GET, __LINE__.": Unknown"); # Save to Gateway Log: name, data array, status
        //    header('HTTP/1.1 400 Unknown');
        //    exit(__LINE__.': Unknown');


function getpaymethod($invoiceid) {
    
      $command = 'getinvoice';
      $values = array(
          'invoiceid' => $invoiceid
      );
      $adminuser = 'fajar';
  
      // Call the localAPI function
      $results = localAPI($command, $values, $adminuser);
      return $results['paymentmethod'];
  }


?>