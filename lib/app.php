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

class App {
/*
 * Gets all addresses (phone numbers) that we have messages from in the database
 * @param int $date The unix timestamp which is the minimum requirement
 * @return array Array with address numbers
 */
	public static function getAddresses($date=null) {
		$sql = 'SELECT DISTINCT `address` FROM `*PREFIX*smstorage_items` WHERE `uid_owner` = ?';
		$args = array(\OCP\User::getUser());
		if ($date !== null)
		{
			$sql .= ' AND `date` >= ?';
			$args[] = $date;
		}

		$addresses = array();
		$query = \OCP\DB::prepare($sql);
		$result = $query->execute($args);
		while ($row = $result->fetchRow()) {
			$addresses[] = $row['address'];
		}
		return $addresses;
	}



/*
 * Gets all Messages of one contact
 * @param string $address Phone number
 * @param int $limit Maximum number of rows
 * @param int $offset Index of first row
 * @param int $date Unix timestamp of minimum datetime
 * @return array Array with Messages
 */
	public static function getMessagesOf($address, $limit = null, $offset = null, $date = null) {
		$sql = 'SELECT * FROM `*PREFIX*smstorage_items` WHERE `uid_owner` = ? AND `address` = ?';
		$args = array(\OCP\User::getUser(), $address);
		if ($date !== null) {
			$sql .= ' AND `date` >= ?';
			$args[] = $date;
		}
		$sql .= ' ORDER BY `date` DESC';

		$query = \OCP\DB::prepare($sql, $limit, $offset);
		$result = $query->execute($args);
		$messages = array();
		while ($row = $result->fetchRow()) {
			$messages[] = new Message('DB', $row);
		}
		return $messages;
	}



/**
 * Gets the count of Messages for the given $address
 * @param string $address Phone number
 * @return int Number of messages
 */
	public static function getMessagesCountOf($address) {
		$sql = 'SELECT COUNT(*) AS num FROM `*PREFIX*smstorage_items` WHERE `uid_owner` = ? AND `address` = ?';
		$args = array(\OCP\User::getUser(), $address);

		$query = \OCP\DB::prepare($sql);
		$result = $query->execute($args);
		if ($row = $result->fetchRow()) {
			return $row['num'];
		} else {
			return null;
		}
	}


/**
 * Gets the latest messages
 * @param int $limit Maximum number of rows
 * @param int $offset Number of first row
 * @param int $date
 * @return array Array of Messages
 */
	public static function getMessages($limit = null, $offset = null, $date = null) {
		$sql = 'SELECT * FROM `*PREFIX*smstorage_items` WHERE `uid_owner` = ?';
		$args = array(\OC_User::getUser());
		if ($date !== null) {
			$sql .= ' AND `date` >= ?';
			$args[] = $date;
		}
		$sql .= ' ORDER BY `date` DESC';

		$query = \OCP\DB::prepare($sql, $limit, $offset);
		$result = $query->execute($args);
		$messages = array();
		while ($row = $result->fetchRow()) {
			$messages[] = new Message('DB', $row);
		}
		return $messages;
	}


/**
 * Gets Messages for export
 * @param int $min Minimum number of rows
 * @param int $max Maximum number of rows
 * @param int $minConv Minimum number of rows per conversation. $min has precedence.
 * @param int $date Unix timestamp of minimum datetime
 * @return array Array with Messages
 */
	public static function getMessagesForExport($min = null, $max = null, $minConv = null, $date = null) {
		if ($min === null && $max === null && $minConv === null) {
			$min = 10000;
		}
		$messages = array();

		if ($minConv !== null) {
			$addresses = self::getAddresses();
			foreach ($addresses as $address) {
				$addressMessages = self::getMessagesOf($address, $minConv, null, $date);
				$messages = array_merge($messages, $addressMessages);
			}
		}

		if ($min !== null && count($messages) < $min) {
			$offset = 0;
			while (count($messages) < $min) {
				$nextMessages = self::getMessages($min - count($messages), $offset, $date);
				if (count($nextMessages) === 0) {
					break;
				}
				$messages = array_unique(array_merge($messages, $nextMessages));
				$offset += $min - count($messages);
			}
		}

		if ($max !== null && count($messages) > $max) {
			$messages = array_slice($messages, 0, $max);
		}

		usort($messages, function($a, $b) {
				if ($a->date == $b->date)
					return 0;
				return $a->date - $b->date;
		});

		return $messages;
	}

/**
 * Gets the name from the contacts app. Unfortunately there is no API for that :(
 * @param string $address The address (phone number) for which the name shall be queried
 * @return string The name or null
 */
	public static function getNameOf($address) {

		$sql = <<<EOSQL
SELECT
	`value`
FROM `oc_contacts_cards_properties`
WHERE `name` = 'FN' AND
	`contactid` = 
			(SELECT DISTINCT
				`contactid`
			FROM `oc_contacts_cards_properties`
			WHERE `name` = 'TEL' AND
				(`value` = ?
				OR `value` = ?)
			LIMIT 0, 1)
EOSQL;
		$args = array($address);
		$args[] = '0' . substr($address, strlen(Config::CountryCode()));
		
		$query = \OCP\DB::prepare($sql);
		$result = $query->execute($args);
		if ($row = $result->fetchRow()) {
			return $row['value'];
		} else {
			return null;
		}
	}







/**
 * Tries to import the given file into the database
 * @param string $file Path to file that shall be imported
 * @return object string: error message || array ( [0] => imported message count, [1] => total message count )
 */
	public static function import($file, $type=null) {
		if ($type) {
			switch ($type) {
				case 'SMSBackupAndRestore':
				case 'text/xml':
				case 'application/xml':
					return self::importXML($file, $type);
				default:
					return 'Unknown type: ' . $type;
			}
		} else {
			$result = self::importXML($file);
			if (is_string($result)) {
				// try another import method here:
				// $result = importSomething($file, $type);
				// do this for any method
			}
			if (!isset($result)) {
				return 'Unknown file type: ' . $type;
			}
			return $result;
		}
	}
/**
 * Imports the given XML file into the database
 * @param string $file Path to file that shall be imported
 * @return object string: error message || array ( [0] => imported lines, [1] => total lines )
 */
	private static function importXML($file, $type=null) {
		$xml = simplexml_load_file($file);
		if (!$xml) {
			$errors = libxml_get_errors();
			if (empty($errors)) {
				$msg = 'Not a valid XML file';
			} else {
				$msg = 'Error loading the XML file';
				foreach($errors as $error) {
					$msg = "\t" . $error->message;
				}
			}
			return $msg;
		}

		if ($type == 'text/xml' || $type == 'application/xml') {
			if ($xml->sms->storage->count() !== 0) {
				$type = 'MyPhoneExplorer';
			} elseif ($xml->sms->attributes()->count !== 0) {
				$type = 'SMSBackupAndRestore';
			} else {
				return 'XML Type could not be recognized';
			}
		}

		$new = 0;
		foreach ($xml->sms as $sms) {
			$message = new Message($type, $sms);
			$new += $message->insertIfNotExist();
		}

		return array($new, ((int)$xml->attributes()->count + (int)$xml->attributes()->messagecount));
	}
}
