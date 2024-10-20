 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: README.TXT,v $
 * @modified 2024/02/14 20:06:12 by $Author: dcoomber
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */

testlink_sample.sql: MySQL TL sample DB            
                     Password for admin user: admin     

After restoring the DB dump, you'll need to grant execute permissions on the 
`UDFStripHTMLTags` user-defined function in order for test case search to work.

The example below assumes that your database name is `testlink_sample` and 
that your TestLink user is `testlink`:

```
use testlink_sample;
grant execute on function UDFStripHTMLTags TO 'testlink'@'%';
```
