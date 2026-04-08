USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_AllocationsDetailsByDayAndTeam]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_get_AllocationsDetailsByDayAndTeam]
@intWeek INT,
@intDay INT,
@intTeamID INT

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    SELECT ae.ID, ae.DutyName, ae.WeekNumber, ae.iDay, ae.StartTime, ae.EndTime, ae.SchedulingTeamId, sd.StaffNumber, CASE WHEN sd.Forename IS NULL OR sd.Surname IS NULL THEN sp.DisplayName ELSE sd.Forename + ''  '' + sd.Surname END AS Fullname
        FROM StaffDetails sd
        INNER JOIN ScheduledPeople sp 
        ON sp.StaffDetailsID = sd.StaffID
        INNER JOIN ScheduledPersonTeam_LINK spt 
        ON spt.ScheduledPersonID = sp.ScheduledPersonID
        INNER JOIN Allocations a ON a.SchedulingTeamId = spt.TeamID 
        LEFT OUTER JOIN Allocations_edit ae ON ae.SchedulingTeamId = a.SchedulingTeamId  and a.ID = ae.AllocationID
        WHERE ae.WeekNumber = @intWeek
        AND ae.iDay = @intDay
        AND ae.SchedulingTeamId = @intTeamID
        ORDER BY Fullname
    

END
'

EXEC dbo.sp_executesql @strSQL

GO