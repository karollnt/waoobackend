<?php
  class SolicitudesModel extends CI_Model{

    public function __construct(){
      $this->load->database();
      $this->load->library('s3');
      $this->load->library('OneSignal');
      //$this->db->get_compiled_select();
    }

    public function crearSolicitud($datos,$datos2){
      $mensaje = '';
      $ins = $this->db->query("INSERT INTO trabajo(idusuario,idmateria,titulo,descripcion,fechaEntrega) "
      ." VALUES({$datos['idusuario']},{$datos['idmateria']},".($this->db->escape($datos['titulo'])).",".($this->db->escape($datos['descripcion'])).",'".$datos['fechaEntrega']."')");
      if($this->db->affected_rows()>0) $mensaje = "ok";
      else $mensaje = "No se pudo ingresar la informaci&oacute;n";
      $idtrabajo = $this->db->insert_id();
      $this->notificarAsistentesTrabajoCreado($idtrabajo,"Se ha creado una solicitud");
      $this->enviarNotificacionPushAsistentes($idtrabajo);
      if($datos2!=null) $this->ingresarArchivos($idtrabajo,$datos['idusuario'],$datos2);
      return $mensaje;
    }

    public function ingresarArchivos($idtrabajo,$idusuario,$datos){
      $mensaje = '';
      $buckName = 'waoofiles';
      $bucket = $this->s3->getBucket($buckName);
      if($bucket !==false) ;
      else $this->s3->putBucket($buckName,'public-read-write');
      foreach($datos as $i=>$v){
        $nombrearch = $this->random_str(48)."_".$i;
        $dats = array('idtrabajo'=>$idtrabajo,'idusuario'=>$idusuario,
        'archivo'=>$nombrearch,'tipoarchivo'=>$v['tipoarchivo'],'extension'=>$v['extension']);
        $this->db->insert('trabajoarchivos',$dats);
        $putf = $this->s3->putObject($v['archivo'],$buckName,$nombrearch.$v['extension'],'public-read');
        if($putf) $mensaje = "Se ha guardado el archivo.";
        else $mensaje = "No se pudo subir el archivo.";
        if($this->db->affected_rows()>0) $mensaje = "Se ha creado el registro";
        else $mensaje = "No se pudo ingresar la informaci&oacute;n";
      }
      return $mensaje;
    }

    public function solicitudesUsuario($nickname){
      $mensaje = '';
      $this->db
      ->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,COALESCE(u2.nickname,'Sin asignar') AS asistente,m.nombre AS materia",false)
      ->from("trabajo t")
      ->join("usuarios u","u.id=t.idusuario","inner")
      ->join("estado e","e.id=t.estado","inner")
      ->join("materia m","m.id=t.idmateria","inner")
      ->join("usuarios u2","u2.id=t.idasistente","left")
      ->where("u.nickname",$nickname)
      ->order_by("t.estado","desc")
      ->order_by("t.fecharegistro","desc");
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'",'
          .'"descripcion":"'.trim($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'",'
          .'"fecharesuelto":"'.($row->fecharesuelto).'","asistente":"'.($row->asistente).'",'
          .'"estado":"'.($row->nomestado).'"}';
        }
      }
      return $mensaje;
    }

    public function solicitudesAsistente($nickname){
      $mensaje = '';
      $this->db
      ->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,COALESCE(u2.nickname,'Sin asignar') AS asistente,m.nombre AS materia",false)
      ->from("trabajo t")
      ->join("usuarios u","u.id=t.idasistente","inner")
      ->join("estado e","e.id=t.estado","inner")
      ->join("materia m","m.id=t.idmateria","inner")
      ->join("usuarios u2","u2.id=t.idusuario","inner")
      ->where("u.nickname",$nickname)
      ->order_by("m.nombre","asc")
      ->order_by("t.estado","desc")
      ->order_by("t.fecharegistro","desc");
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'",'
          .'"descripcion":"'.trim($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'",'
          .'"fecharesuelto":"'.($row->fecharesuelto).'","solicitante":"'.($row->solicitante).'",'
          .'"estado":"'.($row->nomestado).'"}';
        }
      }
      return $mensaje;
    }

    public function solicitudesAsistenteMateria($nickname,$idmateria){
      $mensaje = '';
      $this->db->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,u.nickname AS solicitante",false)
      ->from("trabajo t")
      ->join("usuarios u","u.id=t.idasistente","inner")
      ->join("estado e","e.id=t.estado","inner")
      ->join("usuarios u2","u2.id=t.idusuario","inner")
      ->where(array("u.nickname"=>$nickname,"t.idmateria"=>$idmateria))
      ->order_by("t.estado","desc")
      ->order_by("t.fecharegistro","desc");
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'",'
          .'"descripcion":"'.trim($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'",'
          .'"fecharesuelto":"'.($row->fecharesuelto).'","solicitante":"'.($row->solicitante).'",'
          .'"estado":"'.($row->nomestado).'"}';
        }
      }
      else{
        $mensaje = 'No hay solicitudes';
      }
      return $mensaje;
    }

    public function solicitudesSinAsignar(){
      $mensaje = '';
      $this->db
      ->select("t.id,t.titulo,t.descripcion,t.fecharegistro,m.nombre AS materia,u.nickname",false)
      ->from("trabajo t")
      ->join("materia m","m.id=t.idmateria","inner")
      ->join("usuarios u","u.id=t.idusuario","inner")
      ->where("t.idasistente",0);
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        $mensaje = '[';
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'",'
          .'"descripcion":"'.trim($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'","solicitante":"'.($row->nickname).'"}';
          $mensaje .= ']';
        }
      }
      else{
        $mensaje = 'No hay solicitudes';
      }
      return $mensaje;
    }

    public function solicitudesSinAsignarPorMateria($idmateria){
      $mensaje = '';
      $this->db
      ->select("t.id,t.titulo,t.descripcion,t.fecharegistro,m.nombre AS materia,u.nickname",false)
      ->from("trabajo t")
      ->join("materia m","m.id=t.idmateria","inner")
      ->join("usuarios u","u.id=t.idusuario","inner")
      ->where(array("t.idasistente"=>0,"t.idmateria"=>$idmateria));
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        //$mensaje = '[';
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'",'
          .'"descripcion":"'.trim($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'","solicitante":"'.($row->nickname).'"}';
        }
        //$mensaje .= ']';
      }
      else{
        $mensaje = 'No hay solicitudes';
      }
      return $mensaje;
    }

    public function verificaSiAsistenteOferto($idtrabajo,$idasistente){
      $resp = array('hizo'=>false,'valor'=>0);
      $this->db
      ->select("id,valor")
      ->from("ofertatrabajo")
      ->where(array('idtrabajo'=>$idtrabajo,'idasistente'=>$idasistente,'estado'=>1));
      $res = $this->db->get();
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $resp['hizo']=true;
          $resp['valor']=$row->valor;
        }
      }
      return $resp;
    }

    public function verificarPrimerTrabajo($idasistente){
      $primer = true;
      $this->db
      ->select('id')
      ->from('trabajo')
      ->where('idasistente',$idasistente);
      $res = $this->db->get();
      if($res->num_rows()>0) $primer = false;
      return $primer;
    }

    public function enviarPrecioTrabajo($idtrabajo,$idasistente,$val){
      $mensaje = '';
      $verif = $this->verificaSiAsistenteOferto($idtrabajo,$idasistente);
      if($verif['hizo']) $mensaje = "Ya has hecho una oferta por $ ".number_format($verif['valor'],0,".",",")." para esta solicitud";
      else{
        $verif = $this->verificarPrimerTrabajo($idasistente);
        /*if($verif) $valor = 0;
        else */
        $valor = $val;
        $this->db->insert('ofertatrabajo',array("idtrabajo"=>$idtrabajo,"idasistente"=>$idasistente,"valor"=>$valor,'estado'=>1));
        if($this->db->affected_rows()>0){
          $mensaje = "Oferta ingresada";
          //if($verif) $mensaje .= ". Recuerde que su primer trabajo no es cobrado";
        }
        else $mensaje = "No se pudo ingresar la informaci&oacute;n";
        $msg = "Ha recibido una oferta para realizar su trabajo por $ ".number_format($valor,0,".",",")." pesos. Presione para ver las ofertas recibidas.";
        $extraData = array('open_offers' => true, 'requirement_id' => $idtrabajo);
        $this->notificarUsuario($msg,"(SELECT idusuario FROM trabajo WHERE id={$idtrabajo})",$idtrabajo,true,$extraData);
      }
      return $mensaje;
    }

    public function aceptarPrecio($idpreciotrabajo,$numcomprobante,$valor){
      $this->load->model('UsuariosModel');
      $mensaje = "";
      $idtrabajo = "(SELECT idtrabajo FROM ofertatrabajo WHERE id={$idpreciotrabajo})";
      $idasistente = "(SELECT idasistente FROM ofertatrabajo WHERE id={$idpreciotrabajo})";
      $idusuario = "(SELECT idusuario FROM trabajo WHERE id={$idtrabajo})";
      $aupd = array("estado"=>1);
      $verif = $this->verificarPrimerTrabajo($idasistente);
      // $tokens = $this->UsuariosModel->cantidadTokens($idusuario) * 1;
      $valor = $valor * 1;
      if($verif){
        //$aupd["valor"] = 0;
        $numcomprobante = "PT-".$numcomprobante;
      }
      // if($tokens >= $valor){
        $this->db->where('id',$idpreciotrabajo);
        $this->db->update('ofertatrabajo',$aupd);
        $this->logTrabajo($idtrabajo,$idusuario,2,"Usuario escoge asistente para hacer el trabajo");
        $mensaje = $this->asignarAsistenteTrabajo($idasistente,$idtrabajo,$numcomprobante);
        // if($valor>0) $this->UsuariosModel->descontarTokens($idusuario,$valor);
        // $this->notificarUsuario("Una de sus ofertas ha sido aceptada. Revise el menu mis solicitudes.",$idasistente,$idtrabajo,true);
      /*}
      else {
        $mensaje = "No tienes saldo suficiente";
      }*/
      return $mensaje;
    }

    public function asignarAsistenteTrabajo($idasistente,$idtrabajo,$numcomprobante){
      $mensaje = '';
      $this->db->query("UPDATE trabajo SET idasistente={$idasistente},estado=2,numcomprobante='{$numcomprobante}' WHERE id={$idtrabajo}");
      if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
      else $mensaje = "No se pudo actualizar la informaci&oacute;n";
      return $mensaje;
    }

    public function reasignarAsistenteTrabajo($idasistente,$idtrabajo){
      $mensaje = '';
      $this->db->query("UPDATE trabajo SET idasistente={$idasistente} WHERE id={$idtrabajo}");
      if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
      else $mensaje = "No se pudo actualizar la informaci&oacute;n";
      return $mensaje;
    }

    public function asistenteOferta($idoferta){
      $id = 0;
      $this->db->select('idasistente')->from('ofertatrabajo')->where('id',$idoferta);
      $res = $this->db->get();
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $id = $row->idasistente;
        }
      }
      return $id;
    }

    public function nickAsistenteOferta($idoferta){
      $nick = "";
      $this->db->select('u.nickname',false)
      ->from('ofertatrabajo o')
      ->join('usuarios u','u.id=o.idasistente','inner')
      ->where('o.id',$idoferta);
      $res = $this->db->get();
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $nick = $row->nickname;
        }
      }
      return $nick;
    }

    public function logTrabajo($idtrabajo,$idusuario,$tipo,$desc){
      $mensaje = '';
      $this->db->query("INSERT INTO trabajolog(idtrabajo,idusuario,tipolog,descripcion) VALUES ({$idtrabajo},{$idusuario},{$tipo},'{$desc}')");
      if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
      else $mensaje = "No se pudo ingresar la informaci&oacute;n";
      return $mensaje;
    }

    public function notificarUsuario($msg,$idusuario,$idtrabajo,$enviarPush = true, $extraData = null){
      $mensaje = '';
      if ($idtrabajo > 0) {
        $res = $this->db->query("INSERT INTO notificacionesusuario(idusuario,mensaje,idtrabajo) VALUES ({$idusuario},'{$msg}',{$idtrabajo});");
        if($this->db->affected_rows()>0)  $mensaje = "Informaci&oacute;n ingresada";
        else $mensaje = "No se pudo ingresar la informaci&oacute;n";
      }

      if($enviarPush){
        $res = $this->db->query("SELECT token, plataforma FROM usuarios WHERE id = {$idusuario}", false);

        if($res->num_rows() > 0){
          $tokens = array();
          foreach($res->result() as $row){
            array_push($tokens, $row->token);
          }
          if (count($tokens) > 0) {
            $this->onesignal->sendMessageToUsers($msg, $tokens, $extraData);
          }
        }
      }
      return $mensaje;
    }

    public function valorOfertaTrabajo($idoferta){
      $valor = 0;
      $this->db->select("valor",false)->from("ofertatrabajo")->where("id",$idoferta);
      $res = $this->db->get();
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $valor = $row->valor;
        }
      }
      return $valor;
    }

    public function idTrabajoDesdeOfertaTrabajo($idoferta){
      $id = 0;
      $this->db
      ->select("idtrabajo",false)
      ->from("ofertatrabajo")
      ->where("id",$idoferta);
      $res = $this->db->get();
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $id = $row->idtrabajo;
        }
      }
      return $id;
    }

    public function detallesSolicitud($id){
      $mensaje = '';
      $this->db
      ->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nestado,m.nombre AS nmateria,u.nickname,COALESCE(u1.nickname,'Ninguno') AS nickasistente,t.estado,t.fechaEntrega",false)
      ->from("trabajo t")
      ->join("usuarios u","u.id=t.idusuario","inner")
      ->join("usuarios u1","u1.id=t.idasistente","left")
      ->join("materia m","m.id=t.idmateria","inner")
      ->join("estado e","e.id=t.estado","inner")
      ->where("t.id",$id);
      $res = $this->db->get();
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $mensaje = '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","descripcion":"'.($row->descripcion).'","fechaentrega":"'.($row->fechaEntrega).'",'
          .'"fecharegistro":"'.($row->fecharegistro).'","fecharesuelto":"'.($row->fecharesuelto).'","estado":"'.($row->nestado).'","idestado":"'.($row->estado).'",'
          .'"materia":"'.($row->nmateria).'","usuario":"'.($row->nickname).'","asistente":"'.($row->nickasistente).'"}';
        }
      }
      return $mensaje;
    }

    public function notificarAsistentesTrabajoCreado($idtrabajo,$msg){
      $mensaje = '';
      $this->db
      ->query("INSERT INTO notificacionesusuario (idusuario,idtrabajo,mensaje)
      SELECT amt.idasistente,t.id,'{$msg}'
      FROM trabajo t
      INNER JOIN materia m ON t.idmateria = m.id
      INNER JOIN asistentemateria amt ON amt.idmateria=m.id
      WHERE t.id={$idtrabajo}",false);
      if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
      else $mensaje = "No se pudo ingresar la informaci&oacute;n";
      return $mensaje;
    }

    public function enviarNotificacionPushAsistentes($idtrabajo){
      $res = $this->db
      ->query("SELECT m.nombre,amt.idasistente, u.token, u.plataforma
      FROM trabajo t
      INNER JOIN materia m ON t.idmateria = m.id
      INNER JOIN asistentemateria amt ON amt.idmateria=m.id
      INNER JOIN usuarios u ON u.id = amt.idasistente
      WHERE t.id={$idtrabajo} AND u.token IS NOT NULL", false);

      if($res->num_rows() > 0){
        $areas = array();
        $msg = 'Se ha creado una nueva solicitud en el area ';
        foreach($res->result() as $row){
          if (!isset($areas[$row->nombre])) {
            $areas[$row->nombre] = array();
          }
          array_push($areas[$row->nombre], $row->token);
        }
        $extraData = array('open_offer' => true, 'requirement_id' => $idtrabajo);
        foreach ($areas as $key => $value) {
          $this->onesignal->sendMessageToUsers($msg.' '.$key, $value, $extraData);
        }
      }
      return "Mensaje enviado";
    }

    public function ofertasParaTrabajo($idtrabajo){
      $mensaje = '';
      $this->load->model('UsuariosModel');
      $this->db
      ->select("otr.id,otr.valor,u.nickname,otr.idasistente",false)
      ->from("ofertatrabajo otr")
      ->join("usuarios u","u.id=otr.idasistente","inner")
      ->where("idtrabajo",$idtrabajo);
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          $calif = $this->UsuariosModel->calificacionAsesor($row->nickname);
          $verif = $this->verificarPrimerTrabajo($row->idasistente);
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","valor":"'.($verif==true?0:$row->valor).'","asistente":"'.($row->nickname).'","calificacion":"'.($calif).'"}';
        }
      }
      return $mensaje;
    }

    public function listaArchivosTrabajo($idtrabajo){
      $mensaje = '';
      $this->db
      ->select("tra.id,tra.tipoarchivo,u.nickname, CONCAT(archivo,extension) AS nombreArchivo",false)
      ->from("trabajoarchivos tra")
      ->join("usuarios u","u.id=tra.idusuario","inner")
      ->where("tra.idtrabajo",$idtrabajo);
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","tipoarchivo":"'.($row->tipoarchivo).'","nombrearchivo":"'.($row->nombreArchivo).'","usuario":"'.($row->nickname).'"}';
        }
      }
      return $mensaje;
    }

    public function getBlobArchivoSolicitud($idarchivo){
      $mensaje = array('archivo'=>'No hay archivo','tipo'=>'text/plain','extension'=>'.txt');
      $this->db
      ->select("archivo,tipoarchivo,extension",false)
      ->from("trabajoarchivos")
      ->where("id",$idarchivo);
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

    public function enviarSolucion($datos){
      $mensaje = '';
      $this->logTrabajo($datos['idtrabajo'],$datos['idusuario'],4,"Archivo de solucion para idtrabajo ".$datos['idtrabajo']." enviado");
      $sol = json_decode($this->detallesSolicitud($datos['idtrabajo']));
      $usuario = $this->UsuariosModel->usuarioObj($sol->usuario);
      $extraData = array('open_solution' => true, 'requirement_id' => $datos['idtrabajo']);
      $this->notificarUsuario("Archivo de solucion para solicitud recibido",$usuario->id,$datos['idtrabajo'],true,$extraData);
      $this->db->where('id',$datos['idtrabajo']);
      $this->db->update("trabajo",array("estado"=>3));
      if($this->db->affected_rows()>0) $mensaje = "Se ha actualizado la solicitud";
      else $mensaje = "No se pudo ingresar la informaci&oacute;n";
      return $mensaje;
    }

    public function aceptarSolucion($idtrabajo,$idusuario){
      $mensaje = '';
      $this->db->where('id',$idtrabajo);
      $this->db->update("trabajo",array("estado"=>4));
      if($this->db->affected_rows()>0) $mensaje = "Se ha marcado la solicitud como resuelta, gracias por usar nuestros servicios";
      else $mensaje = "No se pudo actualizar la informaci&oacute;n";
      $this->logTrabajo($idtrabajo,$idusuario,5,"El usuario ha marcado el trabajo como solucionado");
      return $mensaje;
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

    public function obtenerDireccionCanalChat($idasistente){
      $canalurl = '';
      $res = $this->db
      ->query("SELECT canalchat
        FROM trabajo
        WHERE idasistente={$idasistente} AND canalchat!='' AND estado=2
        ORDER BY id DESC");
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $canalurl = $row->canalchat;
        }
      }
      return $canalurl;
    }

    public function actualizarDireccionCanalChat($idasistente,$idusuario,$canal){
      $mensaje = '';
      $this->db
      ->query("UPDATE trabajo
        SET canalchat='{$canal}'
        WHERE idasistente={$idasistente} AND idusuario={$idusuario} AND estado<=2");
      if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
      return $mensaje;
    }

    public function canalChatTrabajo($idtrabajo){
      $canalurl = '';
      $res = $this->db
      ->query("SELECT canalchat
        FROM trabajo
        WHERE id={$idtrabajo} ");
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $canalurl = $row->canalchat;
        }
      }
      return $canalurl;
    }

    public function nickAsistenteTrabajo($idtrabajo){
      $nickname = '';
      $res = $this->db
      ->query("SELECT u.nickname
        FROM trabajo t
        INNER JOIN usuarios u ON u.id=t.idasistente
        WHERE t.id={$idtrabajo} ");
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $nickname = $row->nickname;
        }
      }
      return $nickname;
    }

    public function nickUsuarioTrabajo($idtrabajo){
      $nickname = '';
      $res = $this->db
      ->query("SELECT u.nickname
        FROM trabajo t
        INNER JOIN usuarios u ON u.id=t.idusuario
        WHERE t.id={$idtrabajo} ");
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $nickname = $row->nickname;
        }
      }
      return $nickname;
    }

    public function historialTrabajosAceptados($usr){
      $mensaje = '';
      $this->db
      ->select("tr.id,t.fecha,tr.titulo,o.valor AS tokens",false)
      ->from("trabajolog t")
      ->join("trabajo tr","tr.id=t.idtrabajo","inner")
      ->join("usuarios u","u.id=tr.idasistente","inner")
      ->join("ofertatrabajo o","o.idtrabajo=t.idtrabajo AND o.idasistente=u.id","inner")
      ->where(array("u.nickname"=>$usr,"t.tipolog"=>5))
      ->order_by("tr.fecharegistro","desc");
      $res = $this->db->get();
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","fecha":"'.($row->fecha).'","tokens":"'.trim($row->tokens).'"}';
        }
      }
      return $mensaje;
    }

    public function notificarAperturaChatOfertaAceptada($idpreciotrabajo, $nickasistente, $urlChat) {
      $msg = "";
      if ($idpreciotrabajo > 0) {
        $idtrabajo = "(SELECT idtrabajo FROM ofertatrabajo WHERE id={$idpreciotrabajo})";
        $idasistente = "(SELECT idasistente FROM ofertatrabajo WHERE id={$idpreciotrabajo})";
        $msg = "Una de tus ofertas ha sido aceptada. Presiona aqui para ir al chat";
      }
      else {
        $idtrabajo = -1;
        $idasistente = "(SELECT id FROM usuarios WHERE nickname='{$nickasistente}')";
        $tituloSolicitud = $this->nombreSolicitudConIdCanalChat($urlChat);
        $textoSolicitud = (strcasecmp($tituloSolicitud,"")!=0 ? "({$tituloSolicitud})" : "");
        $msg = "Un solicitante ha hecho clic en sustentacion{$textoSolicitud}. Presiona para ir al chat.";
      }
      $extraData = array('open_chat' => true, 'channel_id' => $urlChat, 'assistant_nick' => $nickasistente);
      $mensaje = $this->notificarUsuario($msg,$idasistente,$idtrabajo,true,$extraData);
      return $mensaje;
    }

    private function nombreSolicitudConIdCanalChat($idcanal) {
      $mensaje = "";
      $res = $this->db->query("SELECT titulo FROM trabajo WHERE canalchat='{$idcanal}' LIMIT 1");
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $mensaje = $row->titulo;
        }
      }
      return $mensaje;
    }

    public function ingresarSoporte($nickname,$tokens,$ruta) {
      $mensaje = '';
      $this->db->insert('pagosefectivo',array('nickname'=>$nickname,'consignacion'=>$ruta,'tokens'=>$tokens,'estado'=>1));
      if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
      else $mensaje = "No se pudo actualizar la informaci&oacute;n";
      return $mensaje;
    }

    public function soportesSinAprobar() {
      $mensaje = '';
      $res = $this->db->query("SELECT * FROM pagosefectivo WHERE estado=1");
      if($res->num_rows()>0){
        $cont1 = 0;
        foreach($res->result() as $row){
          if($cont1==0) $cont1 = 1;
          else $mensaje .= ',';
          $mensaje .= '{"id":"'.($row->id).'","nickname":"'.($row->nickname).'","fecha":"'.($row->fechaCreacion).'","tokens":"'.trim($row->tokens).'","consignacion":"'.($row->consignacion).'"}';
        }
      }
      return $mensaje;
    }

    private function obtenerNickSoporte($id) {
      $nickname = "";
      $res = $this->db->query("SELECT * FROM pagosefectivo WHERE id={$id}");
      if($res->num_rows()>0){
        foreach($res->result() as $row){
          $nickname = $row->nickname;
        }
      }
      return $nickname;
    }

    public function aprobarSoporte($id,$fuente) {
      $mensaje = '';
      $this->db->where('id',$id);
      $this->db->update('pagosefectivo',array('estado'=>2,'fechaActualizado'=>'now()'));
      if($this->db->affected_rows()>0) {
        $this->load->model('UsuariosModel');
        $mensaje = $this->UsuariosModel->recargarTokens(
          $this->obtenerNickSoporte($id), "MAP{$id}",
          "(SELECT tokens FROM pagosefectivo WHERE id={$id})", $fuente
        );
      }
      else $mensaje = "No se pudo actualizar la informaci&oacute;n";
      return $mensaje;
    }

  }
