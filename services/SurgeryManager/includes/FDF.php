<?

class FDF
{
    private $workingData;
    public $pid;
    public $id;

    public function __construct()
    {
        $this->workingData = "";
    }

    public function createHeader()
    {

        $fdf = "%FDF-1.2\x0d%\xe2\xe3\xcf\xd3\x0d\x0a"; // header
        $fdf .= "1 0 obj\x0d<< "; // open the Root dictionary
        $fdf .= "\x0d/FDF << "; // open the FDF dictionary
        $fdf .= "/Fields [ "; // open the form Fields array
        return $fdf;

        /*return "%FDF-1.2
            %âãÏÓ
            1 0 obj
            <<
            /FDF << /Fields [ "
            ;
            */
    }

    public function createFooter()
    {
        $fdf = "<< /T (save)/A << /S /SubmitForm /F (http://192.168.0.99:80/sn_mr_form_save.php)>> >>\x0a]\x0d
		/F (http://192.168.0.99/form/" . $pid . "/" . $id . ".pdf)/ID [ <36b9208950696bc0a5834bc2e34e14c8><c35cabeb60b1ce4ff5081a8d0ada4002>\x0d
		>> \x0d >> \x0d endobj \x0d trailer \x0d << \x0d /Root 1 0 R \x0d >> \x0d %%EOF \x0d\x0a
		n \x0d\x0a
		0000028340 00000 n \x0d\x0a
		0000028550 00000 n \x0d\x0a
		0000028752 00000 n \x0d\x0a
		0000029003 00000 n \x0d\x0a
		0000029209 00000 n \x0d\x0a 
		0000029473 00000 n \x0d\x0a 
		0000029613 00000 n \x0d\x0a 
		0000029828 00000 n \x0d\x0a 
		0000030092 00000 n \x0d\x0a 
		0000030250 00000 n \x0d\x0a 
		0000030402 00000 n \x0d\x0a 
		0000030554 00000 n \x0d\x0a 
		0000030706 00000 n \x0d\x0a 
		0000030857 00000 n \x0d\x0a 
		0000031007 00000 n \x0d\x0a 
		0000031165 00000 n \x0d\x0a 
		0000031323 00000 n \x0d\x0a 
		0000031481 00000 n \x0d\x0a 
		0000031639 00000 n \x0d\x0a 
		0000031797 00000 n \x0d\x0a 
		0000031955 00000 n \x0d\x0a 
		0000032113 00000 n \x0d\x0a 
		0000032361 00000 n \x0d\x0a 
		0000032570 00000 n \x0d\x0a 
		0000032679 00000 n \x0d\x0a 
		0000032924 00000 n \x0d\x0a 
		0000033133 00000 n \x0d\x0a 
		0000033242 00000 n \x0d\x0a 
		0000033399 00000 n \x0d\x0a 
		0000033556 00000 n \x0d\x0a 
		0000033713 00000 n \x0d\x0a 
		0000033867 00000 n \x0d\x0a 
		0000034021 00000 n \x0d\x0a 
		0000034175 00000 n \x0d\x0a 
		0000034423 00000 n \x0d\x0a  
		0000034633 00000 n \x0d\x0a 
		0000034744 00000 n \x0d\x0a 
		0000034998 00000 n \x0d\x0a 
		0000035208 00000 n \x0d\x0a 
		0000035319 00000 n \x0d\x0a 
		0000035564 00000 n \x0d\x0a 
		0000035774 00000 n \x0d\x0a 
		0000035885 00000 n \x0d\x0a 
		0000036127 00000 n \x0d\x0a 
		0000036337 00000 n \x0d\x0a 
		0000036448 00000 n \x0d\x0a 
		0000036691 00000 n \x0d\x0a 
		0000036901 00000 n \x0d\x0a 
		0000037012 00000 n \x0d\x0a 
		0000037255 00000 n \x0d\x0a 
		0000037465 00000 n \x0d\x0a 
		0000037576 00000 n \x0d\x0a 
		0000037817 00000 n \x0d\x0a 
		0000038027 00000 n \x0d\x0a 
		0000038138 00000 n \x0d\x0a 
		0000038410 00000 n \x0d\x0a 
		0000038706 00000 n \x0d\x0a 
		0000038820 00000 n \x0d\x0a 
		0000040170 00000 n \x0d\x0a 
		0000040444 00000 n \x0d\x0a 
		0000040743 00000 n \x0d\x0a 
		0000040857 00000 n \x0d\x0a 
		0000042207 00000 n \x0d\x0a 
		0000042246 00000 n \x0d\x0a 
		0000042523 00000 n \x0d\x0a 
		0000043470 00000 n \x0d\x0a 
		0000043662 00000 n \x0d\x0a 
		0000043684 00000 n \x0d\x0a 
		0000044632 00000 n \x0d\x0a 
		0000044904 00000 n \x0d\x0a 
		0000045091 00000 n \x0d\x0a 
		0000045783 00000 n \x0d\x0a 
		0000045805 00000 n \x0d\x0a 
		0000046484 00000 n \x0d\x0a 
		0000046506 00000 n \x0d\x0a 
		0000047191 00000 n \x0d\x0a 
		0000047213 00000 n \x0d\x0a 
		0000047903 00000 n \x0d\x0a 
		0000047925 00000 n \x0d\x0a 
		0000048589 00000 n \x0d\x0a 
		0000048611 00000 n \x0d\x0a 
		0000049279 00000 n \x0d\x0a 
		0000049301 00000 n \x0d\x0a 
		0000049816 00000 n \x0d\x0a 
		0000049838 00000 n \x0d\x0a 
		0000049914 00000 n \x0d\x0a 
		0000049990 00000 n \x0d\x0a 
		0000050079 00000 n \x0d\x0a 
		0000050168 00000 n \x0d\x0a 
		0000050272 00000 n \x0d\x0a 
		0000050541 00000 n \x0d\x0a 
		0000050630 00000 n \x0d\x0a 
		0000051078 00000 n \x0d\x0a  
		0000051102 00000 n \x0d\x0a 
		0000005208 00000 n \x0d\x0a 
		0000005897 00000 n \x0d\x0a 
		trailer \x0d
		<< \x0d
		/Size 225 \x0d
		/Info 18 0 R  \x0d
		/Root 22 0 R  \x0d
		/Prev 75919  \x0d
		/ID[<36b9208950696bc0a5834bc2e34e14c8><c35cabeb60b1ce4ff5081a8d0ada4002>] \x0d
		>> \x0d
		startxref \x0d
		0 \x0d
		%%EOF \x0d
			 
		22 0 obj \x0d
		<<  \x0d
		/Type /Catalog  \x0d
		/Pages 19 0 R  \x0d
		/AcroForm 23 0 R  \x0d
		/Metadata 20 0 R  \x0d
		>>  \x0d
		endobj \x0d
		23 0 obj \x0d
		<<  \x0d
		/Fields [ 26 0 R 27 0 R 28 0 R 29 0 R 30 0 R 31 0 R 32 0 R 33 0 R 34 0 R 35 0 R  \x0d
		36 0 R 37 0 R 38 0 R 39 0 R 40 0 R 41 0 R 42 0 R 43 0 R 44 0 R 48 0 R  \x0d
		50 0 R 52 0 R 56 0 R 57 0 R 1 0 R 2 0 R 66 0 R 67 0 R 68 0 R 3 0 R  \x0d
		73 0 R 74 0 R 4 0 R 5 0 R 85 0 R 86 0 R 6 0 R 7 0 R 8 0";

        return $fdf;
    }

    public function addData($key, $value)
    {
        //<< /T (date) /V (2010-6-16) /ClrF 2 /ClrFf 1 >>
        $value = str_replace("(", "\(", $value);
        $value = str_replace(")", "\)", $value);
        $workingData .= "<< /T (" . $key . ") /V (" . $value . ") /ClrF 2 /ClrFf 1 >>\x0d\x0a";
        return $workingData;
        //$workingData .= "343";
    }

    public function createFDF()
    {
        $out = "";
        $out = $this->createHeader();
        $out .= $workingData;
        $out .= $this->createFooter();
        //return "ATHGFDERTGHBVDSWERTGBVCDRTHGBVCDERGTHGBVCDFRGHNBVCDFGBVCSDFGVCXSDFG";

        return $out;

    }


}


?>