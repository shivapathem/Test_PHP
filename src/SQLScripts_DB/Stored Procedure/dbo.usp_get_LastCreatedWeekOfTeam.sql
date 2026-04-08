USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_LastCreatedWeekOfTeam]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_LastCreatedWeekOfTeam] 
@intTeamId INT
AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @weekNumber INT

	BEGIN
		SELECT TOP 1 @weekNumber = WeekNumber FROM Allocations WHERE SchedulingTeamId = @intTeamId ORDER BY ID DESC
		IF(@weekNumber > 0)
			return @weekNumber
		ELSE
			return 0
	END
END'
EXEC dbo.sp_executesql @strSQL

GO