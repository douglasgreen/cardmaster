CREATE TABLE Cards (
  cardId int(11) NOT NULL AUTO_INCREMENT,
  deckId int(11) DEFAULT NULL,
  cardAnswer varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  cardQuestion varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  cardNote varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  cardInserted timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lastAttempt datetime DEFAULT NULL,
  correctAttempts int(10) unsigned NOT NULL DEFAULT '0',
  allAttempts int(10) unsigned NOT NULL DEFAULT '0',
  totalScore float NOT NULL DEFAULT '0',
  totalTime float NOT NULL DEFAULT '0',
  PRIMARY KEY (cardId),
  KEY deckId (deckId),
  CONSTRAINT CardsIbfk1 FOREIGN KEY (deckId) REFERENCES Decks (deckId) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE Decks (
  deckId int(11) NOT NULL AUTO_INCREMENT,
  deckName varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  deckNote varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  deckInserted timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cardQuestionLangId int(11) DEFAULT NULL,
  cardAnswerLangId int(11) DEFAULT NULL,
  deckActive tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (deckId),
  UNIQUE KEY uniqueName (deckName),
  KEY fkCardQuestionLangId (cardQuestionLangId),
  KEY fkCardAnswerLangId (cardAnswerLangId),
  CONSTRAINT fkCardAnswerLang FOREIGN KEY (cardAnswerLangId) REFERENCES Languages (langId) ON DELETE SET NULL,
  CONSTRAINT fkCardQuestionLang FOREIGN KEY (cardQuestionLangId) REFERENCES Languages (langId) ON DELETE SET NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE Languages (
  langId int(11) NOT NULL AUTO_INCREMENT,
  ietfTag varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  langName varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (langId),
  UNIQUE KEY uniqueLanguage (langName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
