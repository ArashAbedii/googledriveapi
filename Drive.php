<?php

use Server;

class Drive {

    public static $clientId='1012604247986-7mmsi3hf2kojed8s21mapfst7afhj21g.apps.googleusercontent.com';
    public static $clientSecret='0S9jUf6i3ahuHz5rbO3dttTT';
    public static $redirectUri='http://localhost:8000/callback.php';
    

    public static function getCode($scope='https://www.googleapis.com/auth/drive.file',array $options=[]){

        //optional
        if(file_exists('auth.json')){
            echo 'you got your auth before.you dont need do this again';
            return;
        }

        $baseUrl="https://accounts.google.com/o/oauth2/v2/auth";
        $formParams=$options;
        $formParams['client_id']=self::$clientId;
        $formParams['redirect_uri']=self::$redirectUri;
        $formParams['scope']=$scope;
        $formParams['response_type']='code';
        $formParams['access_type']='offline';

        $reqUrl=$baseUrl.Server::changeArrayToGetFormat($formParams);

        return $reqUrl;
        
    }


    public static function getAccessToken($code){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=self::$clientId;
        $formParams['redirect_uri']=self::$redirectUri;
        $formParams['code']=$code;
        $formParams['client_secret']=self::$clientSecret;
        $formParams['grant_type']='authorization_code';
        
        $response=json_decode(Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']));

        //optional
        file_put_contents('auth.json',json_encode(['access_token'=>$response->access_token,'refresh_token'=>$response->refresh_token,'token_life_time'=>time()+$response->expires_in]));
        
        return json_encode($response);
    }


    public static function refreshToken($refresh_token){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=self::$clientId;
        $formParams['refresh_token']=$refresh_token;
        $formParams['client_secret']=self::$clientSecret;
        $formParams['grant_type']='refresh_token';
        $response=Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']);
        
        return $response;
    }


    public static function simpleUpload($file='file url or path'){

        //api base url
        $baseUrl="https://www.googleapis.com/upload/drive/v3/files?uploadType=media";

        $headers['Authorization']=self::generateAuth();

        //get file mime type
        $urlInfo=get_headers($file,1);
        $headers['Content-Type']=$urlInfo['Content-Type'];


        //get file size
        $headers['Content-Length']=$urlInfo['Content-Length'];

        $response=Server::sendFileInBody($baseUrl,$file,$headers);

        return $response;
    
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

        //get auth data
        $auth=json_decode(file_get_contents('auth.json'));
        $refresh_token=$auth->refresh_token;
        $token_life_time=$auth->token_life_time;
        $access_token=$auth->access_token;

        //check token expired or not
        if($token_life_time<time()){
            //refresh token
            $response=json_decode(Drive::refreshToken($refresh_token));
            if(empty($response->error)){
                return "Bearer ".$response->access_token;
            }
            return false;
            
        }

        //return access token
        return "Bearer ".$access_token;

    }

    public static function resumableUpload($file,$title='app.jpg'){
        $baseUrl1='https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable';

        $headers['Authorization']=self::generateAuth();
        
        $headers['Content-Type']="application/json; charset=UTF-8";

        $body['name']=$title;

        $response=json_decode(Server::curl($baseUrl1,json_encode($body),$headers,'post',true));
        

        if(empty($response->location)){
            return false;
        }

        //get file mime type
        $urlInfo=get_headers($file,1);
        $headers2['Content-Length']=$urlInfo['Content-Length'];
        $response=Server::curl($response->location,file_get_contents($file),$headers2,'PUT');
        
        return $response;
        
        
    }

    public static function getAboutMe($accessToken){
        $baseUrl="https://www.googleapis.com/drive/v2/about";
        $headers['Authorization']="Bearer $accessToken";
        $response=Server::sendRequest($baseUrl,[],'get',$headers);
        
        return $response;
    }

    public static function revoke($accessToken){
        $baseUrl="https://oauth2.googleapis.com/revoke";
        $formParams['token']=$accessToken;
        $headers['Content-type']="application/x-www-form-urlencoded";
        $response=Server::sendRequest($baseUrl,$formParams,'post',$headers);
        
        return $response;
    }
}