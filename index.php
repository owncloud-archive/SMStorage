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

// Check if we are a user
\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('smstorage');

\OCP\App::setActiveNavigationEntry('smstorage');

\OCP\Util::addStyle('smstorage', 'app');
\OCP\Util::addStyle('smstorage', 'addresses');
\OCP\Util::addStyle('smstorage', 'bubbles');

\OCP\Util::addScript('files', 'jquery.fileupload');

\OCP\Util::addScript('smstorage', 'app');
\OCP\Util::addScript('smstorage', 'template');		// will be included in OC 6 or 7

$tmpl = new \OCP\Template('smstorage', 'app', 'user');

\OC_Hook::emit('OCA\SMStorage', 'pre_index_output', array( 'tmpl' => &$tmpl));

$tmpl->printPage();
