<?php //build URI string
if(empty($project_id))$project_id=0;
$uri_string="";

if(isset($project_id))$uri_string="&project_id=" . $project_id;
if(isset($company_id))$uri_string="&company_id=" . $company_id;
if(isset($task_id))$uri_string="&task_id=" . $task_id;
if(isset($file_id))$uri_string="&file_id=" . $file_id;

?>
<script>
function newTask(){
	if(<?php echo $project_id;?> ==0){
		alert("You must select a project before you can add a task");
	}
	else{
	window.location="./index.php?m=tasks&a=addedit&project_id=<?php echo $project_id;?>&task_parent=<?php echo $task_id;?>";
	}


}

</script>


<table border="0" cellpadding="0" cellspacing="0">
		<TD align="right" nowrap>Create New: </TD>
		<TD><a href="javascript:window.location='#';newTask()"><img src="./images/icons/minitask.gif" width="29" height="36" alt="" border="0" align="absmiddle"></A></TD>
		<TD><img src="./images/shim.gif" width="5" height=5 align="absmiddle"></TD>
		<TD><a href="./index.php?m=projects&a=addedit<?php echo $uri_string;?>"><img src="./images/icons/miniproject.gif" width="29" height="36" alt="" border="0" align="absmiddle"></a></TD>
		<TD><img src="./images/shim.gif" width="5" height=5 align="absmiddle"></TD>
		<TD><a href="./index.php?m=files&a=addedit<?php echo $uri_string;?>"><img src="./images/icons/minifile.gif" width="29" height="36" alt="" border="0" align="absmiddle"></TD>
		<TD><img src="./images/shim.gif" width="5" height=5 align="absmiddle"></td>
		<TD><a href="./index.php?m=contacts&a=addedit<?php echo $uri_string;?>"><img src="./images/icons/minicontact.GIF" width="29" height="36" alt="" border="0" align="absmiddle"></TD>
		<TD><img src="./images/shim.gif" width="5" height=5 align="absmiddle"></td>
		<TD><a href="./index.php?m=calendar&a=addedit<?php echo $uri_string;?>"><img src="./images/icons/minievent.gif" width="21" height="28" alt="" border="0" align="absmiddle"></TD>
		<TD><img src="./images/shim.gif" width="5" height=5 align="absmiddle"></td>
	</TR>
</TABLE>
