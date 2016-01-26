<?php
	class Bancos extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('BancosModel');
		}
		
		public function listaBancos(){
			$mensaje = "";
			$msg = $this->BancosModel->listaBancos();
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"No se encontraron resultados"}';
			else $mensaje = '{"bancos":['.$msg.']}';
			echo $mensaje;
		}
		
	}