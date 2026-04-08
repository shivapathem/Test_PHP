USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Email'
          AND Object_ID = Object_ID(N'[dbo].[AlterEmailCol_SchedulingTeam]'))
BEGIN
	Alter Table [dbo].[SchedulingTeams] ADD Email NVARCHAR(100);
END
