--
-- Templates Module PostgreSQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE SEQUENCE template_id_seq START 9001;

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL DEFAULT nextval('template_id_seq'),
  "parent_id" integer,
  "name" varchar(255) NOT NULL,
  "device" varchar(255),
  "template" text,
  "history" text,
  "visible" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_template_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE template_id_seq OWNED BY "[{prefix}]templates"."id";
CREATE INDEX "template_parent_id" ON "[{prefix}]templates" ("parent_id");
CREATE INDEX "template_name" ON "[{prefix}]templates" ("name");
