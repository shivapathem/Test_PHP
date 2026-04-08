USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsRestrictCopyDuty' AND Object_ID = Object_ID(N'[dbo].[schedulingTeams]'))
BEGIN
  	ALTER TABLE schedulingteams add IsRestrictCopyDuty BIT DEFAULT (0)

  	UPDATE st
   	SET st.IsRestrictCopyDuty  = 1
  	FROM schedulingTeams st
	INNER JOIN schedulingTeamDivision_Link std ON st.schedulingTeamId = std.schedulingTeamId
	WHERE std.DivisionId =  1
END

GO