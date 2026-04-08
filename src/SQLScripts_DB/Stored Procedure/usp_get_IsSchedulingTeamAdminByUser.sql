USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_IsSchedulingTeamAdminByUser]    Script Date: 27/07/2025 14:56:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE  [dbo].[usp_get_IsSchedulingTeamAdminByUser]

	-- Add the parameters for the stored procedure here
	@userid INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SELECT TOP 1 ur.UR_RoleID RoleID,ur.UR_SchedulingTeamID as TeamID FROM UserRoles ur (nolock)
        INNER JOIN schedulingTeams stl (nolock) on stl.schedulingTeamId = ur.UR_SchedulingTeamID
        where ur.UR_RoleID= 3 and ur.UR_UserID  = @userid and ur.UR_StartDate <= convert(datetime,convert(varchar(10),getdate(),110),110) 
		and isnull(ur.UR_EndDate,'9999-12-01') >= convert(datetime,convert(varchar(10),getdate(),110),110)

END