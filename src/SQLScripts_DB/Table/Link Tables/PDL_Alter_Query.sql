USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveStartTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD LeaveStartTime INT NULL
END
 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveEndTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations]'))
BEGIN
ALTER TABLE [dbo].[Allocations] ADD LeaveEndTime INT NULL
END
 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveStartTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Publish]'))
BEGIN
ALTER TABLE [dbo].[Allocations_Publish] ADD LeaveStartTime INT NULL
END
 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveEndTime'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_Publish]'))
BEGIN
ALTER TABLE [dbo].[Allocations_Publish] ADD LeaveEndTime INT NULL
END
 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveStartTime'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
ALTER TABLE [dbo].[LeaveApplications] ADD LeaveStartTime INT NULL
END
 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'LeaveEndTime'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
ALTER TABLE [dbo].[LeaveApplications] ADD LeaveEndTime INT NULL
END
GO
