USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveApplicationsDetails]    Script Date: 08/12/2023 16:47:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_get_LeaveApplicationsDetails]
	@leaveapplicationid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
   SELECT LA.dDate, 
          LA.Approved, 
		  LA.Sent,
		  LA.LeaveTypesID, 
		  LA.ID,u.UserID,
		  SP.ScheduledPersonID, 
          CASE WHEN SP.ScheduledPersonID IS NULL  
		       THEN sd.Forename + ' ' + sd.Surname 
			   ELSE SP.DisplayFirstName + ' ' + SP.DisplayLastName 
		  END AS FullName,
          LT.description AS TypeDesc, 
		  LT.GroupID, 
		  LRG.ID AS GroupID, 
		  LRG.Description AS GroupDesc, 
		  sd.InternalEmail as emailto,
          LT.ID AS TypeID,
		  LA.unlikely,
		  sd.InternalEmail,
		  LRG.email AS emailfrom,
		  LRG.emailcopiesto,
		  LA.Login,
		  LA.ShortNotice,
		  sd.StaffNumber,
		  LA.OfficeComments,
		  LA.Comments,
		  LA.Created, 
		  la.History,
		  LA.LeaveStartTime,
		  LA.LeaveEndTime
     FROM LeaveApplications (nolock) LA
	INNER JOIN StaffDetails (nolock) sd on sd.NetLogin =LA.Login
	INNER JOIN Users (nolock) u on u.NetLogin = sd.NetLogin
	 LEFT JOIN ScheduledPeople (nolock) SP on SP.StaffDetailsID=sd.StaffID and SP.UserID =U.UserID
    INNER JOIN leave_types (nolock) LT ON LA.LeaveTypesID = LT.ID 
    INNER JOIN LeaveRequestGroups (nolock) LRG ON LT.GroupID = LRG.ID
    WHERE LA.ID = @leaveapplicationid

END
