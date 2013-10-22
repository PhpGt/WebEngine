CREATE  TABLE IF NOT EXISTS `Blog` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  `shortName` VARCHAR(32) NULL ,
  `description` TEXT NULL ,
  `doCommentsRequirePublishing` TINYINT(1) NULL ,
  PRIMARY KEY (`ID`) )
ENGINE = InnoDB;