USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_UserInfo]    Script Date: 19/12/2024 19:12:31 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_get_UserInfo]
       -- Add the parameters for the stored procedure here
       @userID INT = 0,
        @netlogin VARCHAR(60) = NULL 
       
AS
BEGIN
SET NOCOUNT ON 

	DECLARE @cols AS NVARCHAR(MAX),
			@query  AS NVARCHAR(MAX),
			@conditionString AS NVARCHAR(MAX),
			@conditionString2 AS NVARCHAR(MAX)

	SET @conditionString = ''
	SET @conditionString2 = ''
		
	--IF NOT EXISTS (SELECT 1 FROM UserSystemRole_Link  where UserId=@userID)
	if @userID = 0 and  @netlogin is NULL
	Begin
		return
	End

	if isnull(@userID,0) = 0
	Begin 
		select @userID=userid from Users where netlogin= @netlogin;
	End
	
	if @netlogin is NULL
	Begin 
		select @netlogin=netlogin from Users where userid= @userID;
	End
	
	SET @conditionString = 'WHERE  u.NetLogin = '''+ @netlogin +''' '
	SET @conditionString2 = N'WHERE   da.userid = COALESCE ('+ convert(varchar, @userID)+N',da.UserID)'

	SELECT @cols = STUFF((SELECT ',' + QUOTENAME(RoleName) 
							from REF_Roles
							where IsActive = 1 AND RoleID != 2
							group by RoleName,isSequence
							order by isSequence
						FOR XML PATH(''), TYPE
						).value('.', 'NVARCHAR(MAX)'),1,1,'')

	SET @query = N'SELECT  ScheduledPersonID,schedulingTeamId ,schedulingTeamName,hasXmasPoints,hasGridChecks,hasHandovers,showProductionView,isDefault, isWhosIn,SortCode,
					scheduledType,StaffNumber,rota,DivisionId,maskAfter,Todayactivehometeam ,IsHomeTeam as ''Home Team'',' + @cols + N'
					from 
					 (
					   SELECT  utr.TeamID,st.schedulingTeamId,st.schedulingTeamName,sp.ScheduledPersonID,r.RoleID,r.RoleName,
								case when (
								select top 1 ScheduledType from ScheduledPersonTeam_LINK (nolock) where  sp.ScheduledPersonID = ScheduledPersonID
								and isnull(convert(datetime,convert(varchar(30),EndDate,110),110),''9999-01-01'') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								and convert(datetime,convert(varchar(30),StartDate,110),110) > convert(datetime,convert(varchar(30),getdate(),110),110)
								and ScheduledType = 1
								and stl.TeamId = TeamId
								) = 1 then 1
								else stl.scheduledType
								END
								as scheduledType,
								case when (
								select top 1 ScheduledType from ScheduledPersonTeam_LINK (nolock) where  sp.ScheduledPersonID = ScheduledPersonID
								and isnull(convert(datetime,convert(varchar(30),EndDate,110),110),''9999-01-01'') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								and convert(datetime,convert(varchar(30),StartDate,110),110) <= convert(datetime,convert(varchar(30),getdate(),110),110)
								and ScheduledType = 1
								and stl.TeamId = TeamId and stl.IsHomeTeam=1
								) = 1 then ''Current''
								else ''Future''
								END as Todayactivehometeam,
								stl.SortCode, stl.isDefault, stl.isWhosIn,st.hasXmasPoints,st.hasGridChecks,st.hasHandovers,
								st.showProductionView, sd.StaffNumber,stl.IsHomeTeam,
								stl.rota,temp.DivisionId,st.maskAfter
						from Users (nolock) u
							JOIN StaffDetails  (nolock) sd on sd.NetLogin = u.NetLogin
							JOIN ScheduledPeople (nolock) sp on u.UserID = sp.UserID
							JOIN ScheduledPersonTeam_LINK (nolock) stl on  sp.ScheduledPersonID = stl.ScheduledPersonID
								and getdate() <= isnull(enddate,getdate())
							Join schedulingTeams (nolock) st on st.schedulingTeamId = stl.TeamID and st.isActive = 1
							left join (select T2.schedulingTeamId as TeamID,T2.schedulingTeamName as TeamName,da.DivisionId as DivisionId 
											from DivisonalAdmin (nolock) da
											Join schedulingTeamDivision_Link (nolock) stl on stl.divisionId = da.DivisionId and stl.isActive = 1 and da.IsActive = 1
											join schedulingTeams (nolock) T2 on T2.schedulingTeamId = stl.schedulingTeamId and T2.isActive = 1
											join REF_Roles RR on RR.RoleID = da.RoleID and RR.RoleName = ''Area Admin''
											' + @conditionString2 + N' ) temp on temp.TeamID = stl.TeamID
							LEFT JOIN 							
							( select TeamID, userid, roleid
							    from UserTeamRole_LINK (nolock)  
							   where UserID = '+cast(@userID as varchar) + '
							     and getdate() between StartDate and EndDate 
							  union
							  select stl.schedulingTeamId as TeamID, da.UserId, da.RoleId 
								from DivisonalAdmin (nolock) da
								Join schedulingTeamDivision_Link (nolock) stl on stl.divisionId = da.DivisionId 
								join REF_Roles RR on RR.RoleID = da.RoleID and RR.RoleName <>  ''Area Admin''
							   WHERE   da.userid = '+cast(@userID as varchar) + '
							    and stl.isActive = 1 and da.IsActive = 1
							) utr on utr.userid = u.userid and stl.teamid = utr.teamid
							LEFT JOIN REF_Roles (nolock) r on r.RoleID = utr.RoleID 
							'+ @conditionString + ' 
					) x
					pivot 
					(
						MAX    (RoleID)
						for RoleName in (' + @cols + N')
					) p Order by schedulingTeamName ASC,isDefault Desc '
					
					
			
	exec sp_executesql @query
END

