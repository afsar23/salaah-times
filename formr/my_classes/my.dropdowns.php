<?php
use Afsar\trp;


class MyDropdowns extends Dropdowns {
	
	// Add your own drop-down menu arrays here...

	private static function lst_lookup($sql) {

		global $db;

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
		$data = array_merge(array(''=>''),$data);				//   Please select...
		return array_flip($data);

	}

	public static function lst_gender() {
		
		$data = array(
			''		=> '',
			'Male' 	=> 'Male',
			'Female' 	=> 'Female'
		);
		
		return $data;
	}	
	
	public static function lst_reptype() {
		$sql = 'SELECT reptype, reptypeid FROM '.Afsar\trp\prefix('reptype').' ORDER by seqno';
		return self::lst_lookup($sql);
	}	

	public static function lst_maritalstatus() {
		$sql = 'SELECT maritalstatus, maritalstatusid FROM '.Afsar\trp\prefix('maritalstatus').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_height() {
		$sql = 'SELECT height, heightid FROM '.Afsar\trp\prefix('height').' ORDER by inches';
		return self::lst_lookup($sql);
	}	

	public static function lst_height_inches() {
		$sql = 'SELECT height, inches FROM '.Afsar\trp\prefix('height').' ORDER by inches';
		return self::lst_lookup($sql);
	}	

	public static function lst_location() {
		$sql = 'SELECT location, locationid FROM '.Afsar\trp\prefix('location').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_locationtree() {
		$sql = "
		select location, locationid  
		from ".Afsar\trp\LocationTree()." x
		order by loc_names
		";
		return self::lst_lookup($sql);	
	}

	public static function lst_nationality() {
		$sql = 'SELECT nationality, nationalityid FROM '.Afsar\trp\prefix('nationality').' ORDER by seqno';
		return self::lst_lookup($sql);
	}
	
	public static function lst_education() {
		$sql = 'SELECT education, educationid FROM '.Afsar\trp\prefix('education').' ORDER by seqno';
		return self::lst_lookup($sql);
	}
	
	public static function lst_education_seqno() {
		$sql = 'SELECT education, seqno FROM '.Afsar\trp\prefix('education').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_employmentstatus() {
		$sql = 'SELECT employmentstatus, employmentstatusid FROM '.Afsar\trp\prefix('employmentstatus').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_profession() {
		$sql = 'SELECT profession, professionid FROM '.Afsar\trp\prefix('profession').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_language() {
		$sql = 'SELECT language, languageid FROM '.Afsar\trp\prefix('language').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_sect() {
		$sql = 'SELECT sect, sectid FROM '.Afsar\trp\prefix('sect').' ORDER by seqno';
		return self::lst_lookup($sql);
	}

	public static function lst_caste() {
		$sql = 'SELECT caste, casteid FROM '.Afsar\trp\prefix('caste').' ORDER by seqno';
		return self::lst_lookup($sql);
	}
	
	public static function lst_appearance_male() {
		$sql = "SELECT appearance, appearanceid FROM ".Afsar\trp\prefix('appearance')." WHERE gender='M' ORDER by seqno";
		return self::lst_lookup($sql);
	}

	public static function lst_appearance_female() {
		$sql = "SELECT appearance, appearanceid FROM ".Afsar\trp\prefix('appearance')." WHERE gender='F' ORDER by seqno";
		return self::lst_lookup($sql);
	}

	public static function lst_practisinglevel() {
		$sql = 'SELECT practisinglevel, practisinglevelid FROM '.Afsar\trp\prefix('practisinglevel').' ORDER by seqno';
		return self::lst_lookup($sql);
	}	

	public static function lst_build() {
		$sql = 'SELECT build, buildid FROM '.Afsar\trp\prefix('build').' ORDER by seqno';
		return self::lst_lookup($sql);
	}	

	public static function lst_ukdrivinglicene() {
		$sql = 'SELECT ukdrivinglicence, ukdrivinglicenceid FROM '.Afsar\trp\prefix('ukdrivinglicence').' ORDER by seqno';
		return self::lst_lookup($sql);
	}	
	
	public static function lst_dereg_reason() {
		$sql = 'SELECT reason, reasonid FROM '.Afsar\trp\prefix('dereg_reason').' ORDER by seqno';
		return self::lst_lookup($sql);
	}
	
	
	public static function lst_registered_phones() {
		global $wpdb;
		$sql = "SELECT CONCAT(u.user_login, ' (',um.meta_value,')') user, um.meta_value
				FROM ".$wpdb->prefix."users u
				INNER JOIN ".$wpdb->prefix."usermeta um on um.user_id = u.ID and um.meta_key = 'phone_number'
				ORDER BY u.ID
				";
		return self::lst_lookup($sql);			
	}


	public static function lst_ages() {

		$lst=[];
		$lst[""]="";	
		for ($age=18; $age<=50; $age++) {
			$lst[$age] = $age;
		}
		return $lst;

	}
	
		// displays months of the year
	public static function lst_months() {
		
		$data = array(
			'01' => 'Jan',
			'02' => 'Feb',
			'03' => 'Mar',
			'04' => 'Apr',
			'05' => 'May',
			'06' => 'Jun',
			'07' => 'Jul',
			'08' => 'Aug',
			'09' => 'Sep',
			'10' => 'Oct',
			'11' => 'Nov',
			'12' => 'Dec'
		);
		
		return $data;
	}
	
	public static function lst_months_blank() {
		$data = array(
			'' => '',
			'01' => 'Jan',
			'02' => 'Feb',
			'03' => 'Mar',
			'04' => 'Apr',
			'05' => 'May',
			'06' => 'Jun',
			'07' => 'Jul',
			'08' => 'Aug',
			'09' => 'Sep',
			'10' => 'Oct',
			'11' => 'Nov',
			'12' => 'Dec'
			);
		return $data;
	}
	
	
	public static function lst_years() {
		
		$stop_date = date('Y')-18;
		
		// get the current year
		$start_date = date('Y')-60;
		
		// initialize the years array
		$years = array();
		
		// starting with the current year, 
		// loop through the years until we reach the stop date
		for($i=$start_date; $i<=$stop_date; $i++) {
			$years[$i] = $i;
		}
		$years['']='';
		
		// reverse the array so we have 1900 at the bottom of the menu
		$return = array_reverse($years, true);
		
		return $return;
	}


}

