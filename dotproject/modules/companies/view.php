<?php /* COMPANIES $Id$ */
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this record
$canRead = !getDenyRead( $m, $company_id );
$canEdit = !getDenyEdit( $m, $company_id );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CCompany();
$canDelete = $obj->canDelete( $msg, $company_id );

// load the record data
$sql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";

$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// load the list of project statii and company types
$pstatus = dPgetSysVal( 'ProjectStatus' );
$types = dPgetSysVal( 'CompanyType' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Company', 'handshake.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new company').'" />', '',
		'<form action="?m=companies&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=companies", "company list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=companies&a=addedit&company_id=$company_id", "edit this company" );
	
	if ($canEdit) {
		$titleBlock->addCrumbDelete( 'delete company', $canDelete, $msg );
	}
}
$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Company').'?';?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=companies" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="company_id" value="<?php echo $company_id;?>" />
</form>

<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->company_name;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite"><?php echo @$obj->company_phone1;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone');?>2:</td>
			<td class="hilite"><?php echo @$obj->company_phone2;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax');?>:</td>
			<td class="hilite"><?php echo @$obj->company_fax;?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address');?>:</td>
			<td class="hilite"><?php
				echo @$obj->company_address1
					.( ($obj->company_address2) ? '<br />'.$obj->company_address2 : '' )
					.'<br />'.$obj->company_city
					.'&nbsp;&nbsp;'.$obj->company_state
					.'&nbsp;&nbsp;'.$obj->company_zip
					;
			?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?>:</td>
			<td class="hilite">
				<a href="http://<?php echo @$obj->company_primary_url;?>" target="Company"><?php echo @$obj->company_primary_url;?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type');?>:</td>
			<td class="hilite"><?php echo $AppUI->_($types[@$obj->company_type]);?></td>
		</tr>
		</table>

	</td>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Description');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $obj->company_description);?>&nbsp;
			</td>
		</tr>
		<?php
		$custom_fields = dPgetSysVal("CompanyCustomFields");
		if ( count($custom_fields) > 0 ){
			//We have custom fields, parse them!
			//Custom fields are stored in the sysval table under TaskCustomFields, the format is
			//key|serialized array of ("name", "type", "options", "selects")
			
			if ( $obj->company_custom != "" || !is_null($obj->company_custom))  {
				//Custom info previously saved, retrieve it
				$custom_field_previous_data = unserialize($obj->company_custom);
			}
			
			$output = '<tr><table cellspacing="1" cellpadding="2" >';
			foreach ( $custom_fields as $key => $array) {
				$output .= "<tr id='custom_tr_$key' >";
				$field_options = unserialize($array);
				$output .= "<td align='right' nowrap='nowrap' >". ($field_options["type"] == "label" ? "<b>". $field_options['name']. "</b>" : $field_options['name'] . ":") ."</td>";
				switch ( $field_options["type"]){
					case "text":
						$output .= "<td class='hilite' width='300'>" . ( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "") . "</td>";
						break;
					case "select":
						$optionarray = explode(",",$field_options["selects"]);
						$output .= "<td class='hilite' width='300'>". ( isset($custom_field_previous_data[$key]) ? $optionarray[$custom_field_previous_data[$key]] : "") . "</td>";
						break;
					case "textarea":
						$output .=  "<td valign='top' class='hilite'>" . ( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "") . "</td>";
						break;
				}
				$output .= "</tr>";
			}
			$output .= "</table></tr>";
			echo $output;
		}
		?>
		</table>
	</td>
</tr>
</table>

<?php
// tabbed information boxes
$tabBox = new CTabBox( "?m=companies&a=view&company_id=$company_id", "{$AppUI->cfg['root_dir']}/modules/companies/", $tab );
$tabBox->add( 'vw_active', 'Active Projects' );
$tabBox->add( 'vw_archived', 'Archived Projects' );
$tabBox->add( 'vw_depts', 'Departments' );
$tabBox->add( 'vw_users', 'Users' );
$tabBox->add( 'vw_contacts', 'Contacts' );
$tabBox->show();
?>
