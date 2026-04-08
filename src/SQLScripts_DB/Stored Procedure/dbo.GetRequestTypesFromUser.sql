USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[GetRequestTypesFromUser]    Script Date: 28/10/2022 16:28:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[GetRequestTypesFromUser]
	-- Add the parameters for the stored procedure here
	@strUser varchar(50), @strStartDate varchar(50), @strEndDate varchar(50)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	Declare @strQuery VARCHAR(MAX);

	SET @strQuery='SELECT  Staff_Web_Config_LeaveGroups_Link.EFT, Staff_Web_Config_LeaveGroups_Link.LeaveGroupID AS GroupID, LeaveRequestGroups.Description AS GroupDescription,
                           LeaveRequestGroups.RequestsAllowedMonthly, RequestTypes.ID AS TypeID, RequestTypes.description AS TypeDescription, RequestTypes.day_0, RequestTypes.day_1, RequestTypes.day_2, 
                           RequestTypes.day_3, RequestTypes.day_4, RequestTypes.day_5, RequestTypes.day_6, RequestTypes.startdate, RequestTypes.enddate, 
                           RequestTypes.isRestricted, RequestTypes.UniqueCount, RequestTypes.RequestsAllowed, RequestTypes.Starts, RequestTypes.Ends, 
                           RequestTypes.AllowOverLimit, RequestTypes.AffectLocks
               FROM        Staff_Web_Config_LeaveGroups_Link (Nolock)
               INNER JOIN  LeaveRequestGroups (Nolock) ON Staff_Web_Config_LeaveGroups_Link.LeaveGroupID = LeaveRequestGroups.ID 
               INNER JOIN  RequestTypes (Nolock) ON LeaveRequestGroups.ID = RequestTypes.GroupID
               WHERE    (Staff_Web_Config_LeaveGroups_Link.IsActive=1) AND  (Staff_Web_Config_LeaveGroups_Link.Login = '''+@strUser+''') 
               AND         (Staff_Web_Config_LeaveGroups_Link.Admin = 0) 
               AND         (RequestTypes.enddate >= CONVERT(DATETIME, '''+@strStartDate+''', 102)) 
               AND (RequestTypes.startdate <= CONVERT(DATETIME, '''+@strEndDate+''', 102))';
	exec(@strQuery);
END
