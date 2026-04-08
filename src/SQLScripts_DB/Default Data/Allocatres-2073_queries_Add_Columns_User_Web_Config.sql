USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'CurrentFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add CurrentFilter VARCHAR(255);
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'SchedulingTeamId' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add SchedulingTeamId INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'ProdDayCount' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add ProdDayCount INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'ProdStartDay' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add ProdStartDay INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'DailyFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add DailyFilter INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'WeeklyFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add WeeklyFilter INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'EditWeeklyFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add EditWeeklyFilter INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'MultiWeekFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add MultiWeekFilter INT;
END
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'Dailyunallocated' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add Dailyunallocated INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'Checkstartendshift' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add Checkstartendshift INT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'EditWeeklyRotaFilter' AND Object_ID = Object_ID(N'[dbo].[User_Web_Config]'))
BEGIN
  	ALTER TABLE User_Web_Config add EditWeeklyRotaFilter INT;
END
GO