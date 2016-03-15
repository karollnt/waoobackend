<?php
	class MateriasModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function listarMaterias(){
			$mensaje = '';
			$this->db
			->select("*",false)
			->from("materia")
			->where("estado",1)
			->order_by("nombre","asc");
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","nombre":"'.($row->nombre).'"}';
				}
			}
			return $mensaje;
		}
		
		public function listarMateriasAsesor($nickname){
			$mensaje = '';
			$this->db
			->select("am.*,m.nombre",false)
			->from("materia m")
			->join("asistentemateria am","am.idmateria=m.id","inner")
			->join("usuarios u","u.id=am.idasistente","inner")
			->where(array("am.estado"=>1,"u.nickname"=>$nickname,"m.estado"=>1))
			->order_by("m.nombre","asc");
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->idmateria).'","nombre":"'.($row->nombre).'","estado":"'.($row->estado).'"}';
				}
			}
			return $mensaje;
		}
		
		public function ingresarMateria($nombre){
			$mensaje = '';
			$this->db->insert('materia',array("nombre"=>$nombre));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function borrarMateria($id){
			$mensaje = '';
			$this->db->where('id',$id);
			$this->db->update('materia',array("estado"=>0));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function cambiarNombre($id,$nombre){
			$mensaje = '';
			$this->db->where('id',$id);
			$this->db->update('materia',array("nombre"=>$nombre));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
	}