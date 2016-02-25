-- -----------------------------------------------------
-- Table `waoo`.`trabajo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`trabajo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL DEFAULT 0,
  `idmateria` INT NOT NULL DEFAULT 0,
  `titulo` VARCHAR(45) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `idasistente` INT NOT NULL DEFAULT 0,
  `numcomprobante` VARCHAR(45) NOT NULL,
  `fecharegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecharesuelto` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `estado` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `usuario_idx` (`idusuario` ASC),
  INDEX `materia_idx` (`idmateria` ASC),
  INDEX `asistente_idx` (`idasistente` ASC),
  CONSTRAINT `usuariot`
    FOREIGN KEY (`idusuario`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `materiat`
    FOREIGN KEY (`idmateria`)
    REFERENCES `waoo`.`materia` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `asistentet`
    FOREIGN KEY (`idasistente`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;
