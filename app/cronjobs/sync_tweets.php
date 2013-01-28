<?php

require('../../include/include.php');

$oTwitter = new DbaFrtTwitter();
$oTwitter->synchronizeTweets();
