<?php

	header("Cache-Control: no-cache, must-revalidate");

	if ($_SERVER['REMOTE_ADDR'] == '200.108.218.210' || $_GET['pwd'] == 'rfp') {
		$html = file_get_contents('http://localhost/server-status/');
		echo $html;
	}
