<?php

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$user_id = isset( $HTTP_GET_VARS['user_id'] ) ? $HTTP_GET_VARS['user_id'] : 0;
// view mode = 0 tabbed, 1 flat
$vm = isset($HTTP_GET_VARS['vm']) ? $HTTP_GET_VARS['vm'] : 0;

// pull data
$usql = "
SELECT users.*, 
	company_id, company_name, 
	dept_name
FROM users
LEFT JOIN companies ON user_company = companies.company_id
LEFT JOIN departments ON dept_id = user_department
WHERE user_id = $user_id
";
$urc  = mysql_query( $usql );
$urow = mysql_fetch_array( $urc, MYSQL_ASSOC );
?>

<table border=0 cellpadding="1" cellspacing=1>
<tr>
	<td><img src="./images/icons/admin.gif" alt="" border="0"></td>
	<td nowrap><span class="title">View User</span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<tr>
	<td nowrap>
	<a href="./index.php?m=admin">user list</a>
<?php if (!$denyEdit) { ?>
	<b>:</b> <a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $user_id;?>">edit this User</a>
<?php } ?>
	</td>
	<td align="right" width="100%">
<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
<?php } ?>
	</td>
</tr>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="95%" class="std">
<tr valign="top">
	<td width="50%">
		<table width="100%">
		<tr>
			<td><b>Login Name:</b></td>
			<td><?php echo $urow["user_username"];?></td>
		</tr>
		<tr>
			<td><b>User Type:</b></td>
			<td><?php echo $utypes[$urow["user_type"]];?></td>
		</tr>
		<tr>
			<td><b>Full Name:</b></td>
			<td><?php echo $urow["user_first_name"].' '.$urow["user_last_name"];?></td>
		</tr>
		<tr>
			<td><b>Company:</b></td>
			<td><?php echo $urow["company_name"];?></td>
		</tr>
		<tr>
			<td><b>Department:</b></td>
			<td><?php echo $urow["dept_name"];?></td>
		</tr>
		<tr>
			<td><b>Phone:</b></td>
			<td><?php echo @$urow["user_phone"];?></td>
		</tr>
		<tr>
			<td><b>Home Phone:</b></td>
			<td><?php echo @$urow["user_home_phone"];?></td>
		</tr>
		<tr>
			<td><b>Mobile:</b></td>
			<td><?php echo @$urow["user_mobile"];?></td>
		</tr>
		<tr valign=top>
			<td><b>Address:</b></td>
			<td><?php
				echo @$urow["user_address1"]
					.( ($urow["user_address2"]) ? '<br>'.$urow["user_city"] : '' )
					.'<br>'.$urow["user_city"]
					.'&nbsp;&nbsp;'.$urow["user_state"]
					.'&nbsp;&nbsp;'.$urow["user_zip"]
					.'<br>'.$urow["user_coutnry"]
					;
			?></td>
		</tr>
		<tr>
			<td><b>Birthday:</b></td>
			<td><?php echo @$urow["user_birthday"];?></td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<table width="100%">
		<tr>
			<td><b>ICQ#:</b></td>
			<td><?php echo @$urow["user_icq"];?></td>
		</tr>
		<tr>
			<td><b>AOL Nick:</b></td>
			<td><?php echo @$urow["user_aol"];?></td>
		</tr>
		<tr>
			<td><b>E-Mail:</b></td>
			<td><?php echo '<a href="mailto:'.@$urow["user_email"].'">'.@$urow["user_email"].'</a>';?></td>
		</tr>
		<tr>
			<td colspan="2">
				<b>Signature:</b><br>
				<?php
				$newstr = str_replace( chr(10), "<BR>", $urow["signature"]);
				echo $newstr;
				?>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table border="0" cellpadding="2" cellspacing="0" width="95%">
<tr>
	<td>
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_id;?>&vm=0">tabbed</a> :
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_id;?>&vm=1">flat</a>
	</td>
</tr>
</table>

<?php	
$tabs = array(
	'usr_perms' => 'Permissions',
	'usr_proj' => 'Owned Projects'
);

if ($vm == 1) { ?>
<table border="0" cellpadding="2" cellspacing="0" width="95%">
<?php
	foreach ($tabs as $k => $v) {
		echo "<tr><td><b>$v</b></td></tr>";
		echo "<tr><td>";
		include "vw_$k.php";
		echo "</td></tr>";
	}
?>
</table>
<?php 
} else {
	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'usr_perms';
	drawTabBox( $tabs, $tab, "./index.php?m=admin&a=viewuser&user_id=$user_id", "./modules/admin" );
}
?>
