USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_EmailsToSend]    Script Date: 15/09/2025 20:52:52 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_Get_EmailsToSend] 
	@netlogin varchar(100)
AS
BEGIN

	SET NOCOUNT ON;

	SET DATEFORMAT YMD

 SELECT LA.Login, 
        ud.UD_DisplayName AS FullName, 
        LR.Description AS GroupDescription, 
		LVT.description AS TypeDescription,
		LA.Approved,
		LA.ID,
		LA.dDate, 
		LR.ID AS GroupID,
		LR.email AS EmailFrom,
        LR.emailcopiesto,
		LA.OfficeComments, 
		LA.Comments,
		ud.UD_InternalEmail AS UserEmail,
		LT.Description AS LeaveCategory,
		RL.Amount AS LeaveCategoryAmount,
		LA.sentOptionValue
  FROM  LeaveApplications  (NOLOCK) LA                    
  INNER JOIN leave_types (NOLOCK) LVT ON LA.LeaveTypesID = LVT.ID 
  INNER JOIN LeaveRequestGroups (NOLOCK) LR ON LVT.GroupID = LR.ID 
  INNER JOIN Staff_Web_Config_LeaveGroups_Link (NOLOCK) SW ON LR.ID = SW.LeaveGroupID					                 
  INNER JOIN UserDetails ud(nolock) on la.SchedulingPersonID=ud.UD_UserID		
   LEFT JOIN ref_LeaveApplications_Amounts RL ON RL.ApplicationID = LA.ID
   LEFT JOIN LeaveAllocateTypes LT ON LT.id = RL.LeaveTypeID
  WHERE LA.Sent = 0
    AND LA.Deleted = 0 
    AND LA.Approved = 1
    AND SW.Login = @netlogin 
	and SW.isActive = 1
  ORDER BY ud.UD_DisplayName, LA.dDate ASC;
END