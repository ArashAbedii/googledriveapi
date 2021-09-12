<?php

require  'server/Server.php';
include 'constants.php';

class Drive {

    public static function getCode($clientId,$redirectUri,$scope,$responseType,array $options=[]){

        $baseUrl="https://accounts.google.com/o/oauth2/v2/auth";
        $formParams=$options;
        $formParams['client_id']=$clientId;
        $formParams['redirect_uri']=$redirectUri;
        $formParams['scope']=$scope;
        $formParams['response_type']=$responseType;

        $reqUrl=$baseUrl.Server::changeArrayToGetFormat($formParams);
        
        echo "<a href='$reqUrl'>Get Code</a>";
        
    }


    public static function getAccessToken($clientId,$clientSecret,$code,$redirectUri,$grandType='authorization_code'){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=$clientId;
        $formParams['redirect_uri']=$redirectUri;
        $formParams['code']=$code;
        $formParams['client_secret']=$clientSecret;
        $formParams['grant_type']=$grandType;

        $response=Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']);
        $dc=json_decode($response);
        file_put_contents('access_token.txt',$dc->access_token);
        file_put_contents('access_token_expire.txt',time()+$dc->expires_in);
        return $response;
    }


    public static function refreshToken($clientId,$clientSecret,$refresh_token,$grandType='refresh_token'){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=$clientId;
        $formParams['refresh_token']=$refresh_token;
        $formParams['client_secret']=$clientSecret;
        $formParams['grant_type']=$grandType;
        $response=Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']);
        $dc=json_decode($response);
        file_put_contents('access_token.txt',$dc->access_token);
        file_put_contents('access_token_expire.txt',time()+$dc->expires_in);
        return $response;
    }

    public static function getAccessTokenSimply(){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=CLIENT_ID;
        $formParams['refresh_token']=REFRESH_TOKEN;
        $formParams['client_secret']=CLIENT_SECRET;
        $formParams['grant_type']='refresh_token';
        $response=Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']);
        $dc=json_decode($response);
        file_put_contents('access_token.txt',$dc->access_token);
        file_put_contents('access_token_expire.txt',time()+$dc->expires_in);
        return $response;
    }

    
    public static function simpleUpload($file='file url or path'){

        //api base url
        $baseUrl="https://www.googleapis.com/upload/drive/v3/files?uploadType=media";

        $headers['Authorization']=self::generateAuth();


        //get file mime type
        $headers['Content-Type']=mime_content_type($file);




        //get file size
        $headers['Content-Length']=filesize($file);

        $response=Server::sendFileInBody($baseUrl,$file,$headers);
        echo $response;
    
    }


    public static function downloadFile($fileId){
        $baseUrl="https://www.googleapis.com/drive/v2/files/$fileId";

        $headers['Authorization']=self::generateAuth();

        $response=Server::sendRequest($baseUrl,[],'get',$headers);
        return $response;

    }

    public static function getFileDownloadLink($fileId){
        return "https://drive.google.com/uc?id=$fileId&export=download";
    }

    public static function makePermissianFile($role="owner or reader or writer ",$type="user or anyone",$fileId){
        //refrence https://developers.google.com/drive/api/v3/reference/permissions/create
        $baseUrl="https://www.googleapis.com/drive/v3/files/$fileId/permissions";
        $formParams['role']=$role;
        $formParams['type']=$type;
        $headers['Authorization']=self::generateAuth();
        $headers['Content-type']='application/json';

        $response=Server::curl($baseUrl,json_encode($formParams),$headers);
        echo $response;

    }


    public static function generateAuth(){

        //get last access token life time
        $access_token_expire=file_get_contents('access_token_expire.txt');

        //check access token life time
        if($access_token_expire<time()){
            //refresh token and get new access token
            self::getAccessTokenSimply();
        }

        //get access token
        $access_token=file_get_contents('access_token.txt');

        return "Bearer $access_token";
    }
}