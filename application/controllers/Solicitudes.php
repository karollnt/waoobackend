<?php
	class Solicitudes extends CI_Controller{

		public function __construct(){
			parent::__construct();
			$this->load->model('SolicitudesModel');
			$this->load->model('UsuariosModel');
      $this->load->model('MateriasModel');
      $this->load->model('KeysModel');
			$this->load->library('OneSignal');
		}

		public function crearSolicitud(){
			$nickname = $this->input->post('nickname');
			$titulo = utf8_decode($this->input->post('titulo'));
			$descripcion = utf8_decode($this->input->post('descripcion'));
			$idmateria = $this->input->post('idmateria');
			$fecha = $this->input->post('anio')."-".$this->input->post('mes')."-".$this->input->post('dia')." "
				.$this->input->post('hora').":".$this->input->post('minutos').":00";
			if(strcasecmp($fecha,":00")==0 || $fecha == null) $fecha = "0000-00-00 00:00:00";
			$path = './uploads/';
			$this->load->library('upload');
			$this->upload->initialize(array(
			    "upload_path"       =>  $path,
			    "allowed_types"     =>  "gif|jpg|png|jpeg|bmp|pdf|doc|docx|xls|xlsx|txt|zip|rar",
			    "max_size"          =>  '20000000',
			    "max_width"         =>  '13684',
			    "max_height"        =>  '13684'
			));
			$cantfiles = $this->input->post('cantfiles');
			$datos2 = array();
			if($cantfiles>0){
				for($ic=1;$ic<=$cantfiles;$ic++){
					if($this->upload->do_upload("uploadfile{$ic}")){
						$datosarchivo = $this->upload->data();
						$rutaarchivo = $datosarchivo['full_path'];
						$extension = $datosarchivo['file_ext'];
						$fp = fopen($rutaarchivo, 'r');
						$content = fread($fp, filesize($rutaarchivo));
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$tipoarchivo=finfo_file($finfo, $rutaarchivo);
						fclose($fp);
						$archivo = file_get_contents($rutaarchivo);
						$datos2[] = array("archivo"=>$archivo,"tipoarchivo"=>$tipoarchivo,"extension"=>$extension);
						unlink($rutaarchivo);
					}
				}
			}
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$datos = array("idusuario"=>($usuario->id),"idmateria"=>$idmateria,"titulo"=>($titulo),"descripcion"=>($descripcion),"fechaEntrega"=>($fecha));
			$mensaje = $this->SolicitudesModel->crearSolicitud($datos,$datos2);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
			exit();
		}

		function solicitudesPorMateriaAsistente(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$lm = $this->MateriasModel->listarMateriasAsesor($nickname);
			$lm = '['.$lm.']';
			$lista = json_decode($lm);
			$cont1 = 0;
			foreach($lista as $obj){
				if($cont1==0) $cont1 = 1;
				else $mensaje .= '|';
				$mensaje .= $obj->nombre.';['.($this->SolicitudesModel->solicitudesAsistenteMateria($nickname,$obj->id)).']';
			}
			$resp = array("msg"=>html_entity_decode(utf8_encode($mensaje)));
			echo json_encode($resp);
		}

		function solicitudesCreadasUsuario(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$mensaje = $this->SolicitudesModel->solicitudesUsuario($nickname);
			if(strcasecmp($mensaje,"")==0) $mensaje = 'No hay resultados';
			$resp = array("msg"=>html_entity_decode('['.utf8_encode($mensaje).']'));
			echo json_encode($resp);
		}

		function solicitudesSinAsignar(){
			$mensaje = '';
			$mensaje = $this->SolicitudesModel->solicitudesSinAsignar();
			$resp = array("msg"=>html_entity_decode(utf8_encode($mensaje)));
			echo json_encode($resp);
		}

		function solicitudesSinAsignarAsistente(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$lm = $this->MateriasModel->listarMateriasAsesor($nickname);
			$lm = '['.$lm.']';
			$lista = json_decode($lm);
			$cont1 = 0;
			foreach($lista as $obj){
				if($cont1==0) $cont1 = 1;
				else $mensaje .= '|';
				$mensaje .= $obj->nombre.';['.($this->SolicitudesModel->solicitudesSinAsignarPorMateria($obj->id)).']';
			}
			$resp = array("msg"=>html_entity_decode(utf8_encode($mensaje)));
			echo json_encode($resp);
		}

		function solicitudesSinAsignarPorMateria(){
			$mensaje = '';
			$idmateria = $this->input->post('idmateria');
			$mensaje = $this->SolicitudesModel->solicitudesSinAsignarPorMateria($idmateria);
			$resp = array("msg"=>html_entity_decode(utf8_encode($mensaje)));
			echo json_encode($resp);
		}

		public function enviarPrecioTrabajo(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$idtrabajo = $this->input->post('idtrabajo');
			$valor = $this->input->post('valor');
			$valor = str_replace('.','',$valor);
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$mensaje = $this->SolicitudesModel->enviarPrecioTrabajo($idtrabajo,$usuario->id,$valor);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function aceptarPrecio(){
			$mensaje = '';
			$idpreciotrabajo = $this->input->post("idpreciotrabajo");
			$valor = $this->input->post("valor");
			$mensaje = $this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,"WF-".($this->random_str(10)),$valor);
			if(strcasecmp($mensaje,"No se pudo actualizar la informaci&oacute;n")==0) $resp = array("error"=>html_entity_decode($mensaje));
			else $resp = array("msg"=>html_entity_decode($mensaje),"nickasistente"=>$this->SolicitudesModel->nickAsistenteOferta($idpreciotrabajo));
			echo json_encode($resp);
		}

		public function asignarAsistenteTrabajo(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$idtrabajo = $this->input->post('idtrabajo');
			$numcomprobante = $this->input->post('numcomprobante');
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$mensaje = $this->SolicitudesModel->asignarAsistenteTrabajo($usuario->id,$idtrabajo,$numcomprobante);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function reasignarAsistenteTrabajo(){
			$mensaje = '';
			$idasistente = $this->input->post('idasistente');
			$idtrabajo = $this->input->post('idtrabajo');
			$mensaje = $this->SolicitudesModel->reasignarAsistenteTrabajo($idasistente,$idtrabajo);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function detallesSolicitud(){
			$mensaje = '';
			$id = $this->input->post('id');
			$mensaje = $this->SolicitudesModel->detallesSolicitud($id);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function ofertasParaTrabajo(){
			$mensaje = '';
			$idtrabajo = $this->input->post('idtrabajo');
			$mensaje = $this->SolicitudesModel->ofertasParaTrabajo($idtrabajo);
			if(strcasecmp($mensaje,"")==0) $mensaje = 'No hay ofertas';
			$resp = array("msg"=>html_entity_decode(utf8_encode($mensaje)));
			echo json_encode($resp);
		}

		public function listaArchivosTrabajo(){
			$mensaje = '';
			$idtrabajo = $this->input->post('idtrabajo');
			$mensaje = $this->SolicitudesModel->listaArchivosTrabajo($idtrabajo);
			if(strcasecmp($mensaje,"")==0) $mensaje = 'No hay resultados';
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function verArchivoSolicitud($idreg){
			$buckName = 'waoofiles';
			$this->load->library('s3');
			$buckName = 'waoofiles';
			$msg = $this->SolicitudesModel->getBlobArchivoSolicitud($idreg);
			$resp = array("msg"=>html_entity_decode("https://".$buckName.".s3.amazonaws.com/".$msg['archivo'].$msg['extension']));
			echo json_encode($resp);
		}

		public function enviarSolucion(){
			$nickname = $this->input->post('nickasistente');
			$path = './uploads/';
			$this->load->library('upload');
			$this->upload->initialize(array(
			    "upload_path"       =>  $path,
			    "allowed_types"     =>  "gif|jpg|png|jpeg|bmp|pdf|doc|docx|xls|xlsx|txt|zip|rar",
			    "max_size"          =>  '20000000',
			    "max_width"         =>  '13684',
			    "max_height"        =>  '13684'
			));
			$cantfiles = $this->input->post('cantfiles');
			$datos2 = array();
			if($cantfiles>0){
				for($ic=1;$ic<=$cantfiles;$ic++){
					if($this->upload->do_upload("uploadfile{$ic}")){
						$datosarchivo = $this->upload->data();
						$rutaarchivo = $datosarchivo['full_path'];
						$extension = $datosarchivo['file_ext'];
						$fp = fopen($rutaarchivo, 'r');
						$content = fread($fp, filesize($rutaarchivo));
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$tipoarchivo=finfo_file($finfo, $rutaarchivo);
						fclose($fp);
						$archivo = file_get_contents($rutaarchivo);
						$datos2[] = array("archivo"=>$archivo,"tipoarchivo"=>$tipoarchivo,"extension"=>$extension);
						unlink($rutaarchivo);
					}
				}
			}
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$idtrabajo = $this->input->post('idtrabajo');
			$datos = array("idusuario"=>($usuario->id),"idtrabajo"=>$idtrabajo);
			if($datos2!=null) $this->SolicitudesModel->ingresarArchivos($idtrabajo,$datos['idusuario'],$datos2);
			$mensaje = $this->SolicitudesModel->enviarSolucion($datos);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
			exit();
		}

		function aceptarSolucion(){
			$mensaje = '';
			$idtrabajo = $this->input->post('idtrabajo');
      $calificacion = $this->input->post('calificacion');
      $comentario = $this->input->post('comentario');
			$solicitud = $this->SolicitudesModel->detallesSolicitud($idtrabajo);
			$solicitud = '['.$solicitud.']';
			$solicitud = json_decode($solicitud);
			$sol = $solicitud[0];
			if($sol){
				$u1 = $this->UsuariosModel->usuarioObj($sol->usuario);
				$mensaje = $this->SolicitudesModel->aceptarSolucion($idtrabajo,$u1->id);
				if($calificacion>0){
					$u2 = $this->UsuariosModel->usuarioObj($sol->asistente);
					$this->UsuariosModel->calificarAsesor($u2->id,$calificacion,$comentario);
				}
			}
			else $mensaje = "Hay problemas para procesar la solicitud";
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function datosPasarela(){
			//$resp = array('pubKey'=>'TEST-3206830a-fc33-4b26-8856-f8dc77c090ca','accToken'=>'');
			$resp = array('pubKey'=>'APP_USR-e9c2a6eb-6c45-42e7-a3c7-d99cfc16d972','accToken'=>'');
			echo json_encode($resp);
		}

		public function obtenerDireccionCanalChat(){
			$nickname = $this->input->post('idasistente');
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$idasistente = $usuario->id;
			$mensaje = $this->SolicitudesModel->obtenerDireccionCanalChat($idasistente);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function actualizarDireccionCanalChat(){
			$nickname1 = $this->input->post('idasistente');
			$nickname2 = $this->input->post('idusuario');
			$canal = $this->input->post('canal');
			$usuario = $this->UsuariosModel->usuarioObj($nickname1);
			$idasistente = $usuario->id;
			$usuario = $this->UsuariosModel->usuarioObj($nickname2);
			$idusuario = $usuario->id;
			$mensaje = $this->SolicitudesModel->actualizarDireccionCanalChat($idasistente,$idusuario,$canal);
			if(strcasecmp($mensaje,"")==0) $resp = array("error" => "No se pudo terminar de procesar la apertura del chat");
			else $resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
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

		public function canalChatTrabajo(){
			$idtrabajo = $this->input->post('idtrabajo');
			$usr = $this->input->post('nickname');
			$mensaje = $this->SolicitudesModel->canalChatTrabajo($idtrabajo);
			$tipo = $this->UsuariosModel->tipoUsuario($usr);
			if($tipo==1) $nick = $this->SolicitudesModel->nickAsistenteTrabajo($idtrabajo);
			else $nick = $this->SolicitudesModel->nickUsuarioTrabajo($idtrabajo);
			if(strcasecmp($mensaje,"")==0) $resp = array("error" => "No se pudo terminar de procesar la solicitud");
			else $resp = array("msg"=>html_entity_decode($mensaje),"nickusr"=>$nick);
			echo json_encode($resp);
		}

		public function historialTrabajosAceptados(){
			$usr = $this->input->post('nickname');
			$mensaje = $this->SolicitudesModel->historialTrabajosAceptados($usr);
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

    public function notificarAperturaChatOfertaAceptada() {
      $urlChat = $this->input->post("urlChat");
      $idpreciotrabajo = $this->input->post("idpreciotrabajo");
      $nickAsistenteTrabajo = $this->input->post("nickasistente");
      $mensaje = $this->SolicitudesModel->notificarAperturaChatOfertaAceptada($idpreciotrabajo,$nickAsistenteTrabajo,$urlChat);
      $resp = array("msg"=>html_entity_decode($mensaje));
      echo json_encode($resp);
    }

    public function ingresarSoporte() {
      $mensaje = "";
      $nickname = $this->input->post('user');
      $tokens = $this->input->post('valor');

      $path = './uploads/';
      $this->load->library('upload');
      $this->upload->initialize(array(
        "upload_path"       =>  $path,
        "allowed_types"     =>  "gif|jpg|png|jpeg|bmp",
        "max_size"          =>  '20000000',
        "max_width"         =>  '13684',
        "max_height"        =>  '13684'
      ));
      if($this->upload->do_upload('archivo')) {
        $datosarchivo = $this->upload->data();
        $rutaarchivo = $datosarchivo['full_path'];
        $extension = $datosarchivo['file_ext'];
        $myFile = file_get_contents($rutaarchivo);
        $ruta = $this->subirSoporte($myFile,$extension);
        unlink($rutaarchivo);
        $mensaje = $this->SolicitudesModel->ingresarSoporte($nickname,$tokens,$ruta);
      }
      else {
        $mensaje = "No fue posible ingresar el registro";
      }
      $resp = array("msg"=>html_entity_decode($mensaje));
      echo json_encode($resp);
    }

    public function subirSoporte($myFile,$extension) {
      $fileRoute = "";
      $buckName = "waoofiles";

      $bucket = $this->s3->getBucket($buckName);
      if($bucket !==false) ;
      else $this->s3->putBucket($buckName,'public-read-write');

      $filename = $this->random_str(48).$extension;
      $putf = $this->s3->putObject($myFile,$buckName,$filename,'public-read');
      if ($putf) {
        $fileRoute = "https://".$buckName.".s3.amazonaws.com/$filename";
      }
      return $fileRoute;
    }

    public function aprobarSoporte() {
      $id = $this->input->post('id');
      $fuente = $this->input->post('fuente');
      $mensaje = $this->SolicitudesModel->aprobarSoporte($id,$fuente);
      $resp = array("msg"=>html_entity_decode($mensaje));
      echo json_encode($resp);
    }

    public function soportesSinAprobar() {
      $mensaje = $this->SolicitudesModel->soportesSinAprobar();
      $resp = array("msg"=>"[".html_entity_decode($mensaje)."]");
      echo json_encode($resp);
		}
		
		public function procesarPagoBT() {
			$amount = $this->input->post('amount') . '00';
			$token = $this->input->post('token');
			$idpreciotrabajo = $this->input->post('idpreciotrabajo');
			$message = 'Pago de tutoria';
			// $api_key = 'sk_test_syBDwQhdwYsIfLsQd3S8Lp55';
			$api_key = $this->KeysModel->get_key('stripe_api_key');
			$usuario = $this->UsuariosModel->usuarioObj($this->input->post('nickname'));
			$email = $usuario->email;
			if (strcasecmp($usuario->bt_token, '') != 0 && strcasecmp($usuario->bt_token, $token) == 0) {
				$opts = $this->charge_user($amount, $usuario->bt_token, $api_key);
				$type = $opts['type'];
				$response = $opts['message'];
				if(strcasecmp($type, 'error') !== 0) {
					$asistente = $this->UsuariosModel->usuarioObj($this->SolicitudesModel->nickAsistenteOferta($idpreciotrabajo));
					$this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,"BTP-".($this->random_str(16)),0);
					$response = array("msg"=>html_entity_decode($response),"nickasistente"=>$asistente->nickname,"id"=>$idpreciotrabajo);
				} else {
					$response = array("msg"=>html_entity_decode($response));
				}
			} else {
				// Crear cliente
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/customers");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "source={$token}&description=\"{$usuario->nombre} {$usuario->apellido}\"&email={$email}");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":" . "");
				$headers = array();
				$headers[] = "Content-Type: application/x-www-form-urlencoded";
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				if (curl_errno($ch)) {
					echo 'Error:' . curl_error($ch);
				}
				curl_close ($ch);
				$json = json_decode($result);
				if ( isset( $json->error ) ) {
					$response = array("msg"=>html_entity_decode($json->error->message));
					$type = 'error';
				} else {
					$opts = $this->charge_user($amount, $json->id, $api_key);
					$type = $opts['type'];
					$response = $opts['message'];
					if(strcasecmp($type, 'error') !== 0) {
						$asistente = $this->UsuariosModel->usuarioObj($this->SolicitudesModel->nickAsistenteOferta($idpreciotrabajo));
						$this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,"BTP-".($this->random_str(16)),0);
						$response = array("msg"=>html_entity_decode($response),"nickasistente"=>$asistente->nickname,"id"=>$idpreciotrabajo);
						$this->UsuariosModel->set_bt_token($usuario->id, $json->id);
					} else {
						$response = array("msg"=>html_entity_decode($response));
					}
				}
			}
		  echo json_encode($response);
		}

		public function charge_user($amount, $customer_id, $api_key) {
			// Recibir pago
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/charges");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "amount={$amount}&currency=cop&customer={$customer_id}");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":" . "");
			$headers = array();
			$headers[] = "Content-Type: application/x-www-form-urlencoded";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			}
			curl_close ($ch);
			$json = json_decode($result);
			$opts = array('type' => 'msg', 'message' => 'Pago recibido satisfactoriamente');
			if ( isset( $json->error ) ) {
				$opts['message'] = $json->error->message;
				$opts['type'] = 'error';
			}
			return $opts;
		}

    public function procesarPagoBTO() {
      $payment_method = $this->input->post('payment_method_nonce');
      $resultado = null;
      $usuario = null;
      $resp = null;
      if (!isset($payment_method)) {
        $mensaje = "Informacion de pago no validada";
        $resp = array("error"=>html_entity_decode($mensaje));
      }
      else {
        $this->load->library("braintree_lib");
        $usuario = $this->UsuariosModel->usuarioObj($this->input->post('nickname'));
        $customer_id = null;
        $customer_data = array(
          'firstName' => $usuario->nombre,
          'lastName' => $usuario->apellido,
          'email' => $usuario->email,
          'paymentMethodNonce' => $payment_method
        );
        if ( isset($usuario->bt_token) && strcasecmp($usuario->bt_token,'')!=0 ) {
          $customer_id = $usuario->bt_token;
        }
        else {
          $result = $this->braintree_lib->create_customer($customer_data);
          $customer_id = $result->success ? $result->customer->id : null;
        }

        if ($customer_id != null) {
          if (strcasecmp($customer_id,$usuario->bt_token != 0)) {
            $this->UsuariosModel->set_bt_token($usuario->id, $customer_id);
          }
          $resultado = $this->braintree_lib->create_payment(array(
            'amount' => $this->input->post('amount'),
            'paymentMethodNonce' => $payment_method,
            'customerId' => $customer_id,
            'options'=> array(
              'submitForSettlement' => true,
              'storeInVaultOnSuccess' => true
            )
          ));
        }
      }
      if (isset($resultado)) {
        if ($resultado->success === true) {
          //Descuenta la cantidad de Tokens del usuario, cuando paga y tiene un total de tokens mayor a cero
          $cant_tokens = $this->UsuariosModel->cantidadTokens($usuario->id);
          if ($cant_tokens > 0) {
          	$desc_tokens = $this->UsuariosModel->descontarTokens($usuario->id,$cant_tokens);
          }
          //fin
          $type = "msg";
          $idpreciotrabajo = $this->input->post('idpreciotrabajo');
          $mensaje = "Pago recibido satisfactoriamente";
          $mensaje .= ".\n".$this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,"BTP-".($this->random_str(16)),0);
          $resp = array("msg"=>html_entity_decode($mensaje),"nickasistente"=>$this->SolicitudesModel->nickAsistenteOferta($idpreciotrabajo),"id"=>$idpreciotrabajo);
        }
        else {
          $type = "error";
          $codes = array(
            'authorization_expired' => 'Autorizacion expirada',
            'authorized' => 'Pago recibido satisfactoriamente',
            'authorizing' => 'Pendiente de verificar',
            'settlement_pending' => 'Pendiente por crear',
            'settlement_confirmed' => 'Creacion confirmada',
            'settlement_declined' => 'Creacion declinada',
            'failed' => 'Ha ocurrido un error al procesar el pago',
            'gateway_rejected' => 'Pago rechazado por la pasarela: '.( isset($resultado->transaction->processorResponseText) ? $resultado->transaction->processorResponseText : 'Desconocido' ),
            'processor_declined' => 'Pago declinado por pasarela',
            'settled' => 'Transaccion creada',
            'settling' => 'Creando transaccion',
            'submitted_for_settlement' => 'Enviado para crear transaccion',
            'voided' => 'Transaccion invalidada'
          );
          if (isset($resultado->transaction->status)) {
            if (strcasecmp($resultado->transaction->status,"authorized") == 0 || strcasecmp($resultado->transaction->status,"authorizing") == 0
              || strcasecmp($resultado->transaction->status,"settlement_confirmed") == 0 || strcasecmp($resultado->transaction->status,"settled") == 0) {
              $type = "msg";
            }
            $mensaje = $codes[$resultado->transaction->status];
          }
        }
      }
      else {
        $type = "error";
        $mensaje = "No se pudo realizar la transaccion";
      }
      if (!isset($resp) && count($resp) <= 0) {
        $resp = array($type=>html_entity_decode($mensaje));
      }
      echo json_encode($resp);
    }

    public function crearTutoria() {
      $nickname = $this->input->post('nickname');
      $titulo = utf8_decode($this->input->post('titulo'));
      $descripcion = utf8_decode($this->input->post('descripcion'));
      $idmateria = $this->input->post('idmateria');
      $valor = $this->input->post('valor');
      $link = $this->input->post('link');
      $fecha = $this->input->post('anio')."-".$this->input->post('mes')."-".$this->input->post('dia')." "
        .$this->input->post('hora').":".$this->input->post('minutos').":00";
      if(strcasecmp($fecha,":00")==0 || $fecha == null) $fecha = "0000-00-00 00:00:00";
      $usuario = $this->UsuariosModel->usuarioObj($nickname);
      $datos = array("idtutor"=>($usuario->id),"idmateria"=>$idmateria,"titulo"=>$titulo,"descripcion"=>$descripcion,"fecha"=>$fecha, "valor"=>$valor, "link"=>$link);
      echo json_encode( array( "msg" => $this->SolicitudesModel->crearTutoria($datos) ) );
    }

    public function listarTutoriasMateria() {
      $idmateria = $this->input->get('idmateria');
      $resp = array( 'streaming' => '' );
      if (isset($idmateria)) {
        $streaming = $this->SolicitudesModel->listarTutoriasMateria($idmateria);
        $resp['streaming'] = html_entity_decode($streaming);
      }
      echo json_encode($resp);
    }

    public function verDetallesTutoria() {
      $mensaje = '';
      $idtutoria = $this->input->get('id');
      $mensaje = $this->SolicitudesModel->verDetallesTutoria($idtutoria);
      $resp = array("msg"=>html_entity_decode(utf8_encode($mensaje)));
      echo json_encode($resp);
    }

    public function procesarPagoTutoria() {
			$amount = $this->input->post('amount') . '00';
			$token = $this->input->post('token');
			$idtutoria = $this->input->post('id');
			$message = 'Pago de tutoria';
			// $api_key = 'sk_test_syBDwQhdwYsIfLsQd3S8Lp55';
			$api_key = $this->KeysModel->get_key('stripe_api_key');
			$usuario = $this->UsuariosModel->usuarioObj($this->input->post('nickname'));
      $email = $usuario->email;
      $response = array("msg" => "");
			if (strcasecmp($usuario->bt_token, '') != 0 && strcasecmp($usuario->bt_token, $token) == 0) {
				$opts = $this->charge_user($amount, $usuario->bt_token, $api_key);
				$type = $opts['type'];
        $response["msg"] = html_entity_decode($opts['message']);
				if(strcasecmp($type, 'error') !== 0) {
					$this->SolicitudesModel->enviarLinkTutoria($idtutoria, $email, $usuario->id);
				} else {
          $response['error'] = "error";
        }
			} else {
				// Crear cliente
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/customers");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "source={$token}&description=\"{$usuario->nombre} {$usuario->apellido}\"&email={$email}");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":" . "");
				$headers = array();
				$headers[] = "Content-Type: application/x-www-form-urlencoded";
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				if (curl_errno($ch)) {
          $response['msg'] = 'Error:' . curl_error($ch);
          $response['error'] = "error";
				}
				curl_close ($ch);
				$json = json_decode($result);
				if ( isset( $json->error ) ) {
					$response["msg"] = html_entity_decode($json->error->message);
          $response['error'] = "error";
				} else {
					$opts = $this->charge_user($amount, $json->id, $api_key);
          $type = $opts['type'];
          $response["msg"] = html_entity_decode($opts['message']);
					if(strcasecmp($type, 'error') !== 0) {
            $this->SolicitudesModel->enviarLinkTutoria($idtutoria, $email, $usuario->id);
          } else {
            $response['error'] = "error";
          }
				}
			}
		  echo json_encode($response);
    }
    
    public function cargarMateriasSuscripcionStreaming() {
      $usuario = $this->UsuariosModel->usuarioObj( $this->input->get('nickname') );
      $response = array( 'results' => $this->SolicitudesModel->cargarMateriasSuscripcionStreaming( $usuario->id ) );
      echo json_encode( $response );
    }

    public function guardarNotificacionesStreaming() {
      $datosmat = array();
      $response = array('msg' => '');
      $cantmatsreg = $this->input->post('items');
      $usuario = $this->UsuariosModel->usuarioObj( $this->input->post('nickname') );
      for( $ind=0; $ind < $cantmatsreg; $ind++) {
        if( $this->input->post( 'mat_'.$ind ) !=null ) {
          array_push( $datosmat, $this->input->post( 'mat_'.$ind ) );
        }
      }
      $response['msg'] = $this->SolicitudesModel->guardarNotificacionesStreaming( $usuario->id, $datosmat );
      echo json_encode( $response );
    }

  }
