/* 
$Revision: 1.1 $
$Date: 2008/01/02 18:56:17 $
$Author: franciscom $
$Name:  $
*/
CREATE TABLE "api_developer_keys" (  
  "id" BIGSERIAL NOT NULL ,
  "developer_key" VARCHAR(32) NOT NULL,
  "user_id" BIGINT NOT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "api_developer_keys_user_id" ON "api_developer_keys" ("user_id");