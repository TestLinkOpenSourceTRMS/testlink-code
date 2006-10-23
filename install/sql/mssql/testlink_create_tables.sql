--  -----------------------------------------------------------------------------------
-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.7 2006/10/23 20:11:28 schlundus Exp $
--
-- SQL script - create db tables for TL
-- Database Type: Microsoft SQL Server
-- 
--  -----------------------------------------------------------------------------------
USE [master]
GO
IF NOT EXISTS (SELECT name FROM master.dbo.sysdatabases WHERE name = N'testlink')
BEGIN
CREATE DATABASE [testlink] ON  PRIMARY 
( NAME = N'testlink', FILENAME = N'c:\Programme\Microsoft SQL Server\MSSQL.1\MSSQL\DATA\testlink.mdf' , SIZE = 3072KB , MAXSIZE = UNLIMITED, FILEGROWTH = 1024KB )
 LOG ON 
( NAME = N'testlink_log', FILENAME = N'c:\Programme\Microsoft SQL Server\MSSQL.1\MSSQL\DATA\testlink_log.ldf' , SIZE = 1024KB , MAXSIZE = 2048GB , FILEGROWTH = 10%)
END

GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [testlink].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [testlink] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [testlink] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [testlink] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [testlink] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [testlink] SET ARITHABORT OFF 
GO
ALTER DATABASE [testlink] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [testlink] SET AUTO_CREATE_STATISTICS ON 
GO
ALTER DATABASE [testlink] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [testlink] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [testlink] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [testlink] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [testlink] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [testlink] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [testlink] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [testlink] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [testlink] SET  READ_WRITE 
GO
ALTER DATABASE [testlink] SET RECOVERY SIMPLE 
GO
ALTER DATABASE [testlink] SET  MULTI_USER 
GO
if ( ((@@microsoftversion / power(2, 24) = 8) and (@@microsoftversion & 0xffff >= 760)) or 
		(@@microsoftversion / power(2, 24) >= 9) )begin 
	exec dbo.sp_dboption @dbname =  N'testlink', @optname = 'db chaining', @optvalue = 'OFF'
 end
