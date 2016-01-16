<?php
	class Solicitudes extends CI_Controller{
		
		public function __construct(){
			parent::__construct();
			$this->load->model('SolicitudesModel');
		}
		
		public function crearSolicitud(){
			$this->load->model('UsuariosModel');
			$nickname = $this->input->post('nickname');
			$titulo = $this->input->post('titulo');
			$descripcion = $this->input->post('descripcion');
			$idmateria = $this->input->post('idmateria');
			$configura =  array(
                'upload_path' => "./uploads/",
                'upload_url' => "./uploads/",
                'allowed_types' => "gif|jpg|png|jpeg|pdf|doc|xml",
                'overwrite' => true,
                'max_size' => "10240KB",
                'max_height' => "768",
                'max_width' => "1024"
			);
			$this->load->library('upload', $configura);
			$subido = $this->upload->do_upload('archivo');
			if($subido){
				$datosarchivo = $this->upload->data();
				$fp = fopen($datosarchivo['full_path'], 'r');
				$content = fread($fp, filesize($datosarchivo['full_path']));
				$archivo = addslashes($content);
				fclose($fp);
				$usuario = $this->UsuariosModel->usuarioObj($nickname);
				$datos = array("idusuario"=>$usuario->id,"idmateria"=>$idmateria,
				"titulo"=>$titulo,"descripcion"=>$descripcion,
				"archivo"=>$archivo,"tipoarchivo"=>$datosarchivo['file_type'],"extension"=>$datosarchivo['file_ext']);
				unlink($datosarchivo['full_path']);
				$mensaje = $this->SolicitudesModel->crearSolicitud($datos);
			}
			else{
				$mensaje = $this->upload->display_errors());
			}
			$resp = array("msg"=>html_entity_decode($mensaje));
			//echo $_GET['callback'].'('.json_encode($resp).')';
			echo json_encode($resp);
		}
		
	}