DO LANGUAGE plpgsql $$
DECLARE
authSchemaName text;
dbUserName text;
dbUserPass text;
BEGIN
  dbUserName = 'lpingu';
  dbUserPass = '123456';
 authSchemaName = 'auth_test';
 EXECUTE 'create schema if not exists ' || authSchemaName ;
 IF NOT EXISTS (
      SELECT *                       -- SELECT list can stay empty for this
      FROM   pg_catalog.pg_roles
      WHERE  rolname = dbUserName) THEN

      EXECUTE 'CREATE USER ' || dbUserName ||' WITH ENCRYPTED PASSWORD ''' || dbUserPass || '''';
   END IF;


 EXECUTE 'alter schema ' || authSchemaName || ' owner to ' || dbUserName;
 EXECUTE 'create table if not exists ' || authSchemaName || '.users
              (
               id serial not null
                constraint users_pkey
                 primary key,
               login varchar(30) not null,
               email varchar(100) not null,
               password varchar(255) not null,
               active_status integer default 0 not null,
               block_status integer default 1 not null,
               email_activation_code varchar(255),
               email_activation integer default 0 not null,
               remind_password_code varchar(255),
               agreement integer default 0,
               last_remind_time varchar(255)
              )';
  EXECUTE 'alter table ' || authSchemaName || '.users owner to ' || dbUserName;
  EXECUTE ' create table if not exists '|| authSchemaName ||'.tokens
              (
               id serial not null
                constraint user_tokens_pkey
                 primary key,
               user_id integer not null,
               access_token varchar not null,
               refresh_token varchar
              )';
  EXECUTE 'alter table ' || authSchemaName || '.tokens owner to ' || dbUserName;
  EXECUTE 'create table if not exists ' || authSchemaName ||'.roles
              (
               role_id serial not null
                constraint roles_pkey
                 primary key,
               role_name varchar(40) not null
              )';
  EXECUTE 'alter table ' || authSchemaName || '.roles owner to ' || dbUserName;
  EXECUTE 'create table if not exists ' || authSchemaName || '.permissions
              (
               perm_id serial not null
                constraint permissions_pkey
                 primary key,
               perm_desc varchar(40) not null
              )';
  EXECUTE 'alter table ' || authSchemaName || '.permissions owner to ' || dbUserName;
  EXECUTE 'create table if not exists ' || authSchemaName || '.role_perm
              (
               role_id integer not null,
               perm_id integer not null,
               constraint role_perm_pk
                primary key (role_id, perm_id)
              )';
  EXECUTE 'alter table ' || authSchemaName || '.role_perm owner to ' || dbUserName;
  EXECUTE ' create table if not exists ' || authSchemaName || '.user_role
              (
               user_id integer not null,
               role_id integer not null,
               constraint user_role_pk
                primary key (user_id, role_id)
              )';
  EXECUTE 'alter table ' || authSchemaName || '.user_role owner to ' || dbUserName;
END
$$