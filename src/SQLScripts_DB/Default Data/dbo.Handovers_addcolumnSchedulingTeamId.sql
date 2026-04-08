USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingTeamId'
          AND Object_ID = Object_ID(N'[dbo].[Handovers]'))
BEGIN
    Alter Table [dbo].[Handovers] ADD SchedulingTeamId int;


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'WeeklyFilterOption'
          AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
    Alter Table [dbo].[User_Web_Config] ADD WeeklyFilterOption BIT;


END

GO
