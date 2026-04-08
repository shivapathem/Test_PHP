USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_StaffDetailsByTeamID]    Script Date: 24/11/2025 17:50:33 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_GET_StaffDetailsByTeamID]
@teamid INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
		SELECT DISTINCT UD_DisplayName DisplayName,
				UD_DisplayName AS FullName,
				UD_ExternalEmail as NonBBCEmail,
				UD_OfficePhone OfficeMobile,
				UD_InternalEmail as BBCEmail,
				UD_PersonalPhone as NonBBCPhNo ,
				UD_OfficeExtension  OfficeExtension,
				UD_TeampayStaffID StaffID,
				UD_StaffNumber StaffNumber,
				spt.scheduledType,
				UD_DisplayFirstName Forename,
				UD_DisplayFirstName PreferredForename,
				UD_DisplayLastName Surname,
				UD_UserID ScheduledPersonID 
			FROM UserDetails
			JOIN ScheduledPersonTeam_LINK spt on spt.ScheduledPersonID = UD_UserID and   isnull(spt.EndDate,'9999-01-01') >= getdate() 
			WHERE spt.TeamID = @teamid
			AND UD_NetLogin IS NOT NULL
			ORDER BY FullName
END