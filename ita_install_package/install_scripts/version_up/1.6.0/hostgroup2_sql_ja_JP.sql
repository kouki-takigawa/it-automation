ALTER TABLE F_HOSTGROUP_VAR ADD COLUMN ACCESS_AUTH TEXT AFTER DISP_SEQ;

ALTER TABLE F_HOSTGROUP_VAR_JNL ADD COLUMN ACCESS_AUTH TEXT AFTER DISP_SEQ;



UPDATE A_SEQUENCE SET MENU_ID=2100170005, DISP_SEQ=2100720001, NOTE=NULL, LAST_UPDATE_TIMESTAMP=STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f') WHERE NAME='F_HOSTGROUP_VAR_RIC';
UPDATE A_SEQUENCE SET MENU_ID=2100170005, DISP_SEQ=2100720002, NOTE='履歴テーブル用', LAST_UPDATE_TIMESTAMP=STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f') WHERE NAME='F_HOSTGROUP_VAR_JSQ';
