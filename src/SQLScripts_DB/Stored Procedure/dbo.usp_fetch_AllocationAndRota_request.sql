USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_AllocationAndRota_request]    Script Date: 13/08/2025 14:51:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_fetch_AllocationAndRota_request]
@intweekStart          INT,
@intweekEnd            INT,
@intAdmin INT,
@strScheduledPerson varchar(max)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	
	DECLARE @WeekStart          INT
	DECLARE @WeekEnd            INT
	DECLARE @TempAllocations TABLE (WeekNumber INT,
									SchedulingTeamId INT,
									MasterDutyID INT,
									DutyName nvarchar(100),
									SchedulingPersonID INT,
									DOTW INT,
									Duration INT,
									StartTime INT,
									EndTime INT,
									ID INT,
									MarkedOvertime INT,
									display_priority INT,
									ROTA varchar(5),
									ManualOThours INT,
									MarkedSickness INT,
									dutyBreakTime INT
									)
	
	
	SET @WeekStart = CAST(@intweekStart AS INT)
	SET @WeekEnd = CAST(@intweekEnd  AS INT)
	
	IF(@intAdmin=1)
		BEGIN
		INSERT INTO @TempAllocations
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
			   MarkedSickness,
			   dutyBreakTime
		FROM
		(
				SELECT a.AL_WeekNumber   AS WeekNumber,
				case when ISNULL(asp.ASP_DutyTeamID,0) > 0 AND asp.ASP_DutyTeamID <> a.AL_SchedulingTeamID then asp.ASP_DutyTeamID
				else spl.TeamID end      AS SchedulingTeamId,
				ad.AD_MasterDutyID           AS MasterDutyID, 
				ad.AD_DutyName     AS DutyName,
				asp.ASP_SchedulingPersonID     AS SchedulingPersonID, 
				asp.ASP_iDay                   AS DOTW, 
				ad.AD_Duration            AS Duration, 
				ad.AD_StartTimeSec     AS StartTime,
				ad.AD_EndTimeSec  as EndTime,
				ad.AD_AllocationsID                  AS ID,
				asp.ASP_MarkedOverTime         AS MarkedOvertime,
				1 as display_priority,
				--case when ap.AdhocDuty = 1 then 0 else 1 end AS display_priority,
				rank() over (partition by a.AL_WeekNumber,ad.AD_iDay order by spl.ishomeTeam desc) AS rankcol,
				spl.ROTA                   AS ROTA,
				0 AS ManualOThours,
				0 AS MarkedSickness,
				ad.AD_DutyBreakTime  AS dutyBreakTime
		  FROM dbo.Allocations as a (NOLOCK)	    
		  INNER JOIN ScheduledPersonTeam_LINK as spl (NOLOCK) on  spl.teamid=A.AL_SchedulingTeamID
		  --inner join allocations_publish ap(nolock) on ap.SchedulingTeamId=spl.ScheduledPersonID and a.AL_SchedulingTeamID=ap.SchedulingTeamId 
		  inner join AllocationsScheduledPersons asp(nolock) on asp.ASP_AllocationsID=a.AL_AllocationsID and spl.ScheduledPersonID=asp.ASP_SchedulingPersonID
		  inner join AllocationsDuties ad(nolock) on ad.AD_AllocationsDutyID=asp.ASP_AllocationsDutyID
		  INNER JOIN Requests LA (NOLOCK) on LA.dDate = ad.AD_DutyDate and LA.ScheduledPersonID = asp.ASP_SchedulingPersonID AND LA.Deleted=0
		  WHERE asp.ASP_SchedulingPersonID in ( select value FROM string_split(@strScheduledPerson,',') )
		  AND (a.AL_WeekNumber between @WeekStart AND @WeekEnd) And ad.AD_DutyDate >= ISNULL(spl.StartDate,ad.AD_DutyDate) 
		   AND ad.AD_DutyDate <= ISNULL(spl.EndDate,ad.AD_DutyDate)
			) FD where rankcol = 1    
		  END
		  ELSE
			BEGIN
			INSERT INTO @TempAllocations
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
			   MarkedSickness,
			   dutyBreakTime
	FROM
	(
			SELECT a.WeekNumber              AS WeekNumber,
			case when ISNULL(a.DutyTeamID,0) > 0 AND a.DutyTeamID <> a.SchedulingTeamId then a.DutyTeamID
			else spl.TeamID end      AS SchedulingTeamId,
			a.MasterDutyID  AS MasterDutyID, 
			a.DutyName     AS DutyName,
			a.SchedulingPersonID     AS SchedulingPersonID, 
			a.iDay   AS DOTW, 
			a.Duration   AS Duration, 
		    a.StartTime     AS StartTime,
		    a.EndTime  as EndTime,
			a.ID    AS ID,
			a.MarkedOvertime  AS MarkedOvertime,
			case when a.adhocduty = 1 then 0 else 1 end AS display_priority,
			rank() over (partition by a.weeknumber,a.iday order by a.ishometeam desc) AS rankcol,
		    spl.ROTA   AS ROTA,
			ISNULL(a.MannualOThours,0) AS ManualOThours,
			a.MarkedSickness AS MarkedSickness,
			a.dutyBreakTime   AS dutyBreakTime
		  FROM dbo.Allocations_Publish as a (NOLOCK)	   	  
		  INNER JOIN ScheduledPersonTeam_LINK as spl (NOLOCK) on spl.ScheduledPersonID  = a.SchedulingPersonID AND spl.teamid=A.SchedulingTeamId
		  INNER JOIN Requests LA (NOLOCK) on LA.dDate = a.DutyDate and LA.ScheduledPersonID = a.SchedulingPersonID AND LA.Deleted=0
		  WHERE a.SchedulingPersonID in ( select value FROM string_split(@strScheduledPerson,',') )
		   AND (a.WeekNumber BETWEEN @WeekStart AND  @WeekEnd)
		   AND a.DutyDate >= ISNULL(spl.StartDate,a.DutyDate) 
		   AND a.DutyDate <= ISNULL(spl.EndDate,a.DutyDate)
		) FD where rankcol = 1
	  END

    INSERT INTO @TempAllocations
	SELECT td1.ixYearWeek           AS WeekNumber,
		   er.SchedulingTeamId       AS SchedulingTeamId, 
		   er.MasterDutyID, 
		   er.DutyName, 
		   er.SchedulingPersonID, 
		   er.DOTW,
		   er.Duration, 
		   er.StartTime, 
		   er.EndTime,
		   er.ID,
			0                         AS MarkedOvertime, 
		   2                         AS display_priority,
		   sptl.ROTA                 AS ROTA,
		   0                         AS ManualOThours,
		   0                        AS MarkedSickness,
		   0                         AS dutyBreakTime
      FROM Exported_rota as er (NOLOCK)   
      INNER JOIN ( SELECT td.ixYearWeek, SchedulingPersonID, 
								 RotaID,
								 SchedulingTeamId,
		           CASE WHEN ROW_NUMBER() over(partition by SchedulingPersonID,
				                                            RotaID
				   ORDER by td.ixYearWeek) % td.WeeksInRota = 0
					THEN td.WeeksInRota
					ELSE ROW_NUMBER() over(partition by SchedulingPersonID,
					                                    RotaID
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
									 WHERE ixYearWeek BETWEEN @WeekStart AND @WeekEnd ) AS TDI1 ON 1 = 1 
								 WHERE ER1.SchedulingPersonID in ( select value FROM string_split(@strScheduledPerson,',') )
								  AND TDI1.startdate <= ISNULL( CAST(ER1.RotaAssignmentEndDate AS DATE), TDI1.startdate)
								  AND TDI1.enddate >= ISNULL(ER1.RotaAssignmentStartDate, TDI1.enddate)								  
								  
								 ) er
					WHERE td.ixYearWeek BETWEEN er.AssignmentStartWeek AND @intweekEnd) TD) TD1
		        ON td1.weeksinrota = er.rotaweek 
			   AND td1.SchedulingPersonID=er.SchedulingPersonID
			   AND td1.RotaID = er.RotaID 
			   AND td1.SchedulingTeamId = er.SchedulingTeamId 
		INNER JOIN schedulingTeams AS st (NOLOCK) ON er.SchedulingTeamId = st.schedulingTeamId
		INNER JOIN (SELECT MIN(tdt.dDateTime) AS startdate, 
		                   MAX(tdt.dDateTime ) AS enddate 
					  FROM TimeDimension tdt (NOLOCK)
					 WHERE ixYearWeek BETWEEN @WeekStart AND @WeekEnd ) td2 ON 1 = 1
		INNER JOIN TimeDimension TD3 (NOLOCK) ON TD3.ixYearWeek = TD1.ixYearWeek AND TD3.ixDayInWeek = ER.DOTW			 
		INNER JOIN Requests LA (NOLOCK) ON LA.dDate =TD3.dDateTime and LA.ScheduledPersonID = er.SchedulingPersonID AND LA.deleted=0
		INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl on sptl.ScheduledPersonID  = er.SchedulingPersonID
		       AND sptl.TeamID = er.SchedulingTeamId
		       AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime) 
		WHERE er.SchedulingPersonID in ( select value FROM string_split(@strScheduledPerson,',') )
          AND td1.ixYearWeek BETWEEN @WeekStart AND @WeekEnd
		  and td3.dDateTime between er.RotaAssignmentStartDate and ISNULL( er.RotaAssignmentEndDate, td3.dDateTime)
		  AND td1.ixYearWeek NOT IN ( SELECT distinct WeekNumber 
										FROM @TempAllocations
									   WHERE display_priority <> 0)
		  AND NOT EXISTS ( SELECT 1
							 FROM @TempAllocations TLS
							WHERE TLS.SchedulingTeamId = ER.SchedulingTeamId
							  AND TLS.SchedulingPersonID = ER.SchedulingPersonID
							  AND TLS.DOTW = ER.DOTW
							  AND TLS.WeekNumber = TD1.ixYearWeek 
							  AND TLS.display_priority = 0)
		  
		 SELECT * 
		   FROM @TempAllocations 
		  ORDER BY  WeekNumber, DOTW
		
 END