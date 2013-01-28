<?php

require('../../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('StatisticsTTL')));

$oTpl = new TplFrtGeneral();
echo $oTpl->getHomeHTML();