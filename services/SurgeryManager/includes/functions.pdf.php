<?php
//require_once( 'forge_fdf.php' );
//require_once("FDF.php");

function _getPdfs2($date)
{
    //return $date;
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM pdfs where date='" . $date . "' ORDER BY created DESC";
    $rows = $db->fetch_all_array($q);
    $total = count($rows);
    $ret = array();
    $ret['q'] = $q;
    //return $ret;s
    if ($total < 1) {
        $ret['numbers'] = "empty";
        return $ret;
    }
    //return $rows;
    //  $q = "INSERT INTO pdfs (directory,filename,description,date) VALUES ('".$folder."','".$outputfile."','".$jpgfile."','".$date."')";
    if ($rows) {

        /*
            public $filename;
    public $description;
    public $created;
    */
        $ret2 = array();
        foreach ($rows as $s) {
            $pdf = new Pdf();
            $pdf->id = $s['id'];
            $pdf->filename = $s['filename'];
            $pdf->description = $s['description'];
            $pdf->datecreated = $s['created'];

            $ret2[] = $pdf;
        }
        $ret['pdfs'] = $ret2;
        return $ret;
    }
    return $ret;

}

function _deletePDF($url)
{
    $db = $GLOBALS['db'];
    $q = "DELETE FROM pdfs WHERE filename ='" . $url . "'";
    $db->query_insert($q);

}

function _createBookingCard($surgery)
{
    $ret = array();
    //return 0;
    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];


    $reg_datetime = date("y-m-d G:i:s", time()); //select the proper dates and times
    $visit_date = date("Y-m-d", time()); //for the forms
    $visit_time = date("G:i:s", time());
    $date_M_full = date("M d, Y", time());
    $today_year = date("Y");
    $today_year_s = date("y");
    $today_month = date("m");
    $today_month_F = date("F");
    $today_day = date("d");


    $patient = $surgery->patient;


    $fl_id = 76;


    /// insert form info into clineo and get form id
    $insert_query_string = "INSERT INTO co_form_usage (pid, userid, visit_date, visit_time, last_upd_dt, last_upd_userid, form_id, notes, status) VALUES ('" . $surgery->pid . "', '23', '" . $visit_date . "', '" . $visit_time . "', '" . $reg_datetime . "', '23', '" . $fl_id . "', '', 'ACTIVE')";

    $form_id = $clineo_db->query_insert($insert_query_string);
    $myid = $form_id;
    //$form_id = mysql_insert_id($link);
    $ret['q1'] = $insert_query_string;
    $ret['formid'] = $myid;

    $q = "SELECT * FROM co_patient p ,co_patient_detail d WHERE p.pid = d.pid AND p.pid='" . $surgery->pid . "'"; // AND co_patient.pid = '".$pid."'";
    $ret['q2'] = $q;
    $rows = $clineo_db->fetch_all_array($q);
    //print_r($rows);
    //return $rows;
    //$total = count($rows);
    $row = $rows[0];


    $query2 = "SELECT phycn_id, phycn_name FROM rf_physician WHERE phycn_id = '" . $row['family_phycn_id'] . "'";
    $family_phy_qry = $clineo_db->fetch_all_array($query2);
    //$family_phy_row = mysql_fetch_object($family_phy_qry);

    $family_phy_nm = $family_phy_qry[0]['phycn_name'];

    if ($family_phy_nm == "") $family_phy_nm = "N/A";


    $detail = $surgery->surgery_detail;


    $datestring = date("M d/Y", strtotime($surgery->dateString));


    $fdf = "%FDF-1.2
