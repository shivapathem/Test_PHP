USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'PHLLeaveAmount' AND Object_ID = Object_ID(N'[dbo].[ScheduledPeople]'))
BEGIN
  	ALTER TABLE ScheduledPeople add PHLLeaveAmount FLOAT NULL;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'SchedulingPersonID' AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  	ALTER TABLE LeaveAllocation add SchedulingPersonID INT NULL;
END


GO