drop table if exists DEVICE_TYPE;

/*==============================================================*/
/* Table: DEVICE_TYPE                                           */
/*==============================================================*/
create table DEVICE_TYPE
(
   TYPE_ID              INT(10) not null /* Тип устройства */,
   TYPE_NAME            VARCHAR(64) not null /* Наименование */,
   LM_DATE              DATETIME not null /* Дата модиф. */,
   primary key (TYPE_ID)
);

insert into DEVICE_TYPE(TYPE_ID, TYPE_NAME, LM_DATE)
values(1, 'GPS', '2015-07-16 00:00:00');

drop table if exists DEVICE;

/*==============================================================*/
/* Table: DEVICE                                                */
/*==============================================================*/
create table DEVICE
(
   DEVICE_ID            INT(10) not null /* ID устройства */,
   TYPE_ID              INT(10) not null /* Тип устройства */,
   DEVICE_NAME          VARCHAR(64) not null /* Наименование */,
   DEVICE_CODE          VARCHAR(64) not null /* Код устройства */,
   USER_ID              INT(10) not null /* ID пользователя */,
   LM_DATE              DATETIME not null /* Дата модиф. */,
   FLAG_DEL             VARCHAR(1) not null /* Флаг: удалено */,
   FLAG_GPS             VARCHAR(1) not null /* Флаг: GPS */,
   primary key (DEVICE_ID),
   key AK_DEVICE (TYPE_ID, DEVICE_NAME),
   unique key AK_DEVICE_CODE (DEVICE_CODE)
);

alter table DEVICE add constraint FK_DEVICE_TYPE__TYPE_ID foreign key (TYPE_ID)
      references DEVICE_TYPE (TYPE_ID) on delete restrict on update restrict;




	  
drop table if exists GPS_DEVICE;

/*==============================================================*/
/* Table: GPS_DEVICE                                            */
/*==============================================================*/
create table GPS_DEVICE
(
   DEVICE_ID            INT(10) not null /* ID устройства */,
   LATITUDE             FLOAT(18,15) not null /* Коорд. широта */,
   LONGITUDE            FLOAT(18,15) not null /* Коорд. долгота */,
   LM_DATE              DATETIME not null /* Дата модиф. */,
   primary key (DEVICE_ID)
);

alter table GPS_DEVICE add constraint FK_DEVICE__DEVICE_ID foreign key (DEVICE_ID)
      references DEVICE (DEVICE_ID) on delete restrict on update restrict;


drop table if exists GPS_LOCATION;

/*==============================================================*/
/* Table: GPS_LOCATION                                          */
/*==============================================================*/
create table GPS_LOCATION
(
   POI_ID               INT(10) not null /* ID локации */,
   POI_NAME             VARCHAR(64) not null /* Название локации */,
   POI_LAT              FLOAT(18,15) not null /* Коорд. широта */,
   POI_LNG              FLOAT(18,15) not null /* Коорд. долгота */,
   LM_DATE              DATETIME not null /* Дата модиф. */,
   POI_RANGE            FLOAT /* Радиус */,
   primary key (POI_ID),
   key AK_AK_POI_NAME (POI_NAME, POI_LAT, POI_LNG)
);

drop table if exists GPS_ACTION_TYPE;

/*==============================================================*/
/* Table: GPS_ACTION_TYPE                                       */
/*==============================================================*/
create table GPS_ACTION_TYPE
(
   TYPE_ID              INT(10) not null /* Тип действия */,
   TYPE_NAME            VARCHAR(32) not null /* Название */,
   LM_DATE              DATETIME not null /* Дата модиф. */,
   TYPE_DESC            VARCHAR(255) /* Описание */,
   primary key (TYPE_ID),
   unique key AK_GPS_ACTION_TYPE (TYPE_NAME)
)
type = InnoDB;

INSERT INTO GPS_ACTION_TYPE (TYPE_ID, TYPE_NAME, LM_DATE, TYPE_DESC)
VALUES (NULL, 'Entering', '2015-07-16 00:00:00', 'GPS координаты устройства попадает в указанную границу '),
       (NULL, 'Leaving', '2015-07-16 00:00:00', 'GPS координаты устройства выходит из указанных границ');

drop table if exists GPS_ACTION;

/*==============================================================*/
/* Table: GPS_ACTION                                            */
/*==============================================================*/
create table GPS_ACTION
(
   ACTION_ID            INT(10) not null /* ID Действия */,
   POI_ID               INT(10) not null /* ID локации */,
   DEVICE_ID            INT(10) not null /* ID устройства */,
   TYPE_ID              INT(10) not null /* Тип действия */,
   SCRIPT_ID            INT(10) /* ID Скрипта */,
   CODE                 TEXT /* Код */,
   LOG                  TEXT /* Лог */,
   EXECUTED             DATETIME /* Дата выполнения */,
   primary key (ACTION_ID),
   key AK_GPS_ACTION (POI_ID, DEVICE_ID, TYPE_ID)
);

alter table GPS_ACTION add constraint FK_GPS_ACTION_TYPE__TYPE_ID foreign key (TYPE_ID)
      references GPS_ACTION_TYPE (TYPE_ID) on delete restrict on update restrict;

alter table GPS_ACTION add constraint FK_GPS_DEVICE__DEVICE_ID foreign key (DEVICE_ID)
      references GPS_DEVICE (DEVICE_ID) on delete restrict on update restrict;

alter table GPS_ACTION add constraint FK_GPS_LOCATION__POI_ID foreign key (POI_ID)
      references GPS_LOCATION (POI_ID) on delete restrict on update restrict;





drop table if exists GPS_HISTORY;

/*==============================================================*/
/* Table: GPS_HISTORY                                           */
/*==============================================================*/
create table GPS_HISTORY
(
   DEVICE_ID            INT(10) not null /* ID устройства */,
   REC_DATE             DATETIME not null /* Дата записи */,
   LATITUDE             FLOAT(18,15) not null /* Коорд. широта */,
   LONGITUDE            FLOAT(18,15) not null /* Коорд. Долгота */,
   LM_DATE              DATETIME not null /* Дата модиф. */,
   ALTITUDE             FLOAT /* Высота над уровнем моря */,
   PROVIDER             VARCHAR(30) /* Постащик координат */,
   SPEED                FLOAT /* Скорость */,
   BATTERY_LEVEL        INT(3) /* Уровень заряда батареи */,
   BATTERT_STATUS       INT(3) /* Статус заряда батареи */,
   ACCURACY             FLOAT /* Точность */,
   primary key (DEVICE_ID, REC_DATE)
);

alter table GPS_HISTORY add constraint FK_GPS_DEVICE_DEVICE_ID foreign key (DEVICE_ID)
      references GPS_DEVICE (DEVICE_ID) on delete restrict on update restrict;
	  