<?php

require('../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('TwitterApiRefresh')));

$hashtag = filter_var($_GET['hashtag'], FILTER_SANITIZE_STRING);

$oTpl = new TplFrtGeneral();

$oTplContainer = new TplFrtContainer();
$oTplContainer->setMainContent($oTpl->getTweetsPage(trim($hashtag)));
$oTplContainer->assign('DAX_CODE', "noticias.politica.prdecide-elgrandebate.tuits");
$oTplContainer->show();
