USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_StaffDetailsByDayAndTeam]    Script Date: 10/07/2025 13:18:37 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER       PROCEDURE [dbo].[usp_get_StaffDetailsByDayAndTeam]
@intWeek          INT,
@intDay           INT,
@intTeamID        INT,
@selectedDate     DATE,
@IsShiftleader    INT = 2

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	   IF ( ISNULL(@IsShiftleader,0) in (0,1) )
	    BEGIN
            
			SELECT AllocationId,
				   DutyName,	   
				   SchedulingPersonID,
				   FullName,
				   AllocationsDutyID,
				   AllocationsSPID
			FROM   (SELECT stl.scheduledpersonid			AS SchedulingPersonID,
						   sp.UD_DisplayName				AS FullName,
					       ISNULL(al.dutyname,'U')			AS DutyName,
						   ISNULL(AL.AllocationsID,ALS.AL_AllocationsID)		AS AllocationId,
						   ISNULL(AL.AllocationsDutyID,0)	AS AllocationsDutyID,
						   ISNULL(AL.AllocationsSPID,0)		AS AllocationsSPID,
						   CASE WHEN ( ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.schedulingteamid )
						         AND ISNULL(AL.Duration,0) <> 0
							    THEN 0 
								WHEN ISNULL(AL.MarkedOvertime,0) > 0
								THEN 0
								ELSE 1 END AS ShowPerson,
						  stl.ishometeam,
						  ISNULL( CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.SchedulingTeamId THEN 0 ELSE 1 END,1) AS isteamduty,
						  LeaveStatus
					 FROM ScheduledPersonTeam_LINK AS stl (nolock)
					INNER JOIN Allocations ALS on stl.TeamID = ALS.AL_SchedulingTeamID
					INNER JOIN UserDetails AS sp (nolock)  ON sp.UD_UserID = stl.scheduledpersonid
					 LEFT JOIN  ( 
								 SELECT AD_DutyName AS DutyName,
								        AL_AllocationsID AS AllocationsID,
										AD_AllocationsDutyID AS AllocationsDutyID,
										ASP_AllocationsSPID AS AllocationsSPID,
										ASP_DutyTeamID AS DutyTeamID,
										AL_SchedulingTeamID AS SchedulingTeamId,
										ASP_SchedulingPersonID,
										AD_Duration AS Duration,
										ASP_MarkedOverTime AS MarkedOvertime,
										ISNULL(ASP_LeaveStatus,0) AS  LeaveStatus,
										ASP_LeaveType
								   FROM Allocations AS AL (nolock)						 
								  INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
								  INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
								  WHERE AL_SchedulingTeamID = @intTeamID
								    AND AL_WeekNumber = @intWeek
									AND ASP_iDay = @intDay
							   UNION
								 SELECT AD_DutyName AS DutyName,
								        AL_AllocationsID AS AllocationsID,
										AD_AllocationsDutyID AS AllocationsDutyID,
										ASP_AllocationsSPID AS AllocationsSPID,
										ASP_DutyTeamID AS DutyTeamID,
										AL_SchedulingTeamID AS SchedulingTeamId,
										ASP_SchedulingPersonID,
										AD_Duration AS Duration,
										ASP_MarkedOverTime AS MarkedOvertime,
										ISNULL(ASP_LeaveStatus,0) AS  LeaveStatus,
										ASP_LeaveType
								   FROM Allocations AS AL (nolock)	
								  INNER JOIN AllocationsAddPersons AA on AL_AllocationsID = AAP_AllocationsID 
								  INNER JOIN AllocationsScheduledPersons ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
								  INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID						 
								  WHERE AL_SchedulingTeamID = @intTeamID
								    AND AL_WeekNumber = @intWeek
									AND ASP_iDay = @intDay
							 ) AL ON AL.ASP_SchedulingPersonID = stl.ScheduledPersonID
					WHERE stl.teamid = @intTeamID
					  AND ALS.AL_WeekNumber = @intWeek
					  AND @selectedDate >= stl.startdate 
					  AND @selectedDate <= stl.enddate 		   
					  AND stl.scheduledtype = 1 ) SPList 
					WHERE ShowPerson = 1 
			           AND isteamduty = 1
					   AND ISNULL(LeaveStatus,0) = 0

			END
		  ELSE 
		    BEGIN

			SELECT AllocationId,
				   DutyName,	   
				   SchedulingPersonID,
				   FullName,
				   AllocationsDutyID,
				   AllocationsSPID
			FROM   (SELECT stl.scheduledpersonid			AS SchedulingPersonID,
						   sp.UD_DisplayName				AS FullName,
					       ISNULL(al.dutyname,'U')			AS DutyName,
						   ISNULL(AL.AllocationsID,ALS.AL_AllocationsID)		AS AllocationId,
						   ISNULL(AL.AllocationsDutyID,0)	AS AllocationsDutyID,
						   ISNULL(AL.AllocationsSPID,0)		AS AllocationsSPID,
						   CASE WHEN ( ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.schedulingteamid )
						         AND ISNULL(AL.Duration,0) <> 0
							    THEN 0 
								WHEN ISNULL(AL.MarkedOvertime,0) > 0
								THEN 0
								ELSE 1 END AS ShowPerson,
						  stl.ishometeam,
						  ISNULL( CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.SchedulingTeamId THEN 0 ELSE 1 END,1) AS isteamduty,
						  LeaveStatus
					 FROM ScheduledPersonTeam_LINK AS stl (nolock)
					INNER JOIN Allocations ALS on stl.TeamID = ALS.AL_SchedulingTeamID
					INNER JOIN UserDetails AS sp (nolock)  ON sp.UD_UserID = stl.scheduledpersonid
					 LEFT JOIN  ( 
								 SELECT AD_DutyName AS DutyName,
								        AL_AllocationsID AS AllocationsID,
										AD_AllocationsDutyID AS AllocationsDutyID,
										ASP_AllocationsSPID AS AllocationsSPID,
										ASP_DutyTeamID AS DutyTeamID,
										AL_SchedulingTeamID AS SchedulingTeamId,
										ASP_SchedulingPersonID,
										AD_Duration AS Duration,
										ASP_MarkedOverTime AS MarkedOvertime,
										ISNULL(ASP_LeaveStatus,0) AS  LeaveStatus,
										ASP_LeaveType
								   FROM Allocations AS AL (nolock)						 
								  INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
								  INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
								  WHERE AL_SchedulingTeamID = @intTeamID
								    AND AL_WeekNumber = @intWeek
									AND ASP_iDay = @intDay
							   UNION
								 SELECT AD_DutyName AS DutyName,
								        AL_AllocationsID AS AllocationsID,
										AD_AllocationsDutyID AS AllocationsDutyID,
										ASP_AllocationsSPID AS AllocationsSPID,
										ASP_DutyTeamID AS DutyTeamID,
										AL_SchedulingTeamID AS SchedulingTeamId,
										ASP_SchedulingPersonID,
										AD_Duration AS Duration,
										ASP_MarkedOverTime AS MarkedOvertime,
										ISNULL(ASP_LeaveStatus,0) AS  LeaveStatus,
										ASP_LeaveType
								   FROM Allocations AS AL (nolock)	
								  INNER JOIN AllocationsAddPersons AA on AL_AllocationsID = AAP_AllocationsID 
								  INNER JOIN AllocationsScheduledPersons ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
								  INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID						 
								  WHERE AL_SchedulingTeamID = @intTeamID
								    AND AL_WeekNumber = @intWeek
									AND ASP_iDay = @intDay
							 ) AL ON AL.ASP_SchedulingPersonID = stl.ScheduledPersonID
					WHERE stl.teamid = @intTeamID
					  AND @selectedDate >= stl.startdate 
					  AND @selectedDate <= stl.enddate 		   
					  AND stl.scheduledtype = 1 
					  AND ALS.AL_WeekNumber = @intWeek
					  AND ( stl.ishometeam = 1
							OR ( stl.ishometeam = 0
								AND stl.isavailable = 1 )
							)				  
			) SPList WHERE ShowPerson = 1 
			           AND isteamduty = 1
					   AND ISNULL(LeaveStatus,0) = 0

			END
  END