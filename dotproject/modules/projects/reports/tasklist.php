<?php /* PROJECTS $Id$ */
/**
* Generates a report of the task logs for given dates
*/
error_reporting( E_ALL );
$do_report = dPgetParam( $_POST, "do_report", 0 );
$log_all = dPgetParam( $_POST, 'log_all', 0 );
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );
$log_ignore = dPgetParam( $_POST, 'log_ignore', 0 );

$list_start_date = dPgetParam( $_POST, "list_start_date", 0 );
$list_end_date = dPgetParam( $_POST, "list_end_date", 0 );

// create Date objects from the datetime fields
$start_date = intval( $list_start_date ) ? new CDate( $list_start_date ) : new CDate();
$end_date = intval( $list_end_date ) ? new CDate( $list_end_date ) : new CDate();

if (!$list_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

?>
<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.list_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.list_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

<form name="editFrm" action="index.php?m=projects&a=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period');?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="list_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="list_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
</form>
</table>

<?php
if ($do_report) {
	
	$sql = "SELECT * FROM tasks WHERE task_project = $project_id";
	if (!$log_all) {
		$sql .= "\n	AND task_start_date >= '".$start_date->format( FMT_DATETIME_MYSQL )."'"
		."\n	AND task_start_date <= '".$end_date->format( FMT_DATETIME_MYSQL )."'";
	}
	$sql .= " ORDER BY task_start_date";
	$Task_List = db_exec( $sql );
		
	//echo "<pre>".$sql."</pre>";
	//echo db_error();

	echo "<table cellspacing=\"1\" cellpadding=\"4\" border=\"0\" class=\"tbl\">";
	echo "<tr><th>Task Name</th>";
	echo "<th width=400>Task Description</th>";
	echo "<th>Assigned To</th>";
	echo "<th>Task Start Date</th>";
	echo "<th>Task End Date</th>";
	echo "<th>Completion</th></tr>";
	
	$pdfdata = array();
	$columns = array(
		"<b>".$AppUI->_('Task Name')."</b>",
		"<b>".$AppUI->_('Task Description')."</b>",
		"<b>".$AppUI->_('Assigned To')."</b>",
		"<b>".$AppUI->_('Task Start Date')."</b>",
		"<b>".$AppUI->_('Task End Date')."</b>",
		"<b>".$AppUI->_('Completion')."</b>"
	);
	while ($Tasks = db_fetch_assoc($Task_List)){
		$start_date = new CDate( $Tasks['task_start_date'] );
		$end_date = new CDate( $Tasks['task_end_date'] );
		$task_id = $Tasks['task_id'];

		$sql_user = db_exec ("SELECT * FROM user_tasks WHERE task_id = ".$task_id);
		$users = null;
		while ($Task_User = db_fetch_assoc($sql_user)){
			//$current_user = $Task_User['user_id'];
			if ($users!=null){
				$users.=", ";
			}
			$sql_user_array = db_exec ("SELECT user_first_name, user_last_name FROM users WHERE user_id = ".$Task_User['user_id']);
			$user_list = db_fetch_assoc($sql_user_array);
			$users .= $user_list['user_first_name']." ".$user_list['user_last_name'];
		}
		$str =  "<tr>";
		$str .= "<td>".$Tasks['task_name']."</td>";
		$str .= "<td>".$Tasks['task_description']."</td>";
		$str .= "<td>".$users."</td>";
		$str .= "<td>".$start_date->format( $df )."</td>";
		$str .= "<td>".$end_date->format( $df )."</td>";
		$str .= "<td align=\"center\">".$Tasks['task_percent_complete']."%</td>";
		$str .= "</tr>";
		echo $str;
		$pdfdata[] = array(
			$Tasks['task_name'],
			$Tasks['task_description'],
			$users,
			$start_date->format( $df ),
			$end_date->format( $df ),
			$Tasks['task_percent_complete']."%",
		);

	}
	echo "</table>";
if ($log_pdf) {
	// make the PDF file
		$sql = "SELECT project_name FROM projects WHERE project_id=$project_id";
		$pname = db_loadResult( $sql );
		echo db_error();

		$font_dir = $AppUI->getConfig( 'root_dir' )."/lib/ezpdf/fonts";
		$temp_dir = $AppUI->getConfig( 'root_dir' )."/files/temp";
		$base_url  = $AppUI->getConfig( 'base_url' );
		require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

		$pdf =& new Cezpdf($paper='A4',$orientation='landscape');
		$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
		$pdf->selectFont( "$font_dir/Helvetica.afm" );

		$pdf->ezText( $AppUI->getConfig( 'company_name' ), 12 );
		// $pdf->ezText( $AppUI->getConfig( 'company_name' ).' :: '.$AppUI->getConfig( 'page_title' ), 12 );		

		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		$pdf->ezText( "\n" . $AppUI->_('Project Task Report'), 12 );
		$pdf->ezText( "$pname", 15 );
		if ($log_all) {
			$pdf->ezText( "All task entries", 9 );
		} else {
			$pdf->ezText( "Task entries from ".$start_date->format( $df ).' to '.$end_date->format( $df ), 9 );
		}
		$pdf->ezText( "\n" );
		$pdf->selectFont( "$font_dir/Helvetica.afm" );
		//$columns = null; This is already defined above... :)
		$title = null;
		$options = array(
			'showLines' => 2,
			'showHeadings' => 1,
			'fontSize' => 9,
			'rowGap' => 4,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'750',
			'shaded'=> 0,
			'cols'=>array(0=>array('justification'=>'left','width'=>150),
					2=>array('justification'=>'left','width'=>95),
					3=>array('justification'=>'center','width'=>75),
					4=>array('justification'=>'center','width'=>75),
					5=>array('justification'=>'center','width'=>75))
		);

		$pdf->ezTable( $pdfdata, $columns, $title, $options );

		if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.pdf", 'wb' )) {
			fwrite( $fp, $pdf->ezOutput() );
			fclose( $fp );
			echo "<a href=\"$base_url/files/temp/temp$AppUI->user_id.pdf\" target=\"pdf\">";
			echo $AppUI->_( "View PDF File" );
			echo "</a>";
		} else {
			echo "Could not open file to save PDF.  ";
			if (!is_writable( $temp_dir )) {
				"The files/temp directory is not writable.  Check your file system permissions.";
			}
		}
	}
}
?>
</table>