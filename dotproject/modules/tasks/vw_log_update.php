<?php /* TASKS $Id$ */
GLOBAL $AppUI, $canEdit, $task_id, $obj, $percent;

if ($canEdit) {
// Task Update Form
	$df = $AppUI->getPref('SHDATEFORMAT');
	$log_date = new Date();
?>
<table cellspacing="1" cellpadding="2" border="0" width="100%">
<form name="editFrm" action="?m=tasks&a=view&task_id=<?php echo $task_id;?>" method="post">
	<input type="hidden" name="uniqueid" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="task_id" value="<?php echo @$obj->task_id;?>" />
	<input type="hidden" name="task_log_task" value="<?php echo @$obj->task_id;?>" />
	<input type="hidden" name="task_log_creator" value="<?php echo $AppUI->user_id;?>" />
	<input type="hidden" name="task_log_name" value="Update :<?php echo @$obj->task_name;?>" />
	<input type="hidden" name="task_hours_worked" value="<?php echo @$obj->task_hours_worked;?>" />
<tr>
	<td align="right">
		<?php echo $AppUI->_('Date');?>
	</td>
	<td nowrap="nowrap">
		<input type="hidden" name="task_log_date" value="<?php echo $log_date->format( DATE_FORMAT_TIMESTAMP_DATE );?>">
		<input type="text" name="log_date" value="<?php echo $log_date->format( $df );?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('log_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right"><?php echo $AppUI->_('Summary');?>:</td>
	<td>
		<input type="text" class="text" name="task_log_name" maxlength="255" size="30" />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Progress');?></td>
	<td>
<?php
	echo arraySelect( $percent, 'task_percent_complete', 'size="1" class="text"', $obj->task_percent_complete ) . '%';
?>
	</td>
	<td rowspan="3" align="right" valign="top"><?php echo $AppUI->_('Description');?>:</td>
	<td rowspan="3">
		<textarea name="task_log_description" class="textarea" cols="50" rows="6"></textarea>
	</td>
</tr>
<tr>
	<td align="right">
		<?php echo $AppUI->_('Hours Worked');?>
	</td>
	<td>
		<input type="text" class="text" name="task_log_hours" maxlength="8" size="6" />
	</td>
</tr>
<tr>
	<td align="right">
		<?php echo $AppUI->_('Cost Code');?>
	</td>
	<td>
		<input type="text" class="text" name="task_log_costcode" maxlength="8" size="8" />
	</td>
</tr>
<tr>
	<td colspan="4" valign="bottom" align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_('update task');?>" onclick="updateTask()" />
	</td>
</tr>

</form>
</table>
<?php } ?>
