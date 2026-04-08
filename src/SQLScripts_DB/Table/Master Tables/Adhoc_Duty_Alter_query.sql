USE [Allocate7]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsWeekCreated'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
BEGIN
ALTER TABLE [dbo].[adhoc_duty] ADD IsWeekCreated bit null
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyComment'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
BEGIN
ALTER TABLE [dbo].[adhoc_duty] ADD DutyComment varchar(1000)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'DutyColourID'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
		  
BEGIN
ALTER TABLE [dbo].[adhoc_duty] ADD DutyColourID int NULL
END	

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'BreakTime'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
		  
BEGIN
ALTER TABLE [dbo].[adhoc_duty] ADD BreakTime int NULL
END	

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'Duration'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
		  
BEGIN
ALTER TABLE [dbo].[adhoc_duty] ADD Duration int NULL
END	

IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedDate'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
BEGIN
  alter table adhoc_duty add CreatedDate datetime default NULL;
END

IF  NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedBy'
          AND Object_ID = Object_ID(N'[dbo].[adhoc_duty]'))
BEGIN
  alter table adhoc_duty add CreatedBy int default NULL;
END

GO
