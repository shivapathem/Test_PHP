USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveApplicationsDetails]    Script Date: 04/08/2025 21:36:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_get_LeaveApplicationsDetails]
	@leaveapplicationid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
   SELECT LA.dDate, 
          LA.Approved, 
		  LA.Sent,
		  LA.LeaveTypesID, 
		  LA.ID,
		  ud.UD_UserID AS UserID,
		  LA.SchedulingPersonID AS ScheduledPersonID, 
          ud.UD_DisplayName AS FullName,
          LT.description AS TypeDesc, 
		  LT.GroupID, 
		  LRG.ID AS GroupID, 
		  LRG.Description AS GroupDesc, 
		  ud.UD_InternalEmail as emailto,
          LT.ID AS TypeID,
		  LA.unlikely,
		  ud.UD_InternalEmail AS InternalEmail,
		  LRG.email AS emailfrom,
		  LRG.emailcopiesto,
		  LA.Login,
		  LA.ShortNotice,
		  ud.UD_StaffNumber as StaffNumber,
		  LA.OfficeComments,
		  LA.Comments,
		  LA.Created, 
		  la.History,
		  LA.LeaveStartTime,
		  LA.LeaveEndTime
     FROM LeaveApplications (nolock) LA
	INNER JOIN UserDetails(nolock) ud on la.SchedulingPersonID=ud.UD_UserID
    INNER JOIN leave_types (nolock) LT ON LA.LeaveTypesID = LT.ID 
    INNER JOIN LeaveRequestGroups (nolock) LRG ON LT.GroupID = LRG.ID
    WHERE LA.ID = @leaveapplicationid

END