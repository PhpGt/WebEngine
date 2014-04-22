CREATE TABLE IF NOT EXISTS `Content` (
  `ID` VARCHAR(128) NOT NULL,
  `dateTime` DATETIME NOT NULL,
  `dateTimeDeleted` DATETIME NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`ID`, `dateTime`))
ENGINE = InnoDB