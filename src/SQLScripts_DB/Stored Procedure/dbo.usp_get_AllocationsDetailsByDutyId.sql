USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_AllocationsDetailsByDutyId]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_get_AllocationsDetailsByDutyId]
@dutyId INT

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	SELECT DISTINCT ae.DutyName, ae.WeekNumber, ae.iDay, ae.StartTime, ae.EndTime, ae.SchedulingTeamId, sd.StaffNumber, sd.Forename + N'  ' + sd.Surname AS Fullname
        FROM StaffDetails sd
        INNER JOIN ScheduledPeople sp 
        ON sp.StaffDetailsID = sd.StaffID
        INNER JOIN ScheduledPersonTeam_LINK spt 
        ON spt.ScheduledPersonID = sp.ScheduledPersonID
        INNER JOIN Allocations a ON a.SchedulingTeamId = spt.TeamID and a.SchedulingPersonID = sp.ScheduledPersonID 
        INNER JOIN Allocations_edit ae ON ae.SchedulingTeamId = a.SchedulingTeamId  and a.AllocationID = ae.AllocationID
        WHERE (ae.ID = @dutyId)

END
'

EXEC dbo.sp_executesql @strSQL

GO