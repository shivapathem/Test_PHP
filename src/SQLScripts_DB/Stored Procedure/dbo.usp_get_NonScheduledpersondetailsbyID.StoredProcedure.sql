USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_NonScheduledpersondetailsbyID]    Script Date: 24/07/2025 14:50:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR  ALTER  PROCEDURE [dbo].[usp_get_NonScheduledpersondetailsbyID] 
@teamid INT,
@scheduledpersonId INT

AS
BEGIN

	SET NOCOUNT ON;

		SELECT UD_DisplayName AS userDisplayName,rr.RoleName,rr.RoleID
     from UserDetails ud (nolock)
					JOIN ScheduledPersonTeam_LINK st (nolock) on st.ScheduledPersonID = ud.UD_UserID 
					and  convert(date, GETDATE(), 105) between convert(date, st.StartDate, 105) and isnull(convert(date,st.EndDate,105),convert(date, GETDATE(), 105))
					--Join StaffDetails sd (nolock)  on sd.StaffID = sp.StaffDetailsID
					--Join Users u (nolock) on u.NetLogin = sd.NetLogin
				    join UserRoles ur (nolock) on  ur.UR_SchedulingTeamID= st.TeamID AND ur.UR_UserID = ud.UD_UserID and ur.UR_RoleID IN (select RoleID from REF_Roles where RoleID IN(3,4,5,6)) 
						and ur.UR_StartDate <= convert(DATE,convert(varchar(10),getdate(),110),110)
						and ur.UR_EndDate >= convert(DATE,convert(varchar(10),getdate(),110),110)
				 JOIN REF_Roles rr (nolock) on rr.RoleID = ur.UR_RoleID and rr.RoleID IN(3,4,5,6)
		where UD_UserID = @scheduledpersonId  and st.scheduledType=0 and st.TeamID=@teamid;



END