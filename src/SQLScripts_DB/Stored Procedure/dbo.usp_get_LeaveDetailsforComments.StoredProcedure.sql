USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveDetailsforComments]    Script Date: 14/09/2025 19:23:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_get_LeaveDetailsforComments]
  -- Add the parameters for the stored procedure here
  @LeaveApplicationID int
AS
BEGIN

  SELECT ud.UD_DisplayName AS FullName,
      LA.Login,
      LA.ID,
      LA.dDate,
      LA.Comments,
      LA.OfficeComments,
      LA.LeaveStartTime,
      LA.LeaveEndTime     
     FROM LeaveApplications (nolock) LA
    inner join UserDetails (nolock) ud on LA.SchedulingPersonID=ud.UD_UserID
    WHERE (LA.ID  = @LeaveApplicationID)


END