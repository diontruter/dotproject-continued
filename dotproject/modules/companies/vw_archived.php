<?php /* COMPANIES $Id$ */
##
##	Companies: View Archived Projects sub-table
##
GLOBAL $AppUI, $company_id; 

$sql = "
SELECT projects.*, contact_first_name,contact_last_name
FROM projects
LEFT JOIN users ON users.user_id = projects.project_owner
LEFT JOIN contacts ON user_contact = contact_id
WHERE project_company = $company_id
	AND project_active = 0
ORDER BY project_name
";

$s = '';
if (!($rows = db_loadList( $sql, NULL ))) {
	$s .= $AppUI->_( 'No data available' ).'<br />'.$AppUI->getMsg();
} else {
	$s .= '<tr>'
		.'<th>'.$AppUI->_( 'Name' ).'</td>'
		.'<th>'.$AppUI->_( 'Owner' ).'</td>'
		.'</tr>';

	foreach ($rows as $row){
		$s .= '<tr><td>';
		$s .= '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a>';
		$s .= '<td>'.$row["contact_first_name"].'&nbsp;'.$row["contact_last_name"].'</td>';
		$s .= '</tr>';
	}
}
echo '<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">' . $s . '</table>';

?>
