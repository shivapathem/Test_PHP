USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_requestweeklyAllocationAndRotawithmasking]    Script Date: 07/08/2025 20:05:36 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_fetch_requestweeklyAllocationAndRotawithmasking]
@intweekStart          VARCHAR(50),
@intweekEnd            VARCHAR(50),
@startDate             VARCHAR(50),
@endDate			   VARCHAR(50),
@pNetLogin             VARCHAR(30),
@isLeavePage           INT = NULL
	
AS
BEGIN

	SET NOCOUNT ON;	
	DECLARE @IsFreelancer         BIT = 0
	DECLARE @FreelancerSPID       INT
	DECLARE @isRequester          VARCHAR(10) = '1'
	 
	SELECT DISTINCT @FreelancerSPID = STL.scheduledpersonid
	FROM UserDetails SP (NOLOCK)
	 INNER JOIN ScheduledPersonTeam_LINK AS STL (NOLOCK) ON SP.UD_UserID = STL.scheduledpersonid
	 INNER JOIN schedulingTeams ST (NOLOCK) ON ST.schedulingTeamId = STL.TeamID
	 WHERE ST.schedulingTeamName IN ('Freelancers','Other BBC')
	   AND SP.UD_NetLogin = @pNetLogin
	   AND STL.IsHomeTeam = 1
	   AND STL.scheduledType = 1
	   
	 IF ( ISNULL(@FreelancerSPID,0) > 0 )
	  BEGIN
	    SET @IsFreelancer = 1
	  END	 
				SELECT 
					       a.WeekNumber,
						   LA.ID as reqID,
						   sp.UD_NetLogin NetLogin,
						   spl.TeamID as StaffTeamID,
						   CASE WHEN ISNULL(a.DutyTeamID,0) > 0 AND a.DutyTeamID <> a.SchedulingTeamId THEN '-'
						    ELSE
						     case when ISNULL(@isRequester,0) = 1 then 
					           case when a.dutydate > getdate()+isnull(st.maskafter,999) 
							         and isnull(st.masktype,2) = 0 
									 and (a.starttime > 0 or a.endtime > 0 ) then ''
									when a.dutydate > getdate()+isnull(st.maskafter,999) 
							         and isnull(st.masktype,2) = 0  
									 and a.DutyName = 'U' then ''
							        when a.dutydate > getdate()+isnull(st.maskafter,999) 
									 and isnull(st.masktype,2) = 1 
									 and (a.starttime > 0 or a.endtime > 0 ) then substring(a.DutyName,1,1)
						           else a.DutyName end 								
							 else a.DutyName end 
						   END as DutyName,
						   a.SchedulingPersonID,
						   a.iDay as DOTW,
						   a.SchedulingTeamId,
						   spl.IsHomeTeam,
						   case when a.adhocduty = 1 then 0 else 1 end as display_priority
						   INTO #TempAllocations
			          FROM dbo.Allocations_publish as a (NOLOCK)
		             INNER JOIN Timedimension TD (NOLOCK) on a.WeekNumber=TD.ixYearWeek and a.iDay = TD.ixDayInWeek	
					 INNER JOIN Requests LA (NOLOCK) on LA.dDate = a.DutyDate and LA.ScheduledPersonID = a.SchedulingPersonID
						AND LA.dDate >= CONVERT(DATETIME, @startDate, 102) AND  LA.dDate < CONVERT(DATETIME, @endDate, 102) AND LA.Deleted=0		  
			         INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = LA.ScheduledPersonID
			         INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on a.SchedulingPersonID = spl.ScheduledPersonID
					                        AND spl.teamid = A.SchedulingTeamId
					INNER JOIN schedulingTeams AS ST (NOLOCK)  ON A.SchedulingTeamId = ST.SchedulingTeamId
					  --LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
			          INNER JOIN (select min(td.dDateTime) startdate 
					               from TimeDimension td (NOLOCK) 
								   where td.ixYearWeek = @intweekStart ) AS TD1 ON 1=1
			         WHERE 
					    TD.ixYearWeek BETWEEN @intweekStart AND @intweekEnd
					   AND td.dDateTime >= ISNULL(spl.StartDate,td.dDateTime) 
					   AND td.dDateTime <= ISNULL(spl.EndDate,td.dDateTime)
					   AND 1 = CASE WHEN @IsFreelancer = 1 
					                 AND spl.ScheduledPersonID <> @FreelancerSPID 
									 AND a.DutyDate < CAST(getdate() - 1 AS DATE) THEN 2
								    WHEN @IsFreelancer = 1 
									 AND spl.ScheduledPersonID <> @FreelancerSPID 
									 AND a.DutyDate > getdate()+ISNULL(st.freelancerMaskingDays,999) THEN 2
							   ELSE 1 END													   
				       AND 1 = CASE WHEN @isRequester = '1' THEN
					             CASE WHEN td1.startdate > getdate()+isnull(st.maskafter,999) 
				                       AND ISNULL(st.masktype,2) <> 2 
								      THEN 2 
								 ELSE 1 END
						       ELSE 1 END
													   
            INSERT INTO #TempAllocations
			SELECT  td1.ixYearWeek AS WeekNumber,
			LA.ID as reqID,
					sp.UD_NetLogin,
					er.SchedulingTeamId as StaffTeamID,
			        CASE WHEN ISNULL( @isRequester,'0') = '1' 
						  AND ISNULL(sptl.ROTA,0) = 1 THEN ''
			             ELSE er.DutyName END as DutyName,
					er.SchedulingPersonID,
					er.DOTW,
					er.SchedulingTeamId,
					er.IsHomeTeam,
					2 as display_priority					
			FROM Exported_rota as er (NOLOCK)			
			INNER JOIN 
			( 
				SELECT td.ixYearWeek,
				CASE
					when ROW_NUMBER() over(partition by SchedulingPersonID,RotaID 
					order by td.ixYearWeek) % td.WeeksInRota = 0
					THEN td.WeeksInRota
					ELSE ROW_NUMBER() over(partition by SchedulingPersonID,RotaID
					order by td.ixYearWeek) % td.WeeksInRota
				END weeksinrota,SchedulingPersonID, RotaID,
									SchedulingTeamId
				FROM 
				(
					SELECT DISTINCT ixYearWeek,er.WeeksInRota,er.SchedulingPersonID,
					                er.RotaID,
									er.SchedulingTeamId
					FROM TimeDimension td (NOLOCK) ,
					(
						SELECT DISTINCT er1.AssignmentStartWeek,
						                er1.WeeksInRota,
										er1.SchedulingPersonID,
										er1.RotaID,
										er1.SchedulingTeamId
						FROM Exported_rota er1
						INNER JOIN Requests LA ON  LA.ScheduledPersonID = er1.SchedulingPersonID
						WHERE LA.dDate >= CONVERT(DATETIME, @startDate, 102) 
						  AND LA.dDate < CONVERT(DATETIME, @endDate, 102) 
						  AND LA.Deleted=0
						  AND @startDate <= ISNULL( CAST(ER1.RotaAssignmentEndDate AS DATE), @startDate)
			              AND @endDate >= ISNULL(ER1.RotaAssignmentStartDate, @endDate)
                        							
					) er
					WHERE td.ixYearWeek BETWEEN er.AssignmentStartWeek AND @intweekEnd
				) TD
			) TD1 ON td1.weeksinrota = er.rotaweek 
			     and td1.SchedulingPersonID=er.SchedulingPersonID
			     AND td1.RotaID = er.RotaID 
			     AND td1.SchedulingTeamId = er.SchedulingTeamId 				 
			INNER JOIN 
			    (SELECT MIN(tdt.dDateTime) AS startdate, 
				        MAX(tdt.dDateTime ) AS enddate 
				 FROM  TimeDimension tdt (NOLOCK) 
				 WHERE ixYearWeek BETWEEN @intweekStart AND @intweekEnd ) AS td2 ON 1 = 1
			INNER JOIN TimeDimension TD3 ON TD3.ixYearWeek = TD1.ixYearWeek AND TD3.ixDayInWeek = ER.DOTW
			INNER JOIN Requests LA ON LA.dDate =TD3.dDateTime and LA.ScheduledPersonID = er.SchedulingPersonID
				AND LA.dDate >= CONVERT(DATETIME, @startDate, 102) AND  LA.dDate < CONVERT(DATETIME, @endDate, 102)	AND LA.Deleted=0			  
			INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = LA.ScheduledPersonID
			INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl 
			    ON LA.ScheduledPersonID = sptl.ScheduledPersonID AND sptl.TeamID = er.SchedulingTeamId
			  AND ISNULL(sptl.ROTA,0) = CASE WHEN ISNULL(@isRequester,'0') = '1' AND ISNULL(sptl.ROTA,0) =0
			                                  THEN 0
											WHEN ISNULL(@isRequester,'0') = '1' AND ISNULL(sptl.ROTA,0) =2
			                                  THEN 2	
			                                ELSE  -1 END
			 AND sptl.scheduledType = 1 
			 AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime) 
			INNER JOIN schedulingTeams AS ST (NOLOCK)  ON er.SchedulingTeamId = ST.SchedulingTeamId
			--LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID			
			WHERE td1.ixYearWeek BETWEEN @intweekStart AND @intweekEnd
			  and td3.dDateTime between er.RotaAssignmentStartDate and ISNULL( er.RotaAssignmentEndDate, td3.dDateTime)			  
			  AND 1 = CASE WHEN @IsFreelancer = 1 
			                AND	sptl.ScheduledPersonID <> @FreelancerSPID
						    AND td3.dDateTime < CAST(getdate() - 1 AS DATE) THEN 2
						   WHEN @IsFreelancer = 1 
			                AND	sptl.ScheduledPersonID <> @FreelancerSPID 
							AND td3.dDateTime > getdate() + ISNULL(st.freelancerMaskingDays,999) THEN 2
						 ELSE 1 END														   
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

		UPDATE #TempAllocations SET display_priority = 1 WHERE display_priority = 0

		SELECT * 
		   FROM #TempAllocations 
		  ORDER BY  WeekNumber, DOTW			  

END