<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
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
$auth = $_GET["auth"];
$method = $_GET["method"];
$id = $_GET["id"];
$show = $_GET["show"];
$page = $_GET["page"];
$lessons = $_GET["lessons"];
$status = $_GET["status"];
$query = $_GET['query'];

if ($lessons == "ok") {
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
} else {}

//Обновление статуса юзера
if ($status == "ok") {
    $offline = $mysqli->query("UPDATE users SET status = 0");
    $current_time = time()-3600;
    $online = $mysqli->query("UPDATE users JOIN last_time ON users.id_fb= last_time.id_fb SET users.status = 1 WHERE last_time.time > '".$current_time."' ");
}

//Аутентификация
if ($auth != "wr67uythjty65e6hjrtdh4wy") {
    $error = array("error" => "Invalid token");
    $json = json_encode($error);
    echo $json;

    exit();
}

if($method == "users") {
    $result = $mysqli->query("SELECT * FROM users");

    $row2=[];
    while ($rrr = $result->fetch_assoc()){
        $row2[]=$rrr;
    }

    $user_array = [];
    foreach ($row2 as $item=>$value){
        $one_user = [];
        $one_user["first_name"]=$value["first_name"];
        $one_user["last_name"]=$value["last_name"];
        $one_user["id_fb"]=$value["id_fb"];
        $one_user["profile_pic"]=$value["profile_pic"];
        $one_user["reg_time"]=$value["reg_time"];
        $one_user["status"]=(int)$value["status"];
        $user_array[]=$one_user;
    }

    $json = json_encode($user_array);
    echo $json;
} elseif($method == "chats" && $id != "") {

    $result = $mysqli->query("SELECT * FROM chats WHERE id_fb = '".$id."'");

    $row2 = [];
    while ($rrr = $result->fetch_assoc()) {
        $row2[] = $rrr;
    }

    $user_array = [];
    foreach ($row2 as $item => $value) {
        $one_user = [];
        $one_user["message"] = $value["message"];
        $one_user["isUser"] = (int)$value["isUser"];
        $one_user["date"] = $value["date"];
        $user_array[] = $one_user;
    }
    $result = $mysqli->query("SELECT * FROM users WHERE id_fb = '".$id."'");
    $row = $result->fetch_assoc();

    $chat_array = array("username" => $row['first_name'] . " " . $row['last_name'],
        "avatar" => $row['profile_pic'],
        "messages" => $user_array);

    $json = json_encode($chat_array);
    echo $json;

} elseif($method == "marketing_count" && ($show == 10 || $show == 25 || $show == 50 || $show == 100)) {
    $result = $mysqli->query("SELECT id FROM users");
    $users_array = $result->fetch_all();
    $count = array('count' => ceil(count($users_array)/$show));
    $json = json_encode($count);
    echo $json;

} elseif($method == "users_page" && $page > 0 && ($show == 10 || $show == 25 || $show == 50 || $show == 100)) {
    $result = $mysqli->query("SELECT id FROM users");
    $users_array = $result->fetch_all();
    for ($i = ($page * $show) - $show; $i < $page * $show; $i++) {
        if($i < count($users_array)) {
            $result = $mysqli->query("SELECT * FROM users WHERE id = ".$users_array[$i][0]." ");
            $user_info = $result->fetch_assoc();
            $users[$i] = array("full_name" => $user_info['first_name'] . " " . $user_info['last_name'],
                "native" => $user_info['locale'],
                "gender" => $user_info['gender'],
                "platform" => "FBM",
                "platform_id" => "abcede12345",
                "first_seen" => $user_info['reg_time']);
        }
    }
    $json = json_encode($users);
    echo $json;
} elseif ($method == "search" && $query != "" && ($show == 10 || $show == 25 || $show == 50 || $show == 100)) {
    $query = explode(' ',$query);

    $sql = "SELECT id FROM `users` WHERE `full_name` LIKE '%".$query[0]."%'";
    for ($i = 1; $i < count($query); $i++) {
        $sql = $sql . "and `full_name` LIKE '%" . $query[$i] . "%'";
    }
    $result = $mysqli->query($sql);
    $search_array = $result->fetch_all();
    $count = array('count' => ceil(count($search_array)/$show));
    $json = json_encode($count);
    echo $json;

} elseif($method == "search_page" && $query !=" " && $page > 0 && ($show == 10 || $show == 25 || $show == 50 || $show == 100)) {
    $query = explode(' ',$query);

    $sql = "SELECT id FROM `users` WHERE `full_name` LIKE '%".$query[0]."%'";
    for ($i = 1; $i < count($query); $i++) {
        $sql = $sql . "and `full_name` LIKE '%" . $query[$i] . "%'";
    }
    $result = $mysqli->query($sql);
    $search_array = $result->fetch_all();
    for ($i = ($page * $show) - $show; $i < $page * $show; $i++) {
        if($i < count($search_array)) {
            $result = $mysqli->query("SELECT * FROM users WHERE id = ".$search_array[$i][0]." ");
            $user_info = $result->fetch_assoc();
            $users[$i] = array("full_name" => $user_info['first_name'] . " " . $user_info['last_name'],
                "native" => $user_info['locale'],
                "gender" => $user_info['gender'],
                "platform" => "FBM",
                "platform_id" => "abcede12345",
                "first_seen" => $user_info['reg_time']);
        }
    }
    $json = json_encode($users);
    echo $json;

} else {
    $error = array("status" => "0",
        "error" => "Bad Request");

    $json = json_encode($error);
    echo $json;
}


