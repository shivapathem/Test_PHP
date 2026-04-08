USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveApplicationsbyNetLogin]    Script Date: 08/12/2023 16:43:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
--exec usp_get_LeaveApplicationsbyNetLogin '2022','2023','KaurDW1'
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_LeaveApplicationsbyNetLogin]
@StartYear varchar(100),
@NextYear varchar(100),
@NetLogin varchar(100)
AS
BEGIN


SET NOCOUNT ON;

   SELECT LA.ID AS LeaveID, 
          LA.dDate,
          LA.CountLeave, 
          LA.Created, 
          LA.Approved, 
          LA.ShortNotice,
          LA.unlikely, 
          LA.oversummer, 
          LA.LeaveTypesID, 
          LA.isOK, 
          LG.HoursPerLeaveDay,
          LG.ExtraLeaveClicks, 
          LA.Login,  
          LT.countclicks,
          CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) 
               THEN 1 ELSE 0 
               END As  IsPartDayLeave
     FROM LeaveApplications (nolock) LA
    INNER JOIN leave_types (nolock) LT  ON LA.LeaveTypesID = LT.ID 
    INNER JOIN LeaveRequestGroups (nolock) LG ON LT.GroupID = LG.ID
    WHERE (LA.Deleted = 0) AND (LA.dDate >= CONVERT(DATETIME, @StartYear+'-04-01 00:00:00', 102)) 
      AND (LA.dDate <= CONVERT(DATETIME,@NextYear+'-03-31 00:00:00', 102))
      AND (LA.Login = @NetLogin)
    GROUP BY LA.ID, 
             LA.dDate,
             LA.CountLeave, 
             LA.Created, 
             LA.oversummer, 
             LA.LeaveTypesID,
             LG.HoursPerLeaveDay, 
             LA.Approved, 
             LA.ShortNotice, 
             LA.unlikely, 
             LA.isOK,
             LG.ExtraLeaveClicks, 
             LA.Login, 
             LT.countclicks,
             LA.LeaveStartTime,
             LA.LeaveEndTime
END

