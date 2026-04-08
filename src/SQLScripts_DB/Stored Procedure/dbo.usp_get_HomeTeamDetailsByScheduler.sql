USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_HomeTeamDetailsByScheduler]    Script Date: 13/09/2021 16:02:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_HomeTeamDetailsByScheduler]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_HomeTeamDetailsByScheduler]
-- Add the parameters for the stored procedure here
	@scheduledpersonID INT
AS
BEGIN
	select spl.TeamID,st.schedulingTeamName,st.defaultDutyDuration 
    from ScheduledPeople (nolock) sp
              JOIN ScheduledPersonTeam_LINK (nolock) spl on sp.ScheduledPersonID = spl.ScheduledPersonID and spl.IsHomeTeam = 1
              JOIN schedulingTeams (nolock) st on st.schedulingTeamId = spl.TeamID
              where sp.ScheduledPersonID = @scheduledpersonID and convert(datetime,EndDate,110) >= convert(datetime,convert(varchar,GETDATE(),110),110)
								  and (
								  convert(datetime, StartDate, 110) >= convert(datetime, convert(varchar,GETDATE(),110), 110) or
								  isnull(convert(datetime,EndDate,110),''9999-01-01'') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								  and convert(datetime,StartDate,110) <= convert(datetime,convert(varchar(30),getdate(),110),110))
	
END

'

EXEC dbo.sp_executesql @strSQL 

GO
