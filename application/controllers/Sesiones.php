<?php
	class Sesiones extends CI_Controller{

		public function __construct(){
			parent::__construct();
			$this->load->model('UsuariosModel');
		}

		public function login(){
			$usuario = $this->input->post('nickname');
			$clave  = $this->input->post('clave');
			$mensaje = $this->UsuariosModel->verificaLogin($usuario,$clave,1);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function loginOperador(){
			$usuario = $this->input->post('nickname');
			$clave  = $this->input->post('clave');
			$mensaje = $this->UsuariosModel->verificaLogin($usuario,$clave,2);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function loginAdmin(){
			$usuario = $this->input->post('nickname');
			$clave  = $this->input->post('clave');
			$mensaje = $this->UsuariosModel->verificaLogin($usuario,$clave,3);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function logout(){
			$this->load->library('session');
			if($this->session->userdata('uid')){
				$this->session->sess_destroy();
				echo "ok";
			}
		}
	}
?>
