<?php

require('../../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('TwitterApiRefresh')));

$oTplPerson = new TplFrtPerson();
$html = $oTplPerson->getLightboxMentions24($_GET['twitter_user']);
echo $html;
