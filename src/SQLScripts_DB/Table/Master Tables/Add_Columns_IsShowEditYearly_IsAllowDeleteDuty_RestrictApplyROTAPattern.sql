USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsShowEditYearly' AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
  	ALTER TABLE schedulingTeams add IsShowEditYearly BIT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsRestrictDeleteDuty' AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
  	ALTER TABLE schedulingTeams add IsRestrictDeleteDuty BIT;
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'RestrictApplyROTAPattern' AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
  	ALTER TABLE schedulingTeams add RestrictApplyROTAPattern BIT;
END

GO