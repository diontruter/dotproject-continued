<?php
##
##	Companies: View User sub-table
##
GLOBAL $company_id; 

$sql = "
SELECT user_id, user_username, user_first_name, user_last_name
FROM users
WHERE user_company = $company_id
";

$rows = db_loadList( $sql, NULL, __LINE__ );
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>Login Name</td>
	<th>User Name</td>
</tr>
<?php
$s = '';
foreach ($rows as $row){
	$s .= '<tr><td>';
	$s .= '<a href="./index.php?m=admin&a=viewuser&user_id='.$row["user_id"].'">'.$row["user_username"].'</a>';
	$s .= '<td>'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
	$s .= '</tr>';
}
echo $s;
?>
</table>
