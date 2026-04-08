USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetLocksOptionsByScheduledPerson]    Script Date: 20/08/2025 13:59:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE  [dbo].[usp_GetLocksOptionsByScheduledPerson]
	-- Add the parameters for the stored procedure here
	@sheduledPersonId varchar(50)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	SELECT st.schedulingTeamId,st.locks,st.locksStart as LocksStart,st.locksWeekataTime as LocksRollWeek, st.locksEnd as LocksEnd,st.ConfirmedDays
    FROM UserDetails as sp (Nolock) 
    INNER JOIN ScheduledPersonTeam_LINK as stl (Nolock) ON stl.ScheduledPersonID = sp.UD_UserID AND stl.IsHomeTeam=1 AND ISNULL(stl.EndDate,'9999-01-01') >=convert(datetime,getdate(),102) AND stl.StartDate <= convert(datetime,getdate(),102)
    INNER JOIN schedulingTeams as st (Nolock) ON stl.TeamID = st.schedulingTeamId
    WHERE (sp.UD_UserID = @sheduledPersonId)
END