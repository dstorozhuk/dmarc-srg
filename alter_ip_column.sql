-- Dima did the modification bacause of the issue with lenght
-- https://github.com/liuch/dmarc-srg/issues/165
--
ALTER TABLE rptrecords CHANGE ip ip varchar(50) COLLATE 'utf8_general_ci' NOT NULL AFTER report_id;
