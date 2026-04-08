USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GET_getAllocationsDetailsByStaffNumber]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_GET_getAllocationsDetailsByStaffNumber]
@schedulingpersonid INT,
@weeknumber INT,
@iday INT,
@teamid INT 

AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @isEdited INT

	SELECT @isEdited = isEdited
	FROM Allocations 
	WHERE  WeekNumber = @weeknumber 
	AND iDay = @iday 
	AND SchedulingPersonID = @schedulingpersonid
	AND SchedulingTeamId = @teamid

	IF @isEdited <> 1
		BEGIN
			SELECT ID, DutyName,WeekNumber,iDay,Duration,dutyBreakTime,MannualOThours
			FROM Allocations 
			WHERE  WeekNumber = @weeknumber 
			AND iDay = @iday 
			AND SchedulingPersonID = @schedulingpersonid
			AND SchedulingTeamId = @teamid
			ORDER BY   WeekNumber, iDay  
		END
	ELSE
		BEGIN
			SELECT a.DutyName, a.WeekNumber, a.iDay, ae.Duration, ae.dutyBreakTime, a.MannualOThours
			FROM Allocations a
			LEFT JOIN Allocations_edit ae
			ON a.ID = ae.AllocationID
			WHERE a.WeekNumber = @weeknumber 
			AND a.iDay = @iday
			AND a.SchedulingPersonID = @schedulingpersonid
			AND a.SchedulingTeamId = @teamid
		END

END
'

EXEC dbo.sp_executesql @strSQL

GO

