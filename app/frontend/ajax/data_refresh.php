<?php

require('../../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('AjaxTTL')));

$oStats = new DbaFrtStatistics();
echo json_encode($oStats->getStats());
