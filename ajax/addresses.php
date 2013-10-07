<?php
/**
 * Copyright (c) 2013 Jonathan Stump <1-23-4-5@web.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the other files.
 */

namespace OCA\SMStorage;


\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('smstorage');

$addresses = App::getAddresses();

\OC_Hook::emit('OCA\SMStorage', 'pre_ajax_parseAddresses', array( 'addresses' => &$addresses ));

$data = array();
$contactsEnabled = \OC_App::isEnabled('contacts');
foreach ($addresses as &$address) {
	$count = App::getMessagesCountOf($address);
	if ($contactsEnabled) {
		$data[] = array('address' => $address, 'count' => $count, 'name' => App::getNameOf($address));
	} else {
		$data[] = array('address' => $address, 'count' => $count, 'name' => null);
	}
}
usort($data, function($a, $b) {
		if ($a['name'] === null && $b['name'] !== null) {
			return 1;
		}
		if ($a['name'] !== null && $b['name'] === null) {
			return -1;
		}
		return strcmp($a['name'], $b['name']);
});

\OC_Hook::emit('OCA\SMStorage', 'post_ajax_parseAddresses', array('addresses' => &$data));

\OCP\JSON::success(array('addresses' => $data));