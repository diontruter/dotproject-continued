<?php /* COMPANIES $Id$ */
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this company
$canEdit = !getDenyEdit( $m, $company_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the company types
$types = dPgetSysVal( 'CompanyType' );

// load the record data
$sql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";

$obj = null;
if (!db_loadObject( $sql, $obj ) && $company_id > 0) {
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// collect all the users for the company owner list
$owners = array( '0'=>'' );
$sql = "SELECT user_id,CONCAT_WS(' ',user_first_name,user_last_name) FROM users ORDER BY user_first_name";
$owners = db_loadHashList( $sql );

// setup the title block
$ttl = $company_id > 0 ? "Edit Company" : "Add Company";
$titleBlock = new CTitleBlock( $ttl, 'handshake.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=companies", "companies list" );
if ($company_id != 0)
  $titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
$titleBlock->show();
?>

<script language="javascript">
function submitIt() {
	var form = document.changeclient;
	if (form.company_name.value.length < 3) {
		alert( "Please enter a valid Company name" );
		form.company_name.focus();
	} else {
		form.submit();
	}
}

function testURL( x ) {
	var test = "document.changeclient.company_primary_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}
</script>

<table cellspacing="1" cellpadding="1" border="0" width="100%" class="std">
<form name="changeclient" action="?m=companies" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id;?>" />

<tr>
	<td align="right"><?php echo $AppUI->_('Company Name');?>:</td>
	<td>
		<input type="text" class="text" name="company_name" value="<?php echo @$obj->company_name;?>" size="50" maxlength="255" /> (<?php echo $AppUI->_('required');?>)
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email');?>:</td>
	<td>
		<input type="text" class="text" name="company_email" value="<?php echo @$obj->company_email;?>" size="30" maxlength="255" />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
	<td>
		<input type="text" class="text" name="company_phone1" value="<?php echo @$obj->company_phone1;?>" maxlength="30" />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
	<td>
		<input type="text" class="text" name="company_phone2" value="<?php echo @$obj->company_phone2;?>" maxlength="50" />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
	<td>
		<input type="text" class="text" name="company_fax" value="<?php echo @$obj->company_fax;?>" maxlength="30" />
	</td>
</tr>
<tr>
	<td colspan=2 align="center">
		<img src="images/shim.gif" width="50" height="1" /><?php echo $AppUI->_('Address');?><br />
		<hr width="500" align="center" size=1 />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>1:</td>
	<td><input type="text" class="text" name="company_address1" value="<?php echo @$obj->company_address1;?>" size=50 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
	<td><input type="text" class="text" name="company_address2" value="<?php echo @$obj->company_address2;?>" size=50 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('City');?>:</td>
	<td><input type="text" class="text" name="company_city" value="<?php echo @$obj->company_city;?>" size=50 maxlength="50" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('State');?>:</td>
	<td><input type="text" class="text" name="company_state" value="<?php echo @$obj->company_state;?>" maxlength="50" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
	<td><input type="text" class="text" name="company_zip" value="<?php echo @$obj->company_zip;?>" maxlength="15" /></td>
</tr>
<tr>
	<td align="right">
		URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo @$obj->company_primary_url;?>" name="company_primary_url" size="50" maxlength="255" />
		<a href="#x" onClick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test');?>]</a>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Company Owner');?>:</td>
	<td>
<?php
	echo arraySelect( $owners, 'company_owner', 'size="1" class="text"', @$obj->company_owner );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Type');?>:</td>
	<td>
<?php
	echo arraySelect( $types, 'company_type', 'size="1" class="text"', @$obj->company_type );
?>
	</td>
</tr>

<tr>
	<td align="right" valign=top><?php echo $AppUI->_('Description');?>:</td>
	<td align="left">
		<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo @$obj->company_description;?></textarea>
	</td>
</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" /></td>
	<td align="right"><input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()" /></td>
</tr>
</form>
</table>
