<?php
	class UsuariosModel extends CI_Model{

		public function __construct(){
			$this->load->database();
		}

		public function verificaLogin($u,$p,$t=1){
			$mensaje = "";
			$arraywhere = array("nickname"=>$u,"clave"=>$p,"estado"=>1);
			if($t==2){
				$arraywhere["tipo"] = 4;
			}
			if($t==3){
				$arraywhere["tipo"] = 3;
			}
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

		public function existeUsuario($columna,$valor){
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

		public function crearAsistente($datos,$datosmat){
			$mensaje = $this->crearUsuario($datos);
			$this->ingresarMateriasAsesor($datos['nickname'],$datosmat);
			return $mensaje;
		}

		public function borrarUsuario($usuario){
			$mensaje = "";
			$this->db->where('id',$usuario);
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
			else $mensaje = "No se han modificado datos personales";
			return $mensaje;
		}

		public function actualizaImagen($idusuario,$datos){
			$mensaje = "";
			$this->db
			->select("id",false)
			->from("usuarioavatar")
			->where("idusuario",$idusuario);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$this->db->where('idusuario',$idusuario);
				$this->db->update('usuarioavatar',$datos);
				if($this->db->affected_rows()>0) $mensaje = "Imagen actualizada";
				else $mensaje = "No se pudo actualizar la imagen";
			}
			else{
				$datos1 = $datos;
				$datos1['idusuario'] = $idusuario;
				$this->db->insert('usuarioavatar',$datos1);
				if($this->db->affected_rows()>0) $mensaje = "Imagen ingresada";
				else $mensaje = "No se pudo actualizar la imagen";
			}
			return $mensaje;
		}

		public function buscarUsuarios($columna,$valor){
			$mensaje = "";
			$this->db
			->select("*",false)
			->where($columna,$valor);
			$res = $this->db->get("usuarios");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$cal = $this->calificacionAsesor($row->nickname);
					$mensaje .= '{"id":"'.($row->id).'","tipo":"'.($row->tipo).'","nickname":"'.($row->nickname).'",'
					.'"nombre":"'.($row->nombres).'","apellido":"'.($row->apellidos).'",'
					.'"celular":"'.($row->celular).'","email":"'.($row->email).'","calificacion":"'.($cal).'",'
					.'"idbanco":"'.($row->idbanco).'","numerocuenta":"'.($row->numerocuenta).'"'
					.'}';
				}
			}
			else{
				$mensaje = '{"'.$columna.'":"'.$valor.'"}';
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

		public function ingresarMateriasAsesor($nickname,$arraymaterias){
			$mensaje = "";
			$usuario = $this->usuarioObj($nickname);
			$this->db->where('idasistente',$usuario->id);
			$this->db->update('asistentemateria',array("estado"=>0));
			$aexiste = array();
			$this->db
			->select("am.*",false)
			->from("materia m")
			->join("asistentemateria am","am.idmateria=m.id","inner")
			->join("usuarios u","u.id=am.idasistente","inner")
			->where(array("u.nickname"=>$nickname,"m.estado"=>1));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$mensaje = $this->actualizarMateriasAsesor($nickname,$arraymaterias);
			}
			else{
				foreach($arraymaterias as $v){
					$datos = array("idasistente"=>$usuario->id,"idmateria"=>$v);
					$this->db->insert('asistentemateria',$datos);
				}
				if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
				else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			}
			return $mensaje;
		}

		public function actualizarMateriasAsesor($nickname,$arraymaterias){
			$mensaje = "";
			$usuario = $this->usuarioObj($nickname);
			$idsins = array();
			$this->db->where('idasistente',$usuario->id);
			$this->db->update('asistentemateria',array("estado"=>0));
			$aexiste = array();
			$this->db
			->select("am.*",false)
			->from("materia m")
			->join("asistentemateria am","am.idmateria=m.id","inner")
			->join("usuarios u","u.id=am.idasistente","inner")
			->where(array("u.nickname"=>$nickname,"m.estado"=>1));
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$aexiste[$row->id] = $row->estado;
				}
			}
			foreach($arraymaterias as $v){
				if(array_key_exists($v,$aexiste)){
					$this->db->where('idasistente',$usuario->id);
					$this->db->update('asistentemateria',array("estado"=>1));
				}
				else{
					$datos = array("idasistente"=>$usuario->id,"idmateria"=>$v);
					$this->db->insert('asistentemateria',$datos);
				}
			}
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}

		public function calificarAsesor($idasesor,$puntaje){
			$mensaje = "";
			$datos = array("idasistente"=>$idasesor,"puntaje"=>$puntaje);
			$this->db->insert('rating',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}

		public function usuarioObj($nickname){
			$u = $this->buscarUsuarios("nickname",$nickname);
			$u = '['.$u.']';
			$usr = json_decode($u);
			$usuario = $usr[0];
			return $usuario;
		}

		public function notificacionesTrabajoCreado($nickname){
			$mensaje = '';
			$this->db
			->select("t.id,m.nombre",false)
			->from("notificacionesusuario n")
			->join("trabajo t","t.id=n.idtrabajo","inner")
			->join("usuarios u","u.id=n.idusuario","inner")
			->join("materia m","m.id=t.idmateria","inner")
			->where(array("u.nickname"=>$nickname,"n.leido"=>0));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"idtrabajo":"'.($row->id).'","materia":"'.($row->nombre).'"}';
				}
			}
			return $mensaje;
		}

		public function notificacionesNoLeidasCant($nickname){
			$mensaje = 0;
			$this->db
			->select("n.id",false)
			->from("notificacionesusuario n")
			->join("usuarios u","u.id=n.idusuario","inner")
			->where(array("u.nickname"=>$nickname,"n.leido"=>0));
			$res = $this->db->get();
			$mensaje = $res->num_rows();
			return $mensaje;
		}

		public function notificacionesNoLeidas($nickname){
			$mensaje = '';
			$this->db
			->select("n.id,n.mensaje,n.fecha,n.idtrabajo,t.titulo,u.tipo",false)
			->from("notificacionesusuario n")
			->join("usuarios u","u.id=n.idusuario","inner")
			->join("trabajo t","t.id=n.idtrabajo","inner")
			->where(array("u.nickname"=>$nickname,"n.leido"=>0))
			->order_by("n.fecha","desc");
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","mensaje":"'.($row->mensaje).'",'
					.'"fecha":"'.($row->fecha).'","idtrabajo":"'.($row->idtrabajo).'","titulo":"'.($row->titulo).'"'
					.',"tipo":"'.($row->tipo).'"}';
				}
			}
			return $mensaje;
		}

		public function marcarLeida($id){
			$this->db->where('id',$id);
			$this->db->update('notificacionesusuario',array("leido"=>1));
		}

		public function actualizaIdQuick($id,$nickname){
			$this->db->where('nickname',$nickname);
			$this->db->update('usuarios',array("idquickblox"=>$id));
		}

		public function actualizaClave($nickname,$clave){
			$mensaje = "";
			$this->db->where('nickname',$nickname);
			$this->db->update('usuarios',array("clave"=>$clave));
			if($this->db->affected_rows()>0) $mensaje = "Clave actualizada";
			else $mensaje = "No se pudo actualizar la clave";
			return $mensaje;
		}

		public function verificaAvatar($nickname){
			$mensaje = 0;
			$this->db
			->select("ua.id",false)
			->from("usuarioavatar ua")
			->join("usuarios u","u.id=ua.idusuario","inner")
			->where("u.nickname",$nickname);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$mensaje = $row->id;
				}
			}
			return $mensaje;
		}

		public function getBlobAvatar($id){
			$mensaje = array('archivo'=>'No hay archivo','tipo'=>'text/plain','extension'=>'.txt');
			$this->db
			->select("archivo,tipo,extension",false)
			->from("usuarioavatar")
			->where("id",$id);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$mensaje['archivo'] = $row->archivo;
					$mensaje['tipo'] = $row->tipoarchivo;
					$mensaje['extension'] = $row->extension;
				}
			}
			return $mensaje;
		}

		public function actualizarCuenta($nickname,$numerocuenta,$idbanco){
			$mensaje = "";
			$this->db->where('nickname',$nickname);
			$this->db->update('usuarios',array("numerocuenta"=>$numerocuenta,"idbanco"=>$idbanco));
			if($this->db->affected_rows()>0) $mensaje = "Datos actualizados";
			else $mensaje = "No se han modificado datos";
			return $mensaje;
		}

		public function recargarTokens($usuario,$operacion,$cantidad,$fuente){
			$mensaje = "";
			$u1 = $this->usuarioObj($usuario);
			$u2 = $this->usuarioObj($fuente);
			$datos =  array('usuario'=>$u1->id, 'fuente'=>$u2->id, 'cantidad'=>$cantidad, 'transaccion'=>$operacion);
			$this->db->insert('recargas',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Recarga ingresada";
			else $mensaje = "No se ingreso recarga";
			$this->db->query("UPDATE usuarios SET tokens=tokens + {$cantidad} WHERE id=".($u1->id)."");
			if($this->db->affected_rows()>0) $mensaje .= ", tokens agregados a la cuenta";
			else $mensaje .= ", no se agregaron tokens a la cuenta";
			return $mensaje;
		}

		public function cantidadTokens($usuario){
			$cantidad = 0;
			$res = $this->db->query("SELECT tokens FROM usuarios WHERE id={$usuario}");
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$cantidad = $row->tokens * 1;
				}
			}
			return $cantidad;
		}

		public function descontarTokens($usuario,$cantidad){
			$mensaje = "";
			$this->db->query("UPDATE usuarios SET tokens=tokens - {$cantidad} WHERE id={$usuario}");
			if($this->db->affected_rows()>0) $mensaje .= "Tokens descontados de la cuenta";
			else $mensaje .= "No se descontaron tokens de la cuenta";
			return $mensaje;
		}

		public function actualizarToken($token,$plataforma,$idusuario){
  		$mensaje = "";
  		$res = $this->db
  		->query("UPDATE usuarios SET token='{$token}',plataforma='{$plataforma}' WHERE id={$idusuario}");
  		if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
  		return $mensaje;
  	}

		public function listarUsuarios(){
			$mensaje = '';
			$res = $this->db
			->query("SELECT u.id, TRIM(CONCAT(u.nombres,' ',u.apellidos)) AS nombre, u.nickname, u.email, t.nombre AS tipo, u.tokens
			FROM usuarios u
			INNER JOIN tipousuario t ON t.id=u.tipo");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","tipo":"'.($row->tipo).'","nickname":"'.($row->nickname).'",'
						.'"nombre":"'.($row->nombre).'", "email":"'.($row->email).'","tokens":"'.($row->tokens).'"'
					.'}';
				}
			}
			return $mensaje;
		}

		public function trabajosRealizadosSemana($cant=null){
			$mensaje = '';
			$cant_query = $cant!=null ? "LIMIT {$cant}" : "";
			$res = $this->db
			->query("SELECT tr.id,u.nickname,TRIM(CONCAT(u.nombres,' ',u.apellidos)) AS nombreasistente,u.numerocuenta,b.nombre AS banco,o.valor AS tokens
			FROM trabajolog t
			INNER JOIN trabajo tr ON tr.id=t.idtrabajo
			INNER JOIN usuarios u ON u.id=tr.idasistente
			INNER JOIN bancos b ON b.id=u.idbanco
			INNER JOIN ofertatrabajo o ON o.idtrabajo=t.idtrabajo AND o.idasistente=u.id
			WHERE t.tipolog=5 AND YEARWEEK(t.fecha)=YEARWEEK(CURDATE(),1) AND o.estado=1
			{$cant_query}");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","nombreasistente":"'.($row->nombreasistente).'","nickname":"'.($row->nickname).'",'
					.'"numerocuenta":"'.($row->numerocuenta).'","banco":"'.($row->banco).'","tokens":"'.($row->tokens).'"'
					.'}';
				}
			}
			return $mensaje;
		}

		public function ofertasAceptadasSemana($cant=null){
			$mensaje = '';
			$cant_query = $cant!=null ? "LIMIT {$cant}" : "";
			$res = $this->db
			->query("SELECT tr.id,u.nickname,u2.nickname AS usuario,o.valor AS tokens,tr.titulo
			FROM trabajolog t
			INNER JOIN trabajo tr ON tr.id=t.idtrabajo
			INNER JOIN usuarios u ON u.id=tr.idasistente
			INNER JOIN usuarios u2 ON u2.id=tr.idusuario
			INNER JOIN ofertatrabajo o ON o.idtrabajo=t.idtrabajo AND o.idasistente=u.id
			WHERE t.tipolog=2 AND YEARWEEK(t.fecha)=YEARWEEK(CURDATE(),1) AND o.estado=1
			GROUP BY tr.id
			{$cant_query}");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","usuario":"'.($row->usuario).'","nickname":"'.($row->nickname).'",'
					.'"tokens":"'.($row->tokens).'","titulo":"'.($row->titulo).'"'
					.'}';
				}
			}
			return $mensaje;
		}

		public function listarAsistentes(){
			$mensaje = '';
			$res = $this->db
			->query("SELECT u.id, u.nickname, u.email, t.nombre AS tipo, u.tokens
			FROM usuarios u
			INNER JOIN tipousuario t ON t.id=u.tipo
			WHERE u.tipo=2 AND u.estado=1");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","tipo":"'.($row->tipo).'","nickname":"'.($row->nickname).'",'
					.'"email":"'.($row->email).'","tokens":"'.($row->tokens).'"'
					.'}';
				}
			}
			return $mensaje;
		}

		public function rankingAsistentesCalificacion(){
			$mensaje = '';
			$res = $this->db
			->query("SELECT COALESCE(AVG(r.puntaje),0) AS prom, u.nickname
			FROM usuarios u
			LEFT JOIN rating r ON r.idusuario=u.id
			WHERE u.tipo=2 AND u.estado=1
			GROUP BY u.id
			ORDER BY prom DESC");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"nickname":"'.($row->nickname).'","calificacion":"'.($row->prom).'"}';
				}
			}
			return $mensaje;
		}

		public function tipoUsuario($nickname){
			$tipo = 1;
			$this->db
			->select("tipo",false)
			->from("usuarios")
			->where("nickname",$nickname);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$tipo = ($row->tipo)*1;
				}
			}
			return $tipo;
		}

	}
