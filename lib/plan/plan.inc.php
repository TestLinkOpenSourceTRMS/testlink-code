<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: plan.inc.php,v $
 * @version $Revision: 1.46 $
 * @modified $Date: 2007/05/21 06:42:55 $ $Author: franciscom $
 * @author 	Martin Havlat
 *
 * Functions for management: 
 * Test Plans, Test Case Suites, Milestones, Testers assignment
 *
 * 20070121 - franciscom - deprecated insertTestPlanBuild()
 *                         use testplan->create_build() method
 *
 * 20070119 - franciscom - BUGID 510 
 * 
 */

/** include core functions for collect information about Test Plans */
require_once("plan.core.inc.php"); 
?>
