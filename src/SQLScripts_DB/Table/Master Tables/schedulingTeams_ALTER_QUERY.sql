USE [Allocate7]
GO

/****** Object:  Table [dbo].[schedulingTeams]    Script Date: 02/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[ConfirmedDays]'
          AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
	ALTER TABLE [dbo].[schedulingTeams] ADD ConfirmedDays INT NULL   

END
--Added On 17 Nov 2021 By Soniya
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'MultiWeeks'
          AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
	ALTER TABLE [dbo].[schedulingTeams] ADD MultiWeeks INT DEFAULT 4   

END
GO