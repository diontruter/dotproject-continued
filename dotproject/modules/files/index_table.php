<?php /* $Id$ */

// Files modules: index page re-usable sub-table
GLOBAL $AppUI, $deny1;

require_once( "$root_dir/classdefs/date.php" );
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

// get any specifically denied tasks
	$sql = "
	SELECT task_id, task_id
	FROM tasks, permissions
	WHERE permission_user = $AppUI->user_id
		AND permission_grant_on = 'tasks'
		AND permission_item = task_id
		AND permission_value = 0
	";
	$deny2 = db_loadHashList( $sql );

// SETUP FOR FILE LIST
$sql = "
SELECT files.*,
	project_name, project_color_identifier, project_active, 
	user_first_name, user_last_name
FROM files, permissions
LEFT JOIN projects ON project_id = file_project
LEFT JOIN users ON user_id = file_owner
WHERE
	permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
"
. (count( $deny1 ) > 0 ? "\nAND file_project NOT IN (" . implode( ',', $deny1 ) . ')' : '') 
. (count( $deny2 ) > 0 ? "\nAND file_task NOT IN (" . implode( ',', $deny2 ) . ')' : '') 
. ($project_id ? "\nAND file_project = $project_id" : '')
."
GROUP BY file_id
ORDER BY project_name, file_name
";

$file = array();
if (!$denyRead) {
	$files = db_loadList( $sql );
}
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Owner' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?></a></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Date' );?></th>
</tr>
<?php
$fp=-1;
$file_date = new CDate();
$file_date->setFormat( "$df $tf" );

foreach ($files as $row) {
	$file_date->setTimestamp( db_dateTime2unix( $row['file_date'] ) );

	if ($fp != $row["file_project"]) {
		if (!$row["project_name"]) {
			$row["project_name"] = 'All Projects';
			$row["project_color_identifier"] = 'f4efe3';
		}
		if ($showProject) {
			$s = '<tr>';
			$s .= '<td colspan="6" style="background-color:#'.$row["project_color_identifier"].'" style="border: outset 2px #eeeeee">';
			$s .= '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. $row["project_name"] . '</font>';
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row["file_project"];
?>
<tr>
	<td nowrap="nowrap" width="20">
	<?php if (!$denyEdit) { ?>
		<a href="./index.php?m=files&a=addedit&file_id=<?php echo $row["file_id"];?>"><img src="./images/icons/pencil.gif" alt="edit file" border="0" width=12 height=12></a>
	<?php } ?>
	</td>
	<td nowrap="nowrap">
		<?php echo "<a href=\"./fileviewer.php?file_id={$row['file_id']}\">{$row['file_name']}</a>"; ?>
	</td>
	<td width="15%" nowrap="nowrap"><?php echo $row["user_first_name"].' '.$row["user_last_name"];?></td>
	<td width="10%" nowrap="nowrap" align="right"><?php echo intval($row["file_size"] / 1024);?> kb</td>
	<td width="15%" nowrap="nowrap"><?php echo $row["file_type"];?></td>
	<td width="15%" nowrap="nowrap" align="right"><?php echo $file_date->toString();?></td>
</tr>
<?php }?>
</table>
