USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ScheduledPersonID'
          AND Object_ID = Object_ID(N'[dbo].[XmasPoints]'))
BEGIN
  alter table XmasPoints add ScheduledPersonID int;


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingTeamId'
          AND Object_ID = Object_ID(N'[dbo].[XmasPoints]'))
BEGIN
 
  alter table XmasPoints add SchedulingTeamId int;

END


GO