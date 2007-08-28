/* 
$Revision: 1.1 $
$Date: 2007/08/28 23:06:34 $
$Author: asielb $
$Name:  $
*/

# already have a unique index for these fields
ALTER TABLE `builds` DROP INDEX `testplan_id`;
ALTER TABLE `priorities` DROP INDEX `testplan_id`;

# remove srs_id from the index and rename it to status
ALTER TABLE `requirements` DROP INDEX `srs_id` , ADD INDEX `status` ( `status` );

# id is already indexed from being a primary key
ALTER TABLE `testprojects` DROP INDEX `id_active` , ADD INDEX `active` ( `active` );