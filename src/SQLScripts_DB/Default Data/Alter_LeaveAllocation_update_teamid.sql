USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF  EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'TeamID'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingTeamid'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
 

EXEC sp_rename 'dbo.LeaveAllocation.TeamID', 'SchedulingTeamid', 'COLUMN';

END

GO