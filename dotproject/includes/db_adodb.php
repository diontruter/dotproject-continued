<?php /* $Id$ */
/*
	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer
	-----------------------
	MYSQL VERSION
	-----------------------
	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/
require_once( "{$AppUI->cfg['root_dir']}/lib/adodb/adodb.inc.php" );

$db = NewADOConnection($AppUI->cfg['dbtype']);

function db_connect( $host='localhost', $dbname, $user='root', $passwd='', $port='3306', $persist=false ) {
        global $db;

	if ($persist) {
                $db->PConnect($host, $user, $passwd, $dbname)
			or die( 'FATAL ERROR: Connection to database server failed' );
	} else {
                $db->Connect($host, $user, $passwd, $dbname)
			or die( 'FATAL ERROR: Connection to database server failed' );
	}
        
        $ADODB_FETCH_MODE=ADODB_FETCH_ASSOC;
}

function db_error() {
        global $db;
	return $db->ErrorMsg();
}

function db_errno() {
        global $db;
	return $db->ErrorNo();
}

function db_insert_id() {
        global $db;
	return $db->Insert_ID();
}

function db_exec( $sql ) {
        global $db;

//        echo "Executing $sql";
	$qid = $db->Execute( $sql );
//        print_r($qid->GetAssoc());
	//if( !$qid ) {
	//	return false;
	//}
	return $qid;
}

function db_free_result( $cur ) {
        // TODO
        //	mysql_free_result( $cur );
        // Maybe it's done my Adodb
        ;
}

function db_num_rows( $qid ) {
	return $qid->RecordCount();
        //return $db->Affected_Rows();
}

function db_fetch_row( &$qid ) {
//        print_r($qid->GetAssoc());
	return $qid->FetchRow();
}

function db_fetch_assoc( &$qid ) {
        return $qid->FetchRow();
//	return $qid->GetAssoc();
}

function db_fetch_array( &$qid  ) {
        return $qid->FetchRow();
        
//	return $qid->GetArray();
}

function db_fetch_object( $qid  ) {
	return $qid->FetchObject();;
}

function db_escape( $str ) {
        global $db;
	return $db->qstr( $str );
}

function db_version() {
        return "ADODB";
}

function db_unix2dateTime( $time ) {
        global $db;
        return $db->DBDate($time);
}

function db_dateTime2unix( $time ) {
        global $db;

        return $db->UnixDate($time);

        // TODO - check if it's used anywhere...
//	if ($time == '0000-00-00 00:00:00') {
//		return -1;
//	}
}
?>
