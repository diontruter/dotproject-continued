<?php
require_once( "./includes/config.php" );
require_once( "./includes/db_connect.php" );
require_once( "./classdefs/ui.php" );
session_start();
session_register('AppUI');

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$HTTP_SESSION_VARS['AppUI'] = new CAppUI;
$AppUI =& $HTTP_SESSION_VARS['AppUI'];

$ok = $AppUI->login( $username, $password );
if (!$ok) {
	$message = 'Login Failed';
	include "./includes/login.php";
	die;
}
echo '<script language="javascript">window.location = "./index.php";</script>';
?>
