USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_UserTeamByScheduledTypeByStaffid]    Script Date: 26/08/2025 16:32:37 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_UserTeamByScheduledTypeByStaffid] 
	-- Add the parameters for the stored procedure here
	@staffdetailsid varchar(50),
	@scheduledtype int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
 SELECT team.schedulingTeamId, team.schedulingTeamName ,U.UD_StaffNumber AS StaffID,sptl.ScheduledPersonID
FROM  UserDetails AS U with(nolock)
    INNER JOIN [dbo].[ScheduledPersonTeam_LINK] (NOLOCK) AS sptl on u.UD_UserID = sptl.ScheduledPersonID
    INNER JOIN schedulingTeams as team on team.schedulingTeamId = sptl.TeamID and team.isActive = 1
WHERE U.UD_StaffNumber  = @staffdetailsid and 
convert(datetime,convert(varchar(30),isnull(sptl.EndDate,'9999-01-01'),110),110) >= convert(datetime,convert(varchar(30),getdate(),110),110) and sptl.isActive = 1 and sptl.scheduledType = @scheduledtype
ORDER BY sptl.isDefault DESC
END

