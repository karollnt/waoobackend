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
            // Define file rules
            $this->upload->initialize(array(
                "upload_path"       =>  $path,
                "allowed_types"     =>  "gif|jpg|png",
                "max_size"          =>  '10240',
                "max_width"         =>  '1024',
                "max_height"        =>  '768'
            ));
           
            if($this->upload->do_upload("uploadfile")){
                $datosarchivo = $this->upload->data();
				$rutaarchivo = $datosarchivo['full_path'];
				$extension = $datosarchivo['file_ext'];
				$fp = fopen($rutaarchivo, 'r');
				$content = fread($fp, filesize($rutaarchivo));
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$tipoarchivo=finfo_file($finfo, $rutaarchivo);
				$archivo = addslashes($content);
				fclose($fp);
				$usuario = $this->UsuariosModel->usuarioObj($nickname);
				$datos = array("idusuario"=>($usuario->id),"idmateria"=>$idmateria,
				"titulo"=>$titulo,"descripcion"=>$descripcion);
				$datos2 = array("archivo"=>$archivo,"tipoarchivo"=>$tipoarchivo,"extension"=>$extension);
				unlink($rutaarchivo);
				$mensaje = $this->SolicitudesModel->crearSolicitud($datos,$datos2);
				$resp = array("msg"=>html_entity_decode($mensaje));
				echo json_encode($resp);
            }
			else{
                // Output the errors
                $errors = array('error' => $this->upload->display_errors());
                foreach($errors as $k => $error){
                    echo $error;
                }
            }
			// Exit to avoid further execution
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
				$mensaje .= $obj->nombre.';'.($this->SolicitudesModel->solicitudesAsistenteMateria($nickname,$obj->id));
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
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
			$numcomprobante = $this->input->post("numcomprobante");
			$mensaje = $this->SolicitudesModel->aceptarPrecio($idpreciotrabajo,$numcomprobante);
			$resp = array("msg"=>html_entity_decode($mensaje));
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
			$mensaje = '{"ofertas":[';
			$idtrabajo = $this->input->post('idtrabajo');
			$mensaje = $this->UsuariosModel->ofertasParaTrabajo($idtrabajo);
			if(strcasecmp($msg,"")==0) $msg = '{"error":"'.$this->errores['nousf'].'"}';
			$mensaje .= $msg;
			$mensaje .= ']}';
			echo $mensaje;
		}
		
		private function configuracionPayU($usuario,$idtrabajo,$valor){
			$order = array();
			$order['notifyUrl'] = 'http://localhost'.dirname($_SERVER['REQUEST_URI']).'/OrderNotify.php';
			$order['continueUrl'] = 'http://localhost'.dirname($_SERVER['REQUEST_URI']).'/../../layout/success.php';
			$order['customerIp'] = '127.0.0.1';
			$order['merchantPosId'] = OpenPayU_Configuration::getMerchantPosId();
			$order['description'] = 'Pago de trabajo '.$idtrabajo;
			$order['currencyCode'] = 'COP';
			$order['totalAmount'] = $valor;
			$order['extOrderId'] = uniqid('', true);
			$order['products'][0]['name'] = 'Product1';
			$order['products'][0]['unitPrice'] = $valor;
			$order['products'][0]['quantity'] = 1;
			$order['buyer']['email'] = $usuario->email;
			$order['buyer']['phone'] = $usuario->celular;
			$order['buyer']['firstName'] = $usuario->nombres;
			$order['buyer']['lastName'] = $usuario->apellidos;
			return $order;
		}
		
		//Para payu: https://github.com/PayU/openpayu_php
		public function pagarConPayU(){
			$idpreciotrabajo = $this->input->post("idpreciotrabajo");
			$valor = $this->SolicitudesModel->valorOfertaTrabajo($idpreciotrabajo);
			$idtrabajo = $this->SolicitudesModel->idTrabajoDesdeOfertaTrabajo($idpreciotrabajo);
			$nickname = "(SELECT nickname FROM trabajo WHERE id={$idtrabajo})";
			$usuario = $this->UsuariosModel->usuarioObj($nickname);
			$orden = $this->configuracionPayU($usuario,$idtrabajo,$valor);
			$orderFormData = OpenPayU_Order::hostedOrderForm($orden);
			echo $orderFormData;
		}
		
	}