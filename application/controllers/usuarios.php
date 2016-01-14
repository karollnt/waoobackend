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
		}
		
		public function existeUsuario($usuario){
			$existe = $this->UsuariosModel->existeUsuario('nickname',$usuario);
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
			$usuario = trim($this->input->post('nickname'));
			if(strcasecmp($usuario,"")!=0){
				if($this->existeUsuario()){
					$mensaje = $this->errores['usuex'];
				}
				else{
					$clave = trim($this->input->post('clave'));
					if($this->validaClave($clave)){
						$datos = array(
							'usuario'=>$usuario, 'clave'=>md5($clave),'tipo'=>trim($this->input->post('tipo')),
							'nombre'=>trim($this->input->post('nombre')), 'apellido'=>trim($this->input->post('apellido')),
							'celular'=>trim($this->input->post('celular')), 'email'=>trim($this->input->post('email')),
							'idbanco'=>trim($this->input->post('banco')),'numerocuenta'=>trim($this->input->post('numerocuenta'))
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
			$usuario = trim($this->input->post('nickname'));
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
		
		public function datosUsuario(){
			$mensaje = '{"usuarios":[';
			$valor = trim($this->input->post('nickname'));
			$msg = $this->UsuariosModel->buscarUsuarios("nickname",$valor);
			if(strcasecmp($msg,"")==0) $msg = '{"error":"'.$this->errores['nousf'].'"}';
			$mensaje .= $msg;
			$mensaje .= ']}';
			echo $mensaje;
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
		
		public function panelUsuario(){
			$nickname = $this->input->post('nickname');
			$u = $this->UsuariosModel->buscarUsuarios("nickname",$nickname);
			$u = '['.$u.']';
			$usr = json_decode($u);
			$usuario = $usr[0];
			$datos = '{"datos":[';
			switch($usuario->tipo){
				case 1:
					$datos .= '{"menu":"Perfil;Solicitudes;Soporte;Estad&iacute;sticas"}';
					break;
				case 2:
					$datos .= '{"menu":"Perfil;Solicitudes"}';
					break;
				case 3:
					$datos .= '{"menu":"Perfil;Solicitar;Mis solicitudes;Cargar saldo;Soporte"}';
					break;
			}
			$datos .= ']}';
		}
		
	}