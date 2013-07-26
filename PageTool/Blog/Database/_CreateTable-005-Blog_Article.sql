CREATE  TABLE IF NOT EXISTS `Blog_Article` (
  `ID` INT UNSIGNED NOT NULL ,
  `FK_Blog` INT UNSIGNED NOT NULL ,
  `FK_Blog_User__author` INT UNSIGNED NOT NULL ,
  `FK_Blog_Category` INT UNSIGNED NULL ,
  `title` VARCHAR(128) NOT NULL ,
  `content` TEXT NOT NULL ,
  `dateTimeCreated` DATETIME NULL ,
  `dateTimePublished` DATETIME NULL ,
  `dateTimeDeleted` DATETIME NULL ,
  `dateTimeFeatured` DATETIME NULL ,
  `dateTimeUnfeatured` DATETIME NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `FK_Blog_Article___Blog` (`FK_Blog` ASC) ,
  INDEX `FK_Blog_Article___Blog_User__author` (`FK_Blog_User__author` ASC) ,
  INDEX `FK_Blog_Article___Blog_Category` (`FK_Blog_Category` ASC) ,
  CONSTRAINT `FK_Blog_Article___Blog`
    FOREIGN KEY (`FK_Blog` )
    REFERENCES `Blog` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article___Blog_User__author`
    FOREIGN KEY (`FK_Blog_User__author` )
    REFERENCES `Blog_User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article___Blog_Category`
    FOREIGN KEY (`FK_Blog_Category` )
    REFERENCES `Blog_Category` (`ID` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;