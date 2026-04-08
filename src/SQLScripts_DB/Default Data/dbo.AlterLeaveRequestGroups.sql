USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = N'DivisionID' AND Object_ID = Object_ID(N'[dbo].[LeaveRequestGroups]'))
BEGIN
Alter Table LeaveRequestGroups Add DivisionID int
END
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = N'AllowEmails' AND Object_ID = Object_ID(N'[dbo].[LeaveRequestGroups]'))
BEGIN
Alter Table LeaveRequestGroups Add AllowEmails bit
END