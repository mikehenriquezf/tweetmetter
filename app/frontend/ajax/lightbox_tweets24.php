<?php

require('../../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('TwitterApiRefresh')));

$twitter = filter_var($_GET['twitter_user'], FILTER_SANITIZE_STRING);

$oTplPerson = new TplFrtPerson();
$html = $oTplPerson->getLightboxTweets24($twitter);
echo $html;
