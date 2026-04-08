USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_IsDivisonalAdminByUser]    Script Date: 27/07/2025 14:58:32 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE  [dbo].[usp_get_IsDivisonalAdminByUser]

	-- Add the parameters for the stored procedure here
	@userid INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	select DISTINCT 1 
	  from UserRoles ur (Nolock)
     INNER JOIN Divisions d (Nolock) on d.DivisionID= ur.UR_DivisionId and d.isActive =1
	 inner join REF_Roles rr on rr.RoleID = ur.UR_RoleID
     where ur.UR_UserID = @userid
	   and rr.RoleName = 'Area Admin'

END