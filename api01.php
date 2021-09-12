<?php

include 'Drive.php';

if(!empty($_GET['getcode'])){
    //get auth code
    Drive::getCode(CLIENT_ID,REDIRECT_URI,SCOPE,'code',['access_type'=>'offline']);
}elseif(!empty($_GET['gettoken'])){

    //get access token
    echo Drive::getAccessToken(CLIENT_ID,CLIENT_SECRET,file_get_contents('code.txt'),REDIRECT_URI);
}elseif(!empty($_GET['simpleupload'])){

    //simple upload file by size less than 5 mb
    echo Drive::simpleUpload($_GET['simpleupload']);

}elseif(!empty($_GET['permission'])){

    //change file permissions

    if($_GET['role'] && $_GET['type'] && $_GET['fileid']){
        echo Drive::makePermissianFile($_GET['role'],$_GET['type'],$_GET['fileid']);
    }else{
        echo json_encode(['ok'=>false,'message'=>'enter role and type and fileid parameters']);
    }
    
}