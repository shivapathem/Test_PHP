USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_ReadAllocationsStaffing]    Script Date: 25/08/2025 22:45:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 11-Apr, 2022
-- Description:	
-- exec usp_ReadAllocationsStaffing 202215, 134
-- =============================================
CREATE OR ALTER   PROCEDURE [dbo].[usp_ReadAllocationsStaffing] 
	@intStartWeekNumber INT,
	@intTeamId INT
AS
BEGIN
	SET NOCOUNT ON;
	IF(@intTeamId = 0)
	BEGIN
		SELECT U.UD_StaffNumber AS StaffID, U.UD_DisplayName AS FullName, schedulingTeams.schedulingTeamId AS StaffDepartmentID, U.UD_StaffNumber AS StaffNumber, U.UD_StaffNumber AS StaffNumber, U.UD_NetLogin AS NetLogin, AD.AD_DutyBreakTime AS DutyBreakTime, ScheduledPersonTeam_LINK.SortCode,
			AL.AL_WeekNumber AS WeekNumber, AD.AD_iDay AS iDay, AD.AD_AllocationsDutyID  AS DutyID, AL.AL_SchedulingTeamID AS AllocationDepartmentID, 
			schedulingTeams.schedulingTeamName AS AllocationDepartmentName, 0 AS iscopy, 
			AD.AD_IsEditedDutyAttention AS isedited, AD.AD_DutyName AS DutyName, AD.AD_StartTimeSec AS StartTime, AD.AD_EndTimeSec AS EndTime, 
			AD.AD_Duration AS Duration, CAST(ScheduledPersonTeam_LINK.BackgroundColour AS nvarchar) AS AllocBackColour, 
			CAST(ScheduledPersonTeam_LINK.FontColour AS nvarchar) AS AllocFontColour, CASE WHEN AD.AD_Comments IS NULL 
			THEN 0 ELSE DataLength(AD.AD_Comments) END AS DutyComments, CASE WHEN ASP.ASP_Comments IS NULL THEN 0 ELSE DataLength(ASP.ASP_Comments) 
			END AS PersonComments, ScheduledPersonTeam_LINK.fontcolour AS StaffTextColour, ScheduledPersonTeam_LINK.BackgroundColour as StaffColour 
		FROM   Allocations as AL inner join AllocationsScheduledPersons as ASP  ON AL.AL_AllocationsID=ASP.ASP_AllocationsID
		INNER JOIN AllocationsDuties AD ON AL.AL_AllocationsID =AD.AD_AllocationsID AND ASP.ASP_AllocationsDutyID=AD.AD_AllocationsDutyID
		INNER JOIN ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ASP.ASP_SchedulingPersonID  
		AND ScheduledPersonTeam_LINK.IsHomeTeam = 1 AND AL.AL_SchedulingTeamID = ScheduledPersonTeam_LINK.TeamID
		INNER JOIN schedulingTeams ON ScheduledPersonTeam_LINK.TeamID = schedulingTeams.schedulingTeamId 
	    LEFT OUTER JOIN UserDetails U ON ASP.ASP_SchedulingPersonID=U.UD_UserID
		WHERE (AL.AL_WeekNumber = @intStartWeekNumber) AND AD.AD_Duration <> 0 
		AND AD.AD_DutyDate between isNull(ScheduledPersonTeam_LINK.StartDate, AD.AD_DutyDate)  AND isNull(ScheduledPersonTeam_LINK.EndDate, AD.AD_DutyDate)
		AND ScheduledPersonTeam_LINK.scheduledType = 1
		AND (AD.AD_DutyName LIKE N'[a-zA-z][ ]%' OR AD.AD_DutyName  LIKE N'[a-zA-z][8]%')
	END
	ELSE
	BEGIN
		SELECT  U.UD_StaffNumber AS StaffID, U.UD_DisplayName AS FullName, schedulingTeams.schedulingTeamId AS StaffDepartmentID, U.UD_StaffNumber AS StaffNumber, U.UD_NetLogin AS NetLogin, AD.AD_DutyBreakTime AS DutyBreakTime, ScheduledPersonTeam_LINK.SortCode, 
			AL.AL_WeekNumber AS WeekNumber, AD.AD_iDay AS iDay, AD.AD_AllocationsDutyID  AS DutyID, AL.AL_SchedulingTeamID AS AllocationDepartmentID, 
			schedulingTeams.schedulingTeamName AS AllocationDepartmentName, 0 AS iscopy, 
			AD.AD_IsEditedDutyAttention AS isedited, AD.AD_DutyName AS DutyName, AD.AD_StartTimeSec AS StartTime, AD.AD_EndTimeSec AS EndTime, 
			AD.AD_Duration AS Duration, CAST(ScheduledPersonTeam_LINK.BackgroundColour AS nvarchar) AS AllocBackColour, 
			CAST(ScheduledPersonTeam_LINK.FontColour AS nvarchar) AS AllocFontColour, CASE WHEN AD.AD_Comments IS NULL 
			THEN 0 ELSE DataLength(AD.AD_Comments) END AS DutyComments, CASE WHEN ASP.ASP_Comments IS NULL THEN 0 ELSE DataLength(ASP.ASP_Comments) 
			END AS PersonComments, ScheduledPersonTeam_LINK.fontcolour AS StaffTextColour, ScheduledPersonTeam_LINK.BackgroundColour as StaffColour 
		FROM   Allocations as AL inner join AllocationsScheduledPersons as ASP  ON AL.AL_AllocationsID=ASP.ASP_AllocationsID
		INNER JOIN AllocationsDuties AD ON AL.AL_AllocationsID =AD.AD_AllocationsID AND ASP.ASP_AllocationsDutyID=AD.AD_AllocationsDutyID
		INNER JOIN ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ASP.ASP_SchedulingPersonID  
		AND ScheduledPersonTeam_LINK.IsHomeTeam = 1 AND AL.AL_SchedulingTeamID = ScheduledPersonTeam_LINK.TeamID
		INNER JOIN schedulingTeams ON ScheduledPersonTeam_LINK.TeamID = schedulingTeams.schedulingTeamId 
	    LEFT OUTER JOIN UserDetails U ON ASP.ASP_SchedulingPersonID=U.UD_UserID 
		WHERE (AL.AL_WeekNumber >= @intStartWeekNumber) AND AD.AD_Duration <> 0 
		AND AD.AD_DutyDate between isNull(ScheduledPersonTeam_LINK.StartDate, AD.AD_DutyDate)  AND isNull(ScheduledPersonTeam_LINK.EndDate,AD.AD_DutyDate)
		AND ScheduledPersonTeam_LINK.scheduledType = 1 AND schedulingTeams.schedulingTeamId = @intTeamId 
		AND (AD.AD_DutyName LIKE N'[a-zA-z][ ]%' OR AD.AD_DutyName LIKE N'[a-zA-z][8]%')
	END
END