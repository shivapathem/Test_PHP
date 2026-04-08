USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadIndividualAllocationsDay]    Script Date: 18/12/2025 17:45:42 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER              PROCEDURE [dbo].[usp_get_ReadIndividualAllocationsDay]
@WeekNumber            INT,
@iDay                  INT,
@SchedulingPersonId    INT,
@isshiftleader         INT,
@pNetLogin             VARCHAR(30)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	
	SET NOCOUNT ON;
	
	DECLARE @vWeekNumber          INT
	DECLARE @viDay                INT
	DECLARE @IsFreelancer         BIT = 0
	DECLARE @FreelancerSPID       INT 		
	
	
	SET @vWeekNumber        = CAST(@WeekNumber       AS INT)
	SET @viDay              = CAST(@iDay             AS INT)
	
	SELECT DISTINCT @FreelancerSPID = SP.UD_UserID
	  FROM UserDetails SP
	 INNER JOIN ScheduledPersonTeam_LINK AS STL ON SP.UD_UserID = STL.scheduledpersonid
	 INNER JOIN schedulingTeams ST ON ST.schedulingTeamId = STL.TeamID
	 WHERE ST.schedulingTeamName IN ('Freelancers','Other BBC')
	   AND UD_NetLogin = @pNetLogin
	   AND STL.IsHomeTeam = 1
	   AND STL.scheduledType = 1
	   
	 IF ( ISNULL(@FreelancerSPID,0) > 0 AND @SchedulingPersonId <> @FreelancerSPID )
	  BEGIN
	    SET @IsFreelancer = 1
	  END		  	 

	SELECT * 
	  INTO #TempAllocations
	  FROM
	(
		SELECT  UD_DisplayName			  AS FullName, 
				UD_UserID				  AS SchedulingPersonID,
				UD_StaffNumber            AS StaffNumber,
				ISNULL(a.SortCode,ISNULL(spl1.SortCode,
				            spl.SortCode)) AS SortCode, 
				case when ISNULL(a.DutyTeamID,0) > 0 then a.DutyTeamID
				else ISNULL(a.schedulingTeamId, spl.TeamID)
				end                       AS schedulingTeamId,
				ISNULL(spl1.fontcolour,
				       spl.fontcolour)    AS StaffTextColour,
				a.ID                      AS DutyID, 
				a.AllocationID, 
				ISNULL(a.IsHomeTeam,spl.IsHomeTeam)      AS IsHomeTeam,
				0                         AS iscopy, 
				0                         AS isedited,
				ISNULL(a.dutyname,'U')    AS DutyName,
				a.StartTime               AS StartTime,
				a.StartDate               AS DutyStartDate,
				a.EndTime                 AS EndTime,
				a.Duration                AS Duration,  
				0                         AS InternalEdited,  
				0                         AS editable,   
				0                         AS Edited, 
				CASE WHEN (a.dutyColorId IS NULL) THEN '' 
				 ELSE dc.ColourBackground 
				END                       AS AllocBackColour, 
				CASE WHEN (a.dutyColorId IS NULL) THEN '' 
				 ELSE dc.ColourFont 
				 END                      AS AllocFontColour,
				 a.DutyComments           AS DutyComments,
				 a.PersonComments         AS PersonComments,
				 aj.ID                    AS JobID, 
				 a.dutyColorId,
				 aj.StartTime             AS JobStartTime,
				 aj.EndTime               AS JobEndTime,
				 aj.JobName               AS JobName, 
				 aj.Programme             AS Programme, 
				 aj.StaffNumber           AS JobStaffNumber,
				 0                        AS JobEdited,
				 CAST(aj.JobBackColour AS nvarchar) AS JobBackColour, 
				 CAST(aj.JobFontColour AS nvarchar) AS JobFontColour,
				 aj.job_info+' '+aj.Comments        AS JobComments,
				 aj.aftermidnight                  AS aftermidnight,
				 rank() over (partition by TD.ixYearWeek,TD.ixDayInWeek order by (case when ISNULL(a.dutyname,'U') <> 'U' then 1 else 0 end) desc, 
								ISNULL(a.ishometeam,spl.ishometeam) desc) AS rankcol
			FROM vAllocationsPublishedWeeks AL
		   INNER JOIN Timedimension TD on AL.AL_WeekNumber = TD.ixYearWeek 			   
		   INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl ON spl.TeamID = AL.AL_SchedulingTeamID
		   INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = spl.ScheduledPersonID
		   INNER JOIN schedulingTeams AS st ON  st.schedulingTeamId = spl.TeamID
			LEFT JOIN Allocations_publish as a (NOLOCK) ON AL_SchedulingTeamID = a.SchedulingTeamId
														AND UD_UserID = a.SchedulingPersonID
														AND AL_WeekNumber = a.WeekNumber
														AND TD.ixDayInWeek = a.iDay
		    LEFT JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl1 ON a.SchedulingPersonID = spl1.ScheduledPersonID
															   AND a.DutyTeamID		   = spl1.TeamID
															   AND a.DutyDate between spl1.StartDate and spl1.EndDate
			LEFT JOIN Allocations_Jobs_Publish aj ON a.ID = aj.AllocationID
			LEFT JOIN REF_MasterDutyColours AS dc ON dc.MasterDutyColourID = a.dutyColorId
		   WHERE sp.UD_UserID  = @SchedulingPersonId 
			 AND TD.ixYearWeek         = @vWeekNumber 
			 AND TD.ixDayInWeek        = @viDay
			 AND spl.scheduledType = 1
			 AND AL.AL_Status = 1
			 AND TD.dDateTime BETWEEN spl.StartDate AND spl.EndDate
		     AND 1 = CASE WHEN @IsFreelancer = 1 AND a.DutyDate < CAST(getdate() - 1 AS DATE) THEN 2
					      WHEN @IsFreelancer = 1 AND a.DutyDate > getdate()+ isnull(st.freelancerMaskingDays,999) THEN 2
		             ELSE 1 END								
	         AND 1 = CASE WHEN @isshiftleader = 1 THEN
						  CASE WHEN a.DutyDate > getdate()+isnull(st.maskafter,999)
								and ISNULL(st.masktype,2) = 0
							   THEN 2 
						       WHEN a.DutyDate > getdate()+ isnull(st.maskafter,999)
								AND ( a.StartTime > 0 OR a.EndTime > 0) 
				                AND isnull(st.masktype,2) = 1
							   THEN 2 
					           WHEN a.DutyDate > getdate()+ isnull(st.maskafter,999)
					            AND a.DutyName='U' 
								AND isnull(st.masktype,2) <> 2 
							   THEN 2 
 						  ELSE 1 END
			         ELSE 1 END				 
          ) FD WHERE rankcol = 1

		INSERT INTO #TempAllocations
		SELECT  UD_DisplayName   AS FullName, 
				MD.ScheduledPersonID,
				UD_StaffNumber            AS StaffNumber,
				SPTL.SortCode, 
				MD.TeamId       AS schedulingTeamId,
				sptl.fontcolour           AS StaffTextColour,
				0                         AS DutyID, 
				0                         AS AllocationID, 
				SPTL.IsHomeTeam,
				0                         AS iscopy, 
				0                         AS isedited,
				MD.DutyName               AS DutyName,
				MD.StartTime              AS StartTime,
				td2.StartDate             AS DutyStartDate,
				MD.EndTime                AS EndTime,
				MD.Duration               AS Duration,  
				0                         AS InternalEdited,  
				0                         AS editable,   
				0                         AS Edited, 
				CASE WHEN (MD.dutyColourId IS NULL) THEN '' 
				 ELSE dc.ColourBackground 
				END                       AS AllocBackColour, 
				CASE WHEN (MD.dutyColourId IS NULL) THEN '' 
				 ELSE dc.ColourFont 
				 END                      AS AllocFontColour,
				 NULL                     AS DutyComments,
				 NULL                     AS PersonComments,
				 MJ.MasterJobID           AS JobID, 
				 MD.dutyColourId,
				 case when MJ.starttime >= 86400 
				   then (MJ.starttime - 86400) 
				  else MJ.starttime end   as JobStartTime,
				 case when MJ.endtime >= 86400 
				   then (MJ.endtime - 86400) 
				  else MJ.endtime end     as JobEndTime,
				 MJ.JobName               AS JobName, 
				 PG.Programme             AS Programme, 
				 NULL                     AS JobStaffNumber,
				 0                        AS JobEdited,
				 CAST(JC.ColourBackground AS nvarchar) AS JobBackColour, 
				 CAST(JC.ColourFont AS nvarchar)       AS JobFontColour,
				 NULL                     AS JobComments,
				 NULL                     AS aftermidnight,
				 0                        AS rankcol				 
		     FROM MasterDuties as MD (NOLOCK)
		    INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = MD.ScheduledPersonID
			INNER JOIN schedulingTeams AS st ON MD.TeamId = st.schedulingTeamId
			INNER JOIN TimeDimension TD3 (NOLOCK) ON TD3.dDateTime BETWEEN MD.StartDate AND MD.EndDate
			INNER JOIN (SELECT MIN(tdt.dDateTime) AS startdate, 
							   MAX(tdt.dDateTime ) AS enddate 
						  FROM TimeDimension tdt 
						 WHERE ixYearWeek  = @vWeekNumber 
						   AND ixDayInWeek = @viDay ) td2 ON 1 = 1
			INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl on sp.UD_UserID = sptl.ScheduledPersonID 
					AND sptl.TeamID = MD.TeamId
					AND ISNULL(sptl.ROTA,0) = CASE WHEN ISNULL(@isShiftLeader, 0) = 0 then 
													   ISNULL(sptl.ROTA,0) ELSE 0 END
					AND sptl.scheduledType = 1 
					AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime)
			LEFT JOIN REF_MasterDutyColours AS dc ON dc.MasterDutyColourID = MD.DutyColourID
			LEFT JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on mdmj.MasterDutyID = MD.MasterDutyID			
			LEFT JOIN MasterJobs MJ (NOLOCK) ON MJ.MasterJobID = mdmj.MasterJobID
			LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
			LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
			LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
			WHERE MD.ScheduledPersonID = @SchedulingPersonId
			  AND TD3.ixYearWeek = @vWeekNumber
			  AND TD3.ixDayInWeek = @viDay
			  AND MD.DutyTypeID = 6
			  AND MD.isPublished = 1
			  AND 1 = CASE WHEN @IsFreelancer = 1 AND td2.startdate < CAST(getdate() - 1 AS DATE) THEN 2
						   WHEN @IsFreelancer = 1 AND td2.startdate > getdate() + ISNULL(st.freelancerMaskingDays,999) THEN 2
						 ELSE 1 END			  
			  AND NOT EXISTS ( SELECT 1
								 FROM #TempAllocations 
							 )	

    INSERT INTO #TempAllocations
	SELECT  UD_DisplayName   AS FullName, 
				er.SchedulingPersonID,
				UD_StaffNumber            AS StaffNumber,
				er.SortCode, 
				er.schedulingTeamId       AS schedulingTeamId,
				sptl.fontcolour           AS StaffTextColour,
				0                         AS DutyID, 
				0                         AS AllocationID, 
				er.IsHomeTeam,
				0                         AS iscopy, 
				0                         AS isedited,
				er.DutyName               AS DutyName,
				er.StartTime              AS StartTime,
				td2.StartDate             AS DutyStartDate,
				er.EndTime                AS EndTime,
				er.Duration               AS Duration,  
				0                         AS InternalEdited,  
				0                         AS editable,   
				0                         AS Edited, 
				CASE WHEN (er.dutyColourId IS NULL) THEN '' 
				 ELSE dc.ColourBackground 
				END                       AS AllocBackColour, 
				CASE WHEN (er.dutyColourId IS NULL) THEN '' 
				 ELSE dc.ColourFont 
				 END                      AS AllocFontColour,
				 NULL                     AS DutyComments,
				 NULL                     AS PersonComments,
				 MJ.MasterJobID           AS JobID, 
				 er.dutyColourId,
				 case when MJ.starttime >= 86400 
				   then (MJ.starttime - 86400) 
				  else MJ.starttime end   as JobStartTime,
				 case when MJ.endtime >= 86400 
				   then (MJ.endtime - 86400) 
				  else MJ.endtime end     as JobEndTime,
				 MJ.JobName               AS JobName, 
				 PG.Programme             AS Programme, 
				 NULL                     AS JobStaffNumber,
				 0                        AS JobEdited,
				 CAST(JC.ColourBackground AS nvarchar) AS JobBackColour, 
				 CAST(JC.ColourFont AS nvarchar)       AS JobFontColour,
				 NULL                     AS JobComments,
				 NULL                     AS aftermidnight,
				 0                        AS rankcol				 
		    FROM Exported_rota as er (NOLOCK)
		   INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = er.SchedulingPersonID
		   INNER JOIN ( SELECT td.ixYearWeek, SchedulingPersonID, RotaID,
								 SchedulingTeamId,
		           CASE WHEN ROW_NUMBER() over(partition by SchedulingPersonID,  RotaID
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
									(SELECT TDI.dDateTime AS startdate 
									 FROM  TimeDimension TDI (NOLOCK) 
									 WHERE ixYearWeek = @vWeekNumber
   									   AND ixDayInWeek = @viDay ) AS TDI1 ON 1 = 1 
								 WHERE SchedulingPersonID = @SchedulingPersonId
								  AND startdate  between  ISNULL(ER1.RotaAssignmentStartDate, startdate)
 								  AND ISNULL( CAST(ER1.RotaAssignmentEndDate AS DATE), startdate)							  
								  
								 ) er
					WHERE td.ixYearWeek BETWEEN er.AssignmentStartWeek AND @vWeekNumber) TD) TD1
		      ON td1.weeksinrota = er.rotaweek 
		      AND td1.SchedulingPersonID=er.SchedulingPersonID
			   AND td1.RotaID = er.RotaID 
			   AND td1.SchedulingTeamId = er.SchedulingTeamId 			  
		INNER JOIN schedulingTeams AS st ON er.SchedulingTeamId = st.schedulingTeamId
		INNER JOIN TimeDimension TD3 (NOLOCK) ON TD3.ixYearWeek = TD1.ixYearWeek AND TD3.ixDayInWeek = ER.DOTW
		INNER JOIN (SELECT MIN(tdt.dDateTime) AS startdate, 
		                   MAX(tdt.dDateTime ) AS enddate 
					  FROM TimeDimension tdt 
					 WHERE ixYearWeek  = @vWeekNumber 
					   AND ixDayInWeek = @viDay ) td2 ON 1 = 1
		INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl on sp.UD_UserID = sptl.ScheduledPersonID 
		        AND sptl.TeamID = er.SchedulingTeamId
		        AND ISNULL(sptl.ROTA,0) = CASE WHEN ISNULL(@isShiftLeader, 0) = 0 then 
			                                       ISNULL(sptl.ROTA,0) ELSE 0 END
				AND sptl.scheduledType = 1 
				AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime)
		LEFT JOIN REF_MasterDutyColours AS dc ON dc.MasterDutyColourID = er.DutyColourID
		LEFT JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on mdmj.MasterDutyID = ER.MasterDutyID			
		LEFT JOIN MasterJobs MJ (NOLOCK) ON MJ.MasterJobID = mdmj.MasterJobID
		LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
		LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
		LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
        WHERE er.SchedulingPersonID = @SchedulingPersonId
          AND td1.ixYearWeek = @vWeekNumber
		  AND ER.DOTW = @viDay
		  and td3.dDateTime between er.RotaAssignmentStartDate and ISNULL( er.RotaAssignmentEndDate, td3.dDateTime)
		  AND 1 = CASE WHEN @IsFreelancer = 1 AND td2.startdate < CAST(getdate() - 1 AS DATE) THEN 2
					   WHEN @IsFreelancer = 1 AND td2.startdate > getdate() + ISNULL(st.freelancerMaskingDays,999) THEN 2
		             ELSE 1 END			  
		  AND NOT EXISTS ( SELECT 1
							 FROM #TempAllocations 
						 )		  
		  
		 SELECT * 
		   FROM #TempAllocations 
		  ORDER BY StartTime

   END