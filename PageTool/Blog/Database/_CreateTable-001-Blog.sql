CREATE  TABLE IF NOT EXISTS `Blog` (
  `ID` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(32) NOT NULL DEFAULT "Blog" ,
  `autoPublishComments` TINYINT(1) NULL ,
  `previewLength` INT NOT NULL DEFAULT 100 ,
  PRIMARY KEY (`ID`) ,
  UNIQUE INDEX `UNIQUE_name` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'Holds settings for blog, allows multiple blogs per site.';