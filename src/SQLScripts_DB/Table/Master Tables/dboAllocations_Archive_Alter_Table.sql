USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveStartTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Archive]'))
BEGIN
ALTER TABLE [dbo].[Allocations_Archive] ADD LeaveStartTime INT null
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveEndTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Archive]'))
BEGIN
ALTER TABLE [dbo].[Allocations_Archive] ADD LeaveEndTime INT null
END
