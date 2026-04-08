USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_EFTByTeamByStaffNumber]    Script Date: 10/09/2025 18:30:11 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER           PROCEDURE [dbo].[usp_get_EFTByTeamByStaffNumber]
@teamid INT,
@scheduledpersonid INT

AS
BEGIN
	SET NOCOUNT ON;
	--fetch EFT
		SELECT  ISNULL(scp.UC_EFT,0)AS EFT, sp.UD_UserID as ScheduledPersonID
			   FROM            
			   ScheduledPersonTeam_LINK as spl WITH (NOLOCK)
			   INNER JOIN UserDetails as sp WITH (NOLOCK) ON sp.UD_UserID =spl.ScheduledPersonID
			   LEFT JOIN UserConfigs scp (nolock) ON sp.UD_UserID = scp.UC_UserID
					AND convert(datetime,scp.UC_EndDate,110) >= convert(datetime,convert(varchar,GETDATE(),110),110)
					AND convert(datetime, scp.UC_StartDate, 110) <= convert(datetime, convert(varchar,GETDATE(),110), 110)					
					
				
			   WHERE           (spl.TeamID = @teamid) AND (sp.UD_UserID = @scheduledpersonid)
END