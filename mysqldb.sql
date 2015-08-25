SET storage_engine=InnoDB;

CREATE TABLE `queues` (
       `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT
     , `name` VARCHAR(100) NOT NULL
     , `active` BOOLEAN NOT NULL DEFAULT 1
     , PRIMARY KEY (`id`)
);

CREATE TABLE `queue_objects` (
       `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT
     , `mode` TINYINT(2) UNSIGNED NOT NULL
     , `queue_id` SMALLINT UNSIGNED NOT NULL
     , `created` INTEGER UNSIGNED NOT NULL
     , `planned` INTEGER UNSIGNED NOT NULL DEFAULT 0
     , `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
     , `retries` TINYINT UNSIGNED NOT NULL DEFAULT 0
     , `process_hash` CHAR(8)
     , `data` TEXT
     , PRIMARY KEY (`id`)
);
CREATE INDEX `queue_objects_main_idx` ON `queue_objects` (`mode` ASC, `queue_id` ASC, `planned` ASC, `status` ASC);

CREATE TABLE `queue_processes` (
       `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT
     , `mode` TINYINT UNSIGNED NOT NULL
     , `queue_id` SMALLINT UNSIGNED NOT NULL
     , `active` BOOLEAN NOT NULL DEFAULT 1
     , `hash` CHAR(8) NOT NULL
     , `started` INTEGER UNSIGNED NOT NULL
     , `checked` INTEGER UNSIGNED NOT NULL
     , `finished` INTEGER UNSIGNED
     , PRIMARY KEY (`id`)
);

CREATE TABLE `queue_object_logs` (
       `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT
     , `queue_object_id` INTEGER UNSIGNED NOT NULL
     , `created` INTEGER UNSIGNED NOT NULL
     , `status` TINYINT(1) UNSIGNED NOT NULL
     , `process_hash` CHAR(8)
     , `data` TEXT
     , PRIMARY KEY (`id`)
);

ALTER TABLE `queue_objects`
  ADD CONSTRAINT `queue_objects_queue_id_fk`
      FOREIGN KEY (`queue_id`)
      REFERENCES `queues` (`id`)
   ON DELETE CASCADE
   ON UPDATE CASCADE;

ALTER TABLE `queue_processes`
  ADD CONSTRAINT `queue_processes_queue_id_fk`
      FOREIGN KEY (`queue_id`)
      REFERENCES `queues` (`id`)
   ON DELETE CASCADE
   ON UPDATE CASCADE;

ALTER TABLE `queue_object_logs`
  ADD CONSTRAINT `queue_object_logs_queue_object_id_fk`
      FOREIGN KEY (`queue_object_id`)
      REFERENCES `queue_objects` (`id`)
   ON DELETE CASCADE
   ON UPDATE CASCADE;

INSERT INTO queues(id, name) VALUES(1, 'Dummy');

INSERT INTO queue_objects(mode, queue_id, created, planned, data) values('40', 1, 0, 0, '{"dummy_data":"1"}');
INSERT INTO queue_objects(mode, queue_id, created, planned, data) values('40', 1, 0, 0, '{"dummy_data":"2"}');
INSERT INTO queue_objects(mode, queue_id, created, planned, data) values('40', 1, 0, 0, '{"dummy_data":"3"}');
