USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetWeeklyRequests]    Script Date: 07/08/2025 20:07:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_GetWeeklyRequests]
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

SET	@strQuery='SELECT Requests.dDate, 
Requests.RequestType, 
LOWER(Requests.Login) as Login, 
Requests.isOK, 
Requests.Approved, 
usercomments AS UserComments, 
Requests.Created, 
comments AS Comments, 
Requests.Unlikely, 
Requests.ShortNotice, 
Requests.ID AS RequestID, 
RequestTypes.GroupID, 
ud.UD_DisplayName AS FullName,
Requests.ScheduledPersonID
FROM  Staff_Web_Config_LeaveGroups_Link (nolock)
INNER JOIN LeaveRequestGroups (nolock) ON Staff_Web_Config_LeaveGroups_Link.LeaveGroupID = LeaveRequestGroups.ID 
INNER JOIN RequestTypes (NoLock) ON LeaveRequestGroups.ID = RequestTypes.GroupID
INNER JOIN Requests (NoLock) ON Requests.RequestType = RequestTypes.ID  
INNER JOIN UserDetails ud(nolock) on ud.UD_UserID=Requests.ScheduledPersonID
--LEFT JOIN StaffDetails as Staff (NoLock) ON sp.StaffDetailsID = Staff.StaffID
WHERE    (Staff_Web_Config_LeaveGroups_Link.IsActive = 1) AND (Staff_Web_Config_LeaveGroups_Link.Login = '''+@strUser+''')'+@conditionString+'
               AND               (Requests.dDate >= CONVERT(DATETIME, '''+@strStartDate+''', 102)) 
               AND               (Requests.dDate <= CONVERT(DATETIME, '''+@strEndDate+''', 102)) 
               AND               (Requests.Deleted = 0)
               ORDER BY          Requests.Created'
 exec(@strQuery);
END