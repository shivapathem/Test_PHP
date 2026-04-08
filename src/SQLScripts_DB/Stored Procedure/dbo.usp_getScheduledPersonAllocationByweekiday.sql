USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_getScheduledPersonAllocationByweekiday]    Script Date: 06/08/2025 13:16:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_getScheduledPersonAllocationByweekiday] 
	-- Add the parameters for the stored procedure here
	@intWeekNumber int,
	@intDay int,
	@intScheduledPersonID int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	SELECT a.SchedulingTeamId, 
	a.DutyName AS DutyName, 
	a.StartTime AS StartTime, 
	a.EndTime  AS EndTime, 
	a.Duration AS Duration, 
	schedulingTeams.schedulingTeamName AS TeamName
	FROM UserDetails as sp (NOLOCK)
	INNER JOIN ScheduledPersonTeam_LINK as spl (Nolock) ON spl.ScheduledPersonID = sp.UD_UserID
	INNER JOIN Allocations_Publish as a (NOLOCK) ON spl.ScheduledPersonID = a.SchedulingPersonID 
	INNER JOIN schedulingTeams (NOLOCK) ON spl.TeamID = schedulingTeams.schedulingTeamId 
	--Left JOIN StaffDetails as staff (NOLOCK) ON staff.StaffID =sp.StaffDetailsID
	WHERE (a.WeekNumber = @intWeekNumber)
	AND (a.iDay = @intDay) 
	AND (a.SchedulingPersonID = @intScheduledPersonID) AND (spl.scheduledType=1 AND spl.IsHomeTeam=1) AND (isnull(spl.EndDate,'9999-01-01') >= getdate() AND spl.StartDate <=getDate())
END