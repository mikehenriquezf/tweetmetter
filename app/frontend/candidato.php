<?php

require('../../include/include.php');

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate ("D, d M Y H:i:s \G\M\T", time() + ThefConfig::get('StatisticsTTL')));

$twitter = filter_var($_GET['twitter'], FILTER_SANITIZE_STRING);
$twitter = str_replace('(', '', $twitter);
$twitter = str_replace(')', '', $twitter);

$oTpl = new TplFrtGeneral();

$oTplContainer = new TplFrtContainer();
$oTplContainer->setMainContent($oTpl->getHomeHTML($twitter));
$oTplContainer->assign('DAX_CODE', "noticias.politica.prdecide-elgrandebate.perfil.$twitter");
$oTplContainer->show();
