USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveCountApplications]    Script Date: 2/20/2026 7:18:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER         PROCEDURE [dbo].[usp_get_LeaveCountApplications]
@StartYear varchar(100),
@NextYear varchar(100),
@NetLogin varchar(100)
AS
BEGIN

SET NOCOUNT ON;

				Select  lt.ID As TypeID,Lp.dDate,
						Count(Lp.dDate) As CountApplications
					From Staff_Web_Config_LeaveGroups_Link scl (nolock) 
					Inner Join leave_types lt (nolock)  On scl.LeaveGroupID = lt.GroupID
					Inner Join  LeaveApplications Lp (nolock)  On lt.ID = Lp.LeaveTypesID
                 Where  scl.Login = @NetLogin And
						scl.Admin = 0 And scl.IsActive=1 and
						Lp.Deleted = 0 And CountLeave=1 and
						Lp.dDate >= Convert(DATETIME,@StartYear+'-04-01 00:00:00',102) And
						Lp.dDate <= Convert(DATETIME,@NextYear+'-03-31 00:00:00',102) and
						ISNULL(Lp.LeaveTypeID,0) NOT IN ( 3,4,5)
               Group By lt.ID,Lp.dDate  Order By Lp.dDate

END