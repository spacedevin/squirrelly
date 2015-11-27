CREATE TYPE upload_type AS ENUM ('image', 'text');

CREATE TABLE "upload" (
	"id" integer NOT NULL,
	"date" timestamp DEFAULT current_timestamp,
	"type" upload_type,
	"uid" char(36) DEFAULT '',
	"ext" varchar(20) DEFAULT NULL,
	"data" bytea DEFAULT NULL,
	PRIMARY KEY ("id")
);
