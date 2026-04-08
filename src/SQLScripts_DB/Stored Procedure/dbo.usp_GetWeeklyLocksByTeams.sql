USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetWeeklyLocksByTeams]    Script Date: 26/12/2025 21:51:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE  [dbo].[usp_GetWeeklyLocksByTeams]
	@intweekno int,
	@userId int
AS
BEGIN

	SET NOCOUNT ON;

	 SELECT UD_DisplayName AS FullName, 
			spl.SortCode, 
			UD_NetLogin as Login,
			UD_StaffNumber as  StaffNumber, 
			LR.iDay, 
			LR.AllocationsInformation, 
			LR.AdminRequest,
			LR.RequestedOn,
			LR.ID AS LockID, spl.TeamID,
			ST.schedulingTeamName AS TeamFullName, 
			LR.DutyName AS LockDutyName, 
			LR.StartTime AS LockStartTime,
			LR.EndTime AS LockEndTime, 
			LR.Duration AS LockDuration, 
			CASE WHEN AD.AD_DutyName IS NULL THEN 'U'
				 WHEN AD.AD_DutyType IN (8,11,12)
					     THEN CASE WHEN ASP_LeaveType = 1
								   THEN 'Leave'
								   WHEN ASP_LeaveType = 2
								   THEN 'OFF Leave'
								   WHEN ASP_LeaveType = 3
								   THEN 'Sick'
								   WHEN ASP_LeaveType = 4
								   THEN 'U-Sick'
								   WHEN ASP_LeaveType = 5
								   THEN '-Sick'
								   WHEN ASP_LeaveType = 7
								   THEN 'Absent'
							   END
			        ELSE AD.AD_DutyName
			END AS DutyName,
			AD.AD_StartTimeSec AS StartTime,
			AD.AD_EndTimeSec AS EndTime,
			CASE WHEN AD_DutyType IN (8,11,12)
				THEN ISNULL(ASP_LeaveDuration,0)
				ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
			LR.ScheduledPersonID AS ScheduledPersonID
	FROM LockRequests (Nolock) LR
   INNER JOIN ScheduledPersonTeam_LINK as spl (Nolock) ON  LR.ScheduledPersonID = spl.ScheduledPersonID 
	INNER JOIN UserDetails as sp (Nolock) ON sp.UD_UserID = LR.ScheduledPersonID
	INNER JOIN schedulingTeams ST (Nolock) ON spl.TeamID = st.schedulingTeamId
	LEFT JOIN Allocations as a (Nolock)  ON a.AL_WeekNumber = LR.WeekNumber AND a.AL_SchedulingTeamID = spl.TeamID
	LEFT JOIN AllocationsScheduledPersons ASP on a.AL_AllocationsID = ASP.ASP_AllocationsID
												AND ASP.ASP_SchedulingPersonID = LR.ScheduledPersonID
												AND ASP.ASP_iDay = LR.iDay 
	LEFT JOIN AllocationsDuties AD ON AD.AD_AllocationsDutyID = ASP_AllocationsDutyID
	WHERE (LR.WeekNumber = @intweekno) 
		AND (LR.deleted = 0) 
		AND spl.scheduledType=1 AND spl.IsHomeTeam=1  
		AND getDate() between spl.StartDate and isnull(spl.EndDate,'9999-01-01')  
	    AND  exists     ( Select 1
							FROM UserRoles as usr (Nolock)
							INNER JOIN schedulingTeams as st1 (nolock) on usr.UR_SchedulingTeamID = st1.schedulingTeamId
							where usr.UR_UserID = @userId 
							 AND UR_RoleID IN (3,4,5)
							 and GETDATE() between usr.UR_StartDate and usr.UR_EndDate
							AND st.isActive=1 
							AND st.schedulingTeamName NOT IN ('Freelancers','Archive','Other BBC')
							AND spl.TeamID = st1.schedulingTeamId)
	ORDER BY FullName

END