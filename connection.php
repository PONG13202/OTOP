<?php
session_start();
header("Content-Type:text/html;charset=utf-8");

$host = "nozomi.proxy.rlwy.net";
$user = "root";
$pass = "RCzloYIVFpRXDRIAMThtJnJTAsllQDBg";
$db   = "railway";
$port = 28785;

$project_connect = mysqli_connect($host,$user,$pass,$db,$port);

if(!$project_connect){
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($project_connect,"utf8");
date_default_timezone_set('Asia/Bangkok');
?>