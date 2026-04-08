USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[ReadStaffForReports]    Script Date: 03/12/2025 20:34:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 25-04-2022
-- Description:	This SP is used to get staff details by team
-- =============================================
CREATE OR ALTER      PROCEDURE [dbo].[ReadStaffForReports] 
	@teamId INT
AS
BEGIN
	SET NOCOUNT ON;

    select distinct SP.UD_DisplayName AS FullName,
					SP.UD_TeampayStaffID as StaffNumber,
					SPTL.TeamID DepartmentId,
					ISNULL(Sp.UD_NetLogin, N'') AS Login, 
					0 AS HidePerson,
					SPTL.SortCode,
					SCP.UC_EFT EFT,
					SCP.UC_JobTitle AS TeamDescription,
					isNull(UWC.HideStaffList, 0) HidePerson
	from UserDetails SP  WITH (NOLOCK)
		INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = SP.UD_UserID
		INNER JOIn UserConfigs SCP ON SCP.UC_UserID = sp.UD_UserID 
		Inner JOIN schedulingTeams ST ON ST.schedulingTeamId = SPTL.TeamID
		LEFT JOIN User_Web_Config UWC ON UWC.SchedulingTeamId = ST.schedulingTeamId AND UWC.Login = SP.UD_NetLogin
		WHERE SPTL.TeamID = @teamId and sptl.scheduledType = 1 and SPTL.IsHomeTeam = 1 
		AND GETDATE() between sptl.StartDate and sptl.EndDate
		AND GETDATE() between SCP.UC_StartDate and SCP.UC_EndDate
		 ORDER BY            FullName

END