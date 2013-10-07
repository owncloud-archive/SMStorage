<?php

namespace OCA\SMStorage;

\OCP\Util::addScript('smstorage', 'personal');
\OCP\Util::addScript('settings', 'personal');

$tmpl = new \OCP\Template('smstorage', 'personal');
$tmpl->assign('code', Config::CountryCode());

return $tmpl->fetchPage();