USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'HideStats' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
	Alter table User_Web_Config add HideStats bit
END
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'HideStaffList' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
	Alter table User_Web_Config add HideStaffList bit
END
