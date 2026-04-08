USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveRequestsAvailability]    Script Date: 28/10/2022 16:17:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_get_LeaveRequestsAvailability]
@StartDate varchar(100),
@EndDate varchar(100),
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

SET @query ='Select   LR.LeaveType,LR.dDate, LR.Amount
               From           Staff_Web_Config_LeaveGroups_Link SW (nolock)
               Inner Join     leave_types LT (nolock) On SW.LeaveGroupID = LT.GroupID
               Inner Join     LeaveRequestsAvailability LR (nolock) On LT.ID = LR.LeaveType
                WHERE      (SW.IsActive=1) AND       (SW.Login = '''+@NetLogin+''')'+@conditionString+'
                AND               (LR.dDate >= CONVERT(DATETIME, '''+@StartDate+''', 102)) 
               AND               (LR.dDate <= CONVERT(DATETIME, '''+@EndDate+''', 102)) '

			   exec sp_executesql @query
END

 
  