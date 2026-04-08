USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveDefaultamounts]    Script Date: 07/08/2025 10:29:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER         PROCEDURE [dbo].[usp_get_LeaveDefaultamounts]
@isAdmin  INT,
@NetLogin varchar(100)

AS

BEGIN
-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

DECLARE	@query  AS NVARCHAR(MAX),
			    @conditionString AS NVARCHAR(MAX)

	IF(@isAdmin = 0)
			SET @conditionString = 'AND  (ISNULL(SW.Admin, 0) = 0)'
		ELSE
			SET @conditionString = 'AND  (ISNULL(SW.Admin, 0) >= 1)'

SET @query = 'Select   LT.ID As TypeID,LT.defaultamounts
               From        Staff_Web_Config_LeaveGroups_Link SW (nolock)
               Inner Join  leave_types LT (nolock) On SW.LeaveGroupID = LT.GroupID
			  Where  (SW.IsActive= 1) and (SW.Login = '''+@NetLogin+''')'+@conditionString+'
			   Group By     LT.ID, LT.defaultamounts'; 


			   exec sp_executesql @query
END