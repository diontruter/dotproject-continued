<?php
GLOBAL $user_id, $denyEdit, $tab;

$pgos = array(
	'files' => 'file_name',
	'users' => 'user_username',
	'projects' => 'project_name',
	'companies' => 'company_name',
	'forums' => 'forum_name'
);

$pvs = array(
'-1' => 'read-write',
'0' => 'deny',
'1' => 'read only'
);


//Pull User perms
$usql = "
SELECT u.user_id, u.user_username,
	p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
	c.company_id, c.company_name,
	pj.project_id, pj.project_name,
	f.file_id, f.file_name,
	u2.user_id, u2.user_username
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
LEFT JOIN forums fm ON fm.forum_id = p.permission_item and p.permission_grant_on = 'forums'
WHERE u.user_id = p.permission_user
	AND u.user_id = $user_id
";

$urc = mysql_query($usql);
$nums = mysql_num_rows($urc);

//pull the projects into an temp array
$tarr = array();
while ($row = mysql_fetch_array( $urc, MYSQL_ASSOC )) {
	$item = @$row[@$pgos[$row['permission_grant_on']]];
	if (!$item) {
		$item = $row['permission_item'];
	}
	if ($item == -1) {
		$item = 'all';
	}
	$tarr[] = array_merge( $row, array( 'grant_item'=>$item ) );
}

$modules = array(
	'all' => "all",
	"admin" => "Admin",
	"calendar" => "Calendar",
	"companies" => "Companies",
	"contacts" => "Contacts",
	"departments" => "Departments",
	"files" => "Files",
	"forums" => "Forums",
	"mcps" => "MCPs",
	"plans" => "Plans",
	"projects" => "Projects",
	"tasks" => "Tasks",
	"ticketsmith" => "Tickets"
);

?>

<script language="javascript">
function editPerm( id, gon, it, vl, nm ) {
/*
	id = Permission_id
	gon =permission_grant_on
	it =permission_item
	vl =permission_value
	nm = text representation of permission_value
*/
//alert( 'id='+id+'\ngon='+gon+'\nit='+it+'\nvalue='+vl+'\nnm='+nm);
	var f = document.frmPerms;

	f.sqlaction2.value="edit";
	
	f.permission_id.value = id;
	f.permission_item.value = it;
	f.permission_item_name.value = nm;
	for(var i=0, n=f.permission_grant_on.options.length; i < n; i++) {
		if (f.permission_grant_on.options[i].value == gon) {
			f.permission_grant_on.selectedIndex = i;
			break;
		}
	}
	f.permission_value.selectedIndex = vl+1;
	f.permission_item_name.value = nm;
}

function clearIt(){
	var f = document.frmPerms;
	f.sqlaction2.value = "add";
	f.permission_id.value = 0;
	f.permission_grant_on.selectedIndex = 0;
}

function delIt(id) {
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var f = document.frmPerms;
		f.del.value = 1;
		f.permission_id.value = id;
		f.submit();
	}
}

var tables = new Array;
tables['companies'] = 'companies';
tables['departments'] = 'departments';
tables['projects'] = 'projects';
tables['tasks'] = 'projects';
tables['forums'] = 'forums';

function popPermItem() {
	var f = document.frmPerms;
	var pgo = f.permission_grant_on.options[f.permission_grant_on.selectedIndex].value;
	if (!(pgo in tables)) {
		alert( 'No list associated with this Module.' );
		return;
	}
	window.open('./selector.php?callback=setPermItem&table=' + tables[pgo], 'selector', 'left=50,top=50,height=250,width=400,resizable')
}

// Callback function for the generic selector
function setPermItem( key, val ) {
	var f = document.frmPerms;
	if (val != '') {
		f.permission_item.value = key;
		f.permission_item_name.value = val;
	} else {
		f.permission_item.value = '-1';
		f.permission_item_name.value = 'all';
	}
}

</script>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="50%" valign="top">

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>E</th>
	<th nowrap>Module</th>
	<th width="100%">Item</th>
	<th nowrap>Type</th>
	<th>D</th>
</tr>

<?php
foreach ($tarr as $row){
	$buf = '';

	$buf .= '<td nowrap>';
	if (!$denyEdit) {
		$buf .= "<a href=# onClick=\"editPerm({$row['permission_id']},'{$row['permission_grant_on']}',{$row['permission_item']},{$row['permission_value']},'{$row['grant_item']}');\"><img src=\"./images/icons/pencil.gif\" alt=\"edit permissions\" border=\"0\" width='12' height='12'></a>";
	}
	$buf .= '</td>';
	


	$style = '';
	if($row['permission_grant_on'] == "all" && $row['permission_item'] == -1 && $row['permission_value'] == -1) {
		$style =  'style="background-color:#ffc235"';
	} else if($row['permission_item'] == -1 && $row['permission_value'] == -1) {
		$style = 'style="background-color:#ffff99"';
	}

	$buf .= "<td $style>" . $row['permission_grant_on'] . "</td>";

	$buf .= "<td>" . $row['grant_item'] . "</td><td nowrap>" . $pvs[$row['permission_value']] . "</td>";

	$buf .= '<td nowrap>';
	if (!$denyEdit) {
		$buf .= "<a href=# onClick=\"delIt({$row['permission_id']});\"><img align='absmiddle' src='./images/icons/trash.gif' width='16' height='16' alt='Delete this item' border='0'></a>";
	}
	$buf .= '</td>';
	
	echo "<tr>$buf</tr>";
}
?>
</table>

<table>
<tr>
	<td>Key:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffc235">&nbsp; &nbsp;</td>
	<td>=SuperUser</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffff99">&nbsp; &nbsp;</td>
	<td>=full access to module</td>
</tr>
</table>


</td><td width="50%" valign="top">

<?php if (!$denyEdit) {?>

<table cellspacing="1" cellpadding="2" border="0" class="std" width="100%">
<form name="frmPerms" method="post" action="?m=admin">
<input type="hidden" name="del" value="0">
<input type="hidden" name="dosql" value="aed_perms">
<input type="hidden" name="user_id" value="<?php echo $user_id;?>">
<input type="hidden" name="permission_id" value="0">
<input type="hidden" name="permission_item" value="-1">
<?php
	$return = "m=admin&a=viewuser&user_id=$user_id" . ($tab ? "&tab=$tab" : '');
?>
<input type="hidden" name="return" value="<?php echo $return ;?>">
<tr>
	<th colspan="2">Add or Edit Permissions</th>
</tr>
<tr>
	<td nowrap align="right">Module:</td>
	<td width="100%"><?php echo arraySelect($modules, 'permission_grant_on', 'size="1" class="text"', 0);?></td>
</tr>
<tr>
	<td nowrap align="right">Item:</td>
	<td>
		<input type="text" name="permission_item_name" class="text" size="30" value="all" disabled>
		<input type="button" name="" class="text" value="..." onclick="popPermItem();">
	</td>
</tr>
<tr>
	<td nowrap align="right">Level:</td>
	<td><?php echo arraySelect($pvs, 'permission_value', 'size="1" class="text"', 0);?></td>
</tr>
<tr>
	<td>
		<input type="reset" value="clear" style="font-size:9px;width:100px;" name="sqlaction" onClick="clearIt();">
	</td>
	<td align="right">
		<input type="submit" value="add" style="font-size:9px;width:100px;" name="sqlaction2">
	</td>
</tr>
</table>

<?php } ?>

</td>
</tr>
</form>
</table>
