<?php
	class Usuarios extends CI_Controller{
		
		private $errores = array(
			'usuex'=>"E01001: Nombre de usuario ya registrado",
			'usuv'=>"Debe escribir un nombre de usuario",
			'clavev'=>"E01003: Debe escribir una clave",
			'clavec'=>"E01004: Clave demasiado corta",
			'usuiv'=>"E01005: Usuario no valido",
			'sessnv'=>'E01006: No hay sesion iniciada',
			'nousf'=>'E01007: No se encontraron resultados',
			'nopf'=>'E01008: No se encontraron perfiles'
		);
		
		public function __construct(){
			parent::__construct();
			$this->load->model('UsuariosModel');
			$this->load->library('session');
		}
		
		public function existeUsuario($usuario){
			$existe = $this->UsuariosModel->existeUsuario('usuario',$usuario);
			return $existe;
		}
		
		private function validaClave($clave){
			$valida = false;
			if(strcasecmp($clave,"")==0) echo $this->errores['clavev'];
			else{
				if(strlen($clave)<=4) echo $this->errores['clavec'];
				else $valida = true;
			}
			return $valida;
		}
		
		public function crearUsuario(){
			$mensaje = "";
			$usuario = trim($this->input->post('usuario'));
			if(strcasecmp($usuario,"")!=0){
				if($this->existeUsuario()){
					$mensaje = $this->errores['usuex'];
				}
				else{
					$clave = trim($this->input->post('clave'));
					if($this->validaClave($clave)){
						$datos = array(
							'usuario'=>$usuario, 'clave'=>md5($clave),
							'nombre'=>trim($this->input->post('nombre')), 'apellido'=>trim($this->input->post('apellido')),
							'celular'=>trim($this->input->post('celular')), 'email'=>trim($this->input->post('email'))
						);
						$mensaje = $this->UsuariosModel->crearUsuario($datos);
					}
				}
			}
			else{
				$mensaje = $this->errores['usuv'];
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}
		
		public function borrarUsuario(){
			$mensaje = "";
			$usuario = trim($this->input->post('usuario'));
			if($this->existeUsuario($usuario)){
				$mensaje = $this->UsuariosModel->borrarUsuario($usuario);
			}
			else{
				$mensaje = $this->errores['usuiv'];
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}
		
		public function modificarUsuario(){
			$mensaje = "";
			$idusuario = $this->input->post('idusuario');
			$datos = array(
				'nombres'=>trim($this->input->post('nombre')), 'apellidos'=>trim($this->input->post('apellido')), 
				'celular'=>trim($this->input->post('celular')), 'email'=>trim($this->input->post('email'))
			);
			$mensaje = $this->UsuariosModel->modificarUsuario($idusuario,$datos);
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}
		
		public function buscarUsuarios(){
			$mensaje = '{"usuarios":[';
			$columna = trim($this->input->post('col'));
			$valor = trim($this->input->post('val'));
			$msg = $this->UsuariosModel->buscarUsuarios($columna,$valor);
			if(strcasecmp($msg,"")==0) $msg = '{"error":"'.$this->errores['nousf'].'"}';
			$mensaje .= $msg;
			$mensaje .= ']}';
			echo $mensaje;
		}
		
	}