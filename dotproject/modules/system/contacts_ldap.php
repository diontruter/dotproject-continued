<?php

$AppUI->savePlace();

$canEdit = !getDenyEdit( $m );
$canRead = !getDenyRead( $m );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$sql_table = "contacts";

//Modify this mapping to match your LDAP->contact structure
//For instance, of you want the contact_phone2 field to be populated out of, say telephonenumber2 then you would just modify
//	"physicaldeliveryofficename" => "contact_phone2",
// ro 
//	"telephonenumber2" => "contact_phone2",

$sql_ldap_mapping = array(
	"givenname" => "contact_first_name",
	"sn" => "contact_last_name",
	"title" => "contact_title",
	"companyname" => "contact_company",
	"department" => "contact_department",
	"employeeid" => "contact_type",
	"mail" => "contact_email",
	"telephonenumber" => "contact_phone",
	"physicaldeliveryofficename" => "contact_phone2",
	"postaladdress" => "contact_address1",
	"location" => "contact_city",
	"st" => "contact_state",
	"postalcode" => "contact_zip",
	"c" => "contact_country"
	);

$titleBlock = new CTitleBlock("Import Contacts from LDAP Directory", "", "admin", "");
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();


if (isset( $_POST['server'] )) {
	$AppUI->setState( 'LDAPServer', $_POST['server'] );
}
$server = $AppUI->getState( 'LDAPServer' ) ? $AppUI->getState( 'LDAPServer' ) : "";
//$server = "KMP00";

if (isset( $_POST['bind_name'] )) {
	$AppUI->setState( 'LDAPBindName', $_POST['bind_name'] );
}
$bind_name = $AppUI->getState( 'LDAPBindName' ) ? $AppUI->getState( 'LDAPBindName' ) : "";
//$bind_name = "dcordes";

if (isset( $_POST['bind_password'] )) {
	$bind_password=$_POST['bind_password'];
}

if (isset( $_POST['port'] )) {
	$AppUI->setState( 'LDAPPort', $_POST['port'] );
}
$port = $AppUI->getState( 'LDAPPort' ) ? $AppUI->getState( 'LDAPPort' ) : "389";

if (isset( $_POST['dn'] )) {
	$AppUI->setState( 'LDAPDN', $_POST['dn'] );
}
$dn = $AppUI->getState( 'LDAPDN' ) ? $AppUI->getState( 'LDAPDN' ) : "";
//$dn = "OU=USA,O=MINEBEA";

if (isset( $_POST['filter'] )) {
	$AppUI->setState( 'LDAPFilter', $_POST['filter'] );
}
$filter = $AppUI->getState( 'LDAPFilter' ) ? $AppUI->getState( 'LDAPFilter' ) : "(objectclass=Person)";
//$filter = "(objectclass=dominoPerson)"; 

if (isset( $_POST['import'] )) {
	$import=$_POST['import'];
}
if (isset( $_POST['test'] )) {
	$test=$_POST['test'];
}

?>
<form method="post">
<table border="0" cellpadding="2" cellspacing="1" width="600" class="std">
	<tr>
		<td align="right" nowrap="nowrap">Server:</td>
		<td><input type="text" name="server" value="<?php echo isset($server)?$server:""; ?>" size="50"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Port:</td>
		<td><input type="text" name="port" value="<?php echo isset($port)?$port:""; ?>" size="4"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Bind Name:</td>
		<td><input type="text" name="bind_name" value="<?php echo isset($bind_name)?$bind_name:""; ?>" size="50"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Bind Password:</td>
		<td><input type="password" name="bind_password" value="<?php echo isset($bind_password)?$bind_password:""; ?>" size="25"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Base DN:</td>
		<td><input type="text" name="dn" value="<?php echo isset($dn)?$dn:""; ?>" size="100"></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">Filter:</td>
		<td><input type="text" name="filter" value="<?php echo isset($filter)?$filter:""; ?>" size="100"></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="submit" name="test" value="Test Connection and Query"><input type="submit" name="import" value="Import Users"></td>
	</tr>
