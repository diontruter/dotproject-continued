<?php /* PROJECTS $Id$ */
GLOBAL $AppUI, $projects, $company_id, $pstatus, $show_all_projects, $project_types, $tab;
$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');

	// Let's check if the user submited the change status form
	
?>

<form action='./index.php' method='get'>


<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=project_name" class="hdr"><?php echo $AppUI->_('Name');?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_start_date" class="hdr"><?php echo $AppUI->_('Start');?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_end_date" class="hdr"><?php echo $AppUI->_('End');?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_actual_end_date DESC" class="hdr"><?php echo $AppUI->_('Actual');?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=task_log_problem DESC" class="hdr"><?php echo $AppUI->_('P');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=user_username" class="hdr"><?php echo $AppUI->_('Owner');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=total_tasks%20desc" class="hdr"><?php echo $AppUI->_('Tasks');?></a>
		<a href="?m=projects&orderby=my_tasks%20desc" class="hdr">(<?php echo $AppUI->_('My');?>)</a>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Selection'); ?>
	</th>
	<?php
	if($show_all_projects){
		?>
		<th nowrap="nowrap">
			<?php echo $AppUI->_('Status'); ?>
		</th>
		<?php
	}
	?>
</tr>

<?php
$CR = "\n";
$CT = "\n\t";
$none = true;

// When in plain view, $AppUI->getState( 'ProjIdxTab' ) doesn't contain the selected index for which we want
// to filter projects, we must get the current box name from the calling file overrides.php variable $v
if ( $tab == -1 ){
	//Plain view
	foreach ($project_types as $project_key => $project_type){
		$project_type = trim($project_type);
		$flip_project_types[$project_type] = $project_key;
	}
	$project_status_filter = $flip_project_types[$v[1]];
} else{
	//Tabbed view
	$project_status_filter = $tab;
	//Project not defined
	if ($tab == count($project_types)-1)
		$project_status_filter = 0;
}

foreach ($projects as $row) {
	if (! $perms->checkModuleItem('project', 'view', $row['project_id']))
		continue;
	if ($show_all_projects ||
	    ($row["project_active"] > 0 && $row["project_status"] == $project_status_filter)) {
		$none = false;
                $start_date = intval( @$row["project_start_date"] ) ? new CDate( $row["project_start_date"] ) : null;
		$end_date = intval( @$row["project_end_date"] ) ? new CDate( $row["project_end_date"] ) : null;
                $actual_end_date = intval( @$row["project_actual_end_date"] ) ? new CDate( $row["project_actual_end_date"] ) : null;
                $style = (( $actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr>';
		$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#'
			. $row["project_color_identifier"] . '">';
		$s .= $CT . '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. sprintf( "%.1f%%", $row["project_percent_complete"] )
			. '</font>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td width="100%">';
		$s .= $CT . '<a href="?m=projects&a=view&project_id=' . $row["project_id"] . '" title="' . htmlspecialchars( $row["project_description"], ENT_QUOTES ) . '">' . htmlspecialchars( $row["project_name"], ENT_QUOTES ) . '</a>';
		$s .= $CR . '</td>';
                $s .= $CR . '<td align="center">'. ($start_date ? $start_date->format( $df ) : '-') .'</td>';
                $s .= $CR . '<td align="center">'. ($end_date ? $end_date->format( $df ) : '-') .'</td>';
                $s .= $CR . '<td align="center">';
                $s .= $actual_end_date ? '<a href="?m=tasks&a=view&task_id='.$row["critical_task"].'">' : '';
                $s .= $actual_end_date ? '<span '. $style.'>'.$actual_end_date->format( $df ).'</span>' : '-';
                $s .= $actual_end_date ? '</a>' : '';
		$s .= $CR . '</td>';
                $s .= $CR . '<td align="center">';
                $s .= $row["task_log_problem"] ? '<a href="?m=tasks&a=index&f=all&project_id='.$row["project_id"].'">' : '';
                $s .= $row["task_log_problem"] ? dPshowImage( './images/icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem' ): '-';
                $s .= $CR . $row["task_log_problem"] ? '</a>' : '';
                $s .= $CR . '</td>';
		$s .= $CR . '<td nowrap="nowrap">' . htmlspecialchars( $row["user_username"], ENT_QUOTES ) . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">';
		$s .= $CT . $row["total_tasks"] . ($row["my_tasks"] ? ' ('.$row["my_tasks"].')' : '');
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="center">';
		$s .= $CT . '<input type="checkbox" name="project_id[]" value="'.$row["project_id"].'" />';
		$s .= $CR . '</td>';

		if($show_all_projects){
			$s .= $CR . '<td align="center" nowrap="nowrap">';
			$s .= $CT . $row["project_status"] == 0 ? $AppUI->_('Not Defined') : $AppUI->_($project_types[$row["project_status"]]);
			$s .= $CR . '</td>';
		}
		
		$s .= $CR . '</tr>';
		echo $s;
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="10">' . $AppUI->_( 'No projects available' ) . '</td></tr>';
} else {
?>
<tr>
	<td colspan="10" align="right">
		<?php
			echo "<input type='submit' class='button' value='".$AppUI->_('Update projects status')."' />";
			echo "<input type='hidden' name='update_project_status' value='1' />";
			echo "<input type='hidden' name='m' value='projects' />";
			echo arraySelect( $pstatus, 'project_status', 'size="1" class="text"', 2, true );
												                                // 2 will be the next step
}
		?>
	</td>
</tr>
</table>
</form>
