CREATE TABLE IF NOT EXISTS `Blog_Article` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `FK_Blog` INT NOT NULL,
  `FK_Blog_User__author` INT NOT NULL,
  `FK_Blog_Collection` INT NULL,
  `title` VARCHAR(256) NOT NULL,
  `subtitle` VARCHAR(256) NULL,
  `FK_Content` VARCHAR(128) NOT NULL,
  `coverImageUrl` VARCHAR(256) NULL DEFAULT NULL,
  `dateTimeCreated` DATETIME NOT NULL,
  `dateTimePublished` DATETIME NULL DEFAULT NULL,
  `dateTimeDeleted` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`ID`),
  INDEX `FK_Blog_Article__Blog` (`FK_Blog` ASC),
  INDEX `INDEX_dateTime` (`dateTimeCreated` ASC, `dateTimePublished` ASC, `dateTimeDeleted` ASC),
  INDEX `FK_Blog_Article__Blog_User__author_idx` (`FK_Blog_User__author` ASC),
  INDEX `FK_Blog_Article__Blog_Series_idx` (`FK_Blog_Collection` ASC),
  INDEX `FK_Blog_Article__Content_idx` (`FK_Content` ASC),
  CONSTRAINT `FK_Blog_Article__Blog`
    FOREIGN KEY (`FK_Blog`)
    REFERENCES `Blog` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__Blog_User__author`
    FOREIGN KEY (`FK_Blog_User__author`)
    REFERENCES `Blog_User` (`FK_User`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__Blog_Collection`
    FOREIGN KEY (`FK_Blog_Collection`)
    REFERENCES `Blog_Collection` (`ID`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__Content`
    FOREIGN KEY (`FK_Content`)
    REFERENCES `Content` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB