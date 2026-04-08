USE [Allocate7]

GO

/****** Object:  Table [dbo].[LeaveRequestGroups]    Script Date: 18/01/2024 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'HoursPerLeaveDay'
          AND Object_ID = Object_ID(N'[dbo].[LeaveRequestGroups]'))
BEGIN
alter table LeaveRequestGroups alter column HoursPerLeaveDay decimal(6,2)
END

GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'UserEmailSent'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN
Alter Table LeaveApplications Add UserEmailSent bit
Alter Table LeaveApplications alter column UserEmailSent default 0
END

GO

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'UserEmailSent'
          AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
BEGIN

ALTER TABLE LeaveApplications ALTER COLUMN UserEmailSent INT
END

GO
