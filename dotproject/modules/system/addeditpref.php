<?php
##
## add or edit a user preferences
##
$user_id = isset($HTTP_GET_VARS['user_id']) ? $HTTP_GET_VARS['user_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	$AppUI->redirect( 'm=help&a=access_denied' );
}

// load the preferences
$sql = "
SELECT pref_name, pref_value
FROM user_preferences
WHERE pref_user = $user_id
";
$prefs = db_loadHashList( $sql );

// get the user name
$sql = "
SELECT user_first_name, user_last_name
FROM users
WHERE user_id = $user_id
";
$res  = db_exec( $sql );
echo db_error();
$user = db_fetch_row( $res );

$crumbs = array();
$crumbs["?m=system"] = "System Admin";
?>
<script language="javascript">
function submitIt(){
	var form = document.changeuser;
	//if (form.user_username.value.length < 3) {
	//	alert("Please enter a valid user name");
	//	form.user_username.focus();
	//} else {
		form.submit();
	//}
}
</script>

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td valign="top"><img src="./images/icons/preference.gif" alt="" border="0" width="32" height="32"></td>
	<td nowrap>
		<span class="title">
		<?php echo count( $prefs ) ? "Edit User Preferences" : "Add User Preferences" ;?>
		</span>
	</td>
	<td valign="top" align="right" width="100%">&nbsp;</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="1" cellspacing="1" class="std">

<form name="changeuser" action="./index.php?m=system" method="post">
<input type="hidden" name="pref_user" value="<?php echo $user_id;?>">
<input type="hidden" name="dosql" value="preference_aed">
<input type="hidden" name="del" value="0">

<tr height="20">
	<th colspan="2"><?php echo $AppUI->_('User Preferences');?>:
	<?php
		echo $user_id ? "$user[0] $user[1]" : "Default";
	?></th>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Locale');?>:</td>
	<td>
<?php
	echo arraySelect( $AppUI->locales, 'pref_name[LOCALE]', 'class=text size=1', @$prefs['LOCALE'] );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Tabbed Box View');?>:</td>
	<td>
<?php
	$tabview = array( 'either', 'tabbed', 'flat' );
	echo arraySelect( $tabview, 'pref_name[TABVIEW]', 'class=text size=1', @$prefs['TABVIEW'], true );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Short Date Format');?>:</td>
	<td>
<?php
	$formats = array(
		"%d/%m/%Y"=>"dd/mm/yyyy   31/12/2002",
		"%d/%b/%Y"=>"dd/mmm/yyyy  31/Dec/2002",
		"%m/%d/%Y"=>"mm/dd/yyyy   12/31/2002",
		"%b/%d/%Y"=>"mmm/dd/yyyy  Dec/31/2002"
	);
	echo arraySelect( $formats, 'pref_name[SHDATEFORMAT]', 'class=text size=1', @$prefs['SHDATEFORMAT'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('User Interface Style');?>:</td>
	<td>
<?php
	echo arraySelect( $AppUI->styles, 'pref_name[UISTYLE]', 'class=text size=1', @$prefs['UISTYLE'] );
?>
	</td>
</tr>


<tr>
	<td align="left">&nbsp; &nbsp; &nbsp;<input class=button  type=button value="back" onClick="javascript:history.back(-1);"></td>
	<td align="right"><input type=button value="submit" onClick="submitIt()" class=button>&nbsp; &nbsp; &nbsp;</td>
</tr>
</table>
