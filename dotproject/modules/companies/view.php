<?php
$company_id = isset($HTTP_GET_VARS['company_id']) ? $HTTP_GET_VARS['company_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $company_id );
$denyEdit = getDenyEdit( $m, $company_id );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// pull data
$sql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";

db_loadHash( $sql, $row, __LINE__ );

$pstatus = array(
	'Not Defined',
	'Proposed',
	'In planning',
	'In progress',
	'On hold',
	'Complete'
);
?>

<table border=0 cellpadding="1" cellspacing=1>
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0"></td>
	<td nowrap><span class="title">View Company/Client</span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap>
	<a href="./index.php?m=companies">Companies List</a>
<?php if (!$denyEdit) { ?>
	<b>:</b> <a href="./index.php?m=companies&a=addedit&company_id=<?php echo $company_id;?>">Edit this Company</a>
<?php } ?>
	</td>
	<td align="right" width="100%">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new company" onClick="javascript:window.location='./index.php?m=companies&a=addedit';">
	<?php } ?>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr valign="top">
	<td width="50%">
		<b>Details</b>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap>Company:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $row["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Phone:</td>
			<td bgcolor="#ffffff"><?php echo @$row["company_phone1"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Phone2:</td>
			<td bgcolor="#ffffff"><?php echo @$row["company_phone2"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Fax:</td>
			<td bgcolor="#ffffff"><?php echo @$row["company_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap>Address:</td>
			<td bgcolor="#ffffff"><?php
				echo @$row["company_address1"]
					.( ($row["company_address2"]) ? '<br>'.$row["company_address2"] : '' )
					.'<br>'.$row["company_city"]
					.'&nbsp;&nbsp;'.$row["company_state"]
					.'&nbsp;&nbsp;'.$row["company_zip"]
					;
			?></td>
		</tr>
		<tr>
			<td align="right" nowrap>URL:</td>
			<td bgcolor="#ffffff">
				<a href="http://<?php echo @$row["company_primary_url"];?>" target="Company"><?php echo @$row["company_primary_url"];?></a>
			</td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<b>Description</b>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td bgcolor="#ffffff">
				<?php echo str_replace( chr(10), "<BR>", $row["company_description"]);?>&nbsp;
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>

<?php	
// tabbed information boxes
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tabBox = new CTabBox( "?m=companies&a=view&company_id=$company_id", "./modules/companies", $AppUI->getState( 'CompVwTab' ) );
$tabBox->add( 'vw_depts', 'Departments' );
$tabBox->add( 'vw_active', 'Active Projects' );
$tabBox->add( 'vw_archived', 'Archived Projects' );
$tabBox->add( 'vw_users', 'Users' );
$tabBox->show();
?>
