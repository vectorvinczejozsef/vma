<?php
require 'restclient.php';
require 'Connect.php';

$errors = '';
$serverErrors = '';
$serverName = "10.14.3.35";
$uid = "vma_mobil";
$pwd = "v1ct0r";
$databaseName = "vma_mobil";

$connectionInfo = new Connect($serverName, $databaseName, $uid, $pwd);
$conn = $connectionInfo->StartConnection();
$Appverson = '1';
if (isset($_GET["appver"])) {
    $Appverson = $_GET["appver"];
}

/**
 * @param $errorMsg
 * @return string
 */
function errorAlerts($errorMsg)
{
    return "<div style='padding-left: 2%; padding-right: 2%'>
                  <div class='alert alert-danger alert-dismissible fade show' role='alert' style='margin: 0.5%'>
                       <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                             <span aria-hidden='true'>&times;</span>
                        </button>
                        <strong><i class='fa fa fa-info-circle' style='color: dodgerblue; font-size: large; cursor: pointer' onclick='errorInfo()'></i>&nbsp;$errorMsg</strong>  
                  </div>
             </div>";
}

$resp = "";
$dt = new DateTime('UTC');
$hour = time() + 3600 * 24 * 30;
$dieTime = time() - 3600;
if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['submit']) and $logout != 1) {
    if ($_POST['username'] == '') {
        $errors .= errorAlerts('Az azonosító mező nem lehet üres!');
    }

    if ($_POST['password'] == '') {
        $errors .= errorAlerts('A jelszó mező nem lehet üres!');
    }

    if ($_POST['company'] == '') {
        $errors .= errorAlerts('Az ügyfélkód nem lehet üres!');
    }

    if ($errors == "") {
        $params = array($_POST['company']);
        $sql = "SELECT * FROM user_auth WHERE  company_name = ?";
        $stmt = sqlsrv_query($conn, $sql, $params);
        $errors .= errorAlerts('Hibás ügyfélkód!');
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $errors = '';
            $api = new RestClient([
                'curl_options' => [
                    'CURLOPT_CONNECTTIMEOUT' => 10, //kérés időkorlát 10 sec
                    'CURLOPT_TIMEOUT' => 30, //adott művelet írás/olvasás max ideje sec
                ]
            ]);
            try {

                $result = $api->post('http://vmd.vector.hu/public/api/auth', [
                    'appversion' => $Appverson, 'timestamp' => $dt->format("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR'], 'agent' => $_SERVER['HTTP_USER_AGENT'],
                    'username' => $_POST['username'], 'password' => $_POST['password'], 'token' => $_POST['token'], 'loginpage' => $_SERVER['PHP_SELF'], 'secretkey' => $row['secretkey'], 'serviceUrl' => $row['url']
                ]);
                $result->decode_response();

                $resp = $result->decoded_response;
                print_r($resp);
            } catch (RestClientException $ex) {
                $errors .= errorAlerts($result->response_status_lines[0]);
                print_r($result);
            }
            $serverErrors .= errorAlerts($resp->message);
            setcookie('token', $resp->token, $hour);
            if (empty($resp)) {
                $errors .= errorAlerts('A túloldal nem válaszol!');
            } else if ($resp->online && $resp->user_allowed && !empty($resp->url) && !empty($resp->token)) {
                if ($_POST["remember_me"] == '1' || $_POST["remember_me"] == 'on') {
                    setcookie('remember', true, $hour);
                    setcookie('username', $_POST['username'], $hour);
                    setcookie('password', $_POST['password'], $hour);
                    setcookie('companyId', $_POST['company'], $hour);
                    setcookie('token', $resp->token, $hour);

                } else {
                    setcookie("remember", false, $dieTime);
                    setcookie("username", "", $dieTime);
                    setcookie("companyId", "", $dieTime);
                    setcookie("password", "", $dieTime);
                    setcookie('token', $resp->token, $dieTime);

                }
                $api->post($resp->url, [
                    'appversion' => $Appverson, 'timestamp' => $dt->format("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR'], 'agent' => $_SERVER['HTTP_USER_AGENT'],
                    'username' => $_POST['username'], 'password' => $_POST['password'], $resp->token, 'loginpage' => $_SERVER['PHP_SELF'], 'secretkey' => $row['secretkey']
                ]);
                header('Location: ' . $resp->url . '?token=' . $resp->token);
            } else if ($resp->message != '') {
                $errors .= errorAlerts($resp->message);
            }
        }
    }

}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link rel="stylesheet" type="text/css" href="custom.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</head>
<body>

<div class="modal text-center" id="myModal" style="position: relative; margin: auto;">
    <div class="modal-dialog">
        <div class="modal-content">
            <p><strong>Egy pillanat...</strong></p>
            <i class="fa fa-circle-o-notch fa-spin" style="font-size:72px;color:#9ac553"></i>
            <p style="color:#9ac553"><strong>A Vectory Mobility hamarosan betölt!</strong></p>
        </div>
    </div>
</div>
<div class="wrapper fadeInDown" id="loginDiv">
    <div id="formContent">
        <div id="sig-in">
            <div>
                <img src="img/vectory-logo.png" id="icon" alt="User Icon"/>
            </div>
            <div id="errordiv"><?php echo $errors; ?></div>
            <form action="" method='post' id="loginForm">
                <input type="text" hidden id="token" name="token" value="<?php echo $_COOKIE['token'] ?>">
                <input type="text" id="company" class="fadeIn first" name="company" placeholder="Azonosító"
                       value="<?php echo $_COOKIE['companyId'] ?>">
                <input type="text" id="username" class="fadeIn first" name="username" placeholder="Felhasználónév"
                       value="<?php echo $_COOKIE['username'] ?>">
                <input type="password" id="password" class="fadeIn first" name="password" placeholder="Jelszó"
                       value="<?php echo $_COOKIE['password'] ?>"><br>
                <input type="checkbox" name="remember_me" class="fadeIn first" value="on"
                       checked="<?php echo $_COOKIE['remember'] ?>">&nbsp;Jegyezz
                meg!<br>
                <br>
                <a href="#" class="float" id="floatBtnlogin" onclick="document.getElementById('submit').click()">
                    <i class="fa fa fa-sign-in my-float" style="font-size: 16px"></i>
                </a>
                <h1 class="kreep">
                    <button class="button button" type="submit" onclick="waiting()" name="submit" id="submit"><b>Bejelentkezés</b>&nbsp;<i
                                class="fa fa-sign-in"></i></button>
                </h1>
            </form>
        </div>
    </div>
</div>



<script>

    $(document).ready(function(){
        jQuery('#myModal').modal('hide');

        window.setTimeout( errorRemove, 3000 );
    });

    function errorRemove() {
        var myNode = document.getElementById("errordiv");
        while (myNode.firstChild) {
            myNode.removeChild(myNode.firstChild);
        }
    }
    function errorInfo() {
        $(function () {
            $("#errorDialog").dialog({
                resizable: true,
                classes: {
                    "ui-dialog": "highlight"
                },
                height: "auto",
                width: 300,
                modal: true,
                title: 'Segítség',
                buttons: {
                    "Oké": function () {
                        $(this).dialog("close");
                    }
                }
            });
        });
        jQuery("#errorDialog").text('Keresse fel rendszergazdáját!');
    }

    function waiting() {
        if(!($('#company').val() == "" || $('#username').val() == "" || $('#password').val() == ""))
        {
            jQuery('#myModal').modal('show');
            $('#sig-in').hide();
        }
    }


</script>
<div id="errorDialog">
</div>
<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>


