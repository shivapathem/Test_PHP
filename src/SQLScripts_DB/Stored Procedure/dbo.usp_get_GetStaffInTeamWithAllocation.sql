USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_GetStaffInTeamWithAllocation]    Script Date: 2/26/2026 9:39:12 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER         PROCEDURE [dbo].[usp_get_GetStaffInTeamWithAllocation]
	-- Add the parameters for the stored procedure here
	@intDay int,
	@strUserID int,
	@intWeekNumber int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @StartDate DATE, @EndDate DATE

	SELECT @StartDate = MIN(TD.dDateTime),  
	       @EndDate = MAX(TD.dDateTime) 
	 FROM TimeDimension TD
	WHERE TD.ixYearWeek = @intWeekNumber


	If(@intDay=-1)
	BEGIN
			SELECT Distinct		ud.UD_NetLogin as Netlogin,
								spl.TeamID, 
								sdl.schedulingTeamName AS TeamFullName,
								ud.UD_UserID as ScheduledPersonID,
								ud.UD_DisplayLastName,
								ud.UD_DisplayFirstName,
								ud.UD_DisplayLastName + ', ' + ud.UD_DisplayFirstName  AS fullname
			  FROM   ScheduledPersonTeam_LINK as spl (Nolock)  
					INNER JOIN schedulingTeams sdl (Nolock) ON  spl.TeamID  = sdl.schedulingTeamId
					LEFT JOIN UserDetails as ud (Nolock)ON spl.ScheduledPersonID = ud.UD_UserID
			 WHERE spl.scheduledType=1 
			   AND spl.IsHomeTeam=1  
			   AND spl.TeamID IN ( Select usr.UR_SchedulingTeamID as TeamID
			        			     FROM UserRoles as usr (Nolock)
									 INNER JOIN schedulingTeams as st (nolock) on usr.UR_SchedulingTeamID = st.schedulingTeamId
						 			 where usr.UR_UserID = @strUserID 
									   AND usr.UR_RoleID IN (3,4,5)
									   and usr.UR_StartDate <= @EndDate
									   and isnull(usr.UR_EndDate,@StartDate) >= @StartDate
									   AND st.isActive=1 
									   AND st.locks=1 
									   AND st.schedulingTeamName NOT IN ('Freelancers','Archive','Other BBC')) 
									 AND spl.ScheduledPersonID NOT IN ( SELECT ScheduledPersonID 
																		FROM LockRequestsRestricted (NOLOCK) 
																		 WHERE WeekNumber=@intWeekNumber 
																		 AND Deleted=0) 
		       AND spl.scheduledType=1 
			   AND spl.IsHomeTeam=1 
			   AND isnull(spl.EndDate,@StartDate) >= @StartDate
			   AND spl.StartDate <= @EndDate			  
		     ORDER BY ud.UD_DisplayLastName,ud.UD_DisplayFirstName

	END
	ELSE
		BEGIN
			SELECT  DISTINCT
						ud.UD_NetLogin as Netlogin,
						sdl.schedulingTeamName AS TeamFullName,
						ud.UD_UserID as ScheduledPersonID,
						ud.UD_DisplayLastName + ', ' + ud.UD_DisplayFirstName  AS fullname,
						ud.UD_DisplayLastName,
						ud.UD_DisplayFirstName
			FROM   ScheduledPersonTeam_LINK AS spl (nolock)
					INNER JOIN Allocations AS al (nolock) ON al.AL_SchedulingTeamID = spl.TeamID
					INNER JOIN schedulingTeams sdl (nolock) ON sdl.schedulingTeamId = spl.TeamID
				   INNER JOIN TimeDimension TD (nolock) ON al.AL_WeekNumber = td.ixYearWeek 
				   INNER JOIN AllocationsScheduledPersons asp(nolock) on asp.ASP_SchedulingPersonID=spl.ScheduledPersonID
				   --AND asp.ASP_iDay = td.ixDayInWeek
				   LEFT JOIN UserDetails  ud (nolock) ON ud.UD_UserID = spl.ScheduledPersonID
			WHERE spl.scheduledType = 1
			  AND spl.IsHomeTeam = 1
			  AND al.AL_Status <> 9
			  AND TD.dDateTime BETWEEN spl.StartDate AND isnull(spl.EndDate,TD.dDateTime)
			  AND spl.TeamID IN ( SELECT usr.UR_SchedulingTeamID as TeamID
									FROM UserRoles AS usr (nolock)
								   INNER JOIN schedulingTeams AS st (nolock) ON usr.UR_SchedulingTeamID = st.schedulingTeamId
								   WHERE usr.UR_UserID = @strUserID
									 AND usr.UR_RoleID IN ( 3, 4, 5 )
									and usr.UR_StartDate <= @EndDate
									and isnull(usr.UR_EndDate,@StartDate) >= @StartDate
									 AND st.isActive = 1
									 AND st.locks = 1
									 AND st.schedulingTeamName NOT IN ('Freelancers', 'Archive', 'Other BBC' ))
				   AND td.ixYearWeek = @intWeekNumber
				   AND spl.ScheduledPersonID NOT IN (SELECT ScheduledPersonID
													  FROM LockRequests
													 WHERE WeekNumber = @intWeekNumber
													   AND iDay = @intDay
													   AND deleted = 0
												    )
			ORDER BY ud.UD_DisplayLastName,ud.UD_DisplayFirstName
		END
	
END