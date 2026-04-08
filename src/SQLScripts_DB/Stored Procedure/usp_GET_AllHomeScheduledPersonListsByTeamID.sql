USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_AllHomeScheduledPersonListsByTeamID]    Script Date: 1/21/2026 4:11:30 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_GET_AllHomeScheduledPersonListsByTeamID]   
  -- Add the parameters for the stored procedure here  
@teamid INT  
AS  
BEGIN  
 -- SET NOCOUNT ON added to prevent extra result sets from  
 -- interfering with SELECT statements.  
 SET NOCOUNT ON;  
      SELECT sp.UD_DisplayLastName + ' ' + sp.UD_DisplayFirstName  AS FullName,spt.scheduledType,sp.UD_UserID  AS ScheduledPersonID 
     FROM UserDetails sp WITH(NOLOCK)  
     JOIN ScheduledPersonTeam_LINK spt WITH (NOLOCK) on spt.ScheduledPersonID = SP.UD_UserID and    spt.IsHomeTeam = 1  AND isnull(spt.EndDate,'9999-01-01') >= getdate() and spt.StartDate<=getdate()   
     WHERE spt.TeamID = @teamid and spt.scheduledType=1 ORDER BY FullName ASC  
  
  
END