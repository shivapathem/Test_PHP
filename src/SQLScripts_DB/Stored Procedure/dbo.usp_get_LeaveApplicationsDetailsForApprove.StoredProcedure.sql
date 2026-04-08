USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveApplicationsDetailsForApprove]    Script Date: 04/08/2025 21:40:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_get_LeaveApplicationsDetailsForApprove]
	@login VARCHAR(30),
	@dDate VARCHAR(25),
	@IsSingleDay INT = NULL
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.

   DECLARE 	@vSQL VARCHAR(MAX);

   SET @vSQL =
   'SELECT top(28) la.dDate, 
                  la.LeaveTypesID, 
				  la.ShortNotice, 
				  la.Approved,
				  la.oversummer, 
                  la.CountLeave,
				  la.unlikely, 
                  la.Login, 
				  la.Created, 
				  la.ID, 
				  la.isOK, 
				  la.IsAgreed, 
				  lt.description AS TypeDesc, 
                  lg.ID AS GroupID, 
				  lg.Description AS GroupDesc, 
				  ud.UD_DisplayName AS FullName, 
                  ud.UD_UserID as UserID,
				  ud.UD_StaffNumber as StaffNumber,
				  la.SchedulingPersonID as  ScheduledPersonID
  FROM   LeaveApplications (nolock) la
  INNER JOIN leave_types (nolock) lt ON la.LeaveTypesID = lt.ID 
  INNER JOIN LeaveRequestGroups (nolock)lg ON lt.GroupID = lg.ID 
  INNER JOIN UserDetails (nolock) ud on la.SchedulingPersonID=ud.UD_UserID
   WHERE la.Login = '''+@login+''''
  + CASE WHEN ISNULL(@IsSingleDay,0) = 0 THEN
    ' AND la.dDate >= CONVERT(DATETIME,'''+ @dDate+''', 102) '
	ELSE 
    ' AND la.dDate = CONVERT(DATETIME,'''+ @dDate+''', 102) '
	END
  +	' AND la.Deleted = 0
  ORDER BY la.dDate';

  EXEC (@vSQL)
				
END