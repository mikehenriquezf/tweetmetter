<?php

require('../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('StatisticsTTL')));

$oTpl = new TplFrtGeneral();

$oTplContainer = new TplFrtContainer();
$oTplContainer->setMainContent($oTpl->getHomeHTML());
$oTplContainer->assign('DAX_CODE', 'noticias.politica.prdecide-elgrandebate.portada');
$oTplContainer->show();

