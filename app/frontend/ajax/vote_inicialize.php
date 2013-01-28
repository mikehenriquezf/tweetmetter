<?php

require('../../../include/include.php');

header("Cache-Control: no-cache, must-revalidate");

$fbk_signature = $_GET['fbk_signature'];
$fbk_data = $_GET['fbk_data']; // Will be sanitized in DbaFrtVoter by each field

$oVoters = new DbaFrtVoter();
$data = $oVoters->voteInicialize($fbk_signature, $fbk_data);

if ($data) {
	echo json_encode($data);
} else {
	die('ERROR');
}
