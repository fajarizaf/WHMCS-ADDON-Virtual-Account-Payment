<?php

include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");


$retreiveData = file_get_contents('php://input');
$data_faspay = simplexml_load_string($retreiveData);

if ($data_faspay->request == 'Payment Notification') {

    $gatewaymodule = getpaymethod($data_faspay->bill_no);
    $GATEWAY = getGatewayVariables($gatewaymodule);

    if (!$GATEWAY["type"]) die("Module Not Activated"); 
    $systemURL = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
    
    $trx_id                 = $data_faspay->trx_id;
    $bill_no                = $data_faspay->bill_no;
    $bill_amount            = getAmount($data_faspay->bill_no);
    $status                 = $data_faspay->payment_status_code;
    $fee                    = 0;

    if ($data_faspay->payment_status_code == "2") { /*Paid*/

            $statusInvoice = getStatus($data_faspay->bill_no);

            if($statusInvoice !== 'Paid') {
                $invoiceid = checkCbInvoiceID($bill_no,$GATEWAY["name"]);
                addInvoicePayment($invoiceid,$trx_id,$bill_amount,$fee,$gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
                logTransaction($GATEWAY["name"], $retreiveData,"Paid"); # Save to Gateway Log: name, data array, status
            }
            
        } else if($data_faspay->payment_status_code == "1") { /*Dalam Proses*/
        
            logTransaction($GATEWAY["name"], $retreiveData,"Dalam Proses"); # Save to Gateway Log: name, data array, status          

        } else if($data_faspay->payment_status_code == "0") { /*Belum diproses*/
            
            logTransaction($GATEWAY["name"], $retreiveData,"Belum diproses"); # Save to Gateway Log: name, data array, status
        
        } else if($data_faspay->payment_status_code == "4") { /*Reserval*/
       
            logTransaction($GATEWAY["name"], $retreiveData,"Payment Reserval"); # Save to Gateway Log: name, data array, status
        
        } else if($data_faspay->payment_status_code == "8") { /*Cancelled*/
            
            logTransaction($GATEWAY["name"], $retreiveData,"Payment Cancelled"); # Save to Gateway Log: name, data array, status
        
        } else if($data_faspay->payment_status_code == "7") { /*Payment Expired*/
            
            logTransaction($GATEWAY["name"], $retreiveData,"Payment Expired"); # Save to Gateway Log: name, data array, status

        } else { /*Unknown*/
            
            logTransaction($GATEWAY["name"], $retreiveData,"Unknown"); # Save to Gateway Log: name, data array, status
           
        }

    // send response to faspay
    header("Content-type: text/xml; charset=utf-8");
    echo "<faspay>
        <response>Payment Notification</response>
        <trx_id>".$trx_id."</trx_id>
        <merchant_id>".$GATEWAY["faspay_merchant_id"]."</merchant_id>
        <bill_no>".$invoiceid."</bill_no>
        <response_code>00</response_code>
        <response_desc>Sukses</response_desc>
        <response_date>".date('Y-m-d H:i:s')."</response_date>
    </faspay>";
    
} 

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

function getAmount($invoiceid) {
    
    $command = 'getinvoice';
    $values = array(
        'invoiceid' => $invoiceid
    );
    $adminuser = 'fajar';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);
    return $results['total'];
}

function getStatus($invoiceid) {
    
    $command = 'getinvoice';
    $values = array(
        'invoiceid' => $invoiceid
    );
    $adminuser = 'fajar';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);
    return $results['status'];
}


?>