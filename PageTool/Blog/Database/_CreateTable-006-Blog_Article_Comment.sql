CREATE  TABLE IF NOT EXISTS `Blog_Article_Comment` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `FK_Blog_User` INT NOT NULL ,
  `FK_Blog_Article` INT NOT NULL ,
  `FK_Blog_Article_Comment__Reply` INT NULL ,
  `dateTimeCreated` DATETIME NOT NULL ,
  `dateTimeDeleted` DATETIME NULL ,
  `isSubscribed` TINYINT(1) NULL ,
  `isPublished` TINYINT(1) NULL ,
  `name` VARCHAR(64) NULL ,
  `location` VARCHAR(64) NULL ,
  `email` VARCHAR(64) NULL ,
  `website` VARCHAR(64) NULL ,
  `content` TEXT NOT NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `INDEX_FK_Blog_Article` (`FK_Blog_Article` ASC) ,
  INDEX `INDEX_FK_Blog_Article_Comment__Reply` (`FK_Blog_Article_Comment__Reply` ASC) ,
  INDEX `INDEX_FK_Blog_User` (`FK_Blog_User` ASC) ,
  CONSTRAINT `FK_Blog_Article_Comment__Blog_Article`
    FOREIGN KEY (`FK_Blog_Article` )
    REFERENCES `Blog_Article` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article_Comment__Blog_Article_Comment`
    FOREIGN KEY (`FK_Blog_Article_Comment__Reply` )
    REFERENCES `Blog_Article_Comment` (`ID` )
    ON DELETE SET NULL
    ON UPDATE cascade,
  CONSTRAINT `FK_Blog_Article_Comment__Blog_User`
    FOREIGN KEY (`FK_Blog_User` )
    REFERENCES `Blog_User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE cascade)
ENGINE = InnoDB
COMMENT = 'Optionally links to user table, or use anon users.';