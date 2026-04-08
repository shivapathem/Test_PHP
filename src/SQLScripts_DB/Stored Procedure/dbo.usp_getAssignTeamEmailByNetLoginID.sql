USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_getAssignTeamEmailByNetLoginID]    Script Date: 06/08/2025 13:15:24 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER        PROCEDURE  [dbo].[usp_getAssignTeamEmailByNetLoginID]
	-- Add the parameters for the stored procedure here
	@scheduledPersonId varchar(50)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SELECT spl.ScheduledPersonID,spl.scheduledType,
    schedulingTeams.schedulingTeamId,schedulingTeams.schedulingTeamName ,schedulingTeams.Email
    FROM ScheduledPersonTeam_LINK as spl (NOLOCK)
    INNER JOIN UserDetails U (NoLOCK) on spl.ScheduledPersonID=U.UD_UserID
    INNER JOIN schedulingTeams (nolock) on schedulingTeams.schedulingTeamId =spl.TeamID AND schedulingTeams.isActive=1 
    WHERE spl.ScheduledPersonID = @scheduledPersonId 
    AND cast(getdate() as date) between spl.StartDate and isnull (spl.EndDate, cast(getdate() as date))
	ANd  spl.IsHomeTeam=1
END