USE [Allocate7]
GO

/****** Object:  Table [dbo].[Requests]    Script Date: 27/07/2023 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[Requests]'))
BEGIN
ALTER TABLE [dbo].[Requests] ADD ScheduledPersonID INT NULL DEFAULT 0
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'UserComments'
          AND Object_ID = Object_ID(N'[dbo].[Requests]'))
BEGIN
ALTER TABLE [dbo].Requests ALTER COLUMN UserComments varchar(max) 
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Comments'
          AND Object_ID = Object_ID(N'[dbo].[Requests]'))
BEGIN
ALTER TABLE [dbo].Requests ALTER COLUMN Comments varchar(max) 
END

GO
