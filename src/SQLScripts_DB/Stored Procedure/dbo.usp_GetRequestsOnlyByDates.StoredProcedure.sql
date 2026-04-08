USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetRequestsOnlyByDates]    Script Date: 28/10/2022 16:05:23 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_GetRequestsOnlyByDates]
	-- Add the parameters for the stored procedure here
	@strUser varchar(50), @strStartDate varchar(50), @strEndDate varchar(50), @intAdmin int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	Declare @strQuery VARCHAR(MAX),@conditionString AS NVARCHAR(MAX);

		IF(@intAdmin = 0)
			SET @conditionString = 'AND (Staff_Web_Config_LeaveGroups_Link.Admin = 0)'
		ELSE
			SET @conditionString = 'AND (Staff_Web_Config_LeaveGroups_Link.Admin >= 1)'

	SET	@strQuery='SELECT Requests.ID as RequestID, Requests.dDate, Requests.RequestType, Requests.Login, Requests.Approved, Requests.UserComments, Requests.Created, Requests.Comments, 
  Requests.Unlikely, Requests.ShortNotice, Requests.ID, RequestTypes.GroupID
               FROM  Requests (Nolock)
               INNER JOIN RequestTypes (Nolock) ON Requests.RequestType = RequestTypes.ID
               WHERE (Requests.dDate >= CONVERT(DATETIME, '''+@strStartDate+''', 102)) 
               AND (Requests.dDate < CONVERT(DATETIME, '''+@strEndDate+''', 102)) 
               AND (Requests.Deleted = 0) 
               AND (RequestTypes.GroupID IN
                         (SELECT Staff_Web_Config_LeaveGroups_Link.LeaveGroupID
                          FROM Staff_Web_Config_LeaveGroups_Link (Nolock)
			  WHERE (Staff_Web_Config_LeaveGroups_Link.IsActive = 1) and (Staff_Web_Config_LeaveGroups_Link.Login = '''+@strUser+''')'+@conditionString+'
              AND  (Requests.Deleted = 0) GROUP BY Staff_Web_Config_LeaveGroups_Link.LeaveGroupID))
               ORDER BY  Requests.Created'
exec(@strQuery);
 --select @strQuery
END
