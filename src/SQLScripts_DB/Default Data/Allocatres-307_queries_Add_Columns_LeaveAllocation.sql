USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'TimeDemensionID' AND Object_ID = Object_ID(N'[dbo].[LeaveAllocation]'))
BEGIN
  	ALTER TABLE LeaveAllocation add TimeDemensionID INT;
END


GO