</table>
<pre>
<?php
echo "<b>";
if(isset($test)){
	echo $test;
}
if(isset($import)){
	echo $import;
}
echo "</b>\n<hr>";
if(isset($test) || isset($import)){

	$ds = @ldap_connect($server, $port);

	if(!$ds) {
	    if(function_exists("ldap_error")) {
		print ldap_error($ds)."\n"; 
	    } else {
		print "<span style='color:red;font-weight:bold;'>ldap_connect failed.</span>\n";
	    }
	} else {
		print "ldap_connect succeeded.\n";
	}

	if(!@ldap_bind($ds,$bind_name,$bind_password)) {
	    print "<span style='color:red;font-weight:bold;'>ldap_bind failed.</span>\n";
	    if(function_exists("ldap_error")) {
		print ldap_error($ds)."\n"; 
	    }
	} else {
		print "ldap_bind successful.\n";
	}

	$return_types = array();
	foreach ($sql_ldap_mapping as $ldap => $sql) {
		$return_types[] = $ldap;
	}

print "basedn: ".$dn."<br>";
print "expression: ".$filter."<br>";

	$sr = @ldap_search($ds,$dn,$filter,$return_types);
	
	if($sr){
		print "Search completed Sucessfully.\n";
	} else {
		print "Search Error: [".ldap_errno($ds)."] ".ldap_error($ds)."\n";
	}


?>
</pre>
<?php

//	print "Result Count:".(ldap_count_entries($ds,$sr))."\n";
	$info = ldap_get_entries($ds, $sr);
	if(!$info["count"]){
		print "No users were found.\n";
	} else {
		print "Total Users Found:".$info["count"]."\n<hr>";
?>
<table border="0" cellpadding="1" cellspacing="0" width="98%" class="std">
<?php
		if(isset($test)){
			foreach ($sql_ldap_mapping as $ldap => $sql) {
				print "<th>".$sql."</th>";
			}
		} else {
			$contacts = db_loadList( "SELECT contact_id, contact_first_name, contact_last_name FROM $sql_table" );
			foreach($contacts as $contact){
				$contact_list[$contact['contact_first_name']." ".$contact['contact_last_name']] = $contact['contact_id'];
			}
			unset($contacts);
		}
		
		for ($i = 0; $i<$info["count"]; $i++) {
			$pairs = array();
			print "<tr>\n";
			foreach ($sql_ldap_mapping as $ldap_name => $sql_name) {
				if(isset($info[$i][$ldap_name][0])){
					$val = clean_value($info[$i][$ldap_name][0]);
				} 
				if(isset($val)){
					//if an email address is not specified in Domino you get a crazy value for this field that looks like FOO/BAR%NAME@domain.com  This'll filter those values out.
					if(isset($test) && $sql_name=="contact_email" && substr_count($val,"%")>0){
					?>
						<td><span style="color:#880000;"><?php echo $AppUI->_('bad email address')?></span></td>
					<?php
						continue;
					}
					$pairs[$sql_name] = $val;
					if(isset($test)){
					?>
						<td><?php echo $val?></td>
					<?php
					}
				} else {
					?>
						<td>-</td>
					<?php
				}
			}

			if(isset($import)){
				$pairs["contact_order_by"] = $pairs["contact_last_name"]." ".$pairs["contact_first_name"];
				//Check to see if this value already exists.
				if(isset($contact_list[$pairs["contact_first_name"]." ".$pairs["contact_last_name"]])){
					//if it does, remove the old one.
					$pairs["contact_id"] = $contact_list[$pairs["contact_first_name"]." ".$pairs["contact_last_name"]];
					db_updateArray( $sql_table, $pairs, "contact_id");
					echo "<td><span style=\"color:#880000;\">There is a duplicate record for ".$pairs["contact_first_name"]." ".$pairs["contact_last_name"].", the record has been updated.</span></td>\n";
				} else {
					echo "<td>Adding ".$pairs["contact_first_name"]." ".$pairs["contact_last_name"].".</td>\n";
					db_insertArray($sql_table,$pairs);
				}
			}
			print "</tr>\n";

	/*
			for ($ii=0; $ii<$info[$i]["count"]; $ii++){
				$data = $info[$i][$ii];
				for ($iii=0; $iii<$info[$i][$data]["count"]; $iii++) {
					echo $data.":&nbsp;&nbsp;".$info[$i][$data][$iii]."\n";
				}
			}
	*/
			echo "\n";
		}
	}
echo "</table>";
	ldap_close($ds);
}

function clean_value($str){
	$bad_values = array("'");
	return str_replace($bad_values,"",$str);
}
?>
</table>
