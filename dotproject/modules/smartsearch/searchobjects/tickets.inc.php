<?php 
class tickets {
	var $table = 'tickets';
	var $search_fields = array ("author","recipient","subject","type","cc","body","signature");
	var $keyword = null;
	
	
	function ctickets (){
		return new tickets();
	}
	
	function fetchResults(){
		global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);
		$outstring = "<th nowrap='nowrap' STYLE='background: #08245b' >".$AppUI->_('Tickets')."</th>\n";
		if($results){
			foreach($results as $records){
				$outstring .= "<tr>";
				$outstring .= "<td>";
				$outstring .= "<a href = \"index.php?m=ticketsmith&a=view&ticket=".$records["ticket"]."\">".$records["subject"]."</a>\n";
				$outstring .= "</td>\n";
			}
		$outstring .= "</tr>";
		}
		else {
			$outstring .= "<tr>"."<td>".$AppUI->_('Empty')."</td>"."</tr>";
		}
		return $outstring;
	}
	
	function setKeyword($keyword){
		$this->keyword = $keyword;
	}
	
	function _buildQuery(){
		$sql = "SELECT ticket, subject"
			 . "\nFROM $this->table"
			 . "\nWHERE";
		foreach($this->search_fields as $field){
			$sql.=" $field LIKE '%$this->keyword%' or ";
		}
		$sql = substr($sql,0,-4);
		return $sql;
	}
}
?>