%����
1 0 obj
<< 
/FDF << /Fields [ 
<< /V (Dr. Christine Suess)/T (admit_physician)>>";
    $fdf .= "<< /T (date_of_surgery) /V (" . $datestring . ") >>
";
    if ($surgery->eye == "Left") {
        $fdf .= "<< /V (CATARACT OS)/T (admitting_diagnosis)>> ";
        $fdf .= "<< /V (PHACO W/FOLDABLE PCIOL \(Left\) )/T (surgical_procedure)>> ";
    } else {
        $fdf .= "<< /V (CATARACT OD)/T (admitting_diagnosis)>> ";
        $fdf .= "<< /V (PHACO W/FOLDABLE PCIOL \(Right\) )/T (surgical_procedure)>> ";
    }

    $fdf .= "<< /V /Off /T (anaesthetic_con_req)>>";


//FieldStateOption: general
//FieldStateOption: retrobulbar
//FieldStateOption: topical

    $ane = $detail['anaesthesia'][0];
    if ($ane == "General") {
        $fdf_data_names['anestheic_req'] = "general";
        //	$fdf_data_names['anestheic_req'] = "topical
        $fdf .= "<< /V /general /T (anestheic_req)>>
			";
    } elseif ($ane == "Retrobulbar") {
        $fdf .= "<< /V /retrobulbar /T (anestheic_req)>>
			";
    } else {
        $fdf .= "<< /V /topical /T (anestheic_req)>>
			";
    }
    $fdf .= "<< /V /new_booking /T (booking_status)>>
<< /V /Yes /T (day_surg)>> 
";

    if ($detail['diabetic_non_insulin']) {
        $fdf_data_names['diabetic_meds'] = "oral_meds";
    } else {
        $fdf_data_names['diabetic_meds'] = "not";
    }
    if ($stuff['diabetic_insulin']) {
        $fdf .= "<< /V /insulin /T (DIABETIC_MED)>>";
    } else {
        $fdf .= "<< /V /Off /T (DIABETIC_MED)>>";
    }

    $fdf .= "
<< /V /Off /T (docs_to_fax_consent)>> 
<< /V /Off /T (docs_to_fax_consult)>> 
<< /V /Off /T (docs_to_fax_hx_phyc)>> 
<< /V /Off /T (docs_to_fax_other)>> 

<< /V /Off /T (Group13)>> 
<< /V /Off /T (Group3)>> 
<< /V /Off /T (Group5)>>
<< /V /Off /T (in_pat_admit)>> 
";
    if ($detail['latex']) {
        $fdf .= " << /V /yes /T (LATEX)>>
";
    } else {
        $fdf .= " << /V /Off /T (LATEX)>>
";
    }
    $fdf .= "
<< /V (Wtis Priority " . $detail['wtis_priority'][0] . "    Dart : " . $detail['dart_number'][0] . ")/T (comments)>>
<< /V (" . $detail['wtis_id'][0] . ")/T (FillText9)>>
<< /V (0)/T (length_of_stay)>> 
<< /V /Off /T (man_care_plan)>> 
<< /V /Off /T (MARITAL_STATUS)>> 
<< /V /Off /T (POST_OP_CCU)>> 
<< /V /Off /T (post_op_home)>> 
<< /V /Off /T (preop_tests_req_cbc)>> 
<< /V /Off /T (preop_tests_req_ekg)>> 
<< /V /Off /T (preop_tests_req_pt_ptt)>> 
";

    $gender = $row['gender'];
    if ($gender == 1) {
        $fdf .= "<< /V /M /T (SEX)>>";
    } else {
        $fdf .= "<< /V /F /T (SEX)>>";
    }

    $fdf .= "
<< /V /mcconnell_site /T (site)>>";


    if ($surgery->surgeryLength == 30) {
        $fdf .= "<< /V (30 mins)/T (surg_time_req)>>
	";

    } elseif ($surgery->surgeryLength == 45) {
        $fdf .= "<< /V (45 mins)/T (surg_time_req)>>
	";

    } else {
        $fdf .= "<< /V (25 mins)/T (surg_time_req)>>
	";
    }


    $fdf .= "<< /V (Dr. Christine Suess)/T (surgeon)>>

<< /V /Off /T (type_of_adm)>> 
<< /V /Off /T (uninsure_srv)>> 

<< /T (name)/V (" . $patient->full_name . ")>>
<< /T (l_name)/V (" . $row['lastname'] . ")>>
<< /T (f_name)/V (" . $row['firstname'] . ")>>
<< /T (m_name)/V ()>> 
<< /T (fullname)/V (" . $patient->full_name . ")>>
<< /T (pt_re)/V (" . $patient->full_name . "\nP.O.Box 3232  High Street Milton, ON\n DOB: 2001-01-01\n OHIP: 1234512345AB\n PHONE: 905 1234567)>>
<< /T (dr_info)/V ()>> 
<< /T (salutation)/V ()>> 
<< /T (sex_m)/V (M)>> 
<< /T (sex)/V (M)>>";


/// calculate age

    // Calculate Differences Between Birthday And Now
    // By Subtracting Birthday From Current Date

    $row2 = split("-", $row['birthdate']);
    //birthdate = "2001-01-01"


    $ddiff = date("d") - $row2[2];
    $mdiff = date("m") - $row2[1];
    $ydiff = date("Y") - $row2[0];

    // Check If Birthday Month Has Been Reached
    if ($mdiff < 0) {
        // Birthday Month Not Reached
        // Subtract 1 Year From Age
        $ydiff--;
    } elseif ($mdiff == 0) {
        // Birthday Month Currently
        // Check If BirthdayDay Passed
        if ($ddiff < 0) {
            //Birthday Not Reached
            // Subtract 1 Year From Age
            $ydiff--;
        }
    }
    if ($gender == 1) {
        $fdf .= "<< /T (age_sex)/V (" . $ydiff . " - Male)>> ";
    } else {
        $fdf .= "<< /T (age_sex)/V (" . $ydiff . " - Female)>> ";
    }


    /*		$fdf_data_strings['m_address'] = $row['madr_streetno']." ".$row['madr_streetname'];
            $fdf_data_strings['m_city'] = $row['madr_city'];
            $fdf_data_strings['m_prov'] = $row['madr_prov'];
            $fdf_data_strings['m_pc'] = $row['madr_pc'];
            $fdf_data_strings['h_f_phone'] = "(".$row['home_tel_ac'].")".$row['home_tel'];
            $fdf_data_strings['w_f_phone'] = "(".$row['bus_tel_ac'].")".$row['bus_tel'];
            $date_M_full = date("M d, Y",time());
        $today_year = date("Y");
        $today_year_s = date("y");
        $today_month = date("m");
        $today_month_F = date("F");
        $today_day = date("d");
    */


    $fdf .= "<< /T (pid)/V (" . $surgery->pid . ")>>
<< /T (date)/V (" . $visit_date . ")>>
<< /T (date_M_full)/V (" . $date_M_full . ")>>
<< /T (ohip)/V (" . $row['ohip'] . ")>>
<< /T (f_ohip)/V (" . $row['ohip'] . ")>>
<< /T (version)/V ()>> 
<< /T (dob)/V (" . $row['birthdate'] . ")>>
<< /T (dob_y)/V (" . $row2[0] . ")>>
<< /T (dob_m)/V (" . $row2[1] . ")>>
<< /T (dob_d)/V (" . $row2[2] . ")>>
<< /T (m_apt)/V ()>> 
<< /T (m_city)/V (" . $row['madr_city'] . ")>>
<< /T (m_prov)/V (" . $row['madr_prov'] . ")>>
<< /T (m_pc)/V (" . $row['madr_pc'] . ")>>
<< /T (date_y)/V (" . $today_year . ")>>
<< /T (date_m)/V (" . $today_month . ")>>
<< /T (date_d)/V (" . $today_day . ")>>
<< /T (wt_y)/V (" . $today_year . ")>>
<< /T (wt_m)/V (" . $today_month . ")>>
<< /T (wt_d)/V (" . $today_day . ")>>
<< /T (m_address)/V (" . $row['madr_streetno'] . " " . $row['madr_streetname'] . " " . $row['madr_city'] . "," . $row['madr_prov'] . ")>>
<< /T (m_f_address)/V (" . $row['madr_streetno'] . " " . $row['madr_streetname'] . ")>>
<< /T (m_address1)/V (" . $row['madr_streetno'] . " " . $row['madr_streetname'] . ")>>
<< /T (m_address2)/V (" . $row['madr_city'] . "," . $row['madr_prov'] . ")>>
<< /T (m_address3)/V (" . $row['madr_city'] . "," . $row['madr_prov'] . " " . $row['madr_pc'] . ")>>
<< /T (h_area)/V (" . $row['home_tel_ac'] . ")>>
<< /T (h_phone)/V (" . $row['home_tel'] . ")>>
<< /T (h_f_phone)/V (" . $row['home_tel_ac'] . " " . $row['home_tel'] . ")>>
<< /T (w_area)/V (" . $row['bus_tel_ac'] . ")>> << /T (w_phone)/V (" . $row['bus_tel'] . ")>>
<< /T (w_f_phone)/V (" . $row['bus_tel_ac'] . " " . $row['bus_tel'] . ")>>
<< /T (w_ext)/V (0)>> << /T (r_apt)/V (20)>> 
<< /T (r_address)/V (" . $row['madr_streetno'] . " " . $row['madr_streetname'] . ")>>
<< /T (r_city)/V (" . $row['madr_city'] . ")>>
<< /T (r_pc)/V (" . $row['madr_pc'] . ")>>
<< /T (mrp)/V (Dr. Christine Suess)>> 
<< /T (mrp1)/V (Dr. Christine Suess)>> 
<< /T (mrp_first_name)/V (Christine)>> 
<< /T (mrp_last_name)/V (Suess)>> 
<< /T (mrp_f_init)/V (C)>> 
<< /T (physician)/V (Ryan Suess)>> 
<< /T (billing_no)/V ()>>
 << /T (p_no)/V ()>> 
 << /T (p_group)/V ()>> 
<< /T (p_lname)/V ()>> 
<< /T (p_fname)/V ()>>
 << /T (p_fullname)/V ( )>> 
<< /T (p_fullname_cre)/V (  )>> 
<< /T (p_fullname_cre_title)/V ( )>> 
<< /T (website)/V ()>> 
<< /T (p_finit)/V (R)>> 
<< /T (clinic_name)/V (Christine Suess Medicine Professional Corporation)>> 
<< /T (p_org_name)/V (Christine Suess Medicine Professional Corporation)>> 
<< /T (p_dept)/V ()>>
 << /T (p_address2)/V (Cornwall, ON)>> 
 << /T (p_address3)/V (Cornwall, ON K6H1B1)>> 
<< /T (p_address1)/V (10 Montreal Road)>> 
<< /T (p_address)/V (10 Montreal Road)>> 
<< /T (p_f_address)/V (10 Montreal Road Cornwall, ON K6H1B1)>> 
<< /T (footer)/V (10 Montreal Road Cornwall, ON K6H1B1 Phone: 613 9369130 Fax: 613 9369727)>> 
<< /T (p_city)/V (Cornwall)>> 
<< /T (p_prov)/V (ON)>> 
<< /T (p_pc)/V (K6H1B1)>> 
<< /T (p_ac)/V (613)>> 
<< /T (p_phone)/V (9369130)>> 
<< /T (p_f_phone)/V (613 9369130)>> 
<< /T (p_email)/V ()>> << /T (p_fax_ac)/V (613)>> 
<< /T (p_fax)/V (9369727)>> 
<< /T (p_f_fax)/V (613 9369727)>> 
<< /T (family_doc)/V (" . $family_phy_nm . ")>>
<< /T (family_physician)/V (" . $family_phy_nm . ")>>
<< /T (family_clinic_name)/V ()>> 
<< /T (family_clinic_address_1)/V ()>> 
<< /T (family_clinic_address_2)/V ()>> 
<< /T (family_clinic_phone)/V ()>> 
<< /T (family_clinic_fax)/V ()>> 
<< /T (family_physician_provider_no)/V ()>> 
<< /T (pmhx)/V ()>> 
<< /T (pohx1)/V (rthjsrh | 2008-08-21 | 2008-08-21 |  | 2008-08-21 |)>> 
<< /T (pohx)/V (rthjsrh | 2008-08-21 | 2008-08-21 |  | 2008-08-21 |)>> 
<< /T (all1)/V (NKDA)>> 
<< /T (all)/V (NKDA)>> 
<< /T (meds)/V ()>> 
<< /T (save)/A << /S /SubmitForm /F (http://192.168.0.99:80/sn_mr_form_save.php)>> >> 
] 
/F (http://192.168.0.99/form/" . $surgery->pid . "/" . $myid . ".pdf)/ID [ <36b9208950696bc0a5834bc2e34e14c8><c35cabeb60b1ce4ff5081a8d0ada4002>
] >> 
>> 
endobj
trailer
<<
/Root 1 0 R 

>>
%%EOF
";
    $fdf_fn = '\\\\192.168.0.99\\patientForms\\' . $surgery->pid . '\\' . $form_id . '.fdf';

    $ret['fdf'] = $fdf_fn;


    if (!file_exists("\\\\192.168.0.99\\patientForms\\$surgery->pid")) {
        try {
            mkdir("\\\\192.168.0.99\\patientForms\\$surgery->pid", 0777);

        } catch (Exception $e) {
            //var_dump($e->getMessage());
            $ret['error'] = $e->getMessage();
            return $ret;
        }
    }
    @chmod("\\\\192.168.0.99\\patientForms\\$surgery->pid", 0777);
    if (@copy('\\\\192.168.0.99\\patientForms\\master\\' . $fl_id . '.pdf', '\\\\192.168.0.99\\patientForms\\' . $surgery->pid . '\\' . $form_id . '.pdf')) {

        try {
            $fp = @fopen($fdf_fn, 'w');
            //$data = $fdf->createFDF();
            //if( $fp ) {
            fwrite($fp, $fdf);
            fclose($fp);
            $ret['created_fdf'] = "yes";

        } catch (Exception $e) {
            $ret['error2'] = $e->getMessage();
            return $ret;
        }

        $q = "UPDATE surgeries SET bookingCardId='" . $form_id . "' WHERE id='" . $surgery->sid . "'";
        $ret['q3'] = $q;
        $db->query_insert($q);
        return $ret;

    } else {
        $ret['whaterror'] = "adfsadsf";
        return $ret;

    }


}


