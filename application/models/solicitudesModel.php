<?php
	class SolicitudesModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function crearSolicitud($datos){
			$this->db->insert('trabajo',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Se ha creado la solicitud";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			return $mensaje;
		}
		
	}