CREATE  TABLE IF NOT EXISTS `Blog_User` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `FK_User` INT NOT NULL ,
  `username` VARCHAR(32) NOT NULL ,
  `firstName` VARCHAR(64) NULL ,
  `lastName` VARCHAR(64) NULL ,
  `dateTimeCreated` DATETIME NOT NULL ,
  `bio` TEXT NULL ,
  `url` VARCHAR(256) NULL ,
  `twitter` VARCHAR(32) NULL ,
  PRIMARY KEY (`ID`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  INDEX `FK_Blog_User__User` (`FK_User` ASC) ,
  UNIQUE INDEX `FK_User_UNIQUE` (`FK_User` ASC) ,
  CONSTRAINT `FK_Blog_User__User`
    FOREIGN KEY (`FK_User` )
    REFERENCES `User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;