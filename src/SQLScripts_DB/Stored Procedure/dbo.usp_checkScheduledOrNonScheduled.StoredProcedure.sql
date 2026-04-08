USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_checkScheduledOrNonScheduled]    Script Date: 28/04/2023 12:59:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE  OR   ALTER   PROCEDURE [dbo].[usp_checkScheduledOrNonScheduled] 
	-- Add the parameters for the stored procedure here
	@groupid int,
	@NetLogin varchar(100)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

select st.schedulingTeamId
from Users u (NOLOCK)
join StaffDetails sd (NOLOCK) on sd.NetLogin = u.NetLogin
INNER join ScheduledPeople sp  (NOLOCK) on sp.StaffDetailsID = sd.StaffID and u.UserID = sp.UserID
INNER join ScheduledPersonTeam_LINK spl (NOLOCK) on spl.ScheduledPersonID = sp.ScheduledPersonID   
AND isnull(EndDate,'9999-01-01') >= getdate() and StartDate<=getdate()
INNER join schedulingTeams st (NOLOCK) on st.schedulingTeamId= spl.TeamID  and st.isActive =1
WHERE  NOT EXISTS (SELECT 1 FROM  Staff_Web_Config_LeaveGroups_Link sdlc WHERE sd.NetLogin = sdlc.Login and (LeaveGroupID = @groupid) and isActive=1)
and u.NetLogin = @NetLogin
END