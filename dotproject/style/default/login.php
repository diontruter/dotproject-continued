<?php /* $Id$ */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<title><?php echo $AppUI->cfg['company_name'];?> :: dotProject Login</title>
	<meta http-equiv="Pragma" content="no-cache" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>
</head>

<body bgcolor="#f0f0f0" onload="document.loginform.username.focus();">
<br /><br /><br /><br />
<form action="./index.php" method="post" name="loginform">
<table align="center" border="0" width="250" cellpadding="6" cellspacing="0" class="std">
<input type="hidden" name="login" value="<?php echo time();?>" />
<tr>
	<th colspan="2"><em><?php echo $AppUI->cfg['company_name'];?></em></th>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('Username');?>:</td>
	<td align="left" nowrap><input type="text" size="25" name="username" class="text" /></td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('Password');?>:</td>
	<td align="left" nowrap><input type="password" size="25" name="password" class="text" /></td>
</tr>
<tr>
	<td align="left" nowrap><a href="http://www.dotproject.net/"><img src="./style/default/images/dp_icon.gif" width="120" height="20" border="0" alt="dotProject logo" /></a></td>
	<td align="right" valign="bottom" nowrap><input type="submit" name="login" value="<?php echo $AppUI->_('login');?>" class="button" /></td>
</tr>
</table>
</form>
<div align="center">
<?php
	echo '<span class="error">'.$AppUI->getMsg().'</span>';

	$msg = '';
	$msg .= ini_get( 'register_globals') ? '' : '<br /><span class="warning">WARNING: dotproject has not been fully tested with register_globals=off</span>';
	$msg .=  phpversion() < '4.1' ? '<br /><span class="warning">WARNING: dotproject is NOT SUPPORT for this PHP Version ('.phpversion().')</span>' : '';
	echo $msg;
?>
</div>
</body>
</html>
