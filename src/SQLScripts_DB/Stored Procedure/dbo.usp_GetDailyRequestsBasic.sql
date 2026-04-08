USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetDailyRequestsBasic]    Script Date: 26/08/2025 20:02:42 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_GetDailyRequestsBasic]
	-- Add the parameters for the stored procedure here
	@strStartDate Date,
	@intTeamID int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	--Declare @strQuery VARCHAR(MAX);
	--Declare @endDate varchar(30) ='9999-01-01';

	SELECT ud.UD_UserID as StaffNumber,
			Rt.description
	FROM Requests Rs (NOLOCK)
  INNER JOIN UserDetails ud (NOLOCK) ON Rs.ScheduledPersonID = ud.UD_UserID 
  INNER JOIN RequestTypes Rt (NOLOCK) ON Rs.RequestType = Rt.ID
  LEFT JOIN ScheduledPersonTeam_LINK sptl (NOLOCK) on sptl.ScheduledPersonID =ud.UD_UserID AND
		convert(datetime, sptl.StartDate, 110) >= convert(datetime, convert(varchar,GETDATE(),110), 110) or
		(isnull(convert(datetime,sptl.EndDate,110),'9999-01-01') >= convert(datetime,convert(varchar(30),getdate(),110),110) and
		convert(datetime,sptl.StartDate,110) <= convert(datetime,convert(varchar(30),getdate(),110),110))

  WHERE (Rs.dDate = CONVERT(DATETIME, @strStartDate, 102)) AND 
		(Rs.Deleted = 0) AND
		(sptl.TeamID = @intTeamID);
 
END