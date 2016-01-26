<?php
	class Materias extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('MateriasModel');
		}
		
		public function listarMaterias(){
			$mensaje = "";
			$msg = $this->MateriasModel->listarMaterias();
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"No se encontraron resultados"}';
			else $mensaje = '{"materias":['.$msg.']}';
			echo $mensaje;
		}
		
	}