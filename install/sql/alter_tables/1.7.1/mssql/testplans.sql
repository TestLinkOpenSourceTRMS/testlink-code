-- 
-- $File:$
-- $Revision: 1.2 $
-- $Date: 2007/10/19 06:53:06 $
-- $Author: franciscom $
-- $Name:  $
-- 
BEGIN TRANSACTION
SET QUOTED_IDENTIFIER ON
SET ARITHABORT ON
SET NUMERIC_ROUNDABORT OFF
SET CONCAT_NULL_YIELDS_NULL ON
SET ANSI_NULLS ON
SET ANSI_PADDING ON
SET ANSI_WARNINGS ON
COMMIT
BEGIN TRANSACTION
EXECUTE sp_rename N'dbo.testplans.[open]', N'Tmp_is_open_2', 'COLUMN'
EXECUTE sp_rename N'dbo.testplans.Tmp_is_open_2', N'is_open', 'COLUMN'
COMMIT
