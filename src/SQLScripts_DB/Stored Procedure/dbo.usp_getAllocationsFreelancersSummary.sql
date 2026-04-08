SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 11-Apr,2022
-- Description:	This SP is used to resturn allocations summary for freelancer user
-- exec usp_getAllocationsFreelancersSummary 202215, 202219, 134
-- =============================================
CREATE OR ALTER PROCEDURE usp_getAllocationsFreelancersSummary
	@intStartWeekNumber INT,
	@intEndWeekNumber INT,
	@intTeamId INT
AS
BEGIN
	SET NOCOUNT ON;
	IF(@intTeamId > 0)
	BEGIN
		SELECT ScheduledPersonTeam_LINK.TeamID AS StaffDepartmentID, StaffDetails.StaffNumber, Allocations.WeekNumber, Allocations.iDay, Allocations.SchedulingTeamId AS AllocationDepartmentID, Allocations.Duration, Allocations.dutyBreakTime
		FROM   Allocations 
		inner Join ScheduledPeople ON Allocations.SchedulingPersonID = ScheduledPeople.ScheduledPersonID
		left outer join StaffDetails On ScheduledPeople.StaffDetailsID = StaffDetails.StaffID
		Inner Join ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID 
			AND ScheduledPersonTeam_LINK.IsHomeTeam = 1 AND Allocations.SchedulingTeamId = ScheduledPersonTeam_LINK.TeamID
		inner join schedulingTeams ON ScheduledPersonTeam_LINK.TeamID = schedulingTeams.schedulingTeamId AND schedulingTeamName = 'Freelancers' 
		WHERE  (Allocations.WeekNumber >= @intStartWeekNumber) AND (Allocations.WeekNumber <= @intEndWeekNumber) AND (Allocations.Duration <> 0) AND (ScheduledPersonTeam_LINK.TeamID = @intTeamId)
		ORDER BY Allocations.WeekNumber DESC, Allocations.iDay
	END
	ELSE
	BEGIN
		SELECT ScheduledPersonTeam_LINK.TeamID AS StaffDepartmentID, StaffDetails.StaffNumber, Allocations.WeekNumber, Allocations.iDay, Allocations.SchedulingTeamId AS AllocationDepartmentID, Allocations.Duration, Allocations.dutyBreakTime
		FROM   Allocations 
		inner Join ScheduledPeople ON Allocations.SchedulingPersonID = ScheduledPeople.ScheduledPersonID
		left outer join StaffDetails On ScheduledPeople.StaffDetailsID = StaffDetails.StaffID
		Inner Join ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID 
			AND ScheduledPersonTeam_LINK.IsHomeTeam = 1 AND Allocations.SchedulingTeamId = ScheduledPersonTeam_LINK.TeamID
		inner join schedulingTeams ON ScheduledPersonTeam_LINK.TeamID = schedulingTeams.schedulingTeamId AND schedulingTeamName = 'Freelancers' 
		WHERE  (Allocations.WeekNumber >= @intStartWeekNumber) AND (Allocations.WeekNumber <= @intEndWeekNumber) AND (Allocations.Duration <> 0)
		ORDER BY Allocations.WeekNumber DESC, Allocations.iDay
	END
END
GO
