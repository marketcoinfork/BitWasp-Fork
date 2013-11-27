<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * General Library
 * 
 * A general library containing miscellaneous functions used throughout
 * the application.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	General
 * @author		BitWasp
 */
class General {
	
	protected $CI;

	/**
	 * Constructor
	 * 
	 * Load the CodeIgniter framework and load the general model.
	 */
	public function __construct() { 	
		$this->CI = &get_instance();
		$this->CI->load->model('general_model');
	}

	/**
	 * Expect keys
	 * 
	 * Ensures all the comment-separated entries in $str are present in 
	 * the array. Will set unpresent entries = NULL.
	 * 
	 * @param		string
	 * @param		array
	 * @return		array
	 */
	public function expect_keys($str, $array) {
		$keys = explode(",", $str);
		foreach($keys as $key) {
			if(!array_key_exists(trim($key), $array))
				$array[$key] = NULL;
		}
		return $array;
	}
	
	/**
	 * Random Data
	 * 
	 * Generate pseudo-random data of a specified length
	 * 
	 * @param		int
	 * @return		string
	 */
	public function random_data($length) {
		$data = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
		return $data;
	}
	
	/**
	 * Generate Salt
	 * 
	 * Generates a hash from random data.
	 * 
	 * @return		string
	 */
	public function generate_salt() {
		return $this->hash($this->random_data('512'));
	}
	
	/**
	 * Hash
	 * 
	 * Generate the sha512 hash of a supplied string, along with
	 * an optional salt. Performs this several times. This is done
	 * on passwords if javascript was disabled.
	 * 
	 * @param		string	$password
	 * @return		string
	 */
	public function hash($password){ 
		$sha_limit_loop = 10;
		
		$hash = $password;
		for($i = 0; $i < $sha_limit_loop; $i++) {
			$hash = hash('sha512', $hash);
		}
		return $hash;
	}
	
	/**
	 * Password
	 * 
	 * This function is used to create a hash based on a password and
	 * a salt. This is used in the second step of generating the password
	 * hash and is only done server side as it uses the salt.
	 * 
	 * @param	string	$password
	 * @param	string	$salt
	 * @return	string
	 */
	public function password($password, $salt = NULL) {
		$sha_limit_loop = 10;
		
		$hash = $password;
		for($i = 0; $i < $sha_limit_loop; $i++) {
			$hash = hash('sha512', $hash.$salt);
		}
		return $hash;
	}
	
	/**
	 * Unique Hash
	 * 
	 * Generates a unique hash, in the $table table, and column $column.
	 * Default length is 16 characters long.
	 * Generated by creating a salt, trimming it to the required length, 
	 * and checking if it's unique. Will loop until entry is unique.
	 * 
	 * @param		string
	 * @param		string
	 * @param		int
	 * @return		string
	 */ 
	public function unique_hash($table, $column, $length = 16){

		$hash = substr($this->hash($this->generate_salt()), 0, $length);
		// Test the DB, see if the hash is unique. 
		$test = $this->CI->general_model->check_unique_entry($table, $column, $hash);

		while($test == FALSE){
			$hash = substr($this->hash($this->generate_salt()), 0, $length);

			// Perform the test again, and see if the loop goes on.
			$test = $this->CI->general_model->check_unique_entry($table, $column, $hash);	
		}

		// Finally return the generated unique hash.
		return $hash;			
	}
		
	/**
	 * Matches Any
	 * 
	 * Determines if any values in in $arr matches $string.
	 * 
	 * @param		string
	 * @param		array
	 * @return		bool
	 */
	public function matches_any($str, array $arr) {
		foreach($arr as $val) {
			if (($str == $val) == TRUE) return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Role from ID
	 * 
	 * Used to determine which role the ID relates to.
	 * 	1 - Buyer
	 *  2 - Vendor
	 *  3 - Admin
	 * 
	 * @param		int
	 * @return		string
	 */
	public function role_from_id($id){
		switch($id){
			case '1':
				$result = 'Buyer';
				break;
			case '2':
				$result = 'Vendor';
				break;
			case '3':
				$result = 'Admin';
				break;
			default:
				$result = 'Buyer';
				break;
		}
		return $result;
	}
	
	/**
	 * Format Time
	 * 
	 * Create a human readable string of a timestamp.
	 * 
	 * @param		int
	 * @return		string
	 */
	public function format_time($timestamp){
		// Load the current time, and check the difference between the times in seconds.
		$currentTime = time();
		$difference = $currentTime-$timestamp;
		if ($difference < 60) {					// within a minute.
			return 'less than a minute ago';
		} else if($difference < 120) {			// 60-120 seconds.
			return 'about a minute ago';
		} else if($difference < (60*60)) {		// Within the hour. 
			return round($difference / 60) . ' minutes ago';
		} else if($difference < (120*60)) {		// Within a few hours.
			return 'about an hour ago';
		} else if($difference < (24*60*60)) {		// Within a day.
			return 'about ' . round($difference / 3600) . ' hours ago';
		} else if($difference < (48*60*60)) {		// Just over a day.
			return '1 day ago';
		} else if($timestamp == "0" || $timestamp == NULL){ //The timestamp wasn't set which means it has never happened.
			return 'Never';
		} else { // Otherwise just return the basic date.
			return date('j F Y',(int)$timestamp);
		}
	}
};

 /* End of file General.php */
