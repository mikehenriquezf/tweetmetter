<?php

require('../../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('TwitterApiRefresh')));

$word = filter_var($_GET['word'], FILTER_SANITIZE_STRING);
$oTpl = new TplFrtGeneral();
echo $oTpl->getKeywordsPage($word);

