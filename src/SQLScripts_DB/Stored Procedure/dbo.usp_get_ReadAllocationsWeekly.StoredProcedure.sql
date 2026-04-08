USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ReadAllocationsWeekly]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_ReadAllocationsWeekly]
@SchedulingTeamId VARCHAR(50),
@StartWeekNumber VARCHAR(50),
@EndWeekNumber VARCHAR(50),
@SortOrder INT,
@filterCond VARCHAR(MAX),
@filterTeamCond VARCHAR(MAX),
@filterOrderCond VARCHAR(MAX)

AS
BEGIN
    DECLARE @sql VARCHAR(MAX),
            @sortsql VARCHAR(MAX),
        @filterTeamCond2 VARCHAR(MAX)

    IF(@SortOrder=0)
      IF(@filterOrderCond = '''')
        SET @sortsql = '' ORDER BY sp.DisplayName ASC ''
      ELSE
        SET @sortsql = @filterOrderCond
    ELSE
      SET @sortsql = ''''

    IF(@filterTeamCond = '''')
      BEGIN
        SET @filterTeamCond = ''spl.TeamID IN(''+@SchedulingTeamId+'')''
        SET @filterTeamCond2 = REPLACE(@filterTeamCond, ''spl.TeamID'', ''a.SchedulingTeamId'');
      END
    ELSE
      SET @filterTeamCond = @filterTeamCond;
      SET @filterTeamCond2 = REPLACE(@filterTeamCond, ''spl.TeamID'', ''a.SchedulingTeamId'');

    BEGIN
      IF(@filterCond != '''')
        SET @filterCond = '' AND (''+REPLACE(@filterCond,''_REPSQLCOND_'',char(39)+char(39))+'')''
      ELSE
        SET @filterCond = ''''

      SET @sql = ''SELECT  sd.PreferredForename, sd.Forename + '''' '''' + sd.Surname AS FullName,sp.DisplayName
      ,sd.StaffID AS StaffID,sd.StaffNumber,sd.NetLogin,sp.DisplayName,  
      a.SchedulingTeamId as  StaffDepartmentID,sp.PersonalEmail as Email1,s.active, s.inBuilding, 
      s.starttime AS SignInStartTime, s.endtime AS SignInEndTime, a.*,
      FORMAT (a.DutyDate, ''''yyyy-MM-dd'''') as DutyDate
      FROM dbo.Allocations as a
      INNER JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID = a.SchedulingPersonID
      LEFT JOIN ScheduledPersonTeam_LINK spl (NOLOCK) on spl.ScheduledPersonID = sp.ScheduledPersonID 
      AND ''+@filterTeamCond+'' AND spl.EndDate IS NULL
      LEFT JOIN Allocations_jobs aj (NOLOCK) ON a.ID = aj.AllocationID AND spl.TeamID = aj.schedulingTeamId AND 
      aj.SchedulingPersonID=spl.ScheduledPersonID AND (aj.isActive=1 OR aj.isActive is NULL)
      LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
      LEFT JOIN StaffContract sct (NOLOCK) on sd.StaffID = sct.StaffID      
      LEFT JOIN skills_programmes_staff_link spsl on spsl.staff_id = sd.StaffID
      LEFT OUTER JOIN Signin s ON a.SchedulingPersonID = s.SchedulingPersonID AND a.WeekNumber = s.iWeek 
      AND a.iDay = s.iDay
      Where ''+@filterTeamCond2+'' And a.WeekNumber >= ''+@StartWeekNumber+'' AND 
      a.WeekNumber <= ''+@EndWeekNumber+@filterCond

      SET @sql = @sql + @sortsql
      exec (@sql)
    END
END
'

EXEC dbo.sp_executesql @strSQL

GO