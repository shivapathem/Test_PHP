USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_AllLeaveTypesDetails]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GET_AllLeaveTypesDetails]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_GET_AllLeaveTypesDetails] 
	-- Add the parameters for the stored procedure here
	@leaveapplicationid int

AS
BEGIN
	SET NOCOUNT ON;
	

SELECT        LeaveApplications_1.dDate, LeaveApplications_1.LeaveTypesID, LeaveApplications_1.ShortNotice, LeaveApplications_1.Approved,LeaveApplications_1.oversummer, 
                 LeaveApplications_1.CountLeave,LeaveApplications_1.unlikely, 
                         LeaveApplications.Login, LeaveApplications_1.Created, LeaveApplications_1.ID, LeaveApplications_1.isOK, leave_types.description AS TypeDesc, 
                         LeaveRequestGroups.ID AS GroupID, LeaveRequestGroups.Description AS GroupDesc, CASE WHEN ScheduledPeople.ScheduledPersonID IS NULL  THEN StaffDetails.Forename + '''' + StaffDetails.Surname ELSE ScheduledPeople.DisplayFirstName + '''' + ScheduledPeople.DisplayLastName END AS FullName, 
                         ref_LeaveApplications_Amounts.LeaveTypeID, ref_LeaveApplications_Amounts.Amount,Users.UserID,StaffDetails.StaffNumber,ScheduledPeople.ScheduledPersonID,LeaveApplications_1.LeaveID
FROM            dbo.LeaveApplications (nolock) INNER JOIN
                         dbo.LeaveApplications AS LeaveApplications_1 ON LeaveApplications.Login = LeaveApplications_1.Login 
						 INNER JOIN dbo.leave_types (nolock) ON LeaveApplications_1.LeaveTypesID = leave_types.ID 
						 INNER JOIN dbo.LeaveRequestGroups (nolock) ON leave_types.GroupID = LeaveRequestGroups.ID 
						 INNER JOIN  dbo.StaffDetails (nolock) ON LeaveApplications.Login = StaffDetails.NetLogin 
						 INNER JOIN dbo.Users (nolock) ON Users.NetLogin=StaffDetails.NetLogin	
						 LEFT JOIN dbo.ScheduledPeople (nolock) on ScheduledPeople.StaffDetailsID = StaffDetails.StaffID
						 LEFT OUTER JOIN dbo.ref_LeaveApplications_Amounts (nolock) ON LeaveApplications_1.ID = ref_LeaveApplications_Amounts.ApplicationID
WHERE        (LeaveApplications.ID = @leaveapplicationid) AND (LeaveApplications_1.dDate >= LeaveApplications.ddate) AND (LeaveApplications_1.Deleted = 0)
ORDER BY LeaveApplications_1.dDate
						
END'
EXEC dbo.sp_executesql @strSQL

GO
