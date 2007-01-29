<?php /* SMARTSEARCH$Id$ */
/**
* Contacts Class
*/
class contacts extends smartsearch {
	var $table = "contacts";
	var $table_module	= "contacts";
	var $table_key = "contact_id";
	var $table_link = "index.php?m=contacts&a=view&contact_id=";
	var $table_title = "Contacts";
	var $table_orderby = "contact_last_name,contact_first_name";
	var $search_fields = array ("contact_last_name","contact_first_name","contact_title","contact_company","contact_type","contact_email",
								"contact_email2","contact_address1", "contact_address2", "contact_city", "contact_state", "contact_zip",
								"contact_country","contact_notes");
	var $display_fields = array ("contact_last_name","contact_first_name","contact_title","contact_company","contact_type","contact_email",
								"contact_email2","contact_address1", "contact_address2", "contact_city", "contact_state", "contact_zip",
								"contact_country","contact_notes");
	
	function ccontacts (){
		return new contacts();
	}
}
?>
