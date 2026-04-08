USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetLocksDenied]    Script Date: 06/08/2025 13:11:26 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_GetLocksDenied]
	-- Add the parameters for the stored procedure here
	@intweekno int,
	@userId int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
SELECT	lr.ID,
		ud.UD_NetLogin as Login,
		lr.ScheduledPersonID,
		lr.History,
		spl.TeamID, 
		UD_DisplayName AS FullName
FROM LockRequestsRestricted lr (Nolock)
         INNER JOIN ScheduledPersonTeam_LINK as spl (Nolock) ON spl.ScheduledPersonID = lr.ScheduledPersonID 
					AND spl.scheduledType=1 AND spl.IsHomeTeam=1
					AND convert(datetime,spl.StartDate,110) <= convert(datetime,getdate(),110) and convert(datetime,spl.EndDate,110) > convert(datetime,getdate(),110)
         LEFT JOIN UserDetails  ud (Nolock) ON lr.ScheduledPersonID = ud.UD_UserID 
		 WHERE (lr.WeekNumber = @intweekno) AND  (lr.Deleted = 0)
					AND spl.TeamID IN 
					(Select usr.UR_SchedulingTeamID as TeamID FROM 
								UserRoles as usr (Nolock)
							INNER JOIN schedulingTeams as st (nolock) on usr.UR_SchedulingTeamID = st.schedulingTeamId
								where usr.UR_UserID = @userId AND usr.UR_RoleID IN (3,4,5)
							and usr.UR_StartDate <= convert(datetime,convert(varchar(10),getdate(),110),110) 
							and isnull(usr.UR_EndDate,'9999-12-01') >= convert(datetime,convert(varchar(10),getdate(),110),110) AND st.isActive=1)
END

