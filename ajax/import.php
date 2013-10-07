<?php

/**
 * Copyright (c) 2013 Jonathan Stump <1-23-4-5@web.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\SMStorage;

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('smstorage');


if (!isset($_FILES['file'])) {
	\OCP\JSON::error(array('message' => 'Please POST the XML file to this address (name: file)'));
	exit;
}
if ($_FILES['file']['error'] !== 0) {
	switch($_FILES['file']['error']) {
	case 1:
		\OCP\JSON::error(array('message' => 'Error: File is too big! Please adjust PHP settings on server.'));
		exit;
	case 2:
		\OCP\JSON::error(array('message' => 'Error: File is too big!'));
		exit;
	case 3:
		\OCP\JSON::error(array('message' => 'Error: The file upload was aborted!'));
		exit;
	case 6:
		\OCP\JSON::error(array('message' => 'Error: No temporary directory given. Please contact your server administrator'));
		exit;
	case 7:
		\OCP\JSON::error(array('message' => 'Error: Cannot write to the temporary directory. Please contact your server administrator'));
		exit;
	case 8:
		\OCP\JSON::error(array('message' => 'Error: Extension error!'));
		exit;
	default:
		\OCP\JSON::error(array('message' => 'Unknown Error (Code ' . $_FILES['file']['error'] . ')'));
		exit;
	}
}

$import = App::import($_FILES['file']['tmp_name'], $_FILES['file']['type']);
if (is_string($import)) {
	\OCP\JSON::error(array('message' => $import));
} else {
	\OCP\JSON::success(array('inserted' => $import[0], 'total' => $import[1]));
}