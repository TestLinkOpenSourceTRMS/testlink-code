# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# @filesource testlink_create_udf0.sql
#
#
USE `YOUR_TL_DBNAME`; /* Replace before run */
DROP function IF EXISTS `UDFStripHTMLTags`;

DELIMITER $$
USE `YOUR_TL_DBNAME`$$ /* Replace before run */
CREATE FUNCTION `UDFStripHTMLTags`(Dirty varchar(4000)) RETURNS varchar(4000) CHARSET utf8
BEGIN
DECLARE iStart, iEnd, iLength int;
   WHILE Locate( '<', Dirty ) > 0 And Locate( '>', Dirty, Locate( '<', Dirty )) > 0 DO
      BEGIN
        SET iStart = Locate( '<', Dirty ), iEnd = Locate( '>', Dirty, Locate('<', Dirty ));
        SET iLength = ( iEnd - iStart) + 1;
        IF iLength > 0 THEN
          BEGIN
            SET Dirty = Insert( Dirty, iStart, iLength, '');
          END;
        END IF;
      END;
    END WHILE;
RETURN Dirty;
END$$

DELIMITER ;