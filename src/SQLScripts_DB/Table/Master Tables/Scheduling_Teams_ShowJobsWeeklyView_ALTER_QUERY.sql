USE [Allocate7]
GO

/****** Object:  Table [dbo].[schedulingTeams]    Script Date: 29/07/2024  ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF not EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'ShowJobsInWeeklyView'
          AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
    alter table schedulingTeams add ShowJobsInWeeklyView BIT NULL;
END;

GO