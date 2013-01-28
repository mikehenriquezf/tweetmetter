<?php

require('../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('TwitterApiRefresh')));

$word = filter_var($_GET['word'], FILTER_SANITIZE_STRING);
$oTpl = new TplFrtGeneral();

$oTplContainer = new TplFrtContainer();
$oTplContainer->setMainContent($oTpl->getKeywordsPage($word));
$oTplContainer->assign('DAX_CODE', "noticias.politica.prdecide-elgrandebate.wordcloud.$word");
$oTplContainer->show();
