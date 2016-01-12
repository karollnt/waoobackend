<?php
	class UsuariosModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function verificaLogin($u,$p){
			$mensaje = "";
			$arraywhere = array("nickname"=>$u,"clave"=>md5($p),"estado"=>1);
			$this->db
			->select("id,TRIM(CONCAT(nombres,' ',apellidos)) AS nombrecompleto",false)
			->from("usuarios")
			->where($arraywhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$mensaje = "ok";
			}
			else{
				$mensaje = "Usuario y/o constrase&ntilde;a incorrecto(a)";
			}
			return $mensaje;
		}
		
		public function cambiarClave($u,$p,$p2){
			$mensaje = "";
			$arraywhere = array("nickname"=>$u,"clave"=>md5($p),"estado"=>1);
			$arrayupd = array("clave"=>md5($p2));
			$this->db
			->select("id",false)
			->from("usuarios")
			->where($arraywhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$this->db->where($arraywhere);
				$this->db->update("usuarios",$arrayupd);
				if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
				else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			}
			else{
				$mensaje = "Usuario y/o constrase&ntilde;a incorrecto(a)";
			}
			return $mensaje;
		}
		
		protected function existeUsuario($columna,$valor){
			$existe = false;
			$awhere = array($columna=>$valor);
			$this->db
			->select("id",false)
			->from("usuarios")
			->where($awhere);
			$res = $this->db->get();
			if($res->num_rows()>0) $existe = true;
			return $existe;
		}
		
		public function crearUsuario($datos){
			$mensaje = "";
			$this->db->insert('usuarios',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function borrarUsuario($usuario){
			$mensaje = "";
			$this->db->where('usuario',$usuario);
			$this->db->delete('usuarios');
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function modificarUsuario($idusuario,$datos){
			$mensaje = "";
			$this->db->where('id',$idusuario);
			$this->db->update('usuarios',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function buscarUsuarios($columna,$valor){
			$mensaje = "";
			$awhere = array($columna=>$valor);
			$this->db
			->select("*",false)
			->from("usuarios")
			->where($awhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","tipo":"'.($row->tipo).'",'
					.'"nombre":"'.($row->nombres).'","apellido":"'.($row->apellidos).'",'
					.'"celular":"'.($row->celular).'","email":"'.($row->email).'"',
					.'"idbanco":"'.($row->idbanco).'","numerocuenta":"'.($row->numerocuenta).'"',
					.'}';
				}
			}
			return $mensaje;
		}
		
		public function calificacionAsesor($nickname){
			$mensaje = "0";
			$awhere = array('u.nickname'=>$nickname);
			$this->db
			->select("r.puntaje",false)
			->from("usuarios u")
			->join("rating r","r.idasistente=u.id","inner")
			->where($awhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$acum = 0;
				foreach($res->result() as $row){
					$acum += $row->puntaje;
				}
				$mensaje = $acum/($res->num_rows());
			}
			return $mensaje;
		}
		
	}
