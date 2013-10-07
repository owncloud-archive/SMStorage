<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\SMStorage;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('smstorage');

if (!isset($_GET['address']) || empty($_GET['address'])) {
	\OCP\JSON::error(array( 'message' => 'No address parameter given'));
	exit;
}

$messages = App::getMessagesOf($_GET['address']);
\OC_Hook::emit('OCA\SMStorage', 'pre_ajax_parseMessages', array( 'messages' => &$messages ));

$l = new \OC_l10n('smstorage');

$data = array();
foreach ($messages as $message) {
	$data[] = array(
			'date' => $l->l('datetime', $message->date),
			'body' => str_replace(
						array(chr(10)),
						array('<br/>'),
						$message->body),
			'type' => $message->type
	);
}

\OC_Hook::emit('OCA\SMStorage', 'post_ajax_parseMessages', array( 'messages' => &$data ));

\OCP\JSON::success(array(
	'messages' => $data
));