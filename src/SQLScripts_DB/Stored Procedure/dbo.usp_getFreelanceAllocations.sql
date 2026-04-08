SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 11-Apr, 2022
-- Description:	
-- exec usp_getFreelanceAllocations 202215, 0
-- =============================================
CREATE OR ALTER PROCEDURE usp_getFreelanceAllocations 
	@intWeekNumber INT,
	@intTeamId INT
AS
BEGIN
	SET NOCOUNT ON;
	IF(@intTeamId = 0)
	BEGIN
		SELECT  StaffDetails.StaffID, ScheduledPeople.DisplayName AS FullName, schedulingTeams.schedulingTeamId AS DepartmentID, StaffDetails.StaffNumber, StaffDetails.NetLogin, ScheduledPersonTeam_LINK.SortCode, Allocations.dutyBreakTime,
				Allocations.WeekNumber, Allocations.iDay, Allocations.ID AS DutyID, Allocations.DepartmentID AS AllocationDepartmentID, 
				schedulingTeams.schedulingTeamName AS AllocationDepartmentName,  1 AS iscopy, Allocations.isEdited, schedulingTeams.schedulingTeamDescription,
				Allocations.DutyName, Allocations.StartTime, Allocations.EndTime, Allocations.Duration, CAST(Allocations.BackColour AS nvarchar) AS AllocBackColour, ScheduledPeople.PersonalPhone, ScheduledPeople.PersonalEmail,
				CAST(Allocations.FontColour AS nvarchar) AS AllocFontColour, 
				CASE WHEN Allocations.DutyComments IS NULL THEN 0 ELSE DataLength(Allocations.DutyComments) END AS DutyComments, 
				CASE WHEN Allocations.PersonComments IS NULL THEN 0 ELSE DataLength(Allocations.PersonComments) END AS PersonComments,
				ScheduledPersonTeam_LINK.fontcolour AS StaffTextColour, ScheduledPersonTeam_LINK.BackgroundColour as StaffColour
		FROM   Allocations 
		inner Join ScheduledPeople ON Allocations.SchedulingPersonID = ScheduledPeople.ScheduledPersonID
		left outer join StaffDetails On ScheduledPeople.StaffDetailsID = StaffDetails.StaffID
		Inner Join ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID AND ScheduledPersonTeam_LINK.IsHomeTeam = 1 AND Allocations.SchedulingTeamId = ScheduledPersonTeam_LINK.TeamID
		inner join schedulingTeams ON ScheduledPersonTeam_LINK.TeamID = schedulingTeams.schedulingTeamId AND schedulingTeamName = 'Freelancers' 
		WHERE (Allocations.WeekNumber = @intWeekNumber) AND Allocations.Duration <> 0 
				AND Allocations.DutyDate between isNull(ScheduledPersonTeam_LINK.StartDate, Allocations.DutyDate)  AND isNull(ScheduledPersonTeam_LINK.EndDate, Allocations.DutyDate)
				AND ScheduledPersonTeam_LINK.scheduledType = 1 AND (Allocations.SchedulingTeamId > 0)
		ORDER BY schedulingTeamName, StaffDetails.Surname, StaffDetails.Forename
	END
	ELSE
	BEGIN
		SELECT  StaffDetails.StaffID, ScheduledPeople.DisplayName AS FullName, schedulingTeams.schedulingTeamId AS DepartmentID, StaffDetails.StaffNumber, StaffDetails.NetLogin, ScheduledPersonTeam_LINK.SortCode, Allocations.dutyBreakTime,
				Allocations.WeekNumber, Allocations.iDay, Allocations.ID AS DutyID, Allocations.DepartmentID AS AllocationDepartmentID, 
				schedulingTeams.schedulingTeamName AS AllocationDepartmentName,  1 AS iscopy, Allocations.isEdited, schedulingTeams.schedulingTeamDescription,
				Allocations.DutyName, Allocations.StartTime, Allocations.EndTime, Allocations.Duration, CAST(Allocations.BackColour AS nvarchar) AS AllocBackColour, ScheduledPeople.PersonalPhone, ScheduledPeople.PersonalEmail,
				CAST(Allocations.FontColour AS nvarchar) AS AllocFontColour, 
				CASE WHEN Allocations.DutyComments IS NULL THEN 0 ELSE DataLength(Allocations.DutyComments) END AS DutyComments, 
				CASE WHEN Allocations.PersonComments IS NULL THEN 0 ELSE DataLength(Allocations.PersonComments) END AS PersonComments,
				ScheduledPersonTeam_LINK.fontcolour AS StaffTextColour, ScheduledPersonTeam_LINK.BackgroundColour as StaffColour
		FROM   Allocations 
		inner Join ScheduledPeople ON Allocations.SchedulingPersonID = ScheduledPeople.ScheduledPersonID
		left outer join StaffDetails On ScheduledPeople.StaffDetailsID = StaffDetails.StaffID
		Inner Join ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID AND ScheduledPersonTeam_LINK.IsHomeTeam = 1 AND Allocations.SchedulingTeamId = ScheduledPersonTeam_LINK.TeamID
		inner join schedulingTeams ON ScheduledPersonTeam_LINK.TeamID = schedulingTeams.schedulingTeamId AND schedulingTeamName = 'Freelancers' 
		WHERE (Allocations.WeekNumber = @intWeekNumber) AND Allocations.Duration <> 0 
				AND Allocations.DutyDate between isNull(ScheduledPersonTeam_LINK.StartDate, Allocations.DutyDate)  AND isNull(ScheduledPersonTeam_LINK.EndDate, Allocations.DutyDate)
				AND ScheduledPersonTeam_LINK.scheduledType = 1 AND (Allocations.SchedulingTeamId = @intTeamId)
		ORDER BY schedulingTeamName, StaffDetails.Surname, StaffDetails.Forename
	END
END
GO
