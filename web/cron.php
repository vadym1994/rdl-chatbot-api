<?php

$dbHost = 'ehc1u4pmphj917qf.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';// чаще всего это так, но иногда требуется прописать ip адрес базы данных
$dbName = 'l6beyt7vz29lu019';// название вашей базы
$dbUser = 'ow1b2dd09o84kcod';// пользователь базы данных
$dbPass = 'righrl209f0to2id';// пароль пользователя
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset("utf8");

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$url = "https://rdl-dashboard.herokuapp.com/service/bot/lessons?key=f6fefb26e8cb1c9e4447de22decc8d6ea4e088d5d9ecc4e0d9c0b09e7c5d91a8107b3726900369b184c89d71f101944023f5470412703ce4031696dc07aa4e872946119f00ca19f42fe95426a1f0862c7d75";

$lessons = file_get_contents($url);
$lessons = json_decode($lessons);

for($i = 0; $i < count($lessons); $i++ ) {
    $id = $lessons[$i]->_id;
    $image = $lessons[$i]->image;
    $title = $lessons[$i]->label;
    $descr = $lessons[$i]->description;
    $account = $lessons[$i]->account;
    $full_lesson = $lessons[$i]->full_lesson;
    $dialog = $lessons[$i]->dialog;
    $release_date = $lessons[$i]->release_date;
    $startModule = $lessons[$i]->startModule;
    $lang = $lessons[$i]->lang;

    $result = $mysqli->query("SELECT * FROM lesson WHERE `_id` = '".$id."' ");
    $row = $result->fetch_assoc();

    if(!(count($row) > 0) ){
        $sql  = "INSERT INTO `lesson` SET ";
        $sql  .= " `_id` = '".$id."' ,";
        $sql  .= " `image` = '".$image."' ,";
        $sql  .= " `title` = '".$title."' ,";
        $sql  .= " `descr` = '".$descr."' ,";
        $sql  .= " `account` = '".$account."' ,";
        $sql  .= " `full_lesson` = '".$full_lesson."' ,";
        $sql  .= " `dialog` = '".$dialog."' ,";
        $sql  .= " `release_date` = '".$release_date."' ,";
        $sql  .= " `startModule` = '".$startModule."' ,";
        $sql  .= " `lang` = '".$lang."' ";
        $result = $mysqli->query($sql);
    }else{
        $sql  = "UPDATE `lesson` SET ";
        $sql  .= " `image` = '".$image."' ,";
        $sql  .= " `title` = '".$title."' ,";
        $sql  .= " `descr` = '".$descr."' ,";
        $sql  .= " `account` = '".$account."' ,";
        $sql  .= " `full_lesson` = '".$full_lesson."' ,";
        $sql  .= " `dialog` = '".$dialog."' ,";
        $sql  .= " `release_date` = '".$release_date."' ,";
        $sql  .= " `startModule` = '".$startModule."' ,";
        $sql  .= " `lang` = '".$lang."' ";
        $sql  .= "WHERE `_id` = '".$id."'";
        $result = $mysqli->query($sql);
    }
}
