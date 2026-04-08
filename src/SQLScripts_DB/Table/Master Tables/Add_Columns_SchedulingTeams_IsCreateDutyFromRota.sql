USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsCreateDutyFromRota' AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
  	ALTER table schedulingteams add IsCreateDutyFromRota BIT default 0
END

GO