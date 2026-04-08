USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveApplicationsByWeek]    Script Date: 28/08/2025 12:55:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_get_LeaveApplicationsByWeek]
@StartDate DATE,
@EndDate DATE,
@isAdmin  INT,
@NetLogin varchar(20)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

		DECLARE	@query  AS  NVARCHAR(MAX);

		

SET @query = ' SELECT	LR.ID AS GroupID, 
                     LT.ID AS TypeID, 
					 LOWER(LA.Login) as Login,
                     ud.UD_DisplayName as FullName,
                     LA.dDate,
					 LA.CountLeave, 
					 UD_UserID as ScheduledPersonID,
					 LA.Created, 
					 LA.Approved, 
					 LA.Comments,
					 LA.OfficeComments, 
					 LA.Attention, 
					 LA.ShortNotice, 
					 LA.unlikely, 
					 LA.oversummer,             
					 LA.isOK, 
					 LA.ID,
					 CASE WHEN (ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) 
			              THEN 1 ELSE 0 END As IsPartDayLeaveApplied,
					 LA.LeaveStartTime,
					 LA.LeaveEndTime,
					 asp.ASP_AllocationsSPID AS AllocationID,
					 LA.IsAgreed
                FROM Staff_Web_Config_LeaveGroups_Link SW (nolock) 
               INNER JOIN LeaveRequestGroups LR (nolock) ON SW.LeaveGroupID = LR.ID 
               INNER JOIN leave_types LT (nolock) ON LR.ID = LT.GroupID 
               INNER JOIN LeaveApplications LA (nolock) ON LT.ID = LA.LeaveTypesID 
			   INNER JOIN UserDetails ud (nolock) on LA.Login = ud.UD_NetLogin
			   LEFT JOIN Allocationsscheduledpersons ASP(nolock) on  Asp.ASP_SchedulingPersonID =LA.SchedulingPersonID
									                             AND Asp.ASP_DutyDate=LA.dDate 
				WHERE SW.IsActive=1
			     AND SW.Login = '''+@NetLogin+''' '
			  +' AND SW.Admin ' + CASE WHEN @isAdmin = 0 THEN ' = 0 ' ELSE ' >= 1 ' END
			  +' AND LA.dDate >= '''+ FORMAT(@StartDate,'yyyy-MM-dd')+''''
              +' AND LA.dDate <= '''+ FORMAT(@EndDate,'yyyy-MM-dd')+''''
              +' AND LA.Deleted = 0
               ORDER BY LA.Created'

 exec sp_executesql @query

END