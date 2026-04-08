USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingTeamId'
          AND Object_ID = Object_ID(N'[dbo].[EDP]'))
BEGIN
	Alter Table [dbo].[EDP] ADD SchedulingTeamId INT DEFAULT 0;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingPersonId'
          AND Object_ID = Object_ID(N'[dbo].[EDP]'))
BEGIN
	Alter Table [dbo].[EDP] ADD SchedulingPersonId INT DEFAULT 0;
END
