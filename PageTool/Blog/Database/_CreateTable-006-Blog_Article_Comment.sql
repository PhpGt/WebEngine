CREATE  TABLE IF NOT EXISTS `Blog_Article_Comment` (
  `ID` INT UNSIGNED NOT NULL ,
  `FK_Blog_Article` INT UNSIGNED NOT NULL ,
  `FK_Blog_User` INT UNSIGNED NOT NULL ,
  `FK_Blog_Article_Comment__reply` INT UNSIGNED NULL ,
  `content` TEXT NULL ,
  `isPublished` TINYINT(1) NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `FK_Blog_Article_Comment___Blog_Article` (`FK_Blog_Article` ASC) ,
  INDEX `FK_Blog_Article_Comment___Blog_User` (`FK_Blog_User` ASC) ,
  INDEX `FK_Blog_Article_Comment___Blog_Article_Comment__reply` (`FK_Blog_Article_Comment__reply` ASC) ,
  CONSTRAINT `FK_Blog_Article_Comment___Blog_Article`
    FOREIGN KEY (`FK_Blog_Article` )
    REFERENCES `Blog_Article` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article_Comment___Blog_User`
    FOREIGN KEY (`FK_Blog_User` )
    REFERENCES `Blog_User` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article_Comment___Blog_Article_Comment__reply`
    FOREIGN KEY (`FK_Blog_Article_Comment__reply` )
    REFERENCES `Blog_Article_Comment` (`ID` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;
