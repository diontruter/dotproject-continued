<?php
include "./includes/config.php";
include "./includes/db_connect.php";

//Check Login
$psql = "select user_id, user_first_name, user_last_name
					from users,  permissions
					where user_username = '$username'
					and user_password = password('$password') 
					and users.user_id = permissions.permission_user
					and permission_value <> 0
					";
$prc = mysql_query($psql);

//Pull record, write bad login if exists
if (!$row = mysql_fetch_array($prc)) {
	$message  = "Login Failed.";
	include "./includes/login.php";
	die;
}

setcookie( "user_cookie", $row[0] );
setcookie( "user_full_name", $row[1].' '.$row[2] );
?>
<script language="JavaScript">
window.location = "./index.php"
</script>
