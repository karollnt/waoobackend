<?php
	class Solicitudes extends CI_Controller{

		public function __construct(){
			parent::__construct();
			$this->load->model('SolicitudesModel');
			$this->load->model('UsuariosModel');
			$this->load->model('MateriasModel');
		}

		public function crearSolicitud(){
			$nickname = $this->input->post('nickname');
			$titulo = $this->input->post('titulo');
			$descripcion = $this->input->post('descripcion');
			$idmateria = $this->input->post('idmateria');
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
			$datos = array("idusuario"=>($usuario->id),"idmateria"=>$idmateria,"titulo"=>utf8_encode($titulo),"descripcion"=>utf8_encode($descripcion));
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
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		function solicitudesCreadasUsuario(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$mensaje = $this->SolicitudesModel->solicitudesUsuario($nickname);
			if(strcasecmp($mensaje,"")==0) $mensaje = 'No hay resultados';
			$resp = array("msg"=>html_entity_decode('['.utf8_encode($mensaje).']'));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		function solicitudesSinAsignar(){
			$mensaje = '';
			$mensaje = $this->SolicitudesModel->solicitudesSinAsignar();
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
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
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		function solicitudesSinAsignarPorMateria(){
			$mensaje = '';
			$idmateria = $this->input->post('idmateria');
			$mensaje = $this->SolicitudesModel->solicitudesSinAsignarPorMateria($idmateria);
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		public function enviarPrecioTrabajo(){
			$mensaje = '';
			$nickname = $this->input->post('nickname');
			$idtrabajo = $this->input->post('idtrabajo');
			$valor = $this->input->post('valor');
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$mensaje = $this->SolicitudesModel->enviarPrecioTrabajo($idtrabajo,$usuario->id,$valor);
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		public function aceptarPrecio(){
			$mensaje = '';
			$idpreciotrabajo = $this->input->post("idpreciotrabajo");
			$datosmp = array('transaction_amount'=>$this->input->post('valorOferta')*1,
				'token'=>$this->input->post("token"),'installments'=>1,
				'payment_method_id'=>$this->input->post("paymentMethodId"),'description'=>'Waoo - Cobro por realizar tarea',
				'payer'=>array('email'=>$this->input->post("email")));
			$this->load->library('mp');
			$payment = $this->mp->post("/v1/payments", $datosmp);
			if($payment['response']){
				$pay = $payment['response'];
				if($pay['status']=="approved"){
					$mensaje = $this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,$pay['id']);
					if(strcasecmp($mensaje,"No se pudo actualizar la informaci&oacute;n")==0) $resp = array("error"=>html_entity_decode($mensaje));
					else $resp = array("msg"=>html_entity_decode($mensaje),"nickasistente"=>$this->SolicitudesModel->nickAsistenteOferta($idpreciotrabajo));
				}
				else $resp = array("error" => $pay['status']);
			}
			else $resp = array("error" => "No hubo respuesta del proveedor de servicio" );
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		public function aceptarPrecioCero(){
			$idpreciotrabajo = $this->input->post("idpreciotrabajo");
			$mensaje = $this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,"first");
			if(strcasecmp($mensaje,"No se pudo actualizar la informaci&oacute;n")==0) $resp = array("error"=>html_entity_decode($mensaje));
			else $resp = array("msg"=>html_entity_decode($mensaje),"nickasistente"=>$this->SolicitudesModel->nickAsistenteOferta($idpreciotrabajo));
			//echo $_GET['callback'].'('.json_encode($resp).')';
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
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		public function detallesSolicitud(){
			$mensaje = '';
			$id = $this->input->post('id');
			$mensaje = $this->SolicitudesModel->detallesSolicitud($id);
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}

		public function ofertasParaTrabajo(){
			$mensaje = '';
			$idtrabajo = $this->input->post('idtrabajo');
			$mensaje = $this->SolicitudesModel->ofertasParaTrabajo($idtrabajo);
			if(strcasecmp($mensaje,"")==0) $mensaje = 'No hay ofertas';
			$resp = array("msg"=>html_entity_decode($mensaje));
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
			//$idreg = $this->input->get("id");
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
			$solicitud = $this->SolicitudesModel->detallesSolicitud($idtrabajo);
			$solicitud = '['.$solicitud.']';
			$solicitud = json_decode($solicitud);
			$sol = $solicitud[0];
			$u1 = $this->UsuariosModel->usuarioObj($sol->usuario);
			$mensaje = $this->SolicitudesModel->aceptarSolucion($idtrabajo,$u1->id);
			if($calificacion>0){
				$u2 = $this->UsuariosModel->usuarioObj($sol->asistente);
				$this->UsuariosModel->calificarAsesor($u2->id,$calificacion);
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
			echo json_encode($resp);
		}

		public function datosPasarela(){
			//$resp = array('pubKey'=>'TEST-3206830a-fc33-4b26-8856-f8dc77c090ca','accToken'=>'');
			$resp = array('pubKey'=>'APP_USR-e9c2a6eb-6c45-42e7-a3c7-d99cfc16d972','accToken'=>'');
			echo json_encode($resp);
		}

	}
