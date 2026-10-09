--
-- Dave's Message Board -- table layout
--
-- Written for MySQL 3.22: MyISAM tables, no foreign keys, no subqueries,
-- no UNION, and timestamps kept as plain unix ints because that is what
-- PHP's time() hands you.
--

CREATE TABLE messages (
  id       int(11)      NOT NULL auto_increment,
  parent   int(11)      NOT NULL DEFAULT 0,
  thread   int(11)      NOT NULL DEFAULT 0,
  subject  varchar(100) NOT NULL DEFAULT '',
  author   varchar(40)  NOT NULL DEFAULT '',
  email    varchar(80)  NOT NULL DEFAULT '',
  homepage varchar(120) NOT NULL DEFAULT '',
  body     text         NOT NULL,
  posted   int(11)      NOT NULL DEFAULT 0,
  bumped   int(11)      NOT NULL DEFAULT 0,
  ip       varchar(15)  NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY thread (thread),
  KEY bumped (bumped),
  KEY ip (ip)
) ENGINE=MyISAM;

-- Hand-rolled sessions. PHP has no session support until version 4.
CREATE TABLE sessions (
  sid  varchar(32) NOT NULL DEFAULT '',
  data text        NOT NULL,
  ts   int(11)     NOT NULL DEFAULT 0,
  ip   varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (sid),
  KEY ts (ts)
) ENGINE=MyISAM;

CREATE TABLE counter (
  hits int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM;

CREATE TABLE announce (
  id   int(11) NOT NULL DEFAULT 1,
  body text    NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

INSERT INTO counter (hits) VALUES (17423);

INSERT INTO announce (id, body) VALUES (1,
  'The board was down Tuesday night because my host moved servers. Everything is back. Please do NOT post your ICQ number in the subject line. -- Dave');
