USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_Monthly_SchedulepersonTeamsDetails]    Script Date: 23/08/2023 15:49:31 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE OR ALTER PROCEDURE [dbo].[usp_get_Monthly_SchedulepersonTeamsDetails]
	-- Add the parameters for the stored procedure here
	@intschedulepersonid int
AS
 BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
 
  select distinct sp.ScheduledPersonID, 
         sp.DisplayName, 
		 sp.UserID, 
		 sp.StaffDetailsID,
		 sd.InternalEmail, 
		 sp.AdminNotes, 
		 sp.FWANotes, 
		 sptl.ScheduledPersonID, 
		 sptl.TeamID, 
		 team.schedulingTeamName as TeamName, 
		 sptl.IsHomeTeam, 
		 sptl.SortCode, 
		 sptl.CreatedBy, 
		 convert(varchar(30), sptl.CreatedDate,105) as CreatedDate,
		 convert(varchar(30), sptl.StartDate,105) as StartDate, 
		 convert(varchar(30), sptl.EndDate,105) as EndDate, 
		 sptl.LastUpdatedBy, 
		 convert(datetime,sptl.LastUpdatedDate, 103) as LastUpdatedDate, 
		 sptl.BackgroundColour, 
		 sptl.fontcolour, 
		 sptl.IsActive,
		 sptl.isDefault,
		 sptl.IsAvailable,   
		 sd.Forename,
		 sd.PreferredForename,
		 sd.NetLogin,
		 sd.Surname,
		 scp.JobTitle,
		 sd.Title,
		 sd.StaffNumber,
		 scp.AveDayLen,
		 scp.ShiftBreak,
		 sp.DisplayFirstName,
		 sp.DisplayLastName,
		 sptl.IsDefaultBGColour
     from [dbo].[ScheduledPeople] (NOLOCK) as sp
    INNER JOIN [dbo].[ScheduledPersonTeam_LINK] (NOLOCK) as sptl on sp.ScheduledPersonID = sptl.ScheduledPersonID
    INNER JOIN schedulingTeams (NOLOCK) as team on team.schedulingTeamId = sptl.TeamID and team.isActive=1
     LEFT JOIN StaffDetails (NOLOCK) sd on sd.StaffID = sp.StaffDetailsID
	 LEFT JOIN Staffconfig_Processed scp (nolock) ON sd.staffid = scp.staffid
	 AND CAST(GETDATE() AS Date) BETWEEN scp.StartDate AND ISNULL(scp.EndDate, CAST(GETDATE() AS Date)) 	 
    where sp.ScheduledPersonID = @intschedulepersonid 
	  AND cast(getdate() as date) between sptl.StartDate and CAST(isnull(sptl.EndDate,GETDATE()) AS DATE )
	  and sptl.scheduledType = 1 
    order by IsHomeTeam DESC

END
