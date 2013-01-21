<?php
function _getTestingData($pid, $type = "")
{
    global $db;
    if ($type == "") {
        $q = "SELECT * FROM testinginformation WHERE pid='" . $pid . "'";
    } else {

        $q = "SELECT * FROM testinginformation WHERE pid='" . $pid . "' AND type='" . $type . "'";
    }
    $rows = $db->fetch_all_array($q);

    $ret = array();

    foreach ($rows as $record) {
        $ret[$record['type']][$record['key']][] = $record['value'];

    }
    return $ret;
}

function _saveTestingData($d)
{
    global $db;
    $ret['d'] = $d;
    if ($d['type'] == "testinginfo") {
        $pid = $d['pid'];
        $data = $d['data'];
        $q = "DELETE FROM testinginformation WHERE type ='testinginfo' AND pid='" . $pid . "'";
        $ret['query'][] = $q;
        $db->query($q);
        if (count($data) > 0) {
            //foreach($s_info as $sinfo)
            foreach ($data as $key => $value) {

                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q = "INSERT INTO testinginformation (`pid`,`type`,`key`,`value`) VALUES ('" . $pid . "','testinginfo','" . $key . "','" . $v . "')";
                        $ret['query'][] = $q;
                        //	echo($q);
                        $db->query_insert($q);
                    }
                } else {
                    $q = "INSERT INTO testinginformation (`pid`,`type`,`key`,`value`) VALUES ('" . $pid . "','testinginfo','" . $key . "','" . $value . "')";
                    $ret['query'][] = $q;
                    //	echo($q);
                    $db->query_insert($q);
                }
            }
        }


    }
    if ($d['type'] == "prescriptioninfo") {
        $pid = $d['pid'];
        $data = $d['data'];
        $q = "DELETE FROM testinginformation WHERE type ='prescriptioninfo' AND pid='" . $pid . "'";
        $ret['query'][] = $q;
        $db->query($q);
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q = "INSERT INTO testinginformation (`pid`,`type`,`key`,`value`) VALUES ('" . $pid . "','prescriptioninfo','" . $key . "','" . $v . "')";
                        $ret['query'][] = $q;
                        $db->query_insert($q);
                    }
                } else {
                    $q = "INSERT INTO testinginformation (`pid`,`type`,`key`,`value`) VALUES ('" . $pid . "','prescriptioninfo','" . $key . "','" . $value . "')";
                    $ret['query'][] = $q;
                    $db->query_insert($q);
                }
            }
        }


    }


    if ($d['type'] == "pprk") {
        $pid = $d['pid'];
        $data = $d['data'];
        $q = "DELETE FROM testinginformation WHERE type ='pprk' AND pid='" . $pid . "'";
        $ret['query'][] = $q;
        $db->query($q);
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q = "INSERT INTO testinginformation (`pid`,`type`,`key`,`value`) VALUES ('" . $pid . "','pprk','" . $key . "','" . $v . "')";
                        $ret['query'][] = $q;
                        $db->query_insert($q);
                    }
                } else {
                    $q = "INSERT INTO testinginformation (`pid`,`type`,`key`,`value`) VALUES ('" . $pid . "','pprk','" . $key . "','" . $value . "')";
                    $ret['query'][] = $q;
                    $db->query_insert($q);
                }
            }
        }


    }
    return $ret;
}

function process_patient($d)
{


    $p = new Patient();
    $p->pid = $d['pid'];
    $p->f_name = $d['firstname'];
    $p->m_name = $d['middlename'];
    $p->l_name = $d['lastname'];
    $p->salutation = $d['title_code'];
    //$p->dob =  strtotime($d['birthdate']);
    $p->dob = $d['birthdate'];
    $p->home_phone = $d['home_tel_ac'] . $d['home_tel'];
    $p->work_phone = $d['bus_tel_ac'] . $d['bus_tel'];
    $p->cell_phone = $d['mobile_tel_ac'] . $d['mobile_tel'];
    $p->sex = $d['gender'];
    $p->address = $d['madr_streetno'] . " " . $d['radr_streetname'];
    $p->city = $d['radr_city'];
    $p->province = $d['radr_prov'];
    $p->postal = $d['radr_pc'];
    $p->patient_detail = _getPatientInfo($d['pid']);
    if ($d['language'] == 0) {
        $p->language = "English";
    } else {
        $p->language = "French";
    }


    $p->full_name = $p->salutation . "." . $p->f_name;
    if (strlen($p->m_name > 0)) {
        $p->full_name .= " " . $p->m_name;
    }
    $p->full_name .= " " . $p->l_name;


    // get mca info
    $p->mca = "";
    $mca = $d['healthcard_no_other'];
    if (strstr($mca, "DVA") || count($mca) < 1) {
        $p->mca = "";
    } else {
        //$p->mca = preg_replace ('/[^\d\s]/', '', $mca) ;
        $p->mca = $mca;
    }

    $p->healthcardnumber = $d['ohip'];


    return $p;

}

function _savePatientInfo($a)
{
    $ret = "";
    global $db;
    $pid = $a['pid'];
    $data = $a['data'];

    //echo("YO YO YO YO");
    $ret['pid'] = $pid;
    $ret['data'] = $data;
    /// delete all old data for patient_info
    //return $a;
    $q = "DELETE FROM patient_info WHERE patient_id='" . $pid . "'";
    $ret['query'][] = $q;
    $db->query($q);
    if ($data != null) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $q = "INSERT INTO `patient_info` (`patient_id`,`catagory`,`key`,`variable` ) VALUES (" . $pid . ",'" . $key . "','" . $v . "','" . $v . "')";
                    $ret['query'][] = $q;
                    if ($v != "") {
                        $ret['query'][] = "Fluffy Done";
                        //	echo($q);
                        $db->query_insert($q);
                    }
                }
            } else {
                $q = "INSERT INTO `patient_info` (`patient_id`,`catagory`,`key`,`variable` ) VALUES (" . $pid . ",'" . $key . "','" . $value . "','" . $value . "')";
                $ret['query'][] = $q;
                if ($value != "") {
                    $ret['query'][] = "Fluffy Done";
                    //	echo($q);

                    $db->query_insert($q);
                }
            }
        }
    }
    return $ret;

}


function _getPatientInfo($pid)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM patient_info WHERE patient_id='" . $pid . "'";
    $rows = $db->fetch_all_array($q);

    $a = array();
    foreach ($rows as $r) {
        $a[$r['catagory']][] = $r['variable'];
    }
    if (count($a) < 1) {
        return null;
    }
    return $a;
}

function getpatientbypid($pid)
{
    //$db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];

    $q = "SELECT * FROM co_patient JOIN co_patient_detail ON co_patient.pid=co_patient_detail.pid WHERE `co_patient`.`pid`='" . $pid . "' LIMIT 1";


    $rows = $clineo_db->query($q);
    $ret = array();
    $record = $clineo_db->fetch_array($rows);
    if (!$record) {
        return null;
    }
    $patient = process_patient($record);
    return $patient;
}


function getPatientByName($fname, $lname)
{
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT * FROM co_patient JOIN co_patient_detail ON co_patient.pid=co_patient_detail.pid WHERE `co_patient`.`firstname` LIKE '%" . $fname . "%' AND `co_patient`.`lastname` LIKE '%" . $lname . "%' LIMIT 20";

    //echo($q);
    //$rows = $clineo_db->query($q);
    $ret = array();
    $record = $clineo_db->fetch_all_array($q);
    if (!$record) {
        return null;
    }
    $patients = array();
    foreach ($record as $r) {
        $patients[] = process_patient($r);
    }

    return $patients;
}


?>