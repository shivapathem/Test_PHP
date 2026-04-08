USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_notsystemAdminList]    Script Date: 03/08/2022 22:14:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_notsystemAdminList] 
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
		Select sd.NetLogin,u.UserID,CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '') 
	THEN (sd.Surname + ', ' + sd.Forename) ELSE (sd.Surname + ', ' + sd.PreferredForename)    END ELSE (sp.DisplayLastName + ', ' + sp.DisplayFirstName ) END AS FullName,sd.Forename,sd.EmpNumber,sd.StaffNumber,sd.Surname,
			sd.StaffID,sd.NetLogin,sd.InternalEmail From StaffDetails sd  WITH (NOLOCK)
			 JOIN Users u  WITH (NOLOCK) on u.NetLogin = sd.NetLogin
			 LEFT JOIN ScheduledPeople as sp WITH (NOLOCK) ON sp.StaffDetailsID =sd.StaffID
			 where u.UserID NOT IN (Select UserID from UserSystemRole_Link WITH (NOLOCK)  where RoleId = 1)
			  order by FullName asc
END
