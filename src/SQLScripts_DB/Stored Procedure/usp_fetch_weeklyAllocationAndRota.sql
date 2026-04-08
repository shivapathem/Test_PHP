USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_weeklyAllocationAndRota]    Script Date: 30/03/2026 14:46:35 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_fetch_weeklyAllocationAndRota]
@intweekStart          INT,
@intweekEnd            INT,
@filterTeamCond        VARCHAR(MAX),
@OrderbyCondition      INT,
@scheduledPersonId     INT,
@pNetLogin             VARCHAR(30),
@isShiftLeader         VARCHAR(50) = NULL
	
AS
BEGIN

	SET NOCOUNT ON;
			
	DECLARE @IsFreelancer              BIT = 0
	DECLARE @FreelancerSPID            INT 	
	DECLARE @NoOfSkills                INT = 0
	DECLARE @NoOfLabels                INT = 0
	DECLARE @IsAndFilter               INT = 0
	DECLARE @FLStartDate DATE, 
	        @FLEndDate DATE,
			@WeekStartDate DATE,
			@WeekEndDate DATE;
	
		   SELECT @WeekStartDate = MIN(dDateTime),  
		          @WeekEndDate = MAX(dDateTime)
		     FROM TimeDimension
			WHERE ixYearWeek between @intweekStart and @intweekEnd

	 
		SELECT @FreelancerSPID = MAX(UD.UD_UserID),
		       @FLStartDate = MIN(STL.startdate),
			   @FLEndDate = MAX(STL.enddate)
	  FROM UserDetails UD (NOLOCK)
	 INNER JOIN ScheduledPersonTeam_LINK AS STL(NOLOCK) ON UD.UD_UserID = STL.scheduledpersonid
	 INNER JOIN schedulingTeams ST (NOLOCK) ON ST.schedulingTeamId = STL.TeamID
	 WHERE ST.schedulingTeamName IN ('Freelancers','Other BBC')
	   AND UD.UD_NetLogin = @pNetLogin
	   AND STL.IsHomeTeam = 1
	   AND STL.scheduledType = 1
	   AND STL.startdate <= @WeekEndDate and STL.enddate >= @WeekStartDate	
	   
	 IF ( ISNULL(@FreelancerSPID,0) > 0 )
	  BEGIN
	    SET @IsFreelancer = 1
	  END	 
     ELSE
	  BEGIN
		   SET @FLStartDate = @WeekStartDate  
		   SET @FLEndDate = @WeekEndDate 
	  END
	 

					SELECT ID,
					       AllocationID,
					       WeekNumber,
		                   StaffID,
						   StaffNumber,
						   DisplayName,
						   SurName,
						   NetLogin,
						   StaffTeamID,
						   Email1,
						   active,
						   inBuilding,
						   SignInStartTime,
						   SignInEndTime,
						   IsTemplate,
						   MasterDutyID,
						   dutyProgramId, 
						   DutyName,
						   SchedulingPersonID,
						   DOTW,
						   Duration,						   
						   StartTime,								   
						   EndTime,	
						   SchedulingTeamId ,
						   IsHomeTeam,
						   dutyColorId,
						   BackColour,
						   FontColour,
						   StaffTextColour,
						   StaffBackColour,
						   SortCode,
						   isEdited,
						   isEditable,
						   display_priority,
						   CostCode,
					       LeaveType,
						   MarkedOvertime,
						   DutyCommentsFlag,
						   PersonCommentsFlag,
						   MannualOThours,
						   LeaveStartTime,
						   LeaveEndTime,
						   schedulingTeamName,
						   jobs,
						   IsPublished,
						   LeaveFontColour,
						   AllocationsDutyID,
						   AllocationsSPID,
						   DisplayInViewScreen
						   INTO #TempAllocations
					FROM
				   (
		            SELECT ISNULL(a.ID,0) AS ID,
					       ISNULL(a.AllocationID,0) AS AllocationID,
					       AL_WeekNumber AS WeekNumber,
		                   0 AS StaffID,
						   UD_StaffNumber AS StaffNumber,
						   UD_DisplayName AS DisplayName,
						   UD_DisplayLastName AS SurName,
						   UD_NetLogin AS	NetLogin,
						   spl.TeamID as StaffTeamID,
						   UD_InternalEmail as Email1,
						   a.SigninStatus as active,
						   a.SigninINBuilding as inBuilding,
						   a.SigninStartTime AS SignInStartTime,
						   a.SigninEndTime AS SignInEndTime,
						   0 As IsTemplate,
						   a.MasterDutyID,
						   a.dutyProgramId, 
						   case when ISNULL(@isshiftleader,0) = 1 then 
					           case when td.dDateTime > getdate()+isnull(st.maskafter,999) 
							         and isnull(st.masktype,2) = 0 then ''
									when td.dDateTime > getdate()+isnull(st.maskafter,999) 
							         and isnull(st.masktype,2) = 0  
									 and ISNULL(a.DutyName,'U') = 'U' then ''
							        when td.dDateTime > getdate()+isnull(st.maskafter,999) 
									 and isnull(st.masktype,2) = 1 
									 and (a.starttime > 0 or a.endtime > 0 ) then substring(ISNULL(a.DutyName,'U'),1,1)
						           else ISNULL(a.DutyName,'U') end 								
							 else ISNULL(a.DutyName,'U')  
						   END    as DutyName,
						   UD_UserID as  SchedulingPersonID,
						   TD.ixDayInWeek as DOTW,
						     case when ISNULL(@isshiftleader,0) = 1 then 
					           case when td.dDateTime > getdate()+isnull(st.maskafter,999) 
									 and isnull(st.masktype,2) = 0 then NULL
									when td.dDateTime > getdate()+isnull(st.maskafter,999) 
									 and isnull(st.masktype,2) = 1 
									 and (a.starttime > 0 or a.endtime > 0 ) then NULL
								ELSE a.Duration END 
							  ELSE a.Duration 
							 END AS Duration,						   
							case when ISNULL(@isshiftleader,0) = 1 
								 and td.dDateTime > getdate()+isnull(st.maskafter,999) 
								 AND ISNULL(st.masktype,2) <> 2 then NULL
							 else a.StartTime  
						   END AS StartTime,								   
							case when ISNULL(@isshiftleader,0) = 1 
								  and td.dDateTime > getdate()+isnull(st.maskafter,999) 
								  AND ISNULL(st.masktype,2) <> 2 then NULL
								else a.EndTime  
						   END as EndTime,	
						   case when isnull(a.DutyTeamID,0) > 0 then a.dutyteamid else a.SchedulingTeamId end as SchedulingTeamId ,
						   spl.IsHomeTeam,
						   a.dutyColorId as dutyColorId,
							CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourBackground, 'EFEFEF') 
								 ELSE 'EFEFEF'  END AS BackColour,
							CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourFont, '330066') 
								 ELSE ISNULL(AT.TextColour, '330066')  END AS FontColour,
						   spl.fontcolour as StaffTextColour,
						   ISNULL(spl.BackgroundColour,'#cccccc') AS StaffBackColour,
						   ISNULL(a.SortCode, spl.SortCode) AS SortCode,
						   a.isEdited,
						   a.isEditable,
						   case when a.adhocduty = 1 then 0 else 1 end as display_priority,
						   scp.UC_CostCode                                AS CostCode,
					       cast(NULL as VARCHAR)                       AS LeaveType,
						   a.MarkedOvertime                            AS MarkedOvertime,
						   case when ISNULL(a.dutycomments,'') = '' Then 0	else 1 END	AS DutyCommentsFlag,
						   case when ISNULL(a.PersonComments,'') = '' THEN 0 else 1 END	AS PersonCommentsFlag,
						   a.MannualOThours as MannualOThours,
						   ISNULL(a.LeaveStartTime,0) AS LeaveStartTime,
						   ISNULL(a.LeaveEndTime,0) AS LeaveEndTime,
						   ISNULL(st1.schedulingTeamName, st.schedulingTeamName) as schedulingTeamName,
						   (SELECT aj.JobName as JobName, 
						           aj.StartTime as JobStartTime, 
								   aj.EndTime as JobEndTime, 
						           aj.JobBackColour as JobBackColour, 
								   aj.JobFontColour as JobFontColour 
						      FROM Allocations_Jobs_Publish aj 
						     WHERE aj.AllocationID = a.id 
							   AND ST.ShowJobsInWeeklyView = 1 
							   FOR JSON PATH) AS jobs,
						   1 as IsPublished,
						   cast(null as varchar) as LeaveFontColour,
						   a.AllocationsDutyID,
						   a.AllocationsSPID,
						   CASE WHEN spl.IsHomeTeam IN (0,2) and spl.IsAvailable = 0 AND a.AllocationID IS NULL 
						        THEN 1 ELSE 0 END AS IsExlude,
								spl.DisplayInViewScreen
			          FROM vAllocationsPublishedWeeks AL
		             INNER JOIN Timedimension TD (NOLOCK) on AL.AL_WeekNumber = TD.ixYearWeek
			         INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on AL_SchedulingTeamID = spl.TeamID				  
			         INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = spl.ScheduledPersonID
					 INNER JOIN schedulingTeams AS ST (NOLOCK)  ON AL_SchedulingTeamID = ST.SchedulingTeamId
					  LEFT JOIN Allocations_publish as a (NOLOCK) ON AL_SchedulingTeamID = a.SchedulingTeamId
																 AND UD_UserID = a.SchedulingPersonID
																 AND AL_WeekNumber = a.WeekNumber
																 AND TD.ixDayInWeek = a.iDay
					  LEFT JOIN schedulingTeams ST1 on st1.schedulingTeamId = a.DutyTeamID
			          LEFT JOIN UserConfigs scp (nolock) ON sp.UD_UserID = scp.UC_UserID
							AND TD.dDateTime BETWEEN scp.UC_StartDate AND scp.UC_EndDate
					 INNER JOIN (select min(td.dDateTime) startdate 
					               from TimeDimension td (NOLOCK) 
								   where td.ixYearWeek = @intweekStart ) AS TD1 ON 1=1
					  LEFT JOIN REF_MasterDutyColours AS dc (NOLOCK)  ON dc.MasterDutyColourID = a.dutyColorId
					  LEFT JOIN (select Description , stl.schedulingTeamId, TextColour 
								   from AllocationsTextColours AT
								  INNER JOIN schedulingTeams STL on stl.divisionId = at.DivisionID) AT ON AT.Description = A.DutyName
							AND at.schedulingTeamId = case when a.DutyTeamID > 0 then a.DutyTeamID
															 else a.SchedulingTeamId end
			         WHERE SPL.teamid in (select value FROM string_split(@filterTeamCond,',') ) 
					   AND TD.ixYearWeek BETWEEN @intweekStart AND @intweekEnd
					   AND spl.scheduledType = 1
					   AND AL_Status = 1
					   AND td.dDateTime BETWEEN spl.StartDate AND spl.EndDate
					   AND sp.UD_UserID = CASE WHEN ISNULL(@scheduledPersonId,0) <> 0 THEN @scheduledPersonId
					                                   ELSE sp.UD_UserID END
					   AND 1 = CASE WHEN @IsFreelancer = 1 
					                 AND sp.UD_UserID <> @FreelancerSPID 
									 AND td.dDateTime < CAST(getdate() - 1 AS DATE) 
									 AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
									 THEN 2
								    WHEN @IsFreelancer = 1 
									 AND sp.UD_UserID <> @FreelancerSPID 
									 AND td.dDateTime > getdate()+ISNULL(st.freelancerMaskingDays,999) 
									 AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
									 THEN 2
							   ELSE 1 END													   
				       AND 1 = CASE WHEN @isshiftleader = '1' THEN
					             CASE WHEN td1.startdate > getdate()+isnull(st.maskafter,999) 
				                       AND ISNULL(st.masktype,2) <> 2 
								      THEN 2 
								 ELSE 1 END
						       ELSE 1 END	
					 ) AL WHERE IsExlude = 0

		    -- Show Adhoc Duties

			        INSERT INTO #TempAllocations
		            SELECT a.MasterDutyID ID,
					       0 as AllocationID,
					       TD.ixYearWeek WeekNumber,
		                   0 AS StaffID,
						   UD_StaffNumber StaffNumber,
						   UD_DisplayName AS DisplayName,
						   UD_DisplayLastName AS SurName,
						   sp.UD_NetLogin NetLogin,
						   spl.TeamID as StaffTeamID,
						   UD_InternalEmail as Email1,
						   null as active,
						   null as inBuilding,
						   null AS SignInStartTime,
						   null AS SignInEndTime,
						   0 As IsTemplate,
						   0 MasterDutyID,
						   0 dutyProgramId, 
							CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
								  AND ISNULL(spl.ROTA,0) <> 0 THEN ''
								 ELSE a.DutyName END as DutyName,
							a.ScheduledPersonID SchedulingPersonID,
							TD.ixDayInWeek iday,
							CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
								  AND ISNULL(spl.ROTA,0) <> 0 THEN NULL
								 ELSE a.Duration END as Duration,					
							CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
								  AND ISNULL(spl.ROTA,0) <> 0 THEN NULL
								 ELSE a.StartTime END as StartTime,
							CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
								  AND ISNULL(spl.ROTA,0) <> 0 THEN NULL
								 ELSE a.EndTime END as EndTime,
						   a.TeamId  as SchedulingTeamId ,
						   spl.IsHomeTeam,
						   a.dutyColourId as dutyColorId,
							CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourBackground, 'EFEFEF') 
								 ELSE 'EFEFEF'  END AS BackColour,
							CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourFont, '330066') 
								 ELSE ISNULL(AT.TextColour, '330066')  END AS FontColour,
						   spl.fontcolour as StaffTextColour,
						   ISNULL(spl.BackgroundColour,'#cccccc') AS StaffBackColour,
						   spl.SortCode AS SortCode,
						   0 as isEdited,
						   1 as isEditable,
						   0 as display_priority,
						   scp.UC_CostCode                                AS CostCode,
						   CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
									THEN 'PDL'
									WHEN ISNULL(LA.Totalhrs,0) > 0 AND LA.ID > 0
									THEN 'Leave' 
									WHEN ISNULL(LA.Totalhrs,0) = 0 AND LA.ID > 0
									THEN 'OFF Leave' 
									END           AS LeaveType,
						   0                            AS MarkedOvertime,
						   0	AS DutyCommentsFlag,
						   0	AS PersonCommentsFlag,
						   0 as MannualOThours,
						   ISNULL(la.LeaveStartTime,0) AS LeaveStartTime,
						   ISNULL(la.LeaveEndTime,0) AS LeaveEndTime,
						   st.schedulingTeamName as schedulingTeamName,
						   NULL AS jobs,
						   0 as IsPublished,
						 CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc1.FontColour, '330066') 
										 ELSE ISNULL(AT1.TextColour, '330066')  END AS LeaveFontColour,
						   0 as AllocationsDutyID,
						   0 as AllocationsSPID,
						   spl.DisplayInViewScreen
			          FROM MasterDuties as a (NOLOCK)
		             INNER JOIN Timedimension TD (NOLOCK) on td.dDateTime between  a.StartDate  and a.EndDate
			         INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = a.ScheduledPersonID
			         INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.UD_UserID = spl.ScheduledPersonID
					                        AND spl.teamid = A.TeamId
					 INNER JOIN schedulingTeams AS ST (NOLOCK)  ON A.TeamId = ST.SchedulingTeamId
			          LEFT JOIN UserConfigs scp (nolock) ON sp.UD_UserID = scp.UC_UserID
							 AND td.dDateTime BETWEEN ISNULL(scp.UC_StartDate,td.dDateTime)
							                AND ISNULL( scp.UC_EndDate,td.dDateTime)											
					 INNER JOIN (select min(td.dDateTime) startdate 
					               from TimeDimension td (NOLOCK) 
								   where td.ixYearWeek = @intweekStart ) AS TD1 ON 1=1
					  LEFT JOIN REF_MasterDutyColours AS dc (NOLOCK)  ON dc.MasterDutyColourID = a.DutyColourID
					  LEFT JOIN (select Description , stl.schedulingTeamId, TextColour 
								   from AllocationsTextColours AT
								  inner join schedulingTeams STL on stl.divisionId = at.DivisionID
								  where stl.schedulingTeamId in (select value FROM string_split(@filterTeamCond,',') )
								  ) AT ON AT.Description = A.DutyName
							AND at.schedulingTeamId =  a.TeamID 
					  LEFT JOIN LeaveApplications la on la.SchedulingPersonID = a.ScheduledPersonID 
								and la.dDate = td.dDateTime and la.Approved = 1 and la.Deleted = 0 
							 left join ( 
										 SELECT MasterDutyColourID as dutyColorId,
												CASE WHEN (st.colourWeek = 1) THEN mc.ColourBackground 
													  END AS BackColour,
												CASE WHEN (st.colourWeek = 1) THEN mc.ColourFont
													 ELSE '330066' END AS FontColour,
												st.schedulingTeamId
										   FROM REF_MasterDutyColours MC (NOLOCK)
										  INNER JOIN schedulingTeams SL ON SL.divisionId = MC.AreaID
										  inner join schedulingTeams st on st.schedulingTeamId = sl.schedulingTeamId
										  WHERE UPPER(mc.ColourName) like '%LEAVE%'
										  AND St.schedulingTeamId in (select value FROM string_split(@filterTeamCond,',') )
							            ) DC1 on DC1.schedulingTeamId = a.TeamID
							LEFT JOIN ( select Description , 
										        stl.schedulingTeamId, 
												TextColour 
										from AllocationsTextColours AT
										inner join schedulingTeams STL on stl.divisionId = at.DivisionID
										where stl.schedulingTeamId in (select value FROM string_split(@filterTeamCond,',') )
										) AT1 ON AT1.schedulingTeamId = st.schedulingTeamId
									AND	AT1.Description = CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
															 THEN 'PDL'
															 WHEN ISNULL(LA.Totalhrs,0) > 0 
															  THEN 'Leave' 
															  ELSE 'OFF Leave' 
														  END
			         WHERE SPL.teamid in (select value FROM string_split(@filterTeamCond,',') ) 
					   AND TD.ixYearWeek between @intweekStart AND @intweekEnd
					   AND spl.scheduledType = 1
					   AND a.DutyTypeID = 6
					   AND a.IsActive = 1
					   AND td.dDateTime between spl.StartDate and spl.EndDate
					   AND sp.UD_UserID = CASE WHEN ISNULL(@scheduledPersonId,0) <> 0 THEN @scheduledPersonId
					                                   ELSE sp.UD_UserID END
					   AND 1 = CASE WHEN @IsFreelancer = 1 
					                 AND sp.UD_UserID <> @FreelancerSPID 
									 AND td.dDateTime < CAST(getdate() - 1 AS DATE) 
									 AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
									 THEN 2
								    WHEN @IsFreelancer = 1 
									 AND sp.UD_UserID <> @FreelancerSPID 
									 AND td.dDateTime > getdate()+ISNULL(st.freelancerMaskingDays,999) 
									 AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
									 THEN 2
							   ELSE 1 END													   
				       AND 1 = CASE WHEN @isshiftleader = '1' THEN
					             CASE WHEN td1.startdate > getdate()+isnull(st.maskafter,999) 
				                       AND ISNULL(st.masktype,2) <> 2 
								      THEN 2 
								 ELSE 1 END
						       ELSE 1 END	
					   AND NOT EXiSTS ( SELECT 1 
			                                FROM #TempAllocations TTS
										   WHERE TTS.WeekNumber = td.ixYearWeek 
										     AND TTS.display_priority <> 0
										   )
						AND NOT EXISTS ( SELECT 1
											FROM #TempAllocations TLS
										WHERE TLS.SchedulingTeamId = st.SchedulingTeamId
											AND TLS.SchedulingPersonID = sp.UD_UserID
											AND TLS.DOTW = td.ixDayInWeek
											AND TLS.WeekNumber = TD.ixYearWeek 
											AND TLS.display_priority = 0)
			
			-- Show ROTA if there are no allocations to show
													   
            INSERT INTO #TempAllocations
			SELECT  isnull(fd.ID,0) as ID,
			        ISNULL(fd.Allocationid,0) as Allocationid,
			        ISNULL(FD.WeekNumber,LV.WeekNumber) as WeekNumber,
					ISNULL(FD.StaffID,LV.StaffID) as  StaffID,
				    ISNULL(LV.StaffNumber,LV.StaffNumber) AS StaffNumber,
				    ISNULL(FD.DisplayName,LV.DisplayName) AS DisplayName,
				    ISNULL(FD.SurName,LV.SurName) AS SurName,
				    ISNULL(FD.NetLogin,LV.NetLogin) AS NetLogin,
				    isnull(fd.StaffTeamID, lv.SchedulingTeamid) as StaffTeamID,
				    isnull(fd.Email1, lv.Email1) AS Email1,
				    active,
					inBuilding,
					SignInStartTime,
					SignInEndTime,
					isnull(FD.IsTemplate,0) as IsTemplate,
					MasterDutyID,
					dutyProgramId,
			        DutyName,
					isnull(fd.SchedulingPersonID,lv.SchedulingPersonID) as SchedulingPersonID,
					ISNULL(FD.DOTW,LV.DOTW) as DOTW, 
			        Duration,					
			        StartTime,
			        EndTime,
					ISNULL(FD.SchedulingTeamId,LV.SchedulingTeamid) as SchedulingTeamId,
					IsHomeTeam,	
					isnull(fd.dutyColorId,lv.dutyColorId) as dutyColorId,
					isnull(fd.BackColour,isnull(lv.BackColour,'EEEEFF')) as BackColour,
					isnull(fd.FontColour,isnull(lv.FontColour,'330066')) as FontColour,
					StaffTextColour,
					isnull(StaffBackColour,'#cccccc') as StaffBackColour,
					isnull(fd.SortCode, LV.SortCode) as SortCode,
					isEdited,
					isEditable,
					isnull(display_priority,2) as display_priority,
					CostCode,
					LV.LeaveType                                AS LeaveType,
					MarkedOvertime,
					isnull(DutyCommentsFlag,0) DutyCommentsFlag,
					isnull(PersonCommentsFlag,0) PersonCommentsFlag,
					MannualOThours,
					ISNULL(LV.LeaveStartTime,0) AS LeaveStartTime,
					ISNULL(LV.LeaveEndTime,0) AS LeaveEndTime,
					isnull(fd.schedulingTeamName,lv.schedulingTeamName) as schedulingTeamName,
					jobs,
					0 as IsPublished,
					isnull(lv.FontColour,'330066') LeaveFontColour,
					0 as AllocationsDutyID,
					0 as AllocationsSPID,
					isnull(lv.DisplayInViewScreen,fd.DisplayInViewScreen) as DisplayInViewScreen
			FROM (
			SELECT  er.ID,
			        0 as Allocationid,
			        td1.ixYearWeek AS WeekNumber,
					0 AS StaffID,
					UD_StaffNumber StaffNumber,
					UD_DisplayName AS DisplayName,
					UD_DisplayLastName AS SurName,
					UD_NetLogin as  NetLogin,
					er.SchedulingTeamId as StaffTeamID,
					sp.UD_InternalEmail as Email1,
					null as active,
					null as inBuilding,
					null AS SignInStartTime,
					null AS SignInEndTime,
					er.IsTemplate,
					er.MasterDutyID,
					null as dutyProgramId,
			        CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
						  AND ISNULL(sptl.ROTA,0) <> 0 THEN ''
			             ELSE er.DutyName END as DutyName,
					er.SchedulingPersonID,
					er.DOTW,
			        CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
						  AND ISNULL(sptl.ROTA,0) <> 0 THEN NULL
			             ELSE er.Duration END as Duration,					
			        CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
						  AND ISNULL(sptl.ROTA,0) <> 0 THEN NULL
			             ELSE er.StartTime END as StartTime,
			        CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
						  AND ISNULL(sptl.ROTA,0) <> 0 THEN NULL
			             ELSE er.EndTime END as EndTime,
					er.SchedulingTeamId,
					er.IsHomeTeam,
					er.DutyColourID as dutyColorId,
					CASE WHEN (st.colourWeek = 1) THEN dc.ColourBackground END AS BackColour,
					CASE WHEN (st.colourWeek = 1) THEN dc.ColourFont 
						 ELSE AT.TextColour  END AS FontColour,
					er.StaffTextColour,
					ISNULL(er.StaffBackColour,'#cccccc') AS StaffBackColour,
					sptl.SortCode,
					0 as isEdited,
					1 as isEditable,
					2 as display_priority,
					scp.UC_CostCode                                AS CostCode,
					0											AS MarkedOvertime,
					0								AS DutyCommentsFlag,
					0								AS PersonCommentsFlag,
					0 As MannualOThours,
					st.schedulingTeamName,
					td3.dDateTime as Dutydate,
					sptl.DisplayInViewScreen,
					( SELECT mj.JobName as JobName, 
					         mj.StartTime as JobStartTime, 
							 mj.EndTime as JobEndTime, 
							 mjc.ColourBackground as JobBackColour, 
							 mjc.ColourFont as JobFontColour 
					    FROM MasterDutiesMasterJobs_LINK mjl 
					   INNER JOIN MasterJobs as mj  ON mj.MasterJobID = mjl.MasterJobID
					    LEFT JOIN REF_MasterJobColours as mjc ON mjc.MasterJobID = mj.MasterJobID
					WHERE er.MasterDutyID = mjl.MasterDutyID 
					  AND ST.ShowJobsInWeeklyView = 1 
					  FOR JSON PATH) AS jobs
			FROM Exported_rota as er (NOLOCK)
			INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = er.SchedulingPersonID
			INNER JOIN 
			( 
				SELECT td.ixYearWeek,
				CASE
					when ROW_NUMBER() over(partition by SchedulingPersonID,RotaID order by td.ixYearWeek) % td.WeeksInRota = 0
					THEN td.WeeksInRota
					ELSE ROW_NUMBER() over(partition by SchedulingPersonID,RotaID order by td.ixYearWeek) % td.WeeksInRota
				END weeksinrota,SchedulingPersonID, RotaID,
									SchedulingTeamId
				FROM 
				(
					SELECT DISTINCT ixYearWeek,
					                er.WeeksInRota,
					                er.SchedulingPersonID,
									RotaID,
									SchedulingTeamId
					FROM TimeDimension td (NOLOCK) ,
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
							 WHERE ixYearWeek BETWEEN @intweekStart AND @intweekEnd ) AS TDI1 ON 1 = 1 
						 WHERE ER1.SchedulingTeamId in (select value FROM string_split(@filterTeamCond,',') )
						  AND TDI1.startdate <= ISNULL( CAST(ER1.RotaAssignmentEndDate AS DATE), TDI1.startdate)
			              AND TDI1.enddate >= ISNULL(ER1.RotaAssignmentStartDate, TDI1.enddate)												
						
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
			INNER JOIN TimeDimension TD3 (NOLOCK) ON TD3.ixYearWeek = TD1.ixYearWeek AND TD3.ixDayInWeek = ER.DOTW
			INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as sptl 
			    ON sp.UD_UserID = sptl.ScheduledPersonID AND sptl.TeamID = er.SchedulingTeamId
			 AND ISNULL(sptl.ROTA,0) = CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
			                                 AND ISNULL( @scheduledPersonId,0) <> 0 THEN 0
			                                ELSE  ISNULL(sptl.ROTA,0) END
			 AND sptl.scheduledType = 1 
			 AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime) 
			INNER JOIN schedulingTeams AS ST (NOLOCK)  ON er.SchedulingTeamId = ST.SchedulingTeamId
			LEFT JOIN UserConfigs scp (nolock) ON UD_UserID = scp.UC_UserID
							 AND td2.startdate <= ISNULL( scp.UC_EndDate, td2.startdate)
							 AND td2.enddate >= ISNULL(scp.UC_StartDate, td2.enddate)							 
			LEFT JOIN REF_MasterDutyColours AS dc (NOLOCK)  ON dc.MasterDutyColourID = er.DutyColourID
			LEFT JOIN (select Description , stl.schedulingTeamId, TextColour 
						from AllocationsTextColours AT
						inner join schedulingTeams STL on stl.divisionId = at.DivisionID) AT ON AT.Description = er.DutyName
				AND at.schedulingTeamId = er.SchedulingTeamId
			WHERE SPTL.teamid in (select value FROM string_split(@filterTeamCond,',') )  
			  AND td1.ixYearWeek BETWEEN @intweekStart AND @intweekEnd
			  and td3.dDateTime between er.RotaAssignmentStartDate and ISNULL( er.RotaAssignmentEndDate, td3.dDateTime)
		      AND td1.ixYearWeek <= er.AssignmentEndWeek
			  AND TD3.dDateTime between isnull(er.DutyStartDate,TD3.dDateTime) and isnull(er.DutyEndDate,TD3.dDateTime)
			  AND sp.UD_UserID = case when ISNULL(@scheduledPersonId,0) <> 0 then @scheduledPersonId
					                                   else sp.UD_UserID end
			  AND 1 = CASE WHEN @IsFreelancer = 1 
			                AND	sp.UD_UserID <> @FreelancerSPID
						    AND td3.dDateTime < CAST(getdate() - 1 AS DATE) 
							AND TD3.dDateTime BETWEEN @FLStartDate and @FLEndDate
							THEN 2
						   WHEN @IsFreelancer = 1 
			                AND	sp.UD_UserID <> @FreelancerSPID 
							AND td3.dDateTime > getdate() + ISNULL(st.freelancerMaskingDays,999) 
							AND TD3.dDateTime BETWEEN @FLStartDate and @FLEndDate
							THEN 2
						 ELSE 1 END		
			  AND 1 = CASE WHEN ISNULL( @isShiftLeader,'0') = '1' 
						    AND ISNULL(sptl.ROTA,0) <> 0 THEN 2 ELSE 1 END												   
              AND NOT EXiSTS ( SELECT 1 
			                                FROM #TempAllocations TTS
										   WHERE TTS.WeekNumber = td1.ixYearWeek 
										     AND TTS.display_priority <> 0
										   )
			  AND NOT EXISTS ( SELECT 1
			                     FROM #TempAllocations TLS
								WHERE TLS.SchedulingTeamId = ER.SchedulingTeamId
								  AND TLS.SchedulingPersonID = ER.SchedulingPersonID
						          AND TLS.DOTW = ER.DOTW
								  AND TLS.WeekNumber = TD1.ixYearWeek 
								  AND TLS.display_priority = 0)
			 ) FD
			 FULL JOIN (     SELECT SL.TeamID as SchedulingTeamid,
			                        LA.SchedulingPersonID,
									TD.dDateTime       AS DutyDate,
									--SP.DisplayName,
									TD.ixYearWeek WeekNumber,
									TD.ixDayInWeek DOTW,
									LA.LeaveStartTime,
									LA.LeaveEndTime,
									st.schedulingTeamName,
									0 AS StaffID,
									UD_StaffNumber StaffNumber,
									UD_InternalEmail as Email1,
									dc.dutyColorId,
									CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.BackColour, 'EFEFEF') 
										 ELSE 'EFEFEF'  END AS BackColour,
									CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.FontColour, '330066') 
										 ELSE ISNULL(AT.TextColour, '330066')  END AS FontColour,
									UD_DisplayName AS DisplayName,
									UD_DisplayLastName AS SurName,
									UD_NetLogin NetLogin,
									sl.SortCode,
									sl.DisplayInViewScreen,
									CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
									     THEN 'PDL'
									     WHEN ISNULL(LA.ZeroLeave,0) = 0 
										 THEN 'Leave' 
										 ELSE 'OFF Leave' 
										 END           AS LeaveType																
							  FROM LeaveApplications LA (NOLOCK)
							 INNER JOIN TimeDimension TD (NOLOCK) ON TD.dDateTime = LA.dDate 
							 INNER JOIN UserDetails SP on LA.SchedulingPersonID = SP.UD_UserID
							 INNER JOIN ScheduledPersonTeam_LINK SL (NOLOCK)
									 ON LA.SchedulingPersonID = SL.scheduledpersonid
									AND SL.teamid in (select value FROM string_split(@filterTeamCond,',') )  
							 INNER JOIN schedulingTeams st on st.schedulingTeamId = sl.TeamID
							 left join ( 
										 SELECT MasterDutyColourID as dutyColorId,
												CASE WHEN (st.colourWeek = 1) THEN mc.ColourBackground 
													  END AS BackColour,
												CASE WHEN (st.colourWeek = 1) THEN mc.ColourFont
													 ELSE '330066' END AS FontColour,
												st.schedulingTeamId
										   FROM REF_MasterDutyColours MC (NOLOCK)
										  INNER JOIN schedulingTeams SL ON SL.divisionId = MC.AreaID
										  inner join schedulingTeams st on st.schedulingTeamId = sl.schedulingTeamId
										  WHERE UPPER(mc.ColourName) like '%LEAVE%'
										  AND St.schedulingTeamId in (select value FROM string_split(@filterTeamCond,',') )
							            ) DC on dc.schedulingTeamId = st.schedulingTeamId
							LEFT JOIN ( select Description , 
										        stl.schedulingTeamId, 
												TextColour 
										from AllocationsTextColours AT
										inner join schedulingTeams STL on stl.divisionId = at.DivisionID
										where stl.schedulingTeamId in (select value FROM string_split(@filterTeamCond,',') )
										) AT ON AT.schedulingTeamId = st.schedulingTeamId
									AND	AT.Description = CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
															 THEN 'PDL'
															 WHEN ISNULL(LA.Totalhrs,0) > 0 
															  THEN 'Leave' 
															  ELSE 'OFF Leave' 
														  END
							 WHERE Isnull(SL.startdate, TD.dDateTime) <= TD.dDateTime
							   AND Isnull(SL.enddate, TD.dDateTime) >= TD.dDateTime
							   AND LA.Approved = 1	
							   AND LA.Deleted = 0
							   AND SL.scheduledtype = 1			  			
							   AND ( SL.ishometeam = 1
									OR (    SL.ishometeam IN (0,2) 
										AND SL.isavailable = 1 ) )	
							   AND TD.ixYearWeek BETWEEN @intweekStart AND @intweekEnd	
							   AND NOT EXISTS ( SELECT 1
												 FROM #TempAllocations TLS
												WHERE TLS.SchedulingPersonID = SP.UD_UserID
												  AND TLS.DOTW = td.ixDayInWeek
												  AND TLS.WeekNumber = TD.ixYearWeek 
												)
					  ) LV ON LV.SchedulingTeamid = FD.SchedulingTeamId
						  AND LV.SchedulingPersonID = FD.SchedulingPersonID
						  AND LV.DutyDate = FD.DutyDate

			UPDATE #TempAllocations SET display_priority = 1 WHERE display_priority = 0
								  
		   EXEC (' SELECT * FROM #TempAllocations ORDER BY SurName,WeekNumber, DOTW')

END