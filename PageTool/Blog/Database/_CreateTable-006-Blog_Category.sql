CREATE  TABLE IF NOT EXISTS `Blog_Category` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(32) NOT NULL ,
  `isHighlighted` TINYINT(1) NULL ,
  PRIMARY KEY (`ID`) ,
  INDEX `INDEX_isHighlighted` (`isHighlighted` ASC) )
ENGINE = InnoDB