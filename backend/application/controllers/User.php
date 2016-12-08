<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class User extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'User_model' );
		$this->load->database ();
		$this->load->library("JWT");
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
		define("ENCRYPTION_KEY", "itsSecret!");
		define('CONSUMER_KEY', 'itsSecret@234!key');
		define('CONSUMER_SECRET', 'thatsOnlysecret#$%');
		define('CONSUMER_TTL' , 86400);
		
	}
	public function index() {
		//$this->load->view ( 'welcome_message' );
		
	}
	public function create() {
		$userData = $this->input->post ();
		$error = array ();
		$result = array ();
		if (! isset ( $userData ['firstName'] )) {
			$error [] = 'firstname';
		}
		if (! isset ( $userData ['lastName'] )) {
			$error [] = 'lastName';
		}
		if (! isset ( $userData ['email'] )) {
			$error [] = 'email';
		}
		if (! isset ( $userData ['password'] )) {
			$error [] = 'password';
		}
		if (empty ( $error )) {
			if (! $this->User_model->getUserById ( $userData ['email'] )->status) {
				$result['id'] = $this->User_model->saveUser ( $userData );
				$this->load->library('email');
				$this->email->set_header('Content-type', 'text/html; charset=UTF-8');
				$this->email->set_header('MIME-VErsion', '1.0');
				$this->email->set_header('Content-type', 'text/html; charset=UTF-8');
				$this->email->from('no-reply@proshopping.com', 'ecom proshop');
				$this->email->to($userData['email']);
				$this->email->bcc('nrupen92@gmail.com');
				$this->email->subject('Wel come to Proshop');
				$msg = '<html>Hi '.$userData['firstName']. '<br> Please use below link to verify your email : http://capstone.devview.info/user/verifyUser?email='.urlencode(base64_encode($userData['email'])).'  </body></html>';
				$this->email->message($msg);
				$this->email->send();
				$user = $userData;
				$user['id'] = $result['id'];
				$result['auth_token'] = $this->generate_token($user);
				$result['status'] = TRUE;
			} else {
				$result ['msg'] = 'User Already Exists';
				$result ['status'] = false;
			}
		} else {
			$result ['error'] = $error;
		}
		echo json_encode ( $result );
		exit ();
	}
	public function login() {
		$userData = $this->input->post ();
		$error = array ();
		$result = array ();
		if (! isset ( $userData ['email'] )) {
			$error [] = 'email';
		}
		if (! isset ( $userData ['password'] )) {
			$error [] = 'password';
		}
		if (empty ( $error )) {
			$result = $this->User_model->checkLogin ( $userData ['email'], $userData ['password'] );
			if($result['status']== TRUE){
				$data = $result;
				$data['email'] = $userData['email'];
				$data['email'] = $userData['email'];
			}
		} else {
			$result ['error'] = $error;
		}
		echo json_encode ( $result );
		exit ();
	}
	public function update(){
		$userData = $this->input->post();
		$error = array ();
		$result = array ();
		$data = array();
		if (! isset ( $userData ['email'] )) {
			$error [] = 'email';
		}
		if ( isset ( $userData ['firstName'] )) {
			$data['firstname'] = $userData ['firstName'];
		}
		if (isset ( $userData ['lastName'] )) {
			$data ['lastname'] = $userData ['lastName'];
		}
		if (isset ( $userData ['password'] )) {
			$data ['password'] = $userData ['password'];
		}
		if (isset ( $userData ['security_question'] )) {
			$data ['security_question'] = $userData ['security_question'];
		}
		if (isset ( $userData ['security_answer'] )) {
			$data ['security_answer'] = $userData ['security_answer'];
		}if(isset($userData['user_role'])){
			$data ['user_role'] = $userData ['user_role'];
		}
		if(empty($error)){
			$result = $this->User_model->update_user($userData['email'], $data);
		}else{
			$result['status'] = false;
			$result['msg'] = 'User not found.';
		}
		echo json_encode($result);
		exit;
	}
	public function delete(){
		$userData = $this->input->post ();
		if(!empty($userData) && !empty($userData['email'])){
			$result = $this->User_model->delete_user($userData['email']);
		}else{
			$result['status'] = FALSE;
			$result['msg'] = 'User not found';
		}
		echo json_encode($result);
	}
	public function createAdmin(){
		$userData = $this->input->post ();
		$error = array ();
		$result = array ();
		if (! isset ( $userData ['firstName'] )) {
			$error [] = 'firstname';
		}
		if (! isset ( $userData ['lastName'] )) {
			$error [] = 'lastName';
		}
		if (! isset ( $userData ['email'] )) {
			$error [] = 'email';
		}
		if (! isset ( $userData ['password'] )) {
			$error [] = 'password';
		}
		if (empty ( $error )) {
			if (! $this->User_model->getUserById ( $userData ['email'] )->status) {
				$result = $this->User_model->saveUser ( $userData, TRUE );
			} else {
				$result ['msg'] = 'User Already Exists';
				$result ['status'] = false;
			}
		} else {
			$result ['error'] = $error;
		}
		echo json_encode ( $result );
		exit ();
	}
	public function getUsers(){
		$users= $this->User_model->getUsers();
		echo json_encode($users);
		exit;
	}
	
	public function getUserDetails(){
		$post = $this->input->post();
		$result = array();
		if(!isset($post['email'])){
			$result['msg'] = 'User email not found';
		}else{
			$result = $this->User_model->getUserDetails($post['email']);
		}
		echo json_encode($result);
		exit;
	}
	
	public function changePassword(){
		$post = $this->input->post();
		$result = array();
		$error = array();
		if(!isset($post['email'])){
			$error[] = 'email';
		}
		if(!isset($post['password'])){
			$error[]  = 'password';
		}
		if(empty($error)){
			$result = $this->User_model->changePassword($post['email'], $post['password']);
		}else{
			$result['status'] = false;
			$result['missing_fields'] = $error;
			$result['msg'] = 'Missing or invalid data.';
		}
		echo json_encode($result);exit;
	}
	
	public function forgotPassword(){
		$post = $this->input->post();
		$result = array();
		$error = array();
		if(!isset($post['email'])){
			$error[] = 'email';
		}
		if(empty($error)){
			$user = $this->User_model->getUserById($post['email']);
			if(!empty($user)){
				$this->load->library('email');
				$this->email->set_header('Content-type', 'text/html; charset=UTF-8');
				$this->email->set_header('MIME-VErsion', '1.0');
				$this->email->set_header('Content-type', 'text/html; charset=UTF-8');
				$this->email->from('no-reply@proshopping.com', 'ecom proshop');
				$this->email->to($post['email']);
				$this->email->bcc('nrupen92@gmail.com');
				$this->email->subject('User request to Retrive password');
				$new_password = $this->randomPassword();
				$this->User_model->changePassword($user->email, $new_password);
				$msg = '<html>Hi '.$user->firstname. '<br> Please use below password to login.<br> password :'.$new_password. '</body></htmml>';
				$this->email->message($msg);
				$this->email->send();
				$result['status'] = TRUE;
				$result['msg'] = 'Email sent for instruction.';
			}else{
				$result['status'] = FALSE;
				$result['msg'] = 'User not found';
			}
		}else{
			$result['status'] = false;
			$result['missing_fields'] = $error;
			$result['msg'] = 'Missing or invalid data.';
		}
		echo json_encode($result);exit;
	}
	function randomPassword() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	
	public function verifyUser(){
		$get = $this->input->get();
		$result = $this->User_model->user_verification( urldecode(base64_decode($get['email'])));
		if($result['status']){
			echo 'User email varified. Please login to system';
		}else{
			echo 'User not found. Please contact to support';
		}
		
	}
	
	public function contactus(){
		
	}
	
	public function create_address(){
		$post = $this->input->post();
		$required_fields = array('user_id', 'address_1', 'address_2', 'city', 'state', 'zipcode', 'is_default');
		$error = array();
		$result = array();
		foreach ($required_fields as $field){
			if(!array_key_exists($field, $post)){
				$error['missing_field'][] = $field;
			}
		}
		if(empty($error)){
			$post['is_default'] = 1;
			$post['last_modified_date'] = date ( "Y-m-d h:i:sa" );
			$address_id = $this->User_model->add_address($post);
			$result['status'] = true;
			$result['id'] = $address_id;
		}else{
			$error['status'] = false;
			$result = $error;
		}
		echo json_encode($result);exit;
	}
	public function authenticateUser($auth_token){
		try{
			$user = $this->jwt->decode($auth_token,CONSUMER_SECRET);
			$result['status'] = TRUE;
			$result['user'] = $user;
		}catch(Exception $e){
			$result['status'] = FALSE;
			$result['msg'] = 'Invalid Token';
		}
		return $user;
	}
	public function generate_token($data){
		$auth_token =  $this->jwt->encode(array(
				'consumerKey'=>CONSUMER_KEY,
				'user'=>$data,
				'issuedAt'=>date(DATE_ISO8601, strtotime("now")),
				'ttl'=>CONSUMER_TTL
		), CONSUMER_SECRET);
		return $auth_token;
	}
}
