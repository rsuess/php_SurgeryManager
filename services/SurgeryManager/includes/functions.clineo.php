<?php


function createPostOpTime($pid, $ddd, $first)
{

    $r = getNextPostOpTime($ddd, $first);
    $ret['getNextPostOpTime'] = $r;
    $ret['createClineoAppointment'] = createClineoAppointment($pid, $r['date'], $r['time']);

    return $ret;
}

function getNextPostOpTime($ddd, $first)
{

    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];

    /// get start time from surgerymanager database

    $d = "";
    $t = "";
    if ($first == "true") {

        $q = "SELECT * FROM `surgeryday` WHERE '_date'='" . $ddd . "' AND key='firstFollowupStartTime'";
        $ret['query'][] = $q;
        $a = $db->fetch_all_array($q);
        if (count($a) > 0) {
            $t = $a[0]['value'];
        } else {
            $t = 495;
        }
        $ret['firstfollowuptime'] = $t;
        $q = "SELECT * FROM `surgeryday` WHERE '_date'='" . $ddd . "' AND key='firstFollowupStartDate'";
        $ret['query'][] = $q;
        $a = $db->fetch_all_array($q);
        if (count($a) > 0) {
            $d = $a[0]['value'];
        } else {
            //$d = 870;
            $dt = strtotime($ddd . " +1 day");
            $d = date("Y-m-d", $dt);
        }
        $ret['firstfollowupdate'] = $d;
    } else {

        $q = "SELECT * FROM `surgeryday` WHERE '_date'='" . $ddd . "' AND key='secondFollowupStartTime'";
        $ret['query'][] = $q;
        $a = $db->fetch_all_array($q);
        if (count($a) > 0) {
            $t = $a['0']['value'];
        } else {
            $t = 870;
        }
        $ret['secondfollowuptime'] = $t;
        $q = "SELECT * FROM `surgeryday` WHERE '_date'='" . $ddd . "' AND key='secondFollowupStartDate'";
        $ret['query'][] = $q;
        $a = $db->fetch_all_array($q);
        if (count($a) > 0) {
            $d = $a['0']['value'];
        } else {
            $dt = strtotime($ddd . " +1 week");
            $d = date("Y-m-d", $dt);
        }
        $ret['secondfollowupdate'] = $d;
    }


    $time = changeTime($t);
    $lookingfortime = true;
    $donetime = "";


    while ($lookingfortime == true) {
        $q = "SELECT * FROM `sh_patient_schedule` WHERE `apmnt_date`='" . $d . "' AND `note` LIKE '%" . $time . "%'";
        $ret['query'][] = $q;
        $rows = $clineo_db->fetch_all_array($q);
        $ret['query'][] = "Count is " + count($a);
        if (count($rows) > 0) {
            $num = 0;
            foreach ($rows as $record) {
                if ($record['visit_status_id'] != 13 || $record['visit_status_id'] != 5) {
                    $num++;
                    $ret['query'][] = "Adding bitch";
                }

            }
            if ($num < 2) {
                $donetime = $time;
                $ret['query'][] = "the donetime is " + $donetime;
                $lookingfortime = false;
            }

        } else {
            $donetime = $time;
            $lookingfortime = false;
        }
        $t += 10;
        $time = changeTime($t);
    }

    $ret['time'] = $donetime;

    $ret['date'] = $d;
    return $ret;

}

