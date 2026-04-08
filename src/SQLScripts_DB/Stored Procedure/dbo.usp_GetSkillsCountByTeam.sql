USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetSkillsCountByTeam]    Script Date: 12/09/2025 21:59:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 25-05-2022
-- Description:	Usted to get skill details by team
-- =============================================
CREATE OR ALTER   PROCEDURE [dbo].[usp_GetSkillsCountByTeam] 
	@teamId INT
AS
BEGIN
	SELECT          UD.UD_DisplayName AS FullName,
					COUNT(SP.programmename) AS skillscount,
					UD.UD_StaffNumber StaffNumber, 
                    SP.TeamID,
					ST.schedulingTeamName AS DepartmentName
FROM  UserDetails UD(NOLOCK) 
Inner Join ScheduledPersonTeam_LINK SPL(NOLOCK) ON SPL.ScheduledPersonID = UD.UD_UserID AND SPL.IsHomeTeam = 1
inner join schedulingTeams ST(NOLOCK) ON SPL.TeamID = ST.schedulingTeamId
INNER JOIN      skills_programmes_staff_link SPT(NOLOCK) ON UD.UD_UserID = SPT.UserID 
INNER JOIN      skills_programmes SP(NOLOCK) ON SPT.programmes_id = SP.ID 
AND SP.TeamID = ST.schedulingTeamId
WHERE           (ST.schedulingTeamId = @teamId)
            GROUP BY        UD.UD_DisplayName, UD.UD_StaffNumber, SP.TeamID, ST.schedulingTeamName
            HAVING          (SP.TeamID = @teamId)
            ORDER BY        UD.UD_DisplayName
END