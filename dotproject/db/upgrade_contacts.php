<?php
include('../includes/config.php');
include($dPconfig['root_dir'] . '/includes/main_functions.php');
require_once( $dPconfig['root_dir']."/includes/db_adodb.php" );
include($dPconfig['root_dir'] . '/includes/db_connect.php');
$users = db_loadList('SELECT * FROM users');
foreach ($users as $user)
{
        $sql = 'INSERT INTO contacts(
                contact_first_name,
                contact_last_name,
                contact_birthday,
                contact_company,
                contact_department,
                contact_email,
                contact_phone,
                contact_phone2,
                contact_mobile,
                contact_address1,
                contact_address2,
                contact_city,
                contact_state,
                contact_zip,
                contact_country,
                contact_icq,
                contact_icon,
                contact_owner)
                VALUES  (\'' . 
                $user['user_first_name'] . "', '" . 
                $user['user_last_name'] . "', '" .
                $user['user_birthday'] . "', " .
                dPgetParam($user, 'user_company', 0) . ", '" .
                dPgetParam($user, 'user_department', 0) . "', '" .
                $user['user_email'] . "', '" .
                $user['user_phone'] . "', '" .
                $user['user_home_phone'] . "', '" .
                $user['user_mobile'] . "', '" .
                $user['user_address1'] . "', '" .
                $user['user_address2'] . "', '" .
                $user['user_city'] . "', '" .
                $user['user_state'] . "', '" .
                $user['user_zip'] . "', '" .
                $user['user_country'] . "', '" .
                $user['user_icq'] . "', '" .
                $user['user_pic'] . "', '" .
                $user['user_owner'] . "')";

                db_exec($sql);
                echo $sql;
                $sql = 'UPDATE users 
                        SET user_contact=LAST_INSERT_ID() 
                        WHERE user_id = ' . $user['user_id'];
                db_exec($sql);
                echo $sql;
                

}


?>
