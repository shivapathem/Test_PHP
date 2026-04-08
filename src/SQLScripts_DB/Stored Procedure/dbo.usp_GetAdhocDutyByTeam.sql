USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetAdhocDutyByTeam]    Script Date: 25/10/2025 17:36:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_GetAdhocDutyByTeam]
@schedulingTeamID INT,
@startDate VARCHAR(22),
@endDate VARCHAR(22)

AS
BEGIN
	SET NOCOUNT ON;
	SET DATEFORMAT YMD;

	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1

				SELECT ad.*, UD_DisplayName DisplayName, UD_UserID ScheduledPersonID, spl.IsHomeTeam
				from MasterDuties ad
				INNER join UserDetails on UD_UserID = ad.ScheduledPersonID
				INNER join ScheduledPersonTeam_LINK spl on spl.ScheduledPersonID = UD_UserID
				                                       and ad.TeamID = spl.TeamID
				where ad.TeamID = @schedulingTeamID
				AND ad.DutyTypeID = 6
				  AND ad.StartDate <= Convert(datetime, @endDate , 101)
				  AND ad.EndDate >=  Convert(datetime, @startDate, 101)
				  AND spl.scheduledType=1
				  AND SPL.StartDate <= Convert(datetime, @endDate , 101)
				  AND SPL.EndDate >=  Convert(datetime, @startDate, 101)
				  AND ad.IsActive = 1
   
END