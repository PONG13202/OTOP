<?php
session_start();
header("Content-Type:text/html;charset=utf-8");

$host = "nozomi.proxy.rlwy.net";
$user = "root";
$pass = "RCzloYIVFpRXDRIAMThtJnJTAsllQDBg";
$db   = "railway";
$port = "28785";

$project_connect = mysqli_connect($host,$user,$pass,$db,$port);

if(!$project_connect){
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($project_connect,"utf8");
date_default_timezone_set('Asia/Bangkok');
?>


<!-- <?php 
session_start();
header("Content-Type:text/html;charset=utf-8");

$project_connect = mysqli_connect("localhost","chanathip","pong1234","ayutthaya");

if(!$project_connect){
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($project_connect, "SET NAMES 'utf8'");
mysqli_query($project_connect, "SET character_set_results = 'utf8'");
mysqli_query($project_connect, "SET character_set_client = 'utf8'");
mysqli_query($project_connect, "SET character_set_connection = 'utf8'");
date_default_timezone_set('Asia/Bangkok');

?> -->