<?php

/**
* ownCloud - SMStorage
*
* @author Jonathan Stump
* @copyright 2013 Jonathan Stump <1-23-4-5@web.de>
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\SMStorage;

class Message {
	public $protocol;
	public $address;
	public $date;
	public $type;
	public $subject;
	public $body;
	public $toa;
	public $sc_toa;
	public $service_center;
	public $read;
	public $status;
	public $locked;
	public $date_sent;

	const TYPE_RECEIVED = 1;
	const TYPE_SENT = 2;
	const TYPE_DRAFT = 3;


/**
 * Constructor of an SMS Message. Sets all properties
 * @param string $type The type of the source of $attributes: DB || SMSBackupAndRestore
 * @param object $element A SimpleXMLElement with Attributes that contain the information
 * @since 1.0
 */
	public function __construct($type, $element) {
		switch($type) {
		case 'SMSBackupAndRestore':	// from import XML
			$element = $element->attributes();
			$this->protocol 		= (int)		$element->protocol;
			$this->date				= (int)		substr((string)$element->date, 0, strlen((string)$element->date)-3);
			$this->type				= (int)		$element->type;
			$this->subject			= (string)	$element->subject === 'null' ? NULL : (string)$element->subject;
			$this->body				= (string)	$element->body;
			$this->toa				= (string)	$element->toa === 'null' ? NULL : (string)$element->toa;
			$this->sc_toa			= (string)	$element->sc_toa === 'null' ? NULL : (string)$element->sc_toa;
			$this->read				= (boolean)	$element->read;
			$this->status			= (int)		$element->status;
			$this->locked			= (int)		$element->locked;
			$this->date_sent		= (int)		$element->date_sent === 0 ? NULL : (int)(substr((string)$element->date_sent, 0, strlen((string)$element->date_sent)-3));
			$this->service_center	= (string)	$element->service_center === 'null' ? NULL : (string)$element->service_center;
			if (substr_compare((string)$element->address, '0', 0, 1) === 0) {
				$this->address		= Config::CountryCode() . substr((string)$element->address, 1);
			} else {
				$this->address		= (string)	$element->address;
			}
			break;
		case 'MyPhoneExplorer':	// from import XML
			$this->protocol 		= (int)		0;
			$this->date				= (int)		$element->timestamp->count() !== 0 ? (new \DateTime((string)$element->timestamp))->getTimestamp() : 0;
			$this->type				= (int)		($element->from->count() !== 0 ? self::TYPE_RECEIVED : self::TYPE_SENT);
			$this->subject			= (string)	NULL;
			$this->body				= (string)	$element->body;
			$this->toa				= (string)	NULL;
			$this->sc_toa			= (string)	NULL;
			$this->read				= (boolean)	TRUE;
			$this->status			= (int)		-1;
			$this->locked			= (int)		0;
			$this->date_sent		= (int)		NULL;
			$this->service_center	= (string)	NULL;
			$address = (string)($element->from->count() !== 0 ? $element->from : $element->to);
			if (substr_compare($address, '0', 0, 1) === 0) {
				$this->address		= Config::CountryCode() . substr($address, 1);
			} else {
				$this->address		= (string)	($address);
			}
			break;
		case 'DB':					// from Database
			$this->protocol			= (int)		$element['protocol'];
			$this->date				= (int)		$element['date'];
			$this->type				= (int)		$element['type'];
			$this->subject			= 			$element['subject'] === 		NULL ? NULL : (string)	$element['subject'];
			$this->body				= (string)	$element['body'];
			$this->toa				= 			$element['toa'] === 			NULL ? NULL : (string)	$element['toa'];
			$this->sc_toa			= 			$element['sc_toa'] === 			NULL ? NULL : (string)	$element['sc_toa'];
			$this->service_center	= 			$element['service_center'] === 	NULL ? NULL : (string)	$element['service_center'];
			$this->read				= (boolean)	$element['read'];
			$this->status			= (int)		$element['status'];
			$this->locked			= (int)		$element['locked'];
			$this->date_sent		= (int)		$element['date_sent'];
			$this->address			= (string)	$element['address'];
			break;
		default:
			throw new \Exception('Error creating new Message instance from type ' . $type);
			exit;
		}
	}

/**
 * Converts this instance of Message to a string
 * @return string SHA256 hash of concatenation of all properties
 */
	public function __toString() {
		return hash('sha256', $this->protocol . $this->date . $this->type . $this->subject . $this->body . $this->toa . $this->sc_toa . $this->service_center . $this->read . $this->status . $this->locked . $this->date_sent . $this->address);
	}

/**
 * Converts this instance of Message to a named array
 * @return array An array with all items
 */
	public function toArray() {
		return array(
			'uid_owner'			=> \OCP\User::getUser(),
			'protocol'			=> $this->protocol,
			'address'			=> $this->address,
			'date'				=> $this->date,
			'type'				=> $this->type,
			'subject'			=> $this->subject,
			'body'				=> $this->body,
			'toa'				=> $this->toa,
			'sc_toa'			=> $this->sc_toa,
			'service_center'	=> $this->service_center,
			'read'				=> $this->read,
			'status'			=> $this->status,
			'locked'			=> $this->locked,
			'date_sent'			=> $this->date_sent
		);
	}


/**
 * Writes this instance of Message to the Database if it is not yet existing
 * @since 1.0
 * @return int Number of modified rows
 */
	public function insertIfNotExist() {
		$run = true;
		\OC_Hook::emit('OCA\SMStorage', 'pre_insertMessage', array( 'run' => &$run, 'message' => &$this));

		if ($run === false) {
			return 0;
		}

		$rowCount = \OCP\DB::insertIfNotExist('*PREFIX*smstorage_items', $this->toArray());
		\OC_Hook::emit('OCA\SMStorage', 'post_insertMessage', array('message' => &$this, 'inserted' => (bool)$rowCount));
		return $rowCount;
	}

/**
 * Adds this instance of Message to the specified SimpleXMLElement
 * @since 1.0
 * @param SimpleXMLElement The XML element where this Message shall be added as child
 */
	public function addToXML(&$xml) {
		$run = true;
		\OC_Hook::emit('OCA\SMStorage', 'pre_exportMessage', array( 'run' => &$run, 'message' => &$this, 'xml' => &$xml));

		if ($run == false) {
			return;
		}

		$me = $xml->addChild('sms');
		$me->addAttribute('protocol',		$this->protocol);
		$me->addAttribute('address',		$this->address);
		$me->addAttribute('date',			$this->date . '000');
		$me->addAttribute('type',			$this->type);
		$me->addAttribute('subject',		$this->subject === NULL ? 'null' : $this->subject);
		$me->addAttribute('body',			$this->body);
		$me->addAttribute('toa',			$this->toa === NULL ? 'null' : $this->toa);
		$me->addAttribute('sc_toa',			$this->sc_toa === NULL ? 'null' : $this->sc_toa);
		$me->addAttribute('service_center',	$this->service_center === NULL ? 'null' : $this->service_center);
		$me->addAttribute('read',			$this->read);
		$me->addAttribute('status',			$this->status);
		$me->addAttribute('locked',			$this->locked);
		$me->addAttribute('date_sent',		$this->date_sent !== 0 ? $this->date_sent . '000' : '0');

		\OC_Hook::emit('OCA\SMStorage', 'post_exportMessage', array( 'message' => &$this, 'xml' => &$xml, 'child' => &$me));
	}
}
