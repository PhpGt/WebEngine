CREATE  TABLE IF NOT EXISTS `Blog__Blog_User` (
  `FK_Blog` INT UNSIGNED NOT NULL ,
  `FK_Blog_User` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`FK_Blog`, `FK_Blog_User`) ,
  INDEX `FK_Blog__Blog_User___Blog` (`FK_Blog` ASC) ,
  INDEX `FK_Blog__Blog_User___Blog_User` (`FK_Blog_User` ASC) ,
  CONSTRAINT `FK_Blog__Blog_User___Blog`
    FOREIGN KEY (`FK_Blog` )
    REFERENCES `Blog` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog__Blog_User___Blog_User`
    FOREIGN KEY (`FK_Blog_User` )
    REFERENCES `Blog_User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;