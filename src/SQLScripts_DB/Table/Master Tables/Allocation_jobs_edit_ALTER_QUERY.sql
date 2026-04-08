USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocation_jobs_edit]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[info ]'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
ALTER TABLE [dbo].[Allocation_jobs_edit] DROP COLUMN info
END


IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'delete'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
ALTER TABLE [dbo].[Allocation_jobs_edit] DROP COLUMN [delete]
END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'IsDeleted'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
ALTER TABLE [dbo].[Allocation_jobs_edit] Add IsDeleted bit default 0
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Job_Info'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
ALTER TABLE [dbo].[Allocation_jobs_edit] Add Job_Info varchar(1000)
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'JobFontColour'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
ALTER TABLE [dbo].[Allocation_jobs_edit] ADD CONSTRAINT DF_Allocation_jobs_edit_JobFontColour DEFAULT '#ffffff' FOR JobFontColour
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'JobBackColour'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN

ALTER TABLE [dbo].[Allocation_jobs_edit] ADD CONSTRAINT DF_Allocation_jobs_edit_JobBackColour DEFAULT '#000000' FOR JobBackColour
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'JobDefaultColour'
          AND Object_ID = Object_ID(N'[dbo].[Allocation_jobs_edit]'))
BEGIN
ALTER TABLE [dbo].[Allocation_jobs_edit] DROP COLUMN JobDefaultColour
END

GO