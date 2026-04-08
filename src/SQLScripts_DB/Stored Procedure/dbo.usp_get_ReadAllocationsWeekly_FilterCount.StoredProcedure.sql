USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsWeekly_FilterCount]    Script Date: 25/05/2022 20:39:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_ReadAllocationsWeekly_FilterCount]
@SchedulingTeamId VARCHAR(50),
@StartWeekNumber VARCHAR(50),
@EndWeekNumber VARCHAR(50),
@SortOrder INT,
@filterCond1 VARCHAR(MAX),
@filterTeamCond VARCHAR(MAX)

AS
BEGIN
  DECLARE @sql VARCHAR(MAX)
  DECLARE @filterTeamCond2 VARCHAR(MAX)

  IF(@filterCond1 != '')
    SET @filterCond1 = ' AND ('+REPLACE(@filterCond1,'_REPSQLCOND_',char(39)+char(39))+')'
  ELSE
    SET @filterCond1 = ''

  IF(@filterTeamCond = '')
    BEGIN
      SET @filterTeamCond = 'spl.TeamID IN('+@SchedulingTeamId+')'
      SET @filterTeamCond2 = REPLACE(@filterTeamCond, 'spl.TeamID', 'a.SchedulingTeamId');
    END
  ELSE
    BEGIN
      SET @filterTeamCond = @filterTeamCond
      SET @filterTeamCond2 = REPLACE(@filterTeamCond, 'spl.TeamID', 'a.SchedulingTeamId');
    END

  BEGIN
    SET @sql = 'SELECT DISTINCT sp.DisplayName
    FROM dbo.Allocations as a
    INNER JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID = a.SchedulingPersonID
    LEFT JOIN ScheduledPersonTeam_LINK spl (NOLOCK) on spl.ScheduledPersonID = sp.ScheduledPersonID 
    AND '+@filterTeamCond+' 
    LEFT JOIN Allocations_jobs aj (NOLOCK) ON a.ID = aj.AllocationID AND spl.TeamID = aj.schedulingTeamId AND 
    aj.SchedulingPersonID=spl.ScheduledPersonID AND (aj.isActive=1 OR aj.isActive is NULL)
    LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
    LEFT JOIN StaffContract sct (NOLOCK) on sd.StaffID = sct.StaffID      
    LEFT JOIN skills_programmes_staff_link spsl on spsl.staff_id = sd.StaffID
    LEFT OUTER JOIN Signin s ON a.StaffNumber = s.staffnumber AND a.WeekNumber = s.iWeek 
    AND a.iDay = s.iDay
    Where '+@filterTeamCond2+' And a.WeekNumber >= '+@StartWeekNumber+' AND 
    a.WeekNumber <= '+@EndWeekNumber+@filterCond1
    exec (@sql)
  END
END
