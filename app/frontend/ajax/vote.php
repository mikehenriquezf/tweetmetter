<?php

require('../../../include/include.php');

header("Cache-Control: no-cache, must-revalidate");

$fbk_id = filter_var($_GET['fbk_id'], FILTER_SANITIZE_STRING);
$token = filter_var($_GET['token'], FILTER_SANITIZE_STRING);
$id_person = filter_var($_GET['id_person'], FILTER_SANITIZE_NUMBER_INT);
$vote = $_GET['vote'];

$oVoters = new DbaFrtVoter();
$response = $oVoters->vote($fbk_id, $token, $id_person, $vote);
echo json_encode($response);
