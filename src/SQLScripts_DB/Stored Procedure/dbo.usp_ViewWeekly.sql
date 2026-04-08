USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_ViewWeekly]    Script Date: 30/03/2026 14:26:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                    PROCEDURE [dbo].[usp_ViewWeekly]
@intTeamId             VARCHAR(MAX),
@intweekStart          INT,
@intweekEnd            INT,
@pNetLogin             VARCHAR(30)

AS
BEGIN

	SET NOCOUNT ON;
			
    DECLARE @FilterSQL                 VARCHAR(MAX)
	DECLARE @VFilter                   VARCHAR(MAX)
	DECLARE @VLJFilter                 VARCHAR(MAX)	
	DECLARE @NoOfSkills                INT = 0
	DECLARE @NoOfLabels                INT = 0
	DECLARE @IsAndFilter               INT = 0
	DECLARE @WeekStartDate DATE,
			@WeekEndDate DATE;
	
		   SELECT @WeekStartDate = MIN(dDateTime),  
		          @WeekEndDate = MAX(dDateTime)
		     FROM TimeDimension
			WHERE ixYearWeek between @intweekStart and @intweekEnd	 

		            SELECT a.MasterDutyID as ID,
					       0  as AllocationID,
					       td.ixYearWeek as  WeekNumber,
		                   UD_UserID AS StaffID,
						   UD_StaffNumber as StaffNumber,
						   ud_DisplayName AS DisplayName,
						   ud_DisplayLastName  AS SurName,
						   UD_NetLogin as NetLogin,
						   spl.TeamID as StaffTeamID,
						   sp.UD_InternalEmail as Email1,
						   0 as active,
						   0 as inBuilding,
						   0 AS SignInStartTime,
						   0 AS SignInEndTime,
						   0 As IsTemplate,
						   0 MasterDutyID,
						   0 dutyProgramId, 
						   a.DutyName DutyName,
						   a.ScheduledPersonID as SchedulingPersonID,
						   td.ixDayInWeek as DOTW,
						   a.Duration Duration,						   
						   a.StartTime  StartTime,								   
						   a.EndTime   EndTime,	
						   a.TeamID  as SchedulingTeamId ,
						   spl.IsHomeTeam,
						   a.DutyColourID as dutyColorId,
							CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourBackground, 'EFEFEF') 
								 ELSE 'EFEFEF'  END AS BackColour,
							CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourFont, '330066') 
								 ELSE ISNULL(AT.TextColour, '330066')  END AS FontColour,
						   spl.fontcolour as StaffTextColour,
						   ISNULL(spl.BackgroundColour,'#cccccc') AS StaffBackColour,
						   spl.SortCode  AS SortCode,
						   0.isEdited,
						   0 isEditable,
						   1  as display_priority,
						   scp.UC_CostCode                                AS CostCode,
						   CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
									     THEN 'PDL'
									     WHEN ISNULL(LA.ZeroLeave,0) = 0  and LA.ID is not null
										 THEN 'Leave' 
										 WHEN ISNULL(LA.ZeroLeave,0) <> 0  and LA.ID is not null
										 THEN 'OFF Leave' 
										 ELSE ''
										 END           AS  LeaveType,
						   0                            AS MarkedOvertime,
						   case when ISNULL(a.dutycomment,'') = '' Then 0	else 1 END	AS DutyCommentsFlag,
						   case when ISNULL(a.DutyComment,'') = '' THEN 0 else 1 END	AS PersonCommentsFlag,
						   0 as MannualOThours,
						   LA.LeaveStartTime AS LeaveStartTime,
						   LA.LeaveEndTime as  LeaveEndTime,
						   st.schedulingTeamName as schedulingTeamName
						   INTO #TempAllocations
			          FROM MasterDuties as a (NOLOCK)
		             INNER JOIN TimeDimension TD (NOLOCK) ON TD.dDateTime between a.StartDate and a.EndDate			 
			         INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = a.ScheduledPersonID
			         INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.UD_UserID = spl.ScheduledPersonID
					                        AND spl.teamid = A.TeamID
					 INNER JOIN schedulingTeams AS ST (NOLOCK)  ON A.TeamID = ST.SchedulingTeamId
			          LEFT JOIN UserConfigs scp (nolock) ON scp.UC_UserID = UD_UserID
							 AND TD.dDateTime BETWEEN UC_StartDate AND scp.UC_EndDate
					  LEFT JOIN REF_MasterDutyColours AS dc (NOLOCK)  ON dc.MasterDutyColourID = a.DutyColourID											
					  LEFT JOIN (select Description , stl.schedulingTeamId, TextColour 
								   from AllocationsTextColours AT
								  inner join schedulingTeams STL on stl.divisionId = at.DivisionID) AT ON AT.Description = A.DutyName
							AND at.schedulingTeamId = st.SchedulingTeamId 
			        LEFT JOIN	 LeaveApplications LA (NOLOCK) ON LA.SchedulingPersonID = sp.UD_UserID
														AND LA.dDate = TD.dDateTime ANd LA.Approved = 1		
							   AND la.Deleted = 0	
			         WHERE SPL.teamid = @intTeamId 
					   AND TD.ixYearWeek between @intweekStart AND @intweekEnd
					   AND spl.scheduledType = 1
					   AND td.dDateTime >= ISNULL(spl.StartDate,td.dDateTime) 
					   AND td.dDateTime <= ISNULL(spl.EndDate,td.dDateTime)
					   AND a.DutyTypeID = 6
					   AND a.IsActive = 1
			
			-- Show ROTA if there are no allocations to show
													   
            INSERT INTO #TempAllocations
			SELECT  er.ID,
			        0 as Allocationid,
			        td1.ixYearWeek AS WeekNumber,
					UD_UserID AS StaffID,
 					UD_StaffNumber as  StaffNumber,
					UD_DisplayName AS DisplayName,
					UD_DisplayLastName  AS SurName,
					UD_NetLogin as NetLogin,
					er.SchedulingTeamId as StaffTeamID,
					UD_InternalEmail as Email1,
					1 as active,
					0 as inBuilding,
					0 AS SignInStartTime,
					0 AS SignInEndTime,
					er.IsTemplate,
					er.MasterDutyID,
					0 as dutyProgramId,
			        er.DutyName  as DutyName,
					er.SchedulingPersonID,
					er.DOTW,
			        er.Duration  as Duration,					
			        er.StartTime  as StartTime,
			        er.EndTime  as EndTime,
					er.SchedulingTeamId,
					er.IsHomeTeam,
					er.DutyColourID as dutyColorId,
					CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourBackground, 'EEEEFF') 
						ELSE 'EEEEFF' END AS BackColour,
					CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourFont, '330066') 
						 ELSE ISNULL(AT.TextColour, '330066')  END AS FontColour,
					er.StaffTextColour,
					ISNULL(er.StaffBackColour,'#cccccc') AS StaffBackColour,
					er.SortCode,
					0 as isEdited,
					1 as isEditable,
					2 as display_priority,
					scp.UC_CostCode                                AS CostCode,
					CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
									     THEN 'PDL'
									     WHEN ISNULL(LA.ZeroLeave,0) = 0  and LA.ID is not null
										 THEN 'Leave' 
										 WHEN ISNULL(LA.ZeroLeave,0) <> 0  and LA.ID is not null
										 THEN 'OFF Leave' 
										 ELSE ''
										 END                                AS LeaveType,
					0											AS MarkedOvertime,
					0								AS DutyCommentsFlag,
					0								AS PersonCommentsFlag,
					0 As MannualOThours,
					ISNULL(LA.LeaveStartTime,0) AS LeaveStartTime,
					ISNULL(LA.LeaveEndTime,0) AS LeaveEndTime,
					st.schedulingTeamName
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
						 WHERE ER1.SchedulingTeamId in (select value FROM string_split(@intTeamId,',') )
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
			 AND sptl.scheduledType = 1 
			 AND td3.dDateTime between sptl.StartDate and ISNULL(sptl.EndDate, td3.dDateTime) 
			INNER JOIN schedulingTeams AS ST (NOLOCK)  ON er.SchedulingTeamId = ST.SchedulingTeamId
			LEFT JOIN UserConfigs scp (nolock) ON scp.UC_UserID = sp.UD_UserID
							 AND td2.startdate <= ISNULL( scp.UC_EndDate, td2.startdate)
							 AND td2.enddate >= ISNULL(scp.UC_StartDate, td2.enddate)							 
			LEFT JOIN REF_MasterDutyColours AS dc (NOLOCK)  ON dc.MasterDutyColourID = er.DutyColourID
			LEFT JOIN (select Description , stl.schedulingTeamId, TextColour 
						from AllocationsTextColours AT
						inner join schedulingTeams STL on stl.divisionId = at.DivisionID) AT ON AT.Description = er.DutyName
				AND at.schedulingTeamId = er.SchedulingTeamId
				LEFT JOIN	 LeaveApplications LA (NOLOCK) ON LA.SchedulingPersonID = sp.UD_UserID
														AND LA.dDate = TD3.dDateTime ANd LA.Approved = 1		
							   AND la.Deleted = 0	
			WHERE SPTL.teamid = @intTeamId 
			  AND td1.ixYearWeek BETWEEN @intweekStart AND @intweekEnd
			  and td3.dDateTime between er.RotaAssignmentStartDate and ISNULL( er.RotaAssignmentEndDate, td3.dDateTime)
		      AND td1.ixYearWeek <= er.AssignmentEndWeek
			  AND TD3.dDateTime between isnull(er.DutyStartDate,TD3.dDateTime) and isnull(er.DutyEndDate,TD3.dDateTime)										   
             -- AND td1.ixYearWeek NOT IN ( SELECT distinct WeekNumber 
			   --                             FROM #TempAllocations
				--						   WHERE display_priority <> 0)
			  AND NOT EXISTS ( SELECT 1
			                     FROM #TempAllocations TLS
								WHERE TLS.SchedulingTeamId = ER.SchedulingTeamId
								  AND TLS.SchedulingPersonID = ER.SchedulingPersonID
						          AND TLS.DOTW = ER.DOTW
								  AND TLS.WeekNumber = TD1.ixYearWeek 
						      )

			 
			 INSERT INTO #TempAllocations
		            SELECT 0 as  ID,
					       0  as AllocationID,
					       td.ixYearWeek as  WeekNumber,
		                   UD_UserID AS StaffID,
						   UD_StaffNumber as  StaffNumber,
						   UD_DisplayName AS DisplayName,
						   UD_DisplayLastName  AS SurName,
						   UD_NetLogin NetLogin,
						   spl.TeamID as StaffTeamID,
						   UD_InternalEmail as Email1,
						   0 as active,
						   0 as inBuilding,
						   0 AS SignInStartTime,
						   0 AS SignInEndTime,
						   0 As IsTemplate,
						   0 MasterDutyID,
						   0 dutyProgramId, 
						   'U' DutyName,
						   sp.UD_UserID as SchedulingPersonID,
						   td.ixDayInWeek as DOTW,
						   0 Duration,						   
						   0  StartTime,								   
						   0   EndTime,	
						   st.SchedulingTeamId  as SchedulingTeamId ,
						   spl.IsHomeTeam,
						   0 as dutyColorId,
						   'EFEFEF'   AS BackColour,
						   '330066'   AS FontColour,
						   spl.fontcolour as StaffTextColour,
						   ISNULL(spl.BackgroundColour,'#cccccc') AS StaffBackColour,
						   spl.SortCode  AS SortCode,
						   0 as isEdited,
						   0 as isEditable,
						   1  as display_priority,
						   scp.UC_CostCode                                AS CostCode,
					       CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 )
									     THEN 'PDL'
									     WHEN ISNULL(LA.ZeroLeave ,0) = 0 
										 THEN 'Leave' 
										 ELSE 'OFF Leave' 
										 END                       AS LeaveType,
						   0                            AS MarkedOvertime,
						   0	AS DutyCommentsFlag,
						   0	AS PersonCommentsFlag,
						   0 as MannualOThours,
						   LA.LeaveStartTime AS LeaveStartTime,
						   la.LeaveEndTime as   LeaveEndTime,
						   st.schedulingTeamName as schedulingTeamName
			          FROM TimeDimension TD 	 
			         INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on 1 = 1
			         INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = spl.ScheduledPersonID
					 INNER JOIN schedulingTeams AS ST (NOLOCK)  ON spl.TeamId = ST.SchedulingTeamId
			          LEFT JOIN UserConfigs scp (nolock) ON scp.UC_UserID = sp.UD_UserID
							 AND TD.dDateTime BETWEEN scp.UC_StartDate and scp.UC_EndDate
					 INNER JOIN (select min(td.dDateTime) startdate 
					               from TimeDimension td (NOLOCK) 
								   where td.ixYearWeek = @intweekStart ) AS TD1 ON 1=1
				    LEFT JOIN LeaveApplications LA (NOLOCK) ON LA.SchedulingPersonID = sp.UD_UserID
														AND LA.dDate = TD.dDateTime ANd LA.Approved = 1		
							   AND la.Deleted = 0	
			         WHERE SPL.teamid = @intTeamId 
					   AND TD.ixYearWeek between @intweekStart AND @intweekEnd
					   AND spl.scheduledType = 1
					   AND td.dDateTime >= ISNULL(spl.StartDate,td.dDateTime) 
					   AND td.dDateTime <= ISNULL(spl.EndDate,td.dDateTime)
					   AND ( spl.IsHomeTeam = 1 
					         OR (spl.IsHomeTeam IN (0,2) 
							     and spl.IsAvailable = 1  ))
					   AND NOT EXISTS ( SELECT 1
										  FROM #TempAllocations TLS
										 WHERE TLS.SchedulingTeamId = st.SchedulingTeamId
										   AND TLS.SchedulingPersonID = sp.UD_UserID
										   AND TLS.DOTW = td.ixDayInWeek
										   AND TLS.WeekNumber = TD.ixYearWeek 
									   )

					SELECT * 
					  FROM #TempAllocations 
					 ORDER BY SurName,WeekNumber, DOTW
	
END