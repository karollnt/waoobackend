<?php
	class Usuarios extends CI_Controller{

		private $errores = array(
			'usuex'=>"E01001: Nombre de usuario ya registrados",
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

		public function existeUsuario(){
			$usuario = trim($this->input->post('nickname'));
			$existe = $this->UsuariosModel->existeUsuario('nickname',$usuario);
			return $existe;
		}

		private function validaClave(){
			$valida = false;
			$clave = trim($this->input->post('clave'));
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
						$tipo = 1;
						$banco = 1;
						if($this->input->post('tipo')!=null) $tipo = $this->input->post('tipo');
						if($this->input->post('banco')!=null) $banco = $this->input->post('banco');
						$datos = array(
							'nickname'=>$usuario, 'clave'=>md5($clave),'tipo'=>trim($tipo),
							'nombres'=>trim($this->input->post('nombre')), 'apellidos'=>trim($this->input->post('apellido')),
							'celular'=>trim($this->input->post('celular')), 'email'=>trim($this->input->post('email')),
							'idbanco'=>trim($banco),'numerocuenta'=>trim($this->input->post('numerocuenta'))
						);
						if($tipo==1) $mensaje = $this->UsuariosModel->crearUsuario($datos);
						if($tipo==2){
							$datosmat = array();
							$cantmatsreg = $this->input->post('cantmatsreg');
							for($ind=0;$ind<$cantmatsreg;$ind++){
								if($this->input->post('mat_'.$ind)!=null){
									array_push($datosmat,$this->input->post('mat_'.$ind));
								}
							}
							$mensaje = $this->UsuariosModel->crearAsistente($datos,$datosmat);
						}
					}
				}
			}
			else{
				$mensaje = $this->errores['usuv'];
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
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
			echo json_encode($resp);
		}

		public function modificarUsuario(){
			$mensaje = "";
			$nck = $this->input->post('nck');
			$u = $this->UsuariosModel->usuarioObj($nck);
			$idusuario = $u->id;
			$datos = array(
				'nombres'=>trim($this->input->post('nombre')), 'apellidos'=>trim($this->input->post('apellido'))
			);
			$mensaje = $this->UsuariosModel->modificarUsuario($idusuario,$datos);
			//upload avatar img
			$path = './uploads/';
            $this->load->library('upload');
            // Define file rules
            $this->upload->initialize(array(
                "upload_path"       =>  $path,
                "allowed_types"     =>  "gif|jpg|png|jpeg|bmp",
                "max_size"          =>  '12400000',
                "max_width"         =>  '1024',
                "max_height"        =>  '768'
            ));
			$rutaarchivo = "";
			if($this->upload->do_upload("img-profile")){
				$datosarchivo = $this->upload->data();
				$rutaarchivo = $datosarchivo['full_path'];
				$extension = $datosarchivo['file_ext'];
				$fp = fopen($rutaarchivo, 'rb');
				$content = fread($fp, filesize($rutaarchivo));
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$tipoarchivo = finfo_file($finfo, $rutaarchivo);
				//$archivo = addslashes($content);
				fclose($fp);
				$archivo = file_get_contents($rutaarchivo);
				$datos1 = array('archivo'=>$archivo,'tipo'=>$tipoarchivo,'extension'=>$extension);
				$mensaje .= "\n".$this->UsuariosModel->actualizaImagen($idusuario,$datos1);
				unlink($rutaarchivo);
			}
			else {
				$errors = array('error' => $this->upload->display_errors());
                foreach($errors as $k => $error){
                    $mensaje .= ".".html_entity_decode($error);
                }
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
			//exit();
		}

		public function datosUsuario(){
			$mensaje = '';
			$valor = trim($this->input->post('nickname'));
			$msg = $this->UsuariosModel->buscarUsuarios("nickname",$valor);
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"usuarios":['.$msg.']}';
			echo $mensaje;
		}

		public function buscarUsuarios(){
			$mensaje = '';
			$columna = trim($this->input->post('col'));
			$valor = trim($this->input->post('val'));
			$msg = $this->UsuariosModel->buscarUsuarios($columna,$valor);
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"usuarios":['.$msg.']}';
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

		public function tipoUsuario(){
			$nickname = $this->input->post('nickname');
			$u = $this->UsuariosModel->buscarUsuarios("nickname",$nickname);
			$u = '['.$u.']';
			$usr = json_decode($u);
			$usuario = $usr[0];
			echo '{"tipo":"'.$usuario->tipo.'"}';
		}

		public function ingresarMateriasAsesor(){
			$nickname = $this->input->post('nickname');
			$materias = $this->input->post('materias');
			$arraymaterias = explode(";",$materias);
			$mensaje = $this->UsuariosModel->ingresarMateriasAsesor($nickname,$arraymaterias);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function actualizarMateriasAsesor(){
			$nickname = $this->input->post('nickname');
			$materias = $this->input->post('materias');
			$arraymaterias = explode(";",$materias);
			$mensaje = $this->UsuariosModel->actualizarMateriasAsesor($nickname,$arraymaterias);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function calificarAsesor(){
			$idasesor = $this->input->post('idasesor');
			$puntaje = $this->input->post('puntaje');
			$mensaje = $this->UsuariosModel->calificarAsesor($idasesor,$puntaje);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function calificacionAsesor(){
			$nickname = $this->input->post('nickname');
			$mensaje = $this->UsuariosModel->calificacionAsesor($nickname);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function notificacionesNoLeidasCant(){
			$nickname = $this->input->post('nickname');
			$mensaje = $this->UsuariosModel->notificacionesNoLeidasCant($nickname);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function notificacionesNoLeidas(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$mensaje = $this->UsuariosModel->notificacionesNoLeidas($nickname);
			if(strcasecmp($mensaje,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function marcarLeida(){
			$id = $this->input->post('id');
			$this->UsuariosModel->marcarLeida($id);
			echo '{"msg":"ok"}';
		}

		public function actualizaIdQuick(){
			$id = $this->input->post('id');
			$nickname = $this->input->post('nickname');
			$this->UsuariosModel->actualizaIdQuick($id,$nickname);
			echo '{"msg":"ok"}';
		}

		public function actualizaClave(){
			$msg = '';
			$clave = $this->input->post('clave');
			$nickname = $this->input->post('nickname');
			$clave = md5($clave);
			$msg = $this->UsuariosModel->actualizaClave($nickname,$clave);
			echo '{"msg":"'.$msg.'"}';
		}

		public function verificaAvatar(){
			$msg = '';
			$nickname = $this->input->post('nickname');
			$msg = $this->UsuariosModel->verificaAvatar($nickname);
			echo '{"msg":"'.$msg.'"}';
		}

		public function verAvatar($id){
			$msg = $this->UsuariosModel->getBlobAvatar($id);
			if(strcasecmp($msg['archivo'],"No hay archivo")==0) echo "0";
			else {
				header("Content-type: ".($msg['tipo']));
				header("Content-Disposition: attachment; filename=profileimg{$id}".($msg['extension']));
				ob_clean();
				flush();
				echo ($msg['archivo']);
			}
		}

		public function actualizarCuenta(){
			$msg = '';
			$nickname = $this->input->post('nickname');
			$numerocuenta = $this->input->post('numerocuenta');
			$idbanco = $this->input->post('idbanco');
			$msg = $this->UsuariosModel->actualizarCuenta($nickname,$numerocuenta,$idbanco);
			echo '{"msg":"'.$msg.'"}';
		}
	}
