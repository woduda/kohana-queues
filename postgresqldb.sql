
CREATE TABLE "queues" (
       "id" SERIAL NOT NULL
     , "name" VARCHAR(100) NOT NULL
     , "active" CHAR(1) DEFAULT '1' NOT NULL
     , PRIMARY KEY ("id")
);

CREATE TABLE "queue_objects" (
       "id" SERIAL NOT NULL
     , "mode" CHAR(2) NOT NULL
     , "queue_id" INTEGER NOT NULL
     , "created" INTEGER NOT NULL
     , "planned" INTEGER DEFAULT 0 NOT NULL
     , "status" CHAR(1) DEFAULT '0' NOT NULL
     , "retries" SMALLINT DEFAULT '0' NOT NULL
     , "process_hash" CHAR(8)
     , "data" TEXT
     , PRIMARY KEY ("id")
);
CREATE INDEX "queue_objects_main_idx" ON "queue_objects" ("mode", "queue_id", "planned", "status");

CREATE TABLE "queue_processes" (
       "id" SERIAL NOT NULL
     , "mode" CHAR(2) NOT NULL
     , "queue_id" INTEGER NOT NULL
     , "active" CHAR(1) DEFAULT '1' NOT NULL
     , "hash" CHAR(8) NOT NULL
     , "started" INTEGER NOT NULL
     , "checked" INTEGER NOT NULL
     , "finished" INTEGER
     , PRIMARY KEY ("id")
);

CREATE TABLE "queue_object_logs" (
       "id" SERIAL NOT NULL
     , "queue_object_id" INTEGER NOT NULL
     , "created" INTEGER NOT NULL
     , "status" CHAR(1) NOT NULL
     , "process_hash" CHAR(8)
     , "data" TEXT
     , PRIMARY KEY ("id")
);

ALTER TABLE "queue_objects"
  ADD CONSTRAINT "queue_objects_queue_id_fk"
      FOREIGN KEY ("queue_id")
      REFERENCES "queues" ("id")
   ON DELETE CASCADE
   ON UPDATE CASCADE;

ALTER TABLE "queue_processes"
  ADD CONSTRAINT "queue_processes_queue_id_fk"
      FOREIGN KEY ("queue_id")
      REFERENCES "queues" ("id")
   ON DELETE CASCADE
   ON UPDATE CASCADE;

ALTER TABLE "queue_object_logs"
  ADD CONSTRAINT "queue_object_logs_queue_object_id_fk"
      FOREIGN KEY ("queue_object_id")
      REFERENCES "queue_objects" ("id")
   ON DELETE CASCADE
   ON UPDATE CASCADE;

INSERT INTO queues(id, name) VALUES(1, 'Dummy');

INSERT INTO queue_objects(mode, queue_id, created, planned, data) values('40', 1, 0, 0, '{"dummy_data":"1"}');
INSERT INTO queue_objects(mode, queue_id, created, planned, data) values('40', 1, 0, 0, '{"dummy_data":"2"}');
INSERT INTO queue_objects(mode, queue_id, created, planned, data) values('40', 1, 0, 0, '{"dummy_data":"3"}');
