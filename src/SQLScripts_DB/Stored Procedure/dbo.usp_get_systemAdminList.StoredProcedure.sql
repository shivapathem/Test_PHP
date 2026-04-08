USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_systemAdminList]    Script Date: 03/08/2022 22:10:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_systemAdminList] 
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- select statements for procedure here
SELECT DISTINCT CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '') 
	THEN (sd.Surname + ', ' + sd.Forename) ELSE (sd.Surname + ', ' + sd.PreferredForename)    END ELSE (sp.DisplayLastName + ', ' + sp.DisplayFirstName ) END AS FullName,u.UserID,sd.Forename,sd.StaffID,u.NetLogin 
	FROM UserSystemRole_Link ut  WITH (NOLOCK)
	JOIN Users u  WITH (NOLOCK) on u.UserID = ut.UserId
    JOIN StaffDetails sd  WITH (NOLOCK) on sd.NetLogin = u.NetLogin
	LEFT JOIN ScheduledPeople as sp WITH (NOLOCK) ON sp.StaffDetailsID =sd.StaffID
    WHERE RoleId = 1
	ORDER BY u.NetLogin asc
	
END
