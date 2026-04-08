USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_AdminTeamsForLeaveCredit]    Script Date: 29/01/2024 18:56:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE  OR   ALTER  PROCEDURE [dbo].[usp_get_AdminTeamsForLeaveCredit]
@userid INT
AS
BEGIN
	SELECT schedulingTeamName,maskAfter,maskType,editingStart,editingEnd,WeekendOnly,signInDays,allowInBuilding,colourWeek,allowOvertimeRequests,
		schedulingTeamId,locksEnd,dailyViewMaskingDays,locksWeekataTime,hasHandovers,hasXmasPoints,hasGridChecks,Email,defaultNumberweeksRotaPattern,
		isnull(autoImportWeeks, 0) AS autoImportWeeks,freelancerMaskingDays
	FROM UserTeamRole_LINK utrl 
		INNER JOIN schedulingTeams st ON st.schedulingTeamId = utrl.TeamID
		inner join ScheduledPeople SP on SP.UserID = utrl.UserID
		inner join ScheduledPersonTeam_LINK  SL ON st.schedulingTeamId = sl.TeamID and sp.ScheduledPersonID = sl.ScheduledPersonID
    WHERE utrl.UserID = @userid AND utrl.RoleID = 3
	and utrl.StartDate <= cast(getdate() as DATE) and utrl.EndDate >= cast(getdate() as DATE)
	and cast(sl.StartDate as date) <= cast(getdate() as DATE)
	and isnull(sl.EndDate, cast(getdate() as date) ) >= cast(getdate() as DATE)
	and schedulingTeamName NOT IN ('Freelancers','Archive')
	and sl.scheduledType = 0
	and st.isActive =1
	ORDER BY st.schedulingTeamName ASC
END