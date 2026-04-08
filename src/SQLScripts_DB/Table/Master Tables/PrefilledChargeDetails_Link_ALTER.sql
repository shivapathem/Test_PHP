USE [Allocate7]
GO
/****** Object:  Table [dbo].[schedulingTeams]    Script Date: 09/11/2021 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'SchedulingTeamId'
          AND Object_ID = Object_ID(N'[dbo].[PrefilledChargeDetails_Link]'))
BEGIN
    ALTER TABLE [dbo].[PrefilledChargeDetails_Link] ADD SchedulingTeamId INT NULL
END

GO
