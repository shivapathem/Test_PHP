USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CountLeave'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
  alter table LeaveApplications add CountLeave int default 1;


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'ZeroLeave'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
 
  alter table LeaveApplications add ZeroLeave int;

END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'SchedulingPersonID'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
 
 alter table LeaveApplications add SchedulingPersonID int;

END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Totalhrs'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
 
 alter table LeaveApplications add Totalhrs int; 

END

GO