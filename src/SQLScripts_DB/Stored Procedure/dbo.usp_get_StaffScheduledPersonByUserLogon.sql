USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_StaffScheduledPersonByUserLogon]    Script Date: 27/07/2025 20:13:53 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_StaffScheduledPersonByUserLogon]
 @netlogin VARCHAR(500)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


SELECT UD_DisplayName AS userDisplayName, UD_UserID ScheduledPersonID, st.leaveSelectiveHide, ud_StaffNumber StaffNumber
            FROM UserDetails
              JOIN ScheduledPersonTeam_LINK spl on UD_UserID = spl.ScheduledPersonID and spl.IsHomeTeam = 1
              JOIN schedulingTeams st on st.schedulingTeamId = spl.TeamID
			   
              where UD_NetLogin = @netlogin and isnull(convert(datetime,EndDate,110),'9999-01-01') >= convert(datetime,convert(varchar,GETDATE(),110),110)
								  and (
								  convert(datetime, StartDate, 110) >= convert(datetime, convert(varchar,GETDATE(),110), 110) or
								  isnull(convert(datetime,EndDate,110),'9999-01-01') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								  and convert(datetime,StartDate,110) <= convert(datetime,convert(varchar(30),getdate(),110),110))
END