CREATE  TABLE IF NOT EXISTS `Blog_Article__Blog_Category` (
  `FK_Blog_Article` INT NOT NULL ,
  `FK_Blog_Category` INT NOT NULL ,
  PRIMARY KEY (`FK_Blog_Article`, `FK_Blog_Category`) ,
  INDEX `FK_Blog_Article__Blog_Category__Blog_Article` (`FK_Blog_Article` ASC) ,
  INDEX `FK_Blog_Article__Blog_Category__Blog_Category` (`FK_Blog_Category` ASC) ,
  CONSTRAINT `FK_Blog_Article__Blog_Category__Blog_Article`
    FOREIGN KEY (`FK_Blog_Article` )
    REFERENCES `Blog_Article` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__Blog_Category__Blog_Category`
    FOREIGN KEY (`FK_Blog_Category` )
    REFERENCES `Blog_Category` (`ID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB