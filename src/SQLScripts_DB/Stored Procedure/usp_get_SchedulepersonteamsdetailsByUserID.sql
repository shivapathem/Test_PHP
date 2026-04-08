USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_SchedulepersonteamsdetailsByUserID]    Script Date: 25/08/2025 20:54:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER        PROCEDURE [dbo].[usp_get_SchedulepersonteamsdetailsByUserID]
	-- Add the parameters for the stored procedure here
	@userID int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
select
		distinct
    --sp.UD_UserID as ScheduledPersonID,
			sp.UD_DisplayName as DisplayName , 
			sp.UD_UserID as UserID, 
			sp.UD_TeampayStaffID as StaffDetailsID,
			sp.UD_InternalEmail as InternalEmail,
			sp.UD_AdminNotes as AdminNotes,
			sp.UD_FWANotes as FWANotes,
			sptl.ScheduledPersonID,
			sptl.TeamID, 
			team.schedulingTeamName as TeamName, 
			sptl.IsHomeTeam, 
			sptl.SortCode,
			sptl.CreatedBy, convert(varchar(30),
			sptl.CreatedDate,105) as CreatedDate,
			convert(varchar(30), sptl.StartDate,105) as StartDate,
			convert(varchar(30),sptl.EndDate,105) as EndDate,
			sptl.LastUpdatedBy,
			convert(datetime,sptl.LastUpdatedDate, 103) as LastUpdatedDate,
			sptl.BackgroundColour,
			sptl.fontcolour, 
			sptl.IsActive,
			sptl.isDefault,
			sptl.IsAvailable,
			sp.UD_DisplayFirstName as Forename,
			sp.UD_DisplayFirstName as PreferredForename,
			sp.UD_NetLogin as NetLogin,
			sp.UD_DisplayLastName as Surname,
			scp.JobTitle,
			--sd.Title,
			sp.UD_StaffNumber as StaffNumber,
			scp.AveDayLen,
			scp.ShiftBreak,
			sp.UD_DisplayFirstName as DisplayFirstName,
			sp.UD_DisplayLastName as DisplayLastName
from UserDetails (NOLOCK) as sp
    INNER JOIN [dbo].[ScheduledPersonTeam_LINK] (NOLOCK) as sptl on sp.UD_UserID = sptl.ScheduledPersonID
    INNER JOIN schedulingTeams (NOLOCK) as team on team.schedulingTeamId = sptl.TeamID and team.isActive=1
	LEFT JOIN Staffconfig_Processed scp (nolock) ON sp.UD_TeampayStaffID = scp.staffid
							 AND CAST(GETDATE() AS Date) BETWEEN scp.StartDate AND ISNULL(scp.EndDate, CAST(GETDATE() AS Date)) 
where sp.UD_UserID = @userID and  
convert(datetime,convert(varchar(30),isnull(sptl.EndDate,'9999-01-01'),110),110) >= convert(datetime,convert(varchar(30),getdate(),110),110) 
and sptl.isActive = 1 and sptl.scheduledType = 1 
order by IsHomeTeam DESC
END