<?php
	error_reporting(E_ALL | E_STRICT);
	
	class Uploads extends CI_Controller{
		public function __construct(){
			parent::__construct();
			//$this->load->model('MateriasModel');
		}
		
		public function index(){
			require('UploadHandler.php');
			$upload_handler = new UploadHandler();
		}
		
	}