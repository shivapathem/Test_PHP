USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_userLeaveRequestByNetLoginandRequester]    Script Date: 08/12/2023 16:47:11 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_userLeaveRequestByNetLoginandRequester] 
	-- Add the parameters for the stored procedure here
	@adminLogin varchar(20),
	@requesterLogin varchar(20)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
    SELECT ISNULL(Staff_Web_Config_LeaveGroups_Link.Admin, 0) AS LeaveAdmin, LeaveRequestGroups.Description, LeaveRequestGroups.ID,ISNULL(LeaveRequestGroups.IsPartDayLeaveAllowed,0) AS IsPartDayLeaveAllowed,  leave_types.ID AS LeaveTypeID, leave_types.description AS LeaveTypeDescription, RequestTypes.ID AS RequestTypeID, RequestTypes.description AS RequestType 
            FROM Staff_Web_Config_LeaveGroups_Link 
			INNER JOIN Staff_Web_Config_LeaveGroups_Link AS Staff_Web_Config_LeaveGroups_Link_1 ON Staff_Web_Config_LeaveGroups_Link.LeaveGroupID = Staff_Web_Config_LeaveGroups_Link_1.LeaveGroupID 
            INNER JOIN LeaveRequestGroups ON Staff_Web_Config_LeaveGroups_Link.LeaveGroupID = LeaveRequestGroups.ID 
            LEFT OUTER JOIN leave_types ON LeaveRequestGroups.ID = leave_types.GroupID  
            LEFT OUTER JOIN RequestTypes ON LeaveRequestGroups.ID = RequestTypes.GroupID 
            WHERE (Staff_Web_Config_LeaveGroups_Link.Login = @adminLogin AND Staff_Web_Config_LeaveGroups_Link.Admin > 0 AND   Staff_Web_Config_LeaveGroups_Link.isActive = 1) AND (ISNULL(Staff_Web_Config_LeaveGroups_Link_1.Admin, 0) = 0) AND (Staff_Web_Config_LeaveGroups_Link_1.Login = @requesterLogin AND  Staff_Web_Config_LeaveGroups_Link_1.isActive = 1)
            ORDER BY LeaveRequestGroups.Description
END
