<?php

if(!empty($_GET['code'])){
    file_put_contents('code.txt',$_GET['code']);
}