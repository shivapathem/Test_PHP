USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_weeklyshifts]    Script Date: 31/07/2025 19:34:12 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_fetch_weeklyshifts]
@scheduledPersonId     INT,
@StartWeek             INT,
@EndWeek               INT
	
AS
BEGIN

	SET NOCOUNT ON;

	DECLARE @TeamId               INT

	 SELECT @TeamId=spl.teamid
	   FROM ScheduledPersonTeam_LINK (NOLOCK) as spl
	  INNER JOIN UserDetails SP ON SP.UD_UserID = SPL.ScheduledPersonID
	  INNER JOIN ( SELECT MIN(dDateTime) StartDate,
	                      MAX(dDateTime) EndDate 
	                 FROM TimeDimension TD
	                WHERE TD.ixYearWeek between @StartWeek AND @EndWeek) TD ON 1=1
	  INNER JOIN UserRoles UTL ON UTL.UR_SchedulingTeamID = SPL.TeamID AND UTL.UR_UserID=SP.UD_UserID
	  INNER JOIN REF_Roles RR ON RR.RoleID = UTL.UR_RoleID
	  WHERE SP.UD_UserID = @scheduledPersonId
	    AND spl.scheduledType = 0
		AND SPL.isDefault = 1
		AND td.EndDate >= ISNULL(spl.StartDate,td.EndDate) 
		AND td.StartDate <= ISNULL(spl.EndDate,td.StartDate)
		AND td.EndDate >= ISNULL(UTL.UR_StartDate,td.EndDate) 
		AND td.StartDate <= ISNULL(UTL.UR_EndDate,td.StartDate)
		AND RR.RoleName='Shift Leader'


	 
	 SELECT a.ID,
			a.WeekNumber,
		    0 AS StaffID,
			UD_StaffNumber StaffNumber,
			UD_DisplayName AS DisplayName,
			UD_DisplayLastName AS SurName,
			UD_NetLogin	AS NetLogin,
			spl.TeamID as StaffTeamID,
			UD_InternalEmail as Email1,
			a.SigninStatus as active,
			a.SigninINBuilding as inBuilding,
			a.SigninStartTime  AS SignInStartTime,
			a.SigninEndTime AS SignInEndTime,
			0 As IsTemplate,
			a.MasterDutyID,
			a.dutyProgramId, 
			CASE WHEN ISNULL(a.DutyTeamID,0) > 0 AND a.DutyTeamID <> a.SchedulingTeamId THEN '-'
			ELSE
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
			END    as DutyName,
			a.SchedulingPersonID,
			a.iDay as DOTW,
			CASE WHEN ISNULL(a.DutyTeamID,0) > 0 AND a.DutyTeamID <> a.SchedulingTeamId THEN NULL
			ELSE
				case when a.dutydate > getdate()+isnull(st.maskafter,999) 
						and (a.starttime > 0 or a.endtime > 0 ) 
						AND ISNULL(st.masktype,2) <> 2 
					then NULL
				ELSE a.Duration END 
				END AS Duration,
			CASE WHEN ISNULL(a.DutyTeamID,0) > 0 AND a.DutyTeamID <> a.SchedulingTeamId THEN NULL
			ELSE						   
			case when  a.dutydate > getdate()+isnull(st.maskafter,999) 
					AND ISNULL(st.masktype,2) <> 2 then NULL
				else a.StartTime end 
			END AS StartTime,
			CASE WHEN ISNULL(a.DutyTeamID,0) > 0 AND a.DutyTeamID <> a.SchedulingTeamId THEN NULL
			ELSE								   
			case when a.dutydate > getdate()+isnull(st.maskafter,999) 
					AND ISNULL(st.masktype,2) <> 2 then NULL
				else a.EndTime end 
			END as EndTime,	
			a.SchedulingTeamId,
			spl.IsHomeTeam,
			a.dutyColorId as dutyColorId,
			CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourBackground, 'EFEFEF') 
					ELSE 'EFEFEF'  END AS BackColour,
			CASE WHEN (st.colourWeek = 1) THEN ISNULL(dc.ColourFont, '330066') 
					ELSE '330066'  END AS FontColour,
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
			a.ManualOTAmount As ManualOTAmount,
			a.AllocationsDutyID,
			a.AllocationsSPID			
		FROM dbo.Allocations_publish as a (NOLOCK)
		INNER JOIN Timedimension TD (NOLOCK) on A.WeekNumber=TD.ixYearWeek and A.iDay = TD.ixDayInWeek					  
		INNER JOIN UserDetails as sp (NOLOCK) on sp.UD_UserID = a.SchedulingPersonID
		INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.UD_UserID = spl.ScheduledPersonID 
													AND spl.teamid = A.SchedulingTeamId
		INNER JOIN schedulingTeams AS ST (NOLOCK)  ON SPL.TeamId = ST.SchedulingTeamId
		LEFT JOIN UserConfigs AS scp (nolock) ON UD_UserID = scp.UC_UserID
				            AND A.dutydate BETWEEN ISNULL(scp.UC_StartDate,A.dutydate)
							AND ISNULL( scp.UC_EndDate,A.dutydate)						
		INNER JOIN (select min(td.dDateTime) startdate 
					from TimeDimension td (NOLOCK) 
					where td.ixYearWeek = @StartWeek ) AS TD1 ON 1=1
		LEFT JOIN REF_MasterDutyColours AS dc (NOLOCK)  ON dc.MasterDutyColourID = a.dutyColorId
		WHERE SPL.teamid = @TeamId
	      AND SPL.scheduledType = 1
          AND TD.ixYearWeek between @StartWeek AND @EndWeek
	      AND ( a.StartTime > 0 OR a.EndTime > 0 )
		  AND td.dDateTime >= ISNULL(spl.StartDate,td.dDateTime) 
		  AND td.dDateTime <= ISNULL(spl.EndDate,td.dDateTime)
		  AND td1.startdate < getdate()+isnull(st.maskafter,999) 
		  AND ISNULL(st.masktype,2) <> 2 
		ORDER BY SurName,WeekNumber, DOTW

END