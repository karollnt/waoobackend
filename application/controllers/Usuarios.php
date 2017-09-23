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

    public function crearUsuario() {
      $mensaje = '';
      $usuario = trim($this->input->post('nickname'));
      if ( isset($usuario) && strcasecmp($usuario, '') != 0 ) {
        if ( $this->UsuariosModel->existeUsuario('nickname',$usuario) ) {
          $mensaje = $this->errores['usuex'];
        }
        else {
          $tipo = $this->input->post('tipo') ? $this->input->post('tipo') : 1;
          $banco = $this->input->post('idbanco') ? $this->input->post('idbanco') : 1;
          $clave = trim($this->input->post('clave'));
          $datos = array(
            'nickname'=>$usuario, 'clave'=>md5($clave),'tipo'=>trim($tipo),
            'nombres'=>trim($this->input->post('nombre')), 'apellidos'=>trim($this->input->post('apellido')),
            'celular'=>trim($this->input->post('celular')), 'email'=>trim($this->input->post('email')),
            'idbanco'=>trim($banco),'numerocuenta'=>trim($this->input->post('numerocuenta'))
          );
          if ($tipo == 1) {
            $mensaje = $this->UsuariosModel->crearUsuario($datos);
          }
          else {
            $datosmat = array();
            $cantmatsreg = $this->input->post('cantmatsreg');
            for($ind=0;$ind<$cantmatsreg;$ind++){
              if($this->input->post('mat_'.$ind)!=null){
                array_push($datosmat,$this->input->post('mat_'.$ind));
              }
            }
            $mensaje = $this->UsuariosModel->crearAsistente($datos,$datosmat);
            $guardo_detalle = $this->guardarDetalles();
          }
          
        }
      }
      else {
        $mensaje = $this->errores['usuv'];
      }
      $resp = array('msg'=>html_entity_decode($mensaje));
      echo json_encode($resp);
    }

    public function guardarDetalles() {
      $mensaje = "";
      $nivelEducativo = $this->input->post('nivel');
      $certificadoEducativo = $this->input->post('certificado');
      $descripcion = trim( $this->input->post('descripcion') );
      $usuario = trim( $this->input->post('nickname') );
      $institucion_educativa = trim( $this->input->post('institucion_edu'));
      $datosArchivo = '';
      $path = './uploads/';
      $this->load->library('upload');
      $this->upload->initialize(array(
        "upload_path"   =>  $path,
        "allowed_types" =>  "gif|jpg|png|jpeg|bmp|pdf|doc|docx|xls|xlsx|txt",
        "max_size"      =>  '20000000',
        "max_width"     =>  '13684',
        "max_height"    =>  '13684'
      ));
      if ( isset($certificadoEducativo) ) {
        if($this->upload->do_upload('certificado')) {
          $datosarchivo = $this->upload->data();
          $rutaarchivo = $datosarchivo['full_path'];
          $extension = $datosarchivo['file_ext'];
          $archivo = file_get_contents($rutaarchivo);
          $datosArchivo = array("archivo"=>$archivo,"tipoarchivo"=>$tipoarchivo,"extension"=>$extension);
          unlink($rutaarchivo);
        }
      }
       $datos = array(
        'nivel' => $nivelEducativo, 'archivo_certificado' => $certificadoEducativo, 'descripcion' => $descripcion, 'institucionedu' => $institucion_educativa
      );
      if (isset($datosArchivo)) {
        $mensaje = $this->UsuariosModel->guardarDetalles($usuario, $datos, $datosArchivo);
      }
      $resp = array("msg"=>html_entity_decode($mensaje));
      return json_encode($resp);
    }

		public function borrarUsuario(){
			$mensaje = "";
			$usuario = trim($this->input->post('id'));
			if($usuario!=''){
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

		public function random_str($length){
			$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
			$str = '';
			$max = strlen($keyspace) - 1;
			for ($i = 0; $i < $length; ++$i) {
				$str .= $keyspace[rand(0, $max)];
			}
			return $str;
		}

		public function recargarTokensMP() {
      $resp = "";
			try {
        $nickname = $this->input->post('nickname');
  			$datosmp = array('transaction_amount'=>$this->input->post('valor')*1000,
          'token'=>$this->input->post("token"),'installments'=>$this->input->post("installments"),
          'payment_method_id'=>$this->input->post("paymentMethodId"),'description'=>'Waoo - Compra de tokens',
          'payer'=>array('email'=>$this->input->post("email")));
        $status = array(
          'approved' => '¡Listo, se acredit&oacute; tu pago!',
          'in_process' => array(
            'pending_contingency' => 'Estamos procesando el pago.\nEn menos de una hora te enviaremos por e-mail el resultado.',
            'pending_review_manual' => 'Estamos procesando el pago.\nEn menos de 2 d&iacute;as &aacute;ábiles te diremos por e-mail si se acredit&oacute; o si necesitamos más informaci&oacute;n.'
          ),
          'rejected' => array(
            'cc_rejected_bad_filled_card_number' => 'Revisa el número de tarjeta.',
            'cc_rejected_bad_filled_date' => 'Revisa la fecha de vencimiento.',
            'cc_rejected_bad_filled_other' => 'Revisa los datos.',
            'cc_rejected_bad_filled_security_code' => 'Revisa el código de seguridad.',
            'cc_rejected_blacklist' => 'No pudimos procesar tu pago.',
            'cc_rejected_call_for_authorize' => 'Debes autorizar ante '.($this->input->post("paymentMethodId")).' el pago de '.($this->input->post('valor')).' a MercadoPago',
            'cc_rejected_card_disabled' => 'Llama a '.($this->input->post("paymentMethodId")).' para que active tu tarjeta',
            'cc_rejected_card_error' => 'No pudimos procesar tu pago.',
            'cc_rejected_duplicated_payment' => 'Ya hiciste un pago por ese valor.\nSi necesitas volver a pagar usa otra tarjeta u otro medio de pago.',
            'cc_rejected_high_risk' => 'Tu pago fue rechazado.\nElige otro de los medios de pago, te recomendamos con medios en efectivo.',
            'cc_rejected_insufficient_amount' => 'Tu '.($this->input->post("paymentMethodId")).' no tiene fondos suficientes.',
            'cc_rejected_invalid_installments' => ($this->input->post("paymentMethodId")).' no procesa pagos en '.($this->input->post("installments")).' cuotas.',
            'cc_rejected_max_attempts' => 'Llegaste al límite de intentos permitidos.\nElige otra tarjeta u otro medio de pago.',
            'cc_rejected_other_reason' => ($this->input->post("paymentMethodId")).' no procesó el pago.'
          )
        );
  			$this->load->library('mp');
  			$payment = $this->mp->post("/v1/payments", $datosmp);
  			if($payment['response']){
  				$pay = $payment['response'];
  				if($pay['status']=="approved"){
  					$mensaje = $this->UsuariosModel->recargarTokens($nickname,$pay['id'],$datosmp['transaction_amount']/1000,'mercadopagousr');
  					if(strcasecmp($mensaje,"No se pudo actualizar la informaci&oacute;n")==0) $resp = array("error"=>html_entity_decode($mensaje));
  					else $resp = array("msg"=>html_entity_decode($mensaje));
  				}
  				else $resp = array("error" => $pay['status']);
  			}
  			else $resp = array("error" => "No hubo respuesta del proveedor de servicio" );
			}
      catch (Exception $e) {
        $resp = array("error" => $e->getMessage() );
			}
      return $resp;
		}

		public function recargaTokensOperador(){
			$nickname = $this->input->post('nickname');
			$operador = $this->input->post('operador');
			$cantidad = $this->input->post('valor');
			$trans = "WO-".$this->random_str(10);
			$mensaje = $this->UsuariosModel->recargarTokens($nickname,$trans,$cantidad,$operador);
			if(strcasecmp($mensaje,"")==0) $resp = array("error" => "No se pudo terminar de procesar la recarga");
			else $resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function cantidadTokens(){
			$nickname = $this->input->post('nickname');
			$idusuario = "(SELECT id FROM usuarios WHERE nickname='{$nickname}')";
			$mensaje = $this->UsuariosModel->cantidadTokens($idusuario);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function actualizarToken(){
  		$token = $this->input->post('token');
  		$plataforma = $this->input->post('plataforma');
  		$nickname = $this->input->post('nickname');
  		$usuario = $this->UsuariosModel->usuarioObj($nickname);
  		$mensaje = $this->UsuariosModel->actualizarToken($token,$plataforma,$usuario->id);
  		$resp = array("msg"=>html_entity_decode($mensaje));
  		echo json_encode($resp);
  	}

    public function obtenerToken() {
      $token = '';
      $this->load->library('OneSignal');
      $token = $this->onesignal->addDevice(
        $this->input->get('plataforma'),
        $this->input->get('version_sistema')
      );
      echo json_encode($token);
    }

		public function listarUsuarios(){
			$mensaje = '';
			$msg = $this->UsuariosModel->listarUsuarios();
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"usuarios":['.$msg.']}';
			echo utf8_decode($mensaje);
		}

		public function trabajosRealizadosSemana(){
			$mensaje = '';
			$cant = $this->input->post('registros');
			if($cant=='' || $cant == null) $cant = null;
			$msg = $this->UsuariosModel->trabajosRealizadosSemana($cant);
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"trabajos":['.$msg.']}';
			echo utf8_decode($mensaje);
		}

		public function ofertasAceptadasSemana(){
			$mensaje = '';
			$cant = $this->input->post('registros');
			if($cant=='' || $cant == null) $cant = null;
			$msg = $this->UsuariosModel->ofertasAceptadasSemana($cant);
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"trabajos":['.$msg.']}';
			echo utf8_decode($mensaje);
		}

		public function listarAsistentes(){
			$mensaje = '';
			$msg = $this->UsuariosModel->listarAsistentes();
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"usuarios":['.$msg.']}';
			echo utf8_decode($mensaje);
		}

		public function rankingAsistentesCalificacion(){
			$mensaje = '';
			$msg = $this->UsuariosModel->rankingAsistentesCalificacion();
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"'.$this->errores['nousf'].'"}';
			else $mensaje = '{"usuarios":['.$msg.']}';
			echo utf8_decode($mensaje);
		}

    public function initBrainTree() {
      $this->load->library("braintree_lib");
      $nickname = $this->input->post('nickname');
      if (isset($nickname)) {
        $usuario = $this->UsuariosModel->usuarioObj($nickname);
        $token = $this->braintree_lib->create_client_token($usuario->bt_token);
      }
      else {
        $token = $this->braintree_lib->create_client_token();
      }
      $resp = array("msg"=>html_entity_decode($token));
      echo json_encode($resp);
    }
		
		
// Cargo los niveles educativos del modelo
    public function listaNivelEducativo(){
			$mensaje = "";
			$msg = $this->UsuariosModel->listaNivelAcedemico();
			if(strcasecmp($msg,"")==0) $mensaje = '{"error":"No se encontraron resultados"}';
			else $mensaje = '{"niveles":['.$msg.']}';
			echo $mensaje;
		}

	}
