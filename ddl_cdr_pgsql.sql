
--Sequence or set SERIAL on Id table
CREATE SEQUENCE cdr_id_seq;


CREATE TABLE cdr
(
    calldate TIMESTAMP  NOT NULL,
    clid VARCHAR(80) default 'null' NOT NULL,
    src VARCHAR(80) default 'null' NOT NULL,
    dst VARCHAR(80) default 'null' NOT NULL,
    dcontext VARCHAR(80) default 'null' NOT NULL,
    channel VARCHAR(80) default 'null' NOT NULL,
    dstchannel VARCHAR(80) default 'null' NOT NULL,
    lastapp VARCHAR(80) default 'null' NOT NULL,
    lastdata VARCHAR(80) default 'null' NOT NULL,
    duration INTEGER default 0 NOT NULL,
    billsec INTEGER default 0 NOT NULL,
    disposition VARCHAR(45) default 'null' NOT NULL,
    amaflags VARCHAR default '0' NOT NULL,
    accountcode VARCHAR(20) default 'null' NOT NULL,
    userfield VARCHAR(255) default 'null' NOT NULL,
    id INTEGER  NOT NULL,
    PRIMARY KEY (id)
);

COMMENT ON TABLE cdr IS 'Asterisk CDR';

ALTER TABLE cdr ALTER COLUMN id SET DEFAULT nextval('cdr_id_seq') 

-- if you used import_cdr_pgsql
-- recomend add index to table cdr
CREATE UNIQUE INDEX idx_cdr_call ON cdr(src, calldate, duration);
