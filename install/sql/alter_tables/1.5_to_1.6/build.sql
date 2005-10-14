/* 
$Revision: 1.3 $
$Date: 2005/10/14 06:44:46 $
$Author: franciscom $
$Name:  $

20051013 - fm - added Test Plan info in build name
Migration from 1.5.x to 1.6 POST RC1 - 20050925 - fm

*/
UPDATE  build SET name=CONCAT("BUILD ",build, " - Test Plan ID:", projid) 
WHERE (name='undefined' or name IS NULL or name='');
ALTER TABLE `build` COMMENT = 'Updated to TL 1.6 POST RC1';
