CREATE  TABLE IF NOT EXISTS `Blog` (
  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  `description` VARCHAR(256) NULL ,
  `cssClass` VARCHAR(32) NULL COMMENT 'Useful for different colours' ,
  `commentsRequirePublishing` TINYINT(1) NULL ,
  PRIMARY KEY (`ID`) )
ENGINE = InnoDB;