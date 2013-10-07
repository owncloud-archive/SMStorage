<?php

/**
 * Copyright (c) 2013 Jonathan Stump <1-23-4-5@web.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\SMStorage;

require_once 'lib/base.php';
\OCP\App::checkAppEnabled('smstorage');

header('Content-Type: text/xml');


// Parse Path Info
$path_info = \OC_Request::getPathInfo();
if (substr_compare($path_info, '/smstorage/', 0, 11) !== 0) {
	\OC_Response::setStatus(\OC_Response::STATUS_NOT_FOUND);
	exit;
}
$path_info = substr($path_info, 11);
if ($path_info === false) {
    \OC_Response::setStatus(\OC_Response::STATUS_NOT_FOUND);
    exit;
}

list($user, $action) = explode('/', $path_info);
if (empty($user) || empty($action)) {
    \OC_Response::setStatus(\OC_Response::STATUS_NOT_FOUND);
    exit;
}


// Validate credentials
if (   $_SERVER["PHP_AUTH_USER"] !== $user
	|| \OC_User::login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]) === false
	|| \OC_User::getUser() !== $user)
{
	header('WWW-Authenticate: Basic realm="Authorisation Required"');
	header('HTTP/1.0 401 Unauthorized');
	exit;
}



$output = new \SimpleXMLElement('<result></result>');

switch($action) {
	case 'upload':
		// Parse uploaded file
		if (!isset($_FILES['file'])) {
			$output->addChild('successful', 'false');
			$output->addChild('message', 'Please POST the XML file to this address (name: file)');
			echo $output->asXML();
			exit;
		}
		if ($_FILES['file']['error'] !== 0) {
			$output->addChild('successful', 'false');
			switch($_FILES['file']['error']) {
			case 1:
				$output->addChild('message', 'Error: File is too big! Please adjust PHP settings on server.');
				break;
			case 2:
				$output->addChild('message', 'Error: File is too big!');
				break;
			case 3:
				$output->addChild('message', 'Error: The file upload was aborted!');
				break;
			case 6:
				$output->addChild('message', 'Error: No temporary directory given. Please contact your server administrator');
				break;
			case 7:
				$output->addChild('message', 'Error: Cannot write to the temporary directory. Please contact your server administrator');
				break;
			case 8:
				$output->addChild('message', 'Error: Extension error!');
				break;
			default:
				$output->addChild('message', 'Unknown Error (Code ' . $_FILES['file']['error'] . ')');
				break;
			}
			echo $output->asXML();
			exit;
		}

		$import = App::import($_FILES['file']['tmp_name'], $_POST['type'] ? $_POST['type'] : $_FILES['file']['type']);
		if (is_string($import)) {
			$output->addChild('successful', 'false');
			$output->addChild('message', $import);
		} else {
			$output->addChild('successful', 'true');
			$output->addChild('inserted', $import[0]);
			$output->addChild('total', $import[1]);
			echo $output->asXML();
		}
		break;


	case 'download':
		if (isset($_GET['min'])) {
			$min = (int)$_GET['min'];
		} else {
			$min = null;
		}
		if (isset($_GET['max'])) {
			$max = (int)$_GET['max'];
		} else {
			$max = null;
		}
		if (isset($_GET['minConv'])) {
			$minConv = (int)$_GET['minConv'];
		} else {
			$minConv = null;
		}
		if (isset($_GET['date'])) {
			$date = (int)$_GET['date'];
		} else {
			$date = null;
		}

		$messages = App::getMessagesForExport($min, $max, $minConv, $date);
		\OC_Hook::emit('OCA\SMStorage', 'pre_xml_outputMessages', array( 'messages' => &$messages));

		$output = new \SimpleXMLElement('<smses></smses>');
		foreach ($messages as &$message) {
			$message->addToXML($output);
		}
		$output->addAttribute('count', count($messages));
		echo $output->asXML();
		break;


	default:
		OC_Response::setStatus(OC_Response::STATUS_NOT_FOUND);
	    exit;
}