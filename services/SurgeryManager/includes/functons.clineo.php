<?php


function _createClineoSurgeryAppointment($surgery)
{


    $q = "INSERT INTO sh_patient_schedule";


}


function createClineoAppointment($pid, $ddd, $time)
{
    //00:00:00
    $clineo_db = $GLOBALS['clineo_db'];
    $starttime = "";
    if ($time == "8:00" || $time == "8:05" || $time == "8:10") {
        $starttime = "08:00:00";
        $endtime = "08:15:00";
    }
    if ($time == "8:05" || $time == "8:20" || $time == "8:25") {
        $starttime = "08:15:00";
        $endtime = "08:30:00";
    }
    if ($time == "8:30" || $time == "8:35" || $time == "8:40") {
        $starttime = "08:30:00";
        $endtime = "08:45:00";
    }
    if ($time == "8:45" || $time == "8:50" || $time == "8:55") {
        $starttime = "08:45:00";
        $endtime = "09:00:00";
    }
    if ($time == "9:00" || $time == "9:05" || $time == "9:10") {
        $starttime = "09:00:00";
        $endtime = "09:15:00";
    }
    if ($time == "9:15" || $time == "9:20" || $time == "9:25") {
        $starttime = "09:15:00";
        $endtime = "09:30:00";
    }

    if ($time == "9:30") {
        $starttime = "09:30:00";
        $endtime = "09:45:00";
    }


    if ($time == "2:30" || $time == "2:35" || $time == "2:40") {
        $starttime = "14:30:00";
        $endtime = "14:15:00";
    }
    if ($time == "2:45" || $time == "2:50" || $time == "2:55") {
        $starttime = "14:45:00";
        $endtime = "15:00:00";
    }
    if ($time == "3:00" || $time == "3:05" || $time == "3:10") {
        $starttime = "15:00:00";
        $endtime = "15:15:00";
    }
    if ($time == "3:00" || $time == "3:05" || $time == "3:10") {
        $starttime = "15:00:00";
        $endtime = "15:15:00";
    }
    if ($time == "3:15" || $time == "3:20" || $time == "3:25") {
        $starttime = "15:15:00";
        $endtime = "15:30:00";
    }

    if ($time == "3:30") {
        $starttime = "15:30:00";
        $endtime = "15:15:00";
    }


    if ($starttime == "") return 0;


    $q = "INSERT INTO `decc_db`.`sh_patient_schedule` (
`apmnt_date` ,`apmnt_st_time` ,`apmnt_end_time` ,`userid` ,`pid` ,`time_slot_value` ,`visit_status_id` ,
`recur_ind` ,`bill_status_id` ,`visit_type` ,`note` ,
`pre_allocated_id` ,`pre_allocation_blockId` ,`delete_record` ,`last_upd_userid` ,`created_by_userid`
)
VALUES (
 '" . $ddd . "', '" . $starttime . "', '" . $endtime . "', '2', '" . $pid . "', '15', '15',
'N', '1' , 'POST OP' , '" . $time . "' ,
 '0', '0', 'N', '23', '23')";
    //echo($q);
    //echo($q);
    //return $clineo_db->query_insert($q);

    if ($insert_id = $clineo_db->query_insert($q)) {
        //echo("INSERT ID : ".$insert_id);

        $q = "INSERT INTO sh_patient_schedule_status_audit (
		patient_schedule_id,
		visit_status_id,
		created_by_userid,
		
		last_upd_userid,
		last_upd_dt) VALUES
		{
		'" . $insert_id . "',
		'15',
		'23',
		'23',
		'NOW()'
		}";
        $clineo_db->query_insert($q);

        return $insert_id;
    }
    return null;
}


function updateClineoAppointment($psd, $ddd, $time)
{
    //00:00:00
    $clineo_db = $GLOBALS['clineo_db'];
    $starttime = "";
    if ($time == "8:00" || $time == "8:05" || $time == "8:10") {
        $starttime = "08:00:00";
        $endtime = "08:15:00";
    }
    if ($time == "8:05" || $time == "8:20" || $time == "8:25") {
        $starttime = "08:15:00";
        $endtime = "08:30:00";
    }
    if ($time == "8:30" || $time == "8:35" || $time == "8:40") {
        $starttime = "08:30:00";
        $endtime = "08:45:00";
    }
    if ($time == "8:45" || $time == "8:50" || $time == "8:55") {
        $starttime = "08:45:00";
        $endtime = "09:00:00";
    }
    if ($time == "9:00" || $time == "9:05" || $time == "9:10") {
        $starttime = "09:00:00";
        $endtime = "09:15:00";
    }
    if ($time == "9:15" || $time == "9:20" || $time == "9:25") {
        $starttime = "09:15:00";
        $endtime = "09:30:00";
    }

    if ($time == "9:30") {
        $starttime = "09:30:00";
        $endtime = "09:45:00";
    }


    if ($time == "2:30" || $time == "2:35" || $time == "2:40") {
        $starttime = "14:30:00";
        $endtime = "14:15:00";
    }
    if ($time == "2:45" || $time == "2:50" || $time == "2:55") {
        $starttime = "14:45:00";
        $endtime = "15:00:00";
    }
    if ($time == "3:00" || $time == "3:05" || $time == "3:10") {
        $starttime = "15:00:00";
        $endtime = "15:15:00";
    }
    if ($time == "3:00" || $time == "3:05" || $time == "3:10") {
        $starttime = "15:00:00";
        $endtime = "15:15:00";
    }
    if ($time == "3:15" || $time == "3:20" || $time == "3:25") {
        $starttime = "15:15:00";
        $endtime = "15:30:00";
    }

    if ($time == "3:30") {
        $starttime = "15:30:00";
        $endtime = "15:15:00";
    }


    if ($starttime == "") return;


    $q = "UPDATE `decc_db`.`sh_patient_schedule` SET `apmnt_date`='" . $ddd . "' ,`apmnt_st_time` ='" . $starttime . "',`apmnt_end_time`='" . $endtime . "',`note`='" . $time . "' WHERE `patient_schedule_id` = '" . $psd . "'";

    //echo($q);
    //echo($q);
    //return $clineo_db->query_insert($q);

    if ($insert_id = $clineo_db->query_insert($q)) {

        return $insert_id;
    }
    return null;
}


?>