USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'PlannedDutyBreakTime' AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
  	ALTER TABLE Allocations add PlannedDutyBreakTime INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'PlannedDutyBreakTime' AND Object_ID = Object_ID(N'[dbo].[Allocations_Removed]'))
BEGIN
  	ALTER TABLE Allocations_Removed add PlannedDutyBreakTime INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'PlannedDutyBreakTime' AND Object_ID = Object_ID(N'[dbo].[Allocations_Archive]'))
BEGIN
  	ALTER TABLE Allocations_Archive add PlannedDutyBreakTime INT;
END

GO