function createClineoAppointment($pid, $ddd, $time)
{
    $o['pid'] = $pid;
    $o['ddd'] = $ddd;
    $o['time'] = $time;
    //return $o;
    //00:00:00
    $clineo_db = $GLOBALS['clineo_db'];
    $starttime = "";
    if ($time == "8:00" || $time == "8:05" || $time == "8:10") {
        $starttime = "08:00:00";
        $endtime = "08:15:00";
    }
    if ($time == "8:15" || $time == "8:20" || $time == "8:25") {
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


    if ($time == "2:30" || $time == "2:35" || $time == "2:40" || $time == "14:30" || $time == "14:35" || $time == "14:40") {
        $starttime = "14:30:00";
        $endtime = "14:45:00";
    }
    if ($time == "2:45" || $time == "2:50" || $time == "2:55" || $time == "14:45" || $time == "14:50" || $time == "14:55") {
        $starttime = "14:45:00";
        $endtime = "15:00:00";
    }
    if ($time == "3:00" || $time == "3:05" || $time == "3:10" || $time == "15:00" || $time == "15:05" || $time == "15:10") {
        $starttime = "15:00:00";
        $endtime = "15:15:00";
    }
    if ($time == "3:15" || $time == "3:20" || $time == "3:25" || $time == "15:15" || $time == "15:20" || $time == "15:25") {
        $starttime = "15:15:00";
        $endtime = "15:30:00";
    }

    if ($time == "3:30" || $time == "15:30") {
        $starttime = "15:30:00";
        $endtime = "15:45:00";
    }


    if ($starttime == "") {
        $ret['starttime is fucked'] = true;
        return $ret;
    }


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
    $ret['q'][] = $q;
    if ($insert_id = $clineo_db->query_insert($q)) {
        $ret['InsertId'] = $insert_id;
        $q = "INSERT INTO `sh_patient_schedule_status_audit` (  `patient_schedule_id` , `visit_status_id` , `created_by_userid` , `create_date` , `create_time` , `last_upd_userid` , `last_upd_dt` )VALUES ( '" . $insert_id . "', '15', '23', NOW(),NOW(), '0',NOW() );";
        $ret['q'][] = $q;
        if ($clineo_db->query_insert($q)) {
            $ret['insert_id'] = $insert_id;
            return $ret;
        }
        $ret["Error Clineo Second Stage"] = $q;
        return $ret;
    }
    $ret["Error Clineo First Stage "] = $q;
    return $ret;


}


function updateClineoAppointment($apptdate, $time, $clineo_id)
{

    //00:00:00
    $ret = array();
    $clineo_db = $GLOBALS['clineo_db'];
    $starttime = "";
    if ($time == "8:00" || $time == "8:05" || $time == "8:10") {
        $starttime = "08:00:00";
        $endtime = "08:15:00";
    }
    if ($time == "8:15" || $time == "8:20" || $time == "8:25") {
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
    if ($time == "14:30") {
        $time = "2:30";
    }
    if ($time == "14:35") {
        $time = "2:35";
    }
    if ($time == "14:40") {
        $time = "2:40";
    }
    if ($time == "14:45") {
        $time = "2:45";
    }
    if ($time == "14:50") {
        $time = "2:50";
    }
    if ($time == "14:55") {
        $time = "2:55";
    }
    if ($time == "15:00") {
        $time = "3:00";
    }
    if ($time == "15:05") {
        $time = "3:05";
    }
    if ($time == "15:10") {
        $time = "2:10";
    }
    if ($time == "15:15") {
        $time = "2:15";
    }
    if ($time == "15:20") {
        $time = "2:20";
    }
    if ($time == "15:25") {
        $time = "2:25";
    }
    if ($time == "15:30") {
        $time = "2:30";
    }


    if ($time == "2:30" || $time == "2:35" || $time == "2:40" || $time == "14:30" || $time == "14:35" || $time == "14:40") {
        $starttime = "14:30:00";
        $endtime = "14:45:00";
    }
    if ($time == "2:45" || $time == "2:50" || $time == "2:55" || $time == "14:45" || $time == "14:50" || $time == "14:55") {
        $starttime = "14:45:00";
        $endtime = "15:00:00";
    }
    if ($time == "3:00" || $time == "3:05" || $time == "3:10" || $time == "15:00" || $time == "15:05" || $time == "15:10") {
        $starttime = "15:00:00";
        $endtime = "15:15:00";
    }
    if ($time == "3:15" || $time == "3:20" || $time == "3:25" || $time == "15:15" || $time == "15:20" || $time == "15:25") {
        $starttime = "15:15:00";
        $endtime = "15:30:00";
    }

    if ($time == "3:30" || $time == "15:30") {
        $starttime = "15:30:00";
        $endtime = "15:45:00";
    }


    if ($starttime == "") {
        $ret['starttime is fucked'] = true;
        //return $ret;
    }


    if ($starttime == "") return "returned because time " . $time;

    $q = "UPDATE `decc_db`.`sh_patient_schedule` SET `apmnt_date`='" . $apptdate . "' ,`apmnt_st_time`='" . $starttime . "' ,`apmnt_end_time`='" . $endtime . "',`note`='" . $time . "' WHERE `patient_schedule_id`='" . $clineo_id . "'";
    //$clineo_db->query($q);
    // return $q;
    $ret['query'][] = $q;
    if ($clineo_db->query($q)) {

        $ret['insert_id'] = $clineo_id;
    }

    return $ret;
}


function getClineoTime($sid)
{
    $q = "SELECT * FROM sh_patient_schedule WHERE patient_schedule_id = '" . $sid . "'";
    //echo($q);

    $clineo_db = $GLOBALS['clineo_db'];
    $rows = $clineo_db->query($q);

    if ($record = $clineo_db->fetch_array($rows)) {
        $o['date'] = $record['apmnt_date'];
        $o['time'] = $record['note'];
        return $o;
    }
    return 0;
}


function delete_clineo_appt($id)
{
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "DELETE FROM sh_patient_schedule_status_audit WHERE patient_schedule_id='" . $id . "'";
    $clineo_db->query($q);
    $q = "DELETE FROM sh_patient_schedule WHERE patient_schedule_id='" . $id . "'";
    $clineo_db->query($q);

}

?>