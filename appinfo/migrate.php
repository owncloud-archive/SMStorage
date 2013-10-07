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

class OC_Migration_Provider_SMStorage extends OC_Migration_Provider {
	public function export() {
		$options = array(
			'table' => 'smstorage_items',
			'matchcol'	=> 'uid_owner',
			'matchval'	=> $this->uid,
			'idcol'		=> 'id'
		);
		$ids = $this->content->copyRows($options);

		return is_array($ids);
	}

	public function import() {
		switch($this->appinfo->version) {
		default:
			$query = $this->content->prepare('SELECT * FROM smstorage_items WHERE uid_owner = ?');
			$results = $query->execute(array($this->olduid));

			while ($row = $results->fetchRow()) {
				$message = new Message($row);
				$message->uid_owner = $this->uid;
				$message->insertIfNotExist();
			}
		}
	}
}
