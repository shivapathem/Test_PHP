USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetchAllocationInforByschedullerorAboveRole]    Script Date: 26/12/2025 21:47:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[usp_fetchAllocationInforByschedullerorAboveRole]
	@intweekno int,
	@iday int,
	@userId int,
	@scheduledpersonid int

AS
BEGIN

  SET NOCOUNT ON;

	SELECT spl.TeamID,
		   sp.UD_UserID  ScheduledPersonID,
		   CASE WHEN AD_DutyName IS NULL THEN 'U'
					WHEN  AD_DutyType IN (8,11,12)
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
			        ELSE AD_DutyName
					END AS   DutyName,
           AD_StartTimeSec AS StartTime,
           AD_EndTimeSec AS EndTime,
		   CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ISNULL(ASP_LeaveDuration,0)
						 ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
           ST.schedulingTeamName AS TeamName
	FROM UserDetails as sp (Nolock) 
	INNER JOIN ScheduledPersonTeam_LINK as spl (Nolock) ON spl.ScheduledPersonID = sp.UD_UserID 
	INNER JOIN schedulingTeams ST (Nolock) ON spl.TeamID = ST.schedulingTeamId
	INNER JOIN Allocations as al (NOLOCK) on al.AL_SchedulingTeamID = spl.TeamID 
	LEFT JOIN AllocationsScheduledPersons asp on AL_AllocationsID = ASP_AllocationsID
												AND sp.UD_UserID = ASP_SchedulingPersonID
												AND asp.ASP_iDay = @iday
	LEFT JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
	WHERE sp.UD_UserID = @scheduledpersonid 
	  AND al.AL_WeekNumber = @intweekno
	  AND al.AL_Status <> 9
	  AND spl.TeamID IN ( Select usr.UR_SchedulingTeamID
							FROM  UserRoles as usr (Nolock)
							INNER JOIN schedulingTeams as st (nolock) on usr.UR_SchedulingTeamID = st.schedulingTeamId
							where UR_UserID = @userId 
							  AND UR_RoleID IN (3,4,5)
							  and CAST(getdate() as date) between UR_StartDate and UR_EndDate
							  AND st.isActive=1
						 )
      AND spl.scheduledType=1 
	  AND spl.IsHomeTeam=1 
	  AND CAST(getdate() as DATE) between spl.StartDate and isnull(spl.EndDate,'9999-01-01')
END