USE [testlink]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[cfield_design_values]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[cfield_design_values](
	[field_id] [int] NOT NULL,
	[node_id] [int] NOT NULL CONSTRAINT [DF_cfield_design_values_node_id]  DEFAULT ((0)),
	[value] [nvarchar](255) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_cfield_design_values_value]  DEFAULT ((0)),
 CONSTRAINT [PK_cfield_design_values] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[node_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[cfield_design_values]') AND name = N'dx_cfield_design_values')
CREATE NONCLUSTERED INDEX [dx_cfield_design_values] ON [dbo].[cfield_design_values] 
(
	[node_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[cfield_execution_values]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[cfield_execution_values](
	[field_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_field_id]  DEFAULT ((0)),
	[execution_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_execution_id]  DEFAULT ((0)),
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_testplan_id]  DEFAULT ((0)),
	[tcversion_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_tcversion_id]  DEFAULT ((0)),
	[value] [nvarchar](255) COLLATE Latin1_General_CI_AS NOT NULL,
 CONSTRAINT [PK_cfield_execution_values] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[execution_id] ASC,
	[testplan_id] ASC,
	[tcversion_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[testplan_tcversions]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[testplan_tcversions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[tcversion_id] [int] NOT NULL CONSTRAINT [DF_testplan_tcversions_tcversion_id]  DEFAULT ((0)),
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_testplan_tcversions_testplan_id]  DEFAULT ((0)),
 CONSTRAINT [PK_testplan_tcversions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_tp_tcversion] UNIQUE NONCLUSTERED 
(
	[tcversion_id] ASC,
	[testplan_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[db_version]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[db_version](
	[version] [nvarchar](50) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_db_version_version]  DEFAULT (N'unknown'),
	[upgrade_ts] [datetime] NOT NULL CONSTRAINT [DF_db_version_upgrade_ts]  DEFAULT (getdate())
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[object_keywords]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[object_keywords](
	[id] [int] NOT NULL CONSTRAINT [DF_object_keywords_id]  DEFAULT ((0)),
	[fk_id] [int] NOT NULL CONSTRAINT [DF_object_keywords_fk_id]  DEFAULT ((0)),
	[fk_table] [nvarchar](30) COLLATE Latin1_General_CI_AS NOT NULL,
	[keyword_id] [int] NOT NULL CONSTRAINT [DF_object_keywords_keyword_id]  DEFAULT ((0)),
 CONSTRAINT [PK_object_keywords] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[roles]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[roles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL,
	[notes] [ntext] COLLATE Latin1_General_CI_AS NULL,
 CONSTRAINT [PK_roles] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_Description1] UNIQUE NONCLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[execution_bugs]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[execution_bugs](
	[execution_id] [int] NOT NULL CONSTRAINT [DF_execution_bugs_execution_id]  DEFAULT ((0)),
	[bug_id] [nvarchar](16) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_execution_bugs_bug_id]  DEFAULT ((0)),
 CONSTRAINT [PK_execution_bugs] PRIMARY KEY CLUSTERED 
(
	[execution_id] ASC,
	[bug_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[user_assignments]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[user_assignments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[type] [int] NOT NULL CONSTRAINT [DF_user_assignments_type]  DEFAULT ((0)),
	[feature_id] [int] NULL CONSTRAINT [DF_user_assignments_feature_id]  DEFAULT ((0)),
	[user_id] [int] NULL,
	[deadline_ts] [datetime] NOT NULL,
	[assigner_id] [int] NULL,
	[creation_ts] [datetime] NOT NULL,
	[status] [int] NULL,
 CONSTRAINT [PK_user_assignments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[executions]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[executions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[build_id] [int] NOT NULL CONSTRAINT [DF_executions_build_id]  DEFAULT ((0)),
	[tester_id] [int] NULL CONSTRAINT [DF_executions_tester_id]  DEFAULT (NULL),
	[execution_ts] [datetime] NULL CONSTRAINT [DF_executions_execution_ts]  DEFAULT (NULL),
	[status] [nchar](1) COLLATE Latin1_General_CI_AS NULL CONSTRAINT [DF_executions_status]  DEFAULT (NULL),
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_executions_testplan_id]  DEFAULT ((0)),
	[tcversion_id] [int] NOT NULL CONSTRAINT [DF_executions_tcversion_id]  DEFAULT ((0)),
	[notes] [ntext] COLLATE Latin1_General_CI_AS NULL CONSTRAINT [DF_executions_notes]  DEFAULT (NULL),
 CONSTRAINT [PK_executions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[risk_assignments]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[risk_assignments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_risk_assignments_testplan_id]  DEFAULT ((0)),
	[node_id] [int] NOT NULL CONSTRAINT [DF_risk_assignments_node_id]  DEFAULT ((0)),
	[risk] [int] NOT NULL CONSTRAINT [DF_risk_assignments_risk]  DEFAULT ((2)),
	[importance] [nchar](1) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_risk_assignments_importance]  DEFAULT (N'M'),
 CONSTRAINT [PK_risk_assignments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_tp_node_id] UNIQUE NONCLUSTERED 
(
	[testplan_id] ASC,
	[node_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[rights]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[rights](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL,
 CONSTRAINT [PK_rights] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_Description] UNIQUE NONCLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[builds]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[builds](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_builds_testplan_id]  DEFAULT ((0)),
	[name] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_builds_name]  DEFAULT (N'undefined'),
	[notes] [ntext] COLLATE Latin1_General_CI_AS NULL,
 CONSTRAINT [PK_builds] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[builds]') AND name = N'IX_name')
CREATE UNIQUE NONCLUSTERED INDEX [IX_name] ON [dbo].[builds] 
(
	[testplan_id] ASC,
	[name] ASC
) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[builds]') AND name = N'IX_testplan_id')
CREATE NONCLUSTERED INDEX [IX_testplan_id] ON [dbo].[builds] 
(
	[testplan_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[priorities]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[priorities](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_priorities_testplan_id]  DEFAULT ((0)),
	[risk_importance] [nchar](2) COLLATE Latin1_General_CI_AS NOT NULL,
	[priority] [nchar](1) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_priorities_priority]  DEFAULT (N'b'),
 CONSTRAINT [PK_priorities] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[priorities]') AND name = N'IX_testplan_id')
CREATE NONCLUSTERED INDEX [IX_testplan_id] ON [dbo].[priorities] 
(
	[testplan_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[keywords]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[keywords](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[keyword] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL,
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_keywords_testproject_id]  DEFAULT ((0)),
	[notes] [ntext] COLLATE Latin1_General_CI_AS NULL,
 CONSTRAINT [PK_keywords] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[keywords]') AND name = N'IX_keywords')
CREATE NONCLUSTERED INDEX [IX_keywords] ON [dbo].[keywords] 
(
	[testproject_id] ASC
) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[keywords]') AND name = N'IX_keywords_keyword')
CREATE NONCLUSTERED INDEX [IX_keywords_keyword] ON [dbo].[keywords] 
(
	[keyword] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[milestones]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[milestones](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_milestones_testplan_id]  DEFAULT ((0)),
	[date] [datetime] NOT NULL,
	[A] [tinyint] NOT NULL CONSTRAINT [DF_milestones_A]  DEFAULT ((0)),
	[B] [tinyint] NOT NULL CONSTRAINT [DF_milestones_B]  DEFAULT ((0)),
	[C] [tinyint] NOT NULL CONSTRAINT [DF_milestones_C]  DEFAULT ((0)),
	[name] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_milestones_name]  DEFAULT (N'undefined'),
 CONSTRAINT [PK_Milestones] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[milestones]') AND name = N'IX_Testplan')
CREATE NONCLUSTERED INDEX [IX_Testplan] ON [dbo].[milestones] 
(
	[testplan_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[attachments]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[attachments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NOT NULL CONSTRAINT [DF_attachments_fk_id]  DEFAULT ((0)),
	[fk_table] [nvarchar](250) COLLATE Latin1_General_CI_AS NULL,
	[title] [nvarchar](250) COLLATE Latin1_General_CI_AS NULL,
	[description] [nvarchar](250) COLLATE Latin1_General_CI_AS NULL,
	[file_name] [nvarchar](250) COLLATE Latin1_General_CI_AS NOT NULL,
	[file_path] [nvarchar](250) COLLATE Latin1_General_CI_AS NOT NULL,
	[file_size] [int] NOT NULL CONSTRAINT [DF_attachments_file_size]  DEFAULT ((0)),
	[file_type] [nvarchar](250) COLLATE Latin1_General_CI_AS NOT NULL,
	[date_added] [datetime] NOT NULL,
	[content] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[compression_type] [int] NOT NULL CONSTRAINT [DF_attachments_compression_type]  DEFAULT ((0)),
 CONSTRAINT [PK_attachments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[node_types]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[node_types](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_node_types_description]  DEFAULT (N'testproject'),
 CONSTRAINT [PK_node_types] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[nodes_hierarchy]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[nodes_hierarchy](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) COLLATE Latin1_General_CI_AS NULL,
	[parent_id] [int] NULL,
	[node_type_id] [int] NOT NULL CONSTRAINT [DF_nodes_hierarchy_node_type_id]  DEFAULT ((1)),
	[node_order] [int] NULL,
 CONSTRAINT [PK_nodes_hierarchy] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[nodes_hierarchy]') AND name = N'IX_pid_m_nodeorder')
CREATE NONCLUSTERED INDEX [IX_pid_m_nodeorder] ON [dbo].[nodes_hierarchy] 
(
	[parent_id] ASC,
	[node_order] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[req_coverage]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[req_coverage](
	[req_id] [int] NOT NULL,
	[testcase_id] [int] NOT NULL
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[req_coverage]') AND name = N'IX_req_testcase')
CREATE NONCLUSTERED INDEX [IX_req_testcase] ON [dbo].[req_coverage] 
(
	[req_id] ASC,
	[testcase_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[req_specs]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[req_specs](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testproject_id] [int] NOT NULL,
	[title] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL,
	[scope] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[total_req] [int] NOT NULL CONSTRAINT [DF_req_specs_total_req]  DEFAULT ((0)),
	[type] [nchar](1) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_req_specs_type]  DEFAULT (N'n'),
	[author_id] [int] NULL,
	[creation_ts] [datetime] NOT NULL CONSTRAINT [DF_req_specs_creation_ts]  DEFAULT (getdate()),
	[modifier_id] [int] NULL,
	[modification_ts] [datetime] NULL,
 CONSTRAINT [PK_req_specs] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[req_specs]') AND name = N'IX_testproject_id')
CREATE NONCLUSTERED INDEX [IX_testproject_id] ON [dbo].[req_specs] 
(
	[testproject_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[requirements]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[requirements](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[srs_id] [int] NOT NULL,
	[req_doc_id] [nvarchar](16) COLLATE Latin1_General_CI_AS NULL,
	[title] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL,
	[scope] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[status] [nchar](1) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_requirements_status]  DEFAULT (N'n'),
	[type] [nchar](1) COLLATE Latin1_General_CI_AS NULL,
	[author_id] [int] NULL,
	[creation_ts] [datetime] NULL CONSTRAINT [DF_requirements_creation_ts]  DEFAULT (getdate()),
	[modifier_id] [int] NULL,
	[modification_ts] [datetime] NULL,
 CONSTRAINT [PK_requirements] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[role_rights]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[role_rights](
	[role_id] [int] NOT NULL CONSTRAINT [DF_role_rights_role_id]  DEFAULT ((0)),
	[right_id] [int] NOT NULL CONSTRAINT [DF_role_rights_right_id]  DEFAULT ((0)),
 CONSTRAINT [PK_role_rights] PRIMARY KEY CLUSTERED 
(
	[role_id] ASC,
	[right_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[testcase_keywords]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[testcase_keywords](
	[testcase_id] [int] NOT NULL CONSTRAINT [DF_testcase_keywords_testcase_id]  DEFAULT ((0)),
	[keyword_id] [int] NOT NULL CONSTRAINT [DF_testcase_keywords_keyword_id]  DEFAULT ((0)),
 CONSTRAINT [PK_testcase_keywords] PRIMARY KEY CLUSTERED 
(
	[testcase_id] ASC,
	[keyword_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[testcase_keywords]') AND name = N'IX_testcase_keywords')
CREATE NONCLUSTERED INDEX [IX_testcase_keywords] ON [dbo].[testcase_keywords] 
(
	[testcase_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[tcversions]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[tcversions](
	[id] [int] NOT NULL,
	[version] [smallint] NOT NULL CONSTRAINT [DF_tcversions_version]  DEFAULT ((1)),
	[summary] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[steps] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[expected_results] [ntext] COLLATE Latin1_General_CI_AS NOT NULL,
	[importance] [nchar](1) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_tcversions_importance]  DEFAULT (N'M'),
	[author_id] [int] NULL,
	[creation_ts] [datetime] NOT NULL CONSTRAINT [DF_tcversions_creation_ts]  DEFAULT (getdate()),
	[updater_id] [int] NULL,
	[modification_ts] [datetime] NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_tcversions_active]  DEFAULT ((1)),
	[open] [tinyint] NOT NULL CONSTRAINT [DF_tcversions_open]  DEFAULT ((1)),
 CONSTRAINT [PK_tcversions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[testplans]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[testplans](
	[id] [int] NOT NULL,
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_testplans_testproject_id]  DEFAULT ((0)),
	[notes] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_testplans_active]  DEFAULT ((1)),
	[open] [tinyint] NOT NULL CONSTRAINT [DF_testplans_open]  DEFAULT ((1)),
 CONSTRAINT [PK_testplans] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[testplans]') AND name = N'IX_testproject_id_active')
CREATE NONCLUSTERED INDEX [IX_testproject_id_active] ON [dbo].[testplans] 
(
	[testproject_id] ASC,
	[active] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[testprojects]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[testprojects](
	[id] [int] NOT NULL,
	[notes] [ntext] COLLATE Latin1_General_CI_AS NULL,
	[color] [nvarchar](12) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_testprojects_color]  DEFAULT (N'#9BD'),
	[active] [tinyint] NOT NULL CONSTRAINT [DF_testprojects_active]  DEFAULT ((1)),
	[option_reqs] [tinyint] NOT NULL CONSTRAINT [DF_testprojects_option_reqs]  DEFAULT ((0)),
	[option_priority] [tinyint] NOT NULL CONSTRAINT [DF_testprojects_option_priority]  DEFAULT ((1)),
 CONSTRAINT [PK_testprojects] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[testprojects]') AND name = N'IX_id_active')
CREATE NONCLUSTERED INDEX [IX_id_active] ON [dbo].[testprojects] 
(
	[id] ASC,
	[active] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[testsuites]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[testsuites](
	[id] [int] NOT NULL,
	[details] [ntext] COLLATE Latin1_General_CI_AS NULL,
 CONSTRAINT [PK_testsuites] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[user_testproject_roles]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[user_testproject_roles](
	[user_id] [int] NOT NULL CONSTRAINT [DF_user_testproject_roles_user_id]  DEFAULT ((0)),
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_user_testproject_roles_testproject_id]  DEFAULT ((0)),
	[role_id] [int] NOT NULL CONSTRAINT [DF_user_testproject_roles_role_id]  DEFAULT ((0)),
 CONSTRAINT [PK_user_testproject_roles] PRIMARY KEY CLUSTERED 
(
	[user_id] ASC,
	[testproject_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[user_testplan_roles]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[user_testplan_roles](
	[user_id] [int] NOT NULL,
	[testplan_id] [int] NOT NULL,
	[role_id] [int] NOT NULL,
 CONSTRAINT [PK_user_testplan_roles] PRIMARY KEY CLUSTERED 
(
	[user_id] ASC,
	[testplan_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[users]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[login] [nvarchar](30) COLLATE Latin1_General_CI_AS NOT NULL,
	[password] [nvarchar](32) COLLATE Latin1_General_CI_AS NOT NULL,
	[role_id] [int] NOT NULL CONSTRAINT [DF_users_role_id]  DEFAULT ((0)),
	[email] [nvarchar](100) COLLATE Latin1_General_CI_AS NOT NULL,
	[first] [nvarchar](30) COLLATE Latin1_General_CI_AS NOT NULL,
	[last] [nvarchar](30) COLLATE Latin1_General_CI_AS NOT NULL,
	[locale] [nvarchar](10) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_users_locale]  DEFAULT (N'en_US'),
	[default_testproject_id] [int] NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_users_active]  DEFAULT ((1)),
 CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[users]') AND name = N'IX_users_login')
CREATE NONCLUSTERED INDEX [IX_users_login] ON [dbo].[users] 
(
	[login] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[assignment_types]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[assignment_types](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_table] [nchar](30) COLLATE Latin1_General_CI_AS NOT NULL,
	[description] [nchar](100) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_assignment_types_description]  DEFAULT (N'unknown'),
 CONSTRAINT [PK_assignment_types] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[assignment_status]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[assignment_status](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [nchar](100) COLLATE Latin1_General_CI_AS NOT NULL CONSTRAINT [DF_assignment_status_description]  DEFAULT (N'unknown'),
 CONSTRAINT [PK_assignment_status] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[cfield_node_types]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[cfield_node_types](
	[field_id] [int] NOT NULL CONSTRAINT [DF_cfield_node_types_field_id]  DEFAULT ((0)),
	[node_type_id] [int] NOT NULL CONSTRAINT [DF_cfield_node_types_node_type_id]  DEFAULT ((0)),
 CONSTRAINT [PK_cfield_node_types] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[node_type_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM dbo.sysindexes WHERE id = OBJECT_ID(N'[dbo].[cfield_node_types]') AND name = N'idx_custom_fields_assign')
CREATE NONCLUSTERED INDEX [idx_custom_fields_assign] ON [dbo].[cfield_node_types] 
(
	[node_type_id] ASC
) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[cfield_testprojects]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[cfield_testprojects](
	[field_id] [int] NOT NULL CONSTRAINT [DF_cfield_testprojects_field_id]  DEFAULT ((0)),
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_cfield_testprojects_testproject_id]  DEFAULT ((0)),
 CONSTRAINT [PK_cfield_testprojects] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[testproject_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
