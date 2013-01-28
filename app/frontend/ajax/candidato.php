<?php

require('../../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('StatisticsTTL'))." GMT");

$twitter = filter_var($_GET['twitter'], FILTER_SANITIZE_STRING);
$twitter = str_replace('(', '', $twitter);
$twitter = str_replace(')', '', $twitter);

$oTpl = new TplFrtPerson();
echo $oTpl->getHomeHTML($twitter);
