<?php
/**
 * @author Kariuki
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class User extends MY_Controller {

	function __construct() {
		parent::__construct();

		$this -> load -> helper(array('form', 'url'));
		$this -> load -> library(array('hcmp_functions', 'form_validation'));
	}


	public function index() {
		$data['title'] = "Login";
		$this -> load -> view("shared_files/login_pages/login_v", $data);
	}

	private function _submit_validate() {

		$this -> form_validation -> set_rules('username', 'Username', 'trim|required|callback_authenticate');

		$this -> form_validation -> set_rules('password', 'Password', 'trim|required');

		$this -> form_validation -> set_message('authenticate', 'Invalid login. Please try again.');

		return $this -> form_validation -> run();

	}

	public function login_submit() {

		$user = new Users();

		$password = $this -> input -> post('password');
		$username = $this -> input -> post('username');
		$returned_user = $user -> login($username, $password);

		//If user successfully logs in, proceed here
		if ($returned_user) {
			//Create basic data to be saved in the session
			$reply = Users::login($username, $password);
			$user_data = $reply -> toArray();

			$access_typeid = $user_data['usertype_id'];
			$fname = $user_data['fname'];
			$user_id = $user_data['id'];
			$lname = $user_data['lname'];
			$district_id = $user_data['district'];
			$facility_id = $user_data['facility'];
			$phone = $user_data['telephone'];
			$user_email = $user_data['email'];
			$county_id = $user_data['county_id'];
			$fullname = $fname . ' ' . $lname;
            $banner_name = '';
			$access_level = Access_level::get_access_level_name($access_typeid);

			$user_indicator = $access_level['user_indicator'];
            
            if ($user_indicator  == 'district') :
             //get county name
            $district_name = districts::get_district_name_($district_id);
            $banner_name = $district_name['district'];
            elseif ($user_indicator  == 'county') :            
            //get county name
            $county_name = Counties::get_county_name($county_id);
            $banner_name = $county_name['county'];
            elseif ($user_indicator  == 'facility' || $user_indicator == 'facility_admin') :
             //get county name
            $facility_name = Facilities::get_facility_name2($facility_id);
            $banner_name = $facility_name['facility_name'];
            endif;
   
			$session_data = array('county_id' => $county_id, 'phone_no' => $phone,
			'user_email' => $user_email, 'user_id' => $user_id, 'user_indicator' => $user_indicator,
			'fname' => $fname, 'lname' => $lname, 'facility_id' => $facility_id,
			'district_id' => $district_id, 'user_type_id' => 
			$access_typeid,'full_name' => $fullname,
			'banner_name'=>$banner_name);

			$this -> session -> set_userdata($session_data);
				
			//get menu items
			$menu_items = Menu::getByUsertype($access_typeid);
			//Create array that will hold all the accessible menus in the session
			$menus = array();
			$menuids = array();
			$counter = 0;
			foreach ($menu_items as $menu_item) {
				$menus[$counter] = array("menu_text" => $menu_item -> menu_text, "menu_url" => $menu_item -> menu_url, "menu_id" => $menu_item -> id, "parent_status" => $menu_item -> parent_status);
				$counter++;
				$menuids[] = $menu_item -> id;

			}
			$sub_menus = array();
			foreach ($menuids as $parentid) {

				$sub_items = Sub_menu::getByparent((int)$parentid);

				foreach ($sub_items as $item) {
					$sub_menus[] = array("submenu_text" => $item -> subm_text, "submenu_url" => $item -> subm_url, "menu_id" => $item -> parent_id);

				}

			}

			//Save this menus array in the session
			$this -> session -> set_userdata(array("menus" => $menus));
			//Save this sub menus array in the session
			$this -> session -> set_userdata(array("sub_menus" => $sub_menus));
			//creating a new log value
			Log::update_log_out_action($this -> session -> userdata('user_id'));
		
			$u1 = new Log();
			$action = 'Logged In';
			$u1->user_id = $this -> session -> userdata('user_id');
			$u1->action = $action;
			$u1->save();
			
			redirect('Home');
		} else {
			$data['popup'] = "errorpopup";
			$data['title'] = "Login";
			$this -> load -> view("shared_files/login_pages/login_v", $data);
		}
	}

	public function logout() {

		Log::update_log_out_action($this -> session -> userdata('user_id'));

		$this -> session -> sess_destroy();
		$data['title'] = "Login";
		$this -> load -> view("shared_files/login_pages/login_v", $data);
	}

	public function forgot_password() {

		$this -> load -> view("shared_files/login_pages/forgotpassword_v");

	}

	public function password_recovery() {

		$email = $_POST['username'];

		//check if user exists and is activated

		$myresult = Users::check_user_exist($email);

		//get user details
		if (count($myresult) > 0) {
			foreach ($myresult as $key => $value) {

				$user_id = $value["id"];
				$Usersname = $value["fname"] . " " . $value["lname"];
				$email_address = $value["email"];

			}

			//check if user requested for a password recovery in last 3 days.

			$check_code_request = Log_monitor::check_code_request($user_id);

			if (count($check_code_request) > 0) {
				$data['user_email'] = $email_address;
				$data['popup'] = "request_valid";
				$data['title'] = "Password Recovery";
				$this -> load -> view("shared_files/login_pages/enter_reset_code_v", $data);

			} else {

				//generate random code
				$range = microtime(true);
				$rand = rand(0, $range);
				//encrypt code
				$save_code = md5($rand);

				//Send Code to User
				$subject = "Request For Password Reset";
				$message = "<html><body>
		<div style='border-color: #666; margin:auto;'>	
		Hi " . $Usersname . ", </br>
		<p>
		You (HCMP Account - " . $email_address . " - )  recently requested for a password reset.</br>
		If you made this request this is your reset code.</br></p> 
		<p>
		<strong style='font-size:20px;'>" . $rand . "</strong>
		</p>
		<p>
		<strong style='font-size:16px;'>This code will expire in 3 Days.</strong>
		</p>
		<p>
		If you didn't request for a password reset,your account might have been hijacked. </br>
		To get back into your account, you'll need to reset your password or contact your administrator.
		</p>
		<p>
		Yours sincerely,</br>
		</p>
		<p>
		The HCMP team.</br>
		</p>
		This email can't receive replies. </br> For more information, Contact your Administrator.
		</body></html>";

				//exit;

				$this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file = NULL, $bcc_email = NULL, $cc_email = NULL);

				//log the password recovery requests as a code
				$Logthis = new Log_monitor();
				$Logthis -> user_id = $user_id;
				$Logthis -> log_activity = 4;
				$Logthis -> forgetpw_code = $save_code;
				$Logthis -> status = 1;
				$Logthis -> save();

				//deactivate user

				Users::set_deactivate_for_recovery($user_id);

				$data['user_email'] = $email_address;
				$data['popup'] = "success";
				$data['title'] = "Password Recovery";
				$this -> load -> view("shared_files/login_pages/enter_reset_code_v", $data);

			}
		} else {

			$data['popup'] = "error_no_exist";
			$data['title'] = "Password Recovery";
			$this -> load -> view("shared_files/login_pages/forgotpassword_v", $data);

		}

	}

	public function confirm_code() {

		$reset_code = $_POST['code'];
		$email = $_POST['username'];
		$code = md5($reset_code);

		//get user details

		$userdetail_result = Users::check_user_exist($email);

		foreach ($userdetail_result as $key => $value) {

			$user_id = $value["id"];

		}
		//check if user requested for a password recovery

		$user_code_check_result = Log_monitor::check_code_exist($code, $user_id);

		if (count($user_code_check_result) > 0) {

			//echo "code correct";
			$data['user_email'] = $email;
			$data['popup'] = "success";
			$data['title'] = "Password Recovery";
			$this -> load -> view("shared_files/login_pages/recover_password_v.php", $data);

		} else {

			$data['user_email'] = $email;
			$data['popup'] = "error";
			$data['title'] = "Password Recovery";
			$this -> load -> view("shared_files/login_pages/enter_reset_code_v", $data);

		}

	}

	public function change_pw_recovery() {

		$email = $_POST['username'];
		$new_pass = $_POST['new_password'];
		$new_password_confirm = $_POST['new_password_confirm'];

		if ($new_pass != $new_password_confirm || $new_pass = "" || $new_password_confirm = "") {
			$data['user_email'] = $email;
			$data['popup'] = "error_nomatch";
			$data['title'] = "Password Recovery";
			$this -> load -> view("shared_files/login_pages/recover_password_v.php", $data);
		} else {
			//get user details
			$new_password_confirm = $_POST['new_password_confirm'];

			$userdetail_result = Users::check_user_exist($email);

			foreach ($userdetail_result as $key => $value) {

				$user_id = $value["id"];

			}

			Users::reset_password($user_id, $new_password_confirm);
			Log_monitor::deactivate_code($user_id);
			$data['user_email'] = $email;
			$data['popup'] = "passwordchange";
			$data['title'] = "Login";
			$this -> load -> view("shared_files/login_pages/login_v.php", $data);

		}

	}

	public function user_create() {

		//get user details in session

		$identifier = $this -> session -> userdata('user_indicator');
		$user_type_id = $this -> session -> userdata('user_type_id');
		$district = $this -> session -> userdata('district_id');
		$county = $this -> session -> userdata('county_id');
		

		//query to get user listing by type of user

		switch ($identifier):
			case 'moh':
			$permissions='moh_permissions';
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/dashboard_template_v';
			break;
			case 'facility_admin':
			$permissions='facilityadmin_permissions';
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/template';
			break;
			case 'district':
			$permissions='district_permissions';
			$data['listing']= Users::get_user_list_district($district);
			$data['facilities']=Facilities::getFacilities($district);
			$data['counts']=Users::get_users_district($district);
			$template = 'shared_files/template/template';
			break;
			case 'moh_user':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/dashboard_template_v';
			break;
			case 'district_tech':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/template';
			break;
			case 'rtk_manager':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/template';
			break;
			case 'super_admin':
			$permissions='super_permissions';
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/dashboard_template_v';
			break;
			case 'allocation_committee':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/template';
			break;	
			case 'county':
			$permissions='county_permissions';
			$data['listing']= Users::get_user_list_county($county);	
			$data['district_data'] = districts::getDistrict($county);
			$data['counts']=Users::get_users_county($county);
			$template = 'shared_files/template/template';
			
			break;	
        endswitch;

        $data['title'] = "User Management";
		$data['user_types']=Access_level::get_access_levels($permissions);	
		$data['banner_text'] = "User Management";
		$data['content_view'] = "shared_files/user_creation_v";
		$this -> load -> view($template, $data);
	}

		public function get_user_type_json()	{
			
			$identifier = $this -> session -> userdata('user_indicator');	
			if ($identifier=="county") {
				$permissions='county_permissions';	
			} else {
				$permissions='district_permissions';	
			}
					
					
			echo json_encode(Access_level::get_access_levels($permissions));
		
		}

	public function addnew_user(){

		$county = $this -> session -> userdata('county_id');
		$identifier = $this -> session -> userdata('user_indicator');

		$fname = $_POST['first_name'];
		$lname = $_POST['last_name'];
		$telephone = $_POST['telephone'];
		$email_address = $_POST['email'];
		$username = $_POST['username'];
		$facility_id = $_POST['facility_id'];
		$district_code = $_POST['district_name'];
		$user_type = $_POST['user_type'];
		$full_name= $fname .''.$lname; 
		
		switch ($identifier):
			case 'moh':
			
			break;
			case 'facility_admin':
			
			break;
			case 'district':
				
			$district_code=$this -> session -> userdata('district_id');
			
			break;
			case 'super_admin':
			
			case 'county':
			
			
			break;	
        endswitch;

		//Generate a activation code
		
				$range = microtime(true);
				$activation = rand(0, $range);
				//encrypt code to be saved
				$save_activation_code = md5($activation);
		
		//Send registered user email with password and validation link
		//
		$full_name = $fname.' '.$lname;
		$link = base_url().'user/activation/'.$activation;
		
		$subject = "Account Activation";
				$message = "<html><body>
		<div style='border-color: #666; margin:auto;'>	
		Hi " . $full_name . ", </br>
		<p>
		You (HCMP Account - " . $email_address . " - ) was recently created.</br>
		Before your account can be activated , one last step must be taken to complete your registration.</br></p>
		<p>
		Please note - you must complete this last step to become a registered member.</br></p>
		<p>
		You will only need to visit this URL once to activate your account.</br></p> 

		<p>
		<strong style='font-size:16px;'>" . $link . "</strong>
		</p>
		
		<p>
		If you are still having problems activating your account please contact your Administrator.

		</p>
		
		<p>
		All the best,</br>
		</p>
		<p>
		The HCMP team.</br>
		</p>
		This email can't receive replies. </br> For more information, Contact your Administrator.
		</body></html>";

				//exit;

				$this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file = NULL, $bcc_email = NULL, $cc_email = NULL);

				//exit;

		//log the password recovery requests as a code
				$savethis =  new Users();
				$savethis -> fname = $fname;
				$savethis -> lname = $lname;
				$savethis -> email = $email_address;
				$savethis -> username = $username;
				$savethis -> password = "";
				$savethis -> activation = $save_activation_code ;
				$savethis -> usertype_id = $user_type;
				$savethis -> telephone = $telephone;
				$savethis -> district = $district_code;
				$savethis -> facility = $facility_id;
				$savethis -> status = 0;
				$savethis -> county_id = $county;
				$savethis -> save();
		


	}
	
	public function edit_user(){
		$county = $this -> session -> userdata('county_id');
		$identifier = $this -> session -> userdata('user_indicator');

		$fname = $_POST['fname_edit'];
		$lname = $_POST['lname_edit'];
		$status = $_POST['status'];
		$telephone_edit= $_POST['telephone_edit'];
		$email_edit = $_POST['email_edit'];
		$username_edit = $_POST['username_edit'];
		$facility_id_edit_district = $_POST['facility_id_edit_district'];
		$user_type_edit_district = $_POST['user_type_edit_district'];
		$district_name_edit = $_POST['district_name_edit'];
		$facility_id_edit= $_POST['facility_id_edit'];
		$user_id= $_POST['user_id'];
		
		if ($status=="true") {
			
			$status=1;
			
		} elseif($status=="false") {
			
			$status=0;
		}
		
		
		//update user
			$update_user = Doctrine_Manager::getInstance()->getCurrentConnection();
			$update_user->execute("UPDATE `user` SET fname ='$fname' ,lname ='$lname',email ='$email_edit',usertype_id =$user_type_edit_district,telephone ='$telephone_edit',
									district ='$district_name_edit',facility ='$facility_id_edit',status ='$status',county_id ='$county'
                                  	WHERE `id`= '$user_id'");
		
	}
		public function activation($myurl){
			
			$myurl=$this->uri->segment(3);
			$cipher= md5($myurl);
						
			//query to find match 
			Users::check_activation($cipher);
			$restrict= count(Users::check_activation($cipher));
			
			
			if ($restrict==0) {
				
    				$this -> load -> view('shared_files/404');
				}else {
					$this -> load -> view('shared_files/activation');
				}
	
		}
		
		public function activation_final_phase(){
			
			$email = $_POST['username'];
			$password = $_POST['new_password'];
			
			
			//confirm user exists and is inactive
			
			$data=Users::check_user_exist_activate($email);
			foreach ($data as $key => $value) {					
				
				$user_id=$value->id;
			}
			
			$new_password_confirm=$password ;
			
			Users::reset_password($user_id, $new_password_confirm);
			
			
		}

}
