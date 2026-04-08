USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_AllocationAndRota_leave]    Script Date: 26/12/2025 22:00:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                    PROCEDURE [dbo].[usp_fetch_AllocationAndRota_leave]
@intweekStart          INT,
@intweekEnd            INT,
@intschedulingPersonId INT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	
	DECLARE @WeekStart          INT
	DECLARE @WeekEnd            INT
	
	
	SET @WeekStart = CAST(@intweekStart AS INT)
	SET @WeekEnd = CAST(@intweekEnd  AS INT)
	
	

	SELECT WeekNumber,
		   SchedulingTeamId, 
		   MasterDutyID, 
		   DutyName,
		   SchedulingPersonID, 
		   DOTW, 
		   Duration, 
		   StartTime,
		   EndTime,
		   ID,
		   MarkedOvertime,
		   display_priority,
           ROTA,
		   ManualOThours,
		   ChargingId,
		   MarkedSickness,
		   dutyBreakTime,
		   LeaveStartTime,
		   LeaveEndTime,
		   IsPartDayLeaveApplied,
		   LeaveID,
		   IsLeaveApproved,
		   IsPartDayAllowed
	  INTO #TempAllocations
	FROM
	   (
		SELECT  AL_WeekNumber              AS WeekNumber,
				SPL.TeamID				AS SchedulingTeamId,
				AD_MasterDutyID           AS MasterDutyID, 
				CASE WHEN AD_DutyType IN  (8,11,12)
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
			        ELSE ISNULL(AD_DutyName,'U') END AS DutyName,
				SPL.ScheduledPersonID     AS SchedulingPersonID, 
				TD.ixDayInWeek                   AS DOTW, 
				CASE WHEN AD_DutyType IN  (8,11,12)
					 THEN ASP_LeaveDuration
					 ELSE AD_Duration   END            AS Duration, 
				AD_StartTimeSec              AS StartTime,
				AD_EndTimeSec                as EndTime,
				AL_AllocationsID                     AS ID,
				ASP_MarkedOverTime         AS MarkedOvertime,
				case when AD_DutyType = 6 then 0 else 1 end AS display_priority,
				spl.ROTA                   AS ROTA,
				ISNULL(ASP_OverTimeHours,0) AS ManualOThours,
				C.ChargingId               AS ChargingId,
				CASE WHEN ASP_LeaveType IN (3,4,5) THEN 1 ELSE 0 END AS MarkedSickness,
				AD_DutyBreakTime            AS dutyBreakTime,
				ISNULL(la.LeaveStartTime,0)          AS LeaveStartTime,
				ISNULL(la.LeaveEndTime,0)            AS LeaveEndTime,
				CASE WHEN (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0 ) 
				     THEN 1 ELSE 0 END As IsPartDayLeaveApplied,
				LA.ID                      as LeaveID,
				LA.Approved                As IsLeaveApproved,
				CASE WHEN ( (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0  ) 				      
					  AND LA.Approved = 0 ) 
				     THEN 1 
				     WHEN ( (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0 ) 
					  AND LA.Approved = 1 ) 
				     THEN 2
					 WHEN ISNULL(AD_StartTimeSec,0) > 0 OR ISNULL(AD_EndTimeSec,0) > 0   
				     THEN 1
					 ELSE 0 
					 END AS IsPartDayAllowed
		   FROM dbo.Allocations as a (NOLOCK)	
		  INNER JOIN TimeDimension TD ON TD.ixYearWeek = a.AL_WeekNumber
		  INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as SPL on spl.teamid = AL_SchedulingTeamID
		  INNER JOIN LeaveApplications LA on TD.dDateTime = LA.dDate and LA.SchedulingPersonID = spl.ScheduledPersonID
		  LEFT JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP.ASP_AllocationsID
												   AND TD.ixDayInWeek = ASP.ASP_iDay
												   AND SPL.ScheduledPersonID = ASP.ASP_SchedulingPersonID
		  LEFT JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID		  
		   LEFT JOIN ChargingDutyMapping_Link C (NOLOCK) ON C.AllocationId = ASP_AllocationsSPID
		  WHERE TD.dDateTime BETWEEN spl.StartDate AND spl.EndDate
		    AND SPL.scheduledType = 1
			AND SPL.IsHomeTeam = 1
			AND ISNULL(LA.LeaveTypeID,0) NOT IN ( 3,4,5)
		    and TD.ixYearWeek BETWEEN @WeekStart AND  @WeekEnd
			AND spl.ScheduledPersonID = @intschedulingPersonId
	 ) AL

    INSERT INTO #TempAllocations
	SELECT td.ixYearWeek             AS WeekNumber,
		   ad.TeamId       AS SchedulingTeamId, 
		   0                         AS MasterDutyID, 
		   ad.DutyName               AS DutyName, 
		   ad.ScheduledPersonID                AS SchedulingPersonID, 
		   TD.ixDayInWeek            AS DOTW,
		   case when isnull(AD.duration,0) = 0 then 
                case when ad.endtime > ad.starttime then ad.endtime- ad.starttime
                   when ad.endtime < ad.starttime then (86400-ad.starttime)+ad.endtime end
              else  ad.duration end as Duration,
		   AD.StartTime, 
		   AD.EndTime,
		   ad.MasterDutyID           AS ID,
		   0                         AS MarkedOvertime, 
		   0                         AS display_priority,
		   sptl.ROTA                 AS ROTA,
		   0                         AS ManualOThours,
		   0						 AS ChargingId,
		   0                         AS MarkedSickness,
		   ad.BreakTime              AS dutyBreakTime,
		   ISNULL(la.LeaveStartTime,0)          AS LeaveStartTime,
		   ISNULL(la.LeaveEndTime,0)            AS LeaveEndTime,
	       CASE WHEN (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0 ) 
				     THEN 1 ELSE 0 END As IsPartDayLeaveApplied,
		   LA.ID                     as LeaveID,
		   LA.Approved               AS IsLeaveApproved,
		   CASE WHEN ( (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0  ) 				      
				 AND LA.Approved = 0 ) 
				THEN 1 
				WHEN ( (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0 ) 
				 AND LA.Approved = 1 ) 
				THEN 2
				WHEN ISNULL(ad.StartTime,0) > 0 OR ISNULL(ad.EndTime,0) > 0   
				THEN 1
				ELSE 0 
			END AS IsPartDayAllowed
      FROM MasterDuties as ad (NOLOCK)      		  
	 INNER JOIN TimeDimension TD (NOLOCK) ON TD.dDateTime BETWEEN ad.StartDate AND ad.EndDate
	 INNER JOIN LeaveApplications LA (NOLOCK) ON LA.dDate =TD.dDateTime and LA.SchedulingPersonID = AD.ScheduledPersonID
	 INNER JOIN schedulingTeams AS st ON ad.TeamID = st.schedulingTeamId
	 INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl on sptl.ScheduledPersonID  = AD.ScheduledPersonID
			           AND sptl.TeamID = ad.TeamId
		               AND td.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td.dDateTime) 
	 WHERE ad.ScheduledPersonID = @intschedulingPersonId
       AND TD.ixYearWeek BETWEEN @WeekStart AND @WeekEnd
       AND SPTL.scheduledType = 1
       AND ST.isActive = 1
	   AND ad.DutyTypeID = 6
	   AND ISNULL(LA.LeaveTypeID,0) NOT IN ( 3,4,5)
	   AND NOT EXISTS ( SELECT 1
						  FROM #TempAllocations TLS
						 WHERE TLS.SchedulingTeamId = AD.TeamId
						   AND TLS.SchedulingPersonID = AD.ScheduledPersonID
						   AND TLS.DOTW = td.ixDayInWeek
						   AND TLS.WeekNumber = TD.ixYearWeek 
					  )


    INSERT INTO #TempAllocations
	SELECT td1.ixYearWeek            AS WeekNumber,
		   er.SchedulingTeamId       AS SchedulingTeamId, 
		   er.MasterDutyID, 
		   md.DutyName, 
		   er.SchedulingPersonID, 
		   er.DOTW,
		   case when isnull(MD.duration,0) = 0 then 
                case when md.endtime > md.starttime then md.endtime- md.starttime
                   when md.endtime < md.starttime then (86400-md.starttime)+md.endtime end
              else  md.duration end as Duration,
		   md.StartTime, 
		   md.EndTime,
		   er.ID,
		   0                         AS MarkedOvertime, 
		   2                         AS display_priority,
		   sptl.ROTA                 AS ROTA,
		   0                         AS ManualOThours,
		   0						 AS ChargingId,
		   0                         AS MarkedSickness,
		   md.BreakTime              AS dutyBreakTime,
		   ISNULL(la.LeaveStartTime,0)          AS LeaveStartTime,
		   ISNULL(la.LeaveEndTime,0)            AS LeaveEndTime,
	       CASE WHEN (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0 ) 
				     THEN 1 ELSE 0 END As IsPartDayLeaveApplied,
		   LA.ID                     as LeaveID,
		   LA.Approved               AS IsLeaveApproved,
		   CASE WHEN ( (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0  ) 				      
				 AND LA.Approved = 0 ) 
				THEN 1 
				WHEN ( (ISNULL(la.LeaveStartTime,0) > 0 OR ISNULL(la.LeaveEndTime,0) > 0 ) 
				 AND LA.Approved = 1 ) 
				THEN 2
				WHEN ISNULL(md.StartTime,0) > 0 OR ISNULL(md.EndTime,0) > 0   
				THEN 1
				ELSE 0 
			END AS IsPartDayAllowed
      FROM Exported_rota as er (NOLOCK)      
      INNER JOIN ( SELECT td.ixYearWeek, SchedulingPersonID, RotaID, SchedulingTeamId,
		           CASE WHEN ROW_NUMBER() over(partition by SchedulingPersonID, RotaID
				   ORDER by td.ixYearWeek) % td.WeeksInRota = 0
					THEN td.WeeksInRota
					ELSE ROW_NUMBER() over(partition by SchedulingPersonID, RotaID
					               order by td.ixYearWeek) % td.WeeksInRota 
								   END weeksinrota
					FROM (
						  SELECT DISTINCT ixYearWeek,
						         er.WeeksInRota,
								 SchedulingPersonID,
								 RotaID,
								 SchedulingTeamId
						    FROM TimeDimension td, 
							    (
								  
								SELECT DISTINCT AssignmentStartWeek,
								                WeeksInRota,
												SchedulingPersonID,
												RotaID,
												SchedulingTeamId
								FROM Exported_rota (NOLOCK) ER1
								INNER JOIN 
									(SELECT MIN(TDI.dDateTime) AS startdate, 
											MAX(TDI.dDateTime ) AS enddate 
									 FROM  TimeDimension TDI (NOLOCK) 
									 WHERE ixYearWeek BETWEEN @WeekStart AND @WeekEnd
									 ) AS TDI1 ON 1 = 1 
								 WHERE SchedulingPersonID = @intschedulingPersonId
								  AND TDI1.startdate <= ISNULL( CAST(ER1.RotaAssignmentEndDate AS DATE), TDI1.startdate)
								  AND TDI1.enddate >= ISNULL(ER1.RotaAssignmentStartDate, TDI1.enddate)								  
								  
								 ) er
					WHERE td.ixYearWeek BETWEEN er.AssignmentStartWeek AND @intweekEnd) TD) TD1
		      ON td1.weeksinrota = er.rotaweek 
		      AND td1.SchedulingPersonID=er.SchedulingPersonID
			   AND td1.RotaID = er.RotaID 
			   AND td1.SchedulingTeamId = er.SchedulingTeamId 			  
		INNER JOIN schedulingTeams AS st ON er.SchedulingTeamId = st.schedulingTeamId
		INNER JOIN (SELECT MIN(tdt.dDateTime) AS startdate, 
		                   MAX(tdt.dDateTime ) AS enddate 
					  FROM TimeDimension tdt 
					 WHERE ixYearWeek BETWEEN @WeekStart AND @WeekEnd 
					 ) td2 ON 1 = 1
		INNER JOIN TimeDimension TD3 (NOLOCK) ON TD3.ixYearWeek = TD1.ixYearWeek AND TD3.ixDayInWeek = ER.DOTW			 
		INNER JOIN LeaveApplications LA (NOLOCK) ON LA.dDate =TD3.dDateTime and LA.SchedulingPersonID = er.SchedulingPersonID
		INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl on sptl.ScheduledPersonID  = er.SchedulingPersonID
		       AND sptl.TeamID = er.SchedulingTeamId
		       AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime) 
		INNER JOIN MasterDuties md (NOLOCK) on md.MasterDutyID = er.MasterDutyID
		WHERE er.SchedulingPersonID = @intschedulingPersonId
          AND td1.ixYearWeek BETWEEN @WeekStart AND @WeekEnd
		  AND st.isActive=1
		  AND ISNULL(LA.LeaveTypeID,0) NOT IN ( 3,4,5)
		  and td3.dDateTime between er.RotaAssignmentStartDate and ISNULL( er.RotaAssignmentEndDate, td3.dDateTime)
		  AND td1.ixYearWeek NOT IN ( SELECT distinct WeekNumber 
										FROM #TempAllocations
									   WHERE display_priority <> 0)
		  AND NOT EXISTS ( SELECT 1
							 FROM #TempAllocations TLS
							WHERE TLS.SchedulingTeamId = ER.SchedulingTeamId
							  AND TLS.SchedulingPersonID = ER.SchedulingPersonID
							  AND TLS.DOTW = ER.DOTW
							  AND TLS.WeekNumber = TD1.ixYearWeek 
							  AND TLS.display_priority = 0)



		  
		 SELECT * 
		   FROM #TempAllocations 
		  ORDER BY  WeekNumber, DOTW
		
 END