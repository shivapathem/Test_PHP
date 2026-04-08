USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveRequestGroupsDetails]    Script Date: 28/10/2022 16:18:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_LeaveRequestGroupsDetails]
@NetLogin varchar(100)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

SELECT  LeaveRequestGroups.HoursPerLeaveDay, LeaveRequestGroups.ExtraLeaveClicks
                 FROM          LeaveRequestGroups (nolock)
                 INNER JOIN    Staff_Web_Config_LeaveGroups_Link (nolock) ON LeaveRequestGroups.ID = Staff_Web_Config_LeaveGroups_Link.LeaveGroupID
                 WHERE         (Staff_Web_Config_LeaveGroups_Link.Login = @Netlogin) 
                 AND           (Staff_Web_Config_LeaveGroups_Link.Admin = 0) AND (Staff_Web_Config_LeaveGroups_Link.IsActive=1); 

END
