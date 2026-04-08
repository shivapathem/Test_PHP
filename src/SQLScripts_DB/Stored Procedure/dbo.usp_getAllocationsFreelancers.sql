USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_getAllocationsFreelancers]    Script Date: 03/04/2026 13:50:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 11-Apr,2022
-- Description:
-- exec usp_getAllocationsFreelancers 202215, 0
-- =============================================
CREATE OR ALTER               PROCEDURE [dbo].[usp_getAllocationsFreelancers]
	@intStartWeekNumber INT =  0,
	@intTeamId INT,
	@UserID INT,
	@AreaID  INT=NULL,
	@StartDate DATE = NULL,
	@EndDate DATE = NULL
AS
BEGIN

	SET NOCOUNT ON;
    SET DATEFORMAT YMD;

	IF(@intTeamId = 0 AND @AreaID IS NULL)
	  BEGIN

		SELECT  ISNULL(UD_TeampayStaffID,UD_UserID)  Staffid,
				UD_DisplayName               AS FullName,
				ST.Schedulingteamid          AS StaffDepartmentID,
				UD_StaffNumber Staffnumber,
				UD_NetLogin  Netlogin,
				SL.Sortcode,
				AD_DutyBreakTime Dutybreaktime,
				AL.AL_WeekNumber Weeknumber,
				AD_iDay Iday,
				AD_AllocationsDutyID                            AS DutyID,
				0                  AS AllocationDepartmentID,
				STF.Schedulingteamname        AS AllocationDepartmentName,
				1                                         AS iscopy,
				AD_IsDutyEdited  Isedited,
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
			    END AS Dutyname,
				AD_StartTimeSec Starttime,
				AD_EndTimeSec Endtime,
				CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) - isnull(AD_DutyBreakTime,0) END AS Duration,
				Cast(NULL AS NVARCHAR)  AS AllocBackColour,
				Cast(NULL AS NVARCHAR)  AS AllocFontColour,
				AD_Comments Dutycomments,
				AP.AAP_Comments PersonComments,
				SL.Fontcolour       AS StaffTextColour,
				SL.Backgroundcolour AS StaffColour,
			    UD_UserID ScheduledPersonID,
				ASP_DutyDate DutyDate
		FROM   Allocations AL
		INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID
		INNER JOIN schedulingTeams STF on STF.schedulingTeamId = ASP.ASP_DutyTeamID	
		INNER JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
		INNER JOIN AllocationsAddPersons AP on AP.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
		INNER JOIN Allocations AAT ON AAT.AL_AllocationsID = AP.AAP_AllocationsID
								  AND AAT.AL_WeekNumber = AL.AL_WeekNumber
								  AND STF.schedulingTeamId = AAT.AL_SchedulingTeamID
		INNER JOIN UserDetails SP ON UD_UserID = ASP_SchedulingPersonID
		INNER JOIN Scheduledpersonteam_link sl ON SL.Scheduledpersonid = UD_UserID
											  AND sl.TeamID = AL.AL_SchedulingTeamID
		INNER JOIN Schedulingteams st ON ST.Schedulingteamid = SL.Teamid									
		--INNER JOIN UserRoles UR on UR.UR_SchedulingTeamID = STF.schedulingTeamId
		--INNER JOIN REF_Roles RR on UR.UR_RoleID = RR.RoleID
		WHERE ( AD_Duration <> 0 OR ASP_LeaveDuration <> 0 )
		AND AD_DutyDate BETWEEN SL.Startdate AND SL.Enddate
		--AND AD_DutyDate BETWEEN UR.UR_StartDate AND UR.UR_EndDate
		AND AL.AL_WeekNumber = @intStartWeekNumber
		AND SL.Scheduledtype = 1
		AND SL.Ishometeam = 1
		AND st.Schedulingteamname = 'Freelancers'
		--AND RR.RoleName in ('Advanced Reports','Basic Reports')
		--AND UR.UR_UserID = @UserID
		AND EXISTS (
			SELECT 1
			FROM UserRoles UR
			INNER JOIN REF_Roles RR
			ON RR.RoleID = UR.UR_RoleID
			WHERE UR.UR_SchedulingTeamID = STF.SchedulingTeamId
			AND UR.UR_UserID = @UserID 
			AND RR.RoleName IN ('Advanced Reports', 'Basic Reports', 'Scheduling Team Admin')
		UNION
			SELECT 1
			FROM UserRoles UR
			INNER JOIN REF_Roles RR
			ON RR.RoleID = UR.UR_RoleID
			INNER join schedulingTeams stf2 on stf2.divisionId = UR.UR_DivisionId
			WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
			AND RR.RoleName = 'Area Admin'
			AND stf2.schedulingTeamId = STF.schedulingTeamId
			AND UR.UR_UserID = @UserID 
		UNION
			SELECT 1
			FROM UserRoles UR
			INNER JOIN REF_Roles RR
			ON RR.RoleID = UR.UR_RoleID
			WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
			AND RR.RoleName = 'System Admin'
			AND UR.UR_UserID = @UserID 
		)
		ORDER  BY   UD_DisplayLastName,
		            AD_DutyName,
					AL.AL_WeekNumber,
					ASP_iDay
	 END
	ELSE IF(@intTeamId = 0 AND @AreaID IS NOT NULL)
	  BEGIN

	  			SELECT  sl.ScheduledPersonID, 
					cast(sl.StartDate as date) startdate, 
					cast(sl.EndDate as date) enddate, 
					Backgroundcolour, Fontcolour,
					Sortcode,Schedulingteamid 
			INTO #TempFreeLancerList
			FROM  Scheduledpersonteam_link sl
			INNER JOIN Schedulingteams st ON SL.Teamid = ST.Schedulingteamid
			WHERE  sl.StartDate <= @EndDate
			AND sl.EndDate >= @StartDate
			AND SL.Scheduledtype = 1
			AND SL.Ishometeam = 1
			AND st.Schedulingteamname = 'Freelancers'

		SELECT  ISNULL(UD_TeampayStaffID,UD_UserID)  Staffid,
				UD_DisplayName               AS FullName,
				Sl.Schedulingteamid          AS StaffDepartmentID,
				UD_StaffNumber Staffnumber,
				UD_NetLogin  Netlogin,
				SL.Sortcode,
				AD_DutyBreakTime Dutybreaktime,
				AL.AL_WeekNumber Weeknumber,
				AD_iDay Iday,
				AD_AllocationsDutyID                            AS DutyID,
				0                  AS AllocationDepartmentID,
				STF.Schedulingteamname        AS AllocationDepartmentName,
				1                                         AS iscopy,
				AD_IsDutyEdited  Isedited,
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
			    END AS Dutyname,
				AD_StartTimeSec Starttime,
				AD_EndTimeSec Endtime,
				CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) - isnull(AD_DutyBreakTime,0) END AS Duration,
				Cast(NULL AS NVARCHAR)  AS AllocBackColour,
				Cast(NULL AS NVARCHAR)  AS AllocFontColour,
				AD_Comments Dutycomments,
				AP.AAP_Comments PersonComments,
				SL.Fontcolour       AS StaffTextColour,
				SL.Backgroundcolour AS StaffColour,
			    UD_UserID ScheduledPersonID,
				ASP_DutyDate DutyDate,
				DV.DivisionName,
				ec.EstablishCode CostCode,
				1 as IsAreaUnderUser,
				UD_DisplayLastName DisplayLastName
		INTO #TempFreelancers
		FROM   Allocations AL
		INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID
		INNER JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
		INNER JOIN AllocationsAddPersons AP on AP.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
		INNER JOIN Allocations AAT ON AAT.AL_AllocationsID = AP.AAP_AllocationsID
								   AND AAT.AL_SchedulingTeamID  = ASP.ASP_DutyTeamID
								   AND AAT.AL_WeekNumber = AL.AL_WeekNumber
		INNER JOIN UserDetails SP ON UD_UserID = ASP_SchedulingPersonID
		INNER JOIN #TempFreeLancerList sl ON SL.Scheduledpersonid = UD_UserID
		INNER JOIN schedulingTeams STF on STF.schedulingTeamId =  AAT.AL_SchedulingTeamID 
		INNER JOIN Divisions DV on DV.DivisionID = STF.divisionId
		left join EstablishCode EC on ec.EstablishCodeId = stf.establishCodeID
		WHERE  ( AD_Duration <> 0 OR ASP_LeaveDuration <> 0 )
			AND ASP_DutyDate >= SL.Startdate 
			AND ASP_DutyDate <= SL.Enddate
			AND ASP_DutyDate between @StartDate and @EndDate
			AND exists   (
									select da.UR_DivisionId
										from UserRoles da
										INNER join REF_Roles rr on rr.RoleID = da.UR_RoleID
										where UR_UserID = @UserID
										and rr.RoleName = 'Area Reports'
										and da.UR_DivisionId = case when @AreaID = 0 then UR_DivisionId else @AreaID end
										and STF.divisionid = da.UR_DivisionId
										union
									select da.UR_DivisionId
										from UserRoles da
										INNER join REF_Roles rr on rr.RoleID = da.UR_RoleID
										inner join Divisions dv on 1 = 1
										where rr.RoleName = 'System Admin'
										and UR_UserID = @UserID
										and dv.DivisionID = case when @AreaID = 0 then dv.DivisionID else @AreaID end
										and STF.divisionId = dv.DivisionID
									)


        INSERT INTO #TempFreelancers
		SELECT  ISNULL(UD_TeampayStaffID,UD_UserID)  Staffid,
				UD_DisplayName               AS FullName,
				SL.Schedulingteamid          AS StaffDepartmentID,
				UD_StaffNumber Staffnumber,
				UD_NetLogin  Netlogin,
				SL.Sortcode,
				AD_DutyBreakTime Dutybreaktime,
				AL.AL_WeekNumber Weeknumber,
				AD_iDay Iday,
				AD_AllocationsDutyID                            AS DutyID,
				0                  AS AllocationDepartmentID,
				STF.Schedulingteamname        AS AllocationDepartmentName,
				1                                         AS iscopy,
				AD_IsDutyEdited  Isedited,
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
			    END AS Dutyname,
				AD_StartTimeSec Starttime,
				AD_EndTimeSec Endtime,
				CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) - isnull(AD_DutyBreakTime,0) END AS Duration,
				Cast(NULL AS NVARCHAR)  AS AllocBackColour,
				Cast(NULL AS NVARCHAR)  AS AllocFontColour,
				AD_Comments Dutycomments,
				AP.AAP_Comments PersonComments,
				SL.Fontcolour       AS StaffTextColour,
				SL.Backgroundcolour AS StaffColour,
			    UD_UserID ScheduledPersonID,
				ASP_DutyDate DutyDate,
				DV.DivisionName,
				ec.EstablishCode CostCode,
				0 as IsAreaUnderUser,
				UD_DisplayLastName DisplayLastName
		FROM   Allocations AL
		INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID
		INNER JOIN schedulingTeams STF on STF.schedulingTeamId = ASP.ASP_DutyTeamID
		INNER JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
		INNER JOIN AllocationsAddPersons AP on AP.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
		INNER JOIN Allocations AAT ON AAT.AL_AllocationsID = AP.AAP_AllocationsID
								   AND AAT.AL_SchedulingTeamID  = STF.schedulingTeamId
								   AND AAT.AL_WeekNumber = AL.AL_WeekNumber
		INNER JOIN UserDetails SP ON UD_UserID = ASP_SchedulingPersonID
		INNER JOIN #TempFreeLancerList sl ON SL.Scheduledpersonid = UD_UserID
		INNER JOIN Divisions DV on DV.DivisionID = STF.divisionId
		left join EstablishCode EC on ec.EstablishCodeId = stf.establishCodeID
		WHERE  ( AD_Duration <> 0 OR ASP_LeaveDuration <> 0 )
			AND ASP.ASP_DutyDate  >= SL.Startdate 
			AND ASP.ASP_DutyDate <= SL.Enddate
			AND ASP.ASP_DutyDate between @StartDate and @EndDate
			and exists ( select 1 from #TempFreelancers TSP where TSP.ScheduledPersonID = UD_UserID )
			AND not exists (select 1 from #TempFreelancers TF where TF.DutyID = AD_AllocationsDutyID) 

		 SELECT Staffid,
				FullName,
				StaffDepartmentID,
				Staffnumber,
				Netlogin,
				Sortcode,
				Dutybreaktime,
				Weeknumber,
				Iday,
				DutyID,
				AllocationDepartmentID,
				AllocationDepartmentName,
				iscopy,
				Isedited,
				Dutyname,
				Starttime,
				Endtime,
				Duration ,
				AllocBackColour,
				AllocFontColour,
				Dutycomments,
				PersonComments,
				StaffTextColour,
				StaffColour,
			    ScheduledPersonID,
				DutyDate,
				DivisionName,
				CostCode,
				IsAreaUnderUser
		  FROM #TempFreelancers
		ORDER  BY   DisplayLastname,
		            Dutyname,
					Weeknumber,
					Iday


	 END
	ELSE
	 BEGIN

		SELECT  ISNULL(UD_TeampayStaffID,UD_UserID)  Staffid,
				UD_DisplayName               AS FullName,
				ST.Schedulingteamid          AS StaffDepartmentID,
				UD_StaffNumber Staffnumber,
				UD_NetLogin  Netlogin,
				SL.Sortcode,
				AD_DutyBreakTime Dutybreaktime,
				AL.AL_WeekNumber Weeknumber,
				AD_iDay Iday,
				AD_AllocationsDutyID                            AS DutyID,
				0                  AS AllocationDepartmentID,
				STF.Schedulingteamname        AS AllocationDepartmentName,
				1                                         AS iscopy,
				AD_IsDutyEdited  Isedited,
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
			    END AS Dutyname,
				AD_StartTimeSec Starttime,
				AD_EndTimeSec Endtime,
				CASE WHEN AD_DutyType IN (8,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) - isnull(AD_DutyBreakTime,0) END AS Duration,
				Cast(NULL AS NVARCHAR)  AS AllocBackColour,
				Cast(NULL AS NVARCHAR)  AS AllocFontColour,
				AD_Comments Dutycomments,
				AP.AAP_Comments PersonComments,
				SL.Fontcolour       AS StaffTextColour,
				SL.Backgroundcolour AS StaffColour,
			    UD_UserID ScheduledPersonID,
				ASP_DutyDate DutyDate
		FROM   Allocations AL
		INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID
		INNER JOIN schedulingTeams STF on STF.schedulingTeamId = ASP.ASP_DutyTeamID
		INNER JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
		INNER JOIN AllocationsAddPersons AP on AP.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
		INNER JOIN Allocations AAT ON AAT.AL_AllocationsID = AP.AAP_AllocationsID
								AND STF.schedulingTeamId = AAT.AL_SchedulingTeamID
		INNER JOIN UserDetails SP ON UD_UserID = ASP_SchedulingPersonID
		INNER JOIN Scheduledpersonteam_link sl ON SL.Scheduledpersonid = UD_UserID
		INNER JOIN Schedulingteams st ON SL.Teamid = ST.Schedulingteamid
		WHERE  AL.AL_WeekNumber = @intStartWeekNumber
			   AND  ( AD_Duration <> 0 OR ASP_LeaveDuration <> 0 )
			   AND ST.Schedulingteamname = 'Freelancers'
			   AND AD_DutyDate BETWEEN SL.Startdate AND SL.Enddate
			   AND SL.Scheduledtype = 1
			   AND SL.Ishometeam = 1
			   AND STF.schedulingTeamId = @intTeamId
		ORDER  BY   UD_DisplayLastName,
		            AD_DutyName,
					AL.AL_WeekNumber,
					ASP_iDay
	END


END