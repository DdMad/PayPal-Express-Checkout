<?php

	session_start();

	require_once("config.php");
	require_once("postRequest.php");

	$paypalMode = (PAYPAL_MODE == 'sandbox') ? '.sandbox' : '';

	if($_POST) {
		$itemName = $_POST["itemName"];
		$itemId = $_POST["itemId"];
		$itemUnitPrice = $_POST["itemUnitPrice"];
		$itemQty = $_POST["itemQty"];
		$itemDescription = $_POST["itemDescription"];

		$itemTotalPrice = $itemUnitPrice * $itemQty;

		$totalTax = itemTotalPrice * 0.07;
		$handlingFee = 2.00;
		$insuranceFee = 1.00;
		$shipDiscount = -2.00;
		$shippingFee = 5.00;

		$totalCost = $itemTotalPrice + $totalTax + $handlingFee + $insuranceFee + $shippingFee;

		$urlString = '&METHOD=SetExpressCheckout'.
						'&RETURNURL='.urlencode(PAYPAL_RETURN_URL).
						'&CANCELURL='.urlencode(PAYPAL_CANCEL_URL).
						'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").

						'&L_PAYMENTREQUEST_0_NAME0='.urlencode($itemName).
						'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($itemId).
						'&L_PAYMENTREQUEST_0_DESC0='.urlencode($itemDescription).
						'&L_PAYMENTREQUEST_0_AMT0='.urlencode($itemUnitPrice).
						'&L_PAYMENTREQUEST_0_QTY0='.urlencode($itemQty).

						'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($itemTotalPrice).
						'&PAYMENTREQUEST_0_TAXAMT='.urlencode($totalTax).
						'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($shippingFee).
						'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($handlingFee).
						'&PAYMENTREQUEST_0_SHIPDISCANT='.urlencode($shipDiscount).
						'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($insuranceFee).
						'&PAYMENTREQUEST_0_AMT='.urlencode($totalCost).
						'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode(PAYPAL_CURRENCY_CODE).
						'&LOCALECODE=GB'.
						'&CARTBORDERCOLOR=FFFFFF'.
						'&ALLOWNOTE=1';

		$_SESSION['itemName'] = $itemName;
		$_SESSION["itemId"] = $itemId;
		$_SESSION['itemUnitPrice'] = $itemUnitPrice;
		$_SESSION['itemDescription'] = $itemDescription;
		$_SESSION['itemQty'] = $itemQty;
		$_SESSION['itemTotalPrice'] = $itemTotalPrice;
		$_SESSION['totalTax'] = $totalTax;
		$_SESSION['handlingFee'] = $handlingFee;
		$_SESSION['insuranceFee'] = $insuranceFee;
		$_SESSION['shippingFee'] = $shippingFee;
		$_SESSION['shipDiscount'] = $shipDiscount;
		$_SESSION['totalCost'] = $totalCost;

		$httpResponseParsedDetails = postRequest('SetExpressCheckout', $urlString, PAYPAL_MODE);

		if (strtoupper($httpResponseParsedDetails["ACK"]) == "SUCCESS" || strtoupper($httpResponseParsedDetails["ACK"]) == "SUCCESSWITHWARNING") {
			$ppURL = 'https://www'.$paypalMode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpResponseParsedDetails["TOKEN"].'';
			header('Location: '.$ppURL);
		} else {
			echo '<div style="color:red"><b>Error: </b>'.urlencode($httpResponseParsedDetails["L_LONGMESSAGE0"]).'</div>';
		}
	}

	if (isset($_GET["token"]) && isset($_GET["PayerID"])) {
		$token = $_GET["token"];
		$payerID = $_GET["PayerID"];

		$itemName = $_SESSION['itemName'];
		$itemId = $_SESSION["itemId"];
		$itemUnitPrice = $_SESSION['itemUnitPrice'];
		$itemDescription = $_SESSION['itemDescription'];
		$itemQty = $_SESSION['itemQty'];
		$itemTotalPrice = $_SESSION['itemTotalPrice'];
		$totalTax = $_SESSION['totalTax'];
		$handlingFee = $_SESSION['handlingFee'];
		$insuranceFee = $_SESSION['insuranceFee'];
		$shippingFee = $_SESSION['shippingFee'];
		$shipDiscount = $_SESSION['shipDiscount'];
		$totalCost = $_SESSION['totalCost'];

		$urlString = '&TOKEN='.urlencode($token).
						'&PAYERID='.urlencode($payerID).
						'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
						'&L_PAYMENTREQUEST_0_NAME0='.urlencode($itemName).
						'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($itemId).
						'&L_PAYMENTREQUEST_0_DESC0='.urlencode($itemDescription).
						'&L_PAYMENTREQUEST_0_AMT0='.urlencode($itemUnitPrice).
						'&L_PAYMENTREQUEST_0_QTY0='.urlencode($itemQty).

						'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($itemTotalPrice).
						'&PAYMENTREQUEST_0_TAXAMT='.urlencode($totalTax).
						'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($shippingFee).
						'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($handlingFee).
						'&PAYMENTREQUEST_0_SHIPDISCANT='.urlencode($shipDiscount).
						'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($insuranceFee).
						'&PAYMENTREQUEST_0_AMT='.urlencode($totalCost).
						'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode(PAYPAL_CURRENCY_CODE);
		$httpResponseParsedDetails = postRequest('DoExpressCheckoutPayment', $urlString, PAYPAL_MODE);

		if (strtoupper($httpResponseParsedDetails["ACK"]) == "SUCCESS" || strtoupper($httpResponseParsedDetails["ACK"]) == "SUCCESSWITHWARNING") {
			echo "<h2>Success</h2>";
		} else {
			echo "<h2>Error</h2>";
		}
	}

?>