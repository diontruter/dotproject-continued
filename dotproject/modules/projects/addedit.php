<?php /* PROJECTS $Id$ */
$project_id = dPgetParam( $_GET, "project_id", 0 );

// check permissions for this project
$canEdit = !getDenyEdit( $m, $project_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// pull companies
$sql = "SELECT company_id, company_name FROM companies ORDER BY company_name";
$companies = arrayMerge( array( 0 => '' ), db_loadHashList( $sql ) );

// pull users
$sql = "SELECT user_id, CONCAT( user_last_name, ', ', user_first_name) FROM users ORDER BY user_last_name";
$users = db_loadHashList( $sql );

// pull the project
$sql = "SELECT * FROM projects WHERE project_id = $project_id";
if (!db_loadHash( $sql, $project ) && $project_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Project ID', 'projects.gif', $m, 'ID_HELP_PROJ_EDIT' );
	$titleBlock->addCrumb( "?m=projects", "projects list" );
	$titleBlock->show();
} else if (count( $companies ) < 2) {
	$titleBlock = new CTitleBlock( 'noCompanies', 'projects.gif', $m, 'ID_HELP_PROJ_EDIT' );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->addCrumb( "?m=projects", "projects list" );
	$titleBlock->show();
} else {
	// format dates
	$df = $AppUI->getPref('SHDATEFORMAT');

	$start_date = $project["project_start_date"] ? CDate::fromDateTime( $project["project_start_date"] ) : new CDate();
	$start_date->setFormat( $df );

	if ($project["project_end_date"]) {
		$end_date = CDate::fromDateTime( $project["project_end_date"] );
		$end_date->setFormat( $df );
	} else {
		$end_date = null;
	}

	if ($project["project_actual_end_date"]) {
		$actual_end_date = CDate::fromDateTime( $project["project_actual_end_date"] );
		$actual_end_date->setFormat( $df );
	} else {
		$actual_end_date = null;
	}

// setup the title block
	$ttl = $project_id > 0 ? "Edit Project" : "New Project";
	$titleBlock = new CTitleBlock( $ttl, 'projects.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=projects", "projects list" );
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view this project" );
	$titleBlock->show();
?>
<script language="javascript">
function setColor(color) {
	var f = document.frmEditProject;
	if (color) {
		f.project_color_identifier.value = color;
	}
	test.style.background = f.project_color_identifier.value;
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.frmEditProject.project_' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.frmEditProject.project_' + calendarField );
	fld_fdate = eval( 'document.frmEditProject.' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}

function setShort() {
	var f = document.frmEditProject;
	var x = 10;
	if (f.project_name.value.length < 11) {
		x = f.project_name.value.length;
	}
	if (f.project_short_name.value.length == 0) {
		f.project_short_name.value = f.project_name.value.substr(0,x);
	}
}

function submitIt() {
	var f = document.frmEditProject;
	var msg = '';

	if (f.project_name.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('projectsValidName');?>";
		f.project_name.focus();
	}
	if (f.project_color_identifier.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('projectsColor');?>";
		f.project_color_identifier.focus();
	}
	if (f.project_company.options[f.project_company.selectedIndex].value < 1) {
		msg += "\n<?php echo $AppUI->_('projectsBadCompany');?>";
		f.project_name.focus();
	}
	if (f.project_end_date.value > 0 && f.project_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate1');?>";
	}
	if (f.project_actual_end_date.value > 0 && f.project_actual_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate2');?>";
	}
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<form name="frmEditProject" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="project_creator" value="<?php echo $AppUI->user_id;?>" />

<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Name');?></td>
			<td width="100%">
				<input type="text" name="project_name" value="<?php echo @$project["project_name"];?>" size="25" maxlength="50" onBlur="setShort();" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner');?></td>
			<td>
<?php echo arraySelect( $users, 'project_owner', 'size="1" style="width:200px;" class="text"', dPgetParam( $project, "project_owner", $AppUI->user_id ) ) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company');?></td>
			<td width="100%" nowrap="nowrap">
<?php
	echo arraySelect( $companies, 'project_company', 'class="text" size="1"', $project["project_company"] );
?> *</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></td>
			<td>
				<input type="hidden" name="project_start_date" value="<?php echo $start_date->getTimestamp();?>" />
				<input type="text" name="start_date" value="<?php echo $start_date->toString();?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('start_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Finish Date');?></td>
			<td>
				<input type="hidden" name="project_end_date" value="<?php echo $end_date ? $end_date->getTimestamp() : '-1';?>" />
				<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->toString() : '';?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('end_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?> $</td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo @$project["project_target_budget"];?>" maxlength="10" class="text" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date');?></td>
			<td>
				<input type="hidden" name="project_actual_end_date" value="<?php echo $actual_end_date ? $actual_end_date->getTimestamp() : '-1';?>" />
				<input type="text" name="actual_end_date" value="<?php echo $actual_end_date ? $actual_end_date->toString() : '';?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('actual_end_date','actual_end_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget');?> $</td>
			<td>
				<input type="text" name="project_actual_budget" value="<?php echo @$project["project_actual_budget"];?>" size="10" maxlength="10" class="text"/>
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?></td>
			<td>
				<input type="text" name="project_url" value="<?php echo @$project["project_url"];?>" size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL');?></td>
			<td>
				<input type="Text" name="project_demo_url" value="<?php echo @$project["project_demo_url"];?>" size="40" maxlength="255" class="text" />
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name');?></td>
			<td colspan="3">
				<input type="text" name="project_short_name" value="<?php echo @$project["project_short_name"];?>" size="10" maxlength="10" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Color Identifier');?></td>
			<td nowrap="nowrap">
				<input type="text" name="project_color_identifier" value="<?php echo @$project["project_color_identifier"];?>" size="10" maxlength="6" onBlur="setColor();" class="text" /> *
			</td>
			<td nowrap="nowrap">
				<a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scollbars=false');"><?php echo $AppUI->_('change color');?></a>
			</td>
			<td nowrap="nowrap">
				<span id="test" title="test" style="background:#<?php echo @$project["project_color_identifier"];?>;"><a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border="1" width="40" height="20" /></a></span>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<table width="100%" bgcolor="#cccccc">
				<tr>
					<td><?php echo $AppUI->_('Status');?> *</td>
					<td nowrap="nowrap"><?php echo $AppUI->_('Progress');?></td>
					<td><?php echo $AppUI->_('Active');?>?</td>
				</tr>
				<tr>
					<td>
						<?php echo arraySelect( $pstatus, 'project_status', 'size="1" class="text"', $project["project_status"], true ); ?>
					</td>
					<td>
						<strong><?php echo intval(@$project["project_percent_complete"]);?> %</strong>
					</td>
					<td>
						<input type=checkbox value=1 name=project_active <?php echo $project["project_active"]||$project_id==0 ? 'checked="checked"' : '';?>>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<?php echo $AppUI->_('Description');?><br />
				<textarea name="project_description" cols="50" rows="10" wrap="virtual" class="textarea"><?php echo @$project["project_description"];?></textarea>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<input class=button type="button" name="<?php echo $AppUI->_('cancel');?>" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}" />
	</td>
	<td align="right">
		<input class=button type="Button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt();" />
	</td>
</tr>
</form>
</table>
* <?php echo $AppUI->_('requiredField');?>
<?php } ?>