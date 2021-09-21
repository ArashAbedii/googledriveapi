<?php

if(empty($_GET['code'])){
    return 'code required!';
}

require 'Server.php';
require 'Drive.php';

echo Drive::getAccessToken($_GET['code']);