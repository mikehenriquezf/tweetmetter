<?php

require('../../include/include.php');

$oPersons = new DbaFrtPerson();
$oPersons->synchronizeFollowers();
