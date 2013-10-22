CREATE  TABLE IF NOT EXISTS `Blog_Article` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `FK_Blog` INT NOT NULL ,
  `FK_Blog_User__author` INT NOT NULL ,
  `title` VARCHAR(256) NOT NULL ,
  `content` TEXT NOT NULL ,
  `coverImageUrl` VARCHAR(256) NULL ,
  `dateTimeCreated` DATETIME NOT NULL ,
  `dateTimePublished` DATETIME NULL ,
  `dateTimeDeleted` DATETIME NULL ,
  `dateTimeFeatured` DATETIME NULL ,
  `dateTimeUnfeatured` DATETIME NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `FK_Blog_Article__Blog` (`FK_Blog` ASC) ,
  INDEX `FK_Blog_Article__Blog_User` (`FK_Blog_User__author` ASC) ,
  INDEX `INDEX_dateTime` (`dateTimeCreated` ASC, `dateTimePublished` ASC, `dateTimeDeleted` ASC, `dateTimeFeatured` ASC, `dateTimeUnfeatured` ASC) ,
  CONSTRAINT `FK_Blog_Article__Blog`
    FOREIGN KEY (`FK_Blog` )
    REFERENCES `Blog` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__Blog_User`
    FOREIGN KEY (`FK_Blog_User__author` )
    REFERENCES `Blog_User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;