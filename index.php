<?php

require 'Server.php';
require 'Drive.php';

//echo Drive::getCode();
//echo Drive::simpleUpload('http://dl.mokhtalefmusic.com/Music/1395/02/25/Shahrum%20Kashani%20-%20Ye%20Lahze%20(128).mp3',Drive::generateAuth());
//Drive::revoke('ya29.a0ARrdaM9T187KDimBjNXkJoKpLsEMkt1oQegsdOUo7ya4nbKKNJZYsWo9Iw2oEd4Z_Pfr2hpCq9z5muvBOBvI-g3qpmacq4SApj8lIXdqKb4NPcISdTKAf5GinlKwk7OckMqL0QiXdcHD0iiHwmxVb0wiVP3sPA');
var_dump(Drive::resumableUpload('http://dl.mokhtalefmusic.com/Music/1395/02/25/Shahrum%20Kashani%20-%20Ye%20Lahze%20(128).mp3','my.mp3'));