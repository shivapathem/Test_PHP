USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_fetch_multiweeklyAllocationAndRota_FilterCount]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_fetch_multiweeklyAllocationAndRota_FilterCount] 
@intTeamId VARCHAR(50),
@intweekStart VARCHAR(50),
@intweekEnd VARCHAR(50),
@filterCond VARCHAR(MAX),
@filterTeamCond VARCHAR(MAX)
AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @sql VARCHAR(MAX),
			@filterCond2 VARCHAR(MAX),
			@filterTeamCond2 VARCHAR(MAX),
			@filterTeamCond3 VARCHAR(MAX)

	IF(@filterCond != '''')
		BEGIN
			SET @filterCond = '' AND (''+@filterCond+'')''
			SET @filterCond2 = REPLACE(@filterCond,''a.DutyName'',''er.DutyName'')
			SET @filterCond2 = REPLACE(@filterCond2,''sp.DisplayName'',''er.DisplayName'')
			SET @filterCond2 = REPLACE(@filterCond2,''spl.SortCode'',''er.SortCode'')
		END
	ELSE
		BEGIN
			SET @filterCond = ''''
			SET @filterCond2 = ''''
		END

	IF(@filterTeamCond != '''')
		BEGIN
			SET @filterTeamCond2 = REPLACE(@filterTeamCond,''a.SchedulingTeamId'',''er.SchedulingTeamId'')
			SET @filterTeamCond3 = REPLACE(@filterTeamCond,''a.SchedulingTeamId'',''SchedulingTeamId'')
		END
	ELSE
		BEGIN
			SET @filterTeamCond = '' a.SchedulingTeamId IN(''+@intTeamId+'') ''
			SET @filterTeamCond2 = REPLACE(@filterTeamCond,''a.SchedulingTeamId'',''er.SchedulingTeamId'')
			SET @filterTeamCond3 = REPLACE(@filterTeamCond,''a.SchedulingTeamId'',''SchedulingTeamId'')
		END 
	
	BEGIN
		SET @sql = ''SELECT DISTINCT sp.ScheduledPersonID
			FROM dbo.Allocations as a (NOLOCK)
			LEFT JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID = a.SchedulingPersonID
			LEFT JOIN ScheduledPersonTeam_LINK (NOLOCK) as spl on sp.ScheduledPersonID = spl.ScheduledPersonID
			LEFT JOIN Allocations_jobs aj (NOLOCK) ON a.ID = aj.AllocationID AND spl.TeamID = aj.schedulingTeamId AND aj.SchedulingPersonID=spl.ScheduledPersonID AND (aj.isActive=1 OR aj.isActive is NULL)
			LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
			LEFT JOIN StaffContract sct (NOLOCK) on sd.StaffID = sct.StaffID      
			LEFT JOIN skills_programmes_staff_link spsl on spsl.staff_id = sd.StaffID
			LEFT OUTER JOIN Signin ON a.StaffNumber = signin.staffnumber AND a.WeekNumber = signin.iWeek AND a.iDay = signin.iDay
			WHERE (''+@filterTeamCond+'') AND a.WeekNumber >= ''+@intweekStart+'' AND a.WeekNumber <= ''+@intweekEnd+'' AND spl.isActive = 1 AND a.isPublished = 1 AND (a.DutyDate >= spl.StartDate AND a.DutyDate <= spl.EndDate)
			''+@filterCond+''
			GROUP BY sp.ScheduledPersonID
			UNION ALL
			SELECT DISTINCT sp.ScheduledPersonID
			FROM Exported_rota as er (NOLOCK)
			LEFT JOIN ScheduledPeople as sp (NOLOCK) on sp.ScheduledPersonID = er.SchedulingPersonID
			INNER JOIN 
			( 
				SELECT td.ixYearWeek,
				CASE
					WHEN ROW_NUMBER() over(order by td.ixYearWeek) % td.WeeksInRota = 0
					THEN td.WeeksInRota
					ELSE ROW_NUMBER() over(order by td.ixYearWeek) % td.WeeksInRota
				END weeksinrota
				FROM 
				(
					SELECT DISTINCT ixYearWeek,er.WeeksInRota
					FROM TimeDimension td,
					(
						SELECT DISTINCT AssignmentStartWeek,WeeksInRota
						FROM Exported_rota
						WHERE ''+@filterTeamCond3+''
					) er
					WHERE td.ixYearWeek BETWEEN er.AssignmentStartWeek AND ''+@intweekEnd+''
				) TD
			) TD1 ON td1.weeksinrota = er.rotaweek
			LEFT JOIN StaffDetails sd (NOLOCK) on sd.StaffID = sp.StaffDetailsID
			LEFT JOIN StaffContract sct (NOLOCK) on sd.StaffID = sct.StaffID      
			LEFT JOIN skills_programmes_staff_link spsl on spsl.staff_id = sd.StaffID
			WHERE ''+@filterTeamCond2+'' AND td1.ixYearWeek BETWEEN ''+@intweekStart+'' AND ''+@intweekEnd+'' 
			''+@filterCond2+''
			GROUP BY sp.ScheduledPersonID''
		exec(@sql)
	END
END'
EXEC dbo.sp_executesql @strSQL

GO