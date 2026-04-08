USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_UnAllocatedDailyAllocations]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_UnAllocatedDailyAllocations]
@WeekNumber INT,
@iDay        INT,
@SchedulingTeamId INT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


select Allocations.SchedulingPersonID,Allocations.schedulingTeamId,Allocations.WeekNumber, Allocations.iDay,
 Allocation_jobs_edit.ID AS JobID, Allocation_jobs_edit.StartTime AS JobStartTime, 
 Allocation_jobs_edit.EndTime AS JobEndTime, Allocation_jobs_edit.JobName, Allocation_jobs_edit.Edited AS JobEdited, Allocation_jobs_edit.Programme, Allocation_jobs_edit.JobBackColour, 
  Allocation_jobs_edit.JobFontColour, Allocation_jobs_edit.Comments AS JobComments,
 CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.ID ELSE Allocations_edit.ID END AS DutyID,
  CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.DutyName ELSE Allocations_edit.DutyName END AS DutyName, 
    CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.StartTime ELSE Allocations_edit.StartTime END AS StartTime, 
  CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.EndTime ELSE Allocations_edit.EndTime END AS EndTime, 
  CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.Duration ELSE Allocations_edit.Duration END AS Duration,
   CASE WHEN Allocations_edit.ID IS NULL THEN CASE WHEN Allocations.DutyComments IS NULL THEN 0 ELSE DataLength(Allocations.DutyComments) END ELSE CASE WHEN Allocations_edit.DutyComments IS NULL THEN 0 ELSE DataLength(Allocations_edit.DutyComments) END END AS DutyComments,  
  CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.ID ELSE Allocations_edit.AllocationID END AS AllocationID,
  CASE WHEN Allocations_edit.ID IS NULL THEN 0 ELSE Allocations_edit.History END AS History,
  CASE WHEN Allocations_edit.ID IS NULL THEN 1 ELSE Allocations_edit.editable END AS editable,
  CASE WHEN Allocations_edit.ID IS NULL THEN 1 ELSE Allocations_edit.isworking END AS isworking,
  CASE WHEN Allocations_edit.ID IS NULL THEN 0 ELSE Allocations_edit.deleted END AS deleted,
  CASE WHEN Allocations_edit.ID IS NULL THEN 1 ELSE Allocations_edit.edited END AS edited,
  CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.BackColour ELSE Allocations_edit.BackColour END AS AllocBackColour, 
  CASE WHEN Allocations_edit.ID IS NULL THEN Allocations.FontColour ELSE Allocations_edit.FontColour END AS AllocFontColour

  from Allocations_edit RIGHT OUTER JOIN 
  Allocations  ON Allocations.ID=Allocations_edit.AllocationID 
  LEFT OUTER JOIN
Allocation_jobs_edit ON Allocations.ID = Allocation_jobs_edit.AllocationID AND Allocations.schedulingTeamId = Allocation_jobs_edit.schedulingTeamId
WHERE        (Allocations.SchedulingPersonID = N''0'')  AND (Allocations.schedulingTeamId = @SchedulingTeamId) AND 
 (Allocations.WeekNumber =  @WeekNumber) AND (Allocations.iDay = @iDay)  AND (NOT (Allocations.StartTime IS NULL))
 ORDER BY Allocations.StartTime, Allocations.DutyName



END
'

EXEC dbo.sp_executesql @strSQL

GO