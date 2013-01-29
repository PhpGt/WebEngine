CREATE  TABLE IF NOT EXISTS `Blog_Article__Blog_Tag` (
  `FK_Blog_Article` INT NOT NULL ,
  `FK_Blog_Tag` INT NOT NULL ,
  PRIMARY KEY (`FK_Blog_Article`, `FK_Blog_Tag`) ,
  INDEX `INDEX_FK_Blog_Article` (`FK_Blog_Article` ASC) ,
  INDEX `INDEX_FK_Blog_Tag` (`FK_Blog_Tag` ASC) ,
  CONSTRAINT `FK_Blog_Article__Blog_Tag__Blog_Article`
    FOREIGN KEY (`FK_Blog_Article` )
    REFERENCES `Blog_Article` (`ID` )
    ON DELETE cascade
    ON UPDATE CASCADE,
  CONSTRAINT `FK_Blog_Article__Blog_Tag__Blog_Tag`
    FOREIGN KEY (`FK_Blog_Tag` )
    REFERENCES `Blog_Tag` (`ID` )
    ON DELETE CASCADE
    ON UPDATE cascade)
ENGINE = InnoDB;