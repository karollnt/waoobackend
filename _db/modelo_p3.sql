-- -----------------------------------------------------
-- Table `waoo`.`trabajoarchivos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`trabajoarchivos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idtrabajo` INT NOT NULL DEFAULT 0,
  `idusuario` INT NOT NULL DEFAULT 0,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archivo` MEDIUMBLOB NOT NULL,
  `tipoarchivo` VARCHAR(45) NOT NULL,
  `extension` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `trabajo_idx` (`idtrabajo` ASC),
  INDEX `usuario_idx` (`idusuario` ASC),
  CONSTRAINT `trabajo`
    FOREIGN KEY (`idtrabajo`)
    REFERENCES `waoo`.`trabajo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `usuario`
    FOREIGN KEY (`idusuario`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `waoo`.`reasignacion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`reasignacion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idtrabajo` INT NOT NULL DEFAULT 0,
  `idasistenteprevio` INT NOT NULL DEFAULT 0,
  `idasistentenuevo` INT NOT NULL DEFAULT 0,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idreasigna` INT NOT NULL DEFAULT 0,
  `comentario` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idtrabajore_idx` (`idtrabajo` ASC),
  INDEX `idasistant_idx` (`idasistenteprevio` ASC),
  INDEX `idasistnue_idx` (`idasistentenuevo` ASC),
  INDEX `idreasigna_idx` (`idreasigna` ASC),
  CONSTRAINT `idtrabajore`
    FOREIGN KEY (`idtrabajo`)
    REFERENCES `waoo`.`trabajo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idasistant`
    FOREIGN KEY (`idasistenteprevio`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idasistnue`
    FOREIGN KEY (`idasistentenuevo`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idreasigna`
    FOREIGN KEY (`idreasigna`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `waoo`.`tipolog`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`tipolog` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `waoo`.`trabajolog`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`trabajolog` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idtrabajo` INT NOT NULL,
  `idusuario` INT NOT NULL,
  `tipolog` INT NOT NULL,
  `descripcion` VARCHAR(45) NOT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idtrabl_idx` (`idtrabajo` ASC),
  INDEX `idusuariol_idx` (`idusuario` ASC),
  INDEX `idtipologtr_idx` (`tipolog` ASC),
  CONSTRAINT `idtrabl`
    FOREIGN KEY (`idtrabajo`)
    REFERENCES `waoo`.`trabajo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idusuarioltr`
    FOREIGN KEY (`idusuario`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idtipologtr`
    FOREIGN KEY (`tipolog`)
    REFERENCES `waoo`.`tipolog` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `waoo`.`ofertatrabajo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`ofertatrabajo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idasistente` INT NOT NULL DEFAULT 0,
  `idtrabajo` INT NOT NULL DEFAULT 0,
  `valor` INT NOT NULL DEFAULT 0,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idasistoftr_idx` (`idasistente` ASC),
  INDEX `idtraboftr_idx` (`idtrabajo` ASC),
  CONSTRAINT `idasistoftr`
    FOREIGN KEY (`idasistente`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idtraboftr`
    FOREIGN KEY (`idtrabajo`)
    REFERENCES `waoo`.`trabajo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `waoo`.`notificacionesusuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `waoo`.`notificacionesusuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL DEFAULT 0,
  `mensaje` TEXT NOT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` INT NOT NULL DEFAULT 0,
  `idtrabajo` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idusuarionotif_idx` (`idusuario` ASC),
  INDEX `idtrabnotif_idx` (`idtrabajo` ASC),
  CONSTRAINT `idusuarionotif`
    FOREIGN KEY (`idusuario`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `idtrabnotif`
    FOREIGN KEY (`idtrabajo`)
    REFERENCES `waoo`.`trabajo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `waoo`.`usuarioavatar` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `archivo` MEDIUMBLOB NOT NULL,
  `tipo` VARCHAR(45) NOT NULL,
  `extension` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idusuarioav_fk_idx` (`idusuario` ASC),
  CONSTRAINT `idusuarioav_fk`
    FOREIGN KEY (`idusuario`)
    REFERENCES `waoo`.`usuarios` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
