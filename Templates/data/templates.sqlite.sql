--
-- Templates Module SQLite Database for Phire CMS 2.0
--

--  --------------------------------------------------------

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "name" varchar NOT NULL,
  "device" varchar NOT NULL,
  "template" text NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_template_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]templates', 9000);
CREATE INDEX "template_parent_id" ON "[{prefix}]templates" ("parent_id");
CREATE INDEX "template_name" ON "[{prefix}]templates" ("name");
