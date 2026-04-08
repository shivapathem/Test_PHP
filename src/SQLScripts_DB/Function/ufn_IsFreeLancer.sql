USE [BBCSchedules]
GO
/****** Object:  UserDefinedFunction [dbo].[ufn_IsFreeLancer]    Script Date: 10/07/2025 12:50:22 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER     FUNCTION [dbo].[ufn_IsFreeLancer]
 (
	@UserID INT, 
	@DutyDate DATE
 )
RETURNS INT
AS
BEGIN

   DECLARE @IsFreelancer BIT = 0;

	IF EXISTS ( SELECT TOP 1 STL.ScheduledPersonID
				  FROM ScheduledPersonTeam_LINK AS STL
				 INNER JOIN schedulingTeams ST ON ST.schedulingTeamId = STL.TeamID
				 WHERE ST.schedulingTeamName in ('Other BBC', 'Freelancers','Apprentices')
				   AND STL.IsHomeTeam = 1
				   AND STL.ScheduledPersonID = @UserID
				   AND @DutyDate BETWEEN STL.StartDate AND STL.EndDate
				)
		SET @IsFreelancer = 1

	RETURN @IsFreelancer

END