CREATE  TABLE IF NOT EXISTS `Blog_User` (
  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `FK_User` INT UNSIGNED NOT NULL ,
  `name` VARCHAR(32) NOT NULL ,
  `username` VARCHAR(32) NULL COMMENT 'For user profiles' ,
  `bio` VARCHAR(256) NULL ,
  `url` VARCHAR(256) NULL ,
  `twitter` VARCHAR(32) NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `FK_Blog_User___User` (`FK_User` ASC) ,
  CONSTRAINT `FK_Blog_User___User`
    FOREIGN KEY (`FK_User` )
    REFERENCES `User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;