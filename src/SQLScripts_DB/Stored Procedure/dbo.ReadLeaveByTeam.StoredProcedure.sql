USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[ReadLeaveByTeam]    Script Date: 25/08/2025 13:37:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 25-04-2022
-- Description:	This SP is used to get leave details according to team and date
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[ReadLeaveByTeam] 

	@startdate DATE,
	@enddate DATE,
	@teamId INT
AS
BEGIN

	SET NOCOUNT ON;
	SELECT		LeaveApplications.Approved,
				LeaveApplications.Sent,
				LeaveApplications.unlikely, 
				LeaveApplications.oversummer,
				LeaveApplications.ShortNotice, 
				LeaveApplications.isOK,
				UserDetails.UD_DisplayName AS FullName,
				leave_types.ID AS LeaveTypeID, 
				leave_types.description AS LeaveType, 
				LeaveRequestGroups.ID AS LeaveGroupID, 
				LeaveRequestGroups.Description AS LeaveGroup
		FROM LeaveApplications
		INNER JOIN    leave_types ON LeaveApplications.LeaveTypesID = leave_types.ID 
		INNER JOIN    LeaveRequestGroups ON leave_types.GroupID = LeaveRequestGroups.ID
		INNER JOIN	  UserDetails ON UserDetails.UD_UserID = LeaveApplications.SchedulingPersonID
		INNER JOIN    ScheduledPersonTeam_LINK AS stl ON UserDetails.UD_UserID = stl.ScheduledPersonID
		WHERE         (LeaveApplications.Deleted = 0) AND (LeaveApplications.dDate >= CONVERT(DATETIME, @startdate, 102)) 
        AND           (LeaveApplications.dDate <= CONVERT(DATETIME, @enddate, 102)) 
        AND           (stl.TeamID = @teamId)    
		AND	isnull(CAST(stl.EndDate AS DATE),@startdate)>=@startdate
		AND cast (stl.StartDate AS DATE) <= @enddate
		AND stl.IsHomeTeam = 1
        Order by       LeaveRequestGroups.Description, leave_types.description
END