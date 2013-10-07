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

class Config {
/**
 * Gets the user value of the country code
 * @since 1.0
 */
	public static function CountryCode() {
		return \OCP\Config::getUserValue(\OCP\USER::getUser(), 'smstorage', 'countryCode', '+49');
	}
/**
 * Sets the user value of the country code
 * @param string $code The country code
 * @since 1.0
 */
	public static function setCountryCode($code) {
		return \OCP\Config::setUserValue(\OCP\USER::getUser(), 'smstorage', 'countryCode', $code);
	}
}