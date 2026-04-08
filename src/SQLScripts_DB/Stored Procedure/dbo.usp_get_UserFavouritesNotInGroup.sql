USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_UserFavouritesNotInGroup]    Script Date: 18/12/2024 20:31:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_get_UserFavouritesNotInGroup] 
@userId int,
 @intTeamID int
AS
BEGIN
 -- SET NOCOUNT ON added to prevent extra result sets from
 -- interfering with SELECT statements.
 SET NOCOUNT ON;
 DECLARE @isHomeTeam INT

 SET @isHomeTeam = 1

IF EXISTS(select 1 from UserSystemRole_Link (NOLOCK) where UserID = @userid and RoleID = 1)
BEGIN
	IF @intTeamID = 0
	BEGIN
		SELECT T2.TeamID, T4.Forename + N', ' + T4.Surname AS FullName,  T4.StaffNumber
		FROM  ScheduledPeople T1
		INNER  JOIN ScheduledPersonTeam_LINK T2 ON T2.ScheduledPersonID = T1.ScheduledPersonID
		INNER  JOIN schedulingTeams T3 ON T3.schedulingTeamId = T2.TeamID
		LEFT JOIN StaffDetails T4 ON T4.StaffID = T1.StaffDetailsID
		WHERE t2.IsActive = @isHomeTeam 
		 and T2.IsHomeTeam = @isHomeTeam   
		 AND isnull(T2.EndDate,'9999-01-01') >= getdate() 
		 and T2.scheduledType = 1
		ORDER BY DisplayName DESC
	END
	ELSE
	BEGIN
		SELECT T2.TeamID, T4.Forename + N', ' + T4.Surname AS FullName,  T4.StaffNumber
		  FROM  ScheduledPeople T1
		 INNER  JOIN ScheduledPersonTeam_LINK T2 ON T2.ScheduledPersonID = T1.ScheduledPersonID
		 INNER  JOIN schedulingTeams T3 ON T3.schedulingTeamId = T2.TeamID
		  LEFT JOIN StaffDetails T4 ON T4.StaffID = T1.StaffDetailsID
		 WHERE t2.IsActive = @isHomeTeam 
		   and T2.IsHomeTeam = @isHomeTeam   
		   AND isnull(T2.EndDate,'9999-01-01') >= getdate() 
		   and T2.scheduledType = 1 
		   and T2.TeamID = @intTeamID
		 ORDER BY DisplayName DESC
	END
END
	ELSE
	BEGIN
		IF @intTeamID = 0
		BEGIN
			SELECT T2.TeamID, T4.Forename + N', ' + T4.Surname AS FullName,  T4.StaffNumber
			FROM  ScheduledPeople T1
					  INNER  JOIN ScheduledPersonTeam_LINK T2 ON T2.ScheduledPersonID = T1.ScheduledPersonID
					  INNER  JOIN schedulingTeams T3 ON T3.schedulingTeamId = T2.TeamID
					  LEFT JOIN StaffDetails T4 ON T4.StaffID = T1.StaffDetailsID
			WHERE t2.IsActive = @isHomeTeam and T2.IsHomeTeam = @isHomeTeam   AND isnull(T2.EndDate,'9999-01-01') >= getdate()
			  and  T2.TeamID IN (SELECT DISTINCT	st.schedulingTeamId
										from Schedulingteams as st
							inner join UserTeamRole_LINK as ut on st.schedulingTeamId = ut.TeamID
							left join [dbo].[schedulingTeamDivision_Link] as std on std.schedulingTeamId = st.schedulingTeamId
							left join [dbo].[Divisions] as div on div.DivisionID = std.divisionId
							left join [dbo].[maskType] as mt on mt.maskTypeId = st.maskType
						where ut.UserID = @userid and ut.StartDate <= convert(datetime,convert(varchar(10),getdate(),110),110)
							and ut.EndDate >= convert(datetime,convert(varchar(10),getdate(),110),110)
						UNION
						SELECT DISTINCT
							st.schedulingTeamId
						from Schedulingteams as st
							left join [dbo].[schedulingTeamDivision_Link] as std on std.schedulingTeamId = st.schedulingTeamId 
							left join [dbo].[Divisions] as div on div.DivisionID = std.divisionId and div.isActive = 1
							left join [dbo].[maskType] as mt on mt.maskTypeId = st.maskType
							inner join DivisonalAdmin  as da on da.DivisionID = std.divisionId and da.IsActive = 1
							inner join REF_Roles rr on rr.RoleID = da.RoleId
						where da.UserID = @userid
						  and rr.RoleName = 'Area Admin'
			  ) and T2.scheduledType = 1
			ORDER BY DisplayName DESC
		END
		ELSE
		BEGIN
			SELECT T2.TeamID, T4.Forename + N', ' + T4.Surname AS FullName,  T4.StaffNumber
			FROM  ScheduledPeople T1
					  INNER  JOIN ScheduledPersonTeam_LINK T2 ON T2.ScheduledPersonID = T1.ScheduledPersonID
					  INNER  JOIN schedulingTeams T3 ON T3.schedulingTeamId = T2.TeamID
					  LEFT JOIN StaffDetails T4 ON T4.StaffID = T1.StaffDetailsID
			WHERE t2.IsActive = @isHomeTeam and T2.IsHomeTeam = @isHomeTeam   AND isnull(T2.EndDate,'9999-01-01') >= getdate()
			  and  T2.TeamID IN ( SELECT DISTINCT st.schedulingTeamId
									from Schedulingteams as st
									inner join UserTeamRole_LINK as ut on st.schedulingTeamId = ut.TeamID
									 left join [dbo].[schedulingTeamDivision_Link] as std on std.schedulingTeamId = st.schedulingTeamId
									 left join [dbo].[Divisions] as div on div.DivisionID = std.divisionId
									 left join [dbo].[maskType] as mt on mt.maskTypeId = st.maskType
									where ut.UserID = @userid and ut.StartDate <= convert(datetime,convert(varchar(10),getdate(),110),110)
									  and ut.EndDate >= convert(datetime,convert(varchar(10),getdate(),110),110)
									UNION
								  SELECT DISTINCT
										 st.schedulingTeamId
									from Schedulingteams as st
									left join [dbo].[schedulingTeamDivision_Link] as std on std.schedulingTeamId = st.schedulingTeamId 
									left join [dbo].[Divisions] as div on div.DivisionID = std.divisionId and div.isActive = 1
									left join [dbo].[maskType] as mt on mt.maskTypeId = st.maskType
								   inner join DivisonalAdmin  as da on da.DivisionID = std.divisionId and da.IsActive = 1
								   inner join REF_Roles rr on rr.RoleID = da.RoleId
								   where da.UserID = @userid
									 and rr.RoleName = 'Area Admin'
			                       ) and T2.scheduledType = 1 and T2.TeamID = @intTeamID
			ORDER BY DisplayName DESC
		END
	END
END
