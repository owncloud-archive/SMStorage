<?php

namespace OCA\SMStorage;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::callCheck();

require dirname(__DIR__) . '/3rdparty/countrycodes.php';

// Get data
if (isset($_POST['countryCode']))
	$code=trim($_POST['countryCode']);
if ($code && array_key_exists($code, $countryCodes)) {
	Config::setCountryCode($code);
	\OCP\JSON::success(array('data' => array('message' => 'Code saved')));
} else {
	\OCP\JSON::error(array('data' => array('message' => 'Invalid code')));
}