function createFdF($pdfname, $dataarray)
{
//print_r($dataarray);
    $header = "%FDF-1.2
%����
1 0 obj
<</Type /Catalog
/FDF 
	<<
		/F (" . $pdfname . ")
		/Fields 
			[";
    $footer = "]
	>>
>>

endobj
xref
0 2
0000000000 65535 f
0000000016 00000 n
trailer
<</Root 1 0 R
/Size 2
>>
startxref
3356
%%EOF
";

    $data = "";
    foreach ($dataarray as $k => $v) {
        $data .= "\t<</T (" . $k . ")/V (" . $v . ")>>
		";

    }

    $return = $header . $data . $footer;
    return $return;
}

function _getPdfInfoByDate($date)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM pdfData WHERE `date`='" . $date . "'";

    $rows = $db->fetch_all_array($q);
    $ret['raw'] = $rows;
    $a = array();
    foreach ($rows as $r) {
        //slot key value
        $a[$r['slot']][$r['key']] = $r['value'];

    }
    $ret['data'] = $a;
    return $a;
}


function _createSurgeryFaxCover($date)
{
    $db = $GLOBALS['db'];
    $folder = "F:/web/surgerymanager.christinesuessmpc.com/wwwroot/files/SurgeryFaxCovers/";
    $url = "http://surgerymanager.christinesuessmpc.com/files/SurgeryFaxCovers/";


    $subfolder = str_replace("-", "", $date);
    $folder .= $subfolder;
    $url .= $subfolder . "/";

    if (!is_dir($folder)) {
        mkdir($folder);
    }
    $count = 1;
    $handle = opendir($folder);
    while (false !== ($file = readdir($handle))) {
        //echo "$file\n";
        if (preg_match("/hospital_cover_sheet/i", $file)) {
            $count++;
        }
    }
    $fdfname = $folder . "/hospital_cover_sheet_" . $count . ".fdf";
    //$d = split("-",$date);
    $b = _getPdfInfoByDate($date);


    $a = _getSurgeriesByDate($date);
    $ret['surgeries'] = $a['surgeries'];
    $ret['pdfinfo'] = $b;
    $ret['queries'] = $a['query'];


    /////////////////////////
    //// If there is already a pdf for this date in the system, sort it out
    ////////////////////////


    $data = array();
    $data['filename'] = $folder . "/hospital_cover_sheet_" . $count;


    /// loop through etsurgerybydate and match up
    $c = $a['surgeries'];
    $ret['shittybangbang'] = array();

    foreach ($c as $surgery) {
        $new = true;
        foreach ($b as $slot => $row) {
            $name = $row['name'];
            $ret['shittybangbang'][] = $surgery->full_name . " == " . $name;
            $ret['shittybangbang'][] = $row;


            if ($surgery->full_name == $name) {
                $new = false;

                $n = "time" . $surgery->surgerynumber;
//				$data[$n] = $surgery->surgeryLength."/".$surgery->surgeryTime;
                $data[$n] = $surgery->surgeryTime;
                $n = "name" . $surgery->surgerynumber;
                $data[$n] = $surgery->full_name;
                $n = "eye" . $surgery->surgerynumber;
                $data[$n] = $surgery->eye;
                $n = "anaes" . $surgery->surgerynumber;
                if ($surgery->surgery_detail['anaesthesia'])
                    $data[$n] = $surgery->surgery_detail['anaesthesia'][0];
                $n = "special" . $surgery->surgerynumber;
                if ($surgery->surgery_detail['specialorders']) {
                    $aa = $surgery->surgery_detail['specialorders'];
                    $tmp = "";
                    if (count($aa) > 1) {
                        foreach ($aa as $ff) {
                            $tmp .= $ff . ",";
                        }
                        $len = strlen($tmp);
                        $tmp = substr($tmp, 0, $len - 1);

                    } else if (count($aa) == 1) {
                        $tmp = $aa[0];
                    }
                    $data[$n] = $tmp;
                }
                $n = "iol" . $surgery->surgerynumber;
                $data[$n] = $surgery->iol;
                $n = "recvd" . $surgery->surgerynumber;
                $data[$n] = $row['recvd'];
                $n = "iol" . $surgery->surgerynumber;
                $data[$n] = $row['iol'];
            }
        }

        if ($new == true) {

            $n = "time" . $surgery->surgerynumber;
//			$data[$n] = $surgery->surgeryLength."/".
            $data[$n] = $surgery->surgeryTime;
            $n = "name" . $surgery->surgerynumber;
            $data[$n] = $surgery->full_name;
            $n = "eye" . $surgery->surgerynumber;
            $data[$n] = $surgery->eye;
            $n = "anaes" . $surgery->surgerynumber;
            if ($surgery->surgery_detail['anaesthesia'])
                $data[$n] = $surgery->surgery_detail['anaesthesia'][0];
            $n = "special" . $surgery->surgerynumber;
            if ($surgery->surgery_detail['specialorders']) {
                $aa = $surgery->surgery_detail['specialorders'];
                $tmp = "";
                if (count($aa) > 1) {
                    foreach ($aa as $ff) {
                        $tmp .= $ff . ",";
                    }
                    $len = strlen($tmp);
                    $tmp = substr($tmp, 0, $len - 1);

                } else if (count($aa) == 1) {
                    $tmp = $aa[0];
                }
                $data[$n] = $tmp;
            }
            $n = "iol" . $surgery->surgerynumber;

            $data[$n] = $surgery->iol;

            $n = "recvd" . $surgery->surgerynumber;


        }
    }


    $data['fileid'] = $count;
    $data['filedate'] = $date;
    $data['date'] = $date;


    //$data['date'] = date("F d,Y",strtotime($date));

    //return $data;
    //$master = "F:/web/surgerymanager.christinesuessmpc.com/wwwroot/files/masters/hospitalcoversheet.pdf";

    $master = "F:/web/surgerymanager.christinesuessmpc.com/wwwroot/files/masters/HospitalCoverSheetMaster.pdf";

    /// CREATE FDF
    ////////////////
    $file = createFdF($master, $data);

    $fh = fopen($fdfname, 'w') or die("can't open file");

    fwrite($fh, $file);

    fclose($fh);

    //////////////////////////
    //// Merge fdf and pdf////
    //////////////////////////

    $outputfile = $folder . "/hospitalcoversheet_" . $count . "_combined.pdf";
    $jpgfile = $folder . "/hospitalcoversheet_" . $count . ".jpg";
    //return "pdftk ".$master." fill_form ".$fdfname." output ".$outputfile;
    //passthru("C:/Windows/System32/pdftk ".$master." fill_form ".$fdfname." output ".$outputfile);
    system("F:/web/surgerymanager.christinesuessmpc.com/wwwroot/files/pdftk.exe " . $master . " fill_form " . $fdfname . " output " . $outputfile, $out);
    //return $out;
    //$urlpdf = $url."hospitalcoversheet_".$count."_combined.pdf";
    $urlpdf = $url . "hospitalcoversheet_" . $count . "_combined.pdf";

    $urljpg = $url . "hospitalcoversheet_" . $count . ".jpg";

    ////////////////////
    //// Create JPG
    ////////////////////

    $line = "F:/web/surgerymanager.christinesuessmpc.com/wwwroot/files/convert.exe -debug Coder -resize 600x300 " . $outputfile . " " . $jpgfile;
    $ret['line'] = $line;
    //passthru($line,$out);
    $out = `$line`;
    $ret['out'] = $out;


    /////////////////////////
    /// insert into database
    /*
    INSERT INTO `pdfs`
  VALUES (null,
  "F:/websurgerymanager.christinesuessmpc.com/wwwroot/files/SurgeryFaxCovers/20101027",
  "http://surgerymanager.christinesuessmpc.com/files/SurgeryFaxCovers/20101027/hospitalcoversheet_1_combined.pdf",
  "http://surgerymanager.christinesuessmpc.com/files/SurgeryFaxCovers/20101027/hospitalcoversheet_1.jpg",
  null,
  "2010-10-27",
  0,
  0)
  */
    //$q = "INSERT INTO `pdfs` ('directory','filename','description','date') VALUES ('".$folder."','".$urlpdf."','".$urljpg."','".$date."')";

    $q = "INSERT INTO `pdfs`
VALUES (null,
'" . $folder . "',
'" . $urlpdf . "',
'" . $urljpg . "',
null,
'" . $date . "',
0,
0)";

    $ret['query'][] = $q;

    $db->query_insert($q);


    $ret['data'] = $data;

    return $ret;

    return true;


}


?>