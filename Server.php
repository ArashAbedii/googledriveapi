<?php


class Server{

    private static $err=false; 

    //USAGE SEND REQUEST FUNCTION

    public static function sendRequest($url,array $params=[],$type='get',array $headers=[]){

        //check valid data

        self::validateUrl($url);

        self::validHeaders($headers);

        self::validParams($params);

        self::validType($type);



        if(self::$err==true){

            echo "ERR! Check ErrHandler.log file on your project directory\n";

            return FALSE;

        }



        if(strtolower($type)=='get'){

            //GET REQUESTS

            return self::sendGetRequests($url,$params,$headers);



        }elseif(strtolower($type)=='post'){

            //SEND POST REQUESTS

            return self::sendPostRequests($url,$params,$headers);

        }

    }

    //SEND REQUEST WITHOUT RESPONSE

    public static function sendRequestWithoutResponse($url,$params=[]){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,false);
        curl_setopt($ch,CURLOPT_TIMEOUT,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,1);
        curl_exec($ch);
        curl_close($ch);
    }


    //send file
    public static function sendFileInBody($url,$fileName,array $headers=[]){

        //SEND CURL

        $ch=curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);

        //get binery file
        $file=file_get_contents($fileName);

        curl_setopt($ch,CURLOPT_POSTFIELDS,$file);

        

        if($headers){

            $headers=self::changeArrayToHeaderFormat($headers);

            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

        }

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        $result=curl_exec($ch);

        curl_close($ch);

        return $result;
    }




    //GET REQUEST ACTION

    public static function sendGetRequests($url,array $params,array $headers=[]){

        $url=$url.self::changeArrayToGetFormat($params);

        //echo $url;

        //SEND CURL

        $ch=curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);

        if($headers){

            $headers=self::changeArrayToHeaderFormat($headers);

            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

        }

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        $result=curl_exec($ch);

        curl_close($ch);

        return $result;



    }





    //POST REQUEST ACTION

    public static function sendPostRequests($url,array $params,array $headers=[]){


        //SEND CURL

        $ch=curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);

        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if($headers){

            $headers=self::changeArrayToHeaderFormat($headers);

            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

        }

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        $result=curl_exec($ch);

        curl_close($ch);

        return $result;
    }


    public static function curl($url,$params=[],$headers=[],$requestType='POST',$header=false){

        //SEND CURL

        $ch=curl_init();
        
        curl_setopt($ch,CURLOPT_URL,$url);

        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$requestType);

        curl_setopt($ch,CURLOPT_POSTFIELDS,$params);



        if($headers){

            $headers=self::changeArrayToHeaderFormat($headers);

            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

        }

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        if($header){
            curl_setopt($ch, CURLOPT_HEADER,1);
            $result=curl_exec($ch);
            
            return self::get_headers_from_curl_response($result);
        }else{
            $result=curl_exec($ch);
            return $result;
        }
        curl_close($ch);
    }


    public static function get_headers_from_curl_response($response)
    {
        $headers = array();
    
        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
    
        foreach (explode("\r\n", $header_text) as $i => $line)
            if ($i === 0)
                $headers['http_code'] = $line;
            else
            {
                list ($key, $value) = explode(': ', $line);
    
                $headers[$key] = $value;
            }
    
        return json_encode($headers);
    }


    //MAKING VALID FORMAT



    public static function changeArrayToGetFormat($params){

        $output=''; 

        foreach($params as $key=>$value){
            $value=str_replace(" ","%20",$value);
            $output.=$key."=".$value."&";

        }

        return "?".trim($output,"&"); //return GET format (?a=1&b=2&c=3)

    }



    public static function changeArrayToHeaderFormat($headers){

        $output=array();

        foreach($headers as $key=>$value){

            $output[]="$key: $value";

        }

        return $output; //return array of headers format (["Referer: https://www.google.com/","Content-type: audio/mpeg"])

    }





    //CHECK VALIDATE INPUTS

    public static function validateUrl($url){

        if(empty($url)){

            file_put_contents("ErrHandler.log","\nERR MESSAGE: Url required !\t".date("d M Y H:i:s"),FILE_APPEND);

            self::$err=true;

        }

        if(!filter_var($url,FILTER_VALIDATE_URL)){

            file_put_contents("ErrHandler.log","\nERR MESSAGE: Invalid url format !\t".date("d M Y H:i:s"),FILE_APPEND);

            self::$err=true;

        }

    }



    public static function validParams($params){

        if(!is_array($params)){

            file_put_contents("ErrHandler.log","\nERR MESSAGE: Invalid params format! params format should be an array\t".date("d M Y H:i:s"),FILE_APPEND);

            self::$err=true;

        }

    }



    public static function validHeaders($headers){

        if(!is_array($headers)){

            file_put_contents("ErrHandler.log","\nERR MESSAGE: Invalid headers format! headers format should be an array\t".date("d M Y H:i:s"),FILE_APPEND);

            self::$err=true;

        }

    }



    public static function validType($reqtype){

        if(empty($reqtype)){

            file_put_contents("ErrHandler.log","\nERR MESSAGE: Request type required! put GET or POST or etc type\t".date("d M Y H:i:s"),FILE_APPEND);

            self::$err=true;

        }

    }







}
