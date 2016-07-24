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

		public function ingresarMateria(){
			$mensaje = "";
			$nombre = $this->input->post('nombre');
			$mensaje = $this->MateriasModel->ingresarMateria($nombre);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function borrarMateria(){
			$mensaje = "";
			$id = $this->input->post('id');
			$mensaje = $this->MateriasModel->borrarMateria($id);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

	}
