USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_Schedulepersonteamsdetails]    Script Date: 10/07/2025 13:18:06 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER         PROCEDURE [dbo].[usp_get_Schedulepersonteamsdetails]
	-- Add the parameters for the stored procedure here
	@intschedulepersonid int
AS
 BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
 
  select distinct UD_UserID ScheduledPersonID, 
         UD_DisplayName DisplayName, 
		 UD_UserID UserID, 
		 sptl.TeamID, 
		 team.schedulingTeamName as TeamName, 
		 sptl.IsHomeTeam, 
		 sptl.SortCode, 
		 sptl.StartDate as StartDate, 
		 sptl.EndDate as EndDate, 
		 sptl.BackgroundColour, 
		 sptl.fontcolour, 
		 sptl.IsActive,
		 sptl.isDefault,
		 sptl.IsAvailable,   
		 UD_NetLogin NetLogin,
		 UD_StaffNumber StaffNumber,
		 UD_DisplayFirstName DisplayFirstName,
		 UD_DisplayLastName  DisplayLastName,
		 sptl.IsDefaultBGColour
     from UserDetails (NOLOCK) as sp
    INNER JOIN [dbo].[ScheduledPersonTeam_LINK] (NOLOCK) as sptl on sp.UD_UserID = sptl.ScheduledPersonID
    INNER JOIN schedulingTeams (NOLOCK) as team on team.schedulingTeamId = sptl.TeamID and team.isActive=1
	 LEFT JOIN UserConfigs scp (nolock) ON UD_UserID = scp.UC_UserID
	 AND CAST(GETDATE() AS Date) BETWEEN scp.UC_StartDate AND ISNULL(scp.UC_EndDate, CAST(GETDATE() AS Date)) 	 
    where sp.UD_UserID = @intschedulepersonid 
	  and sptl.EndDate  >= CAST(GETDATE() AS DATE)
	  and sptl.scheduledType = 1 
    order by IsHomeTeam DESC

END