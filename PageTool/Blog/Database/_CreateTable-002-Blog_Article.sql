CREATE  TABLE IF NOT EXISTS `Blog_Article` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `FK_Blog` INT NOT NULL ,
  `FK_User` INT NOT NULL ,
  `title` VARCHAR(128) NOT NULL ,
  `dateTimeCreated` DATETIME NOT NULL ,
  `dateTimePublish` DATETIME NOT NULL ,
  `dateTimePublishEnd` DATETIME NULL ,
  `content` TEXT NOT NULL ,
  `isPrivate` TINYINT(1) NULL ,
  `isFeatured` TINYINT(1) NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `INDEX_FK_Blog` (`FK_Blog` ASC) ,
  INDEX `INDEX_FK_User` (`FK_User` ASC) ,
  CONSTRAINT `FK_Blog_Article__Blog`
    FOREIGN KEY (`FK_Blog` )
    REFERENCES `Blog` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__User`
    FOREIGN KEY (`FK_User` )
    REFERENCES `User` (`ID` )
    ON DELETE cascade
    ON UPDATE cascade)
ENGINE = InnoDB;