USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ReadAllocationsWeekly_FilterCount]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_ReadAllocationsWeekly_FilterCount]
@TeamID VARCHAR(10),
@StartWeekNo VARCHAR(10),
@EndWeekNo VARCHAR(10),
@startDate VARCHAR(10),
@endDate VARCHAR(10),
@filterCond VARCHAR(MAX)

AS
BEGIN
  SET NOCOUNT ON;

    DECLARE @sql VARCHAR(MAX)
    DECLARE @sqlCond VARCHAR(MAX)

  BEGIN
    IF(@filterCond != '''')
      SET @filterCond = '' AND (''+REPLACE(@filterCond,''_REPSQLCOND_'',char(39)+char(39))+'')''
    ELSE
      SET @filterCond = ''''

    IF(@startDate = ''0000-00-00'')
      SET @sqlCond = ''a.WeekNumber >= ''+@StartWeekNo+'' AND a.WeekNumber <= ''+@EndWeekNo
    ELSE
      SET @sqlCond = ''a.DutyDate >= CONVERT(DATETIME,''''''+@startDate+'''''',101) AND a.DutyDate < CONVERT(DATETIME,''''''+@endDate+'''''',101)''

    SET @sql = ''SELECT DISTINCT CASE WHEN ae.ID IS NULL THEN a.DutyName ELSE ae.DutyName END AS DutyName FROM Allocations a
        LEFT OUTER JOIN  Allocations_edit as ae ON ae.AllocationID = a.ID 
        LEFT OUTER JOIN ScheduledPeople AS sp ON a.SchedulingPersonID = sp.ScheduledPersonID
        LEFT JOIN StaffDetails sd ON sp.StaffDetailsID=sd.StaffID
        WHERE (''+@sqlCond+'' AND a.SchedulingTeamId=''+@TeamID+'' AND a.isPublished=1 AND a.isEditable=1 AND a.isActive=1)
        ''+@filterCond+''
        ORDER BY DutyName'';
    exec(@sql)
  END
END
'

EXEC dbo.sp_executesql @strSQL

GO