<?php

require('../app/libs/lgAPI.lib.php');

$init = new lgAPI('http://lg.api/api/v1', "odske710r3KyS8nIjV82L6SS8e32X5zCKnIjV82L6S44odske710r3Kye32X5zCK", array());

var_dump($init->sendCommand('ping', '8.8.8.8')->getJsonResponse());
var_dump(dns_get_record("google.com", DNS_A)[0]['ip']);