USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_GetScheduledPeopleUserList]    Script Date: 17/07/2025 15:04:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_GetScheduledPeopleUserList]
	-- Add the parameters for the stored procedure here
	@userID INT,
	@teamid VARCHAR(60) ,
	@searchuservalue varchar(60),
	@formid int,
	@excludeNoTeam int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @SQL NVARCHAR(MAX)
	DECLARE @isHomeTeam int	
	DECLARE @searchstring varchar(20)
	DECLARE @conditionstring varchar(max)
	DECLARE @noTeamId int =  -1
	
	if @excludeNoTeam = 1
	BEGIN
		SELECT @noTeamId = schedulingTeamId 
		  FROM schedulingTeams 
		 WHERE LOWER(schedulingTeamName) = 'archive' 
		   AND isActive = 1
	END
	SET @isHomeTeam = 1
	
	if @teamid = ''
	SET @teamid = NULL
	SET @conditionstring = '';

	IF @teamid IS NULL
	BEGIN
		IF EXISTS(select 1 
					from UserRoles USRL (NOLOCK) 
					JOIN RolePermissionForm_LINK rpl (NOLOCK) on rpl.RoleID = 1 
				where USRL.UR_UserID = @userid 
					and USRL.UR_RoleID = 1 
					AND  FormID = @formid)
				BEGIN
					SELECT DISTINCT ud.UD_DisplayName DisplayName,ud.UD_DisplayFirstName as Forename,ud.UD_DisplayLastName as Surname, ud.UD_EmpNumber EmpNumber
						FROM  UserDetails ud (NOLOCK)
				INNER  JOIN ScheduledPersonTeam_LINK T2 (NOLOCK) ON T2.ScheduledPersonID = ud.UD_UserID
				WHERE   T2.IsHomeTeam =@isHomeTeam  AND isnull(T2.EndDate,'9999-01-01') >= getdate()
				AND ud.UD_DisplayName LIKE '%'+ @searchuservalue +'%'
				AND T2.TeamID != @noTeamId
		END
		ELSE
		BEGIN
			SELECT DISTINCT ud.UD_DisplayName DisplayName,
							ud.UD_DisplayFirstName as Forename,
							ud.UD_DisplayLastName as Surname,
							ud.UD_EmpNumber EmpNumber
				FROM  UserDetails ud (NOLOCK)
					INNER  JOIN ScheduledPersonTeam_LINK T2 (NOLOCK) ON T2.ScheduledPersonID = ud.UD_UserID
					WHERE   T2.IsHomeTeam =@isHomeTeam  AND isnull(T2.EndDate,'9999-01-01') >= getdate()
					AND ud.UD_DisplayName LIKE '%'+ @searchuservalue +'%'
					AND T2.TeamID != @noTeamId
			UNION
			SELECT DISTINCT ud.UD_DisplayName DisplayName,
							ud.UD_DisplayFirstName as Forename,
							ud.UD_DisplayLastName as Surname,
							ud.UD_EmpNumber EmpNumber
				FROM  UserDetails ud (NOLOCK)
					INNER  JOIN ScheduledPersonTeam_LINK T2 (NOLOCK) ON T2.ScheduledPersonID = ud.UD_UserID
				INNER join (select DISTINCT st.schedulingTeamId,ur.UR_DivisionId 
				           from UserRoles ur (NOLOCK)
							join schedulingTeams st (NOLOCK) on st.divisionid = ur.UR_DivisionId  and st.isActive=1 
							JOIN RolePermissionForm_LINK rpl (NOLOCK) on rpl.RoleID = 2 and FormID = @formid
							JOIN REF_Roles RR ON ur.UR_RoleID = RR.RoleID AND RR.RoleName = 'Area Admin'
							where ur.UR_UserID = @userid )  as temp2 on temp2.schedulingTeamId = T2.TeamID
			WHERE T2.IsHomeTeam = @isHomeTeam  AND  isnull(T2.EndDate,'9999-01-01') >= getdate() 
				and T2.scheduledType = 1
				AND CHARINDEX(@searchuservalue, ud.UD_DisplayName) > 0 AND T2.TeamID != @noTeamId
		END
	END
	ELSE
	BEGIN
		SELECT  ud.UD_DisplayName DisplayName,
				ud.UD_DisplayFirstName as Forename,
				ud.UD_DisplayLastName as Surname,
				ud.UD_EmpNumber EmpNumber
				FROM  UserDetails ud (NOLOCK)
				INNER  JOIN ScheduledPersonTeam_LINK T2 (NOLOCK) ON T2.ScheduledPersonID = ud.UD_UserID
		WHERE  T2.TeamID = @teamid 
			AND ud.UD_DisplayName LIKE '%'+ @searchuservalue +'%'
			AND T2.IsHomeTeam =@isHomeTeam  AND isnull(T2.EndDate,'9999-01-01') >= getdate()
			AND T2.TeamID != @noTeamId
	END
END