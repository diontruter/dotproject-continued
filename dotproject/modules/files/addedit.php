<?php /* FILES $Id$ */
$file_id = intval( dPgetParam( $_GET, 'file_id', 0 ) );
$ci = dPgetParam($_GET, 'ci', 0) == 1 ? true : false;
 


// check permissions for this record
$perms =& $AppUI->acl();
$canEdit = $perms->checkModuleItem( $m, 'edit', $file_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$canAdmin = $perms->checkModule('system', 'edit');

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'projects' ) );

$file_task = intval( dPgetParam( $_GET, 'file_task', 0 ) );
$file_parent = intval( dPgetParam( $_GET, 'file_parent', 0 ) );
$file_project = intval( dPgetParam( $_GET, 'project_id', 0 ) );

$q =& new DBQuery;

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CFile();
$canDelete = $obj->canDelete( $msg, $file_id );

// load the record data
// $obj = null;
if ($file_id > 0 && ! $obj->load($file_id)) {
	$AppUI->setMsg( 'File' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($obj->file_checkout != $AppUI->user_id)
        $ci = false;

if (! $canAdmin)
	$canAdmin = $obj->canAdmin();

if ($obj->file_checkout == 'final' && ! $canAdmin) {
	redirect('m=public&a=access_denied');
}
// setup the title block
$ttl = $file_id ? "Edit File" : "Add File";
$ttl = $ci ? 'Checking in' : $ttl;
$titleBlock = new CTitleBlock( $ttl, 'folder5.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=files", "files list" );
if ($canEdit && $file_id > 0 && !$ci) {
	$titleBlock->addCrumbDelete( 'delete file', $canDelete, $msg );
}
$titleBlock->show();

//Clear the file id if checking out so a new version is created.
if ($ci)
        $file_id = 0;

if ($obj->file_project) {
	$file_project = $obj->file_project;
}
if ($obj->file_task) {
	$file_task = $obj->file_task;
	$task_name = $obj->getTaskName();
} else if ($file_task) {
	$sql = "SELECT task_name FROM tasks WHERE task_id=$file_task";
	$task_name = db_loadResult( $sql );
} else {
	$task_name = '';
}

$extra = array(
	'where'=>'project_active <> 0'
);
$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>$AppUI->_('All', UI_OUTPUT_RAW) ), $projects );

//$sql = "SELECT project_id, project_name  FROM projects ORDER BY project_name";
//$projects = arrayMerge( array( '0'=>'- ALL PROJECTS -'), db_loadHashList( $sql ) );
?>
<script language="javascript">
function submitIt() {
	var f = document.uploadFrm;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('filesDelete');?>" )) {
		var f = document.uploadFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask() {
    var f = document.uploadFrm;
    if (f.file_project.selectedIndex == 0) {
        alert( "<?php echo $AppUI->_('Please select a project first!');?>" );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&task_project='
            + f.file_project.options[f.file_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

function finalCI()
{
        var f = document.uploadFrm;
        if (f.final_ci.value = '1')
        {
                f.file_checkout.value = 'final';
                f.file_co_reason.value = 'Final Version';
        }
        else
        {
                f.file_checkout.value = '';
                f.file_co_reason.value = '';
        }
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.uploadFrm;
    if (val != '') {
        f.file_task.value = key;
        f.task_name.value = val;
    } else {
        f.file_task.value = '0';
        f.task_name.value = '';
    }
}
</script>

<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">

<form name="uploadFrm" action="?m=files" enctype="multipart/form-data" method="post">
	<input type="hidden" name="dosql" value="do_file_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_id" value="<?php echo $file_id;?>" />
	<input type="hidden" name="file_version_id" value="<?php echo $file_version_id;?>" />

<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="60%">
	<?php if ($file_id) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?>:</td>
			<td align="left" class="hilite"><?php echo strlen($obj->file_name)== 0 ? "n/a" : $obj->file_name;?></td>
			<td>
				<a href="./fileviewer.php?file_id=<?php echo $obj->file_id;?>"><?php echo $AppUI->_( 'download' );?></a>
			</td>
		</tr>
		<tr valign="top">
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->file_type;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->file_size;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Uploaded By' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->getOwner();?></td>
		</tr>
	<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Version' );?>:</td>
			<td align="left">
                        <?php if ($ci || ($canAdmin && $obj->file_checkout == 'final') ) { ?>
				<input type="hidden" name="file_checkout" value="" />
				<input type="hidden" name="file_co_reason" value="" />
				<?php } if ($ci) { ?>
				<input type="hidden" name="file_version" value="<?php echo strlen( $obj->file_version ) > 0 ? $obj->file_version+0.1 : "1";?>" />
                                <?php echo strlen( $obj->file_version ) > 0 ? $obj->file_version+0.1 : "1";?>
                        <?php } else { ?>
				<input type="text" name="file_version" value="<?php echo strlen( $obj->file_version ) > 0 ? $obj->file_version : "1";?>" maxlength="10" size="5" />
                        <?php } ?>
			</td>
		</tr>
                <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Category');?>:</td>
                        <td align="left">
                                <?php echo arraySelect(dPgetSysVal("FileType"), 'file_category', '', $obj->file_category); ?>
                        <td>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
			<td align="left">
			<?php
				echo arraySelect( $projects, 'file_project', 'size="1" class="text" style="width:270px"', $file_project  );
			?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task' );?>:</td>
			<td align="left" colspan="2" valign="top">
				<input type="hidden" name="file_task" value="<?php echo $file_task;?>" />
				<input type="text" class="text" name="task_name" value="<?php echo $task_name;?>" size="40" disabled />
				<input type="button" class="button" value="<?php echo $AppUI->_('select task');?>..." onclick="popTask()" />
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
			<td align="left">
				<textarea name="file_description" class="textarea" rows="4" style="width:270px"><?php echo $obj->file_description;?></textarea>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Upload File' );?>:</td>
			<td align="left"><input type="File" class="button" name="formfile" style="width:270px"></td>
		</tr>
                <?php if ($ci || ( $canAdmin && $obj->file_checkout == 'final') ) {
                ?>
		<tr>
			<td align="right" nowrap="nowrap">&nbsp;</td>
			<td align="left"><input type="checkbox" name="final_ci" onClick="finalCI()"><?php echo $AppUI->_('Final Version'); ?></td>		
		</tr>
                <?php } ?>
		<tr>
			<td align="right" nowrap="nowrap">&nbsp;</td>
			<td align="left"><input type="checkbox" name="notify" checked="checked"><?php echo $AppUI->_('Notify Assignees of Task or Project Owner by Email'); ?></td>		
		</tr>
		
		</table>
	</td>
</tr>
<tr>
	<td>
		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?'); ?>')){location.href = './index.php?m=files';}" />
	</td>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'submit' );?>" onclick="submitIt()" />
	</td>
</tr>
</form>
</table>
