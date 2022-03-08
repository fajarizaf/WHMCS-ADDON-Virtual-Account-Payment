<?php

include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$complete = "https://www.masterweb.com/success.png";
$systemURL = "https://masterkey.masterweb.com/";

if (isset($_POST)) {

        $gatewaymodule = getpaymethod($_REQUEST['bill_no']);
        $GATEWAY = getGatewayVariables($gatewaymodule);
    
        echo "<head>";
            echo '<link rel="stylesheet" type="text/css" href="'.$systemURL.'templates/six/css/invoice.css">';
            echo '<link rel="stylesheet" type="text/css" href="'.$systemURL.'templates/six/css/all.min.css">';
        echo "</head>";
        
        echo "<img src='https://www.masterweb.com/masterweb-logo.png' width='250px;' style='display: block;margin-left: auto;margin-right: auto;'></img>";

        echo "<div class='container-fluid invoice-container'>";
            echo "<main id='htlfndr-main-content' class='htlfndr-main-content' role='main'>";
                echo "<article class='htlfndr-thanks-page text-center'>";
                
                    if($_REQUEST["status"]=="2") {
                        echo "<h1>Terima Kasih</h1>";
                        echo "<img style='width:230px; padding:20px' src=".$complete.">";
                        echo "<h3>Untuk pembayaran pesanan anda pada Invoice : #".$_REQUEST['bill_no'];
                        
                        $status = "Di Bayar";
                        echo "<div>";
                            echo "<h4>Status pembayaran anda : <span style='color:#3ca108'><b>".$status."</b></span></h4>";
                        echo "</div>";
                        echo '<a href="'.$systemURL.'viewinvoice.php?id='.$_REQUEST['bill_no'].'" class="btn btn-success"><i class="fas fa-download"></i>View Invoice</a>';
                    } else {
                        echo "<h1>Terima Kasih</h1>";
                        echo "<img style='width:230px; padding:20px' src=".$complete.">";
                        echo "<h3>Untuk pembayaran pesanan anda pada Invoice : #".$_REQUEST['bill_no'];
                        
                        $status = "Belum Terbayar";
                        echo "<div>";
                            echo "<h4>Status pembayaran anda : <span style='color:red'><b>".$status."</b></span></h4>";
                            echo "<h5>Untuk petunjuk pembayaran telah kami kirimkan ke email anda.</h5>";
                        echo "</div>";

                        sendEmail($_REQUEST['bill_no'], $GATEWAY['name'], $_REQUEST['trx_id']);
                        echo '<a href="'.$systemURL.'clientarea.php?action=invoices" class="btn btn-danger"><i class="fas fa-download"></i>Invoice History</a>';
                    }

                
            echo "</main>";
        echo "</div>";
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


function sendEmail($invoiceid, $methodpayment, $VANumber) {
    
    // Define parameters
    $command = 'SendEmail';
    $values = array(
        'messagename' => ''.$methodpayment.' Payment Instructions',
        'id' => $invoiceid,
        'customvars' => base64_encode(serialize(array("VANumber"=>$VANumber)))
    );
    $adminuser = 'fajar';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);

}

?>