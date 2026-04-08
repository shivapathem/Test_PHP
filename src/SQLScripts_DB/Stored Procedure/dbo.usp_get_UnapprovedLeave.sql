USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_UnapprovedLeave]    Script Date: 26/08/2025 16:33:13 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_get_UnapprovedLeave]
	@strtoday Date ,
	@netlogin varchar(60)
AS
BEGIN

	SELECT LR.Description AS GroupDescription, 
	       LR.ID AS GroupID, 
		   LT.ID AS TypeID, 
		   LT.description AS TypeDescription, 
		   LA.Login, 
		  ud.UD_DisplayName AS FullName, 
		   LA.dDate, 
		   LA.Created, 
		   LA.Approved, 
		   LA.Deleted, 
		   LA.Sent, 
		   isnull(LA.Comments, '') as Comments,                                 
		   isnull(LA.OfficeComments, '') as OfficeComments, 
		   LA.Attention, 
		   LA.ShortNotice, 
		   LA.unlikely, 
		   LA.oversummer,
		   LA.isOK, 
		   LA.ID,
		   LA.LeaveStartTime,
		   LA.LeaveEndTime,
		   LA.IsAgreed
     FROM  LeaveApplications (NOLOCK) LA
	INNER JOIN leave_types (NOLOCK) LT ON LA.LeaveTypesID = LT.ID 
	INNER JOIN LeaveRequestGroups (NOLOCK) LR ON LT.GroupID = LR.ID 
	INNER JOIN Staff_Web_Config_LeaveGroups_Link (NOLOCK) SC ON LR.ID = SC.LeaveGroupID 
	INNER JOIN UserDetails ud(nolock) on ud.UD_NetLogin=la.Login
	WHERE (LA.dDate >= CONVERT(DATETIME,@strtoday, 110)) 
      AND (LA.Deleted = 0 AND LA.Approved=0)
      AND (SC.Login = @netlogin) 
      AND (SC.Admin > 0)
      AND (SC.IsActive = 1)
    ORDER BY GroupDescription, 
	         LA.dDate
	
	
END