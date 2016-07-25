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

		public function crearBanco(){
			$mensaje = "";
			$nombre = $this->input->post('nombre');
			$mensaje = $this->BancosModel->crearBanco($nombre);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function borrarBanco(){
			$mensaje = "";
			$id = $this->input->post('id');
			$mensaje = $this->BancosModel->borrarBanco($id);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

	}
