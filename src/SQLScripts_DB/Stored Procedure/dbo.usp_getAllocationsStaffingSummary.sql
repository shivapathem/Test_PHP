SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 11-Apr, 2022
-- Description:	This SP is used to get allocation staffing summary
-- exec usp_getAllocationsStaffingSummary 202215, 202219, 0
-- =============================================
CREATE OR ALTER PROCEDURE usp_getAllocationsStaffingSummary 
	@intStartWeekNumber INT,
	@intEndWeekNumber INT,
	@intTeamId INT
AS
BEGIN
	SET NOCOUNT ON;
	IF(@intTeamId = 0)
	BEGIN
		SELECT ScheduledPeople.StaffDetailsID AS StaffId , ScheduledPersonTeam_LINK.TeamID AS StaffDepartmentID, StaffDetails.StaffNumber, Allocations.WeekNumber, Allocations.iDay, 
						Allocations.SchedulingTeamId AS AllocationDepartmentID, Allocations.Duration AS Duration, Allocations.dutyBreakTime 
		FROM Allocations
		INNER JOIN schedulingTeams ON schedulingTeams.schedulingTeamId = Allocations.SchedulingTeamId
		INNER JOIN ScheduledPeople ON ScheduledPeople.ScheduledPersonID = Allocations.SchedulingPersonID
		INNER JOIN ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID AND 
				   isNull(ScheduledPersonTeam_LINK.EndDate, 9999-12-31) >= getdate()
		INNER JOIN StaffDetails ON  StaffDetails.StaffID = ScheduledPeople.StaffDetailsID
		WHERE      (Allocations.WeekNumber >= @intStartWeekNumber) AND (Allocations.WeekNumber <= @intEndWeekNumber) AND Allocations.Duration <> 0
					AND ( Allocations.DutyName LIKE N'[a-zA-z][ ]%' OR Allocations.DutyName LIKE N'[a-zA-z][8]%' ) 
		ORDER BY Allocations.WeekNumber DESC, Allocations.iDay
	END
	ELSE
	BEGIN
		SELECT ScheduledPeople.StaffDetailsID AS StaffId , ScheduledPersonTeam_LINK.TeamID AS StaffDepartmentID, StaffDetails.StaffNumber, Allocations.WeekNumber, Allocations.iDay, 
						Allocations.SchedulingTeamId AS AllocationDepartmentID, Allocations.Duration AS Duration, Allocations.dutyBreakTime  
		FROM Allocations
		INNER JOIN schedulingTeams ON schedulingTeams.schedulingTeamId = Allocations.SchedulingTeamId
		INNER JOIN ScheduledPeople ON ScheduledPeople.ScheduledPersonID = Allocations.SchedulingPersonID
		INNER JOIN ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID AND 
				   isNull(ScheduledPersonTeam_LINK.EndDate, 9999-12-31) >= getdate()
		INNER JOIN StaffDetails ON  StaffDetails.StaffID = ScheduledPeople.StaffDetailsID
		WHERE      (Allocations.WeekNumber >= @intStartWeekNumber) AND (Allocations.WeekNumber <= @intEndWeekNumber) AND Allocations.Duration <> 0 AND (Allocations.SchedulingTeamId = @intTeamId)
					AND ( Allocations.DutyName LIKE N'[a-zA-z][ ]%' OR Allocations.DutyName LIKE N'[a-zA-z][8]%') 
		ORDER BY Allocations.WeekNumber DESC, Allocations.iDay
	END
END
GO
