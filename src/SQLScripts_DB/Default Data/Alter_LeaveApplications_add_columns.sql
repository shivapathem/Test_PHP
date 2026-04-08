USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'CreatedBy'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
  alter table LeaveApplications add CreatedBy int;


END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'LastModBy'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
 
  alter table LeaveApplications add LastModBy int;

END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'LastModDate'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
 
  alter table LeaveApplications add LastModDate datetime;

END


GO