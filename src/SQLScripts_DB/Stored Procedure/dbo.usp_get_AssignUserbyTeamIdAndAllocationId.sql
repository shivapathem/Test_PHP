USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_AssignUserbyTeamIdAndAllocationId]    Script Date: 28/02/2025 17:32:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE  [dbo].[usp_get_AssignUserbyTeamIdAndAllocationId]
	-- Add the parameters for the stored procedure here navi
	@allocationIds varchar(max)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
   -- interfering with SELECT statements.
    SET NOCOUNT ON;
	Declare @SQL varchar(max);

	  SET @SQL =' SELECT a.DutyName,a.WeekNumber, 
						 a.iDay,
						 a.StartTime,a.EndTime,
						 a.SchedulingTeamId,
						 ScheduledPeople.DisplayName as FullName,
						 st.schedulingTeamName,
						 spl.TeamID,spl.IsHomeTeam,
						 spl.ScheduledPersonID,
						 a.ID, 
						 a.dutydate,
						 a.MannualOThours
					FROM Allocations as a (NOLOCK)
				   INNER JOIN schedulingTeams AS st (NOLOCK) ON st.schedulingTeamId = a.SchedulingTeamId
					LEFT JOIN ScheduledPeople (NOLOCK) on  ScheduledPeople.ScheduledPersonID = a.SchedulingPersonID  
					LEFT JOIN ScheduledPersonTeam_LINK as spl (NOLOCK) on spl.ScheduledPersonID = a.SchedulingPersonID AND spl.teamid=a.schedulingteamid
					 AND a.dutyDate between spl.StartDate AND isnull(spl.EndDate,a.DutyDate) AND spl.scheduledType=1
				   WHERE a.ID IN (' +@allocationIds+' )';

		Exec (@SQL);
	  
END