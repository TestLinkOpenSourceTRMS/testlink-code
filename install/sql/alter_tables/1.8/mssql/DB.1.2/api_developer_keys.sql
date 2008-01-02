/* 
$Revision: 1.1 $
$Date: 2008/01/02 18:55:52 $
$Author: franciscom $
$Name:  $
*/
CREATE TABLE [api_developer_keys] (  
	[id] [int] IDENTITY(1,1) NOT NULL,
  [developer_key] [VARCHAR] (32) NOT NULL,
  [user_id] [int] NOT NULL,
  CONSTRAINT [PK_api_developer_keys] PRIMARY KEY CLUSTERED 
  (
	 [id] ASC
  ) ON [PRIMARY]
) ON [PRIMARY] 
CREATE NONCLUSTERED INDEX [api_developer_keys_user_id] ON [api_developer_keys] 
(
	[user_id] ASC
) ON [PRIMARY]