<?php
	require_once("config.php");

	function postRequest($methodName, $urlString, $paypalMode) {

		$URL_STRING_USERNAME = urlencode(PAYPAL_USERNAME);
		$URL_STRING_PASSWORD = urlencode(PAYPAL_PASSWORD);
		$URL_STRING_SIGNATURE = urlencode(PAYPAL_SIGNATURE);
		$URL_STRING_PAYPAL_MODE = ($paypalMode=='sandbox') ? '.sandbox' : '';
		$URL_STRING_END_POINT = "https://api-3t".$URL_STRING_PAYPAL_MODE.".paypal.com/nvp";
		$URL_STRING_VERSION = urlencode('121.0');

		$requestURL = "METHOD=$methodName&VERSION=$URL_STRING_VERSION&PWD=$URL_STRING_PASSWORD&USER=$URL_STRING_USERNAME&SIGNATURE=$URL_STRING_SIGNATURE$urlString";

		$ch = curl_init();
		// curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem');
		curl_setopt($ch, CURLOPT_URL, $URL_STRING_END_POINT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestURL);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$httpResponse = curl_exec($ch);

		if (!$httpResponse) {
			exit("$methodName failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}

		$httpResponseDetails = explode("&", $httpResponse);
		$httpResponseParsedDetails = array();
		foreach ($httpResponseDetails as $i => $value) {
			$temp = explode("=", $value);
			if (sizeof($temp) > 1) {
				$httpResponseParsedDetails[$temp[0]] = $temp[1];
			}
		}

		if ((sizeof($httpResponseParsedDetails) == 0) || !array_key_exists('ACK', $httpResponseParsedDetails)) {
			exit("Invalid HTTP Response.");
		}

		return $httpResponseParsedDetails;

	}
?>