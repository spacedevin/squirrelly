CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE OR REPLACE TYPE upload_type AS ENUM ('image', 'text');

CREATE OR REPLACE FUNCTION upload_uid() RETURNS trigger AS $$
	BEGIN
		NEW.uid := REPLACE(CAST(gen_random_uuid() as char(36)),'-','');
		RETURN NEW;
	END;
$$ LANGUAGE plpgsql;

# leaving here for the future.
#CREATE TABLE "upload" (
#	"id" serial primary key,
#	"date" timestamp DEFAULT current_timestamp,
#	"type" upload_type,
#	"uid" character varying DEFAULT NULL UNIQUE,
#	"ext" varchar(20) DEFAULT NULL,
#	"size" integer,
#	"data" bytea DEFAULT NULL
#);

CREATE TABLE "upload" (
	"id" serial primary key,
	"date" timestamp DEFAULT current_timestamp,
	"type" upload_type,
	"uid" character varying DEFAULT NULL UNIQUE,
	"ext" varchar(20) DEFAULT NULL,
	"size" integer,
	"data" text DEFAULT NULL
);

CREATE TRIGGER "upload_uid" BEFORE INSERT ON "upload" FOR EACH ROW EXECUTE PROCEDURE upload_uid();
