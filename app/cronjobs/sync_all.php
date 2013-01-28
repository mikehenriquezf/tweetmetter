<?php

require('../../include/include.php');

$oSync = new DbaFrtSync();
echo "Result: ".$oSync->